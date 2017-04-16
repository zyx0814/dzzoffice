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
if (!$_G['uid']) {
	include template('common/header_reload');
	echo "<script type=\"text/javascript\">";
	echo "try{top._login.logging();win.Close();}catch(e){location.href='user.php?mod=logging';}";
	echo "</script>";
	include template('common/footer_reload');
	exit();
}
include_once libfile('function/profile');
include_once libfile('function/organization');
$uid = intval($_GET['uid'] ? $_GET['uid'] : $_G['uid']);

$space = getuserbyuid($uid);
space_merge($space, 'profile1');
space_merge($space, 'field');
space_merge($space, 'status');

$privacy = $space['privacy']['profile'] ? $space['privacy']['profile'] : array();
$_G['setting']['privacy'] = $_G['setting']['privacy'] ? $_G['setting']['privacy'] : array();
$_G['setting']['privacy'] = is_array($_G['setting']['privacy']) ? $_G['setting']['privacy'] : dunserialize($_G['setting']['privacy']);
$_G['setting']['privacy']['profile'] = !empty($_G['setting']['privacy']['profile']) ? $_G['setting']['privacy']['profile'] : array();
$privacy = array_merge($_G['setting']['privacy']['profile'], $privacy);

$space['regdate'] = dgmdate($space['regdate']);

if ($space['lastvisit'])
	$profiles['lastvisit'] = array('title' => lang('last_visit'), 'value' => dgmdate($space['lastvisit']));

$profiles['regdate'] = array('title' => lang('registration_time'), 'value' => $space['regdate']);

$user = array();

$space['fusesize'] = formatsize($space['usesize']);

if (!$_G['cache']['usergroups'])
	loadcache('usergroups');
$usergroup = $_G['cache']['usergroups'][$space['groupid']];
$profiles['usergroup'] = array('title' => lang('usergroup'), 'value' => $usergroup['grouptitle']);
//资料用户所在的部门
$department = '';
foreach (C::t('organization_user')->fetch_orgids_by_uid($uid) as $orgid) {
	$orgpath = getPathByOrgid($orgid);
	$department .= '<span class="label label-primary">' . implode('-', array_reverse($orgpath)) . '</span>';
}
if (empty($department))
	$department = lang('not_join_agency_department');
$profiles['department'] = array('title' => lang('category_department'), 'value' => $department);

if ($usergroup['maxspacesize'] == 0) {
	$space['maxspacesize'] = 0;
} elseif ($usergroup['maxspacesize'] < 0) {
	if (($space['addsize'] + $space['buysize']) > 0) {
		$space['maxspacesize'] = ($space['addsize'] + $space['buysize']) * 1024 * 1024;
	} else {
		$space['maxspacesize'] = -1;
	}
} else {
	$space['maxspacesize'] = ($usergroup['maxspacesize'] + $space['addsize'] + $space['buysize']) * 1024 * 1024;
}
if ($space['maxspacesize'] > 0) {
	$space['fmaxspacesize'] = formatsize($space['maxspacesize']);
} elseif ($space['maxspacesize'] == 0) {
	$space['fmaxspacesize'] = lang('no_limit');
} else {
	$space['fmaxspacesize'] = lang('unallocated_space');
}
//$profiles['fspacesize']=array('title'=>'总空间','value'=>$space['fmaxspacesize']);

$profiles['fusersize'] = array('title' => lang('space_usage'), 'value' => $space['fusesize'] . ' / ' . $space['fmaxspacesize']);
//$space['dateline']=dgmdate($space['dateline']);
//$space['updatetime']=$space['updatetime']?dgmdate($space['updatetime']):'-';
//统计相关信息
/*$count_arr=array('folder','link','image','video','attach');
 foreach($count_arr  as $value){
 $key=lang('message','desktop_sum_'.$value);
 $user['count'][$key]=0;
 //$profiles['count_'.$value]=array('title'=>$key,'value'=>0);
 }*/

/*$query=DB::query("select type from ".DB::table('icos')." where uid='{$space[uid]}' and type IN (".dimplode($count_arr).")");
 while($value=DB::fetch($query)){
 //$profiles['count_'.$value['type']]['value']+=1;
 }
 //$profiles['count_app']=array('title'=>'应用数','value'=>count(explode(',',$space['applist'])));
 */
if (empty($_G['cache']['profilesetting'])) {
	loadcache('profilesetting');
}
foreach ($_G['cache']['profilesetting'] as $fieldid => $field) {
	if (empty($field) || $fieldid == 'department' || !$field['available'] || $field['invisible'] || !profile_privacy_check($uid, intval($privacy[$fieldid]))) {
		continue;
	}
	$val = profile_show($fieldid, $space);
	if ($val !== false && $val != '') {
		$profiles[$fieldid] = array('title' => $field['title'], 'value' => $val);
	}
}

include template('space');
?>
