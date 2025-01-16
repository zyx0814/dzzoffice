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

if(!$path=dzzdecode(rawurldecode($_GET['path']))){
	@header('HTTP/1.1 404 Not Found');
	@header('Status: 404 Not Found');
	exit('Access Denied');
}
if(!$url=(IO::getStream($path))){
	@header('HTTP/1.1 403 Not Found');
	@header('Status: 403 Not Found');
	exit(lang('attachment_nonexistence'));
}
if (is_array($url) && isset($url['error'])) {
	@header('HTTP/1.1 403 Not Found');
    @header('Status: 403 Not Found');
    exit($url['error']);
}
$filename = trim($_GET['n'], '.dzz') ?: $_GET['filename'];
// 检查是否包含 Unicode 编码的字符
if (preg_match('/\\\\u[0-9a-fA-F]{4}/', $filename)) {
    // 将 Unicode 编码转换为 UTF-8
    $filename = json_decode('"' . $filename . '"');
}
$ext=strtolower(substr(strrchr($filename, '.'), 1, 10));
if(!$ext) $ext=strtolower(substr(strrchr(preg_replace("/\.dzz$/i",'',preg_replace("/\?.*/i",'',$url)), '.'), 1, 10));
if($ext=='dzz' || ($ext && in_array($ext,$_G['setting']['unRunExts']))){//如果是本地文件,并且是阻止运行的后缀名时;
	$mime='text/plain';
}else{
	$mime=dzz_mime::get_type($ext);
}
@header('Content-Disposition: inline; filename="' . $filename . '"');
@header('cache-control:public'); 
@header('Content-Type: '.$mime);
@ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
@readfile($url);
@flush(); @ob_flush();
exit();
?>