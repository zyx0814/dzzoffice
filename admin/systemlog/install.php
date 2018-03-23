<?php
/*
 * 应用安装文件；
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      qchlian
 */
if(!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}
/*以下配置初始化时已存入数据库配置表
	*$systemlog_setting = array(
	*  级别                 名称                标记            是否开启
	   "errorlog"=>array("title"=>"系统错误","is_open"=>1,"issystem"=>1),	  
	   "cplog"=>array("title"=>"后台访问","is_open"=>1,"issystem"=>1),	  
	   "deletelog"=>array("title"=>"数据删除","is_open"=>1,"issystem"=>1),	  
	   "updatelog"=>array("title"=>"数据更新","is_open"=>1,"issystem"=>1),
	   "loginlog"=>array("title"=>"用户登录","is_open"=>1,"issystem"=>1),
	   "sendmail"=>array("title"=>"邮件发送","is_open"=>1,"issystem"=>1),
	   "otherlog"=>array("title"=>"其他信息","is_open"=>1,"issystem"=>1), 
	);
*/

$sql = <<<EOF
DELETE FROM `dzz_setting` WHERE `skey` = 'systemlog_open';
INSERT INTO `dzz_setting` (`skey`,`svalue`) VALUES ('systemlog_open', '1');
DELETE FROM `dzz_setting` WHERE `skey` = 'systemlog_setting';
INSERT INTO `dzz_setting` (`skey`,`svalue`) VALUES ('systemlog_setting','a:7:{s:8:"errorlog";a:3:{s:5:"title";s:12:"系统错误";s:7:"is_open";i:1;s:8:"issystem";i:1;}s:5:"cplog";a:3:{s:5:"title";s:12:"后台访问";s:7:"is_open";i:1;s:8:"issystem";i:1;}s:9:"deletelog";a:3:{s:5:"title";s:12:"数据删除";s:7:"is_open";i:1;s:8:"issystem";i:1;}s:9:"updatelog";a:3:{s:5:"title";s:12:"数据更新";s:7:"is_open";i:1;s:8:"issystem";i:1;}s:8:"loginlog";a:3:{s:5:"title";s:12:"用户登录";s:7:"is_open";i:1;s:8:"issystem";i:1;}s:8:"sendmail";a:3:{s:5:"title";s:12:"邮件发送";s:7:"is_open";i:1;s:8:"issystem";i:1;}s:8:"otherlog";a:3:{s:5:"title";s:12:"其他信息";s:7:"is_open";i:1;s:8:"issystem";i:1;}}');
EOF;
runquery($sql);
$finish = true;  //结束时必须加入此句，告诉应用安装程序已经完成自定义的安装流程