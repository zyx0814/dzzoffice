<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}

error_reporting(E_ERROR);

$db = &DB::object();
$tabletype = $db -> version() > '4.1' ? 'Engine' : 'Type';
$tablepre = $_G['config']['db'][1]['tablepre'];
$dbcharset = $_G['config']['db'][1]['dbcharset'];
$backupdir = C::t('setting') -> fetch('backupdir');
if (!$backupdir) {
	$backupdir = random(6);
	@mkdir('./data/backup_' . $backupdir, 0777);
	C::t('setting') -> update('backupdir', $backupdir);
}
$backupdir = 'backup_' . $backupdir;
if (!is_dir('./data/' . $backupdir)) {
	mkdir('./data/' . $backupdir, 0777);
}
$operation = $_GET['operation'] ? $_GET['operation'] : 'export';
$op = $_GET['op'];
if ($operation == 'export') {

	$navtitle = lang('database_export') . ' - ' . lang('admin_navtitle');
	if (!submitcheck('exportsubmit', 1)) {

		$shelldisabled = function_exists('shell_exec') ? '' : 'disabled';
		$tables = '';
		$dztables = array();
		$tables = C::t('setting') -> fetch('custombackup', true);

		$dzz_tables = fetchtablelist($tablepre);

		foreach ($dzz_tables as $table) {
			$dztables[$table['Name']] = $table['Name'];
		}

		$defaultfilename = date('ymd') . '_' . random(8);

	} else {
		$submit = true;
		DB::query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');
		if (!$_GET['filename'] || preg_match("/(\.)(exe|jsp|asp|aspx|cgi|fcgi|pl)(\.|$)/i", $_GET['filename'])) {
			cpmsg('database_export_filename_invalid', '', 'error');
		}

		$time = dgmdate(TIMESTAMP);
		if ($_GET['type'] == 'dzz') {
			$tables = arraykeys2(fetchtablelist($tablepre), 'Name');
		} elseif ($_GET['type'] == 'custom') {
			$tables = array();
			if (empty($_GET['setup'])) {
				$tables = C::t('setting') -> fetch('custombackup', true);
			} else {
				C::t('setting') -> update('custombackup', empty($_GET['customtables']) ? '' : $_GET['customtables']);
				$tables = &$_GET['customtables'];
			}
			if (!is_array($tables) || empty($tables)) {
				showmessage('database_export_custom_invalid');
			}
		}

		$memberexist = array_search(DB::table('user'), $tables);
		if ($memberexist !== FALSE) {
			unset($tables[$memberexist]);
			array_unshift($tables, DB::table('user'));
		}

		$volume = intval($_GET['volume']) + 1;
		$idstring = '# Identify: ' . base64_encode("$_G[timestamp]," . $_G['setting']['version'] . ",{$_GET['type']},{$_GET['method']},{$volume},{$tablepre},{$dbcharset}") . "\n";

		$dumpcharset = $_GET['sqlcharset'] ? $_GET['sqlcharset'] : str_replace('-', '', $_G['charset']);
		$setnames = ($_GET['sqlcharset'] && $db -> version() > '4.1' && (!$_GET['sqlcompat'] || $_GET['sqlcompat'] == 'MYSQL41')) ? "SET NAMES '$dumpcharset';\n\n" : '';
		if ($db -> version() > '4.1') {
			if ($_GET['sqlcharset']) {
				DB::query('SET NAMES %s', array($_GET['sqlcharset']));
			}
			if ($_GET['sqlcompat'] == 'MYSQL40') {
				DB::query("SET SQL_MODE='MYSQL40'");
			} elseif ($_GET['sqlcompat'] == 'MYSQL41') {
				DB::query("SET SQL_MODE=''");
			}
		}

		$backupfilename = './data/' . $backupdir . '/' . str_replace(array('/', '\\', '.', "'"), '', $_GET['filename']);

		if ($_GET['usezip']) {
			require_once './core/class/class_zip.php';
		}

		if ($_GET['method'] == 'multivol') {

			$sqldump = '';
			$tableid = intval($_GET['tableid']);
			$startfrom = intval($_GET['startfrom']);

			if (!$tableid && $volume == 1) {
				foreach ($tables as $table) {
					$sqldump .= sqldumptablestruct($table);
				}
			}

			$complete = TRUE;
			for (; $complete && $tableid < count($tables) && strlen($sqldump) + 500 < $_GET['sizelimit'] * 1000; $tableid++) {
				$sqldump .= sqldumptable($tables[$tableid], $startfrom, strlen($sqldump));
				if ($complete) {
					$startfrom = 0;
				}
			}

			$dumpfile = $backupfilename . "-%s" . '.sql';
			!$complete && $tableid--;
			if (trim($sqldump)) {
				$sqldump = "$idstring" . "# <?php exit();?>\n" . "# DzzOffice Multi-Volume Data Dump Vol.$volume\n" . "# Version: DzzOffice! " . $_G['setting']['version'] . "\n" . "# Time: $time\n" . "# Type: {$_GET['type']}\n" . "# Table Prefix: $tablepre\n" . "#\n" . "# Dzz! Home: http://www.dzzoffice.com\n" . "# Please visit our website for newest infomation about DzzOffice\n" . "# --------------------------------------------------------\n\n\n" . "$setnames" . $sqldump;
				$dumpfilename = sprintf($dumpfile, $volume);

				@$fp = fopen($dumpfilename, 'wb');
				@flock($fp, 2);
				if (@!fwrite($fp, $sqldump)) {
					@fclose($fp);
					showmessage('database_export_file_invalid', '', 'error');
				} else {
					fclose($fp);
					if ($_GET['usezip'] == 2) {
						$fp = fopen($dumpfilename, "r");
						$content = @fread($fp, filesize($dumpfilename));
						fclose($fp);
						$zip = new zipfile();
						$zip -> addFile($content, basename($dumpfilename));
						$fp = fopen(sprintf($backupfilename . "-%s" . '.zip', $volume), 'w');
						if (@fwrite($fp, $zip -> file()) !== FALSE) {
							@unlink($dumpfilename);
						}
						fclose($fp);
					}
					unset($sqldump, $zip, $content);
					$redirecturl = BASESCRIPT . "?mod=system&op=database&operation=export&type=" . rawurlencode($_GET['type']) . "&saveto=server&filename=" . rawurlencode($_GET['filename']) . "&method=multivol&sizelimit=" . rawurlencode($_GET['sizelimit']) . "&volume=" . rawurlencode($volume) . "&tableid=" . rawurlencode($tableid) . "&startfrom=" . rawurlencode($startrow) . "&extendins=" . rawurlencode($_GET['extendins']) . "&sqlcharset=" . rawurlencode($_GET['sqlcharset']) . "&sqlcompat=" . rawurlencode($_GET['sqlcompat']) . "&exportsubmit=yes&usehex={$_GET['usehex']}&usezip={$_GET['usezip']}";
					$msg = lang('database_export_multivol_redirect', array('volume' => $volume));
					$msg_type = 'text-success';

				}
			} else {
				$msg = '';
				$volume--;
				$filelist = '<ul>';

				if ($_GET['usezip'] == 1) {
					$zip = new zipfile();
					$zipfilename = $backupfilename . '.zip';
					$unlinks = array();
					for ($i = 1; $i <= $volume; $i++) {
						$filename = sprintf($dumpfile, $i);
						$fp = fopen($filename, "r");
						$content = @fread($fp, filesize($filename));
						fclose($fp);
						$zip -> addFile($content, basename($filename));
						$unlinks[] = $filename;
						$filelist .= "<li><a href=\"$filename\">$filename</a></li>\n";
					}
					$fp = fopen($zipfilename, 'w');
					if (@fwrite($fp, $zip -> file()) !== FALSE) {
						foreach ($unlinks as $link) {
							@unlink($link);
						}
					} else {
						C::t('cache') -> insert(array('cachekey' => 'db_export', 'cachevalue' => serialize(array('dateline' => $_G['timestamp'])), 'dateline' => $_G['timestamp'], ), false, true);
						$msg .= lang('database_export_multivol_succeed', array('volume' => $volume, 'filelist' => $filelist));
						$msg_type = 'text-success';
					}
					unset($sqldump, $zip, $content);
					fclose($fp);
					@touch('./data/' . $backupdir . '/index.htm');
					$filename = $zipfilename;
					C::t('cache') -> insert(array('cachekey' => 'db_export', 'cachevalue' => serialize(array('dateline' => $_G['timestamp'])), 'dateline' => $_G['timestamp'], ), false, true);
					$msg .= lang('database_export_zip_succeed', array('filename' => $filename));
					$msg_type = 'text-success';
				} else {
					@touch('./data/' . $backupdir . '/index.htm');
					for ($i = 1; $i <= $volume; $i++) {
						$filename = sprintf($_GET['usezip'] == 2 ? $backupfilename . "-%s" . '.zip' : $dumpfile, $i);
						$filelist .= "<li><a href=\"$filename\">$filename</a></li>\n";
					}
					C::t('cache') -> insert(array('cachekey' => 'db_export', 'cachevalue' => serialize(array('dateline' => $_G['timestamp'])), 'dateline' => $_G['timestamp'], ), false, true);
					$msg .= lang('database_export_multivol_succeed', array('volume' => $volume, 'filelist' => $filelist));
					$msg_type = 'text-success';
				}
			}

		} else {

			$tablesstr = '';
			foreach ($tables as $table) {
				$tablesstr .= '"' . addslashes($table) . '" ';
			}
			//$tablesstr=escapeshellarg($tablesstr);
			require DZZ_ROOT . './config/config.php';
			$dbhost=$_config['db'][1]['dbhost'];
			$dbport=$_config['db'][1]['port'];
			$dbuser=$_config['db'][1]['dbuser'];
			$dbpw=$_config['db'][1]['dbpw'];
			$dbname=$_config['db'][1]['dbname'];
			
			$query = DB::query("SHOW VARIABLES LIKE 'basedir'");
			$arr = DB::fetch($query);
			$mysql_base=rtrim($arr['Value'],'/').'/';
			
			$dumpfile = DZZ_ROOT . $backupfilename . '.sql';
			@unlink($dumpfile); 
			

			$mysqlbin = $mysql_base == '/' ? '' : addslashes($mysql_base) . 'bin/';
			@shell_exec($mysqlbin . 'mysqldump --force --quick ' . ($db -> version() > '4.1' ? '--skip-opt --create-options' : '-all') . ' --add-drop-table' . ($_GET['extendins'] == 1 ? ' --extended-insert' : '') . '' . ($db -> version() > '4.1' && $_GET['sqlcompat'] == 'MYSQL40' ? ' --compatible=mysql40' : '') . ' --host="' . $dbhost . ($dbport ? (is_numeric($dbport) ? ' --port=' . $dbport : ' --socket="' . $dbport . '"') : '') . '" --user="' . $dbuser . '" --password="' . $dbpw . '" "' . $dbname . '" ' . $tablesstr . ' > ' . $dumpfile);
          
			if (@file_exists($dumpfile)) {

				if ($_GET['usezip']) {
					require_once   libfile('class/zip');
					$zip = new zipfile();
					$zipfilename = $backupfilename . '.zip';
					$fp = fopen($dumpfile, "r");
					$content = @fread($fp, filesize($dumpfile));
					fclose($fp);
					$zip -> addFile($idstring . "# <?php exit();?>\n " . $setnames . "\n #" . $content, basename($dumpfile));
					$fp = fopen($zipfilename, 'w');
					@fwrite($fp, $zip -> file());
					fclose($fp);
					@unlink($dumpfile);
					@touch('./data/' . $backupdir . '/index.htm');
					$filename = $backupfilename . '.zip';
					unset($sqldump, $zip, $content);
					C::t('cache') -> insert(array('cachekey' => 'db_export', 'cachevalue' => serialize(array('dateline' => $_G['timestamp'])), 'dateline' => $_G['timestamp'], ), false, true);
					$msg = lang('database_export_zip_succeed', array('filename' => $filename));
					$msg_type = 'text-success';
				} else {
					if (@is_writeable($dumpfile)) {
						$fp = fopen($dumpfile, 'rb+');
						@fwrite($fp, $idstring . "# <?php exit();?>\n " . $setnames . "\n #");
						fclose($fp);
					}
					@touch('./data/' . $backupdir . '/index.htm');
					$filename = $backupfilename . '.sql';
					C::t('cache') -> insert(array('cachekey' => 'db_export', 'cachevalue' => serialize(array('dateline' => $_G['timestamp'])), 'dateline' => $_G['timestamp'], ), false, true);
					$msg = lang('database_export_succeed', array('filename' => $filename));
					$msg_type = 'text-success';
				}

			} else {
				$msg = lang('database_shell_fail');
				$msg_type = 'text-error';

			}

		}

	}
	include  template('database');
} elseif ($operation == 'import') {

	$msg = '';
	$navtitle = lang('db_recover') . ' - ' . lang('admin_navtitle');
	if (($re = checkpermission('dbimport')) !== true) {
		$msg = $re;
		$msg_type = 'text-error';
		include  template('database');
		exit();
	}

	if (!submitcheck('deletesubmit')) {
		$exportlog = $exportsize = $exportziplog = array();
		if (is_dir(DZZ_ROOT . './data/' . $backupdir)) {
			$dir = dir(DZZ_ROOT . './data/' . $backupdir);
			while ($entry = $dir -> read()) {
				$entry = './data/' . $backupdir . '/' . $entry;
				if (is_file($entry)) {
					if (preg_match("/\.sql$/i", $entry)) {
						$filesize = filesize($entry);
						$fp = fopen($entry, 'rb');
						$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
						fclose($fp);
						$key = preg_replace('/^(.+?)(\-\d+)\.sql$/i', '\\1', basename($entry));
						$exportlog[$key][$identify[4]] = array('version' => $identify[1], 'type' => $identify[2], 'method' => $identify[3], 'volume' => $identify[4], 'filename' => $entry, 'dateline' => filemtime($entry), 'size' => $filesize);
						$exportsize[$key] += $filesize;
					} elseif (preg_match("/\.zip$/i", $entry)) {
						$filesize = filesize($entry);
						$exportziplog[] = array('type' => 'zip', 'filename' => $entry, 'size' => filesize($entry), 'dateline' => filemtime($entry));
					}
				}
			}
			$dir -> close();
		} else {
			$msg = lang('database_export_dest_invalid');
			$msg_type = 'text-error';
			include  template('database');
			exit();
		}

		$restore_url = $_G['siteurl'] . 'data/restore.php';
		$do_import_option = lang('do_import_option', array('restore_url' => $restore_url));
		$datasiteurl = $_G['siteurl'] . 'data/';
		$lang = lang();
		$list = array();
		foreach ($exportlog as $key => $val) {
			$info = $val[1];
			$info['dateline'] = is_int($info['dateline']) ? dgmdate($info['dateline']) : $lang['unknown'];
			$info['ftype'] = lang('db_export_' . $info['type']);
			$info['size'] = sizecount($exportsize[$key]);
			$info['volume'] = count($val);
			$info['method'] = $info['type'] != 'zip' ? ($info['method'] == 'multivol' ? lang('db_multivol') : lang('db_shell')) : '';

			$info['datafile_server'] = '.' . $info['filename'];
			$list[$key] = $info;
			foreach ($val as $info) {
				$info['dateline'] = is_int($info['dateline']) ? dgmdate($info['dateline']) : lang('unknown');
				$info['size'] = sizecount($info['size']);

				$list[$key]['list'][$key . '-' . $info['volume'] . '.sql'] = $info;
			}
		}

		foreach ($exportziplog as $info) {
			$info['dateline'] = is_int($info['dateline']) ? dgmdate($info['dateline']) : $lang['unknown'];
			$info['size'] = sizecount($info['size']);
			$info['ftype'] = lang('db_export_' . $info['type']);
			$info['method'] = $info['method'] == 'multivol' ? $lang['db_multivol'] : $lang['db_zip'];
			$info['datafile_server'] = '.' . $info['filename'];
			$key = substr(strrchr($info['filename'], "/"), 1);
			$list[$key] = $info;
			
		}

	} else {
		if (is_array($_GET['delete'])) {
			foreach ($_GET['delete'] as $filename) {
				$file_path = './data/' . $backupdir . '/' . str_replace(array('/', '\\'), '', $filename);
				if (is_file($file_path)) {
					@unlink($file_path);
				} else {
					$i = 1;
					while (1) {
						$file_path = './data/' . $backupdir . '/' . str_replace(array('/', '\\'), '', $filename . '-' . $i . '.sql');
						if (is_file($file_path)) {
							@unlink($file_path);
							$i++;
						} else {
							break;
						}
					}
				}
			}
			$msg = lang('database_file_delete_succeed');
			$msg_type = 'text-success';
			$redirecturl = dreferer();
		} else {
			$msg = lang('database_file_delete_invalid');
			$msg_type = 'text-error';
			$redirecturl = dreferer();
		}
	}
	include  template('database');

} elseif ($operation == 'runquery') {
	$navtitle = lang('nav_db_runquery') . ' - ' . lang('admin_navtitle');
	$checkperm = checkpermission('runquery', 0);
	if ($checkperm !== true) {
		$msg = $checkperm;
		$msg_type = 'text-error';
		include  template('database');
		exit();
	}
	$runquerys = array();
	if (!submitcheck('sqlsubmit')) {

	} else {
		$queries = $_GET['queries'];

		$sqlquery = splitsql(str_replace(array(' {tablepre}', ' cdb_', ' `cdb_', ' pre_', ' `pre_'), array(' ' . $tablepre, ' ' . $tablepre, ' `' . $tablepre, ' ' . $tablepre, ' `' . $tablepre), $queries));
		$affected_rows = 0;
		foreach ($sqlquery as $sql) {
			if (trim($sql) != '') {
				$sql = !empty($_GET['createcompatible']) ? syntablestruct(trim($sql), $db -> version() > '4.1', $dbcharset) : $sql;

				DB::query($sql, 'SILENT');
				if ($sqlerror = DB::error()) {
					break;
				} else {
					$affected_rows += intval(DB::affected_rows());
				}
			}
		}
		if ($sqlerror) {
			$msg = lang('database_run_query_invalid', array('sqlerror' => $sqlerror));
			$msg_type = 'text-error';
			$redirecturl = dreferer();
		} else {
			$msg = lang('database_run_query_succeed', array('affected_rows' => $affected_rows));
			$msg_type = 'text-success';
			$redirecturl = dreferer();
		}
	}
	include  template('database');
}

function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql) . (mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
}

function fetchtablelist($tablepre = '') {
	global $db;
	$arr = explode('.', $tablepre);
	$dbname = $arr[1] ? $arr[0] : '';
	$tablepre = str_replace('_', '\_', $tablepre);
	$sqladd = $dbname ? " FROM $dbname LIKE '$arr[1]%'" : "LIKE '$tablepre%'";
	$tables = $table = array();
	$query = DB::query("SHOW TABLE STATUS $sqladd");
	while ($table = DB::fetch($query)) {
		$table['Name'] = ($dbname ? "$dbname." : '') . $table['Name'];
		$tables[] = $table;
	}
	return $tables;
}

function arraykeys2($array, $key2) {
	$return = array();
	foreach ($array as $val) {
		$return[] = $val[$key2];
	}
	return $return;
}

function syntablestruct($sql, $version, $dbcharset) {

	if (strpos(trim(substr($sql, 0, 18)), 'CREATE TABLE') === FALSE) {
		return $sql;
	}

	$sqlversion = strpos($sql, 'ENGINE=') === FALSE ? FALSE : TRUE;

	if ($sqlversion === $version) {

		return $sqlversion && $dbcharset ? preg_replace(array('/ character set \w+/i', '/ collate \w+/i', "/DEFAULT CHARSET=\w+/is"), array('', '', "DEFAULT CHARSET=$dbcharset"), $sql) : $sql;
	}

	if ($version) {
		return preg_replace(array('/TYPE=HEAP/i', '/TYPE=(\w+)/is'), array("ENGINE=MEMORY DEFAULT CHARSET=$dbcharset", "ENGINE=\\1 DEFAULT CHARSET=$dbcharset"), $sql);

	} else {
		return preg_replace(array('/character set \w+/i', '/collate \w+/i', '/ENGINE=MEMORY/i', '/\s*DEFAULT CHARSET=\w+/is', '/\s*COLLATE=\w+/is', '/ENGINE=(\w+)(.*)/is'), array('', '', 'ENGINE=HEAP', '', '', 'TYPE=\\1\\2'), $sql);
	}
}

function sqldumptablestruct($table) {
	global $_G, $db, $excepttables;

	if (in_array($table, $excepttables)) {
		return;
	}

	$createtable = DB::query("SHOW CREATE TABLE $table", 'SILENT');

	if (!DB::error()) {
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

	if ($_GET['sqlcompat'] == 'MYSQL41' && $db -> version() < '4.1') {
		$tabledump = preg_replace("/TYPE\=(.+)/", "ENGINE=\\1 DEFAULT CHARSET=" . $dumpcharset, $tabledump);
	}
	if ($db -> version() > '4.1' && $_GET['sqlcharset']) {
		$tabledump = preg_replace("/(DEFAULT)*\s*CHARSET=.+/", "DEFAULT CHARSET=" . $_GET['sqlcharset'], $tabledump);
	}

	$tablestatus = DB::fetch_first("SHOW TABLE STATUS LIKE '$table'");
	$tabledump .= ($tablestatus['Auto_increment'] ? " AUTO_INCREMENT=$tablestatus[Auto_increment]" : ''). ";\n\n";
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
	global $_G, $db, $startrow, $dumpcharset, $complete, $excepttables;

	$offset = 300;
	$tabledump = '';
	$tablefields = array();

	$query = DB::query("SHOW FULL COLUMNS FROM $table", 'SILENT');
	if (strexists($table, 'adminsessions')) {
		return;
	} elseif (!$query && DB::errno() == 1146) {
		return;
	} elseif (!$query) {
		$_GET['usehex'] = FALSE;
	} else {
		while ($fieldrow = DB::fetch($query)) {
			$tablefields[] = $fieldrow;
		}
	}

	if (!in_array($table, $excepttables)) {
		$tabledumped = 0;
		$numrows = $offset;
		$firstfield = $tablefields[0];

		if ($_GET['extendins'] == '0') {
			while ($currsize + strlen($tabledump) + 500 < $_GET['sizelimit'] * 1000 && $numrows == $offset) {
				if ($firstfield['Extra'] == 'auto_increment') {
					$selectsql = "SELECT * FROM $table WHERE $firstfield[Field] > $startfrom ORDER BY $firstfield[Field] LIMIT $offset";
				} else {
					$selectsql = "SELECT * FROM $table LIMIT $startfrom, $offset";
				}
				$tabledumped = 1;
				$rows = DB::query($selectsql);
				$numfields = $db -> num_fields($rows);

				$numrows = DB::num_rows($rows);
				while ($row = $db -> fetch_row($rows)) {
					$comma = $t = '';
					for ($i = 0; $i < $numfields; $i++) {
						$t .= $comma . ($_GET['usehex'] && !empty($row[$i]) && (strexists($tablefields[$i]['Type'], 'char') || strexists($tablefields[$i]['Type'], 'text')) ? '0x' . bin2hex($row[$i]) : '\'' . (function_exists('mysql_escape_string')?mysql_escape_string($row[$i]): addslashes($row[$i])) . '\'');
						$comma = ',';
					}
					if (strlen($t) + $currsize + strlen($tabledump) + 500 < $_GET['sizelimit'] * 1000) {
						if ($firstfield['Extra'] == 'auto_increment') {
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
		} else {
			while ($currsize + strlen($tabledump) + 500 < $_GET['sizelimit'] * 1000 && $numrows == $offset) {
				if ($firstfield['Extra'] == 'auto_increment') {
					$selectsql = "SELECT * FROM $table WHERE $firstfield[Field] > $startfrom LIMIT $offset";
				} else {
					$selectsql = "SELECT * FROM $table LIMIT $startfrom, $offset";
				}
				$tabledumped = 1;
				$rows = DB::query($selectsql);
				$numfields = $db -> num_fields($rows);

				if ($numrows = DB::num_rows($rows)) {
					$t1 = $comma1 = '';
					while ($row = $db -> fetch_row($rows)) {
						$t2 = $comma2 = '';
						for ($i = 0; $i < $numfields; $i++) {
							$t2 .= $comma2 . ($_GET['usehex'] && !empty($row[$i]) && (strexists($tablefields[$i]['Type'], 'char') || strexists($tablefields[$i]['Type'], 'text')) ? '0x' . bin2hex($row[$i]) : '\'' . (function_exists('mysql_escape_string')?mysql_escape_string($row[$i]): addslashes($row[$i])) . '\'');
							$comma2 = ',';
						}
						if (strlen($t1) + $currsize + strlen($tabledump) + 500 < $_GET['sizelimit'] * 1000) {
							if ($firstfield['Extra'] == 'auto_increment') {
								$startfrom = $row[0];
							} else {
								$startfrom++;
							}
							$t1 .= "$comma1 ($t2)";
							$comma1 = ',';
						} else {
							$tabledump .= "INSERT INTO $table VALUES $t1;\n";
							$complete = FALSE;
							break 2;
						}
					}
					$tabledump .= "INSERT INTO $table VALUES $t1;\n";
				}
			}
		}

		$startrow = $startfrom;
		$tabledump .= "\n";
	}

	return $tabledump;
}

function splitsql($sql) {
	$sql = str_replace("\r", "\n", $sql);
	$ret = array();
	$num = 0;
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach ($queriesarray as $query) {
		$queries = explode("\n", trim($query));
		foreach ($queries as $query) {
			$ret[$num] .= $query[0] == "#" ? NULL : $query;
		}
		$num++;
	}
	return ($ret);
}

function slowcheck($type1, $type2) {
	$t1 = explode(' ', $type1);
	$t1 = $t1[0];
	$t2 = explode(' ', $type2);
	$t2 = $t2[0];
	$arr = array($t1, $t2);
	sort($arr);
	if ($arr == array('mediumtext', 'text')) {
		return TRUE;
	} elseif (substr($arr[0], 0, 4) == 'char' && substr($arr[1], 0, 7) == 'varchar') {
		return TRUE;
	}
	return FALSE;
}

function checkpermission($action, $break = 1) {
	global $_G;
	if (!isset($_G['config']['admincp'])) {
		return lang('db_config_admincp');
	} elseif (!$_G['config']['admincp'][$action]) {
		return lang('db_not_allow_config_admincp');
	} else {
		return true;
	}
}
?>
