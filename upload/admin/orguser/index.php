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
require_once  libfile('function/organization');
$orgtree = array();
if ($_G['adminid'] != 1) {
	//获取用户的有权限的部门树
	$orgids = C::t('organization_admin') -> fetch_orgids_by_uid($_G['uid'], 0);
	foreach ($orgids as $orgid) {
		$arr = getUpOrgidTree($orgid, true);
		$arr = array_reverse($arr);
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
