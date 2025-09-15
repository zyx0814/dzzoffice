<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/12/26
 * Time: 11:38
 */
if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {
    exit('Access Denied');
}
global $_G;
$uid = $_G['uid'];
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
if ($operation == 'editpermgroup') {//编辑权限组
    if ($_G['adminid'] != 1) exit(json_encode(array('error' => lang('no_privilege'))));
    $perms = get_permsarray();//获取所有权限;
    if (isset($_GET['submit'])) {
        $id = isset($_GET['id']) ? intval($_GET['id']) : '';
        $groupperminfo = C::t('resources_permgroup')->fetch($id);
        $permname = isset($_GET['pername']) ? trim($_GET['pername']) : '';
        if (preg_match('/^\s*$/', $permname)) {
            exit(json_encode(array('error' => '权限组名称不能为空')));
        } else {
            if ($groupperminfo['pername'] != $permname && C::t('resources_permgroup')->fetch_by_name($permname)) {
                exit(json_encode(array('error' => '权限组名称不能重复')));
            }

        }
        $selperms = isset($_GET['perms']) ? $_GET['perms'] : '';
        $groupperm = 0;
        if (!empty($selperms)) {
            foreach ($selperms as $v) {
                $groupperm += $v;
            }
            $groupperm += 1;
        }
        if (!$groupperm) {
            exit(json_encode(array('error' => '请勾选权限')));
        }
        $setarr = array(
            'pername' => $permname,
            'perm' => $groupperm,
            'default' => isset($_GET['default']) ? intval($_GET['default']) : 0
        );
        if (C::t('resources_permgroup')->update_by_id($id, $setarr)) {
            $selectperm = array();
            foreach ($perms as $k => $v) {
                if ($v[1] & $setarr['perm']) {
                    $selectperm[] = $v[2];
                }
            }
            showTips(array('success' => array('id' => $id, 'pername' => $setarr['pername'], 'perm' => $selectperm, 'default' => $setarr['default'], 'off' => $groupperminfo['off'])));
        } else {
            showTips(array('error' => true));
        }

    } else {
        $id = isset($_GET['id']) ? intval($_GET['id']) : '';
        if (!$groupperm = C::t('resources_permgroup')->fetch($id)) {
            exit(json_encode(array('error' => '权限组不存在或已经被删除')));
        }
    }
}
include template('ajax');