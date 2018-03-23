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
include libfile('function/admin');
include libfile('function/organization');
$do = empty($_GET['do']) ? 'available' : trim($_GET['do']);

$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
$perpage = 20;
$gets = array('mod' => 'app', 'op' => 'import', 'do' => $do, );
$theurl = BASESCRIPT . "?" . url_implode($gets);
$refer = urlencode($theurl . '&page=' . $page);

$order = 'ORDER BY disp';
$start = ($page - 1) * $perpage;
$apps = array();
if ($do == 'available') {
	if ($count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('app_market') . " WHERE  available<1")) {
		$apps = DB::fetch_all("SELECT * FROM " . DB::table('app_market') . " WHERE  available<1  limit $start,$perpage");
		$multi = multi($count, $perpage, $page, $theurl, 'pull-right');
	}
} elseif ($do == 'notinstall') {
	$identifiers = C::t('app_market') -> fetch_all_identifier(); 
	
	$list=search_app('dzz',$identifiers);
	$list2=search_app('admin',$identifiers);
	$list3=search_app('user',$identifiers);
	$list=array_merge($list,$list2,$list3); 

} elseif ($do == 'upgrade') {
	$sql = '';
	if ($group) {
		$sql = " and `group` = '{$group}'";
	}
	if ($count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('app_market') . " WHERE 1 $sql")) {
		$apps = DB::fetch_all("SELECT * FROM " . DB::table('app_market') . " WHERE 1 $sql $order limit $start,$perpage");
		$multi = multi($count, $perpage, $page, $theurl, 'pull-right');
	}
}

$list = array();
$grouptitle = array('0' => lang('all'), '-1' => lang('visitors_visible'), '1' => lang('members_available'), '2' => lang('section_administrators_available'), '3' => lang('system_administrators_available'));
foreach ($apps as $value) {

	$value['tags'] = C::t('app_relative') -> fetch_all_by_appid($value['appid']);
	if ($value['appico'] != 'dzz/images/default/icodefault.png' && !preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $value['appico'])) {
		$value['appico'] = $_G['setting']['attachurl'] . $value['appico'];
	}
	$value['appurl'] = replace_canshu($value['appurl']);
	$value['grouptitle'] = $grouptitle[$value['group']];
	$value['department'] = getDepartmentByAppid($value['appid']);
	$list[] = $value;
}


function search_app($dir,$identifiers){
	$plugindir = DZZ_ROOT . './'.$dir;
	$pluginsdir = dir($plugindir);
	$newplugins = array();
	$list = array();
	while ($entry = $pluginsdir -> read()) {
		if (!in_array($entry, array('.', '..')) && is_dir($plugindir . '/' . $entry) && !in_array($entry, $identifiers)) {
			$entrydir = DZZ_ROOT . './'.$dir.'/' . $entry;
			$d = dir($entrydir);
			$filemtime = filemtime($entrydir);
			$entrytitle = $entry;
			$entryversion = $entrycopyright = $importtxt = '';
			if (file_exists($entrydir . '/dzz_app_' . $entry . '.xml')) {
				$importtxt = @implode('', file($entrydir . '/dzz_app_' . $entry . '.xml'));
			}
			if ($importtxt) {
				$pluginarray = getimportdata('Dzz! app', 0, 1);
				if (!empty($pluginarray['plugin']['name'])) {
					$pluginarray['plugin']['name'] = dhtmlspecialchars($pluginarray['plugin']['name']);
					$pluginarray['plugin']['version'] = dhtmlspecialchars($pluginarray['plugin']['version']);
					$pluginarray['plugin']['copyright'] = dhtmlspecialchars($pluginarray['plugin']['copyright']);
				}
				$list[$entry] = $pluginarray;
			}
		}
	}
	return $list;
}

include template('import');
?>
