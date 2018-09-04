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
$refer = dreferer();
$data = array();
@include DZZ_ROOT . './core/core_version.php';
$arr = array();
//$arr['start']=microtime(true);

$data = array();
$data['version'] = CORE_VERSION;
$data['release'] = CORE_RELEASE;
$explorer_setting = get_resources_some_setting();
$data['deletefinally'] = 0;
if($explorer_setting['finallydelete'] === 0){
    $data['deletefinally'] = 1;
}
$data['myuid'] = $uid;
if ($_G['setting']['upgrade']) $space['upgrade'] = 1;
else $space['upgrade'] = 0;


//图标排列方式
if ($_G['setting']['dzz_iconview']) {
    $iconview = $_G['setting']['iconview'];
} else {
    $iconview = C::t('iconview')->fetch_all();
}

$data['iconview'] = $iconview;

$config = array();
$config=C::t('user_field')->fetch($_G['uid']);
$applist=$config['applist']?explode(',',$config['applist']):array();
if($applist_n =array_keys(C::t('app_market')->fetch_all_by_notdelete($_G['uid']))) {
	$newappids = array();
	foreach ($applist_n as $appid) {
		if (!in_array($appid, $applist)) {
			$applist[] = $appid;
			$newappids[] = $appid;
		}
	}
	if ($newappids){
		C::t('app_user')->insert_by_uid($_G['uid'], $newappids);
		C::t('user_field')->update($_G['uid'], array('applist' => implode(',', $applist)));
	} 
}
//应用数据
	$appdata=array();
	$appdata=C::t('app_market')->fetch_all_by_appid($applist);
	//$arr['appdata']=microtime(true);
	$applist_1=array();
	foreach($appdata as $value){
		if($value['isshow']<1) continue;
		if($value['available']<1) continue;
		if($value['system'] == 2) continue;
		$applist_1[]=$value['appid'];
	}
	
	
$data['applist']=array_values($applist_1);
//获取系统桌面设置信息
$icosdata = array();
$data['noticebanlist'] = $config['noticebanlist'] ? explode(',', $config['noticebanlist']) : array();
//获取打开方式
$data['extopen']['all'] = C::t('app_open')->fetch_all_ext();
$data['extopen']['ext'] = C::t('app_open')->fetch_all_orderby_ext($_G['uid'], $data['extopen']['all']);
$data['extopen']['user'] = C::t('app_open_default')->fetch_all_by_uid($_G['uid']);
//获取用户的默认打开方式
$data['extopen']['userdefault'] = C::t('app_open_default')->fetch_all_by_uid($_G['uid']);
//目录数据
$folderdata = array();
$data['cut']=array();
//用户粘贴板数据
$clipboardinfo = C::t('resources_clipboard')->fetch_by_uid($uid);
if($clipboardinfo){
    //复制类型1为复制，2为剪切
    $copttype = $clipboardinfo['copytype'];
    $data['cut']['iscut'] = ($copttype == 1) ? 0:1;

    $files = explode(',',$clipboardinfo['files']);
    foreach($files as $v){
        $resourcesdata = C::t('resources')->fetch_by_rid($v);
        if($resourcesdata['type'] == 'folder'){
            $folderdata[$resourcesdata['fid']] = C::t('folder')->fetch_by_fid($resourcedata['oid']);
			$icosdata[$v] = $resourcesdata;
        }else{
            $icosdata[$v] = $resourcesdata;
        }
    }
    $data['cut']['icos'] = $files;
}else{
	 $data['cut']['icos']=array();
}

$data['formhash'] = $_G['formhash'];


$data['sourcedata'] = array(
    'icos' => $icosdata ? $icosdata : array(),
    'folder' => $folderdata ? $folderdata : array(),
	'app'   => $appdata?$appdata:array()
);
$space['attachextensions'] = $space['attachextensions'] ? explode(',', $space['attachextensions']) : array();

$data['myspace'] = $data['space'] = $space;
$thame = getThames();
$data['thame'] = $thame['data'];
$infoPanelOpened = C::t('user_setting')->fetch_by_skey('infoPanelOpened');
if (isset($infoPanelOpened)) {
    $data['infoPanelOpened'] = ($infoPanelOpened) ? 1 : 0;
} else {
	C::t('user_setting')->update_by_skey('infoPanelOpened',1);
    $data['infoPanelOpened'] = 1;
}
$data['mulitype'] = $mulitype;
$data['fileselectiontype'] = $type;
if($exttype){
    $exttype = str_replace(array('&quot;','|','$'),array('"','(',')'),$exttype);
}
$data['allowselecttype'] = json_decode($exttype);
$data['defaultfilename'] = isset($filename) ? $filename:'';
$data['defaultselect'] = $_GET['defaultselect'];
$data['allowcreate'] = $_GET['allowcreate'];
$data['permfilter'] = $_GET['perm'];
echo json_encode($data);
exit();
