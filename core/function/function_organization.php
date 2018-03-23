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

function checkAtPerm($gid){//检查@部门权限
	global $_G;
	$range=$at_range=$_G['setting']['at_range'][$_G['groupid']];	
	if($range==3){//所有机构
		return true;
	}elseif($range==2){//机构
		$orgids=C::t('organization_user')->fetch_orgids_by_uid($_G['uid']);
		foreach($orgids as $orgid){
			$toporgids= C::t('organization')->fetch_parent_by_orgid($orgid);
			if(in_array($gid,$toporgids)) return true;
		}
		return false;
		
	}elseif($range==1){//部门
		$orgids=C::t('organization_user')->fetch_orgids_by_uid($_G['uid']);
		if(in_array($gid,$orgids)) return true;
		return false;
	}
	return false;
}
//获取部门的目录树,返回从机构到此部门的名称的数组
function getPathByOrgid($orgid,$path=array()){
	return C::t('organization')->getPathByOrgid($orgid,false);
}
//获取机构树
function getDepartmentOption($orgid,$url='',$all=false,$i=0,$pname=array()){
	$html='';
	
	//$data[$orgid]['i']=$i;
	
	if( $i<1){
		if($all){
			$html.='<li role="presentation">';
			$html.='<a href="'.($url?($url.'&depid=0'):'javascript:;').'" tabindex="-1" role="menuitem" _orgid="0" '.(!$url?'onclick="selDepart(this)"':'').'>';
			$html.='<div class="child-org">';
			for($j=0;$j<$i-1;$j++){
				$html.='<span class="child-tree tree-su">&nbsp;</span>';
			}
			$html.=lang('all');
			$html.='</div>';
			$html.='</a></li>';
		}
		if($org=C::t('organization')->fetch($orgid)){
			$pname[$i]=$org['orgname'];
			$html.='<li role="presentation">';
			$html.='<a href="'.($url?($url.'&depid='.$org['orgid']):'javascript:;').'" tabindex="-1" role="menuitem" _orgid="'.$org['orgid'].'" '.(!$url?'onclick="selDepart(this)"':'').' data-orgname='.implode('-',$pname).'>';
			$html.='<div class="child-org">';
			for($j=0;$j<$i-1;$j++){
				$html.='<span class="child-tree tree-su">&nbsp;</span>';
			}
			$html.=$org['orgname'];
			$html.='</div>';
			$html.='</a></li>';
		}
	}
	$i++;
	$count=C::t('organization')->fetch_all_by_forgid($orgid,true,0);
	if($count){
		$k=1;
		
		foreach(C::t('organization')->fetch_all_by_forgid($orgid) as $key=> $value){
			$pname[$i]=$value['orgname'];
			$html.='<li role="presentation">';
			$html.='<a href="'.($url?($url.'&depid='.$value['orgid']):'javascript:;').'" tabindex="-1" role="menuitem" _orgid="'.$value['orgid'].'" '.(!$url?'onclick="selDepart(this)"':'').' data-orgname='.implode('-',$pname).'>';
			$html.='<div class="child-org">';
			for($j=0;$j<$i-1;$j++){
				$html.='<span class="child-tree tree-su">&nbsp;</span>';
			}
			$html.='<span class="child-tree '.($k<$count?'tree-heng':'tree-heng1').'">&nbsp;</span>'.$value['orgname'];
			$html.='</div>';
			$html.='</a></li>';
			$html.=getDepartmentOption($value['orgid'],$url,false,$i,$pname);
			$k++;
		}
		//$html.='</tbody>';
	}
	return $html;
}
//获取机构树
function getDepartmentOption_admin($orgid,$url='',$all=false,$i=0,$tree=array()){
	global $_G;
	$html='';
	if($i<1 && ($org=C::t('organization')->fetch($orgid)) && $org['forgid']<1){
		 $tree[]=$org['orgname'];
		 if(!$all){
			$ismoderator=C::t('organization_admin')->ismoderator_by_uid_orgid($org['orgid'],$_G['uid']);
		 }else{
			$ismoderator=1;
		 }
			if($ismoderator){
				$html.='<li role="presentation">';
			}else{
				$html.='<li role="presentation" class="disabled">';
			}
			
				
			$html.='<a href="'.($url?($url.'&depid='.$org['orgid']):'javascript:;').'" tabindex="-1" role="menuitem" _orgid="'.$org['orgid'].'" '.(!$url?($ismoderator?'onclick="selDepart(this)"':''):'').' data-text="'.implode(' - ',$tree).'">'.$org['orgname'].'</a>';
			$html+'</li>';
	}
	
	$i++;
	$count=C::t('organization')->fetch_all_by_forgid($orgid,true);
	if($count){
		$k=1;
		$value=array();
		foreach(C::t('organization')->fetch_all_by_forgid($orgid) as $key=> $value){
			 if(!$all){
				 $ismoderator=C::t('organization_admin')->ismoderator_by_uid_orgid($value['orgid'],$_G['uid']);
			 }else{
				$ismoderator=1;
			 }
			
			if($ismoderator){
				$html.='<li role="presentation">';
			}else{
				$html.='<li role="presentation" class="disabled">';
			}
				
				$html.='<a href="'.($url?($url.'&depid='.$value['orgid']):'javascript:;').'" tabindex="-1" role="menuitem" _orgid="'.$value['orgid'].'" '.(!$url?($ismoderator?'onclick="selDepart(this)"':''):'').' data-text="'.($tree?(implode(' - ',$tree).' - '):'').$value['orgname'].'">';
				$html.='<div class="child-org">';
				for($j=0;$j<$i-1;$j++){
					$html.='<span class="child-tree tree-su">&nbsp;</span>';
				}
				$html.='<span class="child-tree '.($k<$count?'tree-heng':'tree-heng1').'">&nbsp;</span>'.$value['orgname'];
				$html.='</div>';
				$html.='</a></li>';
			$html.=getDepartmentOption_admin($value['orgid'],$url,$all,$i,array_merge($tree,array($value['orgname'])));
			$k++;
		}
		//$html.='</tbody>';
	}
	return $html;
}
//获取机构树
function getDepartmentJStree($orgid=0,$notin=array()){
	static $uids=array();
	$html='';
	foreach(C::t('organization')->fetch_all_by_forgid($orgid) as $key=> $value){
			 $html.='<li  data-jstree=\'{"type":"org","icon":"dzz/system/images/organization.png"}\'>'.$value['orgname'];
			 $html.='<ul>';
			  if(C::t('organization')->fetch_all_by_forgid($value['orgid'],true)){
				$re=getDepartmentJStree($value['orgid'],$notin,$html);
				$html.= $re['html'];
			 }
			 $users=getUserByOrgid($value['orgid'],0,$notin);
				foreach($users as $value1){
					$uids[]=$value1['uid'];
					 $html.='<li uid="'.$value1['uid'].'" data-jstree=\'{"type":"user","icon":"dzz/system/images/user.png"}\'>'.$value1['username'].'</li>';
				}
			
			 $html.='</ul>';
			 $html.=' </li>';
	}
	return array('html'=>$html,'uids'=>$uids);
}
//获取用户所在的部门
function getDepartmentByUid($uid,$getManage=0){
	$data=array();
	//获取用户所加入的所有部门
	$orgids=C::t('organization_user')->fetch_orgids_by_uid($uid);
	if($getManage && $orgids_m=C::t('organization_admin')->fetch_orgids_by_uid($uid)){
		$orgids=array_merge($orgids,$orgids_m);
	}
	foreach($orgids as $orgid){
		if($tree=getTreeByOrgid($orgid)){
			$data[$orgid]=$tree;
		}
	}
	return $data;	
}

//获取用户部门及所属机构
function getOrgByUid($uid,$getManage=0){
	$orglist=array();
	$arr=getDepartmentByUid($uid,$getManage);
	foreach($arr as $key => $value){
		$orglist[$value[0]['orgid']]=$value[0];
	}
	foreach($arr as $key => $value){
		if(count($value)>1){
			 $orglist[$value[0]['orgid']]['sublist'][$value[count($value)-1]['orgid']]=$value[count($value)-1];
		}
	}
	return $orglist;
}
//获取应用可以使用的部门
function getDepartmentByAppid($appid){
	$data=array();
	//获取用户所加入的所有部门
	$orgids=C::t('app_organization')->fetch_orgids_by_appid($appid);
	foreach($orgids as $orgid){
		if($tree=getTreeByOrgid($orgid)){
			$data[$orgid]=$tree;
		}
	}
	return $data;	
}
function getTreeByOrgid($orgid){
	$orgarr= C::t('organization')->fetch_parent_by_orgid($orgid,false);
	return $orgarr;
}
//获取机构或部门的用户列表
//$dep: ==0 只获取此机构的用户；
//		>0  获取全部下级机构的成员
// $notin   排除的用户列表;
//返回 user列表数组;
function getUserByOrgid($orgids,$dep=0,$notin=array(),$onlyuid=false){
	$orgids=(array)$orgids;
	if(!$orgids){ return array();}
	$ids=array();
	foreach($orgids as $orgid){
		if($dep){
			$ids=array_merge($ids,getOrgidTree($orgid));
		}else{
			$ids[]=$orgid;
		}
	}
	$uids=C::t('organization_user')->fetch_uids_by_orgid($ids);
	if($notin){
		$arr=array();
		foreach($uids as $uid){
			if(!in_array($uid ,$notin)) $arr[]=$uid;
		}
		$uids=$arr;
		unset($arr);
	}
	
	if($onlyuid) return $uids;
	
	return DB::fetch_all("select uid,username from %t where uid IN (%n) ",array('user',$uids));
}
function getOrgidByUid($uid,$sub=true){//获取用户所在部门ID和所有下级部门ID
	$ret=array();
	$orgids=C::t('organization_user')->fetch_orgids_by_uid($uid);
	if($sub){
		foreach($orgids as $orgid){
			$ret=array_merge($ret,getOrgidTree($orgid));
		}
	}else{
		$ret=$orgids;
	}
	return array_unique($ret);
}
//获取此机构和所有下属机构的id
function getOrgidTree($orgid){
	$oids=array();
	if($org=C::t('organization')->fetch($orgid)){
		foreach(DB::fetch_all("select orgid from %t where pathkey REGEXP %s order by disp",array('organization','^'.$org['pathkey'])) as $value){
			$oids[]=$value['orgid'];
		}
		$oids=array_diff($oids,array($orgid));
		array_unshift($oids,$orgid);
	}
	return $oids;
}
//获取此机构和所有上级机构的id
//获取此机构和所有上级机构的id
function getUpOrgidTree($orgid,$onlyid=true,$pids=array()){
	global $_G;
 	if($org=C::t('organization')->fetch($orgid)){
		if($onlyid){
			array_unshift($pids,$orgid);
		}else{
			$pids[$orgid]=$org;
		}
		$pids=getUpOrgidTree($org['forgid'],$onlyid,$pids);
	}
	return ($pids);
}