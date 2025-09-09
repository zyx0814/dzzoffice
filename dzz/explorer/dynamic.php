<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
include_once libfile('function/code');
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
$uid = $_G['uid'];
if ($do == 'filelist' && !$uid) {
    $errorResponse = [
        "code" => 1,
        "msg" => lang('no_login_operation'),
        "count" => 0,
        "data" => [],
    ];
    exit(json_encode($errorResponse));
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;

//获取文件夹右侧信息
if ($do == 'getfolderdynamic') {
    //接收文件夹id数据
    $bz = isset($_GET['bz']) ? trim($_GET['bz']) : '';
    $fileinfo = array();
    if($bz && $bz !== 'dzz') {
        $fileinfo=IO::getCloud($bz);
        $fileinfo['fname'] = $fileinfo['cloudname'];
        $fileinfo['type'] = $fileinfo['cloudtype'].'('.$fileinfo['name'].')';
        $fileinfo['realpath'] = $fileinfo['attachdir'];
        if ($fileinfo['uid']) {
            $user = getuserbyuid($fileinfo['uid']);
            if($user['uid']) {
                $fileinfo['username'] =  $user['username'];
            } else {
                $fileinfo['username'] = '该用户已不存在！';
            }
        } else {
            $fileinfo['username'] = '系统盘';
        }
        $fileinfo['fdateline'] = ($fileinfo['dateline']) ? dgmdate($fileinfo['dateline'], 'Y-m-d H:i:s') : '';;
        include template('right_folder_menu');
        exit();
    }
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    $fid = isset($_GET['fid']) ? trim($_GET['fid']) : '';
    $gid = 0;
    //默认动态查询条件
    $start = 0;
    $limit = 3;
    $next = false;
    $nextstart = $start + $limit;
    //如果获取到rid
    if ($rid) {
        //文件夹属性信息
        $fileinfo = C::t('resources')->get_property_by_rid($rid);
        //权限信息
        $perm = perm_check::getPerm($fileinfo['pfid']);//C::t('folder')->fetch_perm_by_fid($fileinfo['pfid']);//获取文件夹权限
        //动态信息
        $total = C::t('resources_event')->fetch_by_rid($rid, $start, $limit, true);
        if ($total > $nextstart) {
            $next = $nextstart;
        }
        if ($total) {
            $events = C::t('resources_event')->fetch_by_rid($rid, $start, $limit);
        }
        $gid = $fileinfo['gid'];
    } elseif ($fid) {//如果获取到文件夹id
        //文件夹信息
        $fileinfo = C::t('resources')->get_folderinfo_by_fid($fid);
        if (!$fileinfo['gid'] && ($fileinfo['uid'] !== $_G['uid'])) {
            return;
        }
        $gid = $fileinfo['gid'];
        if ($fileinfo['isgroup']) {
            $org = C::t('organization')->fetch($gid);
            if (!$org) return;
            //获取已使用空间
            $usesize = C::t('organization')->get_orgallotspace_by_orgid($gid, 0, false);
            //获取总空间
            if ($org['maxspacesize'] == 0) {
                $maxspace = 0;
            } else {
                if ($org['maxspacesize'] == -1) {
                    $maxspace = -1;
                } else {
                    $maxspace = $org['maxspacesize'] * 1024 * 1024;
                }
            }
            //成员信息
            $members = C::t('organization_user')->fetch_user_byorgid($gid);
            //处理成员头像函数
            $userids = array();
            foreach ($members as $k => $v) {
                $userids[] = $v['uid'];
            }
            $userstr = implode(',', $userids);
            $members = C::t('resources_event')->result_events_has_avatarstatusinfo($userids, $members);
        } elseif ($fileinfo['pfid'] == 0) {
            $spaceinfo = dzzgetspace($uid);
            $maxspace = $spaceinfo['maxspacesize'];
            $usesize = $spaceinfo['usesize'];
        }
        $progress = set_space_progress($usesize, $maxspace);
        //统计表数据
        $statis = C::t('resources_statis')->fetch_by_fid($fid);
        $fileinfo['opendateline'] = ($statis['opendateline']) ? dgmdate($statis['opendateline'], 'Y-m-d H:i:s') : '';
        $fileinfo['editdateline'] = ($statis['editdateline']) ? dgmdate($statis['editdateline'], 'Y-m-d H:i:s') : '';
        $fileinfo['fdateline'] = ($foldeinfo['dateline']) ? dgmdate($foldeinfo['dateline'], 'Y-m-d H:i:s') : '';
        $fileinfo['fid'] = $fid;
        $perm = perm_check::getPerm($fid);//C::t('folder')->fetch_perm_by_fid($fid);//获取文件夹权限
        //动态信息
        $total = C::t('resources_event')->fetch_by_pfid_rid($fid, true);
        //动态信息
        if ($total > $nextstart) {
            $next = $nextstart;
        }
        if($total) {
            $events = C::t('resources_event')->fetch_by_pfid_rid($fid, '', $start, $limit, '');
        }
    }
    $usergroupperm = C::t('organization_admin')->chk_memberperm($gid, $uid);
    $fileinfo['type'] = '文件夹';
    $perms = get_permsarray();//获取所有权限
    $commentperm = true;
    if (!perm_check::checkperm_Container($fid, 'comment')) {
        $commentperm = false;
    }
    $folderrid = C::t('resources')->fetch_rid_by_fid($fid);
    if($folderrid) {
        $fileinfo['rid'] = $folderrid;
        $filemeta = C::t('resources_meta')->fetch_by_key($folderrid,'desc', true);
        if($filemeta) $fileinfo['desc'] = $filemeta;
        $editperm = true;
        if (!perm_check::checkperm_Container($fid, 'edit2') && !($_G['uid'] == $fileinfo['uid'] && perm_check::checkperm_Container($fid, 'edit1'))) {
            $editperm = false;
        }
    }
    include template('right_folder_menu');
    exit();
} elseif ($do == 'getfiledynamic') {//获取文件或多文件右侧信息
    //文件信息或者动态请求
    $noselectnum = false;
    $operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
    $rids = isset($_GET['rid']) ? $_GET['rid'] : '';
    if (!is_array($rids)) $rids = explode(',', $rids);
    $ridnum = count($rids);
    //动态数据请求
    $start = 0;
    $limit = 3;
    $next = false;
    $nextstart = $start + $limit;
    $total = C::t('resources_event')->fetch_by_rid($rids, $start, $limit, true);
    if ($total > $nextstart) {
        $next = $nextstart;
    }
    if($total) {
        $events = C::t('resources_event')->fetch_by_rid($rids, $start, $limit);
    }
    //文件信息数据请求
    if ($ridnum == 1) {//如果只有一个选中项，判断是否是文件夹
        $rid = $rids[0];
        $file = C::t('resources')->fetch_info_by_rid($rid);
        if ($file['type'] == 'folder') {
            $gid = $file['gid'];
            $fileinfo = C::t('resources')->get_property_by_rid($rid);
            if ($fileinfo['isgroup']) {
                $org = C::t('organization')->fetch($gid);
                //获取已使用空间
                $usesize = C::t('organization')->get_orgallotspace_by_orgid($gid, 0, false);
                //获取总空间
                if ($org['maxspacesize'] == 0) {
                    $maxspace = 0;
                } else {
                    if ($org['maxspacesize'] == -1) {
                        $maxspace = -1;
                    } else {
                        $maxspace = $org['maxspacesize'] * 1024 * 1024;
                    }
                }
                //成员信息
                $members = C::t('organization_user')->fetch_user_byorgid($gid);
                //处理成员头像函数
                $userids = array();
                foreach ($members as $v) {
                    $userids[] = $v['uid'];
                }
                $userstr = implode(',', $userids);
                $members = C::t('resources_event')->result_events_has_avatarstatusinfo($userids, $members);
            }
            $usergroupperm = C::t('organization_admin')->chk_memberperm($gid, $uid);//获取用户权限
            $progress = set_space_progress($usesize, $maxspace);
            $perm = perm_check::getPerm($file['oid']);//C::t('folder')->fetch_perm_by_fid($file['oid']);//获取文件夹权限
            $fileinfo['fid'] = $file['oid'];
            $perms = get_permsarray();//获取所有权限
            $commentperm = true;
            if (!perm_check::checkperm_Container($file['oid'], 'comment')) {
                $commentperm = false;
            }
            $filemeta = C::t('resources_meta')->fetch_by_key($rid,'desc', true);
            if($filemeta) $fileinfo['desc'] = $filemeta;
            $editperm = true;
            if (!perm_check::checkperm_Container($file['oid'], 'edit2') && !($_G['uid'] == $fileinfo['uid'] && perm_check::checkperm_Container($file['oid'], 'edit1'))) {
                $editperm = false;
            }
            include template('right_folder_menu');
            exit();
        } else {
            $vstart = 0;
            $vlimit = 3;
            $vnext = false;
            $vnextstart = $vstart + $vlimit;
            $total = C::t('resources_version')->fetch_all_by_rid($rid, $vlimit, true);
            if ($total > $vnextstart) {
                $vnext = $vnextstart;
            }
            $versions = C::t('resources_version')->fetch_all_by_rid($rid, $vlimit, false);
            $fileinfo = C::t('resources')->get_property_by_rid($rid);
            if ($fileinfo['isdelete'] && $fileinfo['pfid'] == -1) {
                $pathrecord = DB::result_first("select pathinfo from %t where rid = %s", array('resources_recyle', $rid));
                $fileinfo['realpath'] = preg_replace('/dzz:(.+?):/', '', $pathrecord);
            }
            $fileinfo['dpath'] = dzzencode($rid);
            $pfid = $fileinfo['pfid'];
            $gid = $fileinfo['gid'];
            $versionnums = count($versions);
            $editperm = true;
            if (!perm_check::checkperm_Container($pfid, 'edit2') && !($_G['uid'] == $fileinfo['uid'] && perm_check::checkperm_Container($pfid, 'edit1'))) {
                $editperm = false;
            }
            $commentperm = true;
            if (!perm_check::checkperm_Container($pfid, 'comment')) {
                $commentperm = false;
            }
            $filemeta = C::t('resources_meta')->fetch_by_key($rid,'desc', true);
            if($filemeta) $fileinfo['desc'] = $filemeta;
            $tags = C::t('resources_tag')->fetch_tag_by_rid($rid);
            $explorer_setting = get_resources_some_setting();
            include template('right_menu');
            exit();
        }
    } elseif ($ridnum > 1) {//如果是多项选中，则调对应综合文件信息
        $fileinfo = C::t('resources')->get_property_by_rid($rids);
        include template('right_folder_menu');
        exit();
    }
} elseif ($do == 'loadmoredynamic') {//加载更多处理
    $ridval = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    $start = isset($_GET['next']) ? intval($_GET['next']) : 0;
    $tplmore = isset($_GET['adddynamisc']) ? $_GET['adddynamisc'] : 0;//判断是否为单独页动态1不是
    $limit = 15;
    if (!preg_match('/\w{32}/', $ridval)) {
        $pfid = intval($ridval);
        $rid = '';
    } else {
        $rids = isset($_GET['rid']) ? $_GET['rid'] : '';
        if (!is_array($rids)) $rids = explode(',', $rids);
        // $ridnum = count($rids);
        // if ($ridnum == 1) {//如果只有一个选中项，判断是否是文件夹
        //     $rid = $rids[0];
        //     $file = C::t('resources')->fetch_info_by_rid($rid);
        //     if ($file['type'] == 'folder') {
        //         $pfid = $file['oid'];
        //     }
        // }
    }
    $next = false;
    $nextstart = $start + $limit;
    if ($pfid) {
        //查询文件夹所有下级
        $total = C::t('resources_event')->fetch_by_pfid_rid($pfid, true, $start, $limit);
        if ($total) {
            $events = C::t('resources_event')->fetch_by_pfid_rid($pfid, false, $start, $limit);
        }
        if ($total > $nextstart) {
            $next = $nextstart;
        }
    } else {
        $total = C::t('resources_event')->fetch_by_rid($rids, $start, $limit, true);
        if ($total) {
            $events = C::t('resources_event')->fetch_by_rid($rids, $start, $limit);
        }
        if ($total > $nextstart) {
            $next = $nextstart;
        }
    }
    if ($tplmore) {//加载多条动态
        include template('template_dynamic_list');
        exit();
    } else {//加载单独动态页
        include template('template_more_dynamic');
        exit();
    }
    exit();
} elseif ($do == 'loadmoreversion') {
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    $fileinfo = C::t('resources')->get_property_by_rid($rid);
    $fileinfo['dpath'] = dzzencode($rid);
    $vstart = isset($_GET['next']) ? intval($_GET['next']) : 0;
    $vlimit = 20;
    $limit = ($vstart) ? $vstart . '-' . $vlimit : $vlimit;
    $vnext = false;
    $vnextstart = $vstart + $vlimit;

    // 判断是否有更多数据
    $total = C::t('resources_version')->fetch_all_by_rid($rid, '', true);
    if ($total > $vnextstart) {
        $vnext = $vnextstart;
    }
    $versions = C::t('resources_version')->fetch_all_by_rid($rid, $limit, false);
    if ($vstart) {//加载多条历史版本
        include template('template_historyversion_list');
        exit();
    } else {//加载单独历史版本页
        include template('historyversion_content');
    }
    exit();
} elseif ($do == 'filelist') {
    $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
    $field = isset($_GET['sort']) ? $_GET['sort'] : 'dateline';
    $limit = empty($_GET['limit']) ? 50 : $_GET['limit'];
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $page = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
    $start = ($page - 1) * $limit;
    $validfields = ['body_data', 'do_obj', 'do', 'username', 'dateline'];
    $validSortOrders = ['asc', 'desc'];
    if (in_array($field, $validfields) && in_array($order, $validSortOrders)) {
        $order = " ORDER BY e.$field $order";
    } else {
        $order = ' ORDER BY e.dateline DESC';
    }
    $condition = array();
    if (!empty($_GET['doevent'])) {
        $eventdo = trim($_GET['doevent']);
        if ($eventdo == 'recover' || $eventdo == 'recoverfile') {
            $condition['do'] = 'recover';
            $condition['do'] = 'recoverfile';
        } else {
            $condition['do'] = $eventdo;
        }
    }
    if (!empty($_GET['doobj'])) {
        $obj = trim($_GET['doobj']);
        $condition['do_obj'] = array($obj, 'like', 'and');
    }
    //开始时间
    if (!empty($_GET['startdate']) && $_GET['startdate']) {
        $startdate = strtotime($_GET['startdate']);
        $condition[] = array(' e.dateline > ' . $startdate, 'stringsql', 'and');
    }

    //结束时间
    if (!empty($_GET['enddate']) && $_GET['enddate']) {//结束时间+1天
        $enddate = strtotime($_GET['enddate']) + 86400;
        $condition[] = array(' e.dateline <' . $enddate, 'stringsql', 'and');
    }
    if (!empty($_GET['username'])) {
        $username = trim($_GET['username']);
        $condition['username'] = array($username, 'like', 'and');
    }
    if (!empty($_GET['uids'])) {
        $uids = $_GET['uids'];
        $condition['uidval'] = array($uids, 'nowhere');
    }
    $events = $list = array();
    $count = C::t('resources_event')->fetch_all_event($start, $limit, $condition, $order, true);
    if ($count) {
        $events = C::t('resources_event')->fetch_all_event($start, $limit, $condition, $order);
        foreach ($events as $data) {
            $list[] = [
                "username" => '<a href="user.php?uid=' . $data['uid'] . '" target="_blank">' . $data['username'] . '</a>',
                "do_lang" => $data['do_lang'],
                "do_obj" => $data['do_obj'],
                "body_data" => $data['details'],
                "do" => $data['do'],
                "dateline" => dgmdate($data['dateline'], 'Y-m-d H:i:s'),
            ];
        }
    }
    header('Content-Type: application/json');
    $return = [
        "code" => 0,
        "msg" => "",
        "count" => $count ? $count : 0,
        "data" => $list ? $list : [],
    ];
    $jsonReturn = json_encode($return);
    if ($jsonReturn === false) {
        $errorMessage = json_last_error_msg();
        $errorResponse = [
            "code" => 1,
            "msg" => "JSON 编码失败，请刷新重试: " . $errorMessage,
            "count" => 0,
            "data" => [],
        ];
        exit(json_encode($errorResponse));
    }
    exit($jsonReturn);
} elseif ($do == 'deletecomment') {
    $id = $_GET['id'];
    $return = C::t('resources_event')->delete_comment_by_id($id);
    if ($return['error']) {
        exit(json_encode(array('error' => $return['error'])));
    } else {
        exit(json_encode(array('success' => true)));
    }
}
$operation_type = array(
    array('addtag', lang('addtag')),
    array('edit', lang('edit')),
    array('down', lang('down')),
    array('create', lang('create')),
    array('recoverfile', lang('recoverfile')),
    array('movedfolder', lang('movedfolder')),
    array('movefile', lang('movefile')),
    array('update_groupname', lang('update_groupname')),
    array('update_setting', lang('update_setting')),
    array('delfolder', lang('delfolder')),
    array('delfile', lang('delfile')),
    array('deleteuser', lang('deleteuser')),
    array('deltag', lang('deltag')),
    array('delversion', lang('delversion')),
    array('finallydelete', lang('finallydelete')),
    array('updatevesion', lang('updatevesion')),
    array('setprimaryversion', lang('setprimaryversion')),
    array('editversionname', lang('editversionname')),
    array('editversiondesc', lang('editversiondesc')),
    array('rename', lang('rename')),
    array('share', lang('share')),
    array('cancleshare', lang('cancleshare')),
    array('addcomment', lang('addcomment')),
    array('adduser', lang('adduser')),
    array('setperm', lang('setperm')),
    array('update_perm', lang('update_perm'))
);
require template('dynamic_content');