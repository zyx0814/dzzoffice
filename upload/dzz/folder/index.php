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
$sid = $_GET['sid'];
$share = C::t('share') -> fetch($sid);
if ($share['status'] == -4)showmessage('shared_links_screened_administrator');
//判断是否过期
if ($share['endtime'] && $share['endtime'] < TIMESTAMP) {
	showmessage('share_link_expired');
}
if ($share['times'] && $share['times'] < $share['count']) {
	showmessage('link_already_reached_max_number');
}
if ($share['status'] == -3) {
	showmessage('share_file_deleted');
}
if ($share['password'] && (dzzdecode($share['password']) != authcode($_G['cookie']['pass_' . $sid]))) {
	if (submitcheck('passwordsubmit')) {
		if ($_GET['password'] != dzzdecode($share['password'])) {
			include  template('common/share_password');
			exit();
		}
		dsetcookie('pass_' . $sid, authcode($_GET['password'], 'ENCODE'));
	} else {
		include  template('common/share_password');
		exit();
	}
}

$sharestatus = array(
	'-4' => lang('has_blocked'),
 	'-3' => lang('file_been_deleted'),
 	'-2' => lang('exhaust'),
 	'-1' => lang('have_expired'),
 	'0' => lang('normal')
);
$typearr = array(
	'folder' => lang('catalogue'), 
	'image' => lang('photo'), 
	'app' => lang('type_app'), 
	'link' => lang('type_link'), 
	'video' => lang('type_video'), 
	'attach' => lang('typename_attach'), 
	'document' => lang('type_attach'), 
	'dzzdoc' => lang('extname_dzzdoc'), 
	'url' => lang('rest')
);

$asc = intval($_GET['asc']);
$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
$perpage = 20;
$start = ($page - 1) * $perpage;
$gets = array('mod' => 'folder', 'sid' => $sid, );
$theurl = BASESCRIPT . "?" . url_implode($gets);

$orderby = "order by dateline DESC";
$path = $share['path'];
$dpath = dzzencode($path);
$icoarr = IO::getMeta($path);
$navtitle = $icoarr['name'];
$icoarr['fsize'] = '-';
$list = array();
$marker = '';
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
		} else {
			$ignore++;
		}
	}
}
if ($list && ($count = count($list)) >= ($perpage - $ignore)) {
	$nextpage = $page + 1;
} else {
	$naxtpage = 0;
}
include template('list');
?>
