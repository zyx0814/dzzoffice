<?php
/*
 * 计划任务脚本 定期清理 缓存数据
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
removedir($_G['setting']['attachdir'].'temp/',true);

//清空临时缓存区
$time=60*60*24*7; //7天 七天没有修改的将被删除；
removedir($_G['setting']['attachdir'].'cache/',true,$time);



//清理上传未成功的文件
$like='%dzz_upload_%';
$like1='%FTP_upload_%';
foreach(DB::fetch_all("select * from %t where (cachekey like %s or cachekey like %s) and dateline<%d",array('cache',$like,$like1,TIMESTAMP-24*60*60)) as $value){
	@unlink($_G['setting']['attachdir'].$value['cachevalue']);
	C::t('cache')->delete($value['cachekey']);
}

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
