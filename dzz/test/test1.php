<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
 
 if(!defined('IN_DZZ')) { //所有的php文件必须加上此句，防止被外部调用
	exit('Access Denied');
}
require libfile('function/test');//引入函数文件示例，此例将会调用./function/function_test.php,注意函数文件名的命名规则。
require libfile('class/test');//引入类文件示例，此例将会调用./class/class_test.php,注意类文件名的命名规则。

$testid=!empty($_GET['testid'])?intval($_GET['testid']):0;//所有参数使用$_GET获取；
$test=array();
if($testid) $test=C::t('test')->fetch($testid); //读取一条数据；
include template('test');//调用./template/test.htm模板；
