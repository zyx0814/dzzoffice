<?php
/*
 * 计划任务脚本 每周获取检查百度网盘的token，过期时间小于1周的全部刷新
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
foreach(DB::fetch_all("select * from %t where copys<1 ORDER BY dateline limit $limit",array('attachment',$limit)) as $value){
	if(io_remote::DeleteFromSpace($value)){
		C::t('attachment')->delete($value['aid']);
	}
}
?>
