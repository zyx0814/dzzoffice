<?php
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

function build_cache_organization() {
	global $_G;
	set_time_limit(0);
	@ini_set("memory_limit","512M");
	include_once libfile('function/organization');
	$data=array();
	/*$query=DB::query("select * from ".DB::table('organization')." where 1 limit 1000");
	while($value=DB::fetch($query)){
		//获取此机构下的用户；
		$value['uids']=C::t('organization_user')->fetch_uids_by_orgid($value['orgid']);//获取部门所有用户（不包括下级）
		$value['moderators']=C::t('organization_admin')->fetch_moderators_by_orgid($value['orgid']);
		$data[$value['orgid']]=$value;
	}*/
	savecache('organization', $data);
}

?>
