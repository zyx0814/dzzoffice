<?php
/*
 * 计划任务脚本
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
 
//按月清除未用附件（copys<=0)
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
$limit=100;//考虑到计划任务占用的系统资源，一次最大删除100个;
$delay=TIMESTAMP-60*60*1;//仅删除1小时以前的未用附件；
foreach(DB::fetch_all("select * from %t where dateline<%d and copys<1 ORDER BY dateline limit $limit",array('attachment',$delay,$limit)) as $value){
	if(io_remote::DeleteFromSpace($value)){
		C::t('attachment')->delete($value['aid']);
	}
}