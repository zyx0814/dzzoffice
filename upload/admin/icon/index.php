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
$do = trim($_GET['do']);

$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
$perpage = 50;
$domain = trim($_GET['domain']);
$gets = array('mod' => 'icon', 'domain' => $domain, );
$theurl = BASESCRIPT . "?" . url_implode($gets);
$start = ($page - 1) * $perpage;
$list = array();
$sql = '';
$param = array('icon');
if ($domain) {
	$sql .= " and domain like %s";
	$param[] = "%" . $domain . "%";
}
if ($do == 'getMore') {
	if ($count = DB::result_first("SELECT COUNT(*) FROM %t  WHERE 1 $sql", $param)) {
		$list = DB::fetch_all("SELECT * FROM %t WHERE 1 $sql order by dateline DESC limit $start,$perpage", $param);
	}
	$next = false;
	if ($count && $count > $perpage * $page)
		$next = true;
	include  template('icon_item');
} else {
	if ($count = DB::result_first("SELECT COUNT(*) FROM %t  WHERE 1 $sql", $param)) {
		$list = DB::fetch_all("SELECT * FROM %t WHERE 1 $sql order by dateline DESC limit $start,$perpage", $param);
	}
	$next = false;
	if ($count && $count > $perpage * $page)
		$next = true;

	include template('main');
}
?>
