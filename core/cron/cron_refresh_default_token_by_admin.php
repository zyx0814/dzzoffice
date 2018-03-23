<?php
/*
 * 计划任务脚本 获取管理员用户的cuid=1731268900刷新
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
set_time_limit(0);

$data=DB::fetch_first("select * from ".DB::table('connect_pan')." where bz='baiduPCS' and cuid='1731268900' and uid='1'");
unset($data['id']);
unset($data['uid']);
unset($data['dateline']);
$data['cloudname']=lang('baidu_network_disk_test');

DB::update('connect_pan',$data,"bz='baiduPCS' and cuid='1731268900' and uid>1");
?>
