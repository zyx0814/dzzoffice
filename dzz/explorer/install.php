<?php
/* @authorcode  codestrings
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
DROP TABLE IF EXISTS dzz_resources_cat;
CREATE TABLE dzz_resources_cat (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  uid int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  catname varchar(30) NOT NULL DEFAULT '',
  ext text NOT NULL,
  tag text NOT NULL,
  keywords text NOT NULL,
  iconview tinyint(1) NOT NULL DEFAULT '1',
  `default` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1,系統默認；0，非系统默认',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

EOF;
runquery($sql);




$finish = true;
