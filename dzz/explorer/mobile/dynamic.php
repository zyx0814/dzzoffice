<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
if ($operation == 'dynamiclist') {//获取动态信息
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    $fid = isset($_GET['fid']) ? trim($_GET['fid']) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    //文件信息或者动态请求
    $noselectnum = false;
    //动态数据请求
    $limit = 10;
    $start = ($page - 1) * $limit;
    $next = false;
    $nextstart = $start + $limit;
    $eventdatas = array();
	$next = false;
    if ($rid) {
        if (!is_array($rid)) $rids = explode(',', $rid);
        if (C::t('resources_event')->fetch_by_rid($rids, $start, $limit, true,1) >= $nextstart) {
            $next = $page + 1;
        }
        $events = C::t('resources_event')->fetch_by_rid($rids, $start, $limit,false,1);
    } else if($fid){
        //动态信息
        if (C::t('resources_event')->fetch_by_pfid_rid($fid, true) > $nextstart) {
            $next = $page + 1;
        }
        $events = C::t('resources_event')->fetch_by_pfid_rid($fid, '', $start, $limit, '',1);
    }
    foreach($events as $v){
        global $_G;
        $_G['currenteventpfid'] = $v['pfid'];
        $v['details'] = preg_replace_callback('/(.+?)location\.hash=\'(.+?)\'(.+?)/',function($match){
            $details =  $match[1]."location.href='".MOD_URL."&op=mobile&do=file&fid=".getglobal('currenteventpfid')."'".$match[3];
            return $details;
        },$v['details']);
        $eventdatas[] = $v;
    }
  	$param = array(
		'page'=>$next,
		'fid'=>$fid,
		'rid'=>$rid
	);
	$return = json_encode($param);
    require template('mobile/dynamic_list');
    exit();
} else {
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    $fid = isset($_GET['fid']) ? trim($_GET['fid']) : '';
    require template('mobile/dynamic');
}
