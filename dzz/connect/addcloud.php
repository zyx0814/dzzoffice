<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if (!defined('IN_DZZ')) {
	exit('Access Denied');
}
if (!$_G['uid']) {
	include  template('common/header_reload');
	echo "<script type=\"text/javascript\">";
	echo "try{top._login.logging();}catch(e){}";
	echo "try{win.Close();}catch(e){}";
	echo "</script>";
	include template('common/footer_reload');
	exit();
}
$cloud = array();
$list = C::t('connect') -> fetch_all_by_available(true);
foreach ($list as $value) {
	$cloud[$value['type']]['list'][] = $value;
	$cloud[$value['type']]['header'] = lang('cloud_type_' . $value['type']);
}
include template("addcloud");
?>
