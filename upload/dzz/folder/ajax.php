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
$dpath = $_GET['path'];
$path = dzzdecode($dpath);
$asc = intval($_GET['asc']);
$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
$perpage = 20;
$start = ($page - 1) * $perpage;
$gets = array('mod' => 'folder', 'sid' => $sid, );
$theurl = BASESCRIPT . "?" . url_implode($gets);

$orderby = "order by dateline DESC";

$icoarr = IO::getMeta($path);
$list = array();
if ($bz = $icoarr['bz']) {

	$order = $asc > 0 ? 'asc' : "desc";
	$by = 'time';
	$limit = $start . '-' . ($start + $perpage);
	if (strpos($bz, 'ALIOSS') === 0 || strpos($bz, 'JSS') === 0 || strpos($bz, 'qiniu') === 0) {
		$order = $_GET['marker'];
		$limit = $perpage;
	}
	$icosdata = IO::listFiles($path, $by, $order, $limit, $force);
	if ($icosdata['error']) {
		showmessage($icosdata['error']);
	}
	$folderdata = array();
	$ignore = 0;
	$marker = '';
	foreach ($icosdata as $key => $value) {
		if ($value['error']) {
			$ignore++;
			continue;
		}
		if ($value['nextMarker'])
			$marker = $value['nextMarker'];
		if (strpos($bz, 'ftp') === false) {
			if (trim($value['path'], '/') == trim($path, '/')) {
				$ignore++;
				continue;
			}
		}
		$list[$key] = $value;
	}
} else {
	$ignore = 0;
	$wheresql = '';
	$sql = " isdelete<1 and type!='shortcut' and pfid=%d";
	$param = array('icos', $icoarr['oid']);
	foreach (DB::fetch_all("SELECT icoid FROM %t where $sql order by dateline DESC limit $start,$perpage", $param) as $value) {
		if ($arr = C::t('icos') -> fetch_by_icoid($value['icoid'])) {
			if ($arr['type'] == 'folder')
				$arr['img'] = 'dzz/images/default/system/folder.png';
			$list[$value['icoid']] = $arr;
		} else
			$ignore++;
	}
}

if ($list && ($count = count($list)) >= ($perpage - $ignore)) {
	$nextpage = $page + 1;
} else {
	$naxtpage = 0;
}
include template('list_item');
?>
