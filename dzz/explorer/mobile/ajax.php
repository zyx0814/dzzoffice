<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;
$uid = $_G['uid'];
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
if ($operation == 'createFolder') {
    $fid = isset($_GET['fid']) ? intval($_GET['fid']) : '';
    $folderinfo = C::t('folder')->fetch($fid);
    $perm = 0;
    if ($folderinfo['gid'] && C::t('organization_admin')->chk_memberperm($folderinfo['gid'])) {
        $perm = DB::result_first("select perm from %t where fid = %d", array('folder', $fid));
    }
    $name = !empty($_GET['foldername']) ? trim($_GET['foldername']) : lang('newfolder');
    $fname = io_dzz::name_filter(getstr($name, 80));
    if ($arr = IO::CreateFolder($fid, $fname, $perm)) {
        if ($arr['error']) {
        } else {
            $arr = array_merge($arr['icoarr'], $arr['folderarr']);
            $arr['msg'] = 'success';
        }
    } else {
        $arr = array();
        $arr['error'] = lang('failure_newfolder');
    }
    exit(json_encode($arr));
} elseif ($operation == 'uploadfiles') {
    $container = trim($_GET['container']);
    $space = dzzgetspace($uid);
    $space['self'] = intval($space['self']);
    $bz = trim($_GET['bz']);
    require_once dzz_libfile('class/UploadHandler');
    //上传类型
    $allowedExtensions = $space['attachextensions'] ? explode(',', $space['attachextensions']) : array();

    $sizeLimit = ($space['maxattachsize']);

    $options = array('accept_file_types' => $allowedExtensions ? ("/(\.|\/)(" . implode('|', $allowedExtensions) . ")$/i") : "/.+$/i",
        'max_file_size' => $sizeLimit ? $sizeLimit : null,
        'upload_dir' => $_G['setting']['attachdir'] . 'cache/',
        'upload_url' => $_G['setting']['attachurl'] . 'cache/',
    );
    $upload_handler = new UploadHandler($options);
    exit();
} elseif ($operation == 'collect') {
    $paths = $_GET['paths'];
    //collect参数为1为收藏,否则为取消收藏,未接收到此参数，默认为收藏
    $collect = isset($_GET['collect']) ? $_GET['collect'] : 1;
    $rids = array();
    foreach ($paths as $v) {
        $rids[] = dzzdecode($v);
    }
    if ($collect) {//加入收藏
        $return = C::t('resources_collect')->add_collect_by_rid($rids);
        exit(json_encode($return));
    } else {//取消收藏
        $return = C::t('resources_collect')->delete_usercollect_by_rid($rids);
        exit(json_encode($return));
    }
} elseif ($operation == 'addgroupuser') {//添加群组成员
    $gid = isset($_GET['gid']) ? intval($_GET['gid']) : '';
    //检查群组是否存在
    if (!$group = C::t('organization')->fetch($gid)) {
        exit(json_encode(array('error' => lang('group_not_exists'))));
    }
    //检测管理权限
    if (!$perm = C::t('organization_admin')->chk_memberperm($gid, $uid)) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }
    if (!$perm || !$group['type']) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }
    //添加或修改用户时

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

        foreach (C::t('organization_user')->delete_by_uid_orgid($removeuser, $gid) as $v) {
            if ($v['uid'] != getglobal('uid')) {
                $notevars = array(
                    'from_id' => $appid,
                    'from_idtype' => 'app',
                    // 'url' => getglobal('siteurl') . '/#group&gid='.$orgid,
                    'author' => getglobal('username'),
                    'authorid' => getglobal('uid'),
                    'dataline' => dgmdate(TIMESTAMP),
                    'fname' => getstr($group['orgname'], 31),
                );
                $action = 'explorer_user_remove';
                $ntype = 'explorer_user_remove_' . $gid;

                dzz_notification::notification_add($v['uid'], $ntype, $action, $notevars, 1, 'dzz/explorer');
            }
        }
        //增加事件
        $eventdata = array('username' => getglobal('username'), 'uid' => getglobal('uid'), 'orgname' => $group['orgname'], 'delusers' => implode(',', $delusers));
        C::t('resources_event')->addevent_by_pfid($group['fid'], 'delete_group_user', 'deleteuser', $eventdata, $gid, '', $group['orgname']);
    }
    //新添加用户
    $insertuserdata = array();
    $insertusername = array();
    foreach ($uids as $v) {
        if (!in_array($v, $olduids) && !empty($v)) {
            $insertuser[] = $v;
            $insertusername[] = $userarr[$v];
            $insertuserdata[] = array('uid' => $v, 'username' => $userarr[$v], 'ufirst' => new_strsubstr(ucfirst($userarr[$v]), 1, ''));
        }

    }
    //添加用户
    if (count($insertuser) > 0) {
        $permtitle = lang('explorer_gropuperm');
        foreach (C::t('organization_user')->insert_by_orgid($gid, $insertuser) as $iu) {
            //发送通知
            if ($iu != getglobal('uid')) {
                $notevars = array(
                    'from_id' => $appid,
                    'from_idtype' => 'app',
                    'url' => getglobal('siteurl') . MOD_URL . '#group&gid=' . $gid,
                    'author' => getglobal('username'),
                    'authorid' => getglobal('uid'),
                    'dataline' => dgmdate(TIMESTAMP),
                    'fname' => getstr($group['orgname'], 31),
                    'permtitle' => $permtitle[0]
                );
                $action = 'explorer_user_add';
                $ntype = 'explorer_user_add_' . $gid;
                dzz_notification::notification_add($iu, $ntype, $action, $notevars, 1, 'dzz/explorer');
            }
        }
        $insertuserdata = C::t('resources_event')->result_events_has_avatarstatusinfo($insertuser, $insertuserdata);
        //增加事件
        $eventdata = array('username' => getglobal('username'), 'uid' => getglobal('uid'), 'orgname' => $group['orgname'], 'insertusers' => implode(',', $insertusername));
        C::t('resources_event')->addevent_by_pfid($group['fid'], 'add_group_user', 'adduser', $eventdata, $gid, '', $group['orgname']);
    }
    if ($type == 1) {
        exit(json_encode(array('success' => true, 'insertuser' => $insertuserdata, 'delusers' => $delusers, 'adminid' => ($_G['adminid'] == 1) ? 1 : 0, 'perm' => $perm, 'grouptype' => $group['type'])));
    } else {
        exit(json_encode(array('success' => true, 'fid' => $group['fid'])));
    }
} elseif ($operation == 'share') {
    $defer = dreferer();
    $files = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    $shareid = isset($_GET['shareid']) ? intval($_GET['shareid']) : '';
    if ($shareid) {
        if ($share = C::t('shares')->fetch($shareid)) {
            $edit = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
            $share['sharelink'] = C::t('shorturl')->getShortUrl($_G['siteurl'].'index.php?mod=shares&sid='.dzzencode($shareid));
            $share['fdateline'] = dgmdate($share['dateline'], 'Y-m-d H:i:s');
            $share['password'] = ($share['password']) ? dzzdecode($share['password']) : '';
            $sid = dzzencode($share['id']);
            if (is_file($_G['setting']['attachdir'] . './qrcode/' . $sid[0] . '/' . $sid . '.png')) {
                $share['qrcode'] = $_G['setting']['attachurl'] . './qrcode/' . $sid[0] . '/' . $sid . '.png';
            } else {
                $share['qrcode'] = C::t('shares')->getQRcodeBySid($sid);
            }
            if ($share['endtime']) {
                $timediff = ($share['endtime'] - $share['dateline']);
                $days = 0;
                if ($timediff > 0) {
                    $days = ceil($timediff / 86400);
                }
                $share['expireday'] = ($days > 0) ? $days . '天后' : '已过期';
            } else {
                $share['expireday'] = '永久有效';
            }
            $rids = explode(',', $share['filepath']);
            if (count($rids) > 1) {
                $share['img'] = '/dzz/explorer/img/ic-files.png';
            } else {
                $share['img'] = C::t('resources')->get_icosinfo_by_rid($share['filepath']);
            }

            $files = $share['filepath'];
        }
        if ($edit) {
            require template('mobile/share_edit');
        } else {
            require template('mobile/share_detail');
        }
        exit();
    } else {
        //如果已经存在该分享查询分享数据
        if ($share = C::t('shares')->fetch_by_path($files)) {
            $share['sharelink'] = C::t('shorturl')->getShortUrl($_G['siteurl'].'index.php?mod=shares&sid='.dzzencode($share['id']));
            $share['fdateline'] = dgmdate($share['dateline'], 'Y-m-d H:i:s');
            $share['password'] = ($share['password']) ? dzzdecode($share['password']) : '';
            $sid = dzzencode($share['id']);
            if (is_file($_G['setting']['attachdir'] . './qrcode/' . $sid[0] . '/' . $sid . '.png')) {
                $share['qrcode'] = $_G['setting']['attachurl'] . './qrcode/' . $sid[0] . '/' . $sid . '.png';
            } else {
                $share['qrcode'] = C::t('shares')->getQRcodeBySid($sid);
            }
            if ($share['endtime']) {
                $timediff = ($share['endtime'] - $share['dateline']);
                $days = 0;
                if ($timediff > 0) {
                    $days = ceil($timediff / 86400);
                }
                $share['expireday'] = ($days > 0) ? $days . '天后' : '已过期';
            } else {
                $share['expireday'] = '永久有效';
            }
            $rids = explode(',', $share['filepath']);
            if (count($rids) > 1) {
                $share['img'] = '/dzz/explorer/img/ic-files.png';
            } else {
                $share['img'] = C::t('resources')->get_icosinfo_by_rid($share['filepath']);
            }

            $files = $share['filepath'];
            require template('mobile/share_detail');
            exit();
        } else {//不存在该分享获取分享默认标题
            $rids = explode(',', $files);
            //默认单个文件分享
            $more = false;
            //多个文件分享
            if (count($rids) > 1) $more = true;
            $filenames = array();
            $gidarr = array();
            foreach (DB::fetch_all("select pfid,name,gid from %t where rid in(%n)", array('resources', $rids)) as $v) {
                if (!perm_check::checkperm_Container($v['pfid'], 'share')) {
                    $arr = array('error' => lang('no_privilege'));
                } else {
                    $gidarr[] = $v['gid'];
                    $filenames[] = $v['name'];
                }
            }
            //判断文件来源
            if (count(array_unique($gidarr)) > 1) {
                $arr = array('error' => lang('share_notallow_from_different_zone'));
            }
            //自动生成分享标题
            if ($more) {
                $share['title'] = $filenames[0] . lang('more_file_or_folder');
            } else {
                $share['title'] = $filenames[0];
            }
        }
        require template('mobile/share_edit');
        exit();
    }
} elseif ($operation == 'editshare') {
    $defer = dreferer();
    $share = $_GET['share'];
    $share['title'] = getstr($share['title']);
    if ($share['endtime']) $share['endtime'] = strtotime($share['endtime']) + 24 * 60 * 60;
    if ($share['password']) $share['password'] = dzzencode($share['password']);
    $share['times'] = intval($share['times']);
    if (isset($_GET['shareid']) && $_GET['shareid']) $id = intval($_GET['shareid']);
    $share['filepath'] = trim($_GET['rid']);
    if ($id) {
        if ($ret = C::t('shares')->update_by_id($id, $share)) {
            exit(json_encode((array('success' => true, 'rid' => $share['filepath']))));
        } else {
            exit(json_encode((array('error' => lang('create_share_failer') . '！'))));
        }
    } else {
        $ret = C::t('shares')->insert($share);
        if ($ret['success']) {
            exit(json_encode((array('success' => true, 'rid' => $share['filepath']))));
        } else {
            exit(json_encode((array('error' => lang('create_share_failer') . '！'))));
        }
    }
} elseif ($operation == 'delshare') {//删除分享
    $id = isset($_GET['id']) ? intval($_GET['id']) : '';
    $return = C::t('shares')->delete_by_id($id);
    if ($return['success']) {
        showTips(array('success' => true));
    } else {
        showTips(array('error' => $return['error']));
    }
}