<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
$path=rawurldecode($_GET['path']);
if(!$path) exit(lang('app_not_support_open_alone'));
$stream=$_G['siteurl'].DZZSCRIPT.'?mod=io&op=getStream&path='.urlencode($path);
header("location: https://docs.google.com/viewer?url=".urlencode($stream));
?>
