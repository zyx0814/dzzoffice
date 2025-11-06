<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
$gid = isset($_GET['gid']) ? $_GET['gid'] : '';
//群组信息
if (!$group = C::t('organization')->fetch($gid)) {
    showmessage('no_group', dreferer());
}
//获取群组成员权限
$perm = C::t('organization_admin')->chk_memberperm($gid, $uid);
//判断群组是否开启，如果未开启(共享目录)并且不是管理员不能访问
if (!$group['diron'] && !$perm) {
    showmessage('no_privilege', dreferer());
}
//判断是否有权限访问群组，如果不是管理员权限(主要针对系统管理员和上级管理员),并且非成员
if (!$perm && !C::t('organization')->ismember($gid, $uid, false)) {
    showmessage('no_privilege', dreferer());
}

$perms = get_permsarray();//获取所有权限

$explorer_setting = get_resources_some_setting();
if ($group['type'] == 1 && !$explorer_setting['grouponperm']) {
    showmessage('no_privilege', dreferer());
}
if ($group['type'] == 0 && !$explorer_setting['orgonperm']) {
    showmessage('no_privilege', dreferer());
}
$contenterrormsg = '';
if (!$group['syatemon']) {
    showmessage('no_group_by_system', dreferer());
}
if (!$group['manageon'] && $perm < 1) {
    showmessage('no_privilege', dreferer());
}

if (!$group['available']) {
    $contenterrormsg = lang('group_no_file_by_system');
} else {
    if (!$group['diron'] && !$perm) {
        $contenterrormsg = ($group['type'] > 0) ? lang('group_no_file_by_manage') : lang('group_no_file_by_system');
    }
}
$allowvisit = array('file', 'group_ajax', 'right_popbox', 'delete_group');
$do = isset($_GET['do']) ? trim($_GET['do']) : 'file';
if ($do == 'delete_group') {
    if ($group['type'] == 0 && $_G['adminid'] != 1) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }
    if ($group['type'] == 1 && $perm < 2) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }
    $return = C::t('organization')->delete_by_orgid($gid);
    if (isset($return['error'])) {
        exit(json_encode(array('error' => $return['error'])));
    } else {
        exit(json_encode(array('success' => true)));
    }
} elseif ($do == 'file') {
    $fid = isset($_GET['fid']) ? intval($_GET['fid']) : '';
    if (!$fid) $fid = $group['fid'];
    if ($folderinfo = C::t('folder')->fetch_folderinfo_by_fid($fid)) {
        if (!$folderinfo['gid'] && (empty($_G['uid']) || !preg_match('/^dzz:uid_(\d+):/', $folderinfo['path'], $matches) || $matches[1] != $_G['uid'])) {
            showmessage('no_privilege', dreferer());
        }
        $folderpatharr = getpath($folderinfo['path']);
        $folderpathstr = implode('\\', $folderpatharr);
        //统计打开次数,如果当前文件夹在resources表无数据，则记录其文件夹id对应数据
        if ($rid = C::t('resources')->fetch_rid_by_fid($fid)) {
            $setarr = array(
                'uid' => $uid,
                'views' => 1,
                'opendateline' => TIMESTAMP,
                'fid' => $fid
            );
            C::t('resources_statis')->add_statis_by_rid($rid, $setarr);
        } else {
            $setarr = array(
                'uid' => $uid,
                'views' => 1,
                'opendateline' => TIMESTAMP,
            );
            C::t('resources_statis')->add_statis_by_fid($fid, $setarr);
        }
    }
} elseif ($do == 'group_ajax') {
    $operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
    if ($operation == 'addgroupuser') {//添加群组成员
        $gid = isset($_GET['gid']) ? intval($_GET['gid']) : '';
        if (!$perm || !$group['type']) {
            return array('error' => lang('no_privilege'));
        }
        //添加或修改用户时
        if (submitcheck('selectsubmit')) {
            $uidarr = explode(',', trim($_GET['uids']));
            $uids = array();
            $userarr = array();
            foreach ($uidarr as $v) {
                $uids[] = preg_replace('/uid_/', '', $v);
            }
            $type = intval($_GET['type']) ? 1 : 0;
            //获取群组原用户数据
            $olduids = C::t('organization_user')->fetch_uids_by_orgid($gid);

            //获取管理员数据
            $adminer = C::t('organization_admin')->fetch_uids_by_orgid($gid);

            $getuserids = array_merge($olduids, $uids);

            //获取用户数据
            foreach (DB::fetch_all("select username,uid from %t where uid in(%n)", array('user', $getuserids)) as $v) {
                $userarr[$v['uid']] = $v['username'];
            }

            //删除用户
            $removeuser = array();
            $insertuser = array();

            foreach ($olduids as $v) {
                if (!in_array($v, $uids) && ($uid != $v || ($uid == $v && $_G['adminid'] == 1))) {
                    $removeuser[] = $v;
                }
            }
            $delusers = array();
            //判断删除用户权限并删除用户
            if (count($removeuser) > 0) {
                foreach ($removeuser as $k => $v) {
                    $uperm = C::t('organization_admin')->chk_memberperm($gid, $v);
                    //如果是系统管理员
                    if ($_G['adminid'] == 1) {
                        if (($group['type'] == 1 && $uperm > 1 && $_G['uid'] != $v)) {
                            unset($removeuser[$k]);
                            continue;
                        } else {
                            $delusers[$v] = $userarr[$v];
                        }
                    } else {
                        //如果操作对象是管理员,并且操作的是群组当前用户不是创建人或者机构,不允许操作
                        if (in_array($v, $adminer) && (($group['type'] == 1 && $perm < 2) || $group['type'] == 0)) {
                            unset($removeuser[$k]);
                            continue;
                        } else {
                            $delusers[$v] = $userarr[$v];
                        }
                    }
                }
            }
            $appid = C::t('app_market')->fetch_appid_by_mod('{dzzscript}?mod=' . MOD_NAME, 2);
            if (count($removeuser) > 0) {

                foreach (C::t('organization_user')->delete_by_uid_orgid($removeuser, $gid) as $uid) {
                    if ($uid != $_G['uid']) {
                        $notevars = array(
                            'from_id' => $appid,
                            'from_idtype' => 'app',
                            // 'url' => getglobal('siteurl') . '/#group&gid='.$orgid,
                            'author' => $_G['username'],
                            'authorid' => $_G['uid'],
                            'dataline' => dgmdate(TIMESTAMP),
                            'fname' => getstr($group['orgname'], 31),
                        );
                        $action = 'explorer_user_remove';
                        $ntype = 'explorer_user_remove_' . $gid;

                        dzz_notification::notification_add($uid, $ntype, $action, $notevars, 1, 'dzz/explorer');
                    }
                }
                //增加事件
                $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($group['fid'], $gid);
                $eventdata = array('username' => $_G['username'], 'uid' => $_G['uid'], 'orgname' => $group['orgname'], 'delusers' => implode(',', $delusers), 'hash' => $hash);
                C::t('resources_event')->addevent_by_pfid($group['fid'], 'delete_group_user', 'deleteuser', $eventdata, $gid, '', $group['orgname']);
            }
            //新添加用户
            $insertusername = array();
            foreach ($uids as $v) {
                if (!in_array($v, $olduids) && !empty($v)) {
                    $insertuser[] = $v;
                    $insertusername[] = $userarr[$v];
                }

            }
            //添加用户
            if (count($insertuser) > 0) {
                $permtitle = lang('explorer_gropuperm');
                foreach (C::t('organization_user')->insert_by_orgid($gid, $insertuser) as $iu) {
                    //发送通知
                    if ($iu != $_G['uid']) {
                        $notevars = array(
                            'from_id' => $appid,
                            'from_idtype' => 'app',
                            'url' => $_G['siteurl'] . MOD_URL . '#group&gid=' . $gid,
                            'author' => $_G['username'],
                            'authorid' => $_G['uid'],
                            'dataline' => dgmdate(TIMESTAMP),
                            'fname' => getstr($group['orgname'], 31),
                            'permtitle' => $permtitle[0]
                        );
                        $action = 'explorer_user_add';
                        $ntype = 'explorer_user_add_' . $gid;
                        dzz_notification::notification_add($iu, $ntype, $action, $notevars, 1, 'dzz/explorer');
                    }
                }
                //增加事件
                $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($group['fid'], $gid);
                $eventdata = array('username' => $_G['username'], 'uid' => $_G['uid'], 'orgname' => $group['orgname'], 'insertusers' => implode(',', $insertusername), 'hash' => $hash);
                C::t('resources_event')->addevent_by_pfid($group['fid'], 'add_group_user', 'adduser', $eventdata, $gid, '', $group['orgname']);
            }
            if ($type == 1) {
                exit(json_encode(array('success' => true)));
            } else {
                exit(json_encode(array('success' => true, 'fid' => $group['fid'])));
            }
        }
    } elseif ($operation == 'groupsetting') {
        $gid = $_GET['gid'];
        if (!$perm || !$group['type']) {
            return array('error' => lang('no_privilege'));
        }
        if (isset($_GET['setsubmit'])) {
            $arr = $_GET['arr'];
            if ($arr['diron']) {
                $arr['diron'] = 1;
            } else {
                $arr['diron'] = 0;
            }
            $return = C::t('organization')->update_by_orgid($gid, $arr);
            if ($return['error']) {
                showTips(array('error' => $return['error']), 'json');
            } else {
                showTips(array('success' => true), 'json');
            }

        } else {
            //$group = C::t('organization')->fetch($gid);
            $grouppatharr = getpath($groupinfo['path']);
            $grouppathstr = implode('\\', $grouppatharr);
        }
    } elseif ($operation == 'getAtData') {
        $gid = isset($_GET['gid']) ? intval($_GET['gid']) : '';
        $fid = isset($_GET['fid']) ? intval($_GET['fid']) : '';
        $keyword = isset($_GET['term']) ? trim($_GET['term']) : '';
        if (!$fid) {
            $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
            $fileinfo = C::t('resources')->fetch_info_by_rid($rid);
            if ($fileinfo['type'] == 'folder') {
                $fid = $fileinfo['oid'];
            } else {
                $fid = $fileinfo['pfid'];
            }
        }
        $perm = DB::result_first("select perm_inherit from %t where fid = %d", array('folder', $fid));
        $powerarr = perm_binPerm::getPowerArr();
        $uids = array();
        if ($perm & $powerarr['read2']) {
            $members = C::t('organization_user')->fetch_parentadminer_andchild_uid_by_orgid($gid, true);
            $uids = $members['all'];
        } else {
            $members = C::t('organization_user')->fetch_parentadminer_andchild_uid_by_orgid($gid, false);
            $uids = $members['adminer'];
        }

        $params = array('user', $uids);
        $sql_user = 'where uid in(%n) ';
        if ($keyword) {
            $sql_user .= ' and username like %s';
            $params[] = '%' . $keyword . '%';
        }
        $list = array();
        foreach (DB::fetch_all("select uid,username  from %t   $sql_user", $params) as $value) {
            if ($value['uid'] == $uid) continue;
            $list[] = array('name' => $value['username'],
                'searchkey' => pinyin::encode($value['username'], 'all') . $value['username'],
                'id' => 'u' . $value['uid'],
                'title' => $value['username'] . ':' . 'u' . $value['uid'],
                'avatar' => avatar_block($value['uid'])
            );
        }
        exit(json_encode($list));
    }
    include template('group_ajax');
    exit();
} elseif ($do == 'right_popbox') {
    $uuid = $_GET['uid'];
    //成员相关信息
    $userinfos = DB::fetch_first("select u.username, u.uid,u.adminid from %t u where u.uid = %d", array('user', $uuid));
    $uperm = DB::fetch_first("select admintype from %t  where uid = %d and orgid = %d", array('organization_admin', $uuid, $gid));
    if($uperm['admintype']) {
        $userinfos['perm'] = $uperm['admintype'];
    } elseif ($userinfos['adminid'] == 1) {
        $userinfos['perm'] = 1;
    } else {
        $userinfos['perm'] = 0;
    }
    $allowoperation = array('setmemberperm', 'deletemember');
    if ($operation && !in_array($operation, $allowoperation)) {
        showmessage('explorer_do_failed', dreferer());
    }
    $operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
    if ($operation && !in_array($operation, $allowoperation)) {
        showmessage('explorer_do_failed', dreferer());
    }

    if ($operation == 'setmemberperm') {
        $guid = isset($_GET['guid']) ? intval($_GET['guid']) : '';
        $perm = isset($_GET['perm']) ? intval($_GET['perm']) : '';
        $appid = C::t('app_market')->fetch_appid_by_mod('{dzzscript}?mod=' . MOD_NAME, 2);
        $return = C::t('organization_user')->set_admin_by_giduid($guid, $gid, $perm);
        if ($return['success']) {
            $appid = C::t('app_market')->fetch_appid_by_mod('{dzzscript}?mod=' . MOD_NAME, 2);
            $permtitle = lang('explorer_gropuperm');
            if ($guid != $_G['uid']) {
                $notevars = array(
                    'from_id' => $appid,
                    'from_idtype' => 'app',
                    'url' => $_G['siteurl'] . MOD_URL . '/#group&gid=' . $gid,
                    'author' => $_G['username'],
                    'authorid' => $_G['uid'],
                    'dataline' => dgmdate(TIMESTAMP),
                    'fname' => getstr($group['orgname'], 31),
                    'permtitle' => $permtitle[$perm],
                );
                $action = 'explorer_user_change';
                $type = 'explorer_user_change_' . $gid;

                dzz_notification::notification_add($guid, $type, $action, $notevars, 1, 'dzz/explorer');
                if ($return['olduser']) {
                    $notevars = array(
                        'from_id' => $appid,
                        'from_idtype' => 'app',
                        'url' => $_G['siteurl'] . MOD_URL . '#group&gid=' . $gid,
                        'author' => $_G['username'],
                        'authorid' => $_G['uid'],
                        'dataline' => dgmdate(TIMESTAMP),
                        'fname' => getstr($group['orgname'], 31),
                        'permtitle' => $permtitle[0],
                    );
                    $action = 'explorer_user_change';
                    $type = 'explorer_user_change_' . $gid;
                    dzz_notification::notification_add($return['olduser']['uid'], $type, $action, $notevars, 1, 'dzz/explorer');

                }
            }
            $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($group['fid'], $gid);
            if ($perm == 2) {
                $body_data = array('username' => $_G['username'], 'oldusername' => $return['olduser']['username'], 'groupname' => $group['orgname'], 'newusername' => $return['member'], 'hash' => $hash);
                $event_body = 'change_creater';
            } else {
                $body_data = array('username' => $_G['username'], 'groupname' => $group['orgname'], 'permname' => $permtitle[$perm], 'member' => $return['member'], 'hash' => $hash);

                $event_body = 'update_member_perm';
            }
            C::t('resources_event')->addevent_by_pfid($group['fid'], $event_body, 'update_perm', $body_data, $gid, '', $group['orgname']);//记录事件
        }
        exit(json_encode($return));
    } elseif ($operation == 'deletemember') {
        $guid = isset($_GET['uids']) ? $_GET['uids'] : '';
        $deluids = C::t('organization_user')->delete_by_uid_orgid($guid, $gid, 1);
        if ($deluids) {
            $appid = C::t('app_market')->fetch_appid_by_mod('{dzzscript}?mod=explorer', 2);
            foreach ($deluids as $uid) {
                if ($uid != $_G['uid']) {
                    $notevars = array(
                        'from_id' => $appid,
                        'from_idtype' => 'app',
                        // 'url' => getglobal('siteurl') . '/#group&gid='.$orgid,
                        'author' => $_G['username'],
                        'authorid' => $_G['uid'],
                        'dataline' => dgmdate(TIMESTAMP),
                        'fname' => getstr($group['orgname'], 31),
                    );
                    $action = 'explorer_user_remove';
                    $type = 'explorer_user_remove_' . $gid;

                    dzz_notification::notification_add($uid, $type, $action, $notevars, 1, 'dzz/explorer');
                }
            }
            $deluserarr = array();
            foreach (DB::fetch_all("select username from %t where uid in(%n)", array('user', $deluids)) as $v) {
                $deluserarr[] = $v['username'];
            }
            //增加事件
            $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($group['fid'], $gid);
            $eventdata = array('username' => $_G['username'], 'uid' => $_G['uid'], 'orgname' => $group['orgname'], 'delusers' => implode(',', $deluserarr), 'hash' => $hash);
            C::t('resources_event')->addevent_by_pfid($group['fid'], 'delete_group_user', 'deleteuser', $eventdata, $gid, '', $group['orgname']);
        }

        exit(json_encode(array('success' => true, 'uids' => $deluids)));
    } else {
        include template('template_right_popbox');
    }
    exit();
}
include template('mydocument_content');
exit();
