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
$refer = dreferer();
//error_reporting(E_ALL);

$id = intval($_GET['id']);

if (submitcheck('thamesubmit')) {
	$thame = $_GET['thame'];
	if (empty($thame['folder']) || empty($thame['name'])) {
		showmessage('set_incomplete_unable_add', $refer);
	}
	$thame['name'] = getstr($thame['name'], 80);
	$thame['vendor'] = getstr($thame['vendor'], 80);
	$thame['modules'] = serialize($thame['modules']);
	if ($thame['default'] > 0) {
		DB::update("thame", array('default' => 0), "`default`='1'");
	}
	if ($id) {
		$data = C::t('thame') -> fetch($id);
		if (DB::result_first("select COUNT(*) from %t where folder=%s and folder!=%s", array('thame', trim($data['folder']), $thame['folder']))) {
			showmessage('theme_edit_already_existed', dreferer());
		}
		if (DB::update('thame', $thame, "id='{$id}'"))
			showmessage(lang('edit_success').'！', dreferer(), array(), array('alert' => 'right'));
	} else {
		if (DB::result_first("select COUNT(*) from %t where folder=%s", array('thame', trim($thame['folder'])))) {
			showmessage('theme_edit_already_existed', dreferer());
		}
		if (DB::insert('thame', $thame)) {
			showmessage(lang('theme_add_success').'！', BASESCRIPT . '?mod=thame', array(), array('alert' => 'right'));
		}
	}

} else {
	//获取所有已经安装的主题的目录
	$thamefolders = array();
	foreach (DB::fetch_all("select folder from %t where 1 ",array('thame')) as $value) {
		$thamefolders[$value['folder']] = $value['folder'];
	}
	if ($thame = DB::fetch_first("select * from %t where id=%d", array('thame', $id))) {
		$thame['modules'] = unserialize(stripslashes($thame['modules']));
		unset($thamefolders[$thame['folder']]);
	} else {
		$thame = array();
	}
	//获取目录；
	$styles = array(
		'thame' => lang('subject_catalog'), 
		'window' => lang('common_window'), 
		'filemanage' => lang('file_window'), 
		'icoblock' => lang('ico_style'), 
		'menu' => lang('right_click_menu_style'), 
		'startmenu' => lang('start_menu'), 
		'taskbar' => lang('taskbar')
	);
	$flags = array_keys($styles);
	$thames = array();
	$thamedir = DZZ_ROOT . './dzz/styles';
	$dirhandle = dir($thamedir);
	while ($entry = $dirhandle -> read()) {
		if (in_array($entry, $flags) && is_dir($thamedir . '/' . $entry)) {
			$dirhandle1 = dir($thamedir . '/' . $entry);
			while ($entry1 = $dirhandle1 -> read()) {
				if (!in_array($entry1, array('.', '..')) && is_dir($thamedir . '/' . $entry . '/' . $entry1)) {
					$thames[$entry][] = $entry1;
				}
			}
		}
	}
	//按照styles顺序排序
	$thameitems = array();
	foreach ($styles as $key => $value) {
		if ($thames[$key]) {
			if ($key == 'thame') {
				foreach ($thames[$key] as $i => $value1) {
					if (in_array($value1, $thamefolders))
						unset($thames[$key][$i]);
				}
			}
			$thameitems[$key] = $thames[$key];
		}
	}
	$folders = $thameitems['thame'];
	unset($thameitems['thame']);
	unset($styles['thame']);
}
include template('edit');
?>
