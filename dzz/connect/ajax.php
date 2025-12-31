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
        exit(json_encode(['error' => '参数错误']));
    }
    if ($bz == 'dzz') {
        echo json_encode(['error' => lang('builtin_dish_allowed_delete')]);
        exit();
    }
    $cloud = DB::fetch_first("select * from %t where bz=%s", ['connect', $bz]);
    if (!$item = C::t($cloud['dname'])->fetch($id)) {
        echo json_encode(['error' => lang('object_exist_been_deleted')]);
        exit();
    }
    //查找icoid
    //判断删除权限
    if (!$_G['adminid']) {
        if ($item['uid'] != $_G['uid']) {
            echo json_encode(['error' => lang('no_privilege')]);
            exit();
        }
    }
    if ($re = C::t($cloud['dname'])->delete_by_id($item['id'])) {
        echo json_encode($re);
    } else {
        echo json_encode(['error' => lang('delete_unsuccess')]);
    }
    exit();
} elseif ($_GET['do'] == 'rename') {
    $return = [];
    $bz = isset($_GET['bz']) ? trim($_GET['bz']) : '';
    $id = isset($_GET['id']) ? intval($_GET['id']) : '';
    $name = isset($_GET['name']) ? trim($_GET['name']) : '';
    if (!$name || !$id || !$bz) {
        exit(json_encode(['error' => '参数错误']));
    }
    if ($bz == 'dzz') {
        if ($_G['adminid']) {
            C::t('connect')->update($bz, ['name' => $name]);
            exit(json_encode(['msg' => 'success']));
        } else {
            exit(json_encode(['error' => lang('no_privilege')]));
        }
    } else {
        $cloud = DB::fetch_first("select * from %t where bz=%s", ['connect', $bz]);
        if ($mycloud = C::t($cloud['dname'])->fetch($id)) {
            if ($mycloud['uid'] != $_G['uid'] && $_G['adminid'] != 1) {
                exit(json_encode(['error' => lang('no_privilege')]));
            } elseif (C::t($cloud['dname'])->update($id, ['cloudname' => $name])) {
                exit(json_encode(['msg' => 'success']));
            }
        }
        exit(json_encode(['error' => lang('rechristen_failure')]));
    }

}
include template("addcloud");

