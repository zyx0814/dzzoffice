<?php
namespace user\register\classes;

class Register{

    public function run()
    {
        global $_G;
        
        $setting = $_G['setting'];

        //判断是否已登录，如果登录直接进入之前界面
        if($_G['uid']) {

            $url_forward = dreferer();

            if(strpos($url_forward, 'user.php') !== false) {

                $url_forward = 'index.php';
            }
            showmessage('login_succeed', $url_forward ? $url_forward : './', array('username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle'], 'uid' => $_G['uid']), array());

        } elseif(!$setting['regclosed']) {//判断是否开启注册，如果未开启则提示

            if($_GET['action'] == 'activation' || $_GET['activationauth']) {

                if(!$setting['ucactivation'] && !$setting['closedallowactivation']) {

                    showmessage(lang('register_disable_activation'));
                }

            } elseif(!$setting['regstatus']) {

                showmessage(!$setting['regclosemessage'] ? lang('register_disable') : str_replace(array("\r", "\n"), '', $setting['regclosemessage']));
            }
        }
    }
}