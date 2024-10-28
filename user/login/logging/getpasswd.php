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

if($_GET['uid'] && $_GET['id']) {

    $dzz_action = 141;
    $member = C::t('user')->get_user_by_uid($_GET['uid']);
    $table_ext =  '';
    list($dateline, $operation, $idstring) = explode("\t", $member['authstr']);

    if($dateline < TIMESTAMP - 86400 * 3 || $operation != 1 || $idstring != $_GET['id']) {
        showmessage(lang('getpasswd_illegal'), NULL);
    }

    if(!submitcheck('getpwsubmit') || $_GET['newpasswd1'] != $_GET['newpasswd2']) {
        $hashid = $_GET['id'];
        $uid = $_GET['uid'];
        include template('getpasswd');
    } else {
        if($_GET['newpasswd1'] != addslashes($_GET['newpasswd1'])) {
            showmessage(lang('profile_passwd_illegal'));
        }
        if($_G['setting']['pwlength']) {
            if(strlen($_GET['newpasswd1']) < $_G['setting']['pwlength']) {
                showmessage(lang('profile_password_tooshort', array('pwlength' => $_G['setting']['pwlength'])));
            }
        }
        if($_G['setting']['strongpw']) {
            $strongpw_str = array();
            if(in_array(1, $_G['setting']['strongpw']) && !preg_match("/\d+/", $_GET['newpasswd1'])) {
                $strongpw_str[] = lang('strongpw_1');
            }
            if(in_array(2, $_G['setting']['strongpw']) && !preg_match("/[a-z]+/", $_GET['newpasswd1'])) {
                $strongpw_str[] = lang('strongpw_2');
            }
            if(in_array(3, $_G['setting']['strongpw']) && !preg_match("/[A-Z]+/", $_GET['newpasswd1'])) {
                $strongpw_str[] = lang('strongpw_3');
            }
            if(in_array(4, $_G['setting']['strongpw']) && !preg_match("/[^a-zA-z0-9]+/", $_GET['newpasswd1'])) {
                $strongpw_str[] = lang('strongpw_4');
            }
            if($strongpw_str) {
                showmessage(lang('password_weak').implode(',', $strongpw_str));
            }
        }
        $salt=substr(uniqid(rand()), -6);

        $password = md5(md5($_GET['newpasswd1']).$salt);
       
        C::t('user')->update($_GET['uid'], array('password' => $password,'authstr' => '','salt'=>$salt));
        showmessage(lang('getpasswd_succeed'), 'index.php', array());
    }

} else {
    showmessage(lang('parameters_error'));
}