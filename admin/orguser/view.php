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
	$org = C::t('organization') -> fetch($orgid);
	if ($org && $org['forgid'] > 0) {
		$toporgid = C::t('organization') -> getTopOrgid($orgid);
		$toporg = C::t('organization') -> fetch($toporgid);
		$folder_available = $toporg['available'];
	} else {
		$folder_available = 1;
	}
	$pmoderator = C::t('organization_admin') -> ismoderator_by_uid_orgid($org['forgid'], $_G['uid']);
	$jobs = C::t('organization_job') -> fetch_all_by_orgid($orgid);
	$moderators = C::t('organization_admin') -> fetch_moderators_by_orgid($orgid);

	include template('detail_org');
}
?>
