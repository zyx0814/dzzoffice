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
$path=dzzdecode($_GET['path']);
$patharr=explode(':',$path);
if($patharr[0]=='ftp'){
	$stream=$_G['siteurl'].DZZSCRIPT.'?mod=io&op=getStream&path='.rawurldecode($_GET['path']);
}else{
	$stream=IO::getFileUri($path);
	$stream=str_replace('-internal.aliyuncs.com','.aliyuncs.com',$stream);
}

//转向地址按您的office web app 要求改写；
header("location: http://view.officeapps.live.com/op/view.aspx?src=".urlencode($stream));
?>
