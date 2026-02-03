<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if (!defined('IN_LEYUN')) {
    exit('Access Denied');
}

define('SOFT_NAME', 'DzzOffice');

define('INSTALL_LANG', 'SC_UTF8');

define('CONFIG', './config/config.php');

$sqlfile = ROOT_PATH . './install/data/install.sql';
$lockfile = ROOT_PATH . './data/install.lock';

@include ROOT_PATH . CONFIG;

define('CHARSET', 'utf-8');
define('DBCHARSET', 'utf8mb4');

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

$func_items = ['mysqli_connect', 'file_get_contents', 'xml_parser_create', 'json_encode', 'filesize', 'curl_init', 'zip_open', 'mb_check_encoding', 'mb_convert_encoding'];
$filesock_items = ['fsockopen', 'pfsockopen', 'stream_socket_client'];
$env_items =
    [
    'os' => ['c' => 'PHP_OS', 'r' => 'notset', 'b' => 'Linux'],
    'php' => ['c' => 'PHP_VERSION', 'r' => '7.2+', 'b' => '8+'],
    'php_bit' => ['c' => 'PHP_INT_SIZE', 'r' => '32位<br>(32位不支持2G以上文件上传下载)', 'b' => '64位'],
    'attachmentupload' => ['r' => 'notset', 'b' => '50M'],
    'gdversion' => ['r' => '1.0', 'b' => '2.0'],
    'diskspace' => ['r' => '50M', 'b' => 'notset'],
    'opcache' => ['r' => 'notset', 'b' => 'enable'],
    ];

$dirfile_items =
    [

    'config' => ['type' => 'file', 'path' => CONFIG],
    'config_dir' => ['type' => 'dir', 'path' => './config'],
    'data' => ['type' => 'dir', 'path' => './data'],
    'cache' => ['type' => 'dir', 'path' => './data/cache'],
    'avatar' => ['type' => 'dir', 'path' => './data/avatar'],
    'ftemplates' => ['type' => 'dir', 'path' => './data/template'],
    'attach' => ['type' => 'dir', 'path' => './data/attachment'],
    'attach_dzz' => ['type' => 'dir', 'path' => './data/attachment/dzz'],
    'attach_icon' => ['type' => 'dir', 'path' => './data/attachment/icon'],
    'attach_appico' => ['type' => 'dir', 'path' => './data/attachment/appico'],
    'attach_appimg' => ['type' => 'dir', 'path' => './data/attachment/appimg'],
    'attach_cache' => ['type' => 'dir', 'path' => './data/attachment/cache'],
    'attach_imgcache' => ['type' => 'dir', 'path' => './data/attachment/imgcache'],
    'attach_qrcode' => ['type' => 'dir', 'path' => './data/attachment/qrcode'],
    'logs' => ['type' => 'dir', 'path' => './data/log'],
    ];

$form_db_init_items =
    [
    'dbinfo' =>
        [
        'company' => ['type' => 'text', 'required' => 0, 'reg' => '/^.+$/', 'value' => ['type' => 'var', 'var' => 'company']],
        'dbhost' => ['type' => 'text', 'required' => 1, 'reg' => '/^.+$/', 'value' => ['type' => 'var', 'var' => 'dbhost']],
        'dbname' => ['type' => 'text', 'required' => 1, 'reg' => '/^.+$/', 'value' => ['type' => 'var', 'var' => 'dbname']],
        'dbuser' => ['type' => 'text', 'required' => 0, 'reg' => '/^.*$/', 'value' => ['type' => 'var', 'var' => 'dbuser']],
        'dbpw' => ['type' => 'text', 'required' => 0, 'reg' => '/^.*$/', 'value' => ['type' => 'var', 'var' => 'dbpw']],
        'tablepre' => ['type' => 'text', 'required' => 0, 'reg' => '/^.*+/', 'value' => ['type' => 'var', 'var' => 'tablepre']],
        'adminemail' => ['type' => 'text', 'required' => 1, 'reg' => '/@/', 'value' => ['type' => 'var', 'var' => 'adminemail']],
        ],
    'admininfo' =>
        [
        'email' => ['type' => 'text', 'required' => 1, 'reg' => '/@/', 'value' => ['type' => 'var', 'var' => 'adminemail']],
        'username' => ['type' => 'text', 'required' => 1, 'reg' => '/^.*$/', 'value' => ['type' => 'constant', 'var' => 'admin']],
        'password' => ['type' => 'password', 'required' => 1, 'reg' => '/^.*$/'],
        'password2' => ['type' => 'password', 'required' => 1, 'reg' => '/^.*$/'],

        ]
    ];
