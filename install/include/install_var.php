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

define('SOFT_NAME', 'DzzOffice');

define('INSTALL_LANG', 'SC_UTF8');

define('CONFIG', './config/config.php');

$sqlfile = ROOT_PATH.'./install/data/install.sql';
$lockfile = ROOT_PATH.'./data/install.lock';

@include ROOT_PATH.CONFIG;

define('CHARSET', 'utf-8');
define('DBCHARSET', 'utf8');

define('ORIG_TABLEPRE', 'dzz_');

define('METHOD_UNDEFINED', 255);
define('ENV_CHECK_RIGHT', 0);
define('ERROR_CONFIG_VARS', 1);
define('SHORT_OPEN_TAG_INVALID', 2);
define('INSTALL_LOCKED', 3);
define('DATABASE_NONEXISTENCE', 4);
define('PHP_VERSION_TOO_LOW', 5);
define('MYSQL_VERSION_TOO_LOW', 6);
define('UC_URL_INVALID', 7);
define('UC_DNS_ERROR', 8);
define('UC_URL_UNREACHABLE', 9);
define('UC_VERSION_INCORRECT', 10);
define('UC_DBCHARSET_INCORRECT', 11);
define('UC_API_ADD_APP_ERROR', 12);
define('UC_ADMIN_INVALID', 13);
define('UC_DATA_INVALID', 14);
define('DBNAME_INVALID', 15);
define('DATABASE_ERRNO_2003', 16);
define('DATABASE_ERRNO_1044', 17);
define('DATABASE_ERRNO_1045', 18);
define('DATABASE_CONNECT_ERROR', 19);
define('TABLEPRE_INVALID', 20);
define('CONFIG_UNWRITEABLE', 21);
define('ADMIN_USERNAME_INVALID', 22);
define('ADMIN_EMAIL_INVALID', 25);
define('ADMIN_EXIST_PASSWORD_ERROR', 26);
define('ADMININFO_INVALID', 27);
define('LOCKFILE_NO_EXISTS', 28);
define('TABLEPRE_EXISTS', 29);
define('ERROR_UNKNOW_TYPE', 30);
define('ENV_CHECK_ERROR', 31);
define('UNDEFINE_FUNC', 32);
define('MISSING_PARAMETER', 33);
define('LOCK_FILE_NOT_TOUCH', 34);

if(function_exists('mysqli_connect')) $func_items = array('mysqli_connect',  'file_get_contents', 'xml_parser_create','filesize', 'curl_init','zip_open');
else $func_items = array('mysql_connect',  'file_get_contents', 'xml_parser_create','filesize', 'curl_init','zip_open');

$filesock_items = array('fsockopen', 'pfsockopen', 'stream_socket_client');

$env_items = array
(
	'os' => array('c' => 'PHP_OS', 'r' => 'notset', 'b' => 'Linux'),
	'php' => array('c' => 'PHP_VERSION', 'r' => '5.3+', 'b' => 'php7+'),
	'attachmentupload' => array('r' => 'notset', 'b' => '50M'),
	'gdversion' => array('r' => '1.0', 'b' => '2.0'),
	'diskspace' => array('r' => '50M', 'b' => '10G以上'),
	
);

$dirfile_items = array
(

	//'config' => array('type' => 'file', 'path' => CONFIG),
	'config_dir' => array('type' => 'dir', 'path' => './config'),
	//'data' => array('type' => 'dir', 'path' => './data'),
	'cache' => array('type' => 'dir', 'path' => './data/cache'),
	'avatar' => array('type' => 'dir', 'path' => './data/avatar'),
	'ftemplates' => array('type' => 'dir', 'path' => './data/template'),
	'attach' => array('type' => 'dir', 'path' => './data/attachment'),
	'attach_dzz' => array('type' => 'dir', 'path' => './data/attachment/dzz'),
	'attach_icon' => array('type' => 'dir', 'path' => './data/attachment/icon'),
	'attach_appico' => array('type' => 'dir', 'path' => './data/attachment/appico'),
	'attach_appimg' => array('type' => 'dir', 'path' => './data/attachment/appimg'),
	'attach_cache' => array('type' => 'dir', 'path' => './data/attachment/cache'),
	'attach_imgcache' => array('type' => 'dir', 'path' => './data/attachment/imgcache'),
	'attach_qrcode' => array('type' => 'dir', 'path' => './data/attachment/qrcode'),
	'logs' => array('type' => 'dir', 'path' => './data/log'),
);


$form_app_reg_items = array
(
	
	'siteinfo' => array
	(
		'sitename' => array('type' => 'text', 'required' => 1, 'reg' => '/^.*$/', 'value' => array('type' => 'constant', 'var' => 'SOFT_NAME')),
		'siteurl' => array('type' => 'text', 'required' => 1, 'reg' => '/^https?:\/\//', 'value' => array('type' => 'var', 'var' => 'default_appurl'))
	)
);

$form_db_init_items = array
(
	'dbinfo' => array
	(
		'company' => array('type' => 'text', 'required' => 0, 'reg' => '/^.+$/', 'value' => array('type' => 'var', 'var' => 'company')),
		'dbhost' => array('type' => 'text', 'required' => 1, 'reg' => '/^.+$/', 'value' => array('type' => 'var', 'var' => 'dbhost')),
		'dbname' => array('type' => 'text', 'required' => 1, 'reg' => '/^.+$/', 'value' => array('type' => 'var', 'var' => 'dbname')),
		'dbuser' => array('type' => 'text', 'required' => 0, 'reg' => '/^.*$/', 'value' => array('type' => 'var', 'var' => 'dbuser')),
		'dbpw' => array('type' => 'text', 'required' => 0, 'reg' => '/^.*$/', 'value' => array('type' => 'var', 'var' => 'dbpw')),
		'tablepre' => array('type' => 'text', 'required' => 0, 'reg' => '/^.*+/', 'value' => array('type' => 'var', 'var' => 'tablepre')),
		'adminemail' => array('type' => 'text', 'required' => 1, 'reg' => '/@/', 'value' => array('type' => 'var', 'var' => 'adminemail')),
	),
	
);
$form_admin_init_items = array
(
	
	'admininfo' => array
	(
		'email' => array('type' => 'text', 'required' => 1, 'reg' => '/@/', 'value' => array('type' => 'var', 'var' => 'adminemail')),
		'username' => array('type' => 'text', 'required' => 1, 'reg' => '/^.*$/', 'value' => array('type' => 'constant', 'var' => 'admin')),
		'password' => array('type' => 'password', 'required' => 1, 'reg' => '/^.*$/'),
		'password2' => array('type' => 'password', 'required' => 1, 'reg' => '/^.*$/'),
		
	)
);

$serialize_sql_setting = array ();

?>
