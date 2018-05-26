<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

error_reporting(E_ERROR);
@set_time_limit(1000);
if(function_exists('set_magic_quotes_runtime')) {
	@set_magic_quotes_runtime(0);
}

define('IN_DZZ', TRUE);
define('IN_LEYUN', TRUE);
define('ROOT_PATH', dirname(__FILE__).'/../');
define('BS',DIRECTORY_SEPARATOR);//系统目录分割符
define('DZZ_ROOT', dirname(dirname(__FILE__)).BS);//系统根目录
define('CORE_NAME','core');//核心目录名
define('CORE_PATH',DZZ_ROOT.CORE_NAME.BS.'class');//核心类目录
define('APP_DIRNAME','dzz');//应用目录名

require ROOT_PATH.'./core/core_version.php';
require ROOT_PATH.'./install/include/install_var.php';
if(function_exists('mysqli_connect')) {
	require ROOT_PATH.'./install/include/install_mysqli.php';
} else {
	require ROOT_PATH.'./install/include/install_mysql.php';
}
require ROOT_PATH.'./install/include/install_function.php';
require ROOT_PATH.'./install/language/zh-cn/lang.php';

$view_off = getgpc('view_off');
define('VIEW_OFF', $view_off ? TRUE : FALSE);

$allow_method = array('show_license', 'env_check','dir_check', 'db_init', 'admin_init','ext_info', 'install_check', 'tablepre_check');
$step = intval(getgpc('step', 'R')) ? intval(getgpc('step', 'R')) : 0;
$method = getgpc('method');

if(empty($method) || !in_array($method, $allow_method)) {
	$method = isset($allow_method[$step]) ? $allow_method[$step] : '';
}

if(empty($method)) {
	show_msg('method_undefined', $method, 0);
}

if(file_exists($lockfile) && $method != 'ext_info') {
	show_msg('install_locked', '', 0);
} elseif(!class_exists('dbstuff')) {
	show_msg('database_nonexistence', '', 0);
}

timezone_set();



if(in_array($method, array('ext_info'))) {
	$isHTTPS = ($_SERVER['HTTPS'] && strtolower($_SERVER['HTTPS']) != 'off') ? true : false;
	$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	$sitepath = substr($PHP_SELF, 0, strrpos($PHP_SELF, '/'));
	$sitepath=preg_replace('/install$/i','',$sitepath);
	$bbserver = 'http'.($isHTTPS ? 's' : '').'://'.preg_replace("/\:\d+/", '', $_SERVER['HTTP_HOST']).($_SERVER['SERVER_PORT'] && $_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443 ? ':'.$_SERVER['SERVER_PORT'] : '').($sitepath);
}

if($method == 'show_license') {

	show_license();

} elseif($method == 'env_check') {

	VIEW_OFF && function_check($func_items);
	env_check($env_items);
	show_env_result($env_items,$func_items, $filesock_items);
	
} elseif($method == 'dir_check') {
	
	dirfile_check($dirfile_items);

	show_dirfile_result($dirfile_items);


} elseif($method == 'db_init') {

	
	$submit = true;

	$default_config = $_config = array();
	$default_configfile = './config/config_default.php';

	if(!file_exists(ROOT_PATH.$default_configfile)) {
		exit('config_default.php was lost, please reupload this  file.');
	} else {
		include ROOT_PATH.$default_configfile;
		$default_config = $_config;
	}
	
	
	/*if(file_exists(ROOT_PATH.CONFIG)) {//修改不调用已有的config.php内的信息
		include ROOT_PATH.CONFIG;
	} else {*/
		$_config = $default_config;
	//}

	$dbhost = $_config['db'][1]['dbhost'];
	$dbname = $_config['db'][1]['dbname'];
	$dbpw = $_config['db'][1]['dbpw'];
	$dbuser = $_config['db'][1]['dbuser'];
	$tablepre = $_config['db'][1]['tablepre'];
	$adminemail = 'admin@dzzoffice.com';

	$error_msg = array();
	if(isset($form_db_init_items) && is_array($form_db_init_items)) {
		foreach($form_db_init_items as $key => $items) {
			$$key = getgpc($key, 'p');
			if(!isset($$key) || !is_array($$key)) {
				$submit = false;
				break;
			}
			foreach($items as $k => $v) {
				$tmp = $$key;
				$$k = $tmp[$k];
				if(empty($$k) || !preg_match($v['reg'], $$k)) {
					if(empty($$k) && !$v['required']) {
						continue;
					}
					$submit = false;
					VIEW_OFF or $error_msg[$key][$k] = 1;
				}
			}
		}
	} else {
		$submit = false;
	}

	if($submit && !VIEW_OFF && $_SERVER['REQUEST_METHOD'] == 'POST') {
		$forceinstall = isset($_POST['dbinfo']['forceinstall']) ? $_POST['dbinfo']['forceinstall'] : '';
		$dbname_not_exists = true;
		if(!empty($dbhost) && empty($forceinstall)) {
			$dbname_not_exists = check_db($dbhost, $dbuser, $dbpw, $dbname, $tablepre);
			if(!$dbname_not_exists) {
				$form_db_init_items['dbinfo']['forceinstall'] = array('type' => 'checkbox', 'required' => 0, 'reg' => '/^.*+/');
				$error_msg['dbinfo']['forceinstall'] = 1;
				$submit = false;
				$dbname_not_exists = false;
			}
		}
	}

	if($submit) {
		
		$step = $step + 1;
		if(empty($dbname)) {
			show_msg('dbname_invalid', $dbname, 0);
		} else {
			$mysqlmode = function_exists("mysqli_connect") ? 'mysqli' :  'mysql';
			if($mysqlmode=='mysqli'){
				//兼容支持域名直接带有端口的情况
				if(strpos($dbhost,':')!==false){
					list($dbhost1,$port)=explode(':',$dbhost);

				}elseif(strpos($dbhost,'.sock')!==false){//地址直接是socket地址
					$unix_socket=$dbhost1;
					$dbhost1='localhost';
				}else{
					$dbhost1=$dbhost;
				}
				if(empty($port)) $port='3306';
				$link =  new mysqli($dbhost1, $dbuser, $dbpw, '', $port, $unix_socket);
				$errno =  $link->connect_errno;
				$error =  $link->connect_error;
			}else{
				list($dbhost1,$port)=explode(':',$dbhost);
				if(empty($port)) $port='3306';
				$link = @mysql_connect($dbhost, $dbuser, $dbpw);
				$errno = mysql_errno();
				$error = mysql_error();
			}
			if($errno) {
				if($errno == 1045) {
					show_msg('database_errno_1045', $error, 0);
				} elseif($errno == 2003 || $errno==2002) {
					show_msg('database_errno_2003', $error, 0);
				} else {
					show_msg('database_connect_error', $error, 0);
				}
			}
			$mysql_version = ($mysqlmode == 'mysqli') ? $link->server_info : mysql_get_server_info() ;
			if($mysql_version > '4.1') {
				if($mysqlmode == 'mysqli') {
					$link->query("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET ".DBCHARSET);
				} else {
					mysql_query("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET ".DBCHARSET, $link);
				}
			} else {
				if($mysqlmode == 'mysqli') {
					$link->query("CREATE DATABASE IF NOT EXISTS `$dbname`");
				} else {
					mysql_query("CREATE DATABASE IF NOT EXISTS `$dbname`", $link);
				}
			}

			if(($mysqlmode == 'mysqli') ?  $link->errno : mysql_errno($link)) {
				show_msg('database_errno_1044', ($mysqlmode == 'mysqli') ? $link->error: mysql_error($link) , 0,0);
			}
			if($mysqlmode == 'mysqli') {
				$link->close();
			} else {
				mysql_close($link);
			}
		}

		if(strpos($tablepre, '.') !== false || intval($tablepre{0})) {
			show_msg('tablepre_invalid', $tablepre, 0);
		}

		$uid = 1 ;
		$authkey = substr(md5($_SERVER['SERVER_ADDR'].$_SERVER['HTTP_USER_AGENT'].$dbhost.$dbuser.$dbpw.$dbname.$pconnect.substr($timestamp, 0, 6)), 8, 6).random(10);
		$_config['db'][1]['dbhost'] = $dbhost;
		$_config['db'][1]['dbname'] = $dbname;
		$_config['db'][1]['dbpw'] = $dbpw;
		$_config['db'][1]['dbuser'] = $dbuser;
		$_config['db'][1]['port'] = $port?$port:'3306';
		$_config['db'][1]['tablepre'] = $tablepre;
		$_config['admincp']['founder'] = (string)$uid;
		$_config['security']['authkey'] = $authkey;
		$_config['cookie']['cookiepre'] = random(4).'_';
		$_config['memory']['prefix'] = random(6).'_';

		save_config_file(ROOT_PATH.CONFIG, $_config, $default_config);
	    $runqueryerror=0;
		$db = new dbstuff;

		$db->connect($dbhost, $dbuser, $dbpw, $dbname, DBCHARSET);

		if(!VIEW_OFF) {
			show_header();
			show_install();
		}
		for($i=0; $i<5;$i++){
			showjsmessage(lang('begin_establish_data_tables'));
		}
		$sql = file_get_contents($sqlfile);
		$sql = str_replace("\r\n", "\n", $sql);
		runquery($sql);
		for($i=0; $i<5;$i++){
			showjsmessage(lang('table_clear_success'));
		}
		
		runquery($extrasql);
		for($i=0; $i<5;$i++){
			showjsmessage(lang('start_importing_initialized_data'));
		}
		$sql = file_get_contents(ROOT_PATH.'./install/data/install_data.sql');
		$sql = str_replace("\r\n", "\n", $sql);
		runquery($sql);
		for($i=0; $i<5;$i++){
			showjsmessage(lang('start_importing_initialized_data1'));
		}
		
		for($i=0; $i<5;$i++){
			showjsmessage(lang('set_system'));
		}
		$onlineip = $_SERVER['REMOTE_ADDR'];
		$timestamp = time();
		$backupdir = substr(md5($_SERVER['SERVER_ADDR'].$_SERVER['HTTP_USER_AGENT'].substr($timestamp, 0, 4)), 8, 6);
		$ret = false;
		if(is_dir(ROOT_PATH.'data/backup')) {
			$ret = @rename(ROOT_PATH.'data/backup', ROOT_PATH.'data/backup_'.$backupdir);
		}
		if(!$ret) {
			@mkdir(ROOT_PATH.'data/backup_'.$backupdir, 0777);
		}
		if(is_dir(ROOT_PATH.'data/backup_'.$backupdir)) {
			$db->query("REPLACE INTO {$tablepre}setting (skey, svalue) VALUES ('backupdir', '$backupdir')");
		}
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$siteuniqueid = 'DZZOFFICE'.$chars[date('y')%60].$chars[date('n')].$chars[date('j')].$chars[date('G')].$chars[date('i')].$chars[date('s')].substr(md5($onlineip.$timestamp), 0, 4).random(4);
		$db->query("REPLACE INTO {$tablepre}setting (skey, svalue) VALUES ('authkey', '$authkey')");
		$db->query("REPLACE INTO {$tablepre}setting (skey, svalue) VALUES ('siteuniqueid', '$siteuniqueid')");
		$db->query("REPLACE INTO {$tablepre}setting (skey, svalue) VALUES ('adminemail', '$adminemail')");
		$db->query("REPLACE INTO {$tablepre}setting (skey, svalue) VALUES ('backupdir', '".$backupdir."')");
		$db->query("REPLACE INTO {$tablepre}setting (skey, svalue) VALUES ('verhash', '".random(3)."')");
		//创建默认机构
		if($company){
			$db->query("REPLACE INTO {$tablepre}setting (skey, svalue) VALUES ('sitename', '".$company."')");
			$db->query("REPLACE INTO {$tablepre}setting (skey, svalue) VALUES ('bbname', '".$company."')");
			//插入默认机构
			$db->query("INSERT INTO {$tablepre}organization (`orgid`,`orgname`, `forgid`, `fid`, `disp`, `dateline`, `usesize`, `maxspacesize`, `indesk`,`available`,`pathkey`,`syatemon`,`manageon`,`diron`)
 VALUES( 1, '$company', 0, 1, 0, '$timestamp', 0, 0, 0,1,'_1_',1,1,1)");
            //插入默认机构文件夹
            $db->query("INSERT INTO {$tablepre}folder (`fid`,`pfid`, `uid`, `username`, `innav`, `fname`, `perm`, `perm_inherit`, `fsperm`,`disp`,`iconview`,`display`,`dateline`,`gid`,`flag`,`default`,`isdelete`,`deldateline`)
 VALUES( 1, 0, 0, '', 1, '$company',7,7,0,0,4,0,'$timestamp', 1, 'organization','',0,0)");
            //插入默认机构path路径
            $db->query("INSERT INTO {$tablepre}resources_path (`fid`,`path`, `pathkey`) VALUES( 1, 'dzz:gid_1:$company/','_1_')");
			//将管理员加入默认机构
			$db->query("INSERT INTO {$tablepre}organization_user (`orgid`, `uid`,`jobid`, `dateline`) VALUES(1, 1, 0, '$timestamp')");
			
		}
		$db->query("UPDATE {$tablepre}cron SET lastrun='0', nextrun='".($timestamp + 3600)."'");
		for($i=0; $i<5;$i++){
			showjsmessage(lang('set_system1'));
		}
		
		for($i=0; $i<5;$i++){
			showjsmessage(lang('import_division_data'));
		}
		install_districtdata();
		
		for($i=0; $i<5;$i++){
			showjsmessage(lang('import_division_data1'));
		}
		
		$yearmonth = date('Ym_', time());
		loginit($yearmonth.'loginlog');
		loginit($yearmonth.'cplog');
		loginit($yearmonth.'errorlog');

		dir_clear(ROOT_PATH.'./data/template');
		dir_clear(ROOT_PATH.'./data/cache');
		

		foreach($serialize_sql_setting as $k => $v) {
			$v = addslashes(serialize($v));
			$db->query("REPLACE INTO {$tablepre}setting VALUES ('$k', '$v')");
		}
		if($runqueryerror){
			showjsmessage('<span class="red">'.lang('error_quit_msg').'</span>');
			exit();
		};
		showjsmessage(lang('system_data_installation_successful'));
		echo '<script type="text/javascript">function setlaststep() {document.getElementById("laststep").disabled=false;}</script><script type="text/javascript">setTimeout(function(){window.location=\'index.php?step=4\'}, 30000);setlaststep();</script>'."\r\n";
	
		show_footer();
	}
	show_form($form_db_init_items, $error_msg);
	
} elseif($method == 'admin_init') {
	$submit = true;
	$adminemail = 'admin@dzzoffice.com';
	$error_msg = array();
	if(isset($form_admin_init_items) && is_array($form_admin_init_items)) {
		foreach($form_admin_init_items as $key => $items) {
			$$key = getgpc($key, 'p');
			if(!isset($$key) || !is_array($$key)) {
				$submit = false;
				break;
			}
			foreach($items as $k => $v) {
				$tmp = $$key;
				$$k = $tmp[$k];
				if(empty($$k) || !preg_match($v['reg'], $$k)) {
					if(empty($$k) && !$v['required']) {
						continue;
					}
					$submit = false;
					VIEW_OFF or $error_msg[$key][$k] = 1;
				}
			}
		}
	} else {
		$submit = false;
	}

	if($submit && !VIEW_OFF && $_SERVER['REQUEST_METHOD'] == 'POST') {
		if($password != $password2) {
			$error_msg['admininfo']['password2'] = 1;
			$submit = false;
		}
	}

	if($submit) {

		$step = $step + 1;
		if($username && $email && $password) {
			if(strlen($username) > 30 || preg_match("/^$|^c:\\con\\con$|　|[,\"\s\t\<\>&]|^Guest/is", $username)) {
				show_msg('admin_username_invalid', $username, 0);
			} elseif(!strstr($email, '@') || $email != stripslashes($email) || $email != dhtmlspecialchars($email)) {
				show_msg('admin_email_invalid', $email, 0);
			} 
		}else {
			show_msg('admininfo_invalid', '', 0);
		}
		
		$uid =  1 ;

		$onlineip = $_SERVER['REMOTE_ADDR'];
		$timestamp = time();
		$salt=random(6);
		$password = md5(md5($password).$salt);
		$db = new dbstuff;
		include ROOT_PATH.CONFIG;
		$dbhost = $_config['db'][1]['dbhost'];
		$dbname = $_config['db'][1]['dbname'];
		$dbpw = $_config['db'][1]['dbpw'];
		$dbuser = $_config['db'][1]['dbuser'];
		$tablepre = $_config['db'][1]['tablepre'];
		$db->connect($dbhost, $dbuser, $dbpw, $dbname, DBCHARSET);
		$db->query("REPLACE INTO {$tablepre}user (uid, username,nickname, password, adminid, groupid, email, regdate,salt,authstr) VALUES ('$uid', '$username', '','$password', '1', '1', '$email', '".time()."','$salt','');");
        $db->query("update {$tablepre}folder set `uid`=$uid,`username`='$username' where `fid` = 1");
		$db->query("REPLACE INTO {$tablepre}user_status (uid, regip,lastip, lastvisit, lastactivity, lastsendmail, invisible, profileprogress) VALUES ('$uid', '', '','$timestamp', '$timestamp', '0', '0', '0');");
		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}user");
		$totalmembers = $db->result($query, 0);
		$userstats = array('totalmembers' => $totalmembers, 'newsetuser' => $username);
		$ctype = 1;
		$data = addslashes(serialize($userstats));
		$db->query("REPLACE INTO {$tablepre}syscache (cname, ctype, dateline, data) VALUES ('userstats', '$ctype', '".time()."', '$data')");

		header("location: index.php?step=5");
	}
	show_form($form_admin_init_items, $error_msg);
	
} elseif($method == 'ext_info') {
	@touch($lockfile);
	@unlink(ROOT_PATH.'./install/index.php');
	@unlink(ROOT_PATH.'./install/update.php');
	show_header();
	echo '<iframe src="../misc.php?mod=syscache" style="display:none;"></iframe>';
	echo '<h3>'.lang('install_successfully').'</h3>';
	echo '<h4 class="red">'.lang('handwork_del').'"./install/index.php"</h4>';
	echo '<div style="text-align:right;width:80%;padding-top:50px;"><a href="'.$bbserver.'" class="button" ><input type="button" value="'.lang('enter_desktop').'"></a></div>';
	show_footer();
	

} elseif($method == 'install_check') {

	if(file_exists($lockfile)) {
		show_msg('installstate_succ');
	} else {
		show_msg('lock_file_not_touch', $lockfile, 0);
	}

} elseif($method == 'tablepre_check') {

	$dbinfo = getgpc('dbinfo');
	extract($dbinfo);
	if(check_db($dbhost, $dbuser, $dbpw, $dbname, $tablepre)) {
		show_msg('tablepre_not_exists', 0);
	} else {
		show_msg('tablepre_exists', $tablepre, 0);
	}
}