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
include_once libfile('function/organization');

$do = trim($_GET['do']);
$orgid = intval($_GET['orgid']);
if ($do == 'upload') {//上传图片文件
    include libfile('class/uploadhandler');
    $options = array('accept_file_types' => '/\.(gif|jpe?g|png)$/i',
        'upload_dir' => $_G['setting']['attachdir'] . 'cache/',
        'upload_url' => $_G['setting']['attachurl'] . 'cache/',
        'thumbnail' => array('max-width' => 40, 'max-height' => 40));
    $upload_handler = new uploadhandler($options);
    exit();
} elseif ($do == 'getchildren') {
    $id = intval($_GET['id']);
    $list = array();
    $limit = 0;
    $html = '';

    //判断用户有没有操作权限
    if($id) {
        $ismoderator = C::t('organization_admin')->ismoderator_by_uid_orgid($id, $_G['uid']);
        if ($ismoderator) {
            $disable = '';
            $type = 'user';
        } else {
            $disable = '"disabled":true,';
            $type = "disabled";
        }
    }
    if ($id) {
        $icon = 'dzz/system/images/department.png';
    } else {
        $icon = 'dzz/system/images/organization.png';
    }
    $data = array();
    if ($_GET['id'] == '#') {
        if ($_G['adminid'] != 1) $topids = C::t('organization_admin')->fetch_toporgids_by_uid($_G['uid']);
        foreach (C::t('organization')->fetch_all_by_forgid($id, 0, 0) as $value) {
            if ($value['type'] == 1) continue;//过滤群
            if ($_G['adminid'] != 1 && !in_array($value['orgid'], $topids)) continue;
            if (C::t('organization_admin')->ismoderator_by_uid_orgid($value['orgid'], $_G['uid'])) {
                $orgdisable = false;
                $orgtype = 'organization';
            } else {
                $orgdisable = true;
                $orgtype = 'disabled';
            }
            $arr = array('id' => $value['orgid'], 'text' => $value['orgname'], 'icon' => $icon, 'state' => array('disabled' => $orgdisable), "type" => $orgtype, 'children' => true);
            if (intval($value['aid']) == 0) {
                $arr['text'] = avatar_group($value['orgid'], array($value['orgid'] => array('aid' => $value['aid'], 'orgname' => $value['orgname']))) . $value['orgname'];
                $arr['icon'] = false;
            } else {
                $arr['text'] = $value['orgname'];
                $arr['icon'] = 'index.php?mod=io&op=thumbnail&width=24&height=24&path=' . dzzencode('attach::' . $value['aid']);
            }
            $data[] = $arr;

        }

        if ($_G['adminid'] == 1) $data[] = array('id' => 'other', 'text' => lang('no_institution_users'), 'state' => array('disabled' => $disable), "type" => 'group');
    } else {
        //获取用户列表
        if ($id) {
            foreach (C::t('organization')->fetch_all_by_forgid($id) as $value) {
                if (C::t('organization_admin')->ismoderator_by_uid_orgid($value['orgid'], $_G['uid'])) {
                    $orgdisable = '';
                    $orgtype = 'organization';
                } else {
                    $orgdisable = '"disabled":true,';
                    $orgtype = 'disabled';
                }
                $arr = array('id' => $value['orgid'], 'text' => $value['orgname'], 'icon' => $icon, 'state' => array('disabled' => $orgdisable), "type" => $orgtype, 'children' => true);
                if (intval($value['aid']) == 0) {
                    $arr['text'] = avatar_group($value['orgid'], array($value['orgid'] => array('aid' => $value['aid'], 'orgname' => $value['orgname']))) . $value['orgname'];
                    $arr['icon'] = false;
                } else {
                    $arr['text'] = $value['orgname'];
                    $arr['icon'] = 'index.php?mod=io&op=thumbnail&width=24&height=24&path=' . dzzencode('attach::' . $value['aid']);
                }
                $data[] = $arr;
            }
        }

    }

    exit(json_encode($data));
} elseif ($do == 'getjobs') {
    $orgid = intval($_GET['orgid']);
    $jobs = C::t('organization_job')->fetch_all_by_orgid($orgid);
    $html = '<li><a href="javascript:;" class="dropdown-item" tabindex="-1" role="menuitem" _jobid="0" onclick="selJob(this)">' . lang('none') . '</a></li>';
    foreach ($jobs as $job) {
        $html .= '<li><a href="javascript:;" class="dropdown-item" tabindex="-1" role="menuitem" _jobid="' . $job['jobid'] . '" onclick="selJob(this)">' . $job['name'] . '</a></li>';
    }
    exit($html);
} elseif ($do == 'create') {
    $forgid = intval($_GET['forgid']);
    $borgid = intval($_GET['orgid']);
    //放在此部门后面
    if (!$ismoderator = C::t('organization_admin')->ismoderator_by_uid_orgid($forgid, $_G['uid'])) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }
    /*默认新建机构和部门开始群组manageon群组管理员开启 syatemon系统管理员开启 available 系统管理员开启共享目录,保留diron(群组管理员开启目录)控制是否开启目录显示在前台*/
    $setarr = array('forgid' => intval($_GET['forgid']), 'orgname' => lang('new_department'), 'fid' => 0, 'disp' => intval($_GET['disp']), 'indesk' => 0, 'dateline' => TIMESTAMP, 'available' => 1, 'syatemon' => 1, 'manageon' => 1, 'maxspacesize' => getglobal('orgmemorySpace', 'setting'));
    if ($setarr = C::t('organization')->insert_by_forgid($setarr, $borgid)) {
        include_once libfile('function/cache');
        updatecache('organization');
    } else {
        $setarr['error'] = 'create organization failure';
    }

    exit(json_encode($setarr));
} elseif ($do == 'rename') {
    $orgid = intval($_GET['orgid']);
    if (!$_GET['text']) {
        exit(json_encode(array('msg' => lang('name_cannot_empty'))));
    }
    if (!$ismoderator = C::t('organization_admin')->ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }
    if (C::t('organization')->update_by_orgid($orgid, array('orgname' => $_GET['text']))) {
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode(array('msg' => lang('rechristen_error'))));
    }
} elseif ($do == 'delete') {
    $forgid = intval($_GET['forgid']);
    if ($_GET['type'] == 'user') {//删除用户
        if ($_G['adminid'] != 1) exit(json_encode(array('error' => '删除用户只限系统管理员操作')));
        $uids = (array)$_GET['uids'];
        $uids = array_filter(array_map('intval', $uids));
        if (empty($uids)) {
            exit(json_encode(['error' => '请选择正确的用户']));
        }
        foreach ($uids as $uid) {
            C::t('user')->delete_by_uid($uid);
        }
        exit(json_encode(array('success' => true)));
    } else {
        $orgid = ($_GET['orgid']);
        if (!$ismoderator = C::t('organization_admin')->ismoderator_by_uid_orgid($forgid, $_G['uid'])) {
            exit(json_encode(array('error' => loang('no_privilege'))));
        }
        if ($return = C::t('organization')->delete_by_orgid($orgid)) {//删除部门，部门的用户移动到上级部门去;
            if ($return['error']) {
                exit(json_encode($return));
            }
            exit(json_encode(array('success' => true)));
        } else {
            exit(json_encode(array('msg' => lang('delete_error'))));
        }
    }
}  elseif ($do == 'remove') {
    $forgid = intval($_GET['forgid']);
    $uids = (array)$_GET['uids'];
    if ($forgid) {
        if($_G['adminid'] != 1) {
            if (!$ismoderator = C::t('organization_admin')->chk_memberperm($forgid, $_G['uid'])) {
                exit(json_encode(array('error' => lang('no_privilege'))));
            }
        }
        if (C::t('organization_user')->delete_by_uid_orgid($uids, $forgid)) {
            exit(json_encode(array('success' => true)));
        } else {
            exit(json_encode(array('msg' => '移除失败，用户不存在或您没有权限移除此用户。<br>如果您移除的用户是机构部门管理员，需要上一级机构部门管理员才能移除此用户')));
        }
    } else {
        exit(json_encode(array('error' => '请选择部门')));
    }
} elseif ($do == 'insert') {
    $uids = (array)$_GET['uids'];
    $orgids = (array)$_GET['orgids'];
    $uids = array_filter(array_map('intval', $uids));
    $orgids = array_filter(array_map('intval', $orgids));
    if (empty($orgids)) {
        exit(json_encode(['error' => '请选择正确的部门']));
    }
    if (empty($uids)) {
        exit(json_encode(['error' => '请选择正确的用户']));
    }
    if($_G['adminid'] != 1) {
        $orgAdminModel = C::t('organization_admin');
        foreach ($orgids as $orgid) {
            if (!$orgAdminModel->ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
                exit(json_encode(['error' => lang('no_privilege')]));
            }
        }
    }
    foreach ($uids as $uid) {
        foreach ($orgids as $orgid) {
            C::t('organization_user')->insert_by_orgid($orgid, $uid);
        }
    }
    exit(json_encode(array('success' => true)));
} elseif ($do == 'move') {
    if ($_GET['type'] == 'user') {//移动用户
        $orgid = intval($_GET['orgid']);
        $forgid = intval($_GET['forgid']);
        $uids = (array)$_GET['uids'];
        $uids = array_filter(array_map('intval', $uids));
        if (empty($uids)) {
            exit(json_encode(['error' => '请选择正确的用户']));
        }
        if(!$orgid) {
            exit(json_encode(['error' => '请选择正确的目标部门']));
        }
        if(!$forgid) {
            exit(json_encode(['error' => '请选择正确的原部门']));
        }
        if($_G['adminid'] != 1) {
            if (!C::t('organization_admin')->ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
                exit(json_encode(array('error' => '您没有目标部门的管理权限')));
            }
            if (!C::t('organization_admin')->ismoderator_by_uid_orgid($forgid, $_G['uid'])) {
                exit(json_encode(array('error' => '您没有原部门的管理权限')));
            }
        }
        $copy = intval($_GET['copy']);
        foreach ($uids as $uid) {
            if (C::t('organization_user')->move_to_by_uid_orgid($uid, $forgid, $orgid, $copy)) {
            } else {
                exit(json_encode(array('error' => lang('movement_error'))));
            }
        }
        exit(json_encode(array('success' => true)));
    } else {
        $orgid = intval($_GET['orgid']);
        $disp = intval($_GET['position']);
        $forgid = intval($_GET['forgid']);
        if (empty($forgid) || empty($orgid)) {
            exit(json_encode(['error' => '请先选择部门']));
        }
        if($_G['adminid'] != 1) {
            if (!C::t('organization_admin')->ismoderator_by_uid_orgid($forgid, $_G['uid'])) {
                exit(json_encode(array('error' => lang('no_privilege'))));
            }
        }
        if (C::t('organization')->setDispByOrgid($orgid, $disp, $forgid)) {//移动部门;
            exit(json_encode(array('success' => true)));
        } else {
            exit(json_encode(array('msg' => lang('delete_error'))));
        }
    }
} elseif ($do == 'jobedit') {
    $jobid = intval($_GET['jobid']);
    $orgid = intval($_GET['orgid']);
    if($_G['adminid'] != 1) {
        if (!C::t('organization_admin')->ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
            exit(json_encode(array('error' => lang('no_privilege'))));
        }
    }
    $name = str_replace('...', '', getstr($_GET['name'], 30));
    if (C::t('organization_job')->update($jobid, array('name' => $name))) {
        exit(json_encode(array('jobid' => $jobid, 'name' => $name)));
    } else {
        exit(json_encode(array('error' => lang('edit_error'))));
    }
} elseif ($do == 'jobdel') {
    $jobid = intval($_GET['jobid']);
    $orgid = intval($_GET['orgid']);
    if($_G['adminid'] != 1) {
        if (!C::t('organization_admin')->ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
            exit(json_encode(array('error' => lang('no_privilege'))));
        }
    }
    if (C::t('organization_job')->delete($jobid)) {
        exit(json_encode(array('jobid' => $jobid)));
    } else {
        exit(json_encode(array('error' => lang('delete_unsuccess'))));
    }
} elseif ($do == 'jobadd') {
    $orgid = intval($_GET['orgid']);
    if($_G['adminid'] != 1) {
        if (!C::t('organization_admin')->ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
            exit(json_encode(array('error' => lang('no_privilege'))));
        }
    }
    $name = str_replace('...', '', getstr($_GET['name'], 30));
    $setarr = array('orgid' => $orgid, 'name' => $name, 'dateline' => TIMESTAMP, 'opuid' => $_G['uid']);
    $setarr['jobid'] = C::t('organization_job')->insert_job_by_name($orgid, $name, $_G['uid']);
    if ($setarr['jobid']) {
        exit(json_encode($setarr));
    } else {
        exit(json_encode(array('error' => lang('add_unsuccess'))));
    }
} elseif ($do == 'folder_available') {
    $orgid = intval($_GET['orgid']);
    if (!C::t('organization_admin')->ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }
    if (C::t('organization')->setFolderAvailableByOrgid($orgid, intval($_GET['available']))) {
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode(array('error' => lang('unable_set') . '，如果上级没有开启目录共享，下级无法开启')));
    }
} elseif ($do == 'folder_indesk') {
    $orgid = intval($_GET['orgid']);
    if (!C::t('organization_admin')->ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }
    if (C::t('organization')->setIndeskByOrgid($orgid, intval($_GET['indesk']))) {
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode(array('error' => lang('no_open_Shared_directory'))));
    }
} elseif ($do == 'set_org_orgname') {
    $orgid = intval($_GET['orgid']);
    if(!$orgid) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }
    $orgname = isset($_GET['orgname']) ? trim($_GET['orgname']) : '';
    if(!$orgname) {
        exit(json_encode(array('error' => lang('name_cannot_empty'))));
    }

    if (!C::t('organization_admin')->ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }
    if (C::t('organization')->update_by_orgid($orgid, array('orgname' => $orgname))) {
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode(array('error' => lang('rechristen_error'))));
    }
} elseif ($do == 'set_org_logo') {
    $orgid = intval($_GET['orgid']);
    if(!$orgid) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }
    $img = intval(($_GET['aid']));
    if (!C::t('organization_admin')->ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }
    if (C::t('organization')->update_by_orgid($orgid, array('aid' => $img))) {
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode(array('error' => lang('rechristen_error'))));
    }
} elseif ($do == 'set_org_desc') {
    $orgid = intval($_GET['orgid']);
    if(!$orgid) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }
    $desc = getstr($_GET['desc']);

    if (!C::t('organization_admin')->ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }
    if (C::t('organization')->update_by_orgid($orgid, array('desc' => $desc))) {
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode(array('error' => lang('rechristen_error'))));
    }
} elseif ($do == 'group_on') {
    if (!C::t('organization_admin')->ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }
    if (C::t('organization')->setgroupByOrgid($orgid, intval($_GET['available']))) {
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode(array('error' => lang('unable_set'))));
    }
} elseif ($do == 'orginfo') {
    $orgid = intval($_GET['orgid']);
    if(!$orgid) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }
    $adminModel = C::t('organization_admin');
    if (!$adminModel->ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }
    $uids = array();
    $uid_arr = explode(',', $_GET['uids']);
    foreach ($uid_arr as $value) {
       if (strpos($value, 'uid_') == 0) {
            $pure_uid = intval(str_replace('uid_', '', $value));
            if ($pure_uid > 0) {
                $uids[] = $pure_uid;
            }
        }
    }
    $frontend_uids = array_unique($uids);
    $existing_uids = $adminModel->fetch_uids_by_orgid($orgid);
    $add_uids = array_diff($frontend_uids, $existing_uids); // 需要新增的UID
    $delete_uids = array_filter(array_diff($existing_uids, $frontend_uids)); // 需要删除的UID
    if (!empty($add_uids)) {
        $add_count = 0;
        foreach ($add_uids as $uid) {
            if ($setarr['id'] = $adminModel->insert($uid, $orgid)) {
                $add_count++;
            } else {
                exit(json_encode(array('error' => lang('add_administrator_unsuccess'))));
            }
        }
    }
    if (!empty($delete_uids)) {
        $delete_count = 0;
        foreach ($delete_uids as $uid) {
            if (C::t('organization_admin')->delete_by_uid_orgid($uid, $orgid)) {
                $delete_count++;
            } else {
                exit(json_encode(array('error' => lang('add_administrator_unsuccess'))));
            }
        }
    }
    exit(json_encode(array('success' => true)));
} elseif ($do == 'folder_maxspacesize') {
    $orgid = intval($_GET['orgid']);
    $setspacesize = intval($_GET['maxspacesize']);
    if (!$org = C::t('organization')->fetch($orgid)) {
        exit(json_encode(array('error' => '该机构或群组不存在或被删除')));
    }
    //暂时只允许系统管理员进行空间相关设置
    if ($_G['adminid'] != 1) {
        exit(json_encode(array('error' => '没有权限')));
    }
    if ($setspacesize != 0) {
        //获取允许设置的空间值
        $allowallotspace = C::t('organization')->get_allowallotspacesize_by_orgid($orgid);
        if ($allowallotspace < 0) {
            exit(json_encode(array('error' => '可分配空间不足')));
        }

        //获取当前已占用空间大小
        $currentallotspace = C::t('organization')->get_orgallotspace_by_orgid($orgid, 0, false);
        //设置值小于当前下级分配总空间值即：当前设置值 < 下级分配总空间
        if ($setspacesize > 0 && $setspacesize * 1024 * 1024 < $currentallotspace) {
            exit(json_encode(array('error' => '设置空间值不足,小于已分配空间值！', 'val' => $org['maxspacesize'])));
        }
        //上级包含空间限制时，无限制不处理，直接更改设置值
        if ($allowallotspace > 0 && ($setspacesize * 1024 * 1024 > $allowallotspace)) {
            exit(json_encode(array('error' => '总空间不足！', 'val' => $org['maxspacesize'])));
        }
    }
    //设置新的空间值
    if (C::t('organization')->update($orgid, array('maxspacesize' => $setspacesize))) {
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode(array('error' => '设置不成功或未更改', 'val' => $org['maxspacesize'])));
    }
} elseif ($do == 'guide') {
    include template('guide');
} elseif ($do == 'userstatus') { 
    $uid = intval($_GET['uid']);
    if (!$uid) {
        exit(json_encode(array('error' => '用户不存在')));
    }
    if($uid == $_G['uid']) {
        exit(json_encode(array('error' => '不能操作自己')));
    }
    $uperm = false;
    if ($_G['adminid'] != 1) {
        if ($orgids_uid = C::t('organization_user')->fetch_orgids_by_uid($uid)) {
            foreach ($orgids_uid as $orgid) {
                if (C::t('organization_admin')->ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
                    $uperm = true;
                    break;
                }
            }
            if (!$uperm) exit(json_encode(array('error' => lang('no_privilege'))));
        } else {
            exit(json_encode(array('error' => lang('no_privilege'))));
        }
    }
    //禁用创始人验证
    $status = intval($_GET['status']) ? 1 : 0;
    $user = C::t('user')->fetch_by_uid($uid);
    if ($status == 1 && C::t('user')->checkfounder($user)) {
        exit(json_encode(array('error' => lang('is_root_user'))));
    }
    $setarr = array('status' => intval($_GET['status']));
    if(C::t('user')->update($uid, $setarr)) {
        if ($setarr['status'] != $user['status']) {
            logStatusChange($uid, $user['status'], $setarr['status']);
        }
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode(array('error' => lang('do_failed'))));
    }
} elseif ($do == 'set') {
    if ($_G['adminid'] != 1) {
        showmessage('no_privilege');
    }
    if (submitcheck('confirmsubmit')) {
        include_once libfile('function/cache');
        $org_import_user = intval($_GET['org_import_user']);
        C::t('setting')->update('org_import_user', $org_import_user);
        updatecache('setting');
        exit(json_encode(array('success' => true)));
    } else {
        include template('ajax');
    }
} elseif ($do == 'getParentsArr') {
    $gid = intval($_GET['gid']);
    $ret = array();
    if ($gid) {
        foreach (C::t('organization')->fetch_parent_by_orgid($gid) as $orgid) {
            $arr[] = $orgid;
        }
        $arr = array_unique($arr);
    }
    exit(json_encode($arr));
} elseif ($do == 'usergroupid') {
    $uids = is_array($_GET['uids']) ? $_GET['uids'] : explode(',', $_GET['uids']);
    if (!$uids) {
        exit(json_encode(array('error' => '用户不存在')));
    }
    if($_G['adminid'] != 1) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }
    $groupid = intval($_GET['groupid']);
    if (!$groupid) {
        exit(json_encode(array('error' => '用户组不存在')));
    }
    foreach ($uids as $v) {
        C::t('user')->setAdministror($v, $groupid);
    }
    exit(json_encode(array('success' => true)));
}
exit();
?>