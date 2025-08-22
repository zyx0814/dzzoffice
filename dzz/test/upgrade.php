<?php
/* @应用升级脚本，当配置文件中的版本大于当前应用的版本，会提示升级，升级时会调用此脚本
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {//所有的php文件必须加上此句，防止被外部和非管理员调用
	exit('Access Denied');
}
$finish = true;//结束时必须加入此句，告诉应用升级程序已经完成自定义的升级流程