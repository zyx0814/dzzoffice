<?php
if (!defined('IN_DZZ')) {

    exit('Access Denied');
}
global  $_G;

$oauth = new user\sso\classes\Oauth();

$do = isset($_GET['do']) ? $_GET['do']:'';

if($do == 'getkeyid'){//申请验证keyid和秘钥

    $backurl = isset($_GET['backurl']) ? $_GET['backurl']:'';

    $result = $oauth->setkeyserectid($backurl);

    exit(json_encode($result));

}elseif($do == 'getcode'){//获取验证字符串

    $keyid = isset($_GET['appid']) ? $_GET['appid']:'';

    $backurl = isset($_GET['client_uri']) ? $_GET['client_uri']:'';

    $state   = isset($_GET['state']) ? $_GET['state']:'';

    if(!$oauth->chk_callback_host($keyid,$backurl)) {

        exit(json_encode($oauth->showError(4006)));
    }

    if($_G['uid']){

        $uid = intval($_G['uid']);

        $oauth->code = $uid;

        $safecode = $oauth->safecode();

        if(!$r = C::t('user_salf')->fetch($uid)) {

            C::t('user_salf')->insert(array('keyid' => $keyid, 'uid' => $uid));
        }

        $j = (strpos($backurl,'?') == false) ? '?':'&';

        $backurl = $backurl.$j.'safecode='.$safecode.'&state='.$state;

        header("location:$backurl");

        exit();

    }else{

        $referer = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];

        $loginurl = $_G['siteurl']."user.php?mod=login&op=logging&action=login&referer=".urlencode($referer);

        header('location:'.$loginurl);

        exit();
    }

}elseif($do == 'gettoken'){//获取token

    $keyid = isset($_GET['appid']) ? $_GET['appid']:'';

    $code = isset($_GET['safecode']) ? $_GET['safecode']:'';

    $uid = $oauth->dsafecode($code);

    if($return = C::t('user_salf')->fetch($uid)){

        if($return['keyid'] != $keyid){

            exit(json_encode($oauth->showError(4001)));
        }
        $oauth->uid = $uid;

    }else{

        exit(json_encode($oauth->showError(4007)));
    }

    $result = $oauth->gettoken($keyid);

    exit(json_encode($result));

}elseif($do == 'updatetoken'){//刷新token

    $refreshtoken = isset($_GET['refreshtoken']) ? $_GET['refreshtoken']:'';

    $tokenid = isset($_GET['tokenid']) ? $_GET['tokenid']:'';

    $result = $oauth->refreshtoken($tokenid,$refreshtoken);

    exit(json_encode($result));



}elseif($do == 'getuserbasic'){//获取用户信息

    $tokenid = isset($_GET['tokenid']) ? $_GET['tokenid']:'';

    if(!$uid = $oauth->chk_token($tokenid)) exit(json_encode($oauth->showError(4004)));

    $info = C::t('user')->fetch_userbasic_by_uid($uid);

    exit(json_encode($info));

}elseif($do == 'getuseratavar'){

    $tokenid = isset($_GET['tokenid']) ? $_GET['tokenid']:'';

    if(!$uid = $oauth->chk_token($tokenid)) exit(json_encode($oauth->showError(4004)));

    $openid = $oauth->safecode($uid);

    exit(json_encode(array('atavar_url'=>$_G['siteurl']."user.php?mod=sso&op=avatar&openid=".$openid)));

}


