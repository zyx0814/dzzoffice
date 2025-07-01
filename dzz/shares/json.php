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
$uid = $_G['uid'];
$space = dzzgetspace($uid);
$space['self'] = intval($space['self']);
$data = array();
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
//获取打开方式
$data['extopen']['all'] = C::t('app_open')->fetch_all_ext();
$data['extopen']['ext'] = C::t('app_open')->fetch_all_orderby_ext($_G['uid'], $data['extopen']['all']);
$data['extopen']['user'] = C::t('app_open_default')->fetch_all_by_uid($_G['uid']);
//目录数据
$folderdata = array();
$data['formhash'] = $_G['formhash'];
$data['sourcedata'] = array(
    'icos' => $icosdata ? $icosdata : array(),
    'folder' => $folderdata ? $folderdata : array(),
    'app' => $appdata ? $appdata : array()
);
$space['attachextensions'] = $space['attachextensions'] ? explode(',', $space['attachextensions']) : array();
$thame = getThames();
$data['thame'] = $thame['data'];
$data['space'] = $space;
echo json_encode($data);
exit();