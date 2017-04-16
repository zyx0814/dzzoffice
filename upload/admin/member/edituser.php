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
if ($_G['adminid'] != 1)showmessage('no_privilege');
require_once libfile('function/user', '', 'user');
require_once libfile('function/mail');
require_once libfile('function/organization');
if (!$_G['cache']['usergroups'])
	loadcache('usergroups');
$do = trim($_GET['do']);
$uid = intval($_GET['uid']);
if (!$uid)$do = 'add';
if (empty($do) && $uid)$do = 'edit';
if ($do == 'add') {

	if (submitcheck('accountadd')) {
		$username = trim($_GET['username']);
		$usernamelen = dstrlen($_GET['username']);
		if ($usernamelen < 3) {
			showmessage('profile_username_tooshort');
		} elseif ($usernamelen > 30) {
			showmessage('profile_username_toolong');
		}
		$censorexp = '/^(' . str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($_G['setting']['censoruser'] = trim($_G['setting']['censoruser'])), '/')) . ')$/i';
		if ($_G['setting']['censoruser'] && @preg_match($censorexp, $username)) {
			showmessage('profile_username_protect');
		}
		$user_extra = array();
		//如果输入手机号码，检查手机号码不能重复
		$phone = trim($_GET['phone']);
		if ($phone) {
			if (!preg_match("/^\d+$/", $phone)) {
				showmessage('user_phone_illegal');
			}
			if (C::t('user') -> fetch_by_phone($phone)) {
				showmessage('user_phone_registered');
			}
			$user_extra['phone'] = $phone;
		}
		//如果输入微信号，检查微信号不能重复
		$weixinid = trim($_GET['weixinid']);
		if ($weixinid) {
			if (!preg_match("/^[a-zA-Z\d_]{5,}$/i", $weixinid)) {
				showmessage(lang('weixin_illegal'));
			}
			if (C::t('user') -> fetch_by_weixinid($weixinid)) {
				showmessage('weixin_registered');
			}
			$user_extra['weixinid'] = $weixinid;
		}
		//用户名验证
		$nickname = trim($_GET['nickname']);
		if ($nickname) {
			$nicknamelen = dstrlen($_GET['nickname']);
			if ($nicknamelen < 3) {
				showmessage('profile_nickname_tooshort');
			} elseif ($nicknamelen > 30) {
				showmessage('profile_nickname_toolong');
			}
			if ($_G['setting']['censoruser'] && @preg_match($censorexp, $nickname)) {
				showmessage('profile_username_protect');
			}
			//如果输入用户名，检查用户名不能重复

			if (C::t('user') -> fetch_by_nickname($nickname)) {
				showmessage('user_registered_retry');
			}

		}

		//邮箱验证部分
		$email = strtolower(trim($_GET['email']));
		checkemail($_GET['email']);

		//密码验证部分
		if ($_G['setting']['pwlength']) {
			if (strlen($_GET['password']) < $_G['setting']['pwlength']) {
				showmessage('profile_password_tooshort', '', array('pwlength' => $_G['setting']['pwlength']));
			}
		}

		if (!$_GET['password'] || $_GET['password'] != addslashes($_GET['password'])) {
			showmessage('profile_passwd_illegal');
		}
		$password = $_GET['password'];

		$result = uc_user_register(addslashes($username), $password, $email, $nickname, $questionid, $answer, $_G['clientip'], 0);
		if (is_array($result)) {
			$uid = $result['uid'];
			$password = $result['password'];
		} else {
			$uid = $result;
		}
		if ($uid <= 0) {
			if ($uid == -1) {
				showmessage('profile_nickname_illegal');
			} elseif ($uid == -2) {
				showmessage('profile_nickname_protect');
			} elseif ($uid == -3) {
				showmessage('profile_nickname_duplicate');
			} elseif ($uid == -4) {
				showmessage('profile_email_illegal');
			} elseif ($uid == -5) {
				showmessage('profile_email_domain_illegal');
			} elseif ($uid == -6) {
				showmessage('profile_email_duplicate');
			} elseif ($uid == -7) {
				showmessage('profile_username_illegal');
			} else {
				showmessage('undefined_action');
			}
		}
		//插入用户状态表
		$status = array('uid' => $uid, 'regip' => '', 'lastip' => '', 'lastvisit' => TIMESTAMP, 'lastactivity' => TIMESTAMP, 'lastsendmail' => 0);
		C::t('user_status') -> insert($status, false, true);
		//处理管理员
		C::t('user') -> setAdministror($uid, intval($_GET['groupid']));

		//加入额外信息
		if ($user_extra)
			C::t('user') -> update($uid, $user_extra);

		//处理额外空间
		$addsize = intval($_GET['addsize']);
		if (C::t('user_field') -> fetch($uid)) {
			C::t('user_field') -> update($uid, array('addsize' => $addsize, 'perm' => 0));
		} else {
			C::t('user_field') -> insert(array('uid' => $uid, 'addsize' => $addsize, 'perm' => 0, 'iconview' => $_G['setting']['desktop_default']['iconview'] ? $_G['setting']['desktop_default']['iconview'] : 2, 'taskbar' => $_G['setting']['desktop_default']['taskbar'] ? $_G['setting']['desktop_default']['taskbar'] : 'bottom', 'iconposition' => intval($_G['setting']['desktop_default']['iconposition']), 'direction' => intval($_G['setting']['desktop_default']['direction']), ));
		}
		//处理用户部门和职位
		$orgids = array();
		foreach ($_GET['orgids'] as $key => $value) {
			$orgids[$value] = intval($_GET['jobids'][$key]);
		}
		if ($orgids)
			C::t('organization_user') -> replace_orgid_by_uid($uid, $orgids);
		//处理上司职位;
		C::t('organization_upjob') -> insert_by_uid($uid, intval($_GET['upjobid']));

		if ($_GET['sendmail']) {
			$email_password_message = lang('email_password_message', array('sitename' => $_G['setting']['sitename'], 'siteurl' => $_G['siteurl'], 'email' => $_GET['email'], 'password' => $_GET['password']));

			if (!sendmail_cron("$_GET[email] <$_GET[email]>", lang('email_password_subject'), $email_password_message)) {
				runlog('sendmail', "$_GET[email] sendmail failed.");
			}
		}

		showmessage('add_user_success', ADMINSCRIPT . '?mod=member&op=edituser&uid=' . $uid, array('uid' => $uid));

	} else {
		$orgid = intval($_GET['orgid']);

		if ($org = C::t('organization') -> fetch($orgid)) {
			$org['jobs'] = C::t('organization_job') -> fetch_all_by_orgid($org['orgid']);
			$orgpath = getPathByOrgid($org['orgid']);
			$org['depart'] = implode('-', array_reverse($orgpath));
		}

		include template('adduser');
	}

} elseif ($do == 'edit') {
	if (submitcheck('accountedit')) {
		$user = C::t('user') -> fetch_by_uid($uid);
		if ($user['groupid'] < $_G['groupid'] || (C::t('user') -> checkfounder($user) && !C::t('user') -> checkfounder($_G['member']))) {
			//处理用户部门和职位
			if ($orgids = $_GET['orgid']) {
				C::t('organization_user') -> replace_orgid_by_uid($uid, $orgids);
			}
			//处理上司职位;
			C::t('organization_upjob') -> insert_by_uid($uid, intval($_GET['upjobid']));

			showmessage('edit_user_success', ADMINSCRIPT . '?mod=orguser#user_' . $uid, array());
		}
		$username = trim($_GET['username']);
		$usernamelen = dstrlen($_GET['username']);
		if ($usernamelen < 3) {
			showmessage('profile_username_tooshort');
		} elseif ($usernamelen > 30) {
			showmessage('profile_username_toolong');
		} elseif (!check_username(addslashes(trim(stripslashes($username))))) {
			showmessage('profile_username_illegal');
		}
		$censorexp = '/^(' . str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($_G['setting']['censoruser'] = trim($_G['setting']['censoruser'])), '/')) . ')$/i';
		if ($_G['setting']['censoruser'] && @preg_match($censorexp, $username)) {
			showmessage('profile_username_protect');
		}

		//用户名验证
		$nickname = trim($_GET['nickname']);
		if ($nickname) {
			$nicknamelen = dstrlen($_GET['nickname']);
			if ($nicknamelen < 3) {
				showmessage('profile_nickname_tooshort');
			} elseif ($nicknamelen > 30) {
				showmessage('profile_nickname_toolong');
			} elseif (!check_username(addslashes(trim(stripslashes($nickname))))) {
				showmessage('profile_nickname_illegal');
			}

			//如果输入用户名，检查用户名不能重复
			if ($nickname != $user['nickname']) {
				if (C::t('user') -> fetch_by_nickname($nickname)) {
					showmessage('user_registered_retry');
				}
				if ($_G['setting']['censoruser'] && @preg_match($censorexp, $nickname)) {
					showmessage('profile_username_protect');
				}
			}
		}

		//如果输入手机号码，检查手机号码不能重复
		$phone = trim($_GET['phone']);
		if ($phone) {
			if (!preg_match("/^\d+$/", $phone)) {
				showmessage('user_phone_illegal');
			}
			if ($phone != $user['phone'] && C::t('user') -> fetch_by_phone($phone)) {
				showmessage('user_phone_registered');
			}
		}
		//如果输入微信号，检查微信号不能重复
		$weixinid = trim($_GET['weixinid']);
		if ($weixinid) {
			if (!preg_match("/^[a-zA-Z\d_]{5,}$/i", $weixinid)) {
				showmessage(lang('weixin_illegal'));
			}
			if ($weixinid != $user['weixinid'] && C::t('user') -> fetch_by_weixinid($weixinid)) {
				showmessage('weixin_registered');
			}
		}

		//邮箱验证部分
		$email = strtolower(trim($_GET['email']));
		if (!isemail($email)) {
			showmessage('profile_email_illegal', '', array(), array('handle' => false));
		} elseif (!check_emailaccess($email)) {
			showmessage('profile_email_domain_illegal', '', array(), array('handle' => false));
		}
		if ($email != $user['email']) {
			//邮箱不能重复
			if (C::t('user') -> fetch_by_email($email)) {
				showmessage('email_registered_retry');
			}
		}

		//密码验证部分
		if ($_GET['password']) {
			if ($_G['setting']['pwlength']) {
				if (strlen($_GET['password']) < $_G['setting']['pwlength']) {
					showmessage('profile_password_tooshort', '', array('pwlength' => $_G['setting']['pwlength']));
				}
			}

			if ($_GET['password'] !== $_GET['password2']) {
				showmessage('profile_passwd_notmatch');
			}
		}
		$password = $_GET['password'];
		if ($password) {
			$salt = substr(uniqid(rand()), -6);
			$setarr = array('salt' => $salt, 'password' => md5(md5($password) . $salt), 'username' => $username, 'nickname' => $nickname, 'phone' => $phone, 'weixinid' => $weixinid, 'secques' => '', 'email' => $email, 'status' => intval($_GET['status']));

		} else {
			$setarr = array('username' => $username, 'email' => $email, 'nickname' => $nickname, 'phone' => $phone, 'weixinid' => $weixinid, 'status' => intval($_GET['status']));
		}
		C::t('user') -> update($uid, $setarr);
		wx_updateUser($uid);

		//处理用户组
		//$groupid=intval($_GET['groupid']);
		C::t('user') -> setAdministror($uid, intval($_GET['groupid']));

		//处理额外空间
		$addsize = intval($_GET['addsize']);
		if (C::t('user_field') -> fetch($uid)) {
			C::t('user_field') -> update($uid, array('addsize' => $addsize, 'perm' => 0));
		} else {
			C::t('user_field') -> insert(array('uid' => $uid, 'addsize' => $addsize, 'perm' => 0, 'iconview' => $_G['setting']['desktop_default']['iconview'] ? $_G['setting']['desktop_default']['iconview'] : 2, 'taskbar' => $_G['setting']['desktop_default']['taskbar'] ? $_G['setting']['desktop_default']['taskbar'] : 'bottom', 'iconposition' => intval($_G['setting']['desktop_default']['iconposition']), 'direction' => intval($_G['setting']['desktop_default']['direction']), ));
		}
		//处理用户部门和职位
		$orgids = array();
		foreach ($_GET['orgids'] as $key => $value) {
			$orgids[$value] = intval($_GET['jobids'][$key]);
		}
		if ($orgids)
			C::t('organization_user') -> replace_orgid_by_uid($uid, $orgids);
		//处理上司职位;

		C::t('organization_upjob') -> insert_by_uid($uid, intval($_GET['upjobid']));
		showmessage('edit_user_success', ADMINSCRIPT . '?mod=member&op=edituser&uid=' . $uid, array());

	} else {
		require_once  libfile('function/organization');

		$user = C::t('user') -> fetch_by_uid($uid);
		$userfield = C::t('user_field') -> fetch($uid);

		//$user['status']=$user['status']>0?0:1;
		$departs = array();
		$data_depart = array();
		//$departs=getDepartmentByUid($uid);
		$orgids = C::t('organization_user') -> fetch_orgids_by_uid($uid);
		$departs = C::t('organization') -> fetch_all($orgids);
		foreach ($departs as $key => $value) {
			$orgpath = getPathByOrgid($value['orgid']);
			$value['depart'] = implode('-', array_reverse($orgpath));
			$value['ismoderator'] = C::t('organization_admin') -> ismoderator_by_uid_orgid($value['orgid'], $_G['uid']);
			$value['jobs'] = C::t('organization_job') -> fetch_all_by_orgid($value['orgid']);
			$value['user'] = C::t('organization_user') -> fetch_by_uid_orgid($uid, $value['orgid']);
			$value['jobid'] = $value['user']['jobid'];
			$value['jobname'] = $value['jobs'][$value['jobid']] ? $value['jobs'][$value['jobid']]['name'] : lang('none');
			$data_depart[$key] = $value;
		}

		//$orgtree_admin=getDepartmentOption_admin(0);
		if ($upjob = C::t('organization_upjob') -> fetch_by_uid($uid)) {
			$upjob['jobs'] = C::t('organization_job') -> fetch_all_by_orgid($upjob['orgid']);
		} else {
			$upjob = array('jobid' => 0, 'depart' => lang('please_select_a_organization_or_department'), 'name' => lang('none'));
		}
		//$orgtree_all=getDepartmentOption_admin(0,'',true);
		$perm = 1;
		if ($user['groupid'] < $_G['groupid'] || (C::t('user') -> checkfounder($user) && !C::t('user') -> checkfounder($_G['member']))) {
			$perm = 0;
		}

		include template('edituser');
	}

} elseif ($do == 'profile') {
	include_once  libfile('function/profile', '', 'user');
	$space = getuserbyuid($uid);
	space_merge($space, 'profile1');
	loadcache('profilesetting');
	if (empty($_G['cache']['profilesetting'])) {
		loadcache('profilesetting');
	}
	if (submitcheck('profilesubmit')) {
		$setarr = array();
		foreach ($_GET as $key => $value) {
			$field = $_G['cache']['profilesetting'][$key];
			if (empty($field)) {
				continue;
			} elseif (profile_check($key, $value, $space)) {
				$setarr[$key] = dhtmlspecialchars(trim($value));
			}
		}
		if (isset($_POST['birthmonth']) && ($space['birthmonth'] != $_POST['birthmonth'] || $space['birthday'] != $_POST['birthday'])) {
			$setarr['constellation'] = get_constellation($_POST['birthmonth'], $_POST['birthday']);
		}
		if (isset($_POST['birthyear']) && $space['birthyear'] != $_POST['birthyear']) {
			$setarr['zodiac'] = get_zodiac($_POST['birthyear']);
		}

		if ($setarr) {
			$setarr['uid'] = $uid;
			C::t('user_profile1') -> insert($setarr);
		}
		showmessage(lang('subscriber_data_alter_success'), ADMINSCRIPT . '?mod=member&op=edituser&do=profile&uid=' . $uid, array());
	} else {
		$allowitems = array();
		foreach ($_G['cache']['profilesetting'] as $key => $value) {
			if ($value['available'] > 0)
				$allowitems[] = $key;
		}
		$htmls = $settings = array();

		foreach ($allowitems as $fieldid) {
			if (!in_array($fieldid, array('department', 'timeoffset'))) {
				$html = profile_setting($fieldid, $space, false, true);
				if ($html) {
					$settings[$fieldid] = $_G['cache']['profilesetting'][$fieldid];
					$htmls[$fieldid] = $html;
				}
			}
		}
		include template('profile');
	}

}
?>
