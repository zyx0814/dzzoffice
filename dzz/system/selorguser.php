<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
	
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
include_once libfile('function/organization');
$ismobile=helper_browser::ismobile();
$ids =isset($_GET['ids'])?rawurldecode($_GET['ids']):'';

$zero=$_GET['zero']?urldecode($_GET['zero']):lang('no_institution_users');//无机构用户名称
$nouser=intval($_GET['nouser']);//不显示用户
$stype=intval($_GET['stype']); //0:可以选择机构和用户；1：仅选择机构和部门：2：仅选择用户
$moderator=intval($_GET['moderator']);//是否仅可以选择我管理的群组或部门
$range=intval($_GET['range']);//0：所有部门和群组；1：仅部门；2：仅群组
$multiple=intval($_GET['multiple']); //是否允许多线
$showjob=intval($_GET['showjob']); //是否显示职位
$callback=$_GET['callback']?$_GET['callback']:'callback_selectuser';//回调函数名称
$callback_url = isset($_GET['callback_url']) ? trim($_GET['callback_url']):'';
$deferer = dreferer();
$token=htmlspecialchars($_GET['token']);
$gets = array(
		'zero'=>$zero,
		'nouser'=>$nouser,
		'stype'=>$stype,
		'moderator'=>$moderator,
		'range'=>$range,
		'multiple'=>$multiple,
		'nosearch'=>1,
		'showjob'=>$showjob,
		'ctrlid'=>'seluser',
		'callback_url'=>$callback_url
	);
$theurl = MOD_URL."&op=orgtree&".url_implode($gets);
$ids=explode(',',$ids);
//规整默认值  g_开头的为群组，纯数字的为uid
$orgids=array();
$uids=array();

foreach($ids as  $value){
	if(strpos($value,'g_')!==false){
		if($stype==2) continue;//仅选择用户时，忽略部门和群组
		$orgid=intval(str_replace($value,'g_',''));
		$orgids[$orgid]=$orgid;
		
	}elseif($uid=intval($value)){
		if($stype==1) continue; //仅选择部门和群组时，忽略用户；
		$uids[$uid]=$uid;
		
	}
}
$selects=array();//已选数组
//组装openarr
$open = array();//默认打开的
if($orgids && $stype!=2){
	$sel_org=C::t('organization')->fetch_all($orgids);
	foreach($sel_org as $key=> $value){
		$orgpath=getPathByOrgid($value['orgid']);
		$value['orgname']=implode('-',($orgpath));
		$selects[$key]=$value;
	}
	$arr=(array_keys($orgpath));

	$count = count($arr);
	if ($open[$arr[$count - 1]]) {
		if (count($open[$arr[$count - 1]]) > $count) $open[$arr[count($arr) - 1]] = $arr;
	} else {
		$open[$arr[$count - 1]] = $arr;
	}
}
if($uids && $stype !=1){
	$sel_user=C::t('user')->fetch_all($uids);
	foreach($sel_user as $value){
		$selects['uid_'.$value['uid']]=$sel_user[$value['uid']];
	}
	if ($aorgids = C::t('organization_user')->fetch_orgids_by_uid($uids)) {
	
		foreach ($aorgids as $orgid) {
			$arr =  C::t('organization')->fetch_parent_by_orgid($orgid, true);
			$count = count($arr);
			if ($open[$arr[$count - 1]]) {
				if (count($open[$arr[$count - 1]]) > $count) $open[$arr[count($arr) - 1]] = $arr;
			} else {
				$open[$arr[$count - 1]] = $arr;
			}
		}
	}
}
//判断是否有无机构用户

if($uids){
	$no_org_uids=C::t('organization_user')->fetch_user_not_in_orgid();
	if(array_intersect($uids,array_keys($no_org_uids))){
		$open['other']=array('other');
	}
}
$openarr_length=count($open)?'1':'';
$openarr=json_encode($open);
$ismobile = helper_browser::ismobile();
if($ismobile){
	include template('mobile_selectuser');
	dexit();
}else{
	include template('selorguser');
	exit();
}

