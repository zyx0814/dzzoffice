<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;
$uid = $_G['uid'];
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if ($do == 'filelist') {
    $limit = isset($_GET['perpage']) ? intval($_GET['perpage']) : 100;//默认每页条数
    $page = empty($_GET['page']) ? 0 : intval($_GET['page']);//页码数
    $start = $page;//开始条数
    $total = 0;//总条数
    $disp = isset($_GET['disp']) ? intval($_GET['disp']) : '';
    $limitsql = "limit $start,$limit";

    $asc = isset($_GET['asc']) ? intval($_GET['asc']) : 1;

    $order = $asc > 0 ? 'ASC' : "DESC";

    switch ($disp) {
        case 0:
            $orderby = 'orgname';
            break;
        case 1:
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
    $params = array('organization');
    $wheresql = ' where `type` = 1 ';
    if (isset($_GET['search']) && $_GET['search'] && $_GET['search'] != 'all') {
        $search = $_GET['search'];
        $orgids = C::t('organization')->fetch_all_orgid();//获取所有有管理权限的部门
        if ($search == 'manage') {
            $myorgid = array();
            foreach (DB::fetch_all("select ou.orgid from %t ou 
                  left join %t o on ou.orgid= o.orgid 
                  where ou.uid = %d and ou.admintype = %d and o.type = %d", array('organization_admin', 'organization', $uid, 1, 1)) as $v) {
                $myorgid[] = $v['orgid'];
            }
            $wheresql .= " and orgid in(%n)";
            $params[] = $myorgid;
        } elseif ($search == 'partake') {
            $partorgids = array();
            //获取参与的群组
            foreach (DB::fetch_all("select u.orgid from %t u 
                  left join %t o on u.orgid= o.orgid 
                  where u.uid = %d  and o.type = %d", array('organization_user', 'organization', $uid, 1, 1)) as $v) {
                $partorgids[] = $v['orgid'];
            }
            //获取管理的群组并排除
            foreach (DB::fetch_all("select ou.orgid from %t ou 
                  left join %t o on ou.orgid= o.orgid 
                  where ou.uid = %d  and o.type = %d", array('organization_admin', 'organization', $uid, 1)) as $v) {
                if (in_array($v['orgid'], $partorgids)) {
                    $index = array_search($v['orgid'], $partorgids);
                    unset($partorgids[$index]);
                }
            }
            $wheresql .= " and orgid in(%n)";
            $params[] = $partorgids;
        } elseif ($search == 'my') {
            $myorgid = array();
            foreach (DB::fetch_all("select ou.orgid from %t ou 
                  left join %t o on ou.orgid= o.orgid 
                  where ou.uid = %d and ou.admintype = %d and o.type = %d", array('organization_admin', 'organization', $uid, 2, 1)) as $v) {
                $myorgid[] = $v['orgid'];
            }
            $wheresql .= " and orgid in(%n)";
            $params[] = $myorgid;
        }
    } else {
        $wheresql .= " and orgid in(%n)";
        //获取用户所在群组id
        $params[] = C::t('organization_user')->fetch_org_by_uid($uid, 1);
    }
    //日期筛选
    if (isset($_GET['after']) && $_GET['after']) {
        $afterdate = strtotime($_GET['after']);
        $wheresql .= " and dateline > %d";
        $params[] = $afterdate;
    }
    if (isset($_GET['before']) && $_GET['before']) {
        $beforedate = strtotime($_GET['before']);
        $wheresql .= " and dateline <= %d";
        $params[] = $beforedate;
    }
    $next = false;
    $nextstart = $start + $limit;
    if (DB::result_first("select count(*) from %t $wheresql $ordersql ", $params) > $nextstart) {
        $next = $nextstart;
    }
    $groups = array();
    $explorer_setting = get_resources_some_setting();
    if ($explorer_setting['grouponperm']) {
        foreach (DB::fetch_all("select * from %t  $wheresql $ordersql $limitsql", $params) as $orginfo) {
            if ($orginfo['syatemon'] == 0) {//系统管理员关闭群组
                continue;
            } elseif ($orginfo['syatemon'] == 1 && $orginfo['manageon'] == 0 && C::t('organization_admin')->chk_memberperm($orginfo['orgid'], $uid) == 0) {//管理员关闭群组，当前用户不具备管理员权限
                continue;
            }
            $orginfo['usernum'] = C::t('organization_user')->fetch_usernums_by_orgid($orginfo['orgid']);
            $orginfo['creater'] = C::t('organization_admin')->fetch_group_creater($orginfo['orgid']);

            if (intval($orginfo['aid']) > 0) {
                //群组图
                $orginfo['imgs'] = 'index.php?mod=io&op=thumbnail&width=24&height=24&path=' . dzzencode('attach::' . $orginfo['aid']);
            }
            /*  $contaions = C::t('resources')->get_contains_by_fid($orginfo['fid'], true);
              $orginfo['ffsize'] = lang('property_info_size', array('fsize' => formatsize($contaions['size']), 'size' => $contaions['size']));
              $orginfo['contain'] = lang('property_info_contain', array('filenum' => $contaions['contain'][0], 'foldernum' => $contaions['contain'][1]));*/
            $groups[] = $orginfo;
        }

    }
    require template('mygroup_list');
} else {
    $limit = isset($_GET['perpage']) ? intval($_GET['perpage']) : 100;//默认每页条数
    $page = empty($_GET['page']) ? 0 : intval($_GET['page']);//页码数
    $start = $page;//开始条数
    $total = 0;//总条数
    $disp = isset($_GET['disp']) ? intval($_GET['disp']) : '';
    $limitsql = "limit $start,$limit";

    $asc = isset($_GET['asc']) ? intval($_GET['asc']) : 1;

    $order = $asc > 0 ? 'ASC' : "DESC";

    switch ($disp) {
        case 0:
            $orderby = 'orgname';
            break;
        case 1:
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
    $next = false;
    $nextstart = $start + $limit;
    $explorer_setting = get_resources_some_setting();
    $groups = array();
//获取用户坐在群组id
    $orgids = C::t('organization_user')->fetch_org_by_uid($uid, 1);
    if (DB::result_first("select count(*) from %t where orgid in(%n) $ordersql ", array('organization', $orgids)) > $nextstart) {
        $next = $nextstart;
    }
    if ($explorer_setting['grouponperm']) {
        foreach (DB::fetch_all("select * from %t where orgid in(%n) $ordersql $limitsql", array('organization', $orgids)) as $orginfo) {
            if ($orginfo['syatemon'] == 0) {//系统管理员关闭群组
                continue;
            } elseif ($orginfo['syatemon'] == 1 && $orginfo['manageon'] == 0 && C::t('organization_admin')->chk_memberperm($orginfo['orgid'], $uid) == 0) {//管理员关闭群组，当前用户不具备管理员权限
                continue;
            }

            $orginfo['usernum'] = C::t('organization_user')->fetch_usernums_by_orgid($orginfo['orgid']);
            $orginfo['creater'] = C::t('organization_admin')->fetch_group_creater($orginfo['orgid']);

            if (intval($orginfo['aid']) > 0) {
                //群组图
                $orginfo['imgs'] = 'index.php?mod=io&op=thumbnail&width=24&height=24&path=' . dzzencode('attach::' . $orginfo['aid']);
            }
            /* $contaions = C::t('resources')->get_contains_by_fid($orginfo['fid']);
             $orginfo['ffsize'] = lang('property_info_size', array('fsize' => formatsize($contaions['size']), 'size' => $contaions['size']));
             $orginfo['contain'] = lang('property_info_contain', array('filenum' => $contaions['contain'][0], 'foldernum' => $contaions['contain'][1]));*/
            $groups[] = $orginfo;
        }
        $groupsnumber = count($groups);

    }
    require template('mygroup');
}
