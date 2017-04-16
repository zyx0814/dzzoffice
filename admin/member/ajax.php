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
$do = trim($_GET['do']);
if ($do == 'getjobs') {
	$orgid = intval($_GET['orgid']);
	$jobs = C::t('organization_job') -> fetch_all_by_orgid($orgid);
	$html = '<li role="presentation"><a href="javascript:;" tabindex="-1" role="menuitem" _jobid="0" onclick="selJob(this)">'.lang('none').'</a></li>';
	foreach ($jobs as $job) {
		$html .= '<li role="presentation"><a href="javascript:;" tabindex="-1" role="menuitem" _jobid="' . $job['jobid'] . '" onclick="selJob(this)">' . $job['name'] . '</a></li>';
	}
	exit($html);
} elseif ($do == 'deleteuser') {
	$uid = intval($_GET['uid']);
	if ($_G['adminid'] != 1)
		exit(json_encode(array('error' => lang('no_privilege'))));

	C::t('user') -> delete_by_uid($uid);
	exit(json_encode(array('msg' => 'success')));
}
?>
