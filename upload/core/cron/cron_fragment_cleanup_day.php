<?php
/*
 * 计划任务脚本 定期清理阿里云分块上传的碎片
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

$time=60*60*1; 
$limit=100;//每次处理100条
$like='ALIOSS_uploadID_%';
$dkeys=array();
foreach(DB::fetch_all("select * from %t where (cachekey like %s) and dateline<%d LIMIT %d",array('cache',$like,TIMESTAMP-$time,$limit)) as $value){
	$data=unserialize($value['cachevalue']);
	if($data['path']){
		$arr=io_ALIOSS::parsePath($data['path']);
		$io=new io_ALIOSS($arr['bz']);
		$oss=$io->init($arr['bz'],1);
		if(is_array($oss) && $oss['error']) continue;
		$response=$oss->abort_multipart_upload($arr['bucket'], $arr['object'], $arr['upload_id']);
		if($response->isOk()){
			$dkeys[]=$value['cachekey'];
		}
	}else{
		$dkeys[]=$value['cachekey'];
	}
}
if($dkeys) C::t('cache')->delete($dkeys);
?>
