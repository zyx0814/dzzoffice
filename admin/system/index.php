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

$operation = $_GET['operation'] ? $_GET['operation'] : 'updatecache';
$url=getglobal('siteurl'). BASESCRIPT . '?mod=system&op=' . $operation;
$url = outputurl($url); 
@header("location: $url");
?>
