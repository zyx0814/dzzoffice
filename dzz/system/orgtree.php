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

$limit=1000;
if($_GET['do']=='orgtree'){
	$id=intval($_GET['id']);
	$nouser=intval($_GET['nouser']);//不显示用户
	$stype=intval($_GET['stype']);  //0:可以选择部门群组和用户；1：仅选择部门群组：2：仅选择用户
	$moderator=intval($_GET['moderator']);//是否仅可以选择我管理的群组或部门
	$range=intval($_GET['range']);//0：所有部门和群组；1：仅部门；2：仅群组
	$showjob=intval($_GET['showjob']); //是否显示职位
	//判断用户有没有操作权限
	$ismoderator=C::t('organization_admin')->ismoderator_by_uid_orgid($id,$_G['uid']);
	
	if(!$moderator || $ismoderator){
		$disable='';
		$type='user';
	}else{
		$disable='"disabled":true,';
		$type="disabled";	
	}

	$data=array();
	if($_GET['id']=='#'){
		if($_G['adminid']!=1  && $moderator) $topids=C::t('organization_admin')->fetch_toporgids_by_uid($_G['uid']);
		foreach(C::t('organization')->fetch_all_by_forgid($id,false,-1) as $value){
			if($_G['adminid']!=1  && $moderator && !in_array($value['orgid'],$topids)) continue;
			if($value['type']=='1' && $range==1){
					continue;
			}elseif($value['type']=='0' && $range==2){		
					continue;
			}
			if(!$moderator || C::t('organization_admin')->ismoderator_by_uid_orgid($value['orgid'],$_G['uid'])){
				$orgdisable=false;
				$orgtype='organization';
			}else{
				$orgdisable=true;
				$orgtype='disable';
			}
			$arr = array(
				'id'=>$value['orgid'],
				'text'=>$value['orgname'],
				'state'=>array('disabled'=>$orgdisable),
				"type"=>$orgtype,
				'children'=>true);
			
			if(preg_match('/^\d+$/',$value['aid']) && $value['aid'] > 0){
				$arr['text'] = $value['orgname'];
				$arr['icon']='index.php?mod=io&op=thumbnail&width=24&height=24&path=' . dzzencode('attach::' . $value['aid']);
			}else{
				$arr['text'] = avatar_group($value['orgid'],$value).$value['orgname'];;
				$arr['icon'] = false;
			}
			$data[]=$arr;
		}
		if($stype!=1 && $range<1){
			$data[]=array('id'=>'other','text'=>$zero,'state'=>array('disabled'=>$disable),"type"=>($type=="disabled")?$type:'default','children'=>true);
		}
		
	}else{
		//获取用户列表
		if($_GET['id']=='other'){//无机构用户
			if(($moderator && $_G['adminid']!=1) || $nouser || $stype==1){
			}else{
				$uids = array();
				$datas = array();
				foreach(C::t('organization_user')->fetch_user_not_in_orgid($limit) as $value){
					$uids[] = $value['uid'];
					$datas[]=array('id'=>'uid_'.$value['uid'],'text'=>$value['username'].'<em class="hide">'.$value['email'].'</em>','icon'=>'dzz/system/images/user.png','state'=>array('disabled'=>$disable),"type"=>$type,'li_attr'=>array('uid'=>$value['uid']));
				}
				getuserIcon($uids,$datas,$data);
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
				$arr = array(
				'id'=>$value['orgid'],
				'text'=>$value['orgname'],
				'state'=>array('disabled'=>$orgdisable),
				"type"=>$orgtype,
				'children'=>true);

				if(preg_match('/^\d+$/',$value['aid']) && $value['aid'] > 0){
					$arr['text'] = $value['orgname'];
					$arr['icon']='index.php?mod=io&op=thumbnail&width=24&height=24&path=' . dzzencode('attach::' . $value['aid']);
				}else{
					$arr['text'] = avatar_group($value['orgid'],$value).$value['orgname'];;
					$arr['icon'] = false;
				}	
				$data[]=$arr;
			}
			if( $nouser || $stype==1 || ($moderator && !$ismoderator)){

			}else{
				$uids = array();
				$datas = array();
				
				foreach(C::t('organization_user')->fetch_user_by_orgid($id,$limit) as $value){
					if(!$value['uid']) continue;
					$uids[] = $value['uid'];
					if($showjob && $value['jobid']) $jobname=DB::result_first("select name from %t where jobid=%d",array('organization_job',$value['jobid']));
					$datas[]=array('id'=>'orgid_'.$value['orgid'].'_uid_'.$value['uid'],'text'=>$value['username'].($jobname?'<em> ['.$jobname.']</em>':'').'<em class="hide">'.$value['email'].'</em>','icon'=>'dzz/system/images/user.png','state'=>array('disabled'=>$disable),"type"=>$type,'li_attr'=>array('uid'=>$value['uid']));
				}
				getuserIcon($uids,$datas,$data);
			}
		}
	}
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
		$uporgids= C::t('organization')->fetch_parent_by_orgid($orgid,true);
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
function getuserIcon($uids,$datas,&$data){
	$uids = array_unique($uids);
	$avatars = array();
	foreach(DB::fetch_all('select u.avatarstatus,u.uid,s.svalue from %t u left join %t s on u.uid=s.uid and s.skey=%s where u.uid in(%n)',array('user','user_setting','headerColor',$uids)) as $v){
		if($v['avatarstatus'] == 1){
			$avatars[$v['uid']]['avatarstatus'] = 1;
		}else{
			$avatars[$v['uid']]['avatarstatus'] = 0;
			$avatars[$v['uid']]['headerColor'] = $v['svalue'];
		}
	}
	$userarr = array();
	$data1 = array();
	foreach($datas as $v){
		$uid=$v['li_attr']['uid'];
		$avatarstatus = $avatars[$uid]['avatarstatus'];
		if($avatars[$v['li_attr']['uid']]['avatarstatus']){
			$v['icon'] = 'avatar.php?uid='.$v['li_attr']['uid'];
		}elseif($avatars[$uid]['headerColor']){
			$headercolor = $avatars[$uid]['headerColor'];
			$v['icon'] = false;
			$v['text']= '<span class="Topcarousel" style="background:'.$headercolor.';" title="'.preg_replace("/<em.+?\/em>/i",'',$v['text']).'">'.strtoupper(new_strsubstr($v['text'],1,'')).'</span>'.$v['text'];
	
		}else{
			$v['icon'] = false;
			$v['text']= avatar_block($uid).$v['text'];
		}
		$data[] = $v;
	}
}
$ismobile=helper_browser::ismobile();
include template('orgtree');

?>
