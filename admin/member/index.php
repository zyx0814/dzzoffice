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
if (!$_G['cache']['usergroups']) {
	loadcache('usergroups');
}
//error_reporting(E_ALL);
//资料审核员和实名认证员跳转到对应的页面
if ($_G['member']['grid'] == '4') {
	if ($_G['setting']['verify'][1]['available']) {
		$op = 'verify';
		$_GET['vid'] = 1;
		require './admin/member/verify.php';
		exit();

	} else {
		showmessage('contact_administrator');
	}
} elseif ($_G['member']['grid'] == '5') {
	$op = 'verify';
	$_GET['vid'] = 0;
	require './admin/member/verify.php';
	exit();

}

if ($_GET['do'] == 'setuserstatus') {
	C::t('user') -> update(intval($_GET['uid']), array('status' => intval($_GET['status'])));
	echo json_encode(array('msg' => 'success'));
	exit();
}
if (!submitcheck('deletesubmit')) {
	require  libfile('function/organization');
	$orgid = intval($_GET['orgid']);
	$groupid = intval($_GET['groupid']);
	$keyword = trim($_GET['keyword']);

	$department = lang('please_select_a_organization_or_department');
	if ($org = C::t('organization') -> fetch($orgid)) {
		$patharr = getPathByOrgid($orgid);
		$department = implode(' - ', array_reverse($patharr));

	}

	$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
	$perpage = 20;
	$gets = array('mod' => 'member', 'keyword' => $keyword, 'orgid' => $orgid, 'groupid' => $groupid);
	$theurl = BASESCRIPT . "?" . url_implode($gets);

	$order = 'ORDER BY uid DESC';
	$start = ($page - 1) * $perpage;
	$sql = '1';
	$param = array('user');
	if ($groupid) {
		$sql .= " and groupid=%d";
		$param[] = $groupid;
	}
	if ($keyword) {
		$sql .= " and (username like %s or email like %s OR nickname LIKE %s)";
		$param[] = '%' . $keyword . '%';
		$param[] = '%' . $keyword . '%';
		$param[] = '%' . $keyword . '%';

	}
	if ($orgid) {
		$orgids = getOrgidTree($orgid);
		$uids = C::t('organization_user') -> fetch_uids_by_orgid($orgids);
		$sql .= " and uid IN(%n)";
		$param[] = $uids;

	}
	if ($count = DB::result_first("SELECT COUNT(*) FROM %t WHERE $sql", $param)) {
		$user = DB::fetch_all("SELECT * FROM %t WHERE $sql $order limit $start,$perpage", $param);
		$multi = multi($count, $perpage, $page, $theurl, 'pull-right');
	}
	$list = array();

	foreach ($user as $value) {
		$value['department'] = getDepartmentByUid($value['uid']);
		$userfield = C::t('user_field') -> fetch($value['uid']);
		$status = C::t('user_status') -> fetch($value['uid']);
		$value['verify'] = '';
		if ($_G['setting']['verify']['enabled']) {
			$verify = C::t('user_verify') -> fetch($value['uid']);
			for ($i = 1; $i < 8; $i++) {
				if ($_G['setting']['verify'][$i]['available'] && $_G['setting']['verify'][$i]['showicon']) {
					$icon = '';
					if ($verify['verify' . $i] && $_G['setting']['verify'][$i]['icon']) {
						$icon = $_G['setting']['attachurl'] . $_G['setting']['verify'][$i]['icon'];
					} elseif (!$verify['verify' . $i] && $_G['setting']['verify'][$i]['unverifyicon']) {
						$icon = $_G['setting']['attachurl'] . $_G['setting']['verify'][$i]['unverifyicon'];
					}
					if ($icon)
						$value['verify'] .= '<img class="verify-icon" src="' . $icon . '" title="' . $_G['setting']['verify'][$i]['title'] . '" >';
				}
			}
		}
		$value = array_merge($value, $userfield, $status);
		$value['fusesize'] = formatsize($value['usesize']);
		//计算用户的总空间大小

		if ($_G['cache']['usergroups'][$value['groupid']]['maxspacesize'] == 0) {
			$value['ftotalsize'] = lang('no_limit');
		} elseif ($_G['cache']['usergroups'][$value['groupid']]['maxspacesize'] < 0) {
			$total = $value['addsize'] * 1024 * 1024 + $value['buysize'] * 1024 * 1024;
			$value['ftotalsize'] = formatsize($total);
		} else {
			$total = $_G['cache']['usergroups'][$value['groupid']]['maxspacesize'] * 1024 * 1024 + $value['addsize'] * 1024 * 1024 + $value['buysize'] * 1024 * 1024;
			$value['ftotalsize'] = formatsize($total);
		}
		$value['grouptitle'] = $_G['cache']['usergroups'][$value['groupid']]['grouptitle'];
		$list[] = $value;
	}
	include template('main');
} else {

	foreach ($_GET['delete'] as $uid) {
		if ($uid == 1 || $_G['uid'] == $uid)
			continue;
		//创始人和自己不能删除；
		$uid = intval($uid);
		//删除用户
		C::t('user') -> delete_by_uid($uid);
	}
	showmessage('do_success', dreferer());
}
?>
