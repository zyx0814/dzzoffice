<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

$dzz_action = 141;

if(submitcheck('lostpwsubmit')) {
	$_GET['email'] = strtolower(trim($_GET['email']));
	if($_GET['email']) {
		$emailcount = C::t('user')->count_by_email($_GET['email'], 1);
		if(!$emailcount) {
			showmessage('抱歉，使用此 Email 的用户不存在，不能使用取回密码功能');
		}
		
		$member = C::t('user')->fetch_by_email($_GET['email'], 1);
		$tmp['email'] = $member['email'];
	}
	if(!$member) {
		showmessage('抱歉，您填写的账户资料不匹配，不能使用取回密码功能，如有疑问请与管理员联系');
	} elseif($member['adminid'] == 1) {
		showmessage('管理员帐号不允许找回');
	}

	
	if($member['username'] != $_GET['username']) {
		showmessage('抱歉，您填写的账户资料不匹配，不能使用取回密码功能，如有疑问请与管理员联系');
	}

	$idstring = random(6);
	C::t('user')->update($member['uid'], array('authstr' => "$_G[timestamp]\t1\t$idstring"));
	require_once libfile('function/mail');
	$get_passwd_subject = lang('email', 'get_passwd_subject');
	$get_passwd_message = lang(
		'email',
		'get_passwd_message',
		array(
			'username' => $member['username'],
			'sitename' => $_G['setting']['sitename'],
			'siteurl' => $_G['siteurl'],
			'uid' => $member['uid'],
			'idstring' => $idstring,
			'clientip' => $_G['clientip'],
		)
	);
	if(!sendmail("$_GET[username] <$tmp[email]>", $get_passwd_subject, $get_passwd_message)) {
		runlog('sendmail', "$tmp[email] sendmail failed.");
	}
	showmessage('取回密码的方法已通过 Email 发送到您的信箱('.$_GET['email'].')中，<br />请在 3 天之内修改您的密码', $_G['siteurl'], array('email'=>$_GET['email']));
}else{
	
	include template('lostpasswd');
}

?>
