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
$applist = C::t('app_market')->fetch_all_by_default($_G['uid'],true);
//获取系统桌面设置信息
$icosdata = array();
//获取打开方式
$data['extopen']['all'] = C::t('app_open')->fetch_all_ext();
$data['extopen']['ext'] = C::t('app_open')->fetch_all_orderby_ext($_G['uid'], $data['extopen']['all'], $applist);
$data['extopen']['user'] = C::t('app_open_default')->fetch_all_by_uid($_G['uid']);
//目录数据
$folderdata = array();
$data['formhash'] = $_G['formhash'];
$data['sourcedata'] = array(
    'icos' => $icosdata ? $icosdata : array(),
    'folder' => $folderdata ? $folderdata : array()
);
$space['attachextensions'] = $space['attachextensions'] ? explode(',', $space['attachextensions']) : array();
$data['space'] = $space;
echo json_encode($data);
exit();