<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}
$operation='setting';

if(submitcheck('addcloudsubmit')){
	$_GET=dhtmlspecialchars($_GET);
	$error=array();
	if(empty($_GET['name'])){
		$error[] = lang('name_cannot_empty');
	}
	if(empty($_GET['bz'])){
		$error[] = lang('identifier_can_not_empty');
	}
	if(DB::result_first("select COUNT(*) from ".DB::table('connect')." where bz='{$_GET[bz]}'")){
		$error[] = lang('designator') . $_GET['bz'] . lang('already_existed');
	}
	if ($error)
		showmessage(implode('<br>', $error), dreferer());
	if($_GET['type']=='pan'){
		$setarr = array('name' => $_GET['name'], 'root' => trim($_GET['root']), 'key' => trim($_GET['key']), 'secret' => trim($_GET['secret']));

	}elseif($_GET['type']=='storage'){
		$setarr = array('name' => $_GET['name']);
	}elseif($_GET['type']=='ftp'){
		$setarr = array('name' => $_GET['name']);
	}
	$setarr['bz']=$_GET['bz'];
	$setarr['dname']=$_GET['dname'];
	$setarr['type']=$_GET['type'];
	$setarr['available']=0;
	C::t('connect')->insert($setarr);
	
	showmessage('do_success',BASESCRIPT.'?mod=cloud');
}
include template('add');
?>
