<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
include libfile('function/filerouterule');
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
$callback_url = isset($_GET['callback_url']) ? $_GET['callback_url'] : '';//回调函数名称
$allowcreate = isset($_GET['allowcreate']) ? intval($_GET['allowcreate']) : 1;//是否允许新建文件夹
//获取配置设置值
$explorer_setting = get_resources_some_setting();
$range = isset($_GET['range']) ? trim($_GET['range']) : '';//指定范围
$defaultselect = isset($_GET['defaultsel']) ? filerouteParse(trim($_GET['defaultsel'])) : filerouteParse('我的网盘');//默认选中,支持路径如：我的网盘/xxx,群组xxx/xxx,群组或机构|xxx，群组或机构|xxx/新建文件夹
$type = isset($_GET['type']) ? intval($_GET['type']) : 0;//2,选择位置；1，保存文件；0，选择文件；默认为0
$exttype = isset($_GET['exttype']) ? trim($_GET['exttype']) : '';//类型范围

$mulitype = isset($_GET['mulitype']) ? intval($_GET['mulitype']) : 0;//0，不允许多选；1，允许多选
$token = isset($_GET['token']) ? trim($_GET['token']) : '';//调用地方传递参数，将原样返回给回调函数;
$formhash = isset($_GET['formhash']) ? $_GET['formhash']:'';
$filename = isset($_GET['filename']) ? trim($_GET['filename']):'';
$deferer  = dreferer();
if($exttype){
    $exttype = str_replace(array('&quot;','|','$'),array('"','(',')'),$exttype);
    $exttype = json_decode($exttype);
}
if($jsondetoken = json_decode($token)){
    $token = $jsondetoken;
}
$gets = array(
    'allowcreate' => $allowcreate,
    'type' => $type,
    'range' => $range,
    'defaultselect' => $defaultselect,
    'mulitype' => $mulitype,
    'exttype' => $exttype,
    'callback_url'=>$callback_url,
    'token'=>$token,
    'formhash'=>$formhash,
    'filename'=>$filename
);
$json = json_encode($gets);
$allowvisit = array('file','searchfile', 'json', 'ajax', 'search', 'save','home','group');
if ($do) {
    if (!in_array($do, $allowvisit)) {
        showmessage(lang('access_denied'), dreferer());
    } else {
        require MOD_PATH . '/mobilefileselection/' . $do . '.php';
    }
} else {
    include template('mobilefileselection/index');
    exit();
}

