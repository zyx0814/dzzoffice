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
require_once libfile('function/user', '', 'user');
require_once libfile('function/mail');
require_once libfile('function/organization');
if (!$_G['cache']['usergroups'])
	loadcache('usergroups');

$do = trim($_GET['do']);
$uid = intval($_GET['uid']);
if (!$uid)
	$do = 'add';
if (empty($do) && $uid)
	$do = 'edit';
if ($do == 'add') {

	if (submitcheck('accountadd')) {

		//处理用户部门和职位
		$orgids = array();
		foreach ($_GET['orgids'] as $key => $orgid) {
			if (!$orgid)
				continue;
			if (C::t('organization_admin') -> ismoderator_by_uid_orgid($orgid, $_G['uid'], 1)) {
				$orgids[$orgid] = intval($_GET['jobids'][$key]);
			}
		}
		if (!$orgids && $_G['adminid'] != 1)
			showmessage('no_parallelism_jurisdiction');
		//用户名验证
		$username = trim($_GET['username']);
		if ($username) {
			$usernamelen = dstrlen($_GET['username']);
			if ($usernamelen < 3) {
				showmessage('profile_username_tooshort');
			} elseif ($usernamelen > 30) {
				showmessage('profile_username_toolong');
			}
			if ($_G['setting']['censoruser'] && @preg_match($censorexp, $username)) {
				showmessage('profile_username_protect');
			}
			//如果输入用户名，检查用户名不能重复

			if (C::t('user') -> fetch_by_username($username)) {
				showmessage('user_registered_retry');
			}

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
				showmessage('profile_username_illegal');
			} elseif ($uid == -2) {
				showmessage('profile_username_protect');
			} elseif ($uid == -3) {
				showmessage('profile_username_duplicate');
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

		if ($orgids)
			C::t('organization_user') -> replace_orgid_by_uid($uid, $orgids);
		//处理上司职位;
		C::t('organization_upjob') -> insert_by_uid($uid, intval($_GET['upjobid']));
		Hook::listen('syntoline_user',$uid);//注册绑定到钉钉部门表
		if ($_GET['sendmail']) {
			$email_password_message = lang('email_password_message', array('sitename' => $_G['setting']['sitename'], 'siteurl' => $_G['siteurl'], 'email' => $_GET['email'], 'password' => $_GET['password']));

			if (!sendmail_cron("$_GET[email] <$_GET[email]>", lang('email_password_subject'), $email_password_message)) {
				runlog('sendmail', "$_GET[email] sendmail failed.");
			}
		}

		showmessage('add_user_success', ADMINSCRIPT . '?mod=orguser#user_' . $uid, array('uid' => $uid, 'orgids' => $orgids));

	} else {
		$orgid = intval($_GET['orgid']);
		if (!C::t('organization_admin') -> ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
			exit(lang('orguser_edituser_add_user'));
		}
		if ($org = C::t('organization') -> fetch($orgid)) {
			$org['jobs'] = C::t('organization_job') -> fetch_all_by_orgid($org['orgid']);
			$orgpath = getPathByOrgid($org['orgid']);
			$org['depart'] = implode('-', ($orgpath));
		}

		include template('adduser');
	}

} elseif ($do == 'edit') {
	if (submitcheck('accountedit')) {

		//判断是否对此用户有管理权限
		$uperm = false;
		if ($_G['adminid'] != 1) {
			if ($orgids_uid = C::t('orginization_user') -> fetch_orgids_by_uid($uid)) {
				foreach ($orgids_uid as $orgid) {
					if (C::t('organization_admin') -> ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
						$uperm = true;
						break;
					}
				}
				if (!$uperm)
					showmessage('privilege');
			} else {
				showmessage('privilege');
			}
		}

		$orgids = array();
		foreach ($_GET['orgids'] as $key => $orgid) {
			if ($orgid)
				$orgids[$orgid] = intval($_GET['jobids'][$key]);
		}

		$user = C::t('user') -> fetch_by_uid($uid);
		if ($user['groupid'] < $_G['groupid'] || (C::t('user') -> checkfounder($user) && !C::t('user') -> checkfounder($_G['member']))) {
			//处理用户部门和职位
			C::t('organization_user') -> replace_orgid_by_uid($uid, $orgids);

			//处理上司职位;
			C::t('organization_upjob') -> insert_by_uid($uid, intval($_GET['upjobid']));

			showmessage('edit_user_success', ADMINSCRIPT . '?mod=orguser#user_' . $uid, array());
		}
	

		//用户名验证
		$username = trim($_GET['username']);
		
		$usernamelen = dstrlen($_GET['username']);
		if ($usernamelen < 3) {
			showmessage('profile_username_tooshort');
		} elseif ($usernamelen > 30) {
			showmessage('profile_username_toolong');
		} elseif (!check_username(addslashes(trim(stripslashes($username))))) {
			showmessage('profile_username_illegal');
		}

		//如果输入用户名，检查用户名不能重复
		if ($username != $user['username']) {
			if (C::t('user') -> fetch_by_username($username)) {
				showmessage('user_registered_retry');
			}
			if ($_G['setting']['censoruser'] && @preg_match($censorexp, $username)) {
				showmessage('profile_username_protect');
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
		if ($email != strtolower($user['email'])) {
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
			$setarr = array('salt' => $salt, 'password' => md5(md5($password) . $salt), 'username' => $username, 'phone' => $phone, 'weixinid' => $weixinid, 'secques' => '', 'email' => $email, 'status' => intval($_GET['status']));

		} else {
			$setarr = array('username' => $username, 'email' => $email, 'phone' => $phone, 'weixinid' => $weixinid, 'status' => intval($_GET['status']));
		}
		C::t('user') -> update($uid, $setarr);
		 
		//处理管理员
		C::t('user') -> setAdministror($uid, intval($_GET['groupid']));
		//处理额外空间和用户空间
		//$addsize = intval($_GET['addsize']);
		$userspace = intval($_GET['userspace']);
		if (C::t('user_field') -> fetch($uid)) {
			C::t('user_field') -> update($uid, array('userspace'=>$userspace,'perm' => 0));
		} else {
			C::t('user_field') -> insert(array('uid' => $uid,'userspace'=>$userspace, 'perm' => 0, 'iconview' => $_G['setting']['desktop_default']['iconview'] ? $_G['setting']['desktop_default']['iconview'] : 2, 'taskbar' => $_G['setting']['desktop_default']['taskbar'] ? $_G['setting']['desktop_default']['taskbar'] : 'bottom', 'iconposition' => intval($_G['setting']['desktop_default']['iconposition']), 'direction' => intval($_G['setting']['desktop_default']['direction']), ));
		}
		//处理用户部门和职位

		if ($orgids)
			C::t('organization_user') -> replace_orgid_by_uid($uid, $orgids);
		//处理上司职位;

		C::t('organization_upjob') -> insert_by_uid($uid, intval($_GET['upjobid']));
		Hook::listen('syntoline_user',$uid);//注册绑定到钉钉部门表
		showmessage('edit_user_success', ADMINSCRIPT . '?mod=orguser#user_' . $uid, array());
	} else {
		require_once  libfile('function/organization');

		$user = C::t('user') -> fetch_by_uid($uid);
		$userfield = C::t('user_field') -> fetch($uid);

		//$user['status']=$user['status']>0?0:1;
		$departs = array();
		$data_depart = array();
		//$departs=getDepartmentByUid($uid);
		$orgids = C::t('organization_user') -> fetch_orgids_by_uid($uid);
		//判断是否对此用户有管理权限
		$uperm = false;
		if ($_G['adminid'] != 1) {
			foreach ($orgids as $orgid) {
				if (C::t('organization_admin') -> ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
					$uperm = true;
					break;
				}
			}
			if (!$uperm)
				exit(lang('orguser_edituser_add_user1'));
		}
		//获取系统可分配空间大小
		$allowallotspace = C::t('organization')->get_system_allowallot_space();
		//如果该用户之前有分配空间，当前用户可分配空间=系统可分配空间+该用户之前分配空间(若无，则加上当前用户已使用空间)
		if($userfield['userspace'] > 0){
			$currentuserAllotspace = $allowallotspace + $userfield['userspace']*1024*1024;
		}else{
			$currentuserAllotspace = $allowallotspace + $userfield['usesize'];
		}
		$departs = C::t('organization') -> fetch_all($orgids);
		foreach ($departs as $key => $value) {
			$orgpath = getPathByOrgid($value['orgid']);
			$value['depart'] = implode('-', ($orgpath));
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
	//判断是否对此用户有管理权限
	$uperm = false;
	if ($_G['adminid'] != 1) {
		if ($orgids = C::t('organization_user') -> fetch_orgids_by_uid($uid)) {
			foreach ($orgids as $orgid) {
				if (C::t('organization_admin') -> ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
					$uperm = true;
					break;
				}
			}
		}
		if (!$uperm)
			exit(lang('orguser_edituser_add_user1'));
	}
	include_once libfile('function/profile', '', 'user');
	$space = getuserbyuid($uid);
	space_merge($space, 'profile');
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
			C::t('user_profile') -> insert($setarr);
		}
		showmessage('subscriber_data_alter_success', ADMINSCRIPT . '?mod=orguser#user_' . $uid . '_profile', array());
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
		$active = array('profile' => 'class="active"');

		include template('profile');
	}

}
exit();
?>
