<?php
namespace core\dzz;

use \core as C;
use \core\dzz\Hook as Hook;
use \DB as DB;
use \IO as IO;

class Route{

    public static function dzzRoute(&$params,$extra=null,&$break)
    {

        global $_G,$_config;
        $mod = !empty($params[MOULD]) ? $params[MOULD]:$_config['default_mod'];

        $op  = !empty($params[DIVIDE]) ? $params[DIVIDE]:$_config['default_op'];
        if(empty($mod)){

            if($_G['uid']<1 && !defined('ALLOWGUEST') && $_G['setting']['loginset']['available']){

                @header("Location: user.php?mod=login".($_GET['referer']?'&referer='.$_GET['referer']:''));

                exit();
            }
			
           $return =  require DZZ_ROOT.'./'.CURSCRIPT.'/'.$op.EXT;

        }else{

            if(strpos(strtolower($mod),':')!==false){

                $patharr=explode(':',$mod);

                foreach($patharr as $path){

                    if(!preg_match("/^\w+$/i",$path)) showmessage(lang('undefined_action'));

                }
                $modfile='./'.CURSCRIPT.'/'.str_replace(':','/',$mod).'/'.($op?$op:'index').EXT;

                if(@!file_exists(DZZ_ROOT.$modfile)){

                   //兼容老版
                    if(@!file_exists($modfile='./'.CURSCRIPT.'/'.CURSCRIPT.'_'.str_replace(':','/',$mod).EXT)){

                        showmessage($modfile.lang('file_nonexistence',array('modfile'=>htmlspecialchars($modfile))));
                    }

                }

            }else{

                if(!preg_match("/^\w+$/i",$mod) && $mod !== '') showmessage('undefined_action');

                if(!preg_match("/^\w+$/i",$op)) showmessage('undefined_action');

                if(@!file_exists(DZZ_ROOT.($modfile = './'.CURSCRIPT.'/'.$mod.'/'.$op.EXT)) && @!file_exists(DZZ_ROOT.($modfile = './'.CURSCRIPT.'/'.$mod.'/'.$mod.EXT))) {
                    //兼容老版
                    if(@!file_exists($modfile='./'.CURSCRIPT.'/'.$mod.EXT)){

                        showmessage(lang('file_nonexistence',array('modfile'=>htmlspecialchars($modfile))));
                    }

                }

            }

            //模块常量
            define('MOD_PATH',CURSCRIPT.'/'.CURMODULE);
            define('MOD_NAME',CURMODULE);
            define('MOD_DIR',dirname($modfile));
            define('MOD_URL',BASESCRIPT.'?mod='.$mod);
            define('OP_NAME',$op);
           // $break = true;
			Hook::listen('mod_run');
            return DZZ_ROOT.$modfile;
        }
    }
}