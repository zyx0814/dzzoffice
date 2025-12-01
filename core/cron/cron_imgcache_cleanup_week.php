<?php
/*
 * 计划任务脚本 定期清理缓存缩略图数据
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

//清空临时缓存区
$time = 60 * 60 * 24 * 7; //7天 七天没有修改的将被删除；

//清理图片缓存
removedirectory($_G['setting']['attachdir'] . 'imgcache/', true, $time);
?>
