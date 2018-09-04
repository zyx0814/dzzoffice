<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
//此页的调用地址  index.php?mod=test;
//同目录的其他php文件调用  index.php?mod=test&op=test1;

if (!defined('IN_DZZ')) {//所有的php文件必须加上此句，防止被外部调用
	exit('Access Denied');
}
//引入函数文件示例，此例将会调用./function/function_test.php,注意函数文件名的命名规则。
//require  libfile('class/test');
require  libfile('function/test');

//class目录下的类库文件会自动载入
$teststring="我是一个三方类库文件调用测试例子";
$obj = new Chareset();
$teststring=$obj->autocharset( $teststring ,'utf8','utf8');

$testid = !empty($_GET['testid']) ? intval($_GET['testid']) : 0;

//所有参数使用$_GET获取；
$test = array();
$navtitle = lang('title1');
//定义模板的title内容；
if ($testid) $test = C::t('test') -> fetch($testid);
 
//读取一条数据；
/*//如果不在应用内部调用需要改成如下的方式
 $test=C::t('#test#test')->fetch($testid); //#test#为此应用所在的目录
 */
/*如果class/table/table_test.php不存在，也可以使用如下的方法来读取；
 $test=DB::fetch_first("select * from %t where testid = %d ",array('test',$testid));
 */
include  template('index');
//调用./template/test.htm模板；
/*//调用./template/sub/demo.htm 模板,按下面的方式；
include template('sub/demo');
/*
