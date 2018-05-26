<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
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
	$re=$p->getBucketList($id,$key);

	if($re){
		echo  json_encode($re);
	}else{
		echo  json_encode(array());
	}
	exit();
}else{
	IO::authorize($bz);
	exit();
}
?>
