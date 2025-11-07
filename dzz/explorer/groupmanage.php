<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
if ($_G['adminid'] != 1) {
    showmessage('no_privilege', dreferer());
}
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if ($do == 'filelist') {
    $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
    $field = isset($_GET['sort']) ? $_GET['sort'] : 'dateline';
    $limit = empty($_GET['limit']) ? 50 : $_GET['limit'];
    $startdate = isset($_GET['startdate']) ? trim($_GET['startdate']) : '';
    $enddate = isset($_GET['enddate']) ? trim($_GET['enddate']) : '';
    $page = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
    $start = ($page - 1) * $limit;
    $validfields = ['orgname','dateline'];
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
    //状态筛选
    if (isset($_GET['groupon']) && $_GET['groupon']) {
        $on = (intval($_GET['groupon']) == 1) ? 0 : 1;
        $wheresql .= " and syatemon = %d";
        $params[] = $on;
    }
    //共享目录状态筛选
    if (isset($_GET['diron']) && $_GET['diron']) {
        $on = (intval($_GET['diron']) == 1) ? 0 : 1;
        $wheresql .= " and available = %d";
        $params[] = $on;
    }
    $list = array();
    $count = DB::result_first("select count(*) from %t $wheresql $order", $params);
    if ($count) {
        $explorer_setting = get_resources_some_setting();
        if ($explorer_setting['grouponperm']) {
            $groupdata = DB::fetch_all("select * from %t $wheresql $order $limitsql", $params);
            foreach ($groupdata as $v) {
                $list[] = [
                    "orgname" => avatar_group($v['orgid']).$v['orgname'],
                    "orgid" => $v['orgid'],
                    "usernum" => C::t('organization_user')->fetch_usernums_by_orgid($v['orgid']),
                    "creater" => C::t('organization_admin')->fetch_group_creater($v['orgid']),
                    "maxspacesize" => $v['maxspacesize'],
                    "diron" => $v['diron'] ? 1 : 0,
                    "desc" => $v['desc'],
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
} elseif ($do == 'folder_maxspacesize') {
    prem();
    $orgid = intval($_GET['orgid']);
    $setspacesize = intval($_GET['maxspacesize']);
    if (!$org = C::t('organization')->fetch($orgid)) {
        exit(json_encode(array('error' => '该机构或群组不存在或被删除')));
    }
    //暂时只允许系统管理员进行空间相关设置
    if ($_G['adminid'] != 1) {
        exit(json_encode(array('error' => '没有权限')));
    }
    if ($setspacesize != 0) {
        //获取允许设置的空间值
        $allowallotspace = C::t('organization')->get_allowallotspacesize_by_orgid($orgid);
        if ($allowallotspace < 0) {
            exit(json_encode(array('error' => '可分配空间不足')));
        }
        //获取当前已占用空间大小
        $currentallotspace = C::t('organization')->get_orgallotspace_by_orgid($orgid, 0, false);
        //设置值小于当前下级分配总空间值即：当前设置值 < 下级分配总空间
        if ($setspacesize > 0 && $setspacesize * 1024 * 1024 < $currentallotspace) {
            exit(json_encode(array('error' => '设置空间值不足,小于已分配空间值！', 'val' => $org['maxspacesize'])));
        }
        //上级包含空间限制时，无限制不处理，直接更改设置值
        if ($allowallotspace > 0 && ($setspacesize * 1024 * 1024 > $allowallotspace)) {
            exit(json_encode(array('error' => '总空间不足！', 'val' => $org['maxspacesize'])));
        }
    }

    //设置新的空间值
    if (C::t('organization')->update($orgid, array('maxspacesize' => $setspacesize))) {
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode(array('error' => '设置不成功或未更改', 'val' => $org['maxspacesize'])));
    }

} elseif ($do == 'groupmanage') {
    prem();
    $gid = isset($_GET['gid']) ? intval($_GET['gid']): '';
    if(!$gid) exit(json_encode(array('error' => lang('explorer_do_failed'))));
    $setarr = array();
    if (isset($_GET['groupon'])) {
        $setarr['manageon'] = intval($_GET['groupon']);
    }
    if (isset($_GET['diron'])) {
        $setarr['diron'] = intval($_GET['diron']);
    }
    if (!empty($setarr)) {
        if (DB::update('organization',$setarr,array('orgid' => $gid))) {
            exit(json_encode(array('success' => true)));
        } else {
            exit(json_encode(array('error' => lang('explorer_do_failed'))));
        }
    } else {
        exit(json_encode(array('error' => lang('explorer_do_failed'))));
    }
} elseif ($do == 'delgroup') {
    prem();
    $gids = isset($_GET['gid']) ? $_GET['gid'] : '';
    if (!is_array($gids)) $gids = array($gids);
    if (!$orgs = DB::fetch_all("select orgid,orgname from %t where orgid in(%n)", array('organization', $gids))) {
        exit(json_encode(array('error' => lang('explorer_do_failed'))));
    }
    $orgarr = array();
    foreach ($orgs as $v) {
        $orgarr[$v['orgid']]['name'] = $v['orgname'];
    }
    $forgid = intval($_GET['forgid']);
    $arr = array();
    foreach ($gids as $orgid) {
        $return = C::t('organization')->delete_by_orgid($orgid);
        if ($return['error']) {
            $arr['sucessicoids'][$orgid] = $orgid;
            $arr['msg'][$orgid] = $return['error'];
            $arr['name'][$orgid] = $orgarr[$v['orgid']]['name'];
        } else {
            $arr['sucessicoids'][$orgid] = $orgid;
            $arr['msg'][$orgid] = 'success';
            $arr['name'][$orgid] = $orgarr[$v['orgid']]['name'];
        }
    }
    exit(json_encode($arr));
} else {
    require template('groupmanage');
}
function prem() {
    global $_G;
    if(!$_G['uid']) {
        exit(json_encode(array('error' => lang('not_login'))));
    }
    if ($_G['adminid'] != 1) {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }
}