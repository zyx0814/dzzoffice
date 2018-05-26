<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2018/3/26
 * Time: 17:40
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
$savefile = isset($_GET['savefile']) ? intval($_GET['savefile']) : 0;//是否直接保存文件
$callback = isset($_GET['callback']) ? $_GET['callback'] : 'callback_selectposition';//回调函数名称
$allowcreate = isset($_GET['allowcreate']) ? intval($_GET['allowcreate']):0;//是否允许新建文件夹
$rids = isset($_GET['rids']) ? trim($_GET['rids']) : '';//文件id(需用dzzencode加密)
$selhome = isset($_GET['selhome']) ? $_GET['selhome']:0;//展示网盘0不展示
$selorg = isset($_GET['selorg']) ? $_GET['selorg']:0;//展示机构0不展示
$selgroup = isset($_GET['selgroup']) ? $_GET['selgroup']:0;//展示群组0不展示
$range = isset($_GET['range']) ? $_GET['range']:0;//是否限制展示0不限定
$gets = array(
    'allowcreate' => $allowcreate,
    'nosearch' => 1,
    'inwindow' => 1,
    'ctrlid' => isset($_GET['selposition']) ? trim($_GET['selposition']) : 'selposition',
    'selhome'=>$selhome,
    'selorg'=>$selorg,
    'selgroup'=>$selgroup,
    'range'=>$range
);
$theurl = MOD_URL . "&op=filelist&callback=".$callback.'&'. url_implode($gets);
include template('selectfile');
exit();