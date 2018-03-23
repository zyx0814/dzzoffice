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
$navtitle= lang('appname');
$orgtree = array();
if ($_G['adminid'] != 1) {
	//获取用户的有权限的部门树
	$orgids = C::t('organization_admin') -> fetch_orgids_by_uid($_G['uid']);
	foreach ($orgids as $orgid) {
		$arr = C::t('organization')->fetch_parent_by_orgid($orgid, true);
		$count = count($arr);
		if ($orgtree[$arr[$count - 1]]) {
			if (count($orgtree[$arr[$count - 1]]) > $count)
				$orgtree[$arr[count($arr) - 1]] = $arr;
		} else {
			$orgtree[$arr[$count - 1]] = $arr;
		}
	}
}
$orgtree = json_encode($orgtree);
include template('main');
?>
