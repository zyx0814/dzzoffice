<?php
/*
 * 应用卸载文件； 
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      qchlian
 */ 
if(!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}  
$sql = <<<EOF
DELETE FROM `dzz_setting` WHERE `dzz_setting`.`skey` = 'systemlog_setting';
DELETE FROM `dzz_setting` WHERE `dzz_setting`.`skey` = 'systemlog_open';
EOF;
runquery($sql);
$finish = true;
