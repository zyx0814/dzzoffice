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
$applist = C::t('app_market')->fetch_all_by_default($_G['uid'],true);
//获取系统桌面设置信息
$icosdata = array();
$data['formhash'] = $_G['formhash'];
$data['sourcedata'] = array(
    'icos' => $icosdata ? $icosdata : array(),
    'folder' => $folderdata ? $folderdata : array()
);
$space['attachextensions'] = $space['attachextensions'] ? explode(',', $space['attachextensions']) : array();

$data['space'] = $space;
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