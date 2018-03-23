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
$navtitle=lang('permission_management').' - '.lang('appname');
$op=$_GET['op'];
if ($_GET['do'] == 'clear') {
	$i = intval($_GET['i']);
	$appid = intval($_GET['appid']);
	if (!$appid)
		exit(json_encode(array('error' => lang('application_nonentity'))));
	
	$start = 0;
	foreach (DB::fetch_all("select uid,applist from %t where uid>%d order by uid limit  50",array('user_field',$i)) as $value) {
		$i = $value['uid'];
		$start++;
		$applist = $value['applist'] ? explode(',', $value['applist']) : array();
		$diff = array_diff($applist, array($appid));
		C::t('user_field') -> update($value['uid'], array('applist' => implode(',', $diff)));
	}
	$ret = array();
	if ($start < 50) {
		$ret['msg'] = 'success';
	} else {
		$ret['msg'] = 'continue';
		$ret['start'] = $i;
	}
	exit(json_encode($ret));

}
if (submitcheck('appsubmit')) {
	$setarr = array();
	foreach ($_GET['disp'] as $key => $value) {
		$setarr = array('disp' => intval($value), 'position' => intval($_GET['position'][$key]), 'notdelete' => intval($_GET['notdelete'][$key]), );
		C::t('app_market') -> update($key, $setarr);
	}
	showmessage('do_success', dreferer());
}
$positionarr = array('0' => lang('none'), '1' => lang('start_menu')/*, '2' => lang('desktop'), '3' => lang('taskbar')*/);
include libfile('function/organization');
$group = intval($_GET['group']);
$depid = intval($_GET['depid']);

$org = array();
if ($depid && $org = C::t('organization') -> fetch($depid)) {
	$orgpath = getPathByOrgid($depid);
	$orgpath = implode('-', ($orgpath));
} else {
	$orgpath = lang('select_a_organization_or_department');
}

$position = intval($_GET['position']);
$keyword = trim($_GET['keyword']);

$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
$perpage = 20;
$gets = array('mod' => 'app', 'op' => 'default', 'keyword' => $keyword, 'depid' => $depid, 'group' => $group, 'position' => $position, );
$theurl = BASESCRIPT . "?" . url_implode($gets);
$refer = urlencode($theurl . '&page=' . $page);

$order = 'ORDER BY disp';
$start = ($page - 1) * $perpage;
$list = array();
$sqlarr = array();
if ($depid) {
	//获取此机构所有下级机构的id
	if ($appids = C::t('app_organization') -> fetch_appids_by_orgid($depid, true)) {
		$sqlarr[] = "appid IN (" . dimplode($appids) . ") and `group`='1'";
	} else {
		$sqlarr[] = "appid='0'";
	}

} elseif ($group == 1) {
	$appids = array();
	// (DB::fetch_all("select appid from %t where 1 ",array('app_organization')) as $value) {
	//	$appids[$value['appid']] = $value['appid'];
	//}
	//if ($appids) {
		//$sqlarr[] = "appid NOT IN (" . dimplode($appids) . ") and `group`='1'";
	//} else {
		$sqlarr[] = "`group`='1'";
	//}

} else {
	$sqlarr[] = "`group`='{$group}'";
}
$sql = 'available>0';
if ($sqlarr) {
	$sql .= " and (" . implode('and', $sqlarr) . " )";
} else {
	$sql .= " and `group='0'";
}
if ($keyword) {
	$sql .= " and  appname like '%$keyword%'";
} elseif ($position) {
	$sql .= " and `position`='{$position}'";
}

$apps = array();

if ($count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('app_market') . " WHERE $sql ")) {
	$apps = DB::fetch_all("SELECT * FROM " . DB::table('app_market') . " WHERE $sql $order limit $start,$perpage");
	$multi = multi($count, $perpage, $page, $theurl, 'pull-right');
}
$orgs = array();
 foreach(DB::fetch_all("select a.appid,o.orgid,o.orgname from %t a LEFT JOIN %t o ON o.orgid=a.orgid where 1 ",array('app_organization','organization')) as $value) {
	$orgs[$value['appid']][] = $value;
}
foreach ($apps as $value) {
    if(isset($orgs[$value['appid']])){
		$value['orgs']=$orgs[$value['appid']];
	}
	if ($value['appico'] != 'dzz/images/default/icodefault.png' && !preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $value['appico'])) {
		$value['appico'] = $_G['setting']['attachurl'] . $value['appico'];
	}
	$value['appurl'] = BASESCRIPT . '?mod=appmarket&op=edit&appid=' . $value['appid'];
	$list[] = $value;
} 
include template('appdefault');
?>
