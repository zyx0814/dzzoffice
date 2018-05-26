<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}
define('DZZSCRIPT', 'index.php');
//$sharestatus = array('-4' => lang('been_blocked'), '-3' => lang('file_been_deleted'), '-2' => lang('degree_exhaust'), '-1' => lang('logs_invite_status_4'), '0' => lang('founder_upgrade_normal'));
$sharestatus = array('-5'=>lang('sharefile_isdeleted_or_positionchange'),'-4' => lang('been_blocked'), '-3' => lang('file_been_deleted'), '-2' => lang('degree_exhaust'), '-1' => lang('logs_invite_status_4'), '0' => lang('founder_upgrade_normal'));
$typearr = array('folder' => lang('catalogue'), 'image' => lang('photo'), 'document' => lang('type_attach'), 'dzzdoc' => 'Dzz'.lang('type_attach'), 'video' => lang('type_video'), 'attach' => lang('attachment'), 'link' => lang('type_link'), 'url' => lang('other'));
$type = trim($_GET['type']);
$keyword = trim($_GET['keyword']);
$username = trim($_GET['username']);
$asc = isset($_GET['asc']) ? intval($_GET['asc']) : 1;
$uid = intval($_GET['uid']);
$order = in_array($_GET['order'], array('title', 'dateline', 'type', 'size', 'count')) ? trim($_GET['order']) : 'dateline';
$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
$perpage = 20;
$start = ($page - 1) * $perpage;
$gets = array('mod' => 'share', 'type' => $type, 'keyword' => $keyword, 'order' => $order, 'asc' => $asc, 'uid' => $uid, 'username' => $username);
$theurl = BASESCRIPT . "?" . url_implode($gets);
$orderby = " order by $order " . ($asc ? 'DESC' : '');

$sql = "1";
$param = array('shares');
if ($type) {
	$sql .= " and type=%s";
	$param[] = $type;
	$navtitle=$typearr[$type].' - '.lang('appname');
}else{
	$navtitle= lang('appname');
}
if ($keyword) {
	$sql .= " and title LIKE %s";
	$param[] = '%' . $keyword . '%';
}
if ($username) {
	$sql .= " and username=%s";
	$param[] = $username;
}
if ($uid) {
	$sql .= " and uid=%d";
	$param[] = $uid;
} 
$list = array();
if ($count = DB::result_first("SELECT COUNT(*) FROM %t WHERE $sql", $param)) {
	$list = DB::fetch_all("SELECT * FROM %t WHERE $sql $orderby limit $start,$perpage", $param); 
	foreach ($list as $k=> $value) {
		$value['sharelink'] =  C::t('shorturl')->getShortUrl(getglobal('siteurl').'index.php?mod=shares&sid='.dzzencode($value['id']));
		if ($value['dateline'])
			$value['fdateline'] = dgmdate($value['dateline']);
		if ($value['password'])
			$value['password'] = dzzdecode($value['password']);
		if ($value['endtime'])
			$value['fendtime'] = dgmdate($value['endtime'], 'Y-m-d');
		$value['fsize'] = formatsize($value['size']);
		$value['ftype'] = getFileTypeName($value['type'], $value['ext']);
		if ($value['type'] == 'folder')
			$value['img'] = 'dzz/images/extimg/folder.png';
		if ($value['img'])
			$value['img'] = str_replace('dzz/images/extimg/', 'dzz/images/extimg_small/', $value['img']);
		if ($value['type'] == 'image' && $value['status'] == -3)
			$value['img'] = '';
		$value['fstatus'] = $sharestatus[$value['status']];
		if (is_file($_G['setting']['attachdir'] . './qrcode/' . $value['sid'][0] . '/' . $value['sid'] . '.png'))
			$value['qrcode'] = $_G['setting']['attachurl'] . './qrcode/' . $value['sid'][0] . '/' . $value['sid'] . '.png';
		$value['shareurl'] = $_G['siteurl'] . 's.php?sid=' . $value['sid'];
		$list[$k] = $value;
	}
	$multi = multi($count, $perpage, $page, $theurl, 'pull-right');
}
include template('share');
?>
