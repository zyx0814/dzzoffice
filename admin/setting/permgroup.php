<?php
/* @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {
    exit('Access Denied');
}
$uid = $_G['uid'];
$perms = get_permsarray();//获取所有权限;
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if ($do == 'addpermgroup') {
    $pername = isset($_GET['pername']) ? trim($_GET['pername']) : '';
    if (preg_match('/^\s*$/', $pername)) {
        exit(json_encode(['error' => '权限组名称不能为空']));
    } else {
        if (C::t('resources_permgroup')->fetch_by_name($pername)) {
            exit(json_encode(['error' => '权限组名称不能重复']));
        }

    }
    $allperms = $perms;
    $perms = isset($_GET['perms']) ? $_GET['perms'] : '';

    $groupperm = 0;
    foreach ($perms as $v) {
        $groupperm += $v;
    }
    if (!$groupperm) {
        exit(json_encode(['error' => '请勾选权限']));
    }
    $setarr = [
        'pername' => $pername,
        'perm' => $groupperm,
        'default' => isset($_GET['default']) ? intval($_GET['default']) : 0
    ];
    if ($insert = C::t('resources_permgroup')->insert($setarr)) {
        $selectperm = [];
        foreach ($allperms as $k => $v) {
            if ($v[1] & $setarr['perm']) {
                $selectperm[] = $v[2];
            }
        }
        exit(json_encode(['success' => ['id' => $insert, 'pername' => $setarr['pername'], 'perm' => $selectperm, 'default' => $setarr['default']]]));
    } else {
        exit(json_encode(['error' => '添加权限组失败']));
    }
} elseif ($do == 'editpermgroup_off') {
    $id = intval($_GET['id']);
    $off = intval($_GET['off']);
    $return = C::t('resources_permgroup')->update_off_status($id, $off);
    if ($return['success']) {
        showTips(['success' => true], 'json');
    } else {
        showTips(['error' => true], 'json');
    }
} elseif ($do == 'setdefault') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : '';
    if (C::t('resources_permgroup')->setdefault_by_id($id)) {
        showTips(['success' => true], 'json');
    } else {
        showTips(['error' => true], 'json');
    }
} elseif ($do == 'deleteperm') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : '';
    if (C::t('resources_permgroup')->delete_by_id($id)) {
        showTips(['success' => true], 'json');
    } else {
        showTips(['error' => true], 'json');
    }
}