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
define('NOROBOT', TRUE);
if ($_GET['action'] == 'checkusername') {

	$username = trim($_GET['username']);
	$usernamelen = dstrlen($username);
	if ($usernamelen < 3) {
		showmessage('profile_nickname_tooshort', '', array(), array('handle' => false));
	} elseif ($usernamelen > 30) {
		showmessage('profile_nickname_toolong', '', array(), array('handle' => false));
	}

	require_once libfile('function/user');
	$ucresult = uc_user_checkname($username);
	if ($ucresult == -1) {
		showmessage('profile_nickname_illegal', '', array(), array('handle' => false));
	} elseif ($ucresult == -2) {
		showmessage('profile_nickname_protect', '', array(), array('handle' => false));
	} elseif ($ucresult == -3) {
		showmessage('register_check_found', '', array(), array('handle' => false));
	}

	$censorexp = '/^(' . str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($_G['setting']['censoruser'] = trim($_G['setting']['censoruser'])), '/')) . ')$/i';
	if ($_G['setting']['censoruser'] && @preg_match($censorexp, $username)) {
		showmessage('profile_nickname_protect', '', array(), array('handle' => false));
	}

} elseif ($_GET['action'] == 'checkemail') {

	require_once libfile('function/user');
	checkemail($_GET['email']);

} elseif ($_GET['action'] == 'checkinvitecode') {

	$invitecode = trim($_GET['invitecode']);
	if (!$invitecode) {
		showmessage('no_invitation_code', '', array(), array('handle' => false));
	}
	$result = array();
	if ($invite = C::t('user_invite') -> fetch_by_code($invitecode)) {
		if (empty($invite['fuid']) && (empty($invite['endtime']) || $_G['timestamp'] < $invite['endtime'])) {
			$result['uid'] = $invite['uid'];
			$result['id'] = $invite['id'];
			$result['appid'] = $invite['appid'];
		}
	}
	if (empty($result)) {
		showmessage('wrong_invitation_code', '', array(), array('handle' => false));
	}

} elseif ($_GET['action'] == 'checkuserexists') {

	if (C::t('user') -> fetch_by_username(trim($_GET['username']))) {
		showmessage('<img src="' . $_G['style']['imgdir'] . '/check_right.gif" width="13" height="13">', '', array(), array('msgtype' => 3));
	} else {
		showmessage('username_nonexistence', '', array(), array('msgtype' => 3));
	}

} elseif ($_GET['action'] == 'district') {
	$container = $_GET['container'];
	$showlevel = intval($_GET['level']);
	$showlevel = $showlevel >= 1 && $showlevel <= 4 ? $showlevel : 4;
	$values = array(intval($_GET['pid']), intval($_GET['cid']), intval($_GET['did']), intval($_GET['coid']));
	$containertype = in_array($_GET['containertype'], array('birth', 'reside'), true) ? $_GET['containertype'] : 'birth';
	$level = 1;
	if ($values[0]) {
		$level++;
	} else if ($_G['uid'] && !empty($_GET['showdefault'])) {

		space_merge($_G['member'], 'profile');
		$district = array();
		if ($containertype == 'birth') {
			if (!empty($_G['member']['birthprovince'])) {
				$district[] = $_G['member']['birthprovince'];
				if (!empty($_G['member']['birthcity'])) {
					$district[] = $_G['member']['birthcity'];
				}
				if (!empty($_G['member']['birthdist'])) {
					$district[] = $_G['member']['birthdist'];
				}
				if (!empty($_G['member']['birthcommunity'])) {
					$district[] = $_G['member']['birthcommunity'];
				}
			}
		} else {
			if (!empty($_G['member']['resideprovince'])) {
				$district[] = $_G['member']['resideprovince'];
				if (!empty($_G['member']['residecity'])) {
					$district[] = $_G['member']['residecity'];
				}
				if (!empty($_G['member']['residedist'])) {
					$district[] = $_G['member']['residedist'];
				}
				if (!empty($_G['member']['residecommunity'])) {
					$district[] = $_G['member']['residecommunity'];
				}
			}
		}
		if (!empty($district)) {
			foreach (C::t('district')->fetch_all_by_name($district) as $value) {
				$key = $value['level'] - 1;
				$values[$key] = $value['id'];
			}
			$level++;
		}
	}
	if ($values[1]) {
		$level++;
	}
	if ($values[2]) {
		$level++;
	}
	if ($values[3]) {
		$level++;
	}
	$showlevel = $level;
	$elems = array();
	if ($_GET['province']) {
		$elems = array($_GET['province'], $_GET['city'], $_GET['district'], $_GET['community']);
	}

	include_once libfile('function/profile');
	$html = showdistrict($values, $elems, $container, $showlevel, $containertype);
	include template('ajax');
	exit();
}
showmessage('succeed', '', array(), array('handle' => false));
?>
