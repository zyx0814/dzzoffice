<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ')) {
	exit('Access Denied');
}
global $_G;
$osid = $_GET['sid'];
$morepath = $osid;
$sid=dzzdecode($osid);
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if($do == 'adddowns'){
	C::t('shares')->add_downs_by_id($sid);
	exit(json_encode(array('success'=>true)));
}else{
	$share=C::t('shares')->fetch($sid);
	if(!$share){
        showmessage('share_file_iscancled');
	}
	if ($share['status'] == -4)showmessage('shared_links_screened_administrator');
    if ($share['status'] == -5)showmessage('sharefile_isdeleted_or_positionchange');
//判断是否过期
	if ($share['endtime'] && $share['endtime'] < TIMESTAMP) {
		showmessage('share_link_expired');
	}
	if ($share['times'] && $share['times'] <= $share['count']) {
		showmessage('link_already_reached_max_number');
	}

	if ($share['status'] == -3) {
		showmessage('share_file_deleted');
	}
	if ($share['password'] && (dzzdecode($share['password']) != authcode($_G['cookie']['pass_' . $sid]))) {
		if (submitcheck('passwordsubmit')) {
			if ($_GET['password'] != dzzdecode($share['password'])) {
				include  template('common/shares_password');
				exit();
			}
			dsetcookie('pass_' . $sid, authcode($_GET['password'], 'ENCODE'));
		} else {
			include  template('common/shares_password');
			exit();
		}
	}
	$sharestatus = array(
		'-4' => lang('has_blocked'),
		'-3' => lang('file_been_deleted'),
		'-2' => lang('exhaust'),
		'-1' => lang('have_expired'),
		'0' => lang('normal')
	);
	$typearr = array(
		'folder' => lang('catalogue'),
		'image' => lang('photo'),
		'app' => lang('type_app'),
		'link' => lang('type_link'),
		'video' => lang('type_video'),
		'attach' => lang('typename_attach'),
		'document' => lang('type_attach'),
		'dzzdoc' => lang('extname_dzzdoc'),
		'url' => lang('rest')
	);
	$share['fdateline'] = dgmdate($share['dateline'],'Y-m-d');
	if($share['endtime']){
		$timediff = ($share['endtime'] - $share['dateline']);
		$days = 0;
		if($timediff > 0){
			$days = ceil($timediff/86400);
		}
		$share['expireday'] = ($days > 0) ? $days.'天后':'已过期';
	}else{
		$share['expireday'] = '永久有效';
	}
	$rids = explode(',',$share['filepath']);
	if(count($rids) > 1){
		$share['img'] = '/dzz/explorer/img/ic-files.png';
	}else{
		$share['img'] =  C::t('resources')->get_icosinfo_by_rid($share['filepath']);
	}
	$shareuser = C::t('user')->fetch($share['uid']);
	$shareusername = $shareuser['username'];
    //增加浏览次数
    C::t('shares')->add_views_by_id($sid);
	$page = (isset($_GET['page'])) ? intval($_GET['page']):1;
	$perpage = 20;
	$start = ($page - 1) * $perpage;
	$gets = array('mod' => 'shares', 'sid' => $sid, );
	$theurl = BASESCRIPT . "?" . url_implode($gets);
	$ordersql = '';
	$asc = (isset($_GET['asc'])) ? intval($_GET['asc']):1;
	$disp = (isset($_GET['disp'])) ? intval($_GET['disp']):0;
	$order = ($asc > 0) ? 'ASC':'DESC';
	switch ($disp) {
		case 0:
			$orderby = 'name';
			break;
		case 1:
			$orderby = 'size';
			break;
		case 2:
			$orderby = array('type', 'ext');
			break;
		case 3:
			$orderby = 'dateline';
			break;
	}
	if(is_array($orderby)){
		foreach($orderby as $key=>$value){
			$orderby[$key]=$value.' '.$order;
		}
		$ordersql=' ORDER BY '.implode(',',$orderby);
	}elseif($orderby){
		$ordersql=' ORDER BY '.$orderby.' '.$order;
	}
	$limitsql = 'limit '.$start .','. ($start + $perpage);
//获取分享文件rid信息
	$filepaths = $share['filepath'];
	$rids  = explode(',',$filepaths);

	$params = array('resources',$rids);
	$wheresql = " where rid in(%n) and isdelete < 1";
	$list = array();
    $allrids = '';

	$count = DB::result_first("select count(*) from %t $wheresql $ordersql $limitsql",$params);
//获取分享数据
	foreach(DB::fetch_all("select rid from %t $wheresql $ordersql $limitsql",$params) as $v){
		$fileinfo = getfileinfo($v['rid']);
		if($fileinfo['type'] == 'folder' && $fileinfo['oid']) {
			$oid = $fileinfo['oid'];
			$fileinfo['dhpath'] = $oid;
		}
		$list[] = $fileinfo;
        $allrids .= dzzencode($v['rid']).',';
	}
	if(count($list) < 1){
		DB::update('shares',array('status'=>'-3'),array('id'=>$sid));
		showmessage('share_file_deleted');
	}
    $allrids =substr($allrids,0,-1);
    if (count($list) >= $perpage) {
        $nextpage = $page + 1;
    } else {
        $naxtpage = 0;
    }
	include template('list');
}
?>
