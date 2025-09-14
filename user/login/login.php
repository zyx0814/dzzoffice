<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
if ($_G['setting']['loginset']['template'] !== '4') {
    exit(json_encode(array('error'=>'管理员已关闭此登录方式')));
}
if(isset($_GET['uid'])){
	$uid=intval($_GET['uid']);
	$user=getuserbyuid($uid);
	if($user['adminid']==1){
        writelog('loginlog', '用户ID：'.$_GET['uid'].' 使用登录模板4登录失败,此用户为管理员');
        exit(json_encode(array('error'=>'管理员禁止登陆')));
	}
    if ($user['status'] > 0) {
        writelog('loginlog', '用户ID：'.$_GET['uid'].' 使用登录模板4登录失败,此用户已禁用');
        exit(json_encode(array('error'=>'此用户已禁用，请联系管理员')));
    }
    $orgid = $_G['setting']['loginset']['orgid'];
    // 检查用户是否属于指定机构
    if($orgid == 'other') {
        $Users = C::t('organization_user')->fetch_orgids_by_uid($uid);
        if ($Users) {
            writelog('loginlog', '用户ID：'.$uid.' 使用登录模板4登录失败，不在允许的机构范围内');
            exit(json_encode(array('error'=>'该用户不在允许登录的用户范围内')));
        }
    } elseif($orgid = intval($orgid)) {
        $Users = DB::result_first("SELECT COUNT(*) FROM %t WHERE orgid=%d AND uid=%d", array('organization_user', $orgid, $uid));
        if(!$Users) {
            writelog('loginlog', '用户ID：'.$uid.' 使用登录模板4登录失败，不在允许的机构范围内');
            exit(json_encode(array('error'=>'该用户不在允许登录的用户范围内')));
        }
    } else {
        exit(json_encode(array('error'=>'该用户不在允许登录的用户范围内')));
    }
	setloginstatus($user, $_GET['cookietime'] ? 2592000 : 0);
    if ($_G['member']['lastip'] && $_G['member']['lastvisit']) {
        dsetcookie('lip', $_G['member']['lastip'] . ',' . $_G['member']['lastvisit']);
    }
    //记录登录
    C::t('user_status')->update($_G['uid'], array('lastip' => $_G['clientip'], 'lastvisit' => TIMESTAMP, 'lastactivity' => TIMESTAMP));

    $location = /*$_G['groupid'] == 8 ? 'user.php?mod=profile' :*/dreferer();//待修改

    $href = str_replace("'", "\'", $location);
    $href = preg_replace("/user\.php\?mod\=login.*?$/i", "", $location);

    writelog('loginlog', '登录成功');
    showTips(array('success' => array('message' => lang('login_succeed_no_redirect'), 'url_forward' => $href)), 'json');
	exit();
}
showmessage('Access Denied',$_GET['referer']?$_GET['referer']:$_G['siteurl']);