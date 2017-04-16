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
			showmessage('use_Email_user_not_exist');
		}
		
		$member = C::t('user')->fetch_by_email($_GET['email'], 1);
		$tmp['email'] = $member['email'];
	}
	if(!$member) {
		showmessage('apology_account_data_mismatching');
	} elseif($member['adminid'] == 1) {
		showmessage('administrator_account_not_allowed_find');
	}

	
	if($member['username'] != $_GET['username']) {
		showmessage('apology_account_data_mismatching');
	}

	$idstring = random(6);
	C::t('user')->update($member['uid'], array('authstr' => "$_G[timestamp]\t1\t$idstring"));
	require_once libfile('function/mail');
	$get_passwd_subject = lang('get_passwd_subject');
	$get_passwd_message = lang(
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
	showmessage(lang('password_has_been_sent_email').'('.$_GET['email'].')'.lang('please_tree_edit_password'), $_G['siteurl'], array('email'=>$_GET['email']));
}else{
	
	include template('lostpasswd');
}

?>
