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
$uid =isset($_GET['uid'])?intval($_GET['uid']):$_G['uid'];
$zero=$_GET['zero']?urldecode($_GET['zero']):lang('no_institution_users');
if($_GET['do']=='orgtree'){
	$id=intval($_GET['id']);
	$nouser=intval($_GET['nouser']);
	$moderator=intval($_GET['moderator']);
	//判断用户有没有操作权限
	$ismoderator=C::t('organization_admin')->ismoderator_by_uid_orgid($id,$_G['uid']);
	
	if(!$moderator || $ismoderator){
		$disable='';
		$type='user';
	}else{
		$disable='"disabled":true,';
		$type="disabled";	
	}
	if($id){
		$icon='dzz/system/images/department.png';
	}else{
		$icon='dzz/system/images/organization.png';
	}
	$data=array();
	if($_GET['id']=='#'){
		//if($_G['adminid']!=1) $topids=C::t('organization_admin')->fetch_toporgids_by_uid($_G['uid']);
		foreach(C::t('organization')->fetch_all_by_forgid($id) as $value){
			//if($_G['adminid']!=1 && !in_array($value['orgid'],$topids)) continue;
			if(!$moderator || C::t('organization_admin')->ismoderator_by_uid_orgid($value['orgid'],$_G['uid'])){
				$orgdisable=false;
				$orgtype='organization';
			}else{
				$orgdisable=true;
				$orgtype='disable';
			}
			$data[]=array('id'=>$value['orgid'],'text'=>$value['orgname'],'icon'=>$icon,'state'=>array('disabled'=>$orgdisable),"type"=>$orgtype,'children'=>true);
		}
	
		$data[]=array('id'=>'other','text'=>$zero,'icon'=>'dzz/system/images/department.png','state'=>array('disabled'=>$disable),"type"=>($type=="disabled")?$type:'default','children'=>true);
			
	}else{
		//获取用户列表
		
			if(!$id){
				if((!$moderator && !$nouser) || (!$nouser && $moderator && $ismoderator)){
					foreach(C::t('organization_user')->fetch_user_not_in_orgid($limit) as $value){
						$data[]=array('id'=>'uid_'.$value['uid'],'text'=>$value['username'].'<em class="hide">'.$value['email'].'</em>','icon'=>'dzz/system/images/user.png','state'=>array('disabled'=>$disable),"type"=>$type,'li_attr'=>array('uid'=>$value['uid']));
					}
				}
			}else{
				foreach(C::t('organization')->fetch_all_by_forgid($id) as $value){
					if(!$moderator || C::t('organization_admin')->ismoderator_by_uid_orgid($value['orgid'],$_G['uid'])){
						$orgdisable='';
						$orgtype='organization';
					}else{
						$orgdisable='"disabled":true,';
						$orgtype='disabled';
					}
					$data[]=array('id'=>$value['orgid'],'text'=>$value['orgname'],'icon'=>$icon,'state'=>array('disabled'=>$orgdisable),"type"=>$orgtype,'children'=>true);
					
				}
				if((!$moderator && !$nouser) || (!$nouser && $moderator && $ismoderator)){
					foreach(C::t('organization_user')->fetch_user_by_orgid($id,$limit) as $value){
						$data[]=array('id'=>'orgid_'.$value['orgid'].'_uid_'.$value['uid'],'text'=>$value['username'].'<em class="hide">'.$value['email'].'</em>','icon'=>'dzz/system/images/user.png','state'=>array('disabled'=>$disable),"type"=>$type,'li_attr'=>array('uid'=>$value['uid']));
					}
				}
			}
	}
	
	/*$list=array();
	$limit=0;
	$html='';
	
	if($id){
		
		$icon='dzz/system/images/department.png';
	}else{
		$icon='dzz/system/images/organization.png';
	}
	$data=array();
	if($_GET['id']=='#'){
		//$data[]=array('id'=>'#','text'=>'全部',"type"=>'organization','children'=>true);
		foreach(C::t('organization')->fetch_all_by_forgid($id) as $value){
			if(C::t('organization_admin')->ismoderator_by_uid_orgid($value['orgid'],$_G['uid'])){
			$orgtype='organization';
			}
			$data[]=array('id'=>$value['orgid'],'text'=>$value['orgname'],'icon'=>$icon,"type"=>'organization','children'=>true);
		}
	
		$data[]=array('id'=>'other','text'=>'无机构用户','icon'=>'dzz/system/images/department.png',"type"=>'department','children'=>true);
			
	}else{
		//获取用户列表
			if(!$id ){
				if(!$nouser){
					foreach(C::t('organization_user')->fetch_user_not_in_orgid($limit) as $value){
						$data[]=array('id'=>'uid_'.$value['uid'],'text'=>$value['username'],'icon'=>'dzz/system/images/user.png',"type"=>'user','li_attr'=>array('uid'=>$value['uid']));
					}
				}
			}else{
				foreach(C::t('organization')->fetch_all_by_forgid($id) as $value){
					$data[]=array('id'=>$value['orgid'],'text'=>$value['orgname'],'icon'=>'dzz/system/images/department.png',"type"=>'organization','children'=>true);
					
				}
				if(!$nouser){
					foreach(C::t('organization_user')->fetch_user_by_orgid($id,$limit) as $value){
							$data[]=array('id'=>'orgid_'.$value['orgid'].'_uid_'.$value['uid'],'text'=>$value['username'].'</em>','icon'=>'dzz/system/images/user.png',"type"=>'user','li_attr'=>array('uid'=>$value['uid']));
					}
				}
			}
		
	}*/
	exit(json_encode($data));
}elseif($_GET['do']=='search'){
	$nouser=intval($_GET['nouser']);
	$str=trim($_GET['str']);
	$str='%'.$str.'%';
	$sql="username LIKE %s";
	$sql_org="orgname LIKE %s";
	//搜索用户
	$data=array('other');
	if(!$nouser){
		$uids=array();
		foreach(DB::fetch_all("select * from %t where $sql ",array('user',$str)) as $value){
			$uids[]=$value['uid'];
			$data['uid_'.$value['uid']]='uid_'.$value['uid'];
		}
		$orgids=array();
		foreach($orgusers=C::t('organization_user')->fetch_all_by_uid($uids) as $value){
			$data['uid_'.$value['uid']]='orgid_'.$value['orgid'].'_uid_'.$value['uid'];
			$orgids[]=$value['orgid'];
		}
	}
	foreach(DB::fetch_all("select orgid from %t where $sql_org",array('organization',$str)) as $value){
		$orgids[]=$value['orgid'];
	}
	$orgids=array_unique($orgids);
	foreach($orgids as $orgid){
		$uporgids=getUpOrgidTree($orgid,true);
		foreach($uporgids as $value){
			$data[$value]=$value;
		}
	}
	$temp=array();
	foreach($data as $value){
		$temp[]=$value;
	}
	exit(json_encode($temp));
}
$ismobile=helper_browser::ismobile();
include template('orgtree');

?>
