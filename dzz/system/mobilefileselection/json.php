<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/10/11
 * Time: 16:18
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
$space = dzzgetspace($uid);
$space['self'] = intval($space['self']);
$data = array();
$data['myuid'] = $uid;
$config = array();
if (!$config = C::t('user_field')->fetch($_G['uid'])) {
    $config = dzz_userconfig_init();
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
//获取系统桌面设置信息
$icosdata = array();
$data['formhash'] = $_G['formhash'];
$data['sourcedata'] = array(
    'icos' => $icosdata ? $icosdata : array(),
    'folder' => $folderdata ? $folderdata : array(),
    'app' => $appdata ? $appdata : array()
);
$space['attachextensions'] = $space['attachextensions'] ? explode(',', $space['attachextensions']) : array();

$data['space'] = $space;
$thame = getThames();
$data['thame'] = $thame['data'];
$data['mulitype'] = $mulitype;
$data['fileselectiontype'] = $type;
$data['callback_url'] = $callback;
if ($exttype) {
    $exttype = str_replace(array('&quot;', '|', '$'), array('"', '(', ')'), $exttype);
}
$data['allowselecttype'] = json_decode($exttype);
$data['defaultfilename'] = isset($filename) ? $filename : '';
echo json_encode($data);
exit();