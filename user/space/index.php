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
$navtitle = "用户资料";
Hook::listen('check_login');
$uid = intval($_GET['uid'] ?: $_G['uid']);
include_once libfile('function/profile');
$users = dzzgetspace($uid);
$space = C::t('user_profile')->get_user_info_by_uid($uid);
$space['regdate'] = dgmdate($users['regdate']);
$privacy = $space['privacy']['profile'] ?: [];
if ($space['lastvisit']) $profiles['lastvisit'] = ['title' => lang('last_visit'), 'value' => dgmdate($space['lastvisit'])];
if ($users['regip']) {
    $profiles['regdate'] = ['title' => lang('registration_time'), 'value' => $space['regdate']];
} else {
    $profiles['regdate'] = ['title' => lang('add_time'), 'value' => $space['regdate']];
}
$user = [];

if (!$_G['cache']['usergroups']) loadcache('usergroups');
$usergroup = $_G['cache']['usergroups'][$space['groupid']];
$profiles['usergroup'] = ['title' => lang('usergroup'), 'value' => $usergroup['grouptitle']];
//资料用户所在的部门
$department = '';
foreach (C::t('organization_user')->fetch_orgids_by_uid($uid) as $orgid) {
    $orgpath = C::t('organization')->getPathByOrgid($orgid, false);
    $department .= '<span class="badge bg-primary rounded-pill me-2 fs-7">' . implode('-', ($orgpath)) . '</span>';
}
if (empty($department)) $department = lang('not_join_agency_department');
$profiles['department'] = ['title' => lang('category_department'), 'value' => $department];

$profiles['fusersize'] = ['title' => lang('space_usage'), 'value' => $users['fusesize'] . ' / ' . $users['fmaxspacesize']];

if (empty($_G['cache']['profilesetting'])) {
    loadcache('profilesetting');
}
foreach ($_G['cache']['profilesetting'] as $fieldid => $field) {
    if (empty($field) || $fieldid == 'department' || !$field['available'] || $field['invisible'] || !profile_privacy_check($uid, intval($privacy[$fieldid]))) {
        continue;
    }
    $val = profile_show($fieldid, $space);
    if ($val !== false && $val != '') {
        $profiles[$fieldid] = ['title' => $field['title'], 'value' => $val];
    }
}

include template('space');
