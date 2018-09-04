<?php
/*
 * //应用安装文件示例；
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}

$sql = <<<EOF
DROP TABLE IF EXISTS dzz_test;
CREATE TABLE IF NOT EXISTS `dzz_test` (
  `testid` int(10) NOT NULL AUTO_INCREMENT,
  `name` char(30) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`testid`),
  KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

EOF;
runquery($sql);

$finish = true;  //结束时必须加入此句，告诉应用安装程序已经完成自定义的安装流程
