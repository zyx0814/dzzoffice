<?php
/*
 * 应用卸载程序示例
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}

//如果有其他非数据库数据，这里添加删除语句先删除

$sql = <<<EOF

DROP TABLE IF EXISTS `dzz_test`;

EOF;

runquery($sql);

$finish = true; //结束时必须加入此句，告诉应用卸载程序已经完成自定义的卸载流程
