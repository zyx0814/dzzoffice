<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
$navtitle = lang('app_name');
$uid = $_G['uid'];
$space = dzzgetspace($uid);
$space['self']=intval($space['self']);
$data['space']=$space;
$openext = str_replace(array('\''),array('\\\''),json_encode($data));
//用户网盘没有则初始化,存在则检查默认
include libfile('function/explorer');
if(!C::t('folder')->check_home_by_uid($uid)){
   dzz_explorer_init();//初始化网盘
}else{
    check_default_explorer_init();
}
//搜索类型
$catsearch = C::t('resources_cat')->fetch_by_uid($uid);
$explorer_setting = get_resources_some_setting();
if($_G['ismobile']){
    include require MOD_PATH.'/mobile.php';
    exit();
}else{
    require template('index');
}