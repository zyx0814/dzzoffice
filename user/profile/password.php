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
Hook::listen('email_chk', $_GET);
$navtitle = lang('myCountCenter');
Hook::listen('check_login');
$type = isset($_GET['returnType']) ? $_GET['returnType'] : 'json';
$do = isset($_GET['do']) ? trim($_GET['do']) : 'editpass';
$uid = intval($_G['uid']);
$seccodecheck = $_G['setting']['seccodestatus'] & 4;
$member = C::t('user_profile')->get_userprofile_by_uid($_G['uid']);
if($_G['adminid'] == 1) {
    $my_info = false;
} else {
    $my_info = perm_check::checkuserperm('my_info');
}

if ($do == 'editpass') {
    $navtitle = lang('password_edit');
    $strongpw = ($_G['setting']['strongpw']) ? json_encode($_G['setting']['strongpw']) : '';
    if (isset($_GET['editpass'])) {
        if ($my_info) showTips(array('error' => lang('no_modify_password')), $type);
        //验证提交是否合法，阻止外部非法提交
        chk_submitroule($type);

        //验证码
        if (!check_seccode($_GET['seccodeverify'], $_GET['sechash'])) {
            showTips(array('error' => lang('submit_seccode_invalid')), $type);
        }
        //验证原密码
        $password0 = $_GET['password0'];
        if (md5(md5("") . $member['salt']) != $member['password']) {
            if (md5(md5($password0) . $member['salt']) != $member['password']) {
                showTips(array('error' => lang('password_error')), $type);
            }
        }
        if ($_GET['password'] != addslashes($_GET['password'])) {
            showTips(array('error' => lang('profile_passwd_illegal')), $type);
        }
        if ($_GET['password'] && $_G['setting']['pwlength']) {
            if (strlen($_GET['password']) < $_G['setting']['pwlength']) {

                showTips(array('error' => lang('profile_password_tooshort'), 'pwlength' => $_G['setting']['pwlength']), $type);
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

                showTips(array('error' => lang('password_weak') . implode(',', $strongpw_str)), $type);

            }
        }

        if ($_GET['password'] && $_GET['password'] !== $_GET['password2']) {
            showTips(array('error' => lang('profile_passwd_notmatch')), $type);
        }
        $setarr = array();

        if ($_GET['password']) {
            $password = preg_match('/^\w{32}$/', $_GET['password']) ? $_GET['password'] : md5($_GET['password']);
            $password = md5($password . $member['salt']);
        }
        if ($password && C::t('user')->update_password($_G['uid'], $password)) {
            showTips(array('success' => lang('update_password_success')), $type);
            exit();
        }
        showTips(array('error' => lang('update_password_failed')), $type);
        exit();
    }

} elseif ($do == 'login') {
    $navtitle = '登录记录';
    function get_log_files($logdir = '', $action = 'action') {
        $dir = opendir($logdir);
        $files = array();
        while ($entry = readdir($dir)) {
            $files[] = $entry;
        }
        closedir($dir);

        if ($files) {
            sort($files);
            $logfile = $action;
            $logfiles = array();
            $ym = '';
            foreach ($files as $file) {
                if (strpos($file, $logfile) !== FALSE) {
                    if (substr($file, 0, 6) != $ym) {
                        $ym = substr($file, 0, 6);
                    }
                    $logfiles[$ym][] = $file;
                }
            }
            if ($logfiles) {
                $lfs = array();
                foreach ($logfiles as $ym => $lf) {
                    $lastlogfile = $lf[0];
                    unset($lf[0]);
                    $lf[] = $lastlogfile;
                    $lfs = array_merge($lfs, $lf);
                }
                return $lfs;
            }
            return array();
        }
        return array();
    }

    !isset($_GET['page']) && $_GET['page'] = 1;
    $lpp = empty($_GET['lpp']) ? 20 : $_GET['lpp'];
    $checklpp = array();
    $checklpp[$lpp] = 'selected="selected"';
    $keyword = "uid=" . $_G['uid'];
    $pattern = '/\b' . preg_quote($keyword, '/') . '\b/';
    $extrainput = '';
    $operation = "loginlog";
    $page = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
    $start = ($page - 1) * $lpp;
    $gets = array(
        'mod' => MOD_NAME,
        'op' => $_GET['op'],
        'do' => $_GET['do']
    );
    $theurl = BASESCRIPT . "?" . url_implode($gets);
    $logdir = DZZ_ROOT . './data/log/';
    $logfiles = get_log_files($logdir, $operation);

    if ($logfiles) $logfiles = array_reverse($logfiles);
    $firstlogs = file($logdir . $logfiles[0]);
    $firstlogsnum = count($firstlogs);
    $countlogfile = count($logfiles);

    $logs = array();
    $jishu = 4000;//每个日志文件最多行数
    $start = ($page - 1) * $lpp;
    $lastlog = $last_secondlog = "";

    $newdata = array();
    foreach ($logfiles as $k => $v) {
        $nowfilemaxnum = ($jishu * ($k + 1)) - ($jishu - $firstlogsnum);
        $startnum = ($nowfilemaxnum - $jishu) <= 0 ? 0 : ($nowfilemaxnum - $jishu + 1);
        $newdata[] = array("file" => $v, "start" => $startnum, "end" => $nowfilemaxnum);
    }
    //print_R($newdata);
    //查询当前分页数据位于哪个日志文件
    $lastlog = $last_secondlog = "";
    foreach ($newdata as $k => $v) {
        if ($start <= $v["end"]) {
            $lastlog = $v;
            if (($start + $lpp) < $v["end"]) {

            } else {
                if (isset($newdata[$k + 1])) {
                    $last_secondlog = $newdata[$k + 1];
                }
            }
            break;
        }
    }

    $j = 0;
    for ($i = $lastlog["start"]; $i < $lastlog["end"]; $i++) {
        if ($start <= ($lastlog["start"] + $j)) {
            break;
        }
        $j++;
    }
    //获取数据开始
    $logs = file($logdir . $lastlog["file"]);
    $logs = array_reverse($logs);
    foreach ($logs as $key => $value) {
        if (!preg_match($pattern, $value)) {
            unset($logs[$key]);
        }
    }
    $count = count($logs);
    if ($lastlog["file"] != $logfiles[0]) {
        $j++;
    }
    $logs = array_slice($logs, $j, $lpp);
    $onecountget = count($logs);

    $jj = 0;
    if ($last_secondlog) {
        for ($i = $last_secondlog["start"]; $i < $last_secondlog["end"]; $i++) {
            if (($jj) >= ($lpp - $onecountget)) {
                break;
            }
            $jj++;
        }
    }

    if ($last_secondlog) {
        $logs2 = file($logdir . $last_secondlog["file"]);
        $logs2 = array_reverse($logs2);
        $end = $lpp - count($logs);
        $logs2 = array_slice($logs2, 0, $jj);
        $logs = array_merge($logs, $logs2);
    }
    $usergroup = array();
    foreach (C::t('usergroup')->range() as $group) {
        $usergroup[$group['groupid']] = $group['grouptitle'];
    }
    $list = array();
    foreach ($logs as $k => $logrow) {
        $log = explode("\t", $logrow);
        if (empty($log[1])) {
            continue;
        }
        $log[1] = dgmdate($log[1], 'y-n-j H:i:s');
        $log[2] = ($log[2] != $_G['member']['username'] ? "<b>$log[2]</b>" : $log[2]);
        $log[3] = $usergroup[$log[3]];

        $list[$k] = $log;
    }
    $multi = multi($count, $lpp, $page, $theurl, 'pull-right');
} elseif ($do == 'changeemail') {
    $navtitle = lang('bindemail_subject');
    $emailchange = $member['emailstatus'];

    $bindemail = isset($_GET['newemail']) ? $_GET['newemail'] : '';

    if (!empty($bindemail)) {
        if ($my_info) showTips(array('error' => lang('no_modify_group')), $type);

        if (C::t('user')->chk_email_by_uid($bindemail, $uid)) {

            showTips(array('error' => lang('profile_email_duplicate')), $type);
        }

        $idstring = random(6);

        $type = $_GET['returnType'];
        $siteurl = $_G['siteurl'];

        $confirmurl = C::t('shorturl')->getShortUrl("user.php?mod=profile&op=password&do=changeemail&uid={$uid}&id=$idstring&email={$bindemail}");

        $email_bind_message = lang('bindemail_message', array(
            'username' => $_G['member']['username'],
            'sitename' => $_G['setting']['sitename'],
            'siteurl' => $_G['siteurl'],
            'url' => $confirmurl
        ));
        if (!sendmail("$member[username] <$bindemail>", lang('bindemail_subject'), $email_bind_message)) {

            runlog('sendmail', "$bindemail sendmail failed.");

            showTips(array('error' => lang('setting_mail_send_error')), $type);

        } else {
            $updatearr = array("emailsenddate" => $idstring . '_' . time());
            C::t('user')->update($uid, $updatearr);
            showTips(array('success' => array('email' => $bindemail)), $type);

        }

    }
}
//三方登录未设置密码时不需要输入原密码
$showoldpassword = 1;
if (md5(md5("") . $member['salt']) == $member['password']) {
    $showoldpassword = 0;
}
include template('pass_safe');
exit();