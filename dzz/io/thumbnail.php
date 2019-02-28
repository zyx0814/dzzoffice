<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
$path=dzzdecode(urldecode($_GET['path']));
$width=intval($_GET['width']);
$height=intval($_GET['height']);
$size=trim($_GET['size']);
$thumbtype=$_GET['thumbtype']?intval($_GET['thumbtype']):'1';
$size=in_array($size,array_keys($_G['setting']['thumbsize']))?$size:'large';
$original=intval($_GET['original']);
if(!$width) $width=$_G['setting']['thumbsize'][$size]['width'];
if(!$height) $height=$_G['setting']['thumbsize'][$size]['height'];
$download=trim($_GET['a']);
if($download=='down'){
	if(!$filename=urldecode($_GET['filename'])){
		$meta=IO::getMeta($path);
		$filename=$meta['name'];
		$filesize=$meta['size'];
	}
	$imgurl=IO::getThumb($path,$width,$height,$original,true,$thumbtype);
	$filesize=filesize($imgurl);
	$filename = '"'.(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($filename) : $filename).'"';
	$db = DB::object();
	$db->close();
	$chunk = 10 * 1024 * 1024; 
	if(!$fp = @fopen($imgurl, 'rb')) {
		exit(lang('attachment_nonexistence'));
	}
	dheader('Date: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
	dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
	dheader('Content-Encoding: none');
	dheader('Content-Disposition: attachment; filename='.$filename);
	dheader('Content-Type: application/octet-stream');
	dheader('Content-Length: '.$filesize);
	@ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
	while (!feof($fp)) { 
		echo fread($fp, $chunk);
		@ob_flush();  // flush output
		@flush();
	}
	exit();
	
}else{
	IO::getThumb($path,$width,$height,$original,false,$thumbtype);
}
if($original){
	if($returnurl) return $_G['setting']['attachurl'].'./'.$data['attachment'];
	$file=$_G['setting']['attachdir'].'./'.$data['attachment'];
	$last_modified_time = filemtime($file); 
	$etag = md5_file($file); 
	header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT"); 
	header("Etag: $etag"); 
	
	if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time || 
		trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) { 
		header("HTTP/1.1 304 Not Modified"); 
		exit; 
	}
	@header('cache-control:public');  
	@header("Content-Type: " . image_type_to_mime_type($imginfo[2]));
	@ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
	@readfile($_G['setting']['attachdir'].'./'.$data['attachment']);
	@flush(); @ob_flush();
	exit();
}
?>
