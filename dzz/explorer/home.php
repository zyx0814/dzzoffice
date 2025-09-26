<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;
$uid = $_G['uid'];
if (!$folder = C::t('folder')->fetch_home_by_uid($uid)) {
    showmessage('sorry_userfile_not_exsists', dreferer());
}
$explorer_setting = get_resources_some_setting();
if (!$explorer_setting['useronperm']) {
    showmessage('no_privilege', dreferer());
}
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
$fid = isset($_GET['fid']) ? intval($_GET['fid']) : $folder['fid'];
if ($folderinfo = C::t('folder')->fetch_folderinfo_by_fid($fid)) {
    if (!$folderinfo['gid'] && (empty($_G['uid']) || !preg_match('/^dzz:uid_(\d+):/', $folderinfo['path'], $matches) || $matches[1] != $_G['uid'])) {
        showmessage('no_privilege', dreferer());
    }
    $folderpatharr = getpath($folderinfo['path']);
    $folderpathstr = implode('\\', $folderpatharr);
    //统计打开次数
    if ($rid = C::t('resources')->fetch_rid_by_fid($fid)) {
        $setarr = array(
            'uid' => $uid,
            'views' => 1,
            'opendateline' => TIMESTAMP,
            'fid' => $fid
        );
        C::t('resources_statis')->add_statis_by_rid($rid, $setarr);
    } else {
        $setarr = array(
            'uid' => $uid,
            'views' => 1,
            'opendateline' => TIMESTAMP
        );
        C::t('resources_statis')->add_statis_by_fid($fid, $setarr);
    }
}
require template('mydocument_content');