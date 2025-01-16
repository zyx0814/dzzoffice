<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2018/1/15
 * Time: 17:18
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
$uuid = $_GET['uid'];
//成员相关信息
$userinfos = DB::fetch_first("select u.username,u.avatarstatus,u.uid,s.svalue from %t u left join %t s on u.uid=s.uid and s.skey=%s where u.uid =%d",array('user','user_setting','headerColor',$uuid));
if($userinfos['avatarstatus'] != 1){
    $userinfos['headerColor'] = $userinfos['svalue'];
}
$uperm =DB::fetch_first("select admintype from %t  where uid = %d and orgid = %d",array('organization_admin',$uuid,$gid));
$userinfos['perm'] = (isset($uperm['admintype'])) ? $uperm['admintype']:0;


$allowoperation = array('setmemberperm','deletemember');
if($operation && !in_array($operation,$allowoperation)){
    showmessage(lang('explorer_do_failed'),dreferer());
}
$operation = isset($_GET['operation']) ? trim($_GET['operation']):'';
if($operation && !in_array($operation,$allowoperation)){
    showmessage(lang('explorer_do_failed'),dreferer());
}

if($operation == 'setmemberperm'){
    $guid = isset($_GET['guid']) ? intval($_GET['guid']):'';
    $perm = isset($_GET['perm']) ? intval($_GET['perm']):'';
    $appid = C::t('app_market')->fetch_appid_by_mod('{dzzscript}?mod='.MOD_NAME, 2);
    $return = C::t('organization_user')->set_admin_by_giduid($guid,$gid,$perm);
    if($return['success']){
        $appid = C::t('app_market')->fetch_appid_by_mod('{dzzscript}?mod='.MOD_NAME, 2);
        $permtitle = lang('explorer_gropuperm');
        if ($guid != getglobal('uid')) {
            $notevars = array(
                'from_id' => $appid,
                'from_idtype' => 'app',
                'url' => $_G['siteurl'] . MOD_URL . '/#group&gid=' . $gid,
                'author' => getglobal('username'),
                'authorid' => getglobal('uid'),
                'dataline' => dgmdate(TIMESTAMP),
                'fname' => getstr($group['orgname'], 31),
                'permtitle' => $permtitle[$perm],
            );
            $action = 'explorer_user_change';
            $type = 'explorer_user_change_' . $gid;

            dzz_notification::notification_add($guid, $type, $action, $notevars, 1, 'dzz/explorer');
            if($return['olduser']){
                    $notevars = array(
                        'from_id' => $appid,
                        'from_idtype' => 'app',
                        'url' => $_G['siteurl'] . MOD_URL . '#group&gid=' . $gid,
                        'author' => getglobal('username'),
                        'authorid' => getglobal('uid'),
                        'dataline' => dgmdate(TIMESTAMP),
                        'fname' => getstr($group['orgname'], 31),
                        'permtitle' => $permtitle[0],
                    );
                    $action = 'explorer_user_change';
                    $type = 'explorer_user_change_' . $gid;
                    dzz_notification::notification_add($return['olduser']['uid'], $type, $action, $notevars, 1, 'dzz/explorer');

            }
        }
        if($perm == 2){
            $body_data = array('username' => getglobal('username'), 'oldusername' => $return['olduser']['username'], 'groupname' => $group['orgname'], 'newusername' => $return['member']);
            $event_body = 'change_creater';
        }else{
            $body_data = array('username' => getglobal('username'), 'groupname' => $group['orgname'], 'permname' =>$permtitle[$perm], 'member' => $return['member']);
            $event_body = 'update_member_perm';
        }
        C::t('resources_event')->addevent_by_pfid($group['fid'], $event_body, 'update_perm', $body_data, $gid, '', $group['orgname']);//记录事件
    }
    exit(json_encode($return));

}elseif($operation == 'deletemember'){
    $guid = isset($_GET['uids']) ? $_GET['uids']:'';
    $deluids = C::t('organization_user')->delete_by_uid_orgid($guid,$gid,1);
    if($deluids){
        $appid = C::t('app_market')->fetch_appid_by_mod('{dzzscript}?mod=explorer', 2);
        foreach($deluids as $v) {
            if ($v['uid'] != getglobal('uid')) {
                $notevars = array(
                    'from_id' => $appid,
                    'from_idtype' => 'app',
                    // 'url' => getglobal('siteurl') . '/#group&gid='.$orgid,
                    'author' => getglobal('username'),
                    'authorid' => getglobal('uid'),
                    'dataline' => dgmdate(TIMESTAMP),
                    'fname' => getstr($group['orgname'], 31),
                );
                $action = 'explorer_user_remove';
                $type = 'explorer_user_remove_' . $gid;

                dzz_notification::notification_add($v['uid'], $type, $action, $notevars, 1, 'dzz/explorer');
            }
        }
        $deluserarr = array();
        foreach(DB::fetch_all("select username from %t where uid in(%n)",array('user',$deluids)) as $v){
            $deluserarr[]= $v['username'];
        }
        //增加事件
        $eventdata = array('username' => getglobal('username'), 'uid' => getglobal('uid'), 'orgname' => $group['orgname'], 'delusers' => implode(',', $deluserarr));
        C::t('resources_event')->addevent_by_pfid($group['fid'], 'delete_group_user', 'deleteuser', $eventdata, $gid, '', $group['orgname']);
    }

    exit(json_encode(array('success' => true, 'uids' => $deluids)));
}else{
    include template('template_right_popbox');
}
exit();