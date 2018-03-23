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
$applist = array();
$userconfig = DB::fetch_first("select * from " . DB::table('user_field') . " where uid='{$_G[uid]}'");
if ($userconfig['applist'])$applist = explode(',', $userconfig['applist']);
$navtitle= lang('appname');
if ($_GET['do'] == 'install') {
	$appid = intval($_GET['appid']);
	$applist[] = $appid;
	C::t('app_user') -> insert_by_uid($_G['uid'], $appid);
	if (C::t('user_field') -> update($_G['uid'], array('applist' => implode(',', $applist)))) {
		echo json_encode(array('msg' => 'success'));
		exit();
	} else {
		echo json_encode(array('error' => lang('app_installa_failed')));
		exit();
	}
}
//获取所有标签top10；
$tags = DB::fetch_all("SELECT * FROM %t WHERE hot>0 ORDER BY HOT DESC limit 100", array('app_tag'));
$keyword = trim($_GET['keyword']);
$tagid = intval($_GET['tagid']);
$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
$perpage = 20;
$gets = array('mod' => 'market', 'keyword' => $keyword, 'tagid' => $tagid, );
$theurl = DZZSCRIPT . "?" . url_implode($gets);
$refer = urlencode($theurl . '&page=' . $page);

$order = 'ORDER BY disp';
$start = ($page - 1) * $perpage;
$apps = array();
//system=2代表系统自带安装应用不能卸载  notdelete=1表示不能删除的，不能删除的直接不可见
$sql = 'system!=2 and available>0 and hideInMarket<1 and notdelete<1';
if ($keyword) {
	$sql .= " and (appname like '%$keyword%' or vendor like '%$keyword%')";
} elseif ($tagid) {
	$appids = C::t('app_relative') -> fetch_appids_by_tagid($tagid);
	$sql .= " and appid IN (" . dimplode($appids) . ")";
}
//获取用户所在组的应用
if (!$_G['uid']) {//游客
	$sql .= " and (`group`='-1' OR `group`='0')";
} elseif ($_G['adminid'] == 1) {//系统管理员
} elseif ($_G['groupid'] == 2) {//部门管理员
	$l = " (`group` = '1')";
	if ($notappids = C::t('app_organization') -> fetch_notin_appids_by_uid($_G['uid'])) {
		$l .= " and appid  NOT IN (" . dimplode($notappids) . ") ";
	}
	$sql .= " and (`group` = '2' OR `group`='0' OR (" . $l . "))";
} else {//普通成员
	$l = " (`group` = '1')";
	if ($notappids = C::t('app_organization') -> fetch_notin_appids_by_uid($_G['uid'])) {
		$l .= " and appid  NOT IN (" . dimplode($notappids) . ") ";
	}
	$sql .= " and (`group`='0' OR (" . $l . "))";
}
if ($count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('app_market') . " WHERE  $sql ")) {
	$apps = DB::fetch_all("SELECT * FROM " . DB::table('app_market') . " WHERE  $sql  $order limit $start,$perpage");
	$multi = multi($count, $perpage, $page, $theurl, 'pull-right');
}
$list = array();
//$grouptitle=array('0'=>'全部','-1'=>'仅游客可用','1'=>'成员可用','2'=>'部门管理员可用','3'=>'仅系统管理员可用');
foreach ($apps as $value) {
	if ($value['isshow'] < 1)
		continue;
	$value['tags'] = C::t('app_relative') -> fetch_all_by_appid($value['appid']);
	if ($value['appico'] != 'dzz/images/default/icodefault.png' && !preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $value['appico'])) {
		$value['appico'] = $_G['setting']['attachurl'] . $value['appico'];
	}
	$value['url'] = replace_canshu($value['appurl']);
	if (in_array($value['appid'], $applist))
		$value['isinstall'] = true;
	$list[$value['appid']] = $value;
}
$jsondata = json_encode($list);

include template('market');
?>
