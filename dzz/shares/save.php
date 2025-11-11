<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2018/3/9
 * Time: 16:11
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
if (!$_G['uid']) {
    exit(json_encode(array('error' => '未登录，请先登录')));
}
$sid = $_GET['sid'] ? $_GET['sid'] : '';
if (!$sid) {
    exit(json_encode(array('error' => 'Access Denied')));
}
$sid = dzzdecode($sid);
$share = C::t('shares')->fetch($sid);
if (!$share) {
    exit(json_encode(array('error' => lang('share_file_iscancled'))));
}
if ($share['status'] == -4) exit(json_encode(array('error' => lang('shared_links_screened_administrator'))));
if ($share['status'] == -5) exit(json_encode(array('error' => lang('sharefile_isdeleted_or_positionchange'))));
//判断是否过期
if ($share['endtime'] && $share['endtime'] < TIMESTAMP) {
    exit(json_encode(array('error' => lang('share_link_expired'))));
}
if ($share['times'] && $share['times'] <= $share['count']) {
    exit(json_encode(array('error' => lang('link_already_reached_max_number'))));
}
if ($share['status'] == -3) {
    exit(json_encode(array('error' => lang('share_file_deleted'))));
}
if (empty($share['filepath'])) {
    exit(json_encode(array('error' => '分享路径无效')));
}
$dzzrids = isset($_GET['dzzrids']) ? trim($_GET['dzzrids']) : '';
if (!$dzzrids) {
    $dzzrids = $_GET['token']['paths'];
}
$download = 1;
if ($share['perm']) {
    $perms = array_flip(explode(',', $share['perm'])); // 将权限字符串转换为数组
    if (isset($perms[1])) {
        $download = 0; // 下载权限被禁用
    }
}
if (!$download) {
    exit(json_encode(array('error' => lang('no_privilege'))));
}
$icoids = explode(',', $dzzrids);
$data = array();
$ridarr = array();
$fid = isset($_GET['fid']) ? intval($_GET['fid']) : 0;
$folder = C::t('folder')->fetch($fid);
$explorer_setting = get_resources_some_setting();
$doing = true;
if ($folder['gid'] > 0) {
    $group = C::t('organization')->fetch($folder['gid']);
    if ($group['type'] == 0 && !$explorer_setting['orgonperm']) {
        $doing = false;
    } elseif ($group['type'] == 0 && !$explorer_setting['grouponperm']) {
        $doing = false;
    } elseif (!$group['manageon'] || !$group['diron']) {
        $doing = false;
    } elseif (!perm_check::checkperm_Container($fid, 'upload', '' , $folder['uid'])) {
        $doing = false;
    }
} else {
    if (!$explorer_setting['useronperm']) {
        $doing = false;
    }
}
if (!$doing) {
    $data['error'][$fid] = lang('no_privilege');
    $data['msg'][$fid] = 'error';
    $data['name'][$fid] = '';
    if (isset($_GET['token'])) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    } else {
        exit(json_encode($data));
    }
}

$totalsize = 0;
$icos = $folderids = array();
$i = 0;
$errorarr = array();
foreach ($icoids as $icoid) {
    $rid = dzzdecode($icoid);
    if (empty($rid)) {
        exit(json_encode(array('error' => $rid . '：' . lang('forbid_operation'))));
    }
    
    $return = IO::CopyTo($rid, $fid, 1);
    if ($return['error']) {
        exit(json_encode(array('error' => $return['error'])));
    }
    if ($return['success'] === true) {
        $data['icoarr'][] = $return['newdata'];
        if (!$tbz) {
            addtoconfig($return['newdata'], $ticoid);
        }
        $i++;
    } else {
        $errorarr[] = $return['error'];
    }
}
if (isset($_GET['token'])) {
    if (count($errorarr)) {
        exit(json_encode(array('error' => $errorarr[0])));
    } else {
        exit(json_encode(array('success' => lang('save_success'))));
    }
} else {
    exit(json_encode(array('success' => lang('save_success'))));
}