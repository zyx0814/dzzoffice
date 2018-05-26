<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
 if(!$_G['uid']){
	exit(); 
 }
 //error_reporting(E_ALL);

  include_once libfile('function/organization'); 

  $data=array();
  $term=trim($_GET['term']);
  $filter=intval($_GET['filter']);//0:机构和用户；1：仅用户；2：仅机构
  if($filter==1 || !$filter){
	  $param_user=array('user','user_status');
	  $sql_user="where status<1";
	  if($term){
		   $sql_user.=" and username LIKE %s";
		   $param_user[]='%'.$term.'%';
	  }

	  foreach(DB::fetch_all("select u.uid,u.username  from %t u LEFT JOIN %t s on u.uid=s.uid  $sql_user order by s.lastactivity DESC limit 10",$param_user) as $value){
		 if($value['uid']!=$_G['uid']){
			 $data[]=array('name'=>$value['username'],
						   'searchkey'=> pinyin::encode($value['username'],'all').$value['username'],
						   'id'=>'u'.$value['uid'],
						   'icon'=>'avatar.php?uid='.$value['uid'].'&size=small',
						   'title'=>$value['username'].':'.'u'.$value['uid']
						);
			
		 }
	  }
  }
   if($filter==2 || !$filter){
	  $orgids=array();
	  if($at_range=$_G['setting']['at_range'][$_G['groupid']]){
		  switch($at_range){
			  case 1: //本部门
				foreach(C::t('organization_user')->fetch_orgids_by_uid($_G['uid']) as $orgid){
					$orgids=array_merge($orgids,getOrgidTree($orgid));
				}
				if($orgids){
					$sql_org.=" and orgid IN(%n)";
					$param_org[]=$orgids;
					foreach(DB::fetch_all("select orgname,orgid,forgid from %t $sql_org limit 10",$param_org) as $org){	
						$porgids=C::t('organization')->fetch_parent_by_orgid($org['orgid']);
						$titles=array();
						foreach($porgids as $porgid){
							if($porg=C::t('organization')->fetch($porgid)) $titles[]=$porg['orgname'];
						}
						 $data[]=array('name'=>$org['orgname'],
									   'title'=>implode('-',$titles),
									   'searchkey'=> pinyin::encode($org['orgname'],'all').$org['orgname'],
									   'id'=>'g'.$org['orgid'],
									   'icon'=>$org['forgid']?'dzz/system/images/department.png':'dzz/system/images/organization.png'
									);
						
					}
				}
				break;
			 case 2: //本机构
				  foreach(C::t('organization_user')->fetch_orgids_by_uid($_G['uid']) as $orgid){
						$orgids=array_merge($orgids,getOrgidTree($orgid));
						$orgids=array_merge($orgids, C::t('organization')->fetch_parent_by_orgid($orgid));
					}
					if($orgids){
						$sql_org.=" and orgid IN(%n)";
						$param_org[]=$orgids;
						foreach(DB::fetch_all("select orgname,orgid,forgid from %t $sql_org limit 10",$param_org) as $org){	
							$porgids=C::t('organization')->fetch_parent_by_orgid($org['orgid']);
							$titles=array();
							foreach($porgids as $porgid){
								if($porg=C::t('organization')->fetch($porgid)) $titles[]=$porg['orgname'];
							}
							$data[]=array('name'=>$org['orgname'],
										   'title'=>implode('-',$titles),
										   'searchkey'=> pinyin::encode($org['orgname'],'all').$org['orgname'],
										   'id'=>'g'.$org['orgid'],
										   'icon'=>$org['forgid']?'dzz/system/images/department.png':'dzz/system/images/organization.png'
										);
						}
					}
				break;
			 case 3: //全部
					foreach(DB::fetch_all("select orgname,orgid,forgid from %t $sql_org limit 10",$param_org) as $org){
						$porgids=C::t('organization')->fetch_parent_by_orgid($org['orgid']);
						$titles=array();
						foreach($porgids as $porgid){
							if($porg=C::t('organization')->fetch($porgid)) $titles[]=$porg['orgname'];
						}
						$data[]=array( 'name'=>$org['orgname'],
									   'title'=>implode('-',$titles),
									   'searchkey'=> pinyin::encode($org['orgname'],'all').$org['orgname'],
									   'id'=>'g'.$org['orgid'],
									   'icon'=>$org['forgid']?'dzz/system/images/department.png':'dzz/system/images/organization.png'
									);
						
					}
					
				break;
		  }
	  }
   }
  exit(json_encode($data));

?>