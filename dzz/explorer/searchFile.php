<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;
$uid = $_G['uid'];
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if ($do == 'filelist') {
    include libfile('function/use');
    $usersettings = C::t('user_setting')->fetch_all_user_setting($_G['uid']);
    $explorer_setting = get_resources_some_setting();
    $searchtype = isset($_GET['searchtype']) ? trim($_GET['searchtype']) : '';
    $searchtypearr = explode('&', $searchtype);
    $searcharr = array();
    foreach ($searchtypearr as $v) {
        $searchtemp = explode('=', $v);
        if ($searchtemp[1] != 'all') {
            $searcharr[$searchtemp[0]] = $searchtemp[1];
        }
    }
    $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 100;//默认每页条数
    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);//页码数
    $start = ($page - 1) * $perpage;//开始条数
    $total = 0;//总条数
    $disp = isset($_GET['disp']) ? intval($_GET['disp']) : intval($usersettings['disp']);
    $sid = empty($_GET['sid']) ? 0 : $_GET['sid'];//id
    $data = array();
    $limitsql = "limit $start,$perpage";
    $keyword = isset($_GET['keyword']) ? urldecode($_GET['keyword']) : '';
    $asc = intval($_GET['asc']);
    $order = $asc > 0 ? 'ASC' : "DESC";
    $powerarr = perm_binPerm::getPowerArr();

    switch ($disp) {
        case 0:
            $orderby = 'name';
            break;
        case 1:
            $orderby = 'size';
            break;
        case 2:
            $orderby = array('type', 'ext');
            break;
        case 3:
            $orderby = 'dateline';
            break;
    }
    $ordersql = '';
    if (is_array($orderby)) {
        foreach ($orderby as $key => $value) {
            $orderby[$key] = $value . ' ' . $order;
        }
        $ordersql = ' ORDER BY ' . implode(',', $orderby);
    } elseif ($orderby) {
        $ordersql = ' ORDER BY ' . $orderby . ' ' . $order;
    }
    $wheresql = ' where 1';
    $folderdata = array();
    $folderids = array();
    $conditions = array();
    //文件位置标志条件 [isdelete,isstarred]
    $param = array('resources', 'folder');
    if (!empty($searcharr['flagval'])) {
        $conditions['flag'] = explode(',', $searcharr['flagval']);
        if (in_array('isdelete', $conditions['flag'])) {
            $wheresql .= " and r.pfid = '-1'";
            $param = array('resources', 'resources_recyle');
        }
        if (in_array('isstarred', $conditions['flag'])) {
            $rids = C::t('resources_collect')->fetch_rid_by_uid();
            $ridarr = array();
            foreach ($rids as $v) {
                $ridarr[] = $v['rid'];
            }
            $wheresql .= " and r.rid IN (%n)";
            $param[] = $ridarr;
        }
    } else {
        $wheresql .= " and (r.isdelete < 1)";
    }
    $orgids = C::t('organization')->fetch_all_orgid(false);//获取所有有管理权限的部门
    $or = array();
    //文件名条件
    if (!empty($searcharr['keywords']) && !preg_match('/^\s*$/', $searcharr['keywords'])) {
        $conditions['keywords'] = trim($searcharr['keywords']);
        $kewordsarr = explode(',', $conditions['keywords']);

        $tids = C::t('tag')->fetch_tid_by_tagname($kewordsarr, 'explorer');
        $tagsql = '';
        if ($tids) {
            $rids = C::t('resources_tag')->fetch_rid_by_tid($tids);
            $tagsql = " r.rid in(%n)";
            $param[] = $rids;
        }
        $keywordsqlarr = array();
        foreach ($kewordsarr as $v) {
            $keywordsqlarr[] = " r.name like(%s) ";
            $param[] = '%' . trim($v) . '%';
        }
        if ($tagsql) {
            $wheresql .= " and ($tagsql or (" . implode(' or ', $keywordsqlarr) . "))";
        } else {
            $wheresql .= " and (" . implode(' or ', $keywordsqlarr) . ")";
        }
    }
    //文件类型条件 如document
    if (!empty($searcharr['type'])) {
        $conditions['type'] = trim($searcharr['type']);
        if ($conditions['type'] == 'folder') {
            $wheresql .= " and r.type = %s and r.flag not in(%n)";
            $param[] = $conditions['type'];
            $param[] = array('document', 'recycle');
        } else {
            $typestr = parsefileType($conditions['type']);
            $wheresql .= " and r.ext IN (%n)";
            $param[] = $typestr;
        }
    } else {//排除特殊目录
        $wheresql .= " and  r.flag not in(%n) and r.type != %s";
        $param[] = array('document', 'recycle');
        $param[] = 'app';

    }
    //开始时间
    if (!empty($searcharr['after'])) {
        $conditions['after'] = strtotime($searcharr['after']);
        $wheresql .= " and r.dateline > %d";
        $param[] = $conditions['after'];
    }
    //结束时间
    if (!empty($searcharr['before'])) {
        $conditions['before'] = strtotime($_GET['before']);
        $wheresql .= " and r.dateline < %d";
        $param[] = $conditions['before'];
    }
    $permsql = ' 1 ';
    //文件位置条件 [1,2,3]
    if (!empty($searcharr['fid'])) {
        $conditions['fid'] = $searcharr['fid'];
        $condition['fid'] = explode(',', $conditions['fid']);
        $fids = array();
        $gids = array();

        foreach (DB::fetch_all("select gid,fid from %t where fid in(%n)", array('folder', $condition['fid'])) as $v) {
            if ($v['gid'] > 0) {
                $gids[] = $v['gid'];
            } else {
                $fids[] = $v['fid'];
            }
        }
        $groupsql = '';
        if ($gids) {
            $orgs = $gids;//保留原始机构ID
            foreach ($gids as $v) {
                foreach (C::t('organization')->get_all_contaionchild_orgid($v, $uid) as $val) {
                    $orgs[] = $val;// 追加子部门
                }
            }
            foreach (DB::fetch_all('select orgid,diron from %t where orgid in(%n)', array('organization', $orgs)) as $v) {
                if ($v['diron'] == 0) {
                    $index = array_search($v['orgid'], $orgs);
                    unset($orgs[$index]);
                }
                if (isset($conditions['flag']) && in_array('isdelete', $conditions['flag']) && C::t('organization_admin')->chk_memberperm($v['orgid'], $uid) < 1) {
                    $index = array_search($v['orgid'], $orgs);
                    unset($orgs[$index]);
                }
            }
            if (count($orgs)) {
                if (isset($conditions['flag']) && in_array('isdelete', $conditions['flag'])) {
                    if ($orgs) {
                        $groupsql = "(r.gid IN(%n) and re.uid = %d)";
                        $param[] = $orgs;
                        $param[] = $uid;
                    }
                } else {
                    $groupsql = " (r.gid IN(%n) and ((f.perm_inherit & %d) OR (r.uid=%d and f.perm_inherit & %d)))";
                    $param[] = $orgs;
                    $param[] = $powerarr['read2'];
                    $param[] = $_G['uid'];
                    $param[] = $powerarr['read1'];
                }

            }
        }
        $fidsql = '';
        if (!empty($fids)) {
            $fidsql = " (r.gid=0 and r.uid = %d)";
            $param[] = $uid;
        }

        if ($fidsql && $groupsql) {
            $permsql .= ' and (' . $groupsql . ' or ' . $fidsql . ')';
        } elseif ($fidsql) {
            $permsql .= ' and ' . $fidsql;
        } elseif ($groupsql) {
            $permsql .= ' and ' . $groupsql;
        }
        $condition['uid'] = 2;
    }

    //所有者条件 如self,[1,2,3]
    if (!empty($searcharr['uid'])) {
        $conditions['uid'] = $searcharr['uid'];
        //我的
        if ($conditions['uid'] == 'self' && $explorer_setting['useronperm']) {
            $or[] = " (r.gid=0 and r.uid=%d)";
            $param[] = $uid;
            $condition['uid'] = 2;//只限制用户不再限制群组
        } elseif ($conditions['uid'] == 'noself') {
            $permsql .= " and r.uid != %d  ";
            $param[] = $uid;
            $condition['uid'] = 1;
        } elseif ($conditions['uid'] == 'all') {
            $condition['uid'] = '';
        } else {
            $condition['uid'] = explode(',', $conditions['uid']);
            $permsql .= " and r.uid IN (%n)  ";
            $param[] = $condition['uid'];
        }
    }

    //如果没有文件fid限制或者需要限制群组id ($condition['uid'] == 2表示只需要用户限制)
    if ($condition['uid'] != 2) {
        //如果筛选条件没有用户限制
        if (!isset($condition['uid']) && !$condition['uid'] && $explorer_setting['useronperm']) {
            //用户自己的文件；
            $or[] = "(r.gid=0 and r.uid=%d)";
            $param[] = $uid;
        }
    }
    if (!$condition['fid']) {
        //我管理的群组或部门的文件
        if ($orgids['orgids_admin']) {
            $or[] = "r.gid IN (%n)";
            $param[] = $orgids['orgids_admin'];
        }
        //我参与的群组的文件
        if (isset($conditions['flag']) && in_array('isdelete', $conditions['flag'])) {
            if ($orgids['orgids_member']) {
                $or[] = "(r.gid IN(%n) and re.uid = %d)";
                $param[] = $orgids['orgids_member'];
                $param[] = $uid;
            }
        } else {
            if ($orgids['orgids_member']) {
                $or[] = "(r.gid IN(%n) and ((f.perm_inherit & %d) OR (r.uid=%d and f.perm_inherit & %d)))";
                $param[] = $orgids['orgids_member'];
                $param[] = $powerarr['read2'];
                $param[] = $_G['uid'];
                $param[] = $powerarr['read1'];
            }
        }

    }
    if (!empty($or)) {
        if (!$condition['fid']) {
            $permsql .= " and (" . implode(' OR ', $or) . ")";
        } else {
            $permsql .= " or (" . implode(' OR ', $or) . ")";
        }
    }

    $wheresql .= ' and  (' . $permsql . ')';
    $data = array();
    $foldersids = $folderdata = array();
    $conditions = array_filter($conditions);
    if (isset($conditions['flag']) && in_array('isdelete', $conditions['flag'])) {
        $countsql = 'SELECT COUNT(*) FROM %t r LEFT JOIN %t re ON r.rid=re.rid';
        $sql = 'SELECT r.rid FROM %t r LEFT JOIN %t re ON r.rid=re.rid';
    } else {
        $countsql = 'SELECT COUNT(*) FROM %t r LEFT JOIN %t f ON r.pfid=f.fid';
        $sql = 'SELECT r.rid  FROM %t r LEFT JOIN %t f ON r.pfid=f.fid';
    }
    if ($total = DB::result_first("$countsql $wheresql", $param)) {
        foreach (DB::fetch_all("$sql $wheresql $ordersql $limitsql", $param) as $value) {
            if ($arr = C::t('resources')->fetch_by_rid($value['rid'])) {
                if ($arr['isdelete']) $arr['relpath'] = lang('explorer_recycle_name');
                $data[$arr['rid']] = $arr;
                $folderids[$value['pfid']] = $arr['pfid'];
                if ($arr['type'] == 'folder') $folderids[$arr['oid']] = $arr['oid'];
            }
        }
        //获取目录信息
        foreach ($folderids as $fid) {
            if ($folder = C::t('folder')->fetch_by_fid($fid)) $folderdata[$fid] = $folder;
        }
    }
    $iconview = isset($_GET['iconview']) ? intval($_GET['iconview']) : ($usersettings['iconview'] ? intval($usersettings['iconview']) : 4);//排列方式
    $total = $total ?  $total : 0;
    if (!$json_data = json_encode($data)) $data = array();
    if (!$json_data = json_encode($folderdata)) $folderdata = array();
    //返回数据
    $return = array(
        'sid' => $sid,
        'total' => $total,

        'data' => $data ? $data : array(),
        'folderdata' => $folderdata ? $folderdata : array(),
        'param' => array(
            'disp' => $disp,
            'view' => $iconview,
            'page' => $page,
            'perpage' => $perpage,
            'bz' => $bz,
            'total' => $total,
            'asc' => $asc,
            'keyword' => $keyword,
            'localsearch' => $bz ? 1 : 0
        ),
        'conditions' => $conditions
    );
    exit(json_encode($return));
} elseif ($do == 'getsearchval') {
    $uids = isset($_GET['uid']) ? $_GET['uid'] : '';
    $fids = isset($_GET['fid']) ? $_GET['fid'] : '';
    $usernamearr = array();
    foreach (DB::fetch_all("select uid,username from %t where uid in(%n)", array('user', $uids)) as $v) {
        $usernamearr[$v['uid']] = $v['username'];
    }
    $foldername = array();
    foreach (DB::fetch_all("select fname,gid from %t where fid in(%n)", array('folder', $fids)) as $v) {
        if ($v['gid'] > 0) {
            $type = DB::result_first("select `type` from %t where orgid = %d", array('organization', $v['gid']));
            $foldername[] = ($type == 1) ? $v['fname'] . '(群组)' : $v['fname'] . '(机构)';
        } else {
            $foldername[] = $v['fname'];
        }

    }
    exit(json_encode(array('folder' => $foldername, 'user' => $usernamearr)));
} elseif ($do == 'parseinputcondition') {
    $foldernames = isset($_GET['foldername']) ? trim($_GET['foldername']) : '';
    $usernames = isset($_GET['username']) ? trim($_GET['username']) : '';
    $uids = array();
    $fids = array();
    if ($foldernames) {
        $orgs = C::t('organization')->fetch_all_orggroup($uid);//机构群组
        $groupinfo = C::t('organization')->fetch_group_by_uid($uid, true);//个人群组
        $homefid = C::t('folder')->fetch_fid_by_flag('home');
        $groups = array_merge($orgs['org'], $groupinfo);

        $positions[] = array($homefid);
        foreach ($groups as $v) {
            $positions[] = array($v['fid']);
        }
        $foldernamearr = explode(',', $foldernames);
        foreach (DB::fetch_all("select fid from %t where fname in(%n) and pfid = 0", array('folder', $foldernamearr)) as $v) {
            $fids[] = $v['fid'];
        }
    }
    if ($usernames) {
        $usernamearr = explode(',', $usernames);
        foreach (DB::fetch_all("select uid from %t where username in(%n)", array('user', $usernamearr)) as $v) {
            $uids[] = $v['uid'];
        }
    }
    exit(json_encode(array('fids' => $fids, 'uids' => $uids)));
}
require template('searchFile');