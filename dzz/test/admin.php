<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
//此页的调用地址  index.php?mod=test&op=admin;
//同目录的其他php文件调用  index.php?mod=test&op=test1;

if (!defined('IN_DZZ')) {//所有的php文件必须加上此句，防止被外部调用
	exit('Access Denied');
}
include_once libfile('function/cache');//系统缓存
require  libfile('function/test');
//引入函数文件示例，此例将会调用./function/function_test.php,注意函数文件名的命名规则。
Hook::listen('adminlogin');//管理员登录验证 钩子
$op = isset($_GET['op'])?$_GET['op']:'admin';//默认菜单的选择
if ( submitcheck('settingsubmit')) { 
	$settingnew = $_GET['settingnew']; 
	$settingnew=array(
		"test_setting"=>$settingnew["test_setting"], 
	);
	
	$result = C::t('setting') -> update_batch($settingnew);
	updatecache('setting');//更新setting缓存
	showmessage('do_success', dreferer());
}
else{
	$setting = C::t('setting') -> fetch_all(null);
}

include  template('admin');
//调用./template/admin.htm模板；
/*//调用./template/sub/admin.htm 模板,按下面的方式；
include template('sub/admin');
/*
