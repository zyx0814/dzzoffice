<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;
$uid = $_G['uid'];
$is_wxwork = helper_browser::is_wxwork();//判断是否是企业微信
$allowvisit = array('file', 'mobile_index', 'share', 'collect', 'cat', 'group','groupmore', 'comment','recent','json','ajax','search','dynamic','property','member');
$do = isset($_GET['do']) ? trim($_GET['do']) : 'mobile_index';
if ($do) {
    if (!in_array($do, $allowvisit)) {
        showmessage(lang('access_denied'), dreferer());
    } else {
        require MOD_PATH . '/mobile/' . $do . '.php';
        exit();
    }
}