<?php
/*
 * 此应用的通知接口
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ')) {
	exit('Access Denied');
}
$appid = intval($_GET['appid']);
$uid = intval($_G['uid']);

$data = array();
$data['timeout'] = 60 * 60;
//一小时查询一次；
//获取应用的提醒数
$lasttime = intval(DB::result_first("select lasttime from " . DB::table('app_user') . " where uid='{$uid}' and appid='{$appid}'"));
$sql = "isshow>0 and dateline>$lasttime and available>0";
//获取用户所在组的应用
if (!$_G['uid']) {//游客
	$sql .= " and (`group`='-1' OR `group`='0')";
} elseif ($_G['adminid'] == 1) {//系统管理员
} elseif ($_G['groupid'] == 2) {//部门管理员

	$l = " (`group` = '1')";
	if ($notappids = C::t('app_organization') -> fetch_notin_appids_by_uid($_G['uid'])) {
		$l .= " and appid  NOT IN (" . dimplode($notappids) . ") ";
	}
	$sql .= " and (`group` = '2' OR `group`='0' OR (" . $l . "))";
} else {//普通成员
	$l = " (`group` = '1')";
	if ($notappids = C::t('app_organization') -> fetch_notin_appids_by_uid($_G['uid'])) {
		$l .= " and appid  NOT IN (" . dimplode($notappids) . ") ";
	}
	$sql .= " and (`group`='0' OR (" . $l . "))";
}
$data['sum'] = (DB::result_first("SELECT COUNT(*) FROM " . DB::table('app_market') . " WHERE  $sql"));
//$data['notice']=array();
echo "noticeCallback(" . json_encode($data) . ")";
exit();
?>
