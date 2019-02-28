<?php
namespace core\dzz;

class Modroute{

    public function run(&$params){

        global $_config,$action,$do;

        $return = false;
        $action = !empty($params[$_config['action_name']]) ? $params[$_config['action_name']]:$_config['default_action'];
        $do = !empty($params[$_config['do_name']]) ? $params[$_config['do_name']]:'';
        if(!empty($action)){
           
            if(!preg_match("/^\w+$/i",$action)) showmessage('undefined_action');
            if(!preg_match("/^\w+$/i",$do) && $do !== '') showmessage('undefined_action');

            if(@!file_exists($file = DZZ_ROOT.CURSCRIPT.BS.CURMODULE.BS.OP_NAME.BS.$action.EXT) ){

                if(@!file_exists($file = DZZ_ROOT.CURSCRIPT.BS.CURMODULE.BS.OP_NAME.BS.$action.BS.$do.EXT)){

                    showmessage($file.lang('file_nonexistence',array('file'=>htmlspecialchars($file))));
                }

            }
			$params['route_file']=$file;
        }
    }
}