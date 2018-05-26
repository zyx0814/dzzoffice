<?php
//获取配置设置值
$explorer_setting = get_resources_some_setting();
$myexplorer = array();
$myorgs = array();
$mygroup = false;
if ($explorer_setting['useronperm']) {
    $myexplorer = C::t('folder')->fetch_home_by_uid();
    $myexplorer['name'] = lang('explorer_user_root_dirname');
    $contains = C::t('resources')->get_contains_by_fid($myexplorer['fid']);
    $myexplorer['filenum'] = $contains['contain'][0];
    $myexplorer['foldernum'] = $contains['contain'][1];
}
if ($explorer_setting['orgonperm']) {
    $orgs = C::t('organization')->fetch_all_orggroup($uid);
    foreach ($orgs['org'] as $v) {
        if(intval($v['aid'])){
            $v['icon']='index.php?mod=io&op=thumbnail&width=24&height=24&path=' . dzzencode('attach::' . $v['aid']);
        }
        $contains =  C::t('resources')->get_contains_by_fid($v['fid']);
        $v['filenum'] = $contains['contain'][0];
        $v['foldernum'] = $contains['contain'][1];
        $myorgs[] = $v;
    }
}
if ($explorer_setting['grouponperm']) {
    $mygroup = true;
}
include template('mobilefileselection/index_content');