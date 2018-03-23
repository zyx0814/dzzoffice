<?php
namespace   user\login\classes;

class Loginvalchk{

    public function run(&$params)
    {
        global $_G;

        $type = isset($_GET['returnType']) ? $_GET['returnType']:'json';

        //验证提交是否合法，阻止外部非法提交

        chk_submitroule($type);

        if (!($_G['member_loginperm'] = logincheck($params['email']))) {//登录失败错误次数

            showTips(array('error' => lang('login_strike')), $type);

        }
        if ($params['fastloginfield']) {

            $params['loginfield'] = $params['fastloginfield'];

        }
        $_G['uid'] = $_G['member']['uid'] = 0;

        $_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';

        if (!$params['password'] || $params['password'] != addslashes($params['password'])) {//密码验证

            showTips(array('error' => lang('profile_passwd_illegal')),$type);

        }

    }
}