<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/3/1
 * Time: 18:53
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;

if ($_GET['formhash'] != $_G['formhash']) {
    showmessage('operation_error', dreferer(), array('formhash' => FORMHASH));
}
//应用退出登录挂载点
Hook::listen('applogout');
clearcookies();
$_G['groupid'] = $_G['member']['groupid'] = 7;

$_G['uid'] = $_G['member']['uid'] = 0;

$_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';

showmessage('logout_succeed', dreferer(), array('formhash' => FORMHASH));