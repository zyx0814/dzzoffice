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
if ($_G['adminid'] != 1) exit(json_encode(array('error' => lang('no_privilege'))));
if (!$_G['uid']) exit(json_encode(array('error' => lang('no_login_operation'))));
if ($_GET['do'] == 'delete') {
    $sids = $_GET['sids'];
    $return = array();
    foreach ($sids as $v) {
        $result = C::t('shares')->delete_by_id($v);
        if ($result['error']) {
            exit(json_encode(array('error' => $result['error'])));
        }
    }
    exit(json_encode(array('success' => true)));
} elseif ($_GET['do'] == 'forbidden') {
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
