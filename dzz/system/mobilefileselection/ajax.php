<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;
$uid = $_G['uid'];
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
//获取文件夹信息
if ($operation == 'createFolder') {//新建文件夹

    $fid = isset($_GET['fid']) ? intval($_GET['fid']) : '';
    $folderinfo = C::t('folder')->fetch($fid);
    $perm = 0;
    $name = !empty($_GET['foldername']) ? trim($_GET['foldername']) : lang('newfolder');
    $fid = intval($_GET['fid']);
    $fname = io_dzz::name_filter(getstr($name, 80));
    if ($arr = IO::CreateFolder($fid, $fname, $perm)) {
        if ($arr['error']) {
        } else {
            $arr = array_merge($arr['icoarr'], $arr['folderarr']);
            $arr['msg'] = 'success';

        }
    } else {
        $arr = array();
        $arr['error'] = lang('failure_newfolder');
    }
    exit(json_encode($arr));
}