<?php
namespace core\dzz;

use \core as C;

class Apprun{

    public  function run(){

        global $_config;

        $this->mod_prem_check($_config['MOD_VIEW_PERM']);
    }

    private function mod_prem_check($chkarr = array()){

        global $_G;

        if(!empty($chkarr)){

            if(!defined('CURMODULE')) return false;

            foreach ($chkarr as $v){

                $modarr = explode(',',$v['MOD_NAME']);

                if(in_array(CURMODULE,$modarr)){

                    $this->perm_chk($v['PERM']);
                }
            }
        }
    }
    private function perm_chk($perm = ''){
        global $_G;

        switch ($perm){
            case 0:
                break;
            case 1:if(!$_G['uid']) exit('Access Denied');
                break;
            case 2:if($_G['adminid']!=1) exit('Access Denied');
                break;
            case 3: if(!C::t('user')->checkfounder($_G['member'])) exit('Access Denied');
                break;
            default: exit('arg error');

        }

    }
}