<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @version     DzzOffice 1.1
 * @link        http://www.dzzoffice.com
 * @author      qchlian(3580164@qq.com)
 */
if (!defined('IN_DZZ')) {
	exit('Access Denied');
}
//管理权限进入
Hook::listen('adminlogin');

$page = empty($_GET['page'])?1:intval($_GET['page']);
$perpage=10;
$start=($page-1)*$perpage;
$count=C::tp_t('vote')->count();

$list=array();
$multi="";
if($count){
	$list=C::tp_t('vote')->limit($start,$perpage)->select();
	$theurl = DZZSCRIPT."?".url_implode($gets);
	$refer=urlencode($theurl.'&page='.$page);
	$multi=multi($count, $perpage, $page, $theurl,'pull-center');
}
include template('index');
?>
