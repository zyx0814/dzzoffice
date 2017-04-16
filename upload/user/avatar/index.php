<?php

/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

error_reporting(E_ERROR);
set_magic_quotes_runtime(0);
		
define('IN_DZZ', TRUE);
define('DZZ_ROOT', substr(dirname(__FILE__), 0, -11));
define('UC_PATH',dirname(__FILE__).'/');
$_SERVER['PHP_SELF'] = htmlspecialchars(_get_script_url());
define('UC_API', strtolower(($_SERVER['HTTPS'] == 'on' ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'))));
define('UC_DATADIR', DZZ_ROOT.'./data/');
define('UC_DATAURL', substr(UC_API,0,-11).'data');
define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
require_once UC_PATH.'./fun.php';
unset($GLOBALS, $_ENV, $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS);

$_GET		= daddslashes($_GET, 1, TRUE);
$_POST		= daddslashes($_POST, 1, TRUE);
$_COOKIE	= daddslashes($_COOKIE, 1, TRUE);
$_SERVER	= daddslashes($_SERVER);
$_FILES		= daddslashes($_FILES);
$_REQUEST	= daddslashes($_REQUEST, 1, TRUE);
$a = getgpc('a');

if($a=='uploadavatar'){
	$data=onuploadavatar();
	
	echo is_array($data) ? XMLserialize($data, 1) : $data;
	exit;
}elseif($a=='rectavatar'){
	$data=onrectavatar();
	echo is_array($data) ? XMLserialize($data, 1) : $data;
	exit;
}
function _get_script_url() {
	$phpself='';
	$scriptName = basename($_SERVER['SCRIPT_FILENAME']);
	if(basename($_SERVER['SCRIPT_NAME']) === $scriptName) {
		$phpself = $_SERVER['SCRIPT_NAME'];
	} else if(basename($_SERVER['PHP_SELF']) === $scriptName) {
		$phpself = $_SERVER['PHP_SELF'];
	} else if(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName) {
		$phpself = $_SERVER['ORIG_SCRIPT_NAME'];
	} else if(($pos = strpos($_SERVER['PHP_SELF'],'/'.$scriptName)) !== false) {
		$phpself = substr($_SERVER['SCRIPT_NAME'],0,$pos).'/'.$scriptName;
	} else if(isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'],$_SERVER['DOCUMENT_ROOT']) === 0) {
		$phpself = str_replace('\\','/',str_replace($_SERVER['DOCUMENT_ROOT'],'',$_SERVER['SCRIPT_FILENAME']));
		$phpself[0] != '/' && $phpself = '/'.$phpself;
	} 
	return $phpself;
}
?>
