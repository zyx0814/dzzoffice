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
$data['deletefinally'] = 0;
if (isset($_G['setting']['explorer_finallydelete'])) {
    if (intval($_G['setting']['explorer_finallydelete']) === 0) {
        $data['deletefinally'] = 1;
    }
}
$applist = C::t('app_market')->fetch_all_by_default($_G['uid'],true);
//获取系统桌面设置信息
$icosdata = array();
//获取打开方式
$data['extopen']['all'] = C::t('app_open')->fetch_all_ext();
$data['extopen']['ext'] = C::t('app_open')->fetch_all_orderby_ext($_G['uid'], $data['extopen']['all'], $applist);
$data['extopen']['user'] = C::t('app_open_default')->fetch_all_by_uid($_G['uid']);
//目录数据
$folderdata = array();
$data['cut'] = array();
//用户粘贴板数据
$clipboardinfo = C::t('resources_clipboard')->fetch_by_uid($uid);
if ($clipboardinfo) {
    //复制类型1为复制，2为剪切
    $copttype = $clipboardinfo['copytype'];
    $data['cut']['iscut'] = ($copttype == 1) ? 0 : 1;

    $files = explode(',', $clipboardinfo['files']);
    foreach ($files as $v) {
        $resourcesdata = C::t('resources')->fetch_by_rid($v);
        if ($resourcesdata['type'] == 'folder') {
            $folderdata[$resourcesdata['fid']] = C::t('folder')->fetch_by_fid($resourcedata['oid']);
            $icosdata[$v] = $resourcesdata;
        } else {
            $icosdata[$v] = $resourcesdata;
        }
    }
    $data['cut']['icos'] = $files;
} else {
    $data['cut']['icos'] = array();
}

$data['formhash'] = $_G['formhash'];


$data['sourcedata'] = array(
    'icos' => $icosdata ? $icosdata : array(),
    'folder' => $folderdata ? $folderdata : array()
);
$space['attachextensions'] = $space['attachextensions'] ? explode(',', $space['attachextensions']) : array();

$data['space'] = $space;
$infoPanelOpened = C::t('user_setting')->fetch_by_skey('infoPanelOpened');
if (isset($infoPanelOpened)) {
    $data['infoPanelOpened'] = ($infoPanelOpened) ? 1 : 0;
} else {
    C::t('user_setting')->update_by_skey('infoPanelOpened', 1);
    $data['infoPanelOpened'] = 1;
}
$data['mulitype'] = $mulitype;
$data['fileselectiontype'] = $type;
if ($exttype) {
    $exttype = str_replace(array('&quot;', '|', '$'), array('"', '(', ')'), $exttype);
}
$data['allowselecttype'] = json_decode($exttype);
$data['defaultfilename'] = isset($filename) ? $filename : '';
$data['defaultselect'] = $_GET['defaultselect'];
$data['allowcreate'] = $_GET['allowcreate'];
$data['permfilter'] = $_GET['perm'];
echo json_encode($data);
exit();