<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
include_once libfile('function/code');
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;
$uid = $_G['uid'];
$do = isset($_GET['do']) ? trim($_GET['do']) : '';

//获取文件夹右侧信息
if ($do == 'getfolderdynamic') {
    //接收文件夹id数据
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
        $userperm = perm_check::getPerm($fileinfo['pfid']);//获取用户权限
        $perm = C::t('folder')->fetch_perm_by_fid($fileinfo['pfid']);//获取文件夹权限
        //动态信息
        if (C::t('resources_event')->fetch_by_rid($rid, $start, $limit, true) > $nextstart) {
            $next = $nextstart;
        }
        $gid = $fileinfo['gid'];
        $events = C::t('resources_event')->fetch_by_rid($rid, $start, $limit);
    } elseif ($fid) {//如果获取到文件夹id
        //文件夹信息
        $fileinfo = C::t('resources')->get_folderinfo_by_fid($fid);
        if(!$fileinfo['gid'] && ($fileinfo['uid'] !== $_G['uid'])){
            return;
        }
        $gid = $fileinfo['gid'];
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
        //权限数据
        $userperm = perm_check::getPerm($fid);//获取用户权限
        $perm = C::t('folder')->fetch_perm_by_fid($fid);//获取文件夹权限
        //动态信息
        if (C::t('resources_event')->fetch_by_pfid_rid($fid, true) > $nextstart) {
            $next = $nextstart;
        }
        $events = C::t('resources_event')->fetch_by_pfid_rid($fid, '', $start, $limit, '');
    }
    $usergroupperm = C::t('organization_admin')->chk_memberperm($gid, $uid);
    $fileinfo['type'] = '文件夹';
    $perms = get_permsarray();//获取所有权限
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
    if (C::t('resources_event')->fetch_by_rid($rids, $start, $limit, true) > $nextstart) {
        $next = $nextstart;
    }
    $events = C::t('resources_event')->fetch_by_rid($rids, $start, $limit);
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
            $perm = C::t('folder')->fetch_perm_by_fid($file['oid']);//获取文件夹权限
            $fileinfo['fid'] = $file['oid'];
            $perms = get_permsarray();//获取所有权限
            include template('right_folder_menu');
            exit();
        } else {
            $vstart = 0;
            $vlimit = 3;
            $vnext = false;
            $vnextstart = $vstart + $vlimit;
            if (C::t('resources_version')->fetch_all_by_rid($rid, $vlimit, true) > $vnextstart) {
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
} elseif
($do == 'loadmoredynamic'
) {//加载更多处理
    $next = false;
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
        $ridnum = count($rids);
        if ($ridnum == 1) {//如果只有一个选中项，判断是否是文件夹
            $rid = $rids[0];
            $file = C::t('resources')->fetch_info_by_rid($rid);
            if ($file['type'] == 'folder') {
                $pfid = $file['oid'];
            }
        }
    }
    $next = false;
    $nextstart = $start + $limit;
    if ($pfid) {
        //查询文件夹所有下级
        if (C::t('resources_event')->fetch_by_pfid_rid($pfid, true, $start, $limit) > $nextstart) {
            $next = $nextstart;
        }
        $events = C::t('resources_event')->fetch_by_pfid_rid($pfid, false, $start, $limit);
    } else {
        if (C::t('resources_event')->fetch_by_rid($rids, $start, $limit, true) > $nextstart) {
            $next = $nextstart;
        }
        $events = C::t('resources_event')->fetch_by_rid($rids, $start, $limit);
    }
    if ($tplmore) {//加载多条动态
        include template('template_dynamic_list');
    } else {//加载单独动态页
        include template('template_more_dynamic');
    }
    exit();
} elseif
($do == 'loadmoreversion'
) {
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    $fileinfo = C::t('resources')->get_property_by_rid($rid);
    $fileinfo['dpath'] = dzzencode($rid);
    $vnext = (isset($_GET['next']) && $_GET['next']) ? intval($_GET['next']) : 1;
    $vlimit = 20;
    $vstart = ($vnext - 1) * $vlimit;
    $limit = ($vstart) ? $vstart . '-' . $vlimit : $vlimit;
    $vnext = false;
    $vnextstart = $vstart + $vlimit;
    if (C::t('resources_version')->fetch_all_by_rid($rid, $limit, true) > $vnextstart) {
        $vnext = $vnextstart;
    }
    $versions = C::t('resources_version')->fetch_all_by_rid($rid, $limit, false);
    if ($vstart == 0) {//加载多条历史版本
        include template('historyversion_content');
    } else {//加载单独历史版本页
        include template('template_historyversion_list');
    }
    exit();
} elseif
($do == 'filelist'
) {
    $limit = isset($_GET['perpage']) ? intval($_GET['perpage']) : 50;//默认每页条数
    $page = empty($_GET['page']) ? 0 : intval($_GET['page']);//页码数
    $start = $page;//开始条数
    $asc = isset($_GET['asc']) ? intval($_GET['asc']) : 0;
    $disp = (isset($_GET['disp'])) ? intval($_GET['disp']) : 0;
    $order = $asc > 0 ? 'ASC' : "DESC";

    switch ($disp) {
        case 0:
            $orderby = 'e.dateline';
            break;
        case 1:
            $orderby = 'e.username';
            break;
        case 2:
            $orderby = 'e.do';
            break;
        case 3:
            $orderby = 'e.do_obj';
            break;
        case 4:
            $orderby = 'e.body_data';
            break;
    }
    if (is_array($orderby)) {
        foreach ($orderby as $key => $value) {
            $orderby[$key] = $value . ' ' . $order;
        }
        $ordersql = ' ORDER BY ' . implode(',', $orderby);
    } elseif ($orderby) {
        $ordersql = ' ORDER BY ' . $orderby . ' ' . $order;
    }
    $array = array('resources_event');
    $condition = array();
    if (!empty($_GET['doevent'])) {
        $eventdo = trim($_GET['doevent']);
        $condition['do'] = $eventdo;
    }
    if (!empty($_GET['doobj'])) {
        $obj = trim($_GET['doobj']);
        $condition['do_obj'] = $obj;
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
    if (!empty($_GET['uids'])) {
        $uids = $_GET['uids'];
        $condition['uidval'] = array($uids, 'nowhere');
    }

    $events = array();
    $next = false;
    $nextstart = $start + $limit;
    if (C::t('resources_event')->fetch_all_event($start, $limit, $condition, $ordersql, true) > $nextstart) {
        $next = $nextstart;
    }

    $events = C::t('resources_event')->fetch_all_event($start, $limit, $condition, $ordersql);
    $eventnumbers = count($events);
    include template('group/dynamic_list');
    exit();
} elseif
($do == 'deletecomment'
) {
    $id = $_GET['id'];
    $return = C::t('resources_event')->delete_comment_by_id($id);
    if ($return['error']) {
        exit(json_encode(array('error' => $return['error'])));
    } else {
        exit(json_encode(array('success' => true)));
    }
} else {
    $limit = isset($_GET['perpage']) ? intval($_GET['perpage']) : 50;//默认每页条数
    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);//页码数
    $start = ($page - 1) * $perpage;//开始条数
    $asc = isset($_GET['asc']) ? intval($_GET['asc']) : 0;
    $disp = (isset($_GET['disp'])) ? intval($_GET['disp']) : 0;
    $order = $asc > 0 ? 'ASC' : "DESC";

    switch ($disp) {
        case 0:
            $orderby = 'e.dateline';
            break;
        case 1:
            $orderby = 'e.username';
            break;
        case 2:
            $orderby = 'e.do';
            break;
        case 3:
            $orderby = 'e.do_obj';
            break;
        case 4:
            $orderby = 'e.body_data';
            break;
    }
    if (is_array($orderby)) {
        foreach ($orderby as $key => $value) {
            $orderby[$key] = $value . ' ' . $order;
        }
        $ordersql = ' ORDER BY ' . implode(',', $orderby);
    } elseif ($orderby) {
        $ordersql = ' ORDER BY ' . $orderby . ' ' . $order;
    }
    $users = C::t('user')->fetch_all_user();
    $next = false;
    $nextstart = $start + $limit;
    if (C::t('resources_event')->fetch_all_event($start, $limit, '', $ordersql, true) > $nextstart) {

        $next = $nextstart;
    }
    $events = C::t('resources_event')->fetch_all_event($start, $limit, '', $ordersql);
    $eventnumbers = count($events);

}
require template('dynamic_content');