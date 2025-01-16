<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;
$uid = $_G['uid'];
$fid = isset($_GET['fid']) ? intval($_GET['fid']) : '';
$cid = isset($_GET['cid']) ? intval($_GET['cid']):'';
$collect = isset($_GET['collect']) ? intval($_GET['collect']):'';
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
if ($operation == 'filelist') {
    $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 20;//默认每页条数
    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);//页码数
    $start = ($page - 1) * $perpage;//开始条数
    $total = 0;//总条数
    $disp = isset($_GET['disp']) ? intval($_GET['disp']) : 3;
    $data = array();
    $limitsql = "limit $start,$perpage";

    $keyword = isset($_GET['keyword']) ? urldecode($_GET['keyword']) : '';

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
            if(count($rids)){
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
    if($cid){
        $cats = C::t('resources_cat')->fetch_by_id($cid);
            if($cats['ext']){
                $extarr = explode(',',str_replace('.','',$cats['ext']));
                $wheresql .= " and r.ext IN (%n)";
                $param[]=$extarr;
            }
        if($cats['tag']){
            //查询标签表中有对应rid
            if(!empty($tagsarr)){
                $trids = C::t('resources_tag')->fetch_rid_in_tid($tagsarr);
               if(count($rids)){
                   $wheresql .= " and r.rid IN (%n)";
                   $param[]=$trids;
               }
            }
        }
    }
    if($collect){
        $collects = C::t('resources_collect')->fetch_by_uid();
        if(count($collects)){
            $collectrids = array();
            foreach($collects as $v){
                $collectrids[] = $v['rid'];
            }
            $wheresql .= " and r.rid IN (%n)";
            $param[]=$collectrids;
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
    if ($total = DB::result_first("SELECT COUNT(*) FROM %t r LEFT JOIN %t f ON r.pfid=f.fid $wheresql", $param)) {
        foreach (DB::fetch_all("SELECT r.rid  FROM %t r LEFT JOIN %t f ON r.pfid=f.fid $wheresql $ordersql $limitsql", $param) as $value) {
            if ($arr = C::t('resources')->fetch_by_rid($value['rid'])) {
                $folderids[$value['pfid']] = $arr['pfid'];
                if ($arr['type'] == 'folder') {
                    $folderids[$arr['oid']] = $arr['oid'];
					if(empty($arr['contaions'])){
						$arr['contaions']=C::t('resources')->get_contains_by_fid($arr['oid']);
					}
                    $arr['filenum'] = $arr['contaions']['contain'][0];
                    $arr['foldernum'] = $arr['contaions']['contain'][1];
                } else {
                    $arr['monthdate'] = dgmdate($arr['dateline'], 'm-d');
                    $arr['hourdate'] = dgmdate($arr['dateline'], 'H:i');
                }
                if ($arr['type'] == 'image') {
                    $arr['img'] = DZZSCRIPT . '?mod=io&op=thumbnail&width=45&height=45&path=' . dzzencode('attach::' . $arr['aid']);
                    $arr['imgpath'] =  DZZSCRIPT.'?mod=io&op=thumbnail&size=large&path=' .dzzencode('attach::' . $arr['aid']);
                }
                $data[$arr['rid']] = $arr;
            }
        }
        //获取目录信息
        foreach ($folderids as $v) {
            if ($folder = C::t('folder')->fetch_by_fid($v)) $folderdata[$v] = $folder;
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
        'total' => count($data)+$start,
        'data' => $data ? $data : array(),
        'folderdata' => $folderdata ? $folderdata : array(),
        'param' => array(
            'disp' => $disp,
            'view' => $iconview,
            'page' => $next,
            'perpage' => $perpage,
            'bz' => $bz,
            'datatotal' => count($data)+$start,
            'asc' => $asc,
            'keyword' => $keyword,
            'fid' => $fid,
            'cid'=>$cid,
            'collect'=>$collect,
            'localsearch' => $bz ? 1 : 0
        ),
    );
    $return = json_encode($return);
    $return = str_replace("'","\'",$return);
    require template('mobile/filelist');
} else {
    require template('mobile/search');
}
exit();