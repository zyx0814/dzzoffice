<?php
if (!defined('IN_DZZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);
require_once DZZ_ROOT . "./user/api_qqlogin/qqConnectAPI.php";
if (!in_array($_GET['type'], array('login', 'callback', 'newuser', 'olduser'))) {
	$_GET['type'] = 'login';
}
if ($_GET['type'] == "login") {
	if ($_G['setting']['qq_login'] != '1') {
		showmessage('qq_log_close', $_G['siteurl']);
	}

	if (!empty($_G['uid'])) {//已经登录直接跳转
		$param = array('username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle'], 'uid' => $_G['member']['uid']);
		showmessage('login_succeed', $referer ? $referer : './', $param);
		$msg = '';
		$msg .= '		<div class="modal-body">';
		$msg .= '		  <div class="alert_right">';
		$msg .= '			<p id="succeedmessage"></p>';
		$msg .= '			<p id="succeedlocation" class="alert_btnleft">' . lang('login_succeed', $param) . '</p>';
		$msg .= '			<p class="alert_btnleft"><a href="' . $referer . '" id="succeedmessage_href">' . lang('message_forward') . '</a></p>';
		$msg .= '		  </div>';
		$msg .= '		</div>';
		$msg .= '	  </div><script type="text/javascript">setTimeout("window.location.href =\'' . $referer . '\';", 3000);</script></div>';
		exit($msg);
	}
	$inurl = $_SERVER["HTTP_REFERER"];
	//来路
	$_SESSION['url_ref'] = $inurl;
	$qc = new QC();
	$qc -> qq_login();

} elseif ($_GET['type'] == 'callback') {

	if (!$_SESSION['openid'] || $_GET['code']) {
		$qc = new QC();
		$access = $qc -> qq_callback();
		$openid = $qc -> get_openid();
		$_SESSION['openid'] = $openid;
		$_SESSION['access'] = $access;
		$qc = new QC($access, $openid);
		$uinfo = $qc -> get_user_info();
		$_SESSION['uinfo'] = $uinfo;

	} else {
		$access = $_SESSION['access'];
		$openid = $_SESSION['openid'];
		$uinfo = $_SESSION['uinfo'];
	}
	if (!DB::result_first("select COUNT(*) from %t where openid=%d", array('user_qqconnect', $openid))) {
		include  template('qqcallback');
		exit();
	}
	session_unset();
	$user = C::t('user_qqconnect') -> fetch_by_openid($openid);
	if ($user['status'] == -2) {
		showmessage('user_stopped_please_admin');
	} elseif ($_G['setting']['bbclosed'] > 0 && $user['adminid'] != 1) {
		showmessage('site_closed_please_admin');
	}
	setloginstatus($user, $_GET['cookietime'] ? 2592000 : 0);
	if ($_G['member']['lastip'] && $_G['member']['lastvisit']) {
		dsetcookie('lip', $_G['member']['lastip'] . ',' . $_G['member']['lastvisit']);
	}
	C::t('user_status') -> update($_G['uid'], array('lastip' => $_G['clientip'], 'lastvisit' => TIMESTAMP, 'lastactivity' => TIMESTAMP));

	$param = array(
		'username' => $result['username'],
		'usergroup' => $_G['group']['grouptitle'],
		'uid' => $_G['member']['uid'],
		'groupid' => $_G['groupid'],
		'syn' => 0
	);

	$extra = array(
		'showdialog' => true,
	 	'locationtime' => true,
	  	'extrajs' => ''
	);

	$loginmessage = $_G['groupid'] == 8 ? 'login_succeed_inactive_member' : 'login_succeed';
	$location = $_G['groupid'] == 8 ? 'index.php?open=password' : dreferer();
	if (defined('IN_MOBILE')) {
		showmessage('location_login_succeed_mobile', $location, array('username' => $result['username']), array('location' => true));
	} else {
		showmessage($loginmessage, $location, $param, $extra);
	}

} elseif ($_GET['type'] == 'newuser') {//不绑定，直接使用时根据QQ登录获取的用户信息来添加用户，用户名，姓名使用QQ昵称，邮箱和密码随机
	$openid = $_SESSION['openid'];
	$uinfo = $_SESSION['uinfo'];
	if (empty($openid)) {
		@header("Location:" . $_G[siteurl] . 'user.php?mod=qqlogin&type=callback');
		exit();
	} elseif (DB::result_first("select COUNT(*) from %t where openid=%d", array('user_qqconnect', $openid))) {

	}
	@session_unset();
	$groupinfo = array();
	$addorg = 0;
	if ($_G['setting']['regverify']) {
		$groupinfo['groupid'] = 8;
	} else {
		$groupinfo['groupid'] = $_G['setting']['newusergroupid'];
		$addorg = 1;
	}
	$password = random(20);
	$email = $password . '@qq.com';
	$result = uc_user_register(addslashes($uinfo['nickname']), $password, $email, '', 0, '', $_G['clientip'], $addorg);
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
	} else {
		C::t('user_qqconnect') -> insert_by_openid($openid, $uid, $uinfo, 1);
	}
	setloginstatus(array('uid' => $uid, 'username' => $result['username'], 'password' => $password, 'groupid' => $groupinfo['groupid'], ), 0);
	showmessage(lang('congratulations') . $result['username'] . '，' . lang('login_success'), $_G['siteurl']);
} elseif ($_GET['type'] == 'olduser') {
	$userinfo = $_GET['userinfo'];
	if (isemail($userinfo['email'])) {
		$user = C::t('user') -> fetch_by_email($userinfo['email']);
	} else {
		$user = C::t('user') -> fetch_by_nickname($userinfo['email']);
	};
	if (!$user) {
		showmessage('username_or_password_error', $_G['siteurl'] . 'user.php?mod=qqlogin&type=callback');
	}
	$md5pw = md5(md5($userinfo['pw']) . $user['salt']);
	if ($md5pw == $user['password']) {
		C::t('user_qqconnect') -> insert_by_openid($_SESSION['openid'], $user['uid'], $_SESSION['uinfo']);
		session_unset();
		setloginstatus($user, $_GET['cookietime'] ? 2592000 : 0);
		showmessage(lang('congratulations') . $user['username'] . '，' . lang('qq_shortcut_login_binding_success'), $_G['siteurl']);
	} else {
		showmessage('user_password_not_correct', $_G['siteurl'] . 'user.php?mod=qqlogin&type=callback');
	}
}
?>