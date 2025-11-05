<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;
$uid = $_G['uid'];
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
//按条件筛选内容
if ($do == 'filelist') {
    $usersettings = C::t('user_setting')->fetch_all_user_setting($_G['uid']);
    $sid = htmlspecialchars($_GET['sid']);
    //分页
    $limit = isset($_GET['perpage']) ? intval($_GET['perpage']) : 20;//默认每页条数
    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);//页码数
    $start = ($page - 1) * $limit;//开始条数
    $disp = isset($_GET['disp']) ? intval($_GET['disp']) : ($usersettings['recycledisp'] ? intval($usersettings['recycledisp']) : 4);
    $keyword = isset($_GET['keyword']) ? urldecode($_GET['keyword']) : '';
    $asc = (isset($_GET['asc'])) ? intval($_GET['asc']) : 0;
    $order = $asc > 0 ? 'ASC' : "DESC";
    switch ($disp) {
        case 0:
            $orderby = 'r.name';
            break;
        case 1:
            $orderby = 'r.size';
            break;
        case 2:
            $orderby = 're.pfid';
            break;
        case 3:
            $orderby = 're.uid';
            break;
        case 4:
            $orderby = 're.deldateline';
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
    $condition = array();
    //文件夹id
    if (!empty($_GET['fids']) && $_GET['fids']) {
        $pfid = intval($_GET['fids']);
        //查询文件夹下级fid
        $fids[] = $pfid;
        foreach (C::t('folder')->fetch_all_folderfid_by_pfid($pfid) as $v) {
            $fids[] = $v;
        }
        if (count($fids) > 1) {
            $condition['re.pfid'] = array($fids, 'in', 'and');
        } else {
            $condition['re.pfid'] = array($pfid);
        }
    }
    //如果接受到的是群组id
    if (!empty($_GET['gid']) && $_GET['gid']) {
        $gid = intval($_GET['gid']);
        //获取下级有权限的gid处理
        $gids = C::t('organization')->get_childorg_by_orgid($gid);
        //如果有下级，即orgid数量大于1
        if (count($gids) > 1) {
            $condition['re.gid'] = array($gids, 'in', 'and');
        } else {
            $condition['re.gid'] = array($gid);
        }

    }
    //时间范围
    if (!empty($_GET['after']) && $_GET['after']) {
        $startdate = strtotime($_GET['after']);
        $condition[] = array(' re.deldateline > ' . $startdate, 'stringsql', 'and');
    }
    if (!empty($_GET['before']) && $_GET['before']) {
        $enddate = strtotime($_GET['before']);
        $condition[] = array(' re.deldateline <= ' . $enddate, 'stringsql', 'and');

    }
    $data = C::t('resources_recyle')->fetch_all_recycle($start, $limit, $condition, $ordersql);
    if ($data !== null && is_array($data)) {
        if (count($data) >= $limit) {
            $total = $start + $limit * 2 - 1;
        } else {
            $total = $start + count($data);
        }
    } else {
        // 处理 $data 为 null 或无效的情况
        $total = $start; // 或者其他合适的默认值
    }
    $iconview = (isset($_GET['iconview'])) ? intval($_GET['iconview']) : intval($usersettings['recycleiconview']);//排列方式
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
            'perpage' => $limit,
            'bz' => $bz,
            'total' => $total,
            'asc' => $asc,
            'keyword' => $keyword,
            'tags' => $tags,
            'exts' => $exts,
            'localsearch' => $bz ? 1 : 0,
            'fid' => '',
        )
    );
    exit(json_encode($return));
} else {
    //分页
    $limit = isset($_GET['perpage']) ? intval($_GET['perpage']) : 20;//默认每页条数
    $page = empty($_GET['page']) ? 0 : intval($_GET['page']);//页码数
    $start = $page;//开始条数
    $limitsql = "limit $start,$perpage";
    $disp = isset($_GET['disp']) ? intavel($_GET['disp']) : 3;

    $keyword = isset($_GET['keyword']) ? urldecode($_GET['keyword']) : '';

    $asc = (isset($_GET['asc'])) ? intval($_GET['asc']) : 1;

    $order = $asc > 0 ? 'ASC' : "DESC";

    switch ($disp) {
        case 0:
            $orderby = 'r.name';
            break;
        case 1:
            $orderby = 'r.size';
            break;
        case 2:
            $orderby = 'r.pfid';
            break;
        case 3:
            $orderby = 're.uid';
            break;
        case 4:
            $orderby = 're.deldateline';
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
    //我的
    $explorer_setting = get_resources_some_setting();
    if ($explorer_setting['useronperm']) {
        $fid = C::t('folder')->fetch_fid_by_flag('home');
        $homearr = array('fid' => $fid, 'name' => lang('explorer_user_root_dirname'));
    }
    //我参与的群组
    $manageorg = C::t('organization')->fetch_all_part_org();
}
require template('recyle_content');
