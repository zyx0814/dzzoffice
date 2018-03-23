<?php
namespace  user\login\classes;


class Logincheck{
    public function run(){

        global $_config,$_G;

        $setting = $_G['setting'];


        if($_config['uid']){//判断是否已经登录

            if($setting['bbclosed']>0 && $setting['adminid']!=1){//判断站点是否关闭，并且登录用户是否是管理员

                showmessage(lang('site_closed_please_admin'));

            }else{

                $referer = (isset($_GET['referer'])) ? $_GET['referer']:dreferer();

                $referer=$referer ? $referer : './';
				if(strpos($referer, 'user.php') !== false) {
					$referer = 'index.php';
				}
                $referer=str_replace('logging','',$referer);

                $param = array('username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle'], 'uid' => $_G['member']['uid']);

                if(!$_GET['inajax']){

                    showTips(array('lang'=>lang('login_succeed',$param),'referer'=>$referer ? $referer : './'),'html','common/showtips');

                }else{
                    include template('login_skip');
                    exit();
                }
            }
        }
    }
}