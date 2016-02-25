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
 function is_utf8($string) { 
     return preg_match('%^(?: 
             [\x09\x0A\x0D\x20-\x7E]                 # ASCII 
         | [\xC2-\xDF][\x80-\xBF]                 # non-overlong 2-byte 
         |     \xE0[\xA0-\xBF][\x80-\xBF]             # excluding overlongs 
         | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}     # straight 3-byte 
         |     \xED[\x80-\x9F][\x80-\xBF]             # excluding surrogates 
         |     \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3 
         | [\xF1-\xF3][\x80-\xBF]{3}             # planes 4-15 
         |     \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16 
     )*$%xs', $string);      
} 
if(!$path=dzzdecode(rawurldecode($_GET['path']))){
	exit('Access Denied');
}

if(!$url=(IO::getStream($path))){
	exit('文件不存在');
}

$filename=rtrim($_GET['n'],'.dzz');
$ext=strtolower(substr(strrchr($filename, '.'), 1, 10));
if(!$ext) $ext=preg_replace("/\?.+/i",'',strtolower(substr(strrchr(rtrim($url,'.dzz'), '.'), 1, 10)));

if($ext=='dzz' || ($ext && in_array($ext,$_G['setting']['unRunExts']))){//如果是本地文件,并且是阻止运行的后缀名时;
	$mime='text/plain';
}else{
	$mime=dzz_mime::get_type($ext);
}
	@header('cache-control:public'); 
	@header('Content-Type: '.$mime);
	@ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
	@readfile($url);
	@flush(); @ob_flush();
	exit();

?>
