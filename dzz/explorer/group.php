<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
$gid = isset($_GET['gid']) ? $_GET['gid']:'';
//群组信息
if(!$group = C::t('organization')->fetch($gid)){
    showmessage(lang('no_group'),dreferer());
}
//获取群组成员权限
$perm = C::t('organization_admin')->chk_memberperm($gid,$uid);
//判断群组是否开启，如果未开启(共享目录)并且不是管理员不能访问
if(!$group['diron'] && !$perm) {
    showmessage(lang('no_privilege'),dreferer());
}
//判断是否有权限访问群组，如果不是管理员权限(主要针对系统管理员和上级管理员),并且非成员
if(!$perm  && !C::t('organization')->ismember($gid,$uid,false)) {
    showmessage(lang('no_privilege'),dreferer());
}

$perms = get_permsarray();//获取所有权限

$explorer_setting = get_resources_some_setting();
if($group['type'] == 1 && !$explorer_setting['grouponperm']){
    showmessage(lang('no_privilege'),dreferer());
}
if($group['type'] == 0 && !$explorer_setting['orgonperm']){
    showmessage(lang('no_privilege'),dreferer());
}
$contenterrormsg = '';
if(!$group['syatemon']){
    showmessage(lang('no_group_by_system'),dreferer());
}
if(!$group['manageon'] && $perm < 1){
    showmessage(lang('no_privilege'),dreferer());
}

if(!$group['available']){
    $contenterrormsg=lang('group_no_file_by_system');
}else{
  if(!$group['diron'] && !$perm){
        $contenterrormsg= ($group['type'] > 0) ? lang('group_no_file_by_manage'):lang('group_no_file_by_system');
    }
}
$allowvisit = array('file','group_ajax','right_popbox','delete_group');
$do = isset($_GET['do']) ? trim($_GET['do']) :'file';
if(!in_array($do,$allowvisit)){
    showmessage(lang('access_denied'),dreferer());
}else{
    require MOD_PATH.'/group/'.$do.'.php';
}

include template('mydocument_content');
exit();
