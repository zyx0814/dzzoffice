<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}

include_once  libfile('function/organization');
$type = trim($_GET['idtype']);
if ($type == 'user') {
	$_GET['uid'] = intval($_GET['id']);
	include DZZ_ROOT . './admin/orguser/edituser.php';
	exit();
} else {
	$orgid = intval($_GET['id']);
	if (!C::t('organization_admin') -> ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
		exit(lang('orguser_vidw_delete'));
	}
	if($org = C::t('organization') -> fetch($orgid)){
		$org['avatar'] = avatar_group($org['orgid'],array($org['orgid']=>array('aid'=>$org['aid'],'orgname'=>$org['orgname'])));
	}
	if ($org && $org['forgid'] > 0) {
		$toporgid = C::t('organization') -> getTopOrgid($orgid);
		$toporg = C::t('organization') -> fetch($toporgid);
		$folder_available = $toporg['available'];
		$group_on = $toporg['syatemon'];
	} else {
		$folder_available = 1;
		$group_on = 1;
	}

	//可分配空间
	$allowallotspace = C::t('organization')->get_allowallotspacesize_by_orgid($orgid);
	//获取已使用空间
	$org['usesize'] = C::t('organization')->get_orgallotspace_by_orgid($orgid,0,false);
	/*echo formatsize($org['usesize']);
	die;*/
	//获取总空间
	if($org['maxspacesize'] == 0){
		$maxspacesize = C::t('organization')->get_parent_maxspacesize_by_pathkey($org['pathkey'],$orgid);
		$org['maxallotspacesize'] = $maxspacesize['maxspacesize'];
	}else{
		if($org['maxspacesize'] == -1){
			$org['maxallotspacesize'] = -1;
		}else{
			$org['maxallotspacesize'] = $org['maxspacesize']*1024*1024;
		}

	}
	$pmoderator = C::t('organization_admin') -> ismoderator_by_uid_orgid($org['forgid'], $_G['uid']);
	$jobs = C::t('organization_job') -> fetch_all_by_orgid($orgid);
	$moderators = C::t('organization_admin') -> fetch_moderators_by_orgid($orgid);
	//$grouppic= C::t('resources_grouppic')->fetch_user_pic();
	include template('detail_org');
}
?>
