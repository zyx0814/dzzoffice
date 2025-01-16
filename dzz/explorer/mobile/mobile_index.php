<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global  $_G;
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
$navtitle = '网盘';
//获取配置设置值
$myexplorer = array();
$myorgs = array();
$mygroup = false;
if ($explorer_setting['useronperm']) {
    $myexplorer = C::t('folder')->fetch_home_by_uid();
    $myexplorer['name'] = lang('explorer_user_root_dirname');
    $contains = C::t('resources')->get_contains_by_fid($myexplorer['fid']);
    $myexplorer['filenum'] = $contains['contain'][0];
    $myexplorer['foldernum'] = $contains['contain'][1];
}
if ($explorer_setting['orgonperm']) {
    $orgs = C::t('organization')->fetch_all_orggroup($uid);
    foreach ($orgs['org'] as $v) {
        if(intval($v['aid'])){
            $v['icon']='index.php?mod=io&op=thumbnail&width=24&height=24&path=' . dzzencode('attach::' . $v['aid']);
        }
        $contains =  C::t('resources')->get_contains_by_fid($v['fid']);
        $v['filenum'] = $contains['contain'][0];
        $v['foldernum'] = $contains['contain'][1];
        $myorgs[] = $v;
    }
}
//用户粘贴板状态获取
$clipboarddata = array('status' => 0);
if ($clipboardtype = C::t('resources_clipboard')->fetch_user_paste_type()) {
    $clipboarddata = array('status' => 1, 'type' => $clipboardtype);
}
if ($explorer_setting['grouponperm']) {
    $mygroup = true;
}
require template('mobile/mobile_index');