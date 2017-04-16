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
$operation = trim($_GET['operation']);
$do = trim($_GET['do']);

if ($operation == 'color') {
	$page = intval($_GET['page']) < 1 ? 1 : intval($_GET['page']);
	$list = array();
	$perpage = 20;
	$start = ($page - 1) * $perpage;
	$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('wallpaper') . " where type='color'");
	if ($count) {
		$query = DB::query("SELECT * FROM " . DB::table('wallpaper') . " where type='color' ORDER BY dateline DESC LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			$value['dateline'] = dgmdate($value['dateline']);
			$list[] = $value;
		}
	}
	$multi = multi($count, $perpage, $page, BASESCRIPT . "?mod=$mod&operation=$operation");

} elseif ($operation == 'syscolor') {
	$page = intval($_GET['page']) < 1 ? 1 : intval($_GET['page']);
	$list = array();
	$perpage = 20;
	$start = ($page - 1) * $perpage;
	$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('wallpaper') . " where type='syscolor'");
	if ($count) {
		$query = DB::query("SELECT * FROM " . DB::table('wallpaper') . " where type='syscolor' ORDER BY dateline DESC LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			$value['dateline'] = dgmdate($value['dateline']);
			$list[] = $value;
		}
	}
	$multi = multi($count, $perpage, $page, BASESCRIPT . "?mod=$mod&operation=$operation");

} elseif ($operation == 'scale' || $operation == 'repeat') {
	if ($do == 'add') {
		$setarr = array('title' => trim($_GET['title']), 'type' => $operation, 'classid' => intval($_GET['classid']), 'disp' => 0, 'dateline' => $_G['timestamp']);

		$upload = dzz_backimg_save($_FILES['backimg']);

		if ($upload && is_array($upload)) {
			$setarr['val'] = $_G['setting']['attachurl'] . $upload['filepath'];
			$setarr['thumb'] = $upload['thumb'];
		} else {
			showmessage($upload, $refer);
		}
		if (DB::insert('wallpaper', ($setarr))) {
			showmessage('add_success', $refer);
		}
	} else {
		$classid = intval($_GET['classid']);
		$class = array();
		$query = DB::query("select * from " . DB::table('wallpaper_class') . " where type='{$operation}' ORDER BY disp DESC");
		while ($value = DB::fetch($query)) {
			$class[] = $value;
		}
		$page = intval($_GET['page']) < 1 ? 1 : intval($_GET['page']);
		$list = array();
		$perpage = 100;
		$start = ($page - 1) * $perpage;
		$wheresql = "type='{$operation}'";
		if ($classid)
			$wheresql .= " and classid='{$classid}'";
		$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('wallpaper') . " where $wheresql");
		if ($count) {
			$query = DB::query("SELECT * FROM " . DB::table('wallpaper') . " where $wheresql ORDER BY dateline DESC LIMIT $start,$perpage");
			while ($value = DB::fetch($query)) {
				$value['dateline'] = dgmdate($value['dateline']);
				if ($value['thumb'])
					$value['thumbpic'] = $value['val'] . '.thumb.jpg';
				else
					$value['thumbpic'] = $value['val'];
				$list[] = $value;
			}
		}
		$multi = multi($count, $perpage, $page, BASESCRIPT . "?mod=$mod&operation=$operation&classid=$classid");
	}

} elseif ($operation == 'url') {
	if ($do == 'add') {
		$setarr = array('title' => trim($_GET['title']), 'val' => trim($_GET['val']), 'type' => 'url', 'classid' => 0, 'disp' => 0, 'dateline' => $_G['timestamp']);
		if (empty($_GET['val']))showmessage('must_fill_address_wallpaper');
		$upload = dzz_backimg_save($_FILES['backimg']);
		if ($upload && is_array($upload)) {
			$setarr['img'] = $_G['setting']['attachurl'] . $upload['filepath'];
			$setarr['thumb'] = $upload['thumb'];
		} else {
			showmessage('please_upload_thumbnail', $refer);
		}
		if (DB::insert('wallpaper', ($setarr))) {
			showmessage(lang('dynamic_state_wallpaper_add_success'), $refer);
		}
	} else {
		$page = intval($_GET['page']) < 1 ? 1 : intval($_GET['page']);
		$list = array();
		$perpage = 20;
		$start = ($page - 1) * $perpage;
		$wheresql = "type='url'";
		if ($classid)
			$wheresql .= " and classid='{$classid}'";
		$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('wallpaper') . " where $wheresql");
		if ($count) {
			$query = DB::query("SELECT * FROM " . DB::table('wallpaper') . " where $wheresql ORDER BY dateline DESC LIMIT $start,$perpage");
			while ($value = DB::fetch($query)) {
				$value['dateline'] = dgmdate($value['dateline']);
				if ($value['thumb'])
					$value['thumbpic'] = $value['img'] . '.thumb.jpg';
				else
					$value['thumbpic'] = $value['img'];
				$list[] = $value;
			}
		}
		$multi = multi($count, $perpage, $page, ADMINSCRIPT . "?action=$action&operation=$operation");
	}

} elseif ($operation == 'class') {
	$ids = $_GET['ids'];
	$deletes = $_GET['del'];
	foreach ($ids as $id) {
		if (!in_array($id, $deletes)) {
			$setarr = array('classname' => getstr($_GET['classname'][$id], 80, 0, 0, 0, -1), 'disp' => intval($_GET['disp'][$id]), );

			DB::update('wallpaper_class', ($setarr), "classid='{$id}'");
		}
	}
	foreach ($_GET['newclassname'] as $key => $value) {
		if (empty($value))continue;
		$setarr = array('classname' => getstr($value, 80, 0, 0, 0, -1), 'disp' => intval($_GET['newdisp'][$key]), 'type' => $_GET['type'], );
		DB::insert('wallpaper_class', ($setarr));
	}
	if ($deletes) {
		DB::update('wallpaper', array('classid' => 0), "classid IN (" . dimplode($deletes) . ")");
		DB::delete('wallpaper_class', "classid IN (" . dimplode($deletes) . ")");
	}
	showmessage(lang('classify_save_success').'！', BASESCRIPT . '?mod=thame&operation=' . $_GET['type'] . '&do=class');
} else {
	$thames = array();
	$folder = array();
	$query = DB::query("SELECT * FROM " . DB::table('thame') . "  where 1 ORDER BY dateline DESC");
	while ($value = DB::fetch($query)) {
		if (!$value['thumb'])
			$value['thumb'] = 'dzz/styles/thame/' . $value['folder'] . "/thumb.jpg";
		$thames[] = $value;
		$folder[] = $value['folder'];
	}
	$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
	$thames = array();
	$perpage = 300;
	//$perpage = mob_perpage($perpage);
	$start = ($page - 1) * $perpage;
	$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('thame') . " where 1 ");
	if ($count) {
		$query = DB::query("SELECT * FROM " . DB::table('thame') . "  where 1 ORDER BY dateline  LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			if (!$value['thumb'])
				$value['thumb'] = 'dzz/styles/thame/' . $value['folder'] . '/thumb.jpg';
			$value['modules'] = unserialize(stripslashes($value['modules']));
			$thames[] = $value;
		}
	}
	$multi = multi($count, $perpage, $page, BASESCRIPT . "?mod=thame");
}

function dzz_backimg_save($FILE, $dir = 'appimg') {
	global $_G;
	$allowpictype = array('jpg', 'jpeg', 'gif', 'png');
	$upload = new dzz_upload();
	$upload -> init($FILE, $dir);

	if ($upload -> error()) {
		return $upload -> errormessage();
	}

	if (!$upload -> attach['isimage']) {
		return lang('only_upload_img_file');
	}
	$upload -> save();
	if ($upload -> error()) {
		return lang('save_unsuccess');
	}
	$setarr = array('filepath' => $dir . '/' . $upload -> attach['attachment'], 'thumb' => 0, );
	//生成缩略图
	require_once libfile('class/image');
	$image = new image;
	if ($thumb = $image -> Thumb($_G['setting']['attachurl'] . $setarr['filepath'], '', 101, 101, 1)) {
		$setarr['thumb'] = $thumb;
	}
	return $setarr;
}

include template('thame');
?>
