<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;
$uid = $_G['uid'];
$fid = isset($_GET['fid']) ? intval($_GET['fid']) : '';
$perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 10;//默认每页条数
$datatotal = isset($_GET['datatotal']) ? intval($_GET['datatotal']):0;
$page = empty($_GET['page']) ? 1 : intval($_GET['page']);//页码数
$start = ($page - 1) * $perpage;//开始条数
$total = 0;//总条数
$disp = isset($_GET['disp']) ? intval($_GET['disp']) : 3;
$data = array();
$limitsql = "limit $start,$perpage";

$keyword = isset($_GET['keyword']) ? urldecode($_GET['keyword']) : '';
$exts = isset($_GET['exts']) ? trim($_GET['exts']) : '';

$asc = intval($_GET['asc']);

$order = $asc > 0 ? 'ASC' : "DESC";

$powerarr = perm_binPerm::getPowerArr();

switch ($disp) {
    case 0:
        $orderby = 'r.name';
        break;
    case 1:
        $orderby = 'r.size';
        break;
    case 2:
        $orderby = array('r.type', 'ext');
        break;
    case 3:
        $orderby = 'r.dateline';
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
$param = array('resources', 'folder');
if ($keyword && !preg_match('/^\s*$/', $keyword)) {
    $kewordsarr = explode(',', $keyword);
    $tids = C::t('tag')->fetch_tid_by_tagname($kewordsarr, 'explorer');
    $tagsql = '';
    if ($tids) {
        $rids = C::t('resources_tag')->fetch_rid_by_tid($tids);
        if (count($rids)) {
            $tagsql = " r.rid in(%n)";
            $param[] = $rids;
        }

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
//类型筛选
if ($exts) {
    if($exts == 'folder'){
        $wheresql .= ' and r.type = %s';
        $param[] = 'folder';
    }else{
        $extarr = explode(',', $exts);
        $wheresql .= ' and r.ext in(%n)';
        $param[] = $extarr;
    }
}
$orgids = C::t('organization')->fetch_all_orgid();//获取所有有管理权限的部门
$or = array();
if (!$fid) {
    //我的
    $or[] = "(r.gid=0 and r.uid=%d)";
    $param[] = $uid;
    //我管理的群组或部门的文件
    if ($orgids['orgids_admin']) {
        $or[] = "r.gid IN (%n)";
        $param[] = $orgids['orgids_admin'];
    }

    if ($orgids['orgids_member']) {
        $or[] = "(r.gid IN(%n) and ((f.perm_inherit & %d) OR (r.uid=%d and f.perm_inherit & %d)))";
        $param[] = $orgids['orgids_member'];
        $param[] = $powerarr['read2'];
        $param[] = $_G['uid'];
        $param[] = $powerarr['read1'];
    }


} else {
    $folderinfo = C::t('folder')->fetch($fid);
    if (($folderinfo['gid'] > 0 && C::t('organization_admin')->chk_memberperm($gid)) || !$folderinfo['gid']) {//如果具有管理员权限
        $wheresql .= ' and r.pfid = %d ';
        $param[] = $fid;

    } else {
        $wheresql .= ' and r.pfid = %d and ((f.perm_inherit & %d) OR (r.uid=%d and f.perm_inherit & %d))';
        $param[] = $fid;
        $param[] = $powerarr['read2'];
        $param[] = $_G['uid'];
        $param[] = $powerarr['read1'];
    }
}
if ($or && !$fid) $wheresql .= " and (" . implode(' OR ', $or) . ")";
$data = array();
$foldersids = $folderdata = array();
if ($total = DB::result_first("SELECT COUNT(*) FROM %t r LEFT JOIN %t f ON r.pfid=f.fid $wheresql", $param) > $start) {
    foreach (DB::fetch_all("SELECT r.rid  FROM %t r LEFT JOIN %t f ON r.pfid=f.fid $wheresql  $limitsql", $param) as $value) {
        if ($arr = C::t('resources')->fetch_by_rid($value['rid'])) {
            $folderids[$value['pfid']] = $arr['pfid'];
            if ($arr['type'] == 'folder') {
                $folderids[$arr['oid']] = $arr['oid'];
                $arr['filenum'] = $arr['contaions']['contain'][0];
                $arr['foldernum'] = $arr['contaions']['contain'][1];
            } else {
                $arr['monthdate'] = dgmdate($arr['dateline'], 'm-d');
                $arr['hourdate'] = dgmdate($arr['dateline'], 'H:i');
            }
            if ($arr['type'] == 'image') {
                $arr['img'] = DZZSCRIPT . '?mod=io&op=thumbnail&width=45&height=45&path=' . dzzencode('attach::' . $arr['aid']);
                $arr['imgpath'] = DZZSCRIPT . '?mod=io&op=thumbnail&path=' . dzzencode('attach::' . $arr['aid']);
            }
            $data[$arr['rid']] = $arr;
        }
    }
}

$disp = isset($_GET['disp']) ? intval($_GET['disp']) : intval($usersettings['disp']);//文件排序
if (!isset($usersettings['iconview'])) $usersettings['iconview'] = 4;
$iconview = (isset($_GET['iconview']) ? intval($_GET['iconview']) : intval($usersettings['iconview']));//排列方式
$next = false;
if (count($data) >= $perpage) {
    $next = $page + 1;
}
$return = array(
    'total' => count($data) + $start,
    'param' => array(
        'disp' => $disp,
        'view' => $iconview,
        'page' => $next,
        'perpage' => $perpage,
        'bz' => $bz,
        'datatotal' => count($data) + $start,
        'asc' => $asc,
        'keyword' => $keyword,
        'fid' => $fid,
        'localsearch' => $bz ? 1 : 0,
        'exts'=>$exts
    ),
);
$params = json_encode($return['param']);
require template('mobilefileselection/searchfile');
exit();