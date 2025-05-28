<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;
$uid = $_G['uid'];
if (isset($_GET['requestfile']) && $_GET['requestfile']) {
    $positions = array();
    $explorer_setting = get_resources_some_setting();
    $orgs = array();
    $groupinfo = array();
    if ($explorer_setting['orgonperm']) {
        $orgs = C::t('organization')->fetch_all_orggroup($uid);//机构群组
    }

    if ($explorer_setting['grouponperm']) {
        $groupinfo = C::t('organization')->fetch_group_by_uid($uid, true);//个人群组
    }

    if ($explorer_setting['useronperm']) {
        $homefid = C::t('folder')->fetch_fid_by_flag('home');
        $positions[] = array('pname' => '我的网盘', 'pfid' => $homefid);
    }

    $groups = array_merge($orgs['org'], $groupinfo);

    foreach ($groups as $v) {
        if ($v['type'] == 1) {
            $positions[] = array('pname' => $v['orgname'], 'pfid' => $v['fid'], 'type' => '群组');
        } else {
            $positions[] = array('pname' => $v['orgname'], 'pfid' => $v['fid'], 'type' => '机构');
        }

    }
    exit(json_encode($positions));
} elseif ($_GET['do'] == 'getuser') {
    $term = trim($_GET['q']);
    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);
    $perpage = 30;
    $start = ($page - 1) * $perpage;
    $uids = array();
    $param_user = array('user', 'user_status');
    $sql_user = "where u.status<1 ";

    if ($term) {
        $sql_user .= " and u.username LIKE %s";
        $param_user[] = '%' . $term . '%';
    }
    $data = array();

    if ($count = DB::result_first("select COUNT(DISTINCT u.uid) from %t u  LEFT JOIN %t s on u.uid=s.uid   $sql_user", $param_user)) {
        foreach (DB::fetch_all("select DISTINCT u.uid,u.username  from %t u LEFT JOIN %t s on u.uid=s.uid  $sql_user order by s.lastactivity DESC limit $start,$perpage", $param_user) as $value) {
            $data[] = array('id' => $value['uid'],
                'text' => $value['username']
            );
        }
    }
    exit(json_encode(array('total_count' => $count + ($extra ? 1 : 0), 'items' => $data)));

}

