<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @version     DzzOffice 1.1 release  2014.7.05
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
$path=dzzdecode($_GET['path']);
$meta=IO::getMeta($path);
$stream=$_G['siteurl'].DZZSCRIPT.'?mod=io&op=getStream&path='.rawurldecode($_GET['path']).'&n='.urlencode($meta['name']);//IO::getFileUri($path);
//转向地址按您的office web app 要求改写；
@header("location: http://view.officeapps.live.com/op/view.aspx?src=".urlencode($stream));
?>
