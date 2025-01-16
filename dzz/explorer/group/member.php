<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/11/16
 * Time: 15:52
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
$allowoperation = array('setmemberperm','deletemember');
$operation = isset($_GET['operation']) ? trim($_GET['operation']):'';
if($operation && !in_array($operation,$allowoperation)){
    showmessage(lang('explorer_do_failed'),dreferer());
}
if($operation == 'setmemberperm'){
    $guid = isset($_GET['uid']) ? intval($_GET['uid']):'';
    $perm = isset($_GET['perm']) ? intval($_GET['perm']):'';
    $return = C::t('organization_user')->set_admin_by_giduid($guid,$gid,$perm);
    exit(json_encode($return));

}elseif($operation == 'deletemember'){
    $guid = isset($_GET['uids']) ? $_GET['uids']:'';
    $return = C::t('organization_user')->delete_by_orgiduid($gid,$guid);
    exit(json_encode($return));
}else{
    if(isset($_GET['searchmember']) && $_GET['searchmember'] == 1 ){
        $username = trim($_GET['username']);
        if(preg_match('/^\s*$/',$username)){
            $users = C::t('organization_user')->fetch_user_byorgid($gid);
        }else{//搜索用戶
            $users = C::t('organization_user')->fetch_user_byorgid($gid,$username);
        }
        include template('group/search_member');
        exit();
    }
    $users = C::t('organization_user')->fetch_user_byorgid($gid);
    $userids = array();
    foreach($users as $v){
        $userids[] = $v['uid'];
    }
    $userids = array_unique($userids);
    $avatars = array();
    foreach(DB::fetch_all('select u.avatarstatus,u.uid,s.svalue from %t u left join %t s on u.uid=s.uid and s.skey=%s where u.uid in(%n)',array('user','user_setting','headerColor',$userids)) as $v){
        if($v['avatarstatus'] == 1){
            $avatars[$v['uid']]['avatarstatus'] = 1;
        }else{
            $avatars[$v['uid']]['avatarstatus'] = 0;
            $avatars[$v['uid']]['headerColor'] = $v['svalue'];
        }
    }
    $userarr = array();
    foreach($users as $v){
        $v['avatarstatus'] = $avatars[$v['uid']]['avatarstatus'];
        if(!$avatars[$v['uid']]['avatarstatus'] && isset($avatars[$v['uid']]['headerColor'])){
            $v['headerColor'] = $avatars[$v['uid']]['headerColor'];
        }
        $userarr[] = $v;
    }
    $grouppatharr = getpath($groupinfo['path']);
    $grouppathstr = implode('\\',$grouppatharr);
    include template('group/member');
    exit();
}
