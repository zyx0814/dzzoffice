<?php
/*
 * 计划任务脚本 定期清理缓存缩略图数据
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

//清空临时缓存区
$time=60*60*24*7; //7天 七天没有修改的将被删除；

//清理图片缓存
removedir($_G['setting']['attachdir'].'imgcache/',true,$time);

function removedir($dirname, $keepdir = FALSE ,$time=0) {
	$dirname = str_replace(array( "\n", "\r", '..'), array('', '', ''), $dirname);

	if(!is_dir($dirname)) {
		return FALSE;
	}
	$handle = opendir($dirname);
	while(($file = readdir($handle)) !== FALSE) {
		if($file != '.' && $file != '..') {
			$dir = $dirname . DIRECTORY_SEPARATOR . $file;
			$mtime=filemtime($dir);
			is_dir($dir) ? removedir($dir) : (((TIMESTAMP-$mtime)>$time)? unlink($dir):'');
		}
	}
	closedir($handle);
	return !$keepdir ? (@rmdir($dirname) ? TRUE : FALSE) : TRUE;
}
?>
