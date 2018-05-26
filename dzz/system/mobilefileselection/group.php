<?php
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;
$uid = $_G['uid'];
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';

$limit = isset($_GET['perpage']) ? intval($_GET['perpage']) : 10;//默认每页条数
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
        if (intval($orginfo['aid']) > 0) {
            //群组图
            $orginfo['imgs'] = 'index.php?mod=io&op=thumbnail&width=45&height=45&path=' . dzzencode('attach::' . $orginfo['aid']);
        } /*else {
            $orginfo['imgs'] = avatar_group($v['orgid'], array($orginfo['orgid'] => array('aid' => $orginfo['aid'], 'orgname' => $orginfo['orgname'])));
        }*/
        $contaions = C::t('resources')->get_contains_by_fid($orginfo['fid']);
        $orginfo['filenum'] = $contaions['contain'][0];
        $orginfo['foldernum'] = $contaions['contain'][1];
        $groups[] = $orginfo;
    }
}
require template('mobilefileselection/group');