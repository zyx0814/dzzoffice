<?php
/**
* 当前版本: 2.7.570 2021/1/25
* 版本更新: https://github.com/mozilla/pdf.js/releases
* 搜索:	var userOptions = Object.create(null);
* 添加:	userOptions =  window.pdfOptions || userOptions;
 */
if(!defined('IN_DZZ')||!$_GET['path']) {
	exit('Access Denied');
}
$path=dzzdecode($_GET['path']);
$meta=IO::getMeta($path);
if(!$meta) showmessage(lang('file_not_exist'));
if(!perm_check::checkperm('download',$meta)){
	$perm_download=0;
	$perm_print=0;
}else{
	$perm_download=1;
	$perm_print=1;
}
$navtitle = $meta['name'];
include template('viewer');
exit();