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
if (!$_G['uid']) {
	include  template('common/header_reload');
	echo "<script type=\"text/javascript\">try{";
	echo "top._login.logging();";
	echo "win.Close();";
	echo "}catch(e){}</script>";
	include template('common/footer_reload');
	exit();
}
$do = trim($_GET['do']);
$uid = intval($_G['uid']);
$seccodecheck = $_G['setting']['seccodestatus'] & 4;
if (submitcheck('accountedit', 0, $seccodecheck)) {
	$member = C::t('user') -> fetch($_G['uid']);
	//验证原密码
	$password0 = $_GET['password0'];
	if (md5(md5($password0) . $member['salt']) != $member['password']) {
		showmessage('original_password_mistake');
	}

	if ($_GET['password'] && $_G['setting']['pwlength']) {
		if (strlen($_GET['password']) < $_G['setting']['pwlength']) {
			showmessage('profile_password_tooshort', '', array('pwlength' => $_G['setting']['pwlength']));
		}
	}
	//验证密码强度
	if ($_GET['password'] && $_G['setting']['strongpw']) {
		$strongpw_str = array();
		if (in_array(1, $_G['setting']['strongpw']) && !preg_match("/\d+/", $_GET['password'])) {
			$strongpw_str[] = lang('strongpw_1');
		}
		if (in_array(2, $_G['setting']['strongpw']) && !preg_match("/[a-z]+/", $_GET['password'])) {
			$strongpw_str[] = lang('strongpw_2');
		}
		if (in_array(3, $_G['setting']['strongpw']) && !preg_match("/[A-Z]+/", $_GET['password'])) {
			$strongpw_str[] = lang('strongpw_3');
		}
		if (in_array(4, $_G['setting']['strongpw']) && !preg_match("/[^a-zA-z0-9]+/", $_GET['password'])) {
			$strongpw_str[] = lang('strongpw_4');
		}
		if ($strongpw_str) {
			showmessage(lang('password_weak') . implode(',', $strongpw_str));
		}
	}

	if ($_GET['password'] && $_GET['password'] !== $_GET['password2']) {
		showmessage('profile_passwd_notmatch');
	}
	$setarr = array();
	if ($_GET['password']) {
		$password = preg_match('/^\w{32}$/', $_GET['password']) ? $_GET['password'] : md5($_GET['password']);
		$setarr['password'] = md5($password . $member['salt']);
	}
	$email = strtolower(trim($_GET['email']));
	if ($email && $email != $member['email']) {
		checkemail($_GET['email']);
		$setarr['email'] = $email;
	}
	//验证用户名
	if ($nickname = (trim($_GET['nickname']))) {
		$nicknamelen = dstrlen($nickname);
		if ($nicknamelen < 3) {
			showmessage('profile_nickname_tooshort');
		}
		if ($nicknamelen > 30) {
			showmessage('profile_nickname_toolong');
		}
		if (!check_username(addslashes(trim(stripslashes($nickname))))) {
			showmessage('profile_nickname_illegal');
		}
		if ($nickname != $member['nickname'] && C::t('user') -> fetch_by_nickname($nickname)) {
			showmessage('username_already_registered');
		}
		$setarr['nickname'] = trim($_GET['nickname']);
	} else {
		$setarr['nickname'] = '';
	}
	//如果输入手机号码，检查手机号码不能重复
	$phone = trim($_GET['phone']);
	if ($phone) {
		if (!preg_match("/^\d+$/", $phone)) {
			showmessage('user_phone_illegal');
		}
		if ($phone != $member['phone'] && C::t('user') -> fetch_by_phone($phone)) {
			showmessage('user_phone_registered');
		}
		$setarr['phone'] = $phone;
	} else {
		$setarr['phone'] = '';
		;
	}

	//如果输入微信号，检查微信号不能重复
	$weixinid = trim($_GET['weixinid']);
	if ($weixinid) {
		if (!preg_match("/^[a-zA-Z\d_]{5,}$/i", $weixinid)) {
			showmessage('WeChat_id_illegal');
		}
		if ($weixinid != $member['weixinid'] && C::t('user') -> fetch_by_weixinid($weixinid)) {
			showmessage('WeChat_already_registered');
		}
		$setarr['weixinid'] = $weixinid;
	} else {
		$setarr['weixinid'] = '';
	}

	if ($setarr) {
		if (C::t('user') -> update($_G['uid'], $setarr))
			wx_updateUser($_G['uid']);
	}

	showmessage('do_success', dreferer());
} elseif ($_GET['sendmail']) {
	$user = C::t('user') -> fetch($_G['uid']);
	$idstring = random(6);
	$authstr = "$_G[timestamp]\t2\t$idstring";
	C::t('user') -> update($_G['uid'], array('authstr' => $authstr));
	$verifyurl = "{$_G[siteurl]}user.php?mod=activate&amp;uid={$_G[uid]}&amp;id=$idstring";
	$email_verify_message = lang('email_verify_message', array('username' => $_G['member']['username'], 'sitename' => $_G['setting']['sitename'], 'siteurl' => $_G['siteurl'], 'url' => $verifyurl));
	if (!sendmail("$user[username] <$user[email]>", lang('email_verify_subject'), $email_verify_message)) {
		runlog('sendmail', "$user[email] sendmail failed.");
		showmessage('sendmail_examine_correct_not', 'user.php?mod=password');
	} else {
		showmessage('email_been_sent_wait_few_minutes', 'user.php?mod=password');
	}
} else {
	$user = C::t('user') -> fetch($_G['uid']);
	include template('password');

}
exit();
?>
