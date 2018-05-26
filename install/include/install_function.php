<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_LEYUN')) {
	exit('Access Denied');
}
function getBucketList($access_id,$access_key){
		$re=array();
		if(!$access_id || !$access_key) return array();
		
		require_once ROOT_PATH.'./core/api/oss_sdk/sdk.class.php';
		$oss = new ALIOSS($access_id,$access_key);
		$response=$oss->list_bucket();
		if(!$response->isOK()){
			return array();
		}
		$bucket=$response->getBody();
		foreach($bucket['ListAllMyBucketsResult']['Buckets']['Bucket'] as $value){
			$re[]=$value['Name'];
		}
		return $re;
	}
function show_msg($error_no, $error_msg = 'ok', $success = 1, $quit = TRUE) {
	
		show_header();
		global $step;
		$title = lang($error_no);
		$comment = lang($error_no.'_comment', false);
		$errormsg = '';
		if($error_msg) {
			if(!empty($error_msg)) {
				foreach ((array)$error_msg as $k => $v) {
					if(is_numeric($k)) {
						$comment .= "<li><em class=\"red\">".lang($v)."</em></li>";
					}
				}
			}
		}
		$back = lang('to_back');
		echo <<<EOT
		<div class="content red">
			
				<div class="header" ><img src="images/logo.png"></div>
			
		
			<h4>$title</h4>
			<ul >$comment</ul>
		
			<div style="text-align:right;width:80%;padding-top:50px;"><a href="#" class="button" onclick="history.back();return false"><input type="button" value="$back"></a></div>
		</div>
EOT;
 show_footer($quit);
}

function check_db($dbhost, $dbuser, $dbpw, $dbname, $tablepre) {
	if(!function_exists('mysql_connect') && !function_exists('mysqli_connect')) {
		show_msg('undefine_func', 'mysql_connect | mysqli_connect', 0);
	}
	$mysqlmode = function_exists('mysqli_connect') ? 'mysqli' : 'mysql';
	if($mysqlmode=='mysqli'){
		//兼容支持域名直接带有端口的情况
		if(strpos($dbhost,':')!==false){
			list($dbhost,$port)=explode(':',$dbhost);
			
		}elseif(strpos($dbhost,'.sock')!==false){//地址直接是socket地址
			$unix_socket=$dbhost;
			$dbhost='localhost';
		}
		if(empty($port)) $port='3306';
		$link =  new mysqli($dbhost, $dbuser, $dbpw, '', $port, $unix_socket);
		$errno =  $link->connect_errno;
		$error =  $link->connect_error;
	}else{
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
	} else {
		if($query = (($mysqlmode == 'mysql') ? @mysql_query("SHOW databases") : $link->query("SHOW databases"))) {
			if(!$query) {
				return false;
			}
			while($row = (($mysqlmode == 'mysql') ? mysql_fetch_row($query) : $query->fetch_row())) {
				if($dbname==$row[0]) {
					return false;
				}
			}
		}
	}
	return true;
}

function dirfile_check(&$dirfile_items) {
	foreach($dirfile_items as $key => $item) {
		$item_path = $item['path'];
		if($item['type'] == 'dir') {
			if(!dir_writeable(ROOT_PATH.$item_path)) {
				if(is_dir(ROOT_PATH.$item_path)) {
					$dirfile_items[$key]['status'] = 0;
					$dirfile_items[$key]['current'] = '+r';
				} else {
					$dirfile_items[$key]['status'] = -1;
					$dirfile_items[$key]['current'] = 'nodir';
				}
			} else {
				$dirfile_items[$key]['status'] = 1;
				$dirfile_items[$key]['current'] = '+r+w';
			}
		} else {
			if(file_exists(ROOT_PATH.$item_path)) {
				if(is_writable(ROOT_PATH.$item_path)) {
					$dirfile_items[$key]['status'] = 1;
					$dirfile_items[$key]['current'] = '+r+w';
				} else {
					$dirfile_items[$key]['status'] = 0;
					$dirfile_items[$key]['current'] = '+r';
				}
			} else {
				if(dir_writeable(dirname(ROOT_PATH.$item_path))) {
					$dirfile_items[$key]['status'] = 1;
					$dirfile_items[$key]['current'] = '+r+w';
				} else {
					$dirfile_items[$key]['status'] = -1;
					$dirfile_items[$key]['current'] = 'nofile';
				}
			}
		}
	}
}

function env_check(&$env_items) {
	foreach($env_items as $key => $item) {
		if($key == 'php') {
			$env_items[$key]['current'] = PHP_VERSION;
		} elseif($key == 'attachmentupload') {
			$env_items[$key]['current'] = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknow';
		} elseif($key == 'allow_url_fopen') {
			$env_items[$key]['current'] = @ini_get('allow_url_fopen') ? ini_get('allow_url_fopen') : 'unknow';
		} elseif($key == 'gdversion') {
			$tmp = function_exists('gd_info') ? gd_info() : array();
			$env_items[$key]['current'] = empty($tmp['GD Version']) ? 'noext' : $tmp['GD Version'];
			unset($tmp);
		} elseif($key == 'diskspace') {
			if(function_exists('disk_free_space')) {
				$env_items[$key]['current'] = floor(disk_free_space(ROOT_PATH) / (1024*1024)).'M';
			} else {
				$env_items[$key]['current'] = 'unknow';
			}
		} elseif(isset($item['c'])) {
			$env_items[$key]['current'] = constant($item['c']);
		}

		$env_items[$key]['status'] = 1;
		if($item['r'] != 'notset' && strcmp($env_items[$key]['current'], $item['r']) < 0) {
			$env_items[$key]['status'] = 0;
		}
	}
}
function function_check(&$func_items) {
	foreach($func_items as $item) {
		function_exists($item) or show_msg('undefine_func', $item, 0);
	}
}

function show_env_result(&$env_items, &$func_items, &$filesock_items) {

	$env_str = $file_str = $func_str = '';
	$error_code = 0;
	foreach($env_items as $key => $item) {
		if($key == 'php' && strcmp($item['current'], $item['r']) < 0) {
			show_msg('php_version_too_low', $item['current'], 0);
		}
		$status = 1;
		if($item['r'] != 'notset') {
			if(intval($item['current']) && intval($item['r'])) {
				if(intval($item['current']) < intval($item['r'])) {
					$status = 0;
					$error_code = ENV_CHECK_ERROR;
				}
			} else {
				if(strcmp($item['current'], $item['r']) < 0) {
					$status = 0;
					$error_code = ENV_CHECK_ERROR;
				}
			}
		}
		if(VIEW_OFF) {
			$env_str .= "\t\t<runCondition name=\"$key\" status=\"$status\" Require=\"$item[r]\" Best=\"$item[b]\" Current=\"$item[current]\"/>\n";
		} else {
			$env_str .= "<tr>\n";
			$env_str .= "<td>".lang($key)."</td>\n";
			$env_str .= "<td class=\"padleft\">".lang($item['r'])."</td>\n";
			$env_str .= "<td class=\"padleft\">".lang($item['b'])."</td>\n";
			$env_str .= ($status ? "<td class=\"w pdleft1\">" : "<td class=\"nw pdleft1\">").$item['current']."</td>\n";
			$env_str .= "</tr>\n";
		}
	}
		show_header();
		if($env_str){
			echo "<h2 class=\"title\">".lang('env_check')."</h2>\n";
			echo "<table class=\"tb\" style=\"margin:20px 0;\">\n";
			echo "<tr>\n";
			echo "\t<th>".lang('project')."</th>\n";
			echo "\t<th class=\"padleft\">".lang('dzzoffice_required')."</th>\n";
			echo "\t<th class=\"padleft\">".lang('dzzoffice_best')."</th>\n";
			echo "\t<th class=\"padleft\">".lang('curr_server')."</th>\n";
			echo "</tr>\n";
			echo $env_str;
			echo "</table>\n";
		}

		foreach($func_items as $item) {
			$status = function_exists($item);
			$func_str .= "<tr>\n";
			$func_str .= "<td>$item()</td>\n";
			if($status) {
				$func_str .= "<td class=\"w pdleft1\">".lang('supportted')."</td>\n";
				$func_str .= "<td class=\"padleft\">".lang('none')."</td>\n";
			} else {
				$error_code = ENV_CHECK_ERROR;
				$func_str .= "<td class=\"nw pdleft1\">".lang('unsupportted')."</td>\n";
				$func_str .= "<td><font color=\"red\">".lang('advice_'.$item)."</font></td>\n";
			}
		}
		$func_strextra = '';
		$filesock_disabled = 0;
		foreach($filesock_items as $item) {
			$status = function_exists($item);
			$func_strextra .= "<tr>\n";
			$func_strextra .= "<td>$item()</td>\n";
			if($status) {
				$func_strextra .= "<td class=\"w pdleft1\">".lang('supportted')."</td>\n";
				$func_strextra .= "<td class=\"padleft\">".lang('none')."</td>\n";
				break;
			} else {
				$filesock_disabled++;
				$func_strextra .= "<td class=\"nw pdleft1\">".lang('unsupportted')."</td>\n";
				$func_strextra .= "<td><font color=\"red\">".lang('advice_'.$item)."</font></td>\n";
			}
		}
		if($filesock_disabled == count($filesock_items)) {
			$error_code = ENV_CHECK_ERROR;
		}
		if($func_str || $func_strextra){
			echo "<h2 class=\"title\">".lang('func_depend')."</h2>\n";
			echo "<table class=\"tb\" style=\"margin:20px 0;width:95%;\">\n";
			echo "<tr>\n";
			echo "\t<th>".lang('func_name')."</th>\n";
			echo "\t<th class=\"padleft\">".lang('check_result')."</th>\n";
			
			echo "</tr>\n";
			echo $func_str.$func_strextra;
			echo "</table>\n";
		}
	show_next_step(2,$error_code);
	show_footer();
}
function show_dirfile_result(&$dirfile_items) {

	$file_str = '';
	$dir_str = '';
	$error_code = 0;

	foreach($dirfile_items as $key => $item) {
		$tagname = $item['type'] == 'file' ? 'file' : 'dir';
		$variable = $item['type'].'_str';
		  if(empty($$variable)) $$variable='';
			$$variable .= "<tr>\n";
			$$variable .= "<td>$item[path]</td><td class=\"w pdleft1\">".lang('writeable')."</td>\n";
			if($item['status'] == 1) {
				$$variable .= "<td class=\"w pdleft1\">".lang('writeable')."</td>\n";
			} elseif($item['status'] == -1) {
				$error_code = ENV_CHECK_ERROR;
				$$variable .= "<td class=\"nw pdleft1\">".lang('nodir')."</td>\n";
			} else {
				$error_code = ENV_CHECK_ERROR;
				$$variable .= "<td class=\"nw pdleft1\">".lang('unwriteable')."</td>\n";
			}
			$$variable .= "</tr>\n";
		
	}
		show_header();
		if($file_str || $dir_str){
			echo "<h3 >".lang('priv_check')."</h3>\n";
			echo "<table class=\"tb\" style=\"margin:20px 0;width:95%;\">\n";
			echo "\t<tr>\n";
			echo "\t<th>".lang('step1_file')."</th>\n";
			echo "\t<th class=\"padleft\">".lang('step1_need_status')."</th>\n";
			echo "\t<th class=\"padleft\">".lang('step1_status')."</th>\n";
			echo "</tr>\n";
			echo $file_str;
			echo $dir_str;
			echo "</table>\n";
		}
	show_next_step(3,$error_code);
	show_footer();
}
function show_next_step($step, $error_code) {
	global $uchidden;
	echo "<form action=\"index.php\" method=\"post\">\n";
	echo "<input type=\"hidden\" name=\"step\" value=\"$step\" />";
	if($error_code == 0) {
		$nextstep = "<a href=\"#\" class=\"button\"><input type=\"button\" onclick=\"history.back();return false;\" value=\"".lang('old_step')."\"></a><a href=\"#\" class=\"button\"><input type=\"submit\" value=\"".lang('new_step')."\"></a>\n";
	} else {
		$nextstep = "<a href=\"#\" class=\"button\"><input type=\"button\" disabled=\"disabled\" value=\"".lang('not_continue')."\"></a>\n";
	}
	echo '<div style="width:85%;float:left;text-align:right;padding:20px;">'.$nextstep.'</div>';
	echo "</form>\n";
}
function show_form(&$form_items, $error_msg) {
	global $step;

	if(empty($form_items) || !is_array($form_items)) {
		return;
	}
	show_header();
	show_setting('start');
	show_setting('hidden', 'step', $step);
	$is_first = 1;
	echo '<div id="form_items_'.$step.'"><br />';
	foreach($form_items as $key => $items) {
		global ${'error_'.$key};
		if($is_first == 0) {
			echo '</table>';
		}
		if(!${'error_'.$key}) {
			show_tips('tips_'.$key);
		} else {
			show_error('tips_admin_config', ${'error_'.$key});
		}

		echo '<table class="tb2">';
		foreach($items as $k => $v) {
			$value = '';
			if(!empty($error_msg)) {
				$value = isset($_POST[$key][$k]) ? $_POST[$key][$k] : '';
			}
			if(empty($value)) {
				if(isset($v['value']) && is_array($v['value'])) {
					if($v['value']['type'] == 'constant') {
						$value = defined($v['value']['var']) ? constant($v['value']['var']) : $v['value']['var'];
					}elseif($v['value']['type'] == 'array') {
						$value = $v['value']['var'];
					} else {
						$value = !empty($GLOBALS[$v['value']['var']])?$GLOBALS[$v['value']['var']]:'';
					}
				} else {
					$value = '';
				}
			}
			show_setting($k, $key.'['.$k.']', $value, $v['type'], isset($error_msg[$key][$k]) ? $key.'_'.$k.'_invalid' : '');
		}
		if($is_first) {
			$is_first = 0;
		}
	}
	echo '</table>';
	echo '</div>';
	echo '<table class="tb2">';
	show_setting('', 'submitname', 'new_step', ($step == 2 ? 'submit|oldbtn' : 'submit' ));
	show_setting('end');
	show_footer();
}

function show_license() {
	global $self,  $step;
	$next = $step + 1;
	show_header();
	$title = lang('step_env_check_title');
	$version='DzzOffice'.CORE_VERSION.' &nbsp;&nbsp;   '.INSTALL_LANG.' '.CORE_RELEASE;
	$release = CORE_RELEASE;
	$install_lang = lang(INSTALL_LANG);
	echo <<<EOT
		<style>
		 body{background:#2d3137}
		</style>
		<table width="95%" height="100%">
		<tr><td valign="middle" align="center">
				<h1><img src="images/logo.png"></h1>
				<div class="spacer"></div>
				<h4 style="font-size:20px;">$install_lang</h4>
				<h4>$version</h4>
				<div class="spacer"></div>
			<div ><a href="?step=1" class="button_start"><span>$title</span></a></div>
			<div class="spacer"></div>
		</td></tr>
		</table>
	
EOT;
show_footer();
}
if(!function_exists('file_put_contents')) {
	function file_put_contents($filename, $s) {
		$fp = @fopen($filename, 'w');
		@fwrite($fp, $s);
		@fclose($fp);
		return TRUE;
	}
}

function createtable($sql, $dbver) {

	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP', 'MEMORY','INNODB')) ? $type : 'MyISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
	(" ENGINE=$type DEFAULT CHARSET=".DBCHARSET);
}

function dir_writeable($dir) {
	$writeable = 0;
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if(is_dir($dir)) {
		if($fp = @fopen("$dir/test.txt", 'w')) {
			@fclose($fp);
			@unlink("$dir/test.txt");
			$writeable = 1;
		} else {
			$writeable = 0;
		}
	}
	return $writeable;
}
function dir_clear($dir) {
	global $lang;
	showjsmessage($lang['clear_dir'].' '.str_replace(ROOT_PATH, '', $dir));
	if($directory = @dir($dir)) {
		while($entry = $directory->read()) {
			$filename = $dir.'/'.$entry;
			if(is_file($filename)) {
				@unlink($filename);
			}
		}
		$directory->close();
		@touch($dir.'/index.htm');
	}
}

function show_header() {
	define('SHOW_HEADER', TRUE);
	global $step;
	$version = CORE_VERSION;
	$release = CORE_RELEASE;
	$install_lang = lang(INSTALL_LANG);
	$title = lang('title_install');
	$charset = CHARSET;
	echo <<<EOT
<!DOCTYPE>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=$charset" />
<title>$title</title>
<link rel="stylesheet" href="images/style.css" type="text/css" media="all" />
<script type="text/javascript">
	function $(id) {
		return document.getElementById(id);
	}

	function showmessage(message) {
		document.getElementById('progress').innerHTML = message;
	}
</script>
<meta content="Leyun internet Technology(Shanghai)Co.,Ltd" name="Copyright" />
</head>
<div class="container step_$step">

EOT;
}
function show_footer($quit = true) {
	echo <<<EOT
	</div>
</div>
</body>
</html>
EOT;
	$quit && exit();
}

function loginit($logfile) {
	global $lang;
	showjsmessage($lang['init_log'].' '.$logfile);
	if($fp = @fopen('./forumdata/logs/'.$logfile.'.php', 'w')) {
		fwrite($fp, '<'.'?PHP exit(); ?'.">\n");
		fclose($fp);
	}
}
function showjsmessage($message) {
	if(VIEW_OFF) return;
	echo '<script type="text/javascript">showmessage(\''.addslashes($message).' \');</script>'."\r\n";
	flush();
	ob_flush();
}
function random($length) {
	$hash = '';
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$max = strlen($chars) - 1;
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	for($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}
function redirect($url) {

	echo "<script>".
	"function redirect() {window.location.replace('$url');}\n".
	"setTimeout('redirect();', 0);\n".
	"</script>";
	exit();

}

function get_onlineip() {
	$onlineip = '';
	if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$onlineip = getenv('HTTP_CLIENT_IP');
	} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
		$onlineip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$onlineip = getenv('REMOTE_ADDR');
	} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$onlineip = $_SERVER['REMOTE_ADDR'];
	}
	return $onlineip;
}
function timezone_set($timeoffset = 8) {
	if(function_exists('date_default_timezone_set')) {
		@date_default_timezone_set('Etc/GMT'.($timeoffset > 0 ? '-' : '+').(abs($timeoffset)));
	}
}
function save_config_file($filename, $config, $default) {

	$config = setdefault($config, $default);
	$date = gmdate("Y-m-d H:i:s", time() + 3600 * 8);
	$content = <<<EOT
<?php


\$_config = array();

EOT;
	$content .= getvars(array('_config' => $config));
	$content .= "\r\n// ".str_pad('  THE END  ', 50, '-', STR_PAD_BOTH)."\r\n return \$_config;";
	file_put_contents($filename, $content);
}

function setdefault($var, $default) {
	foreach ($default as $k => $v) {
		if(!isset($var[$k])) {
			$var[$k] = $default[$k];
		} elseif(is_array($v)) {
			$var[$k] = setdefault($var[$k], $default[$k]);
		}
	}
	return $var;
}

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;
	$key = md5($key ? $key : UC_KEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);
	$result = '';
	$box = range(0, 255);
	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}
	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}
	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

function generate_key() {
	$random = random(32);
	$info = md5($_SERVER['SERVER_SOFTWARE'].$_SERVER['SERVER_NAME'].$_SERVER['SERVER_ADDR'].$_SERVER['SERVER_PORT'].$_SERVER['HTTP_USER_AGENT'].time());
	$return = '';
	for($i=0; $i<64; $i++) {
		$p = intval($i/2);
		$return[$i] = $i % 2 ? $random[$p] : $info[$p];
	}
	return implode('', $return);
}

function show_install() {
	if(VIEW_OFF) return;
?>
<script type="text/javascript">
var timer=0;
function showmessage(message) {
	++timer;
	window.setTimeout(function(){
    	document.getElementById('notice').innerHTML = message ;
        var width=(parseInt(document.getElementById('progress').style.width)+1);
        if(width>100) width=100;
		document.getElementById('progress').style.width = width+'%';
    },50*timer);
}
function initinput() {
	window.location='index.php?step=4';
}
</script>
		<h3><?php echo lang('db_installing_title');?></h3>
		<div class="pContainer" >
        	<div id="progress" class="progress" style="width:0%"></div>
            <span id="notice"></span>
         </div>
        	
		<div style="width:80%;text-align:right">
			<a class="button" href="#"><input type="button" name="submit" value="<?php echo lang('new_step');?>" disabled="disabled" id="laststep" onclick="initinput()"></a>
		</div>
<?php
}

function runquery($sql) {
	global $lang, $tablepre, $db;

	if(!isset($sql) || empty($sql)) return;

	$sql = str_replace("\r", "\n", str_replace(' '.ORIG_TABLEPRE, ' '.$tablepre, $sql));
	$sql = str_replace("\r", "\n", str_replace(' `'.ORIG_TABLEPRE, ' `'.$tablepre, $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$ret[$num] = '';
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0].$query[1] == '--') ? '' : $query;
		}
		$num++;
	}
	unset($sql);
	foreach($ret as $query) {
		$query = trim($query);
		if($query) {

			if(substr($query, 0, 12) == 'CREATE TABLE') {
				$name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
				$db->query(createtable($query, $db->version()));
				showjsmessage(lang('create_table').' '.$name.' ... '.lang('succeed'));
			} else {
				$db->query($query);
			}

		}
	}
}

function charcovert($string) {
	if(!get_magic_quotes_gpc()) {
		$string = str_replace('\'', '\\\'', $string);
	} else {
		$string = str_replace('\"', '"', $string);
	}
	return $string;
}

function insertconfig($s, $find, $replace) {
	if(preg_match($find, $s)) {
		$s = preg_replace($find, $replace, $s);
	} else {
		$s .= "\r\n".$replace;
	}
	return $s;
}

function getgpc($k, $t='GP') {
	$t = strtoupper($t);
	switch($t) {
		case 'GP' : isset($_POST[$k]) ? $var = &$_POST : $var = &$_GET; break;
		case 'G': $var = &$_GET; break;
		case 'P': $var = &$_POST; break;
		case 'C': $var = &$_COOKIE; break;
		case 'R': $var = &$_REQUEST; break;
	}
	return isset($var[$k]) ? $var[$k] : null;
}

function var_to_hidden($k, $v) {
	return "<input type=\"hidden\" name=\"$k\" value=\"$v\" />\n";
}

function fsocketopen($hostname, $port = 80, &$errno, &$errstr, $timeout = 15) {
	$fp = '';
	if(function_exists('fsockopen')) {
		$fp = @fsockopen($hostname, $port, $errno, $errstr, $timeout);
	} elseif(function_exists('pfsockopen')) {
		$fp = @pfsockopen($hostname, $port, $errno, $errstr, $timeout);
	} elseif(function_exists('stream_socket_client')) {
		$fp = @stream_socket_client($hostname.':'.$port, $errno, $errstr, $timeout);
	}
	return $fp;
}

function dfopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE, $encodetype  = 'URLENCODE', $allowcurl = TRUE) {
	$return = '';
	$matches = parse_url($url);
	$scheme = $matches['scheme'];
	$host = $matches['host'];
	$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
	$port = !empty($matches['port']) ? $matches['port'] : 80;

	if(function_exists('curl_init') && $allowcurl) {
		$ch = curl_init();
		$ip && curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: ".$host));
		curl_setopt($ch, CURLOPT_URL, $scheme.'://'.($ip ? $ip : $host).':'.$port.$path);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if($post) {
			curl_setopt($ch, CURLOPT_POST, 1);
			if($encodetype == 'URLENCODE') {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			} else {
				parse_str($post, $postarray);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
			}
		}
		if($cookie) {
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		$status = curl_getinfo($ch);
		$errno = curl_errno($ch);
		curl_close($ch);
		if($errno || $status['http_code'] != 200) {
			return;
		} else {
			return !$limit ? $data : substr($data, 0, $limit);
		}
	}

	if($post) {
		$out = "POST $path HTTP/1.0\r\n";
		$header = "Accept: */*\r\n";
		$header .= "Accept-Language: zh-cn\r\n";
		$boundary = $encodetype == 'URLENCODE' ? '' : '; boundary='.trim(substr(trim($post), 2, strpos(trim($post), "\n") - 2));
		$header .= $encodetype == 'URLENCODE' ? "Content-Type: application/x-www-form-urlencoded\r\n" : "Content-Type: multipart/form-data$boundary\r\n";
		$header .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$header .= "Host: $host:$port\r\n";
		$header .= 'Content-Length: '.strlen($post)."\r\n";
		$header .= "Connection: Close\r\n";
		$header .= "Cache-Control: no-cache\r\n";
		$header .= "Cookie: $cookie\r\n\r\n";
		$out .= $header.$post;
	} else {
		$out = "GET $path HTTP/1.0\r\n";
		$header = "Accept: */*\r\n";
		$header .= "Accept-Language: zh-cn\r\n";
		$header .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$header .= "Host: $host:$port\r\n";
		$header .= "Connection: Close\r\n";
		$header .= "Cookie: $cookie\r\n\r\n";
		$out .= $header;
	}

	$fpflag = 0;
	if(!$fp = @fsocketopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout)) {
		$context = array(
			'http' => array(
				'method' => $post ? 'POST' : 'GET',
				'header' => $header,
				'content' => $post,
				'timeout' => $timeout,
			),
		);
		$context = stream_context_create($context);
		$fp = @fopen($scheme.'://'.($ip ? $ip : $host).':'.$port.$path, 'b', false, $context);
		$fpflag = 1;
	}

	if(!$fp) {
		return '';
	} else {
		stream_set_blocking($fp, $block);
		stream_set_timeout($fp, $timeout);
		@fwrite($fp, $out);
		$status = stream_get_meta_data($fp);
		if(!$status['timed_out']) {
			while (!feof($fp) && !$fpflag) {
				if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
					break;
				}
			}

			$stop = false;
			while(!feof($fp) && !$stop) {
				$data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
				$return .= $data;
				if($limit) {
					$limit -= strlen($data);
					$stop = $limit <= 0;
				}
			}
		}
		@fclose($fp);
		return $return;
	}
}

function check_env() {

	global $lang, $attachdir;

	$errors = array('quit' => false);
	$quit = false;

	if(!function_exists('mysql_connect') && !function_exists('mysqli_connect')) {
		$errors[] = 'mysql_unsupport';
		$quit = true;
	}

	if(PHP_VERSION < '4.3') {
		$errors[] = 'php_version_430';
		$quit = true;
	}

	if(!file_exists(ROOT_PATH.'./config/config.php')) {
		$errors[] = lang('config_nonexistence');
		$quit = true;
	} elseif(!is_writeable(ROOT_PATH.'./config/config.php')) {
		$errors[] = lang('config_unwriteable');
		$quit = true;
	}

	$checkdirarray = array(
		'attach' => $attachdir,
	);

	foreach($checkdirarray as $key => $dir) {
		if(!dir_writeable(ROOT_PATH.$dir)) {
			$langkey = $key.'_unwriteable';
			$errors[] = $key.'_unwriteable';
			if(!in_array($key, array('ftemplate'))) {
				$quit = TRUE;
			}
		}
	}
	$errors['quit'] = $quit;
	return $errors;
}

function show_error($type, $errors = '', $quit = false) {

	global $lang, $step,$runqueryerror;
	$title = lang($type);
	$comment = lang($type.'_comment', false);
	$errormsg = '';
	if($errors) {
		if(!empty($errors)) {
			foreach ((array)$errors as $k => $v) {
				if(is_numeric($k)) {
					$comment .= "<li><em class=\"red\">".lang($v)."</em></li>";
				}
			}
		}
	}
	if($step > 0) {
		echo "<div class=\"desc\"><b>$title</b><ul>$comment</ul>";
	} else {
		echo "</div><div class=\"main\" style=\"margin-top: -123px;\"><b>$title</b><ul style=\"line-height: 200%; margin-left: 30px;\">$comment</ul>";
	}

	if($quit) {
		echo '<br /><span class="red">'.$lang['error_quit_msg'].'</span><br /><br /><br /><br /><br /><br />';
	}

	echo '</div>';
	$runqueryerror++;
	show_footer($quit);
}

function show_tips($tip, $title = '', $comment = '', $style = 1) {
	global $lang;
	$title = empty($title) ? lang($tip) : $title;
	$comment = empty($comment) ? lang($tip.'_comment', FALSE) : $comment;
	if($style) {
		echo "<h3>$title</h3>";
	}
	$comment && print($comment);
	echo "";
}

function show_setting($setname, $varname = '', $value = '', $type = 'text|password|checkbox', $error = '') {
	if($setname == 'start') {
		echo "<form method=\"post\" action=\"index.php\">\n";
		return;
	} elseif($setname == 'end') {
		echo "\n</table>\n</form>\n";
		return;
	} elseif($setname == 'hidden') {
		echo "<input type=\"hidden\" name=\"$varname\" value=\"$value\">\n";
		return;
	}
	if(strpos($type, 'submit') !== FALSE) {
		echo '<div style="width:80%;text-align:right;padding-top:50px;">';
		if(strpos($type, 'oldbtn') !== FALSE) {
			echo "<a href=\"#\" class=\"button\"><input type=\"button\" name=\"oldbtn\" value=\"".lang('old_step')."\"  onclick=\"history.back();\"></a>\n";
		}
		$value = empty($value) ? 'next_step' : $value;
		echo "<a href=\"#\" class=\"button\"><input type=\"submit\" name=\"$varname\" value=\"".lang($value)."\" class=\"btn\"></a>\n";
		echo '</div>';
		return true;
	}

	echo "\n".'<tr><th class="tbopt'.($error ? ' red' : '').'" align="left">&nbsp;'.(empty($setname) ? '' : lang($setname).':')."</th>\n<td>";
	if($type == 'text' || $type == 'password') {
		$value = dhtmlspecialchars($value);
		echo "<input type=\"$type\" name=\"$varname\" value=\"$value\" size=\"35\" class=\"txt\">";
	
	} elseif($type == 'checkbox') {
		if(!is_array($varname) && !is_array($value)) {
			echo "<input id=\"ali_available\" type=\"hidden\" name=\"$varname\" value=\"".($value?'1':'0')."\">";
			echo "<label onclick=\"if($('ali_available').value!=1){ $('ali_available').value='1';$('ali_available_img').className='seled'}else{ $('ali_available').value='0';$('ali_available_img').className='sel';}\"><span id=\"ali_available_img\" class=\"".($value?'seled':'sel')."\" ></span>&nbsp;".lang($setname.'_check_label')."</label>\n";
		}
	}elseif($type=='select'){
		echo "<select name=\"$varname\" >";
		foreach($value as $key => $val){
			echo "<option value=\"$key\">$val</option>";
		}
		echo "</select>";
		
	} else {
		echo $value;
	}

	echo "</td>\n<td>";
	if($error) {
		$comment = '<span class="red">'.(is_string($error) ? lang($error) : lang($setname.'_error')).'</span>';
	} else {
		$comment = lang($setname.'_comment', false);
	}
	echo "$comment</td>\n</tr>\n";
	
	return true;
}

function show_step($step) {

	global $method;

	$laststep = 4;
	$title = lang('step_'.$method.'_title');
	$comment = lang('step_'.$method.'_desc');
	$step_title_1 = lang('step_title_1');
	$step_title_2 = lang('step_title_2');
	$step_title_3 = lang('step_title_3');
	$step_title_4 = lang('step_title_4');

	$stepclass = array();
	for($i = 1; $i <= $laststep; $i++) {
		$stepclass[$i] = $i == $step ? 'current' : ($i < $step ? '' : 'unactivated');
	}
	$stepclass[$laststep] .= ' last';

	echo <<<EOT
	<div class="setup step{$step}">
		<h2>$title</h2>
		<p>$comment</p>
	</div>
	<div class="stepstat">
		<ul>
			<li class="$stepclass[1]">$step_title_1</li>
			<li class="$stepclass[2]">$step_title_2</li>
			<li class="$stepclass[3]">$step_title_3</li>
			<li class="$stepclass[4]">$step_title_4</li>
		</ul>
		<div class="stepstatbg stepstat1"></div>
	</div>
</div>
<div class="main">
EOT;

}

function lang($lang_key, $force = true) {
	return isset($GLOBALS['lang'][$lang_key]) ? $GLOBALS['lang'][$lang_key] : ($force ? $lang_key : '');
}


function _generate_key() {
	$random = random(32);
	$info = md5($_SERVER['SERVER_SOFTWARE'].$_SERVER['SERVER_NAME'].$_SERVER['SERVER_ADDR'].$_SERVER['SERVER_PORT'].$_SERVER['HTTP_USER_AGENT'].time());
	$return = array();
	for($i=0; $i<32; $i++) {
		$return[$i] = $random[$i].$info[$i];
	}
	return implode('', $return);
}

function install_districtdata() {
	global $_G, $db, $tablepre;
	showjsmessage(lang('install_test_data')." ... ".lang('succeed'));

	$sqlfile = ROOT_PATH.'./install/data/common_district_{#id}.sql';
	for($i = 1; $i < 4; $i++) {
		$sqlfileid = str_replace('{#id}', $i, $sqlfile);
		if(file_exists($sqlfileid)) {
			$sql = file_get_contents($sqlfileid);
			$sql = str_replace("\r\n", "\n", $sql);
			runquery($sql);
		}
	}
}

function getvars($data, $type = 'VAR') {
	$evaluate = '';
	foreach($data as $key => $val) {
		if(!preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/", $key)) {
			continue;
		}
		if(is_array($val)) {
			$evaluate .= buildarray($val, 0, "\${$key}")."\r\n";
		} else {
			$val = addcslashes($val, '\'\\');
			$evaluate .= $type == 'VAR' ? "\$$key = '$val';\n" : "define('".strtoupper($key)."', '$val');\n";
		}
	}
	return $evaluate;
}

function buildarray($array, $level = 0, $pre = '$_config') {
	static $ks;
	if($level == 0) {
		$ks = array();
		$return = '';
	}

	foreach ($array as $key => $val) {
		if($level == 0) {
			$newline = str_pad('  CONFIG '.strtoupper($key).'  ', 70, '-', STR_PAD_BOTH);
			$return .= "\r\n// $newline //\r\n";
			if($key == 'admincp') {
				$newline = str_pad(' Founders: $_config[\'admincp\'][\'founder\'] = \'1,2,3\'; ', 70, '-', STR_PAD_BOTH);
				$return .= "// $newline //\r\n";
			}
		}

		$ks[$level] = $ks[$level - 1]."['$key']";
		if(is_array($val)) {
			$ks[$level] = $ks[$level - 1]."['$key']";
			$return .= buildarray($val, $level + 1, $pre);
		} else {
			$val =  is_string($val) || strlen($val) > 12 || !preg_match("/^\-?[1-9]\d*$/", $val) ? '\''.addcslashes($val, '\'\\').'\'' : $val;
			$return .= $pre.$ks[$level - 1]."['$key']"." = $val;\r\n";
		}
	}
	return $return;
}

function dimplode($array) {
	if(!empty($array)) {
		return "'".implode("','", is_array($array) ? $array : array($array))."'";
	} else {
		return '';
	}
}
function implode_field_value($array, $glue = ',') {
	$sql = $comma = '';
	foreach ($array as $k => $v) {
		$sql .= $comma."`$k`='$v'";
		$comma = $glue;
	}
	return $sql;
}

function daddslashes($string, $force = 1) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = daddslashes($val, $force);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}
function dstripslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dstripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}
function dmkdir($dir, $mode = 0777){
	if(!is_dir($dir)) {
		dmkdir(dirname($dir), $mode);
		@mkdir($dir, $mode);
		@touch($dir.'/index.htm'); @chmod($dir.'/index.htm', 0777);
	}
	return true;
}
function dhtmlspecialchars($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dhtmlspecialchars($val);
		}
	} else {
		$string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
		if(strpos($string, '&amp;#') !== false) {
			$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
		}
	}
	return $string;
}
