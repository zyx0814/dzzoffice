<?php
/*
 * 应用卸载程序示例
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
if (!submitcheck('confirmsubmit')) {

	include template('appkey');
} else {
	if ($_GET['app_key'] && preg_match("/\w{32}$/", $_GET['app_key'])) {
		$url = $_GET['adminurl'] . '&op=cp&app_key=' . $_GET['app_key'] . '&do=install&dir=zoho';
		@header("Location: $url");
	} else {
		showmessage('ZohoAPIKey_cannot_install', $_GET['adminurl']);
	}
	exit();
}
