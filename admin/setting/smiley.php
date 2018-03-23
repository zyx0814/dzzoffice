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
include libfile('function/cache');
$imgextarray = array('jpg', 'gif', 'png');
$operation = trim($_GET['operation']);
$id = intval($_GET['id']);
$op = $_GET['op']?$_GET['op']:' ';
if (!$operation) {
	if (!submitcheck('smileysubmit')) {
		$dirfilter = $list = array();
		foreach (C::t('imagetype')->fetch_all_by_type('smiley') as $type) {
			$type['smiliesnum'] = C::t('smiley') -> count_by_type_typeid('smiley', $type['typeid']);
			$list[] = $type;
			$dirfilter[] = $type['directory'];
			$smtypes++;
		}
		$list_no = array();
		$smdir = DZZ_ROOT . './static/image/smiley';
		$smtypedir = dir($smdir);
		$dirnum = 0;
		while ($entry = $smtypedir -> read()) {
			if ($entry != '.' && $entry != '..' && !in_array($entry, $dirfilter) && preg_match("/^\w+$/", $entry) && strlen($entry) < 30 && is_dir($smdir . '/' . $entry)) {
				$smiliesdir = dir($smdir . '/' . $entry);
				$smnums = 0;
				$smilies = '';
				while ($subentry = $smiliesdir -> read()) {
					if (in_array(strtolower(fileext($subentry)), $imgextarray) && preg_match("/^[\w\-\.\[\]\(\)\<\> &]+$/", substr($subentry, 0, strrpos($subentry, '.'))) && strlen($subentry) < 30 && is_file($smdir . '/' . $entry . '/' . $subentry)) {
						$smilies .= '<input type="hidden" name="smilies[' . $dirnum . '][' . $smnums . '][available]" value="1"><input type="hidden" name="smilies[' . $dirnum . '][' . $smnums . '][displayorder]" value="0"><input type="hidden" name="smilies[' . $dirnum . '][' . $smnums . '][url]" value="' . $subentry . '">';
						$smnums++;

					}
				}
				$list_no[$dirnum] = array('entry' => $entry, 'displayorder' => $smtypes + $dirnum + 1, 'available' => 0, 'name' => '', 'smnums' => $smnums, 'smilies' => $smilies);
				$dirnum++;
			}
		}
	} else {

		if (is_array($_GET['namenew'])) {
			foreach ($_GET['namenew'] as $id => $val) {
				$_GET['availablenew'][$id] = $_GET['availablenew'][$id] && $_GET['smiliesnum'][$id] > 0 ? 1 : 0;
				C::t('imagetype') -> update($id, array('available' => $_GET['availablenew'][$id], 'name' => dhtmlspecialchars(trim($val)), 'displayorder' => $_GET['displayordernew'][$id]));
			}
		}

		if ($_GET['delete']) {
			if (C::t('smiley') -> count_by_type_typeid('smiley', $_GET['delete'])) {
				showmessage('setting_smiley_expression_del', dreferer());
			}
			C::t('imagetype') -> delete($_GET['delete']);
		}
		if (is_array($_GET['newname'])) {
			foreach ($_GET['newname'] as $key => $val) {
				$val = trim($val);
				if ($val) {
					$smurl = './static/image/smiley/' . $_GET['newdirectory'][$key];
					$smdir = DZZ_ROOT . $smurl;
					if (!is_dir($smdir)) {
						showmessage(lang('smilies_directory_invalid', array('smurl' => $smurl)), dreferer());
					}
					$newavailable[$key] = $_GET['newavailable'][$key] && $smnums[$key] > 0 ? 1 : 0;
					$data = array('available' => $_GET['newavailable'][$key], 'name' => dhtmlspecialchars($val), 'type' => 'smiley', 'displayorder' => $_GET['newdisplayorder'][$key], 'directory' => $_GET['newdirectory'][$key], );
					$newSmileId = C::t('imagetype') -> insert($data, true);

					$smilies = update_smiles($smdir, $newSmileId, $imgextarray);
					if ($smilies['smilies']) {
						addsmilies($newSmileId, $smilies['smilies']);
						updatecache(array('smilies', 'smileycodes', 'smilies_js'));
					}
				}
			}
		}

		updatecache(array('smileytypes', 'smilies', 'smileycodes', 'smilies_js'));
		showmessage('do_success', dreferer());
	}
} elseif ($operation == 'update' && $id) {

	if (!($smtype = C::t('imagetype') -> fetch($id))) {
		showmessage('smilies_type_nonexistence', dreferer());
	} else {
		$smurl = './static/image/smiley/' . $smtype['directory'];
		$smdir = DZZ_ROOT . $smurl;
		if (!is_dir($smdir)) {
			showmessage(lang('smilies_directory_invalid', array('smurl' => $smurl)), ADMINSCRIPT . '?mod=setting&op=smiley');
		}
	}

	$smilies = update_smiles($smdir, $id, $imgextarray);

	if ($smilies['smilies']) {
		addsmilies($id, $smilies['smilies']);
		updatecache(array('smilies', 'smileycodes', 'smilies_js'));
		showmessage(lang('smilies_update_succeed', array('smurl' => $smurl, 'num' => $smilies['num'], 'typename' => $smtype['name'])), ADMINSCRIPT . '?mod=setting&op=smiley');
	} else {
		showmessage(lang('smilies_update_error', array('smurl' => $smurl)), ADMINSCRIPT . '?mod=setting&op=smiley');
	}
} elseif ($operation == 'edit' && $id) {
	$smtype = C::t('imagetype') -> fetch($id);
	$smurl = './static/image/smiley/' . $smtype['directory'];
	$smdir = DZZ_ROOT . $smurl;
	if (!is_dir($smdir)) {
		showmessage(lang('smilies_directory_invalid', array('smurl' => $smurl)), dreferer());
	}
	if (!submitcheck('editsubmit')) {

		$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
		$smiliesperpage = 100;
		$start_limit = ($page - 1) * $smiliesperpage;

		$num = C::t('smiley') -> count_by_type_typeid('smiley', $id);
		$multipage = multi($num, $smiliesperpage, $page, ADMINSCRIPT . '?mod=setting&op=smiley&operation=edit&id=' . $id);

		$smileynum = 1;
		$smilies = '';
		$list = array();
		foreach (C::t('smiley')->fetch_all_by_typeid_type($id, 'smiley', $start_limit, $smiliesperpage) as $smiley) {
			$imgfilter[] = $smiley[url];
			$list[$smileynum] = $smiley;
			$smileynum++;
		}
		include template('smileyedit');
		exit();
	} else {

		if ($_GET['delete']) {
			C::t('smiley') -> delete($_GET['delete']);
		}

		$unsfast = array();
		if (is_array($_GET['displayorder'])) {
			foreach ($_GET['displayorder'] as $key => $val) {
				if (!in_array($key, $_GET['fast'])) {
					$unsfast[] = $key;
				}
				$_GET['displayorder'][$key] = intval($_GET['displayorder'][$key]);
				$_GET['code'][$key] = trim($_GET['code'][$key]);
				$data = array('displayorder' => $_GET['displayorder'][$key], 'title' => $_GET['title'][$key]);
				if (!empty($_GET['code'][$key])) {
					$data['code'] = $_GET['code'][$key];
				}
				C::t('smiley') -> update($key, $data);
			}
		}

		updatecache(array('smilies', 'smileycodes', 'smilies_js'));
		showmessage('smilies_edit_succeed', dreferer());

	}
}
function addsmilies($typeid, $smilies = array()) {
	if (is_array($smilies)) {
		$ids = array();
		foreach ($smilies as $smiley) {
			if ($smiley['available']) {
				$data = array('type' => 'smiley', 'displayorder' => $smiley['displayorder'], 'typeid' => $typeid, 'code' => '', 'url' => $smiley['url'], );
				$ids[] = C::t('smiley') -> insert($data, true);
			}
		}
		if ($ids) {
			C::t('smiley') -> update_code_by_id($ids);
		}
	}
}

function update_smiles($smdir, $id, &$imgextarray) {
	$num = 0;
	$smilies = $imgfilter = array();
	foreach (C::t('smiley')->fetch_all_by_typeid_type($id, 'smiley') as $img) {
		$imgfilter[] = $img[url];
	}
	$smiliesdir = dir($smdir);
	while ($entry = $smiliesdir -> read()) {
		if (in_array(strtolower(fileext($entry)), $imgextarray) && !in_array($entry, $imgfilter) && preg_match("/^[\w\-\.\[\]\(\)\<\> &]+$/", substr($entry, 0, strrpos($entry, '.'))) && strlen($entry) < 30 && is_file($smdir . '/' . $entry)) {
			$smilies[] = array('available' => 1, 'displayorder' => 0, 'url' => $entry);
			$num++;
		}
	}
	$smiliesdir -> close();

	return array('smilies' => $smilies, 'num' => $num);
}

include template('smiley');
?>
