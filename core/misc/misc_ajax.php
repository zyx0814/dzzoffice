<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
require libfile('function/organization');
if($_GET['op']=='getDepartment'){
	$orgid=intval($_GET['orgid']);
	//获取机构部门树
	$departmenttree=getDepartmentOption($orgid);
	echo  '<li role="presentation"><a href="javascript:;" tabindex="-1" role="menuitem" _orgid="0" onclick="selDepart(this)">'.lang('please_select_department').'</a></li>';
	echo $departmenttree;
	exit();
}elseif($_GET['op']=='getuserlist'){
	$orgid=intval($_GET['orgid']);
	//获取机构部门树
	$users=getUserByOrgid($orgid,1);
	$html='';
	foreach($users as $uid =>$value){
		$html.='<li uid="'.$value['uid'].'" username="'.$value['username'].'" >';
		$html.='<img src="avatar.php?uid='.$value['uid'].'" ><span>'.$value['username'].'</span>';
		$html.='<a class="pull-right add" href="javascript:;"  title="'.lang('add').'"><i class="glyphicon glyphicon-forward"></i></a></li>';
	}
	echo $html;
	exit();

}
?>