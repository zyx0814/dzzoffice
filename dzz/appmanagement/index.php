<?php
/* @authorcode  codestrings
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      qchlian(3580164@qq.com)
 */
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
$navtitle=lang('后台管理');
//管理权限进入
Hook::listen('adminlogin');
$map=array(
	"isshow"=>1,
	"available"=>1,
	"group"=>3
);
$appdata=C::tp_t('app_market')->where($map)->order("disp asc")->select(); 
$data=array();
foreach($appdata as $k => $v){
	if( $v["identifier"]=="appmanagement") continue;
	if ($v['appico'] != 'dzz/images/default/icodefault.png' && !preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $v['appico'])) {
		$v['appico'] = $_G['setting']['attachurl'] . $v['appico'];
	} 
	$v['url']=replace_canshu($v['appurl']);
	$data[]=$v;
}
include template('main');
 


