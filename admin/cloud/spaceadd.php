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
$cloud=$clouds['dzz'];
$navtitle=$cloud['name'].' - '.lang('add_storage_location');
$list=array();
foreach(C::t('connect')->fetch_all_by_available() as $value){
	//if($value['type']!='storage' && $value['type']!='ftp') continue; //限制只有云存储 ftp才能使用
	$list[$value['type']]['list'][]=$value;
	$list[$value['type']]['header'] = lang('cloud_type_' . $value['type']);
}
include template("spaceadd");
?>
