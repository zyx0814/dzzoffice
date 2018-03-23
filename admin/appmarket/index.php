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
$navtitle=lang('installed').' - '.lang('appname');
include libfile('function/organization');
$op='index';
if (submitcheck('appsubmit')) {
	$dels = $_GET['del'];
	$allids = array();
	foreach ($_GET['disp'] as $key => $value) {
		if (!in_array($key, $dels))
			C::t('app_market') -> update($key, array('disp' => $value));
	}
	//删除应用
	if ($dels) {
		C::t('app_market') -> delete_by_appid($dels);
	}
	showmessage('do_success', dreferer());
}
//获取所有标签top50；
$tags = DB::fetch_all("SELECT * FROM %t WHERE hot>0 ORDER BY HOT DESC limit 50", array('app_tag'),'tagid');

$keyword = trim($_GET['keyword']);
$tagid = intval($_GET['tagid']);
$group = intval($_GET['group']);
$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
$perpage = 20;
$gets = array('mod' => 'appmarket', 'keyword' => $keyword, 'tagid' => $tagid, 'group' => $group);
$theurl = BASESCRIPT . "?" . url_implode($gets);
$refer = urlencode($theurl . '&page=' . $page);

$order = ' ORDER BY disp';
$start = ($page - 1) * $perpage;
$apps = array();
$string = " 1 ";
if ($keyword) {
	$string .= " and appname like '%$keyword%' or vendor like '%$keyword%'";
}
if ($tagid) {
	$appids = C::t('app_relative') -> fetch_appids_by_tagid($tagid);
	$string .= " and appid IN (" . dimplode($appids) . ")";
}
if ($group) {
	$sql = " and `group` = '{$group}'";
	$string .= " and `group` = '{$group}'";
}
if ($count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('app_market') . " WHERE ".$string)) {
	$apps = DB::fetch_all("SELECT * FROM " . DB::table('app_market') . " WHERE ".$string." $order limit $start,$perpage");
	$multi = multi($count, $perpage, $page, $theurl, 'pull-right');
}

$list = array();
$grouptitle = array('0' => lang('all'), '-1' => lang('visitors_visible'), '1' => lang('members_available'), '2' => lang('section_administrators_available'), '3' => lang('system_administrators_available'));
foreach ($apps as $value) {
	$value['tags'] = C::t('app_relative') -> fetch_all_by_appid($value['appid']);
	if ($value['appico'] != 'dzz/images/default/icodefault.png' && !preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $value['appico'])) {
		$value['appico'] = $_G['setting']['attachurl'] . $value['appico'];
	}
	$value['appurl'] = replace_canshu($value['appurl']);
	$value['appadminurl'] = replace_canshu($value['appadminurl']);
	$value['grouptitle'] = $grouptitle[$value['group']];
	$value['department'] = getDepartmentByAppid($value['appid']);
	$list[] = $value;
} 
include template('index');
?>
