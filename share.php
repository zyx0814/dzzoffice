<?php
/* //分享地址支持下载（a=down)，预览(a=view)和流
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

define('APPTYPEID', 200);
require './core/coreBase.php';
$dzz = C::app();
$dzz->init();

if(!$path=dzzdecode(trim($_GET['s']))){
	exit('Access Denied');
}
if($_GET['a']=='down'){
	IO::download($path,$_GET['filename']);
	exit();
}elseif($_GET['a']=='view'){
    $vid = isset($_GET['vid']) ? intval($_GET['vid']):0;
    if($vid){
        if(!$icoarr = C::t('resources_version')->fetch_version_by_rid_vid($path,$vid)){
            showmessage(lang('attachment_nonexistence'));
        }else{
            $path = dzzdecode($icoarr['icoid']);
        }
    }else{
        if(!$icoarr=IO::getMeta($path)){
            showmessage(lang('attachment_nonexistence'));
        }
        $icoarr['icoid'] = $_GET['s'];
    }
    $imageexts=array('jpg','jpeg','png','gif'); //图片使用；
    $filename=$icoarr['name'];//rtrim($_GET['n'],'.dzz');
    $ext=$icoarr['ext'];//strtolower(substr(strrchr($filename, '.'), 1, 10));
    if(!$ext) $ext=preg_replace("/\?.+/i",'',strtolower(substr(strrchr(rtrim($url,'.dzz'), '.'), 1, 10)));
	if(in_array($ext,$imageexts)){
		$url=$_G['siteurl'].'index.php?mod=io&op=thumbnail&original=1&path='.$icoarr['icoid'];
		@header("Location: $url");
		exit();
	}
    $extall=C::t('app_open')->fetch_all_ext();
    $exts=array();
    $bzarr=explode(':',$icoarr['rbz']?$icoarr['rbz']:$icoarr['bz']);
    $bz=($bzarr[0]) ? $bzarr[0]:'dzz';
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
					return $icoarr['icoid'];
				}else if($key=='icoid'){
					return 'preview_'.random(5);
				}else{
					return urlencode($icoarr[$key]);
				}
			}, $url);
			//添加path参数；
			if(strpos($url,'?')!==false  && strpos($url,'path=')===false){
				$url.='&path='.$icoarr['icoid'];
			}
			$url = $_G['siteurl'].$url;
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
