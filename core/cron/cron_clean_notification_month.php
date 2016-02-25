<?php
/*
 * 计划任务脚本 清空一个月以上的通知
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

C::t('notification')->delete_clear(0, 30);
C::t('notification')->delete_clear(1, 30);
C::t('notification')->optimize();

?>
