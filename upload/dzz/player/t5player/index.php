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
define('APP_DIR','dzz/player/t5player/');
$path=dzzdecode($_GET['path']);
$io=new io_baiduPCS($path);
if($_GET['do']=='ajax'){
	$update=1;
	if($content=$io->getM3U8Uri($path)){
		$target='cache/'.md5($path).'.m3u8';
		if(strlen($content)>filesize($_G['setting']['attachdir'].$target)){
			file_put_contents($_G['setting']['attachdir'].$target,$content);
			$update=1;
		}elseif(strlen($content)==filesize($_G['setting']['attachdir'].$target)){
			$update=2;
		}else{
			$update=0;
		}
	}
	 echo json_encode(array('update'=>$update,'target'=>$target));
	 exit();
}else{
	
	$ajaxurl=DZZSCRIPT.'?mod=player:t5player&path='.dzzencode($path).'&do=ajax';
	$target='cache/'.md5($path).'.m3u8';
	if(!is_file($_G['setting']['attachdir'].$target)){
		if($content=$io->getM3U8Uri($path)){
			file_put_contents($_G['setting']['attachdir'].$target,$content);
		}
	}
	$url=$_G['setting']['attachurl'].$target;
	$cloud=C::t('connect')->fetch('baiduPCS');
	$ak=$cloud['key'];
	$sk=substr($cloud['secret'],0,16);
	include  template('t5player');
}
?>
