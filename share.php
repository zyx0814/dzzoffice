<?php
/* //分享地址支持下载（a=down)，预览(a=view)和流
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

define('APPTYPEID', 200);
require './core/class/class_core.php';
require './dzz/function/dzz_core.php';
$dzz = C::app();
$dzz->init();
if(!$path=dzzdecode(trim($_GET['s']))){
	exit('Access Denied');
}
if($_GET['a']=='down'){
	IO::download($path,$_GET['filename']);
	exit();
}elseif($_GET['a']=='view'){
	if(!$icoarr=IO::getMeta($path)){
		showmessage(lang('attachment_nonexistence'));
	}
	if($icoarr['type']=='video'){
		@header("Location: $icoarr[url]");
		exit();
	}
	$imageexts=array('jpg','jpeg','png','gif'); //图片使用；
	$filename=$icoarr['name'];//rtrim($_GET['n'],'.dzz');
	$ext=$icoarr['ext'];//strtolower(substr(strrchr($filename, '.'), 1, 10));
	if(!$ext) $ext=preg_replace("/\?.+/i",'',strtolower(substr(strrchr(rtrim($url,'.dzz'), '.'), 1, 10)));
	if(in_array($ext,$imageexts)){
		$url=$_G['siteurl'].'index.php?mod=io&op=thumbnail&original=1&path='.$_GET['s'];
		@header("Location: $url");
		exit();
	}elseif($ext=='mp3'){
		$url=$_G['siteurl'].'index.php?mod=sound&path='.$_GET['s'];
		@header("Location: $url");
		exit();
	}elseif($icoarr['type']=='dzzdoc'){
		$url=$_G['siteurl'].'index.php?mod=document&icoid='.$_GET['s'];
		@header("Location: $url");
		exit();
	}
	$bzarr=explode(':',$icoarr['rbz']?$icoarr['rbz']:$icoarr['bz']);
	$bz=$bzarr[0];
	$extall=C::t('app_open')->fetch_all_ext();
	$exts=array();
	foreach($extall as $value){
		if(!isset($exts[$value['ext']]) || $value['isdefault']) $exts[$value['ext']]=$value;
	}
	
	if(isset($exts[$bz.':'.$ext])){
		$data=$exts[$bz.':'.$ext];
	}elseif($exts[$ext]){
		$data=$exts[$ext];
	}elseif($exts[$icoarr['type']]){
		$data=$exts[$icoarr['type']];
	}else $data=array();
	if($data){
		$url=$data['url'];
		if(strpos($url,'dzzjs:')!==false){//dzzjs形式时
			@header("Location: $icoarr[url]");
			 exit();
		}else{
			//替换参数
			$url=preg_replace_callback("/{(\w+)}/i", function($matches) use($icoarr){
				$key=$matches[1];
				if($key=='path'){
					return $icoarr['dpath'];
				}else if($key=='icoid'){
					return 'preview_'.random(5);
				}else{
					return urlencode($icoarr[$key]);
				}
			}, $url);
					
			//添加path参数；
			if(strpos($url,'?')!==false  && strpos($url,'path=')===false){
				$url.='&path='.$_GET['s'];
			}
			@header("Location: $url");
			exit();
		}
		
	}else{//没有可用的打开方式，转入下载；
		IO::download($path);
		exit();
	}
	
}
//获取文件流地址
if(!$url=(IO::getStream($path))){
	exit(lang('failed_get_file'));
}
if(is_array($url)) exit($url['error']);

//如果是阻止运行的后缀名时，直接调用;
if($ext && in_array($ext,$_G['setting']['unRunExts'])){
	$mime='text/plain';
}else{
	$mime=dzz_mime::get_type($ext);
}
@set_time_limit(0);
@header('Content-Type: '.$mime);
@ob_end_clean();
@readfile($url);
@flush(); 
@ob_flush();
exit();
?>
