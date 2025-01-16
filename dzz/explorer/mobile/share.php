<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
$operation = isset($_GET['operation']) ? trim($_GET['operation']):'';
if($operation == 'filelist') {
    $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 20;//默认每页条数
    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);//页码数
    $start = ($page - 1) * $perpage;//开始条数
    $limitsql = "limit $start,$perpage";
    $disp = isset($_GET['disp']) ? intval($_GET['disp']) : 3;
    $asc = isset($_GET['asc']) ? intval($_GET['asc']):1;

    $order = $asc > 0 ? 'ASC' : "DESC";
    switch ($disp) {
        case 0:
            $orderby = 'title';
            break;
        case 1:
            $orderby = 'downs';
            break;
        case 2:
            $orderby = 'views';
            break;
        case 3:
            $orderby = 'dateline';
            break;
        case 4:
            $orderby = 'endtime';
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
    $data = C::t('shares')->fetch_all_share_file($limitsql, $ordersql);
    $next = false;
    if (count($data) >= $perpage) {
        $next = $page + 1;
    }
   
    require template('mobile/template_share_content');
    exit();
}else{
	$total= C::t('shares')->fetch_all_share_file('', '',true);
    require template('mobile/share');
}
exit();