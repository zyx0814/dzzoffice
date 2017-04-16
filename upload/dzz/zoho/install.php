<?php
/* @authorcode  codestrings
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}
if (empty($_GET['app_key'])) {
	$url = 'index.php?mod=zoho&op=appkey&adminurl=' . urlencode($request_uri);
	header("Location: $url");
	exit();

} else {
	$apparray['app']['extra']['ZohoAPIKey'] = $_GET['app_key'];
	$finish = true;
}
