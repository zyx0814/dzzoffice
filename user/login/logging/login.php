<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/3/1
 * Time: 18:53
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
$setting = isset($_G['setting']) ? $_G['setting'] : '';

if (empty($setting)) {
    $setting = C::t('setting')->fetch_all(array(), true);
}
if ($_G['uid'] > 0) {
    if ($_G['setting']['bbclosed']) {
        include template('site_close');
        exit();
    }

    $location = dreferer();//待修改

    $href = str_replace("'", "\'", $location);
    $href = preg_replace("/user\.php\?mod\=login.*?$/i", "", $location);

    writelog('loginlog', '登录成功');
    showmessage('login_succeed_no_redirect', $href);
}
$_G['allow_loginmod'] = $setting['allow_loginmod'] = unserialize($setting['allow_loginmod']);
//Hook::listen('login_check');//检查登录状态

$from_connect = $setting['connect']['allow'] && !empty($_GET['from']) ? 1 : 0;

$seccodecheck = $from_connect ? false : $setting['seccodestatus'] & 2;//是否开启验证码

$seccodestatus = !empty($_GET['lssubmit']) ? false : $seccodecheck;

if (!isset($_GET['loginsubmit'])) {//是否提交

    $username = !empty($_G['cookie']['loginuser']) ? dhtmlspecialchars($_G['cookie']['loginuser']) : '';

    $cookietimecheck = !empty($_G['cookie']['cookietime']) || !empty($_GET['cookietime']) ? 'checked="checked"' : '';

    if ($seccodecheck) $seccode = random(6, 1);

    $referer = (isset($_GET['referer'])) ? $_GET['referer'] : dreferer();

    $_G['sso_referer'] = $referer;

    $navtitle = lang('title_login');
    $templateId = isset($_GET['template']) ? $_GET['template'] : (isset($setting['loginset']['template']) ? $setting['loginset']['template'] : 1);
    if ($templateId == 4) {
        if (isset($_GET['template']) && $_GET['template'] == 4) {
            $templateId = 1;
        }

        if ($setting['loginset']['template'] == 4) {
            $templateId = 4;
            $data = array();
            if($setting['loginset']['orgid'] && $setting['loginset']['orgid'] !== 'other') {
                $orgid = $setting['loginset']['orgid'];
                $param = array('organization_user', 'organization_job', 'user');
                $sql = "ou.orgid = %d AND u.adminid != 1 AND u.status = 0";
                if (!$_G['cache']['usergroups']) loadcache('usergroups');
                $users = DB::fetch_all("SELECT u.uid,u.username,u.groupid,j.name as jobname FROM %t ou LEFT JOIN %t j ON ou.jobid = j.jobid LEFT JOIN %t u ON ou.uid = u.uid WHERE $sql ORDER BY u.uid ASC LIMIT 1000",array_merge($param, array($orgid)));
                foreach ($users as $user) {
                    $jobname = $user['jobname'];
                    if(!$jobname) {
                        $usergroup = $_G['cache']['usergroups'][$user['groupid']] ?? array();
                        $jobname = $usergroup['grouptitle'] ? $usergroup['grouptitle'] : '成员';
                    }
                    
                    $data[] = array(
                        'uid' => $user['uid'],
                        'username' => $user['username'],
                        'jobname' => $jobname
                    );
                }
            }
            
        }
    }
    include template('login_single' . $templateId);
} else {
    $type = isset($_GET['returnType']) ? $_GET['returnType'] : 'json';//返回值方式

    Hook::listen('login_valchk', $_GET);//验证登录输入值及登录失败次数
    //验证码开启，检测验证码
    if ($seccodecheck && !check_seccode($_GET['seccodeverify'], $_GET['sechash'])) {
        showTips(array('error' => lang('submit_seccode_invalid')), $type);
    }

    //登录
    $result = userlogin($_GET['email'], $_GET['password'], $_GET['questionid'], $_GET['answer'], 'auto', $_G['clientip']);

    if ($result['status'] == -2) {
        $errorlog = "用户" . ($result['ucresult']['email'] ? $result['ucresult']['email'] : $_GET['email']) . "尝试登录失败，该用户已禁用。";
        writelog('loginlog', $errorlog);
        showTips(array('error' => lang('user_stopped_please_admin')), $type);
    } elseif ($_G['setting']['bbclosed'] > 0 && $result['member']['adminid'] != 1) {
        showTips(array('error' => lang('site_closed_please_admin')), $type);
    }

    if ($result['status'] > 0) {

        //设置登录
        setloginstatus($result['member'], $_GET['cookietime'] ? 2592000 : 0);

        if ($_G['member']['lastip'] && $_G['member']['lastvisit']) {

            dsetcookie('lip', $_G['member']['lastip'] . ',' . $_G['member']['lastvisit']);
        }

        //记录登录
        C::t('user_status')->update($_G['uid'], array('lastip' => $_G['clientip'], 'lastvisit' => TIMESTAMP, 'lastactivity' => TIMESTAMP));
        //邀请登录
        //Hook::listen('inviate');

        $location = dreferer();//待修改

        $href = str_replace("'", "\'", $location);
        $href = preg_replace("/user\.php\?mod\=login.*?$/i", "", $location);

        writelog('loginlog', '登录成功');
        showTips(array('success' => array('message' => lang('login_succeed_no_redirect'), 'url_forward' => $href)), $type);


    } else {//登录失败记录日志 
        //写入日志
        $password = preg_replace("/^(.{".round(strlen($_GET['password']) / 4)."})(.+?)(.{".round(strlen($_GET['password']) / 6)."})$/s", "\\1***\\3", $_GET['password']);
        $errorlog = "用户" . ($result['ucresult']['email'] ? $result['ucresult']['email'] : $_GET['email']) . "尝试登录[" . $password . "]错误";
        writelog('loginlog', $errorlog);

        loginfailed($_GET['email']);//更新登录失败记录

        if ($_G['member_loginperm'] > 1) {

            showTips(array('error' => lang('login_invalid', array('loginperm' => $_G['member_loginperm'] - 1))), $type);

        } elseif ($_G['member_loginperm'] == -1) {

            showTips(array('error' => lang('login_password_invalid')), $type);

        } else {

            showTips(array('error' => lang('login_strike', array('forbiddentime' => $_G['setting']['forbiddentime'] ? $_G['setting']['forbiddentime'] : 900))), $type);
        }
    }
}