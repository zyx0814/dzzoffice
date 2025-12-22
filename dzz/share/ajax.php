<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('adminlogin');
$do = isset($_GET['do']) ? $_GET['do'] : '';
if ($do == 'delete') {
    $sids = $_GET['sids'];
    $return = array();
    foreach ($sids as $v) {
        $result = C::t('shares')->delete_by_id($v);
        if ($result['error']) {
            exit(json_encode(array('msg' => $result['error'])));
        }
    }
    exit(json_encode(array('success' => true)));
} elseif ($do == 'forbidden') {
    $sids = $_GET['sids'];
    if (!$sids) {
        showmessage('forbid_operation');
    }
    if ($_GET['flag'] == 'forbidden') {
        $status = -4;
        $msg = lang('share_screen_success');
    } else {
        $status = 0;
        $msg = lang('cancel_shielding_success');
    }
    if ($sids && C::t('shares')->update($sids, array('status' => $status))) {
        exit(json_encode(array('success' => true, 'msg' => $msg)));
    } else {
        showmessage('share_screen_failure');
    }
} elseif ($do == 'shareinfo') {
    $sid = intval($_GET['sid']);
    if (!$sid) {
        showmessage('forbid_operation');
    }
    $share = C::t('shares')->fetch($sid);
    if (!$share['id']) {
        showmessage('share_file_iscancled');
    }
    $sharestatus = array(
        '-5' => lang('sharefile_isdeleted_or_positionchange'),
        '-4' => '<span class="layui-badge">' . lang('been_blocked') . '</span>',
        '-3' => '<span class="layui-badge">' . lang('file_been_deleted') . '</span>',
        '-2' => '<span class="layui-badge layui-bg-gray">' . lang('degree_exhaust') . '</span>',
        '-1' => '<span class="layui-badge layui-bg-gray">' . lang('logs_invite_status_4') . '</span>',
        '0' => '<span class="layui-badge layui-bg-blue">' . lang('founder_upgrade_normal') . '</span>'
    );
    $share['endtime'] = getexpiretext($share['endtime']);
    $share['sharelink'] = C::t('shorturl')->getShortUrl('index.php?mod=shares&sid=' . dzzencode($share['id']));
    $share['dateline'] = $share['dateline'] ? dgmdate($share['dateline'],'Y-m-d H:i:s') : '';
    $share['password'] = $share['password'] ? dzzdecode($share['password']) : lang('open_links');
    $share['times'] = $share['times'] ? $share['count'] . '/' . $share['times'] : lang('no_limit');
    $share['status'] = $sharestatus[$share['status']];
    $share['type'] = getFileTypeName($share['type'], $share['ext']);
    include template('ajax');
    exit();
} else {
    showmessage('forbid_operation');
}
?>
