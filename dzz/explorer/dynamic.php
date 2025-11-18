<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
include_once libfile('function/code');
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
$uid = $_G['uid'];
if ($do == 'filelist' && !$_G['uid']) {
    $errorResponse = [
        "code" => 1,
        "msg" => lang('no_login_operation'),
        "count" => 0,
        "data" => [],
    ];
    exit(json_encode($errorResponse));
}
Hook::listen('check_login');
//获取文件夹右侧信息
if ($do == 'getfiledynamic') {//获取文件或多文件右侧信息
    $bz = isset($_GET['bz']) ? trim($_GET['bz']) : '';
    $fileinfo = array();
    if($bz && $bz !== 'dzz') {
        $fileinfo=IO::getCloud($bz);
        if (!$fileinfo) {
            exit(json_encode(array('error' => lang('cloud_no_info'))));
        }
        if($fileinfo['available']<1) {
            exit(json_encode(array('error' => lang('cloud_no_available'))));
        }
        $fileinfo['fname'] = $fileinfo['cloudname'];
        $fileinfo['ftype'] = $fileinfo['cloudtype'].'('.$fileinfo['name'].')';
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
        $fileinfo['fdateline'] = ($fileinfo['dateline']) ? dgmdate($fileinfo['dateline'], 'Y-m-d H:i:s') : '';
        $info = lang('no_dynamisc');
        include template('right_menu');
        exit();
    }
    $rids = isset($_GET['rid']) ? $_GET['rid'] : '';
    $fid = isset($_GET['fid']) ? trim($_GET['fid']) : '';
    if (!is_array($rids)) $rids = explode(',', $rids);
    $ridnum = count($rids);
    //文件信息数据请求
    if ($ridnum == 1) {//如果只有一个选中项，判断是否是文件夹
        $start = 0;
        $limit = 3;
        $next = false;
        $nextstart = $start + $limit;
        $rid = $rids[0];
        if ($fid) {
            //文件夹信息
            $fileinfo = C::t('resources')->get_property_by_fid($fid);
            if($fileinfo['error']) showmessage($fileinfo['error']);
            if ($fileinfo['isgroup']) {
                $org = C::t('organization')->fetch($fileinfo['gid']);
                if ($org) {
                    //获取已使用空间
                    $usesize = C::t('organization')->get_orgallotspace_by_orgid($fileinfo['gid'], 0, false);
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
                }
                $progress = set_space_progress($usesize, $maxspace);
            } elseif ($fileinfo['pfid'] == 0) {
                $spaceinfo = dzzgetspace($_G['uid']);
                $maxspace = $spaceinfo['maxspacesize'];
                $usesize = $spaceinfo['usesize'];
                $progress = set_space_progress($usesize, $maxspace);
            }
            $total = C::t('resources_event')->fetch_by_pfid_rid($fid, true);
            if ($total > $nextstart) {
                $next = $nextstart;
            }
            if($total) {
                $events = C::t('resources_event')->fetch_by_pfid_rid($fid, '', $start, $limit, '');
            } else {
                $info = lang('no_dynamisc');
            }
        } else {
            $fileinfo = C::t('resources')->get_property_by_rid($rid);
            if($fileinfo['error']) showmessage($fileinfo['error']);
            $vnext = false;
            $total = C::t('resources_version')->fetch_all_by_rid($rid, $limit, true);
            if ($total > $nextstart) {
                $vnext = $nextstart;
            }
            if($total) {
                $versions = C::t('resources_version')->fetch_all_by_rid($rid, $limit, false);
                $versionnums = count($versions);
            }
            $tags = C::t('resources_tag')->fetch_tag_by_rid($rid);
            $total = C::t('resources_event')->fetch_by_rid($fileinfo['rid'], $start, $limit, true);
            if ($total > $nextstart) {
                $next = $nextstart;
            }
            if($total) {
                $events = C::t('resources_event')->fetch_by_rid($fileinfo['rid'], $start, $limit);
            } else {
                $info = lang('no_dynamisc');
            }
        }
        if($fileinfo['rid']) {
            $filemeta = C::t('resources_meta')->fetch_by_key($rid,'desc', true);
            if($filemeta) $fileinfo['desc'] = htmlspecialchars($filemeta);
        }
        if($fileinfo['gid']) {
            $usergroupperm = perm_check::checkgroupPerm($fileinfo['gid'], 'admin');//判断管理员权限
        }
        $myperm = perm_check::getridPerm($fileinfo);
        //获取所有权限
        if ($fileinfo['isfolder']) {
            $perms = get_permsarray();
        } else {
            $perms = get_permsarray('document');
        }
        include template('right_menu');
        exit();
    } elseif ($ridnum > 1) {//如果是多项选中，则调对应综合文件信息
        $fileinfo = C::t('resources')->get_property_by_rid($rids);
        if($fileinfo['error']) showmessage($fileinfo['error']);
        include template('right_menu');
        exit();
    }
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