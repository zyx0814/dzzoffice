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
if($_G['uid']>0){
	  $param = array(
            'username' => $_G['username'],
            'usergroup' => $_G['group']['grouptitle'],
            'uid' => $_G['uid'],
            'groupid' => $_G['groupid'],
            'syn' =>  0
        );
        $loginmessage =  'login_succeed';

        $location = dreferer();//待修改
        
        $href = str_replace("'", "\'", $location);
        $href = preg_replace("/user\.php\?mod\=login.*?$/i", "", $location);
	
        $messageText = lang($loginmessage, $param);
        writelog('loginlog', '登录成功');
		showmessage($messageText,$href);
}

$setting = isset($_G['setting']) ? $_G['setting']:'';

if(empty($setting)){

	$setting= C::t('setting')->fetch_all(array(),true);
}
$_G['allow_loginmod'] = $setting['allow_loginmod'] = unserialize($setting['allow_loginmod']);
//Hook::listen('login_check');//检查登录状态

$from_connect = $setting['connect']['allow'] && !empty($_GET['from']) ? 1 : 0;

$seccodecheck = $from_connect ? false : $setting['seccodestatus'] & 2;//是否开启验证码

$seccodestatus = !empty($_GET['lssubmit']) ? false : $seccodecheck;

if(!isset($_GET['loginsubmit'])) {//是否提交

    $username = !empty($_G['cookie']['loginuser']) ? dhtmlspecialchars($_G['cookie']['loginuser']) : '';

    $cookietimecheck = !empty($_G['cookie']['cookietime']) || !empty($_GET['cookietime']) ? 'checked="checked"' : '';

    if($seccodecheck) $seccode = random(6, 1);

    $referer = (isset($_GET['referer'])) ? $_GET['referer']:dreferer();

    $_G['sso_referer'] = $referer;

    $navtitle = lang('title_login');
	include template('login_single'.($_GET['template']?$_GET['template']:(isset($setting['loginset']['template']) ? $setting['loginset']['template'] : 1)));
} else {
    $type = isset($_GET['returnType']) ?  $_GET['returnType']: 'json';//返回值方式

    Hook::listen('login_valchk',$_GET);//验证登录输入值及登录失败次数


    //验证码开启，检测验证码
    if($seccodecheck && !check_seccode( $_GET['seccodeverify'],$_GET['sechash'])){

        showTips(array('error'=>lang('submit_seccode_invalid')), $type);
    }

    //登录
    $result = userlogin($_GET['email'], $_GET['password'], $_GET['questionid'], $_GET['answer'],'auto', $_G['clientip']);

    if($result['status']== -2){

        showTips(array('error'=>lang('user_stopped_please_admin')),$type);


    }elseif($_G['setting']['bbclosed']>0 && $result['member']['adminid']!=1){

        showTips(array('error'=>lang('site_closed_please_admin')),$type);
    }

    if($result['status'] > 0) {

        //设置登录
        setloginstatus($result['member'], $_GET['cookietime'] ? 2592000 : 0);

        if($_G['member']['lastip'] && $_G['member']['lastvisit']) {

            dsetcookie('lip', $_G['member']['lastip'].','.$_G['member']['lastvisit']);
        }

        //记录登录
        C::t('user_status')->update($_G['uid'], array('lastip' => $_G['clientip'], 'lastvisit' =>TIMESTAMP, 'lastactivity' => TIMESTAMP));

        //邀请登录
        //Hook::listen('inviate');

        //登录成功提示信息
        $param = array(
            'username' => $result['ucresult']['username'],
            'usergroup' => $_G['group']['grouptitle'],
            'uid' => $_G['member']['uid'],
            'groupid' => $_G['groupid'],
            'syn' =>  0
        );
        $loginmessage = /*$_G['groupid'] == 8 ? 'login_succeed_inactive_member' :*/ 'login_succeed';

        $location = /*$_G['groupid'] == 8 ? 'user.php?mod=profile' :*/ dreferer();//待修改

        $href = str_replace("'", "\'", $location);
        $href = preg_replace("/user\.php\?mod\=login.*?$/i", "", $location);

        $messageText = lang($loginmessage, $param);
        writelog('loginlog', '登录成功');
        showTips(array('success'=>array('message'=>$messageText,'url_forward'=>$href)),$type);


    } else {//登录失败记录日志 
        //写入日志
        $errorlog="用户".($result['ucresult']['email'] ? $result['ucresult']['email'] : $_GET['email'])."尝试登录[".$password."]错误";
        writelog('loginlog', $errorlog);

        loginfailed($_GET['email']);//更新登录失败记录

        if($_G['member_loginperm'] > 1) {

            showTips(array('error'=>lang('login_invalid', array('loginperm' => $_G['member_loginperm'] - 1))),$type);

        } elseif($_G['member_loginperm'] == -1) {

            showTips(array('error'=>lang('login_password_invalid')),$type);

        } else {

            showTips(array('error'=>lang('login_strike')),$type);
        }
    }


}
