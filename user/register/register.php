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
$setting = $_G['setting'];
$showregisterform = 1;
Hook::listen('register_before');//注册预处理钩子
if ($_G['uid']) {
    $url_forward = dreferer();
    if (strpos($url_forward, 'user.php') !== false) {
        $url_forward = 'index.php';
    }
    showmessage('login_succeed', $url_forward ? $url_forward : './', array('username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle'], 'uid' => $_G['uid']), array());
} elseif ($setting['bbclosed']) {
    showmessage('site_closed_please_admin');
} elseif (!$setting['regclosed']) {
    if ($_GET['action'] == 'activation' || $_GET['activationauth']) {
        if (!$setting['ucactivation'] && !$setting['closedallowactivation']) {
            showmessage('register_disable_activation');
        }
    } elseif (!$setting['regstatus']) {
        showmessage(!$setting['regclosemessage'] ? 'register_disable' : str_replace(array("\r", "\n"), '', $setting['regclosemessage']));
    }
}
$seccodecheck = $setting['seccodestatus'] & 1;

//判断是否提交
if (!submitcheck('regsubmit')) {
    //应用注册页挂载点
    Hook::listen('appregister');
    $bbrules = $setting['bbrules'];

    $regname = $setting['regname'];

    $bbrulehash = $bbrules ? substr(md5(FORMHASH), 0, 8) : '';
    $auth = $_GET['auth'];

    $username = isset($_GET['username']) ? dhtmlspecialchars($_GET['username']) : '';
    $allowitems = array();
    foreach ($_G['cache']['profilesetting'] as $key => $value) {
        if ($value['available'] > 0)
            $allowitems[] = $key;
    }
    $htmls = $settings = array();
    foreach ($_G['cache']['fields_register'] as $field) {
        $fieldid = $field['fieldid'];
        $html = profile_setting($fieldid, array(), false, false, true);
        if ($html) {
            $settings[$fieldid] = $_G['cache']['profilesetting'][$fieldid];
            $htmls[$fieldid] = $html;
        }
    }
    if ($seccodecheck) {
        $seccode = random(6, 1);
    }
    $navtitle = $setting['reglinkname'];

    $dreferer = dreferer();
    if ($setting['loginset']['template'] == 3) {
        include template('register3');
    } else {
        include template('register');
    }
    exit();
} else {
    $type = isset($_GET['returnType']) ? $_GET['returnType'] : '';
    Hook::listen('check_val', $_GET);//用户数据验证钩子,用户注册资料信息提交验证
    //验证IP同一时间段内注册
    if($setting['regctrl']) {
        if(C::t('regip')->count_by_ip_dateline($_G['clientip'], $_G['timestamp']-$setting['regctrl']*3600)) {
            showTips(array('error' => lang('register_ctrl', array('regctrl' => $setting['regctrl']))), $type);
        }
    }
    $result = $_GET;
    Hook::listen('register_common', $result);//用户注册钩子

    //获取ip
    $ip = $_G['clientip'];
    //用户状态表数据
    $status = array(
        'uid' => $result['uid'],
        'regip' => (string)$ip,
        'lastip' => (string)$ip,
        'lastvisit' => TIMESTAMP,
        'lastactivity' => TIMESTAMP,
        'lastsendmail' => 0
    );
    //插入用户状态表
    DB::insert('user_status', $status, 1);
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
        $setarr['uid'] = $result['uid'];
        C::t('user_profile')->insert($setarr);
    }
    if($setting['regctrl']) {
        C::t('regip')->delete_by_dateline($_G['timestamp']-$setting['regctrl']*3600);
        C::t('regip')->insert(array('ip' => $_G['clientip'], 'count' => -1, 'dateline' => $_G['timestamp']));
    }
    //新用户登录
    setloginstatus(array(
        'uid' => $result['uid'],
        'username' => $result['username'],
        'password' => $result['password'],
        'groupid' => $result['groupid'],
    ), 0);
    $welcomemsg = & $setting['welcomemsg'];
    $welcomemsgtitle = & $setting['welcomemsgtitle'];
    $welcomemsgtxt = & $setting['welcomemsgtxt'];
    $email = $result['email'];
    $username = $result['username'];
    if($welcomemsg && !empty($welcomemsgtxt)) {
        $welcomemsgtitle = replacesitevar($welcomemsgtitle);
        $welcomemsgtxt = replacesitevar($welcomemsgtxt);
        if($welcomemsg == 1) {
            $welcomemsgtxt = nl2br(str_replace(':', '&#58;', $welcomemsgtxt));
            $notevars = array(
                'from_id' => 0,
                'from_idtype' => 'welcomemsg',
                'url' => '',
                'author' => $_G['username'],
                'authorid' => $_G['uid'],
                'note_title' => $welcomemsgtitle,
                'note_message' => $welcomemsgtxt
            );
            $action = 'register_welcomemsg';
            $type = 'register_welcomemsg_' . $result['uid'];

            dzz_notification::notification_add($result['uid'], $type, $action, $notevars);
        } elseif($welcomemsg == 2) {
            if (!sendmail_cron("$username <$email>", $welcomemsgtitle, $welcomemsgtxt)) {
                runlog('sendmail', "$email sendmail failed.");
                return false;
            }
        } elseif($welcomemsg == 3) {
            if (!sendmail_cron("$username <$email>", $welcomemsgtitle, $welcomemsgtxt)) {
                runlog('sendmail', "$email sendmail failed.");
                return false;
            }
            $welcomemsgtxt = nl2br(str_replace(':', '&#58;', $welcomemsgtxt));
            $notevars = array(
                'from_id' => 0,
                'from_idtype' => 'welcomemsg',
                'url' => '',
                'author' => $_G['username'],
                'authorid' => $_G['uid'],
                'note_title' => $welcomemsgtitle,
                'note_message' => $welcomemsgtxt
            );
            $action = 'register_welcomemsg';
            $type = 'register_welcomemsg_' . $result['uid'];

            dzz_notification::notification_add($result['uid'], $type, $action, $notevars);
        }
    }

    //设置显示提示文字
    $param = daddslashes(array('sitename' => $setting['sitename'], 'username' => $result['username'], 'usergroup' => $_G['cache']['usergroups'][$result['groupid']]['grouptitle'], 'uid' => $result['uid']));

    $messageText = lang('register_succeed', $param);

    //获取之前的链接
    $url_forward = (isset($_GET['referer'])) ? $_GET['referer'] : dreferer();


    $url_forward = $url_forward ? $url_forward : './';
    if (strpos($url_forward, 'user.php') !== false) {
        $url_forward = 'index.php';
    }
    showTips(array('success' => array('message' => $messageText, 'url_forward' => $url_forward)), $type);

}