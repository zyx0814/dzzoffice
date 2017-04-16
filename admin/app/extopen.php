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

if (submitcheck('appsubmit')) {

	$allids = array();
	foreach ($_GET['disp'] as $key => $value) {
		if ($_GET['isdefault'][$key]) {
			C::t('app_open') -> setDefault($key);
		}
		C::t('app_open') -> update($key, array('disp' => $value, 'isdefault' => intval($_GET['isdefault'][$key])));
	}

	showmessage('do_success', dreferer());
}

$ext = trim($_GET['ext']);
$appid = intval($_GET['appid']);
$orderby = trim($_GET['s']);
$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
$perpage = 20;
$gets = array('mod' => 'app', 'op' => 'extopen', 'ext' => $ext, 'appid' => $appid);
$theurl = BASESCRIPT . "?" . url_implode($gets);
$refer = urlencode($theurl . '&page=' . $page);
if ($orderby)
	$order = 'ORDER BY ' . $orderby;
else
	$order = 'order by disp DESC';
$start = ($page - 1) * $perpage;
$apps = array();

$sql = '1';
if ($appid) {
	$sql .= " and `appid` = '{$appid}'";
} elseif ($ext) {
	$sql .= " and `ext` = '{$ext}'";
}

if ($count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('app_open') . " WHERE  $sql")) {
	$extlist = DB::fetch_all("SELECT * FROM " . DB::table('app_open') . " WHERE  $sql $order limit $start,$perpage");
	$multi = multi($count, $perpage, $page, $theurl, 'pull-right');
}
$appids = array();
foreach ($extlist as $value) {
	$appids[] = $value['appid'];
}
$appdatas = C::t('app_market') -> fetch_all($appids);
$list = array();
foreach ($extlist as $value) {
	if (!$appdatas[$value['appid']])
		continue;
	$value = array_merge($value, $appdatas[$value['appid']]);

	if ($value['appico'] != 'dzz/images/default/icodefault.png' && !preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $value['appico'])) {
		$value['appico'] = $_G['setting']['attachurl'] . $value['appico'];
	}
	$value['appurl'] = replace_canshu($value['appurl']);
	$list[] = $value;
}

include template('extopen');
?>
