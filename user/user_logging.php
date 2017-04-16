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
define('NOROBOT', TRUE);
if (!in_array($_GET['action'], array('login', 'logout'))) {
	$_GET['action'] = 'login';
}
$ctl_obj = new logging_ctl();
$ctl_obj -> setting = $_G['setting'];
$method = 'on_' . $_GET['action'];
$ctl_obj -> template = 'login';
$ctl_obj -> $method();
?>
