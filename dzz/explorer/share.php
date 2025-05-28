<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
$uid = $_G['uid'];
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if ($do == 'delshare') {
    if (!$_G['uid']) {
        exit(json_encode(array('error' => lang('not_login'))));
    }
    $shareid = explode(',', trim($_GET['shareid']));
    $return = array();
    foreach ($shareid as $v) {
        $result = C::t('shares')->delete_by_id($v);
        if ($result['success']) {
            $return['msg'][$v] = $result;
        } elseif ($result['error']) {
            $return['msg'][$v] = $result['error'];
        }
    }
    exit(json_encode($return));
} elseif ($do == 'filelist') {
    if (!$_G['uid']) {
        $errorResponse = [
            "code" => 1,
            "msg" => lang('no_login_operation'),
            "count" => 0,
            "data" => [],
        ];
        exit(json_encode($errorResponse));
    }
    $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
    $field = isset($_GET['sort']) ? $_GET['sort'] : 'dateline';
    $limit = empty($_GET['limit']) ? 50 : $_GET['limit'];
    $page = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
    $start = ($page - 1) * $limit;
    $validfields = ['title','downs','count','dateline','endtime'];
    $validSortOrders = ['asc', 'desc'];
    if (in_array($field, $validfields) && in_array($order, $validSortOrders)) {
        $order = " ORDER BY $field $order";
    } else {
        $order = ' ORDER BY dateline DESC';
    }
    $limitsql = "limit $start,$limit";
    $list = array();
    $count = C::t('shares')->fetch_all_share_file($limitsql, $order,true);
    if ($count) {
        $sharestatus = array('-5' => lang('sharefile_isdeleted_or_positionchange'), '-4' => '<span class="layui-badge">' . lang('been_blocked') . '</span>', '-3' => '<span class="layui-badge">' . lang('file_been_deleted') . '</span>', '-2' => '<span class="layui-badge layui-bg-gray">' . lang('degree_exhaust') . '</span>', '-1' => '<span class="layui-badge layui-bg-gray">' . lang('logs_invite_status_4') . '</span>', '0' => '<span class="layui-badge layui-bg-blue">' . lang('founder_upgrade_normal') . '</span>');
        $shareinfo = C::t('shares')->fetch_all_share_file($limitsql, $order);
        foreach ($shareinfo as $v) {
            $list[] = [
                "name" => $v['name'],
                "shareid" => $v['id'],
                "title" => '<a href="' . $v['sharelink'] . '" target="_blank"><img class="w-32 pe-2" src="'.$v['img'].'" title="'.$v['title'].'">' . $v['title'] . '</a>',
                "img" => $v['img'],
                "count" => $v['count'] ?? 0,
                "downs" => $v['downs'] ?? 0,
                "dateline" => $v['fdateline'],
                "status" => $sharestatus[$v['status']],
                "sharelink" => $v['sharelink'],
                "password" => $v['password']? $v['password'] : '',
                "endtime" => $v['expireday'],
                "qrcode" => $v['qrcode'],
                "times" => $v['times'] ? $v['count'] . '/' . $v['times'] : lang('no_limit'),
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
} else {
    Hook::listen('check_login');
    require template('share_content');
}