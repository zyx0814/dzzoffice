<?php
/*
 * 计划任务脚本 定期清理 缓存数据
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
if(!function_exists('mysql_escape_string')){
	function mysql_escape_string($str){
		if(function_exists('mysqli_escape_string')) return mysqli_escape_string($str);
		else return addslashes($str);
	}
}
global $db;
$db = & DB::object();
$tabletype = $db->version() > '4.1' ? 'Engine' : 'Type';
$tablepre = $_G['config']['db'][1]['tablepre'];
$dbcharset = $_G['config']['db'][1]['dbcharset'];
$backupdir = C::t('setting')->fetch('backupdir');
if(!$backupdir) {
	$backupdir = random(6);
	@mkdir('./data/backup_'.$backupdir, 0777);
	C::t('setting')->update('backupdir',$backupdir);
}
$backupdir = 'backup_'.$backupdir;
if(!is_dir('./data/'.$backupdir)) {
	mkdir('./data/'.$backupdir, 0777);
}
global $excepttables;
$excepttables=array();
$filename=date('ymd').'_'.random(8);
DB::query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');
$tables = arraykeys2(fetchtablelist($tablepre), 'Name');
$memberexist = array_search(DB::table('user'), $tables);
if($memberexist !== FALSE) {
	unset($tables[$memberexist]);
	array_unshift($tables, DB::table('user'));
}
$time = dgmdate(TIMESTAMP);
$success=false;
global $complete,$startrow;
$startrow=0;
$volume=0;$tableid = 0;$startfrom = 0;
while(!$success){
	$volume +=  1;
	$idstring = '# Identify: '.base64_encode("$_G[timestamp],".$_G['setting']['version'].",dzz,multivol,{$volume},{$tablepre},{$dbcharset}")."\n";
	$dumpcharset =  str_replace('-', '', $_G['charset']);
	$backupfilename = './data/'.$backupdir.'/'.str_replace(array('/', '\\', '.', "'"), '', $filename);
	$sqldump = '';
	$startfrom=$startrow;
	if(!$tableid && $volume == 1) {
		foreach($tables as $table) {
			$sqldump .= sqldumptablestruct($table);
		}
	}
	$complete = TRUE;
	for(; $complete && $tableid < count($tables) && strlen($sqldump) + 500 < 2048 * 1000; $tableid++) {
		$sqldump .= sqldumptable($tables[$tableid], $startfrom, strlen($sqldump));
		if($complete) {
			$startfrom = 0;
		}
	}
	
	$dumpfile = $backupfilename."-%s".'.sql';
	!$complete && $tableid--;
	if(trim($sqldump)) {
		$sqldump = "$idstring".
			"# <?php exit();?>\n".
			"# DzzOffice Multi-Volume Data Dump Vol.$volume\n".
			"# Version: DzzOffice! ".$_G['setting']['version']."\n".
			"# Time: $time\n".
			"# Type: dzz\n".
			"# Table Prefix: $tablepre\n".
			"#\n".
			"# Dzz! Home: http://www.dzzoffice.com\n".
			"# Please visit our website for newest infomation about DzzOffice\n".
			"# --------------------------------------------------------\n\n\n".
			$sqldump;
		$dumpfilename = sprintf($dumpfile, $volume);
		@$fp = fopen($dumpfilename, 'wb');
		@flock($fp, 2);
		if(@!fwrite($fp, $sqldump)) {
			@fclose($fp);
			runlog('database_export','database_export_file_invalid',1);
		} else {
			fclose($fp);
			unset($sqldump, $zip, $content);
			continue;
		}
	} else {
		$success=true;
		C::t('cache')->insert(array(
			'cachekey' => 'db_export',
			'cachevalue' => serialize(array('dateline' => $_G['timestamp'])),
			'dateline' => $_G['timestamp'],
		), false, true);
		
	}
}
function fetchtablelist($tablepre = '') {
	global $db;
	$arr = explode('.', $tablepre);
	$dbname = $arr[1] ? $arr[0] : '';
	$tablepre = str_replace('_', '\_', $tablepre);
	$sqladd = $dbname ? " FROM $dbname LIKE '$arr[1]%'" : "LIKE '$tablepre%'";
	$tables = $table = array();
	$query = DB::query("SHOW TABLE STATUS $sqladd");
	while($table = DB::fetch($query)) {
		$table['Name'] = ($dbname ? "$dbname." : '').$table['Name'];
		$tables[] = $table;
	}
	return $tables;
}

function arraykeys2($array, $key2) {
	$return = array();
	foreach($array as $val) {
		$return[] = $val[$key2];
	}
	return $return;
}

function sqldumptablestruct($table) {
	global $_G, $db, $excepttables;
	if(in_array($table, $excepttables)) {
		return;
	}
	$createtable = DB::query("SHOW CREATE TABLE $table", 'SILENT');
	if(!DB::error()) {
		$tabledump = "DROP TABLE IF EXISTS $table;\n";
	} else {
		return '';
	}
	

	$create = $db -> fetch_row($createtable);

	if (strpos($table, '.') !== FALSE) {
		$tablename = substr($table, strpos($table, '.') + 1);
		$create[1] = str_replace("CREATE TABLE $tablename", 'CREATE TABLE ' . $table, $create[1]);
	}
	$tabledump .= $create[1];
	$tablestatus = DB::fetch_first("SHOW TABLE STATUS LIKE '$table'");
	$tabledump .= (($tablestatus['Auto_increment'] && (strpos($tabledump,'AUTO_INCREMENT')===false))? " AUTO_INCREMENT=$tablestatus[Auto_increment]" : ''). ";\n\n";
	if ($_GET['sqlcompat'] == 'MYSQL40' && $db -> version() >= '4.1' && $db -> version() < '5.1') {
		if ($tablestatus['Auto_increment'] <> '') {
			$temppos = strpos($tabledump, ',');
			$tabledump = substr($tabledump, 0, $temppos) . ' auto_increment' . substr($tabledump, $temppos);
		}
		if ($tablestatus['Engine'] == 'MEMORY') {
			$tabledump = str_replace('TYPE=MEMORY', 'TYPE=HEAP', $tabledump);
		}
	}
	return $tabledump;
}

function sqldumptable($table, $startfrom = 0, $currsize = 0) {
	global $_G, $startrow, $dumpcharset, $complete, $excepttables;
	$db = & DB::object();
	$offset = 300;
	$tabledump = '';
	$tablefields = array();
	$_GET['usehex']=TRUE;
	$query = DB::query("SHOW FULL COLUMNS FROM $table", 'SILENT');
	if(strexists($table, 'adminsessions')) {
		return ;
	} elseif(!$query && DB::errno() == 1146) {
		return;
	} elseif(!$query) {
		$_GET['usehex'] = FALSE;
	} else {
		while($fieldrow = DB::fetch($query)) {
			$tablefields[] = $fieldrow;
		}
	}

	if(!in_array($table, $excepttables)) {
		$tabledumped = 0;
		$numrows = $offset;
		$firstfield = $tablefields[0];

			while($currsize + strlen($tabledump) + 500 < 2048 * 1000 && $numrows == $offset) {
				if($firstfield['Extra'] == 'auto_increment') {
					$selectsql = "SELECT * FROM $table WHERE $firstfield[Field] > $startfrom ORDER BY $firstfield[Field] LIMIT $offset";
				} else {
					$selectsql = "SELECT * FROM $table LIMIT $startfrom, $offset";
				}
				$tabledumped = 1;
				$rows = DB::query($selectsql);
				$numfields = $db->num_fields($rows);

				$numrows = DB::num_rows($rows);
				while($row = $db->fetch_row($rows)) {
					$comma = $t = '';
					for($i = 0; $i < $numfields; $i++) {
						
						$t .= $comma.($_GET['usehex'] && !empty($row[$i]) && (strexists($tablefields[$i]['Type'], 'char') || strexists($tablefields[$i]['Type'], 'text')) ? '0x'.bin2hex($row[$i]) : '\''.mysql_escape_string($row[$i]).'\'');
						$comma = ',';
					}
					
					if(strlen($t) + $currsize + strlen($tabledump) + 500 < 2048 * 1000) {
						if($firstfield['Extra'] == 'auto_increment') {
							$startfrom = $row[0];
						} else {
							$startfrom++;
						}
						$tabledump .= "INSERT INTO $table VALUES ($t);\n";
						
					} else {
						
						$complete = FALSE;
						break 2;
					}
				}
			}
		$startrow = $startfrom;
		$tabledump .= "\n";
	}
	return $tabledump;
}

?>
