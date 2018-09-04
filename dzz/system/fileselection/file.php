<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/11/16
 * Time: 15:20
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
$operation = (isset($_GET['operation'])) ? trim($_GET['operation']) : '';
$gid = isset($_GET['gid']) ? intval($_GET['gid']) : '';
$fid = isset($_GET['fid']) ? intval($_GET['fid']) : '';
if ($gid) {
    if (!$group = C::t('organization')->fetch($gid)) {
        showmessage(lang('no_group'), dreferer());
    }
    if (!$explorer_setting['grouponperm']) {
        showmessage(lang('no_privilege'), dreferer());
    }
    //获取群组成员权限
    $perm = C::t('organization_admin')->chk_memberperm($gid, $uid);
    //判断群组是否开启，如果未开启(共享目录)并且不是管理员不能访问
    if (!$group['diron'] && !$perm) {
        showmessage(lang('no_privilege'), dreferer());
    }
    //判断是否有权限访问群组，如果不是管理员权限(主要针对系统管理员和上级管理员),并且非成员
    if (!$perm && !C::t('organization')->ismember($gid, $uid, false)) {
        showmessage(lang('no_privilege'), dreferer());
    }
    if (!$fid) $fid = $group['fid'];
}
$folderinfo = C::t('folder')->fetch_folderinfo_by_fid($fid);
$patharr = getpath($folderinfo['path']);
$folderpathstr = implode('\\', $patharr);
require template('fileselection/content');
