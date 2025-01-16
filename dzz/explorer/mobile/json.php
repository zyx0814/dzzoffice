<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
$systemdata = array();
$space = dzzgetspace($uid);
$systemdata['myuid'] = $uid;
$systemdata['space'] = $space;
//用户粘贴板状态获取
$clipboarddata = array('status' => 0);
if ($clipboardtype = C::t('resources_clipboard')->fetch_user_paste_type()) {
    $clipboarddata = array('status' => 1, 'type' => $clipboardtype);
}
$config = array();
if(!$config=C::t('user_field')->fetch($_G['uid'])){
    $config= dzz_userconfig_init();
}
$applist = $config['applist'] ? explode(',', $config['applist']) : array();
if ($applist_n = array_keys(C::t('app_market')->fetch_all_by_notdelete($_G['uid']))) {
    $newappids = array();
    foreach ($applist_n as $appid) {
        if (!in_array($appid, $applist)) {
            $applist[] = $appid;
            $newappids[] = $appid;
        }
    }
    if ($newappids) {
        C::t('app_user')->insert_by_uid($_G['uid'], $newappids);
        C::t('user_field')->update($_G['uid'], array('applist' => implode(',', $applist)));
    }
}
//应用数据
$appdata = array();
$appdata = C::t('app_market')->fetch_all_by_appid($applist);
$applist_1 = array();
foreach ($appdata as $value) {
    if ($value['isshow'] < 1) continue;
    if ($value['available'] < 1) continue;
    if ($value['system'] == 2) continue;
    $applist_1[] = $value['appid'];
}
//获取打开方式
$systemdata['extopen']['all'] = C::t('app_open')->fetch_all_ext();
$systemdata['extopen']['ext'] = C::t('app_open')->fetch_all_orderby_ext($_G['uid'], $data['extopen']['all']);
$systemdata['extopen']['user'] = C::t('app_open_default')->fetch_all_by_uid($_G['uid']);
//获取用户的默认打开方式
$systemdata['extopen']['userdefault'] = C::t('app_open_default')->fetch_all_by_uid($_G['uid']);

$systemdata['applist'] = array_values($applist_1);
$systemdata['app'] = $appdata ? $appdata : array();
$systemdata['clipboarddata'] = $clipboarddata;
$systemdata['is_wxwork'] = $is_wxwork;
echo json_encode($systemdata);
exit();