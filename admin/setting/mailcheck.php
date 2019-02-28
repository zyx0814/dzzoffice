<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}

if(!submitcheck('mailchecksubmit')) {
	$op = $_GET['op']?$_GET['op']:' ';
	$navtitle=lang('email_send_test');
	include template('mailcheck');
}else{
	if(!is_array($_G['setting']['mail'])) {
		$_G['setting']['mail'] = dunserialize($_G['setting']['mail']);
	}
	$test_to = $_GET['test_to'];
	$test_from = $_GET['test_from'];
	$date = date('Y-m-d H:i:s');
	$alertmsg = '';

	$title = lang('setting_mail_check_title_'.$_G['setting']['mail']['mailsend']);
	$message = lang('setting_mail_check_message_'.$_G['setting']['mail']['mailsend']).' '.$test_from.lang('setting_mail_check_date').' '.$date;

	$_G['setting']['bbname'] = lang('setting_mail_check_method_1');
	include libfile('function/mail');
	$succeed = sendmail($test_to, $title.' @ '.$date, $_G['setting']['bbname']."\n\n\n$message", $test_from);
	$_G['setting']['bbname'] = lang('setting_mail_check_method_2');
	$succeed = sendmail($test_to, $title.' @ '.$date, $_G['setting']['bbname']."\n\n\n$message", $test_from);
	if($succeed) {
		$alertmsg = lang('setting_mail_check_success_1')."$title @ $date".lang('setting_mail_check_success_2');
	} else {
		$alertmsg = lang('setting_mail_check_error').$alertmsg;
	}
	echo '<script language="javascript">alert(\''.str_replace(array('\'', "\n", "\r"), array('\\\'', '\n', ''), $alertmsg).'\');</script>';
}
?>
