<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @version     DzzOffice 1.1
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ')) {
	exit('Access Denied');
}
if ($_GET['do'] == 'imageUpload') {

	include libfile('class/uploadhandler');
	$options = array(
		'param_name' => 'dzzvotefiles', 
		'accept_file_types' => '/\.(gif|jpe?g|png)$/i', 
		'upload_dir' => $_G['setting']['attachdir'] . 'cache/', 'upload_url' => $_G['setting']['attachurl'] . 'cache/', 
		'thumbnail' => array('max-width' => 240, 'max-height' => 160)
	);
	$upload_handler = new uploadhandler($options);
	exit();
} elseif ($_GET['do'] == 'getvotepost') {
	$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
	$idtype = trim($_GET['idtype']);
	$vote = C::t('vote') -> fetch_by_id_idtype($id, $idtype);
	if (!$vote)
		$vote['voteid'] = 'v_'.random(1);
	if ($vote['endtime'])
		$vote['endtime'] = dgmdate($vote['endtime'], 'Y-m-d');
	else
		$vote['endtime'] = '';

} elseif ($_GET['do'] == 'getvote') {
	$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
	$idtype = trim($_GET['idtype']);
	$vote = C::t('vote') -> fetch_by_id_idtype($id, $idtype);

	//处理投票状态

	$endtime = '';
	$status = 0;
	$today = strtotime(dgmdate(TIMESTAMP, 'Y-m-d'));

	if (!$vote['endtime']) {
		$endtime = lang('there_no_deadline');
		$status = 0;
	} elseif ($vote['endtime'] < $today) {
		$endtime = lang('voting_ended');
		$status = 2;
	} else {
		$darr = getdate($vote['endtime'] - TIMESTAMP - getglobal('member/timeoffset') * 3600);
		if ($darr['yday'])
			$endtime .= $darr['yday'] . lang('day');
		if ($darr['hours'])
			$endtime .= $darr['hours'] . lang('hour');
		if ($darr['minutes'])
			$endtime .= $darr['minutes'] . lang('minute');
		if ($darr['seconds'])
			$endtime .= $darr['seconds'] . lang('sec');
		//$endtime=date("d天H小时i分s秒",$vote['endtime']-TIMESTAMP);
	}
	//投票总人数
	$itemids = array();
	$votetotal = 0;
	foreach ($vote['items']['type_'.$vote['type']] as $value) {
		$itemids[] = $value['itemid'];
		$votetotal += $value['number'];
	}

	$votesum = DB::result_first("select COUNT(DISTINCT uid) from %t where itemid IN(%n)", array('vote_item_count', $itemids));
	if (DB::result_first("select COUNT(*) from %t where itemid IN(%n) and uid=%d", array('vote_item_count', $itemids, $_G['uid'])))
		$status = 1;
	if (!$_G['uid'])
		$status = 3;
	//游客无权投票；
	//if($voteuids) $voteuser=DB::fetch_all("select uid,username,avatarstatus from %t where uid IN (%n) limit 6",array('user',$voteuids));
} elseif ($_GET['do'] == 'getvoteuser') {
	$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
	$perpage = 20;
	$start = $perpage * ($page - 1);
	$nextpage = 0;
	$count = 0;
	$voteuser = array();
	$voteid = empty($_GET['voteid']) ? 0 : intval($_GET['voteid']);
	$vote = C::t('vote') -> fetch_by_voteid($voteid);
	$itemids = array();
	$votetotal = 0;
	foreach ($vote['items']['type_'.$vote['type']] as $value) {
		$itemids[] = $value['itemid'];
	}
	if ($itemids && $vote['showuser']) {//获取投票人
		if ($count = DB::result_first("select COUNT(DISTINCT uid) from %t where itemid IN(%n)", array('vote_item_count', $itemids))) {
			foreach (DB::fetch_all("select DISTINCT u.uid,u.username,u.avatarstatus from %t c LEFT JOIN %t u ON c.uid=u.uid where itemid IN(%n) order by c.dateline DESC limit $start,$perpage",array('vote_item_count','user',$itemids)) as $value) {
				$voteuser[$value['uid']] = $value;
			}
		}
	}
	if ($count > $page * $perpage) {
		$nextpage = $page + 1;
	}
	$theurl = DZZSCRIPT . '?mod=dzzvote&op=ajax&do=getvoteuser&voteid=' . $voteid . '&page=' . $nextpage;
	include template('vote_user');
	exit();
} elseif ($_GET['do'] == 'getvoteresult') {
	$voteid = empty($_GET['voteid']) ? 0 : intval($_GET['voteid']);

	$vote = C::t('vote') -> fetch_by_voteid($voteid);

	//处理投票状态

	$endtime = '';
	$status = 0;
	$today = strtotime(dgmdate(TIMESTAMP, 'Y-m-d'));
	if (!$vote['endtime']) {
		$endtime = lang('there_no_deadline');
		$status = 0;
	} elseif ($vote['endtime'] < $today) {
		$endtime = lang('voting_ended');
		$status = 2;
	} else {
		$darr = getdate($vote['endtime'] - TIMESTAMP - getglobal('member/timeoffset') * 3600);
		if ($darr['yday'])
			$endtime .= $darr['yday'] . lang('day');
		if ($darr['hours'])
			$endtime .= $darr['hours'] . lang('hour');
		if ($darr['minutes'])
			$endtime .= $darr['minutes'] . lang('minute');
		if ($darr['seconds'])
			$endtime .= $darr['seconds'] . lang('sec');		
		//$endtime=date("d天H小时i分s秒",$vote['endtime']-TIMESTAMP);
	}
	//投票总人数
	$itemids = array();
	$votetotal = 0;
	foreach ($vote['items']['type_'.$vote['type']] as $value) {
		$itemids[] = $value['itemid'];
		$votetotal += $value['number'];
	}
	$votesum = DB::result_first("select COUNT(DISTINCT uid) from %t where itemid IN(%n)", array('vote_item_count', $itemids));
	//if($voteuids) $voteuser=DB::fetch_all("select uid,username from %t where uid IN(%n) limit 6",array('user',$voteuids));

} elseif ($_GET['do'] == 'itemdelete') {

	if ($itemid = empty($_GET['itemid']) ? 0 : intval($_GET['itemid'])) {
		C::t('vote_item') -> delete_by_itemid($itemid);
	}
	exit(json_encode(array('msg' => 'success')));
} elseif ($_GET['do'] == 'itemvote') {
	if (submitcheck('votesubmit')) {
		$itemids = $_GET['vote'];
		C::t('vote_item') -> update_number_by_itemid($itemids, $_G['uid']);
		showmessage('do_success', DZZSCRIPT, array(), array('showmsg' => true));
	}
}
//error_reporting(E_ALL);
include template('vote_ajax');
?>
