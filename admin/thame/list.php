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
$list = array();

//获取所有已经安装的主题的目录
$thamefolders = array();
foreach (DB::fetch_all("select folder from %t where 1 ",array('thame')) as $value) {
	$thamefolders[$value['folder']] = $value['folder'];
}
$thamedir = DZZ_ROOT . './dzz/styles/thame';
$thamesdir = dir($thamedir);
$newthames = array();
$list = array();
while ($entry = $thamesdir -> read()) {
	if (!in_array($entry, array('.', '..')) && is_dir($thamedir . '/' . $entry) && !in_array($entry, $thamefolders)) {
		$entrydir = $thamedir . '/' . $entry;

		$filemtime = filemtime($entrydir);
		$importtxt = '';
		if (file_exists($entrydir . '/dzz_theme_' . $entry . '.xml')) {
			//echo $entrydir.'/dzz_app_'.$entry.'.xml'.'<br>';
			$importtxt = implode('', file($entrydir . '/dzz_theme_' . $entry . '.xml'));
		}
		if ($importtxt) {
			$apparray = getimportdata('Dzz! theme', 0, 1, $importtxt);
			$value = $apparray['thame'];
			if (!empty($value['name'])) {
				$value['name'] = dhtmlspecialchars($value['name']);
				$value['folder'] = dhtmlspecialchars($entry);
				$value['version'] = dhtmlspecialchars($value['version']);
				$value['vendor'] = dhtmlspecialchars($value['vendor']);
				$list[$entry] = $value;
			}
		}
	}
}
include template('list');
?>
