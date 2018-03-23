<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/3/23
 * Time: 17:45
 */
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');
$do=isset($_GET['do']) ? trim($_GET['do']):'';
$uid=intval($_G['uid']);
$member=C::t('user_profile')->get_userprofile_by_uid($_G['uid']);

if($do == 'chkpass'){

    session_start();

    $type = isset($_GET['returnType']) ? $_GET['returnType']:'json';

    $password=$_GET['chkpassword'];

    if($_GET['chkcodeverify']){

        if(!check_seccode($_GET['seccodeverify'],$_GET['sechash'])){

            showTips(array('error'=>lang('submit_seccode_invalid'),'codeerror'=>true), $type);
        }
    }

    if(md5(md5($password).$member['salt']) != $member['password']){

            if(isset($_SESSION['chkerrornum'.$uid])){
                $_SESSION['chkerrornum'.$uid] += 1;
            }else{
                $_SESSION['chkerrornum'.$uid] = 1;
            }
        showTips(array('error'=>lang('login_password_invalid'),'errornum'=>$_SESSION['chkerrornum'.$uid]), $type);

    }else{
        $_SESSION['chkerrornum'.$uid] = 0;
        showTips(array('success'=>true), $type);
    }
}elseif($do == 'chkemail'){

    $type = $_GET['returnType'];

    $verifyemail = $member['email'];

    $idstring = random(6);

    $confirmurl = C::t('shorturl')->getShortUrl("{$_G[siteurl]}user.php?mod=profile&op=password&do=changeemail&uid={$_G[uid]}&email={$verifyemail}&idchk=$idstring");

    $email_bind_message = lang('email', 'varifyemail_message', array(
        'username' => $_G['member']['username'],
        'sitename' =>  $_G['setting']['sitename'],
        'siteurl' => $_G['siteurl'],
        'url' => $confirmurl
    ));
    if(!sendmail("$member[username] <$verifyemail>", lang('email', 'varifyemail_subject'), $email_bind_message)) {

        runlog('sendmail', "$verifyemail sendmail failed.");

        showTips(array('error'=>lang('setting_mail_send_error')),$type);

    }else{
        $updatearr = array("emailsenddate"=>$idstring.'_'.time());
        C::t('user')->update($uid,$updatearr);
        showTips(array('success'=>array('email'=>$verifyemail)),$type);

    }
}