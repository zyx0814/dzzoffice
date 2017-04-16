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
if ($do == 'addcolor') {
	$setarr = array(
	'title' => '', 
	'val' => rawurldecode($_GET['val']), 
	'type' => 'color', 
	'classid' => 0, 
	'disp' => 0, 
	'dateline' => $_G['timestamp']);

	if (strpos($setarr['val'], '#') !== 0 && strpos($setarr['val'], 'RGB') !== 0) {
		echo json_encode(array('error' => lang('color_value_error')));
		exit();
	}
	if ($setarr['bid'] = DB::insert('wallpaper', $setarr, 1)) {
		echo json_encode($setarr);
		exit();
	}
	echo json_encode(array('error' => lang('add_unsuccess')));
	exit();
} elseif ($do == 'addsyscolor') {
	$setarr = array(
	'title' => '', 
	'val' => rawurldecode($_GET['val']), 
	'type' => 'syscolor', 'classid' => 0, 
	'disp' => 0, 
	'dateline' => $_G['timestamp']);

	if (strpos($setarr['val'], '#') !== 0 && strpos($setarr['val'], 'RGB') !== 0 && strpos($setarr['val'], 'rgb') !== 0) {
		echo json_encode(array('error' => lang('color_value_error')));
		exit();
	}
	if ($setarr['bid'] = DB::insert('wallpaper', $setarr, 1)) {
		echo json_encode($setarr);
		exit();
	}
	echo json_encode(array('error' => lang('add_unsuccess')));
	exit();
} elseif ($do == 'delete') {
	$bid = intval($_GET['bid']);
	$arr = DB::fetch_first("select * from %t where bid=%d", array('wallpaper', $bid));
	if ($arr['type'] == 'repeat' || $arr['type'] == 'scale') {
		@unlink(DZZ_ROOT . './' . $arr['val']);
		if ($arr['thumb'])
			@unlink(DZZ_ROOT . './' . $arr['val'] . '.thumb.jpg');
	} elseif ($arr['type'] == 'url') {
		@unlink(DZZ_ROOT . './' . $arr['img']);
		if ($arr['thumb'])
			@unlink(DZZ_ROOT . './' . $arr['img'] . '.thumb.jpg');
	}
	DB::delete('wallpaper', "bid='{$bid}'");
	echo json_encode(array('success' => true));
	exit();
} elseif ($do == 'deletethame') {
	$id = intval($_GET['id']);
	DB::delete('thame', "id='{$id}'");
	echo json_encode(array('success' => true));
	exit();
}

include  template('thame');
?>
