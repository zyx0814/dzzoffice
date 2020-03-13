<?php
define('IN_DZZ', true);
define('EXT','.php');//文件后缀
define('BS',DIRECTORY_SEPARATOR);//系统目录分割符
define('DZZ_ROOT', dirname(dirname(__FILE__)).BS);//系统根目录
define('CONFIG_NAME','config');//配置文件名称
define('CORE_NAME','core');//核心目录名
define('DATA_NAME','data');//数据目录名
define('CACHE_NAME','cache');//缓存文件目录名
define('CACHE_DIR',DZZ_ROOT.DATA_NAME.BS.CACHE_NAME);//缓存目录
define('CORE_PATH',DZZ_ROOT.CORE_NAME.BS.'class');//核心类目录
define('APP_DIRNAME','dzz');//应用目录名
define('APP_CHECK_URL', "http://www.dzz.cc/");//检测应用更新地址 http://dzz.cc/ 
//define('APP_DIR',DZZ_ROOT.APP_DIRNAME.BS);//应用目录
define('MOULD','mod');//路由模块键名
define('DIVIDE','op');//路由操作键名

define('DZZ_CORE_DEBUG', false);

define('DZZ_TABLE_EXTENDABLE', false);

global $_G,$_config;

$_config = array();
require DZZ_ROOT.'core/core_version.php';
require DZZ_ROOT.'core/class/class_core.php';

set_exception_handler(array('core', 'handleException'));

$_config = array_merge($_config,core::loadConfig(DZZ_ROOT.CONFIG_NAME.BS.'config_default'.EXT));

$install = core::loadConfig(DZZ_ROOT.CONFIG_NAME.BS.CONFIG_NAME.EXT);
if(!$install){
    header('Location: install/index.php');
    exit();
}

$_config = array_merge($_config,$install);

$_config = array_merge($_config,core::loadConfig(DZZ_ROOT.CONFIG_NAME.BS.'config_frame'.EXT));


if(DZZ_CORE_DEBUG) {
    set_error_handler(array('core', 'handleError'));
    register_shutdown_function(array('core', 'handleShutdown'));
}

if(function_exists('spl_autoload_register')) {
    //注册系统自动加载函数
    spl_autoload_register(array('core', 'autoload'));
    //注册命名空间
    core::addNamespace($_config['namespacelist']);
} 

class C extends \core {}
class Hook extends \core\dzz\Hook{}
class DB extends dzz_database {}

class Tpdb extends \core\dzz\Tpdb{}

if( function_exists('mysqli_connect') ){
    class Tpsqli extends \core\dzz\Tpsqli{}
}else{
    class Tpsql extends \core\dzz\Tpsql{}
}

//class HookRead extends \core\dzz\HookRead{}

class IO extends dzz_io {}

require DZZ_ROOT.'core/function/function_misc.php';

//if(@!file_exists(CACHE_DIR.BS.'tags'.EXT)){

//    HookRead::_init();//注册钩子
//}

//C::creatapp();


