<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if ($do == 'delsearchcat') {//删除搜索类型
    $catid = isset($_GET['id']) ? intval($_GET['id']) : '';
    if (C::t('resources_cat')->del_by_id($catid)) {
        exit(json_encode(array('success' => true, 'insertid' => $insert)));
    } else {
        exit(json_encode(array('error' => true)));
    }
} elseif ($do == 'searchfile') {
    $catid = isset($_GET['id']) ? intval($_GET['id']) : '';
    $cats = C::t('resources_cat')->fetch_by_id($catid);
    $cattext = explode(',', $cats['ext']);
    $cattidarr = explode(',', $cats['tag']);
    $tagarr = C::t('tag')->fetch_tag_by_tid($cattidarr, 'explorer');
} elseif ($do == 'filelist') {
    $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 100;//默认每页条数
    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);//页码数
    $start = ($page - 1) * $perpage;//开始条数
    $total = 0;//总条数
    $disp = intval($_GET['disp']);
    $sid = empty($_GET['sid']) ? 0 : $_GET['sid'];//id
    $catid = intval(str_replace('cat-', '', $sid));
    $data = array();
    $limitsql = "limit $start,$perpage";

    $keyword = isset($_GET['keyword']) ? urldecode($_GET['keyword']) : '';

    $asc = isset($_GET['asc']) ? intval($_GET['asc']) : 1;

    $order = $asc > 0 ? 'ASC' : "DESC";

    switch ($disp) {
        case 0:
            $orderby = 'r.name';
            break;
        case 1:
            $orderby = 'r.size';
            break;
        case 2:
            $orderby = array('r.type', 'r.ext');
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
    $wheresql = ' where r.isdelete < 1';
    $param = array('resources', 'folder');
    $folderdata = array();
    $folderids = array();
    $cats = C::t('resources_cat')->fetch_by_id($catid);
    //如果接收到后缀名条件，则按指定后缀名搜索
    $exts = isset($_GET['exts']) ? getstr($_GET['exts']) : '';
    $tags = isset($_GET['tags']) ? getstr($_GET['tags']) : '';
    if ($exts) {
        $extarr = explode(',', str_replace('.', '', $exts));
        $wheresql .= " and r.ext IN (%n)";
        $param[] = $extarr;
    } else {
        if ($cats['ext']) {
            $extarr = explode(',', str_replace('.', '', $cats['ext']));
            $wheresql .= " and r.ext IN (%n)";
            $param[] = $extarr;
        } else {
            $wheresql .= " and 0 ";
        }
    }
    //如果接收到标签条件
    if ($tags) {
        $tagsarr = explode(',', $tags);
        // print_r($tagsarr);
        //查询标签表中有对应rid
        $rids = C::t('resources_tag')->fetch_rid_by_tid($tagsarr);
        /*print_r($rids);
        die;*/
        if (count($rids) < 1) {
            $wheresql .= " and 0";
        } else {
            $wheresql .= " and r.rid IN (%n)";
            $param[] = $rids;
        }

    } elseif ($cats['tag']) {
        $tagsarr = explode(',', $cats['tag']);
        //查询标签表中有对应rid
        $rids = C::t('resources_tag')->fetch_rid_in_tid($tagsarr);
        if (count($rids) < 1) {
            $wheresql .= " and 0";
        } else {
            $wheresql .= " and r.rid IN (%n)";
            $param[] = $rids;
        }
    }
    $explorer_setting = get_resources_some_setting();
    $orgids = C::t('organization')->fetch_all_orgid(false);//获取所有有管理权限的部门
    $powerarr = perm_binPerm::getPowerArr();

    $or = array();
    //用户自己的文件
    if ($explorer_setting['useronperm']) {
        $or[] = "(r.gid=0 and r.uid=%d)";
        $param[] = $_G['uid'];
    }
    //我管理的群组或部门的文件
    if ($orgids['orgids_admin']) {
        $or[] = "r.gid IN (%n)";
        $param[] = $orgids['orgids_admin'];
    }
    //我参与的群组的文件
    if ($orgids['orgids_member']) {
        $or[] = "(r.gid IN(%n) and ((f.perm_inherit & %d) OR (r.uid=%d and f.perm_inherit & %d)))";
        $param[] = $orgids['orgids_member'];
        $param[] = $powerarr['read2'];
        $param[] = $_G['uid'];
        $param[] = $powerarr['read1'];
    }
    if ($or) {
        $wheresql .= " and (" . implode(' OR ', $or) . ")";
        $data = array();
        $folderids = $folderdata = array();
        if ($total = DB::result_first("SELECT COUNT(*) FROM %t r LEFT JOIN %t f ON r.pfid=f.fid $wheresql", $param)) {
            foreach (DB::fetch_all("SELECT rid FROM %t r LEFT JOIN %t f ON r.pfid=f.fid $wheresql $ordersql $limitsql", $param) as $value) {
                if ($arr = C::t('resources')->fetch_by_rid($value['rid'])) {
                    $data[$arr['rid']] = $arr;
                    $folderids[$arr['pfid']] = $arr['pfid'];
                    if ($arr['type'] == 'folder') $folderids[$arr['oid']] = $arr['oid'];
                }
            }
            //获取目录信息
            foreach ($folderids as $fid) {
                if ($folder = C::t('folder')->fetch_by_fid($fid)) $folderdata[$fid] = $folder;
            }
        }
    }

    $disp = isset($_GET['disp']) ? intval($_GET['disp']) : intval($cats['disp']);//文件排序
    $iconview = (isset($_GET['iconview']) ? intval($_GET['iconview']) : intval($cats['iconview']));//排列方式
    $total = $total ? $total : 0;
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
            'tags' => $tags,
            'exts' => $exts,
            'localsearch' => $bz ? 1 : 0
        )
    );
    exit(json_encode($return));

}
include template('file_content');