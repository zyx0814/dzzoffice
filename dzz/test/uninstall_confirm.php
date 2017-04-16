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
if (!submitcheck('confirmsubmit')) {
	$appid = intval($_GET['appid']);
	if (!$app = C::t('app_market') -> fetch($appid)) {
		exit('Access Denied');
	}
	include  template('uninstall_confirm');
} else {
	if ($_GET['confirm'] == 'DELETE') {
		$url = $_GET['adminurl'] . '&op=cp&confirm=DELETE&do=uninstall&appid=' . intval($_GET['appid']);
		@header("Location: $url");
	} else {
		$url = $_GET['adminurl'] . '&op=list&do=available';
		@header("Location: $url");
	}

}
