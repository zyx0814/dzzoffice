<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}

Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面

include_once libfile('function/profile');
include_once libfile('function/organization');
$uid=intval($_GET['uid']?$_GET['uid']:$_G['uid']);

$space = C::t('user_profile')->get_user_info_by_uid($uid);

$privacy = $space['privacy']['profile'] ? $space['privacy']['profile'] : array();

$space['regdate'] = dgmdate($space['regdate']);

if($space['lastvisit']) $profiles['lastvisit']=array('title'=>lang('last_visit'),'value'=>dgmdate($space['lastvisit']));


$profiles['regdate']=array('title'=>lang('registration_time'),'value'=>$space['regdate']);

$user=array();

$space['fusesize']=formatsize($space['usesize']);

if(!$_G['cache']['usergroups']) loadcache('usergroups');
$usergroup=$_G['cache']['usergroups'][$space['groupid']];
$profiles['usergroup']=array('title'=>lang('usergroup'),'value'=>$usergroup['grouptitle']);
//资料用户所在的部门
$department='';
foreach(C::t('organization_user')->fetch_orgids_by_uid($uid) as $orgid){
    $orgpath=getPathByOrgid($orgid);
    $department.='<span class="label label-primary">'.implode('-',($orgpath)).'</span>';
}
if(empty($department)) $department=lang('not_join_agency_department');
$profiles['department']=array('title'=>lang('category_department'),'value'=>$department);



if($usergroup['maxspacesize']==0){
    $space['maxspacesize']=0;
}elseif($usergroup['maxspacesize']<0){
    if(($space['addsize']+$space['buysize'])>0){
        $space['maxspacesize']=($space['addsize']+$space['buysize'])*1024*1024;
    }else{
        $space['maxspacesize']=-1;
    }
}else{
    $space['maxspacesize']=($usergroup['maxspacesize']+$space['addsize']+$space['buysize'])*1024*1024;
}
if($space['maxspacesize']>0){
    $space['fmaxspacesize']=formatsize($space['maxspacesize']);
}elseif($space['maxspacesize']==0){
    $space['fmaxspacesize']=lang('no_limit');
}else{
    $space['fmaxspacesize']=lang('unallocated_space');
}

$profiles['fusersize']=array('title'=>lang('space_usage'),'value'=>$space['fusesize'].' / '.$space['fmaxspacesize']);

if(empty($_G['cache']['profilesetting'])) {
    loadcache('profilesetting');
}
foreach($_G['cache']['profilesetting'] as $fieldid => $field) {
    if(empty($field) || $fieldid=='department' || !$field['available'] || $field['invisible'] || !profile_privacy_check($uid,intval($privacy[$fieldid]))) {
        continue;
    }
    $val = profile_show($fieldid, $space);
    if($val !== false && $val!='') {
        $profiles[$fieldid] = array('title'=>$field['title'], 'value'=>$val);
    }
}

include template('space');
