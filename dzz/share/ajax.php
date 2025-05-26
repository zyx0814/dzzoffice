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
if (!$_G['uid']) exit(json_encode(array('error' => lang('no_login_operation'))));
if ($_GET['do'] == 'delete') {
    $sids = $_GET['sids'];
    $return = array();
    foreach ($sids as $v) {
        if (!$_G['adminid']) {
            $suid = DB::result_first("select uid from %t where id=%s", array('shares', $v));
            if ($suid !== $_G['uid']) {
                exit(json_encode(array('error' => '该分享文件不存在或您没有权限')));
            }
        }
        $result = C::t('shares')->delete_by_id($v);
        if ($result['error']) {
            exit(json_encode(array('error' => $result['error'])));
        }
    }
    exit(json_encode(array('success' => true)));
} elseif ($_GET['do'] == 'forbidden' && $_G['adminid']) {
    $sids = $_GET['sids'];
    if (!$sids) {
        exit(json_encode(array('error' => '非法操作')));
    }
    if ($_GET['flag'] == 'forbidden') {
        $status = -4;
        $msg = lang('share_screen_success');
    } else {
        $status = 0;
        $msg = lang('cancel_shielding_success');
    }
    if ($sids && C::t('shares')->update($sids, array('status' => $status))) {
        exit(json_encode(array('msg' => $msg)));
    } else {
        exit(json_encode(array('error' => lang('share_screen_failure'))));
    }
} else {
    exit(json_encode(array('error' => '非法操作')));
}
?>
