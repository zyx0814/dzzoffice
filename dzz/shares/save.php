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
$sid = $_GET['sid'] ? dzzdecode($_GET['sid']) : '';
if (!$sid) {
    exit(json_encode(array('error' => 'Access Denied')));
}
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
$dzzrids = isset($_GET['dzzrids']) ? trim($_GET['dzzrids']) : '';
if (!$dzzrids) {
    exit(json_encode(array('error' => lang('no_file_selected'))));
}
if ($share['perm']) {
    $perms = array_flip(explode(',', $share['perm'])); // 将权限字符串转换为数组
    if (isset($perms[1])) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }
}
$icoids = explode(',', $dzzrids);
$data = array();
$fid = isset($_GET['fid']) ? intval($_GET['fid']) : 0;
foreach ($icoids as $icoid) {
    $rid = dzzdecode($icoid);
    if (empty($rid)) {
        exit(json_encode(array('error' => $rid . '：' . lang('forbid_operation'))));
    }
    $return = IO::CopyTo($rid, $fid, 1, true);
    if ($return['error']) {
        exit(json_encode(array('error' => $return['error'])));
    }
}
exit(json_encode(array('success' => lang('save_success'))));