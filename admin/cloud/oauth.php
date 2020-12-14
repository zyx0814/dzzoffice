<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */
if(!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}
$clouds=DB::fetch_all("select * from ".DB::table('connect')." where 1 order by disp",array(),'bz');
$bz=$_GET['bz'];
$navtitle=lang('add_storage_location').' - '.lang('space_management');
if($_GET['do']=='getBucket'){
	$id=$_GET['id'];
	$key=$_GET['key'];
	$bz=empty($_GET['bz'])?'ALIOSS':$_GET['bz'];
    $class='io_'.$bz;

    $p=new $class($bz);
	if($bz == 'QCOS'){
		$region = $_GET['region'];
        $re=$p->getBucketList($id,$key,$region);
	}else{
        $re=$p->getBucketList($id,$key);
	}
	if($re){
		echo  json_encode($re);
	}else{
		echo  json_encode(array());
	}
	exit();
}else{
	//error_reporting(E_ALL);
	IO::authorize($bz);
	exit();
}
?>
