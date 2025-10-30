<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

function build_cache_usergroups() {
    global $_G;
    $data_uf = C::t('usergroup_field')->fetch_all();
    $usergroups = C::t('usergroup')->range_orderby_credit();
    
    $groups = array();
    
    foreach ($usergroups as $gid => $data) {
        $group = array_merge($data, (array)$data_uf[$gid]);
        $group['grouptitle'] = $group['color'] ? '<font color="' . $group['color'] . '">' . $group['grouptitle'] . '</font>' : $group['grouptitle'];
        $group['grouptype'] = $group['type'];
        $group['grouppublic'] = $group['system'] != 'private';
        $group['maxspacesize'] = intval($group['maxspacesize']);
        unset($group['type'], $group['system'], $group['groupavatar'], $group['admingid']);
        
        // 保存单条缓存
        savecache('usergroup_' . $group['groupid'], $group);
        // 处理批量缓存数据
        $groupid = $group['groupid'];
        unset($group['groupid']);
        $groups[$groupid] = $group;
    }
    
    // 保存批量缓存
    savecache('usergroups', $groups);
}