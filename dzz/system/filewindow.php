<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
$callback = isset($_GET['callback']) ? $_GET['callback'] : 'callbak_openfile';//回调函数名称
$allowcreate = isset($_GET['allowcreate']) ? intval($_GET['allowcreate']):1;//是否允许新建,默认允许
$selrange = isset($_GET['range']) ? urldecode(trim($_GET['range'])):'';//指定范围
$defaultselect = isset($_GET['defaultsel']) ? $_GET['defaultsel']:'';//默认选中
$type =  isset($_GET['type']) ? intval($_GET['type']):0;//0，选择文件；1，保存文件；2,另存为
$mulitype =  isset($_GET['mulitype']) ? intval($_GET['mulitype']):0;//0，不允许多选；1，允许多选
$exttype =  isset($_GET['exts']) ? trim($_GET['exts']):'';//类型范围
$perm = isset($_GET['perm']) ? trim($_GET['perm']):'';//权限判断值：比如 write,判断是否有写入权限；再如，write,copy，判断是否有写入和copy权限(即多个权限用逗号分隔)
$rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';//文件id(需用dzzencode加密)
$filename = isset($_GET['filename']) ? trim($_GET['filename']) :'';
$token = isset($_GET['token']) ? trim($_GET['token']):'';//调用地方传递参数，将原样返回给回调函数
$gets = array(
    'allowcreate' => $allowcreate,
    'nosearch' => 1,
    'inwindow' => 1,
    'ctrlid' => isset($_GET['selfiles']) ? trim($_GET['selfiles']) : 'selfiles',
    'selfids'=>$fids,
    'type'=>$type,
    'rid'=>$rid,
    'filename'=>$filename,
    'range'=>$selrange,
    'defaultsel'=>$defaultselect,
    'exttype'=>$exttype,
    'mulitype'=>$mulitype,
    'token'=>$token,
    'perm'=>$perm
);
$theurl = MOD_URL . "&op=fileselection&callback=".$callback.'&'. url_implode($gets);
include template('selectfile');
exit();