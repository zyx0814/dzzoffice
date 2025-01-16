<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
$gid = isset($_GET['gid']) ? intval($_GET['gid']) : 0;
$perm = C::t('organization_admin')->chk_memberperm($gid, $uid);
$groupinfo = C::t('organization')->fetch($gid);
//成员信息
$members = C::t('organization_user')->fetch_user_byorgid($gid);
//处理成员头像函数
$userids = array();
foreach ($members as $k=>$v) {
    $userids[] = $v['uid'];
     $members[$k]['perm'] = C::t('organization_admin')->chk_memberperm($gid,$v['uid']);
}
$userstr = implode(',',$userids);
$members = C::t('resources_event')->result_events_has_avatarstatusinfo($userids, $members);
require template('mobile/member');