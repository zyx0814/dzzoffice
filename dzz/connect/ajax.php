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
if ($_GET['do'] == 'delete') {
    $bz = isset($_GET['bz']) ? trim($_GET['bz']) : '';
    $id = isset($_GET['id']) ? intval($_GET['id']) : '';
    if (!$id || !$bz) {
        exit(json_encode(array('error' => '参数错误')));
    }
    if ($bz == 'dzz') {
        echo json_encode(array('error' => lang('builtin_dish_allowed_delete')));
        exit();
    }
    $cloud = DB::fetch_first("select * from %t where bz=%s", array('connect', $bz));
    if (!$item = C::t($cloud['dname'])->fetch($id)) {
        echo json_encode(array('error' => lang('object_exist_been_deleted')));
        exit();
    }
    //查找icoid
    //判断删除权限
    if (!$_G['adminid']) {
        if ($item['uid'] != $_G['uid']) {
            echo json_encode(array('error' => lang('no_privilege')));
            exit();
        }
    }
    if ($re = C::t($cloud['dname'])->delete_by_id($item['id'])) {
        echo json_encode($re);
    } else {
        echo json_encode(array('error' => lang('delete_unsuccess')));
    }
    exit();
} elseif ($_GET['do'] == 'rename') {
    $return = array();
    $bz = isset($_GET['bz']) ? trim($_GET['bz']) : '';
    $id = isset($_GET['id']) ? intval($_GET['id']) : '';
    $name = isset($_GET['name']) ? trim($_GET['name']) : '';
    if (!$name || !$id || !$bz) {
        exit(json_encode(array('error' => '参数错误')));
    }
    if ($bz == 'dzz') {
        if ($_G['adminid']) {
            C::t('connect')->update($bz, array('name' => $name));
            echo json_encode(array('msg' => 'success'));
            exit();
        } else {
            echo json_encode(array('error' => lang('no_privilege')));
            exit();
        }
    } else {
        $cloud = DB::fetch_first("select * from %t where bz=%s", array('connect', $bz));
        if ($mycloud = C::t($cloud['dname'])->fetch($id)) {
            if ($mycloud['uid'] != $_G['uid'] && $_G['adminid'] != 1) {
                echo json_encode(array('error' => lang('no_privilege')));
                exit();
            } elseif (C::t($cloud['dname'])->update($id, array('cloudname' => $name))) {
                echo json_encode(array('msg' => 'success'));
                exit();
            }
        }
        echo json_encode(array('error' => lang('rechristen_failure')));
        exit();
    }

}
include template("addcloud");
?>
