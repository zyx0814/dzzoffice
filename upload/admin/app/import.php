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
	//$identifiers = C::t('app_market') -> fetch_all_identifier();

	$plugindir = DZZ_ROOT . './dzz';
	$pluginsdir = dir($plugindir);
	$newplugins = array();
	print_r($plugindir);exit('ddd');
	$list = array();
	while ($entry = $pluginsdir -> read()) {
		echo $entry;
		if (!in_array($entry, array('.', '..')) && is_dir($plugindir . '/' . $entry) && !in_array($entry, $identifiers)) {
			$entrydir = DZZ_ROOT . './dzz/' . $entry;
			$filemtime = filemtime($entrydir);
			$entrytitle = $entry;
			$entryversion = $entrycopyright = $importtxt = '';
			if (file_exists($entrydir . '/dzz_app_' . $entry . '.xml')) {
				$importtxt = @implode('', file($entrydir . '/dzz_app_' . $entry . '.xml'));
			}else{
				$plugindir1 = $entrydir;
				$pluginsdir1 = dir($plugindir1);
				while ($entry1 = $pluginsdir1 -> read()) {
					if (!in_array($entry1, array('.', '..')) && is_dir($plugindir1 . '/' . $entry1) && !in_array($entry.':'.$entry1, $identifiers)) {
						$entrydir1 = $entrydir.'/'. $entry1;
						//$filemtime = filemtime($entrydir1);
						$entrytitle1 = $entry1;
						$entryversion1 = $entrycopyright1 = $importtxt = '';
						exit($entrydir1 . '/dzz_app_' . $entry.'_'.$entry1 . '.xml<br>');
						if (file_exists($entrydir1 . '/dzz_app_' . $entry.'_'.$entry1 . '.xml')) {
							$importtxt = @implode('', file($entrydir1 . '/dzz_app_' . $entry.'_'.$entry1 . '.xml'));
						}
						if ($importtxt) {
							$pluginarray1 = getimportdata('Dzz! app', 0, 1);
							if (!empty($pluginarray1['plugin']['name'])) {
								$pluginarray1['plugin']['name'] = dhtmlspecialchars($pluginarray1['plugin']['name']);
								$pluginarray1['plugin']['version'] = dhtmlspecialchars($pluginarray1['plugin']['version']);
								$pluginarray1['plugin']['copyright'] = dhtmlspecialchars($pluginarray1['plugin']['copyright']);
							}
							$list[$entry.':'.$entry1] = $pluginarray1;
						}
						exit($entry);
					}
					
				}
							
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

include template('import');
?>
