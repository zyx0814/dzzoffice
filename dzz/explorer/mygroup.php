<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
$uid = $_G['uid'];
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if ($do == 'filelist') {
    $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
    $field = isset($_GET['sort']) ? $_GET['sort'] : 'dateline';
    $limit = empty($_GET['limit']) ? 50 : $_GET['limit'];
    $startdate = isset($_GET['startdate']) ? trim($_GET['startdate']) : '';
    $enddate = isset($_GET['enddate']) ? trim($_GET['enddate']) : '';
    $page = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
    $start = ($page - 1) * $limit;
    $validfields = ['orgname', 'username', 'dateline'];
    $validSortOrders = ['asc', 'desc'];
    if (in_array($field, $validfields) && in_array($order, $validSortOrders)) {
        $order = " ORDER BY $field $order";
    } else {
        $order = ' ORDER BY dateline DESC';
    }
    $limitsql = "limit $start,$limit";
    $params = array('organization', 1);
    $wheresql = " where `type` = %d";
    //日期筛选
    if ($startdate) {
        $startdate = strtotime($startdate);
        $wheresql .= " and dateline > %d";
        $params[] = $startdate;
    }
    if ($enddate) {
        $enddate = strtotime($enddate);
        $wheresql .= " and dateline <= %d";
        $params[] = $enddate;
    }
    if (isset($_GET['search']) && $_GET['search'] && $_GET['search'] != 'all') {
        $search = $_GET['search'];
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
        $params[] = C::t('organization_user')->fetch_orgids_by_uid($uid, 1);
    }
    $list = array();
    $count = DB::result_first("select count(*) from %t $wheresql $order", $params);
    if ($count) {
        $explorer_setting = get_resources_some_setting();
        if ($explorer_setting['grouponperm']) {
            $groupdata = DB::fetch_all("select * from %t $wheresql $order $limitsql", $params);
            foreach ($groupdata as $v) {
                if ($v['syatemon'] == 0) {//系统管理员关闭群组
                    continue;
                } elseif ($v['syatemon'] == 1 && $v['manageon'] == 0 && C::t('organization_admin')->chk_memberperm($v['orgid'], $uid) == 0) {//管理员关闭群组，当前用户不具备管理员权限
                    continue;
                }
                $list[] = [
                    "orgname" => avatar_group($v['orgid']).$v['orgname'],
                    "orgid" => $v['orgid'],
                    "usernum" => C::t('organization_user')->fetch_usernums_by_orgid($v['orgid']),
                    "creater" => C::t('organization_admin')->fetch_group_creater($v['orgid']),
                    "dateline" => dgmdate($v['dateline'], 'Y-m-d H:i:s'),
                ];
            }
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
} else {
    Hook::listen('check_login');
    require template('mygroup');
}