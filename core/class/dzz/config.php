<?php
namespace core\dzz;

class Config{

    public function run(&$param){

        global $_config,$_G;

        //应用常量
        define('APP_PATH',CURSCRIPT);
        define('APP_NAME',CURSCRIPT);
        define('APP_DIR',DZZ_ROOT.APP_PATH);
        define('APP_URL','index.php');
 		//默认应用配置
        $default_mod_file=DZZ_ROOT.'./data/cache/default_mod.php';
        if(CURSCRIPT == 'dzz' && @file_exists($default_mod_file)){
            $default_mod_config = require_once $default_mod_file; 
            $_config = array_merge($_config,$default_mod_config);
        }
        //应用配置
        if(@file_exists(DZZ_ROOT.CURSCRIPT.BS.CONFIG_NAME.BS.CONFIG_NAME.EXT)){
            $app_config = require_once DZZ_ROOT.CURSCRIPT.BS.CONFIG_NAME.BS.CONFIG_NAME.EXT;
            if(isset($app_config['db']) ){
                unset($app_config['db']);
            }
            $_config = array_merge($_config,$app_config);
        }
        $mod = isset($param[MOULD]) ? $param[MOULD]:$_config['default_mod'];
		
        if(!empty($mod)){
            if(strpos(strtolower($mod),':')!==false) {
                $patharr = explode(':', $mod);
                $modvar = true;
                foreach ($patharr as $path) {
                    if (!preg_match("/\w+/i", $path)) $modvar = false;
                }
                if($modvar) define('CURMODULE',str_replace(':', '/', $mod));
            }else{
               /* if(CURSCRIPT == 'dzz' && $mod == 'index'){
                    define('CURMODULE',CURSCRIPT);
                    $modconfig = DZZ_ROOT.CURMODULE.BS.CONFIG_NAME.BS.CONFIG_NAME.EXT;
                }else{*/
                    define('CURMODULE',$mod);
                    $modconfig = DZZ_ROOT.APP_PATH.BS.CURMODULE.BS.CONFIG_NAME.BS.CONFIG_NAME.EXT;
               // }
            }
            if(@file_exists($modconfig)){
                //模块配置
                $mod_config = require_once $modconfig;
                if(isset($mod_config['db']) ){
                    unset($mod_config['db']);
                }
                //配置合并
                if(is_array($mod_config)){
                    $_config = array_merge($_config,$mod_config);
                }
            }

        }
    }
}