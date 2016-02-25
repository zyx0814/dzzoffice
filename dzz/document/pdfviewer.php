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

if($ismobile=helper_browser::ismobile()){
	$path=dzzdecode($_GET['path']);	
	IO::download($path);
	exit();
}
if($_GET['ok']){
	$path=dzzdecode($_GET['path']);	
	$url=IO::getStream($path,'odconv/pdf');
	@header('Content-Type:application/pdf');
	@ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
	@readfile($url);
	@flush(); @ob_flush();
	exit();
}else{
	$url=DZZSCRIPT.'?mod=document&op=pdfviewer&path='.($_GET['path']).'&ok=1';
	include template('pdfviewer');
}
?>
