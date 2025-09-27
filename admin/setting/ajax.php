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
} elseif ($operation == 'editusergroup') {
    $groupid = isset($_GET['groupid']) ? intval($_GET['groupid']) : 0;
    $group = array();
    if ($groupid > 0) {
        $group = DB::fetch_first("select f.*,g.grouptitle,g.type from %t f LEFT JOIN %t g ON g.groupid=f.groupid where f.groupid=%d", array('usergroup_field', 'usergroup', $groupid));
        if(!$group) showmessage('未查询到用户组信息');
    }
    if (isset($_GET['submit'])) {
        $groupfield = $_GET['group'];
        if(!is_array($groupfield)) showmessage('提交数据有误');
        $grouptitle = isset($_GET['grouptitle']) ? trim($_GET['grouptitle']) : '';
        if (!$grouptitle) {
            showmessage('请填写用户组名称');
        }
        if ($groupid > 0) {
            if ($group['grouptitle'] != $grouptitle) {
                if(DB::fetch_first('SELECT * FROM %t WHERE grouptitle=%s', array('usergroup', $grouptitle))) {
                    showmessage('用户组名称不能重复');
                }
            }
        } else {
            if(DB::fetch_first('SELECT * FROM %t WHERE grouptitle=%s', array('usergroup', $grouptitle))) {
                showmessage('用户组名称不能重复');
            }
        }
        
        
        $data = array(
            'grouptitle' => $grouptitle,
        );
        $updatecache = false;
        $setarr = array(
            'maxspacesize' => intval($groupfield['maxspacesize']),
            'maxattachsize' => intval($groupfield['maxattachsize']),
            'attachextensions' => trim($groupfield['attachextensions'])
        );
        $selperms = isset($_GET['perms']) ? $_GET['perms'] : '';
        $groupperm = 0;
        if (!empty($selperms)) {
            foreach ($selperms as $v) {
                $groupperm += $v;
            }
            $groupperm += 1;
        }
        $setarr['perm'] = $groupperm;
        if ($groupid > 0) {
            $editgid = C::t('usergroup')->update($groupid, $data);
            $usergroup = C::t('usergroup_field')->update($groupid, $setarr);
            if($editgid || $usergroup) $updatecache = true;
        } else {
            $newgid = C::t('usergroup')->insert($data, true);
            if($newgid) {
                $setarr['groupid'] = $newgid;
                C::t('usergroup_field')->insert($setarr);
                $updatecache = true;
            } else {
                showmessage('add_unsuccess');
            }
        }
        if($updatecache) {
            include_once libfile('function/cache');
            updatecache('usergroups');
        }
        exit(json_encode(array('success' => true)));
    } else {
        $perms = get_permsarray();//获取所有权限;
        $controlperms = get_permsarray('control');
        $userperms = get_permsarray('user');
        if(in_array($group['groupid'],array(1,2))) {
            $isadminperm = true;
        }
    }
} elseif ($operation == 'deleteusergroup') {
    $groupid = intval($_GET['groupid']);
    if (!$groupid) {
        showmessage('parameters_error');
    }
    if(!in_array($groupid, array('1','2','3','5','6','7','8','9'))) {
        $count = c::t('user')->count_by_groupid($groupid);
        if($count) {
            showmessage('该用户组当前有'.$count.'个用户正在使用，请先将用户移出该用户组后再删除');
        }
        C::t('usergroup')->delete($groupid, 'member');
        C::t('usergroup_field')->delete($groupid);
        include_once libfile('function/cache');
        updatecache('usergroups');
    } else {
        showmessage('核心组不允许删除');
    }
    exit(json_encode(array('success' => true)));
}
include template('ajax');