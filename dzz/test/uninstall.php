<?php
/*
 * 应用卸载程序示例
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {//所有的php文件必须加上此句，防止被外部和非管理员调用
	exit('Access Denied');
}
//提示用户删除的严重程度
if($_GET['confirm']=='DELETE'){
$sql = <<<EOF
DROP TABLE IF EXISTS `dzz_test`;
EOF;
runquery($sql);
$finish = true; //结束时必须加入此句，告诉应用卸载程序已经完成自定义的卸载流程
}else{
header("Location: $confirm_uninstall_url");
exit();
}