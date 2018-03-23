<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}

define('NOROBOT', TRUE);
global $_G;
if(isset($_GET['lostpwsubmit'])) {
    $_GET['email'] = strtolower(trim($_GET['email']));
    $type = $_GET['returnType'];
    if($_GET['email']) {
        $emailcount = C::t('user')->count_by_email($_GET['email'], 1);
        if(!$emailcount) {
            showTips(array('error'=>lang('use_Email_user_not_exist')),$type);
        }

        $member = C::t('user')->fetch_by_email($_GET['email'], 1);
        $tmp['email'] = $member['email'];
    }
    if(!$member) {
        showTips(array('error'=>lang('apology_account_data_mismatching')),$type);
    } elseif($member['adminid'] == 1) {
        showTips(array('error'=>lang('administrator_account_not_allowed_find')),$type);
    }


    if($member['username'] != $_GET['username']) {

        showTips(array('error'=>lang('apology_account_data_mismatching')),$type);
    }

    $idstring = random(6);
    C::t('user')->update($member['uid'], array('authstr' => "$_G[timestamp]\t1\t$idstring"));
    //require_once libfile('function/mail');
    $get_passwd_subject = lang('email', 'get_passwd_subject');
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
    showTips(array('success'=>array('msg'=>lang('password_has_been_sent_email',array('email'=>$_GET['email'])).lang('please_tree_edit_password'),'url'=>$_G['siteurl'], 'email'=>$_GET['email']),$type));

}else{

    include template('lostpasswd');
}