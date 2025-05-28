<?php
/* @authorcode  codestrings
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {
    exit('Access Denied');
}

//卸载网盘程序；

$sql = <<<EOF

DROP TABLE IF EXISTS `dzz_resources_cat`;
DELETE FROM `dzz_setting` where `skey` = 'explorer_usermemoryOn';
DELETE FROM `dzz_setting` where `skey` = 'explorer_mermoryusersetting';
DELETE FROM `dzz_setting` where `skey` = 'explorer_memoryorgusers';
DELETE FROM `dzz_setting` where `skey` = 'explorer_organizationOn';
DELETE FROM `dzz_setting` where `skey` = 'explorer_groupOn';
EOF;

runquery($sql);

$finish = true;
