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

define('CURRENT_PATH','dzz/system');
$do = empty($_GET['do'])?'':$_GET['do'];
$uid =isset($_GET['uid'])?intval($_GET['uid']):$_G['uid'];

$space=dzzgetspace($uid);
//判断数据唯一
$refer=dreferer();
$msg='';
if($do=='get_children'){
	$sid=empty($_GET['id'])?0:$_GET['id'];
	$winid=$_GET['winid'];
	$bz=rawurldecode($_GET['bz']);
	$path=rawurldecode($_GET['path']);
	
	$data=array();
	list($prex,$id)=explode('-',$sid);
	if(!$prex){
		if($bz=='all'){//获取所有的顶级目录，包括企业盘，网盘，云存储 和机构和部门；
			
			//获取企业盘和组织机构
				$query=DB::query("select * from ".DB::table('folder')." where innav>0 and pfid='0' and  uid='{$_G[uid]}' and uid>0 order by display ");
				while($value=DB::fetch($query)){
					$data[]=array('attr'=>array('id'=>'f-'.$value['fid'].'-'.$winid,'rel'=>$value['flag']),
							   'data'=>$value['fname'],
							   'state'=>(DB::result_first("select COUNT(*) from ".DB::table('folder')." where pfid='{$value[fid]}'"))?'closed':''
							   );
					
				}
				//获取部门
				include_once libfile('function/organization');
				if($_G['adminid']==1){
					$orglist=C::t('organization')->fetch_all_by_forgid(0);
				}else{
					$orglist=getOrgByUid($_G['uid'],true);
				}
				$folderarr=array();
				foreach($orglist as $value){
					if(!$value['available']) continue;
					if($folder=C::t('folder')->fetch($value['fid'])){
						$data[]=array('attr'=>array('id'=>'f-'.$folder['fid'].'-'.$winid,'rel'=>'organization'),
								   'data'=>$folder['fname'],
								   'state'=>'closed'
								   );
								 
						$folderarr[$folder['fid']]=$folder;
					}
					
				}
			//获取所有云盘
			$mycloud=array();
			$icoarr=array();
			foreach(C::t('connect')->fetch_all_folderdata($uid) as $value){
				$folderarr[$value['fid']]=$value;
				$bzarr=explode(':',$value['bz']);
				$data[]=array('attr'=>array('id'=>'f-'.$value['fid'].'-'.$winid,'rel'=>$bzarr[0]),
							   'data'=>$value['fname'],
							   'state'=>'closed'
							  );
			}
			if(count($data)>0)	$data[]=array('icosdata'=>'','folderdata'=>$folderarr);
			
		}else{
		
			if($bz){
				$bzarr=explode(':',$bz);
				$root=IO::getCloud($bz);
				$data[]=array('attr'=>array('id'=>'f-'.md5($path).'-'.$winid,'rel'=>$bzarr[0]),
							   'data'=>$root['cloudname'],
							   'state'=>'closed'
							   );
				
				if(count($data)>0)	$data[]=array('icosdata'=>'','folderdata'=>'');
			}else{
				$query=DB::query("select * from ".DB::table('folder')." where innav>0 and pfid='0' and  uid='{$_G[uid]}' order by display ");
				while($value=DB::fetch($query)){
					$data[]=array('attr'=>array('id'=>'f-'.$value['fid'].'-'.$winid,'rel'=>$value['flag']),
							   'data'=>$value['fname'],
							   'state'=>(DB::result_first("select COUNT(*) from ".DB::table('folder')." where pfid='{$value[fid]}'"))?'closed':''
							   );
					
				}
				//获取部门
				include_once libfile('function/organization');
				if($_G['adminid']==1){
					$orglist=C::t('organization')->fetch_all_by_forgid(0);
				}else{
					$orglist=getOrgByUid($_G['uid'],true);
				}
				
				$folderarr=array();
				foreach($orglist as $value){
					if(!$value['available']) continue;
					if($folder=C::t('folder')->fetch_by_fid($value['fid'])){
						$data[]=array('attr'=>array('id'=>'f-'.$folder['fid'].'-'.$winid,'rel'=>'organization'),
								   'data'=>$folder['fname'],
								   'state'=>'closed'
								   );
						$folderarr[$folder['fid']]=$folder;
					}
				}
				if(count($data)>0)	$data[]=array('icosdata'=>'','folderdata'=>$folderarr);
			}
		}
	}elseif($prex=='f'){
		if($bz){
			$folderdata=$icosdata=array();
			$icosdata=IO::listFiles($path,'','',1000);
			if($icosdata['error']){
				exit(json_encode($icosdata));
			}
			
			//echo($pfid.'<br>');
			foreach($icosdata as $key => $value){
				//print_r($value);
				//echo '<br>';
				
				if($value['path']==$path) continue;
				if($value['type']=='folder'){
				
					$folder=IO::getFolderByIcosdata($value);
					
					//echo 'd_';
					$data[]=array('attr'=>array('id'=>'f-'.$folder['fid'].'-'.$winid,'rel'=>'folder'),
								   'data'=>$folder['fname'],
								   'state'=>'closed',
								   );
					$folderdata[$folder['fid']]=$folder;
				}
			}
			if(count($data)>0)	$data[]=array('icosdata'=>$icosdata,'folderdata'=>$folderdata);
		
		}else{
			$arr=array();
			$icos=array();
			$icosdata=array();
			$folderdata=array();
			$folderids=array();
			if($folder=DB::fetch_first('select * from '.DB::table('folder')." where fid='{$id}'")){
				$sql='';
				if($folder['gid']>0 ){
					
					$folder['perm']=perm_check::getPerm($folder['fid']);
					
					if($folder['perm']>0){
						
						if(perm_binPerm::havePower('read1',$folder['perm'])){
							$sql.=" and uid='{$_G[uid]}'";
						}
						if(perm_binPerm::havePower('read2',$folder['perm'])){
							if($sql) $sql='';
							else $sql.=" and uid!='{$_G[uid]}'";
						}
					}
					$forgid=$folder['gid'];
				}
				
				$query=DB::query("select * from ".DB::table('folder')." where innav>0 and pfid='{$folder[fid]}' and isdelete<1 $sql order by display,fname asc");
				
				while($value=DB::fetch($query)){
					
					if($value['gid'] && $value['flag']=='organization'){
						$uids=C::t('organization_user')->fetch_uids_by_orgid($value['gid']);
						$ismoderator=C::t('organization_admin')->ismoderator_by_uid_orgid($value['gid'],$_G['uid']);
						if(!in_array($_G['uid'],$uids) && !$ismoderator && $_G['adminid']!=1) continue;
						$orglist[$value['gid']]=$value;continue;
					}elseif($value['flag']=='folder' && !DB::result_first("select COUNT(*) from %t where type='folder' and pfid=%d and oid=%d",array('icos',$folder['fid'],$value['fid']))){
						continue;
					}
					
					$folderids[]=$value['fid'];	
					$data[]=array('attr'=>array('id'=>'f-'.$value['fid'].'-'.$winid,'rel'=>$value['flag']),
								   'data'=>$value['fname'],
								   'state'=>'closed'
								 );
				}
				if($orglist){
					include_once libfile('function/organization');
					if(!$orgs=C::t('organization')->fetch_all_by_forgid($forgid)){
						$orgs=$orglist;
					}
				    $list=array();
					foreach($orgs as $orgid => $vlaue1){
						foreach(($orgids=getOrgidTree($orgid)) as $gid){
							if(isset($orglist[$gid])){
								$list[]=$orglist[$gid];
								unset($orglist[$gid]);
							}
						}
					}
					if($orglist) $list=array_merge($orglist,$list);
					foreach($list as $value){
						$folderids[]=$value['fid'];	
						$data[]=array('attr'=>array('id'=>'f-'.$value['fid'].'-'.$winid,'rel'=>'department'),
									   'data'=>$value['fname'],
									   'state'=>'closed'
									   );
					}
				}
				if($folderids){
					$folderdata=C::t('folder')->fetch_all_by_fid($folderids);
					
				}
				if(count($data)>0) $data[]=array('icosdata'=>array(),'folderdata'=>$folderdata);
			}
		}
	}
	
	echo json_encode($data);
	exit();
}elseif($do=='filemanage'){
	$perpage=isset($_GET['perpage'])?intval($_GET['perpage']):100;
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	$start = ($page-1)*$perpage;
	$total=0;
	$winid=$_GET['winid'];
	$sid=empty($_GET['id'])?0:$_GET['id'];
	$marker=empty($_GET['marker'])?'':trim($_GET['marker']);
	$bz=empty($_GET['bz'])?'':urldecode($_GET['bz']);
	$path=rawurldecode($_GET['path']);
	
	$arr=array();
	$icoid=intval($_GET['icoid']);
	$uid=empty($_GET['uid'])?0:intval($_GET['uid']);
	if($icoid && $icoarr=DB::fetch_first("select * from ".DB::table('icos') ." where icoid='{$icoid}'")){
		$icoarr=replace_remote($icoarr);
		$icoarr['url']=replace_canshu($icoarr['url']);
		$icoarr['fsize']=formatsize($icoarr['size']);
		$icoarr['ftype']=getFileTypeName($icoarr['type'],$icoarr['ext']);
		$icoarr['fdateline']=dgmdate($icoarr['dateline']);
		$data1[$icoarr['icoid']]=$icoarr;
		$json_data1=json_encode($data1);
	}
	$data=array();$userdata=array();
	$folderids=$folderdata=$baiduids=$storageids=array();
	$force=intval($_GET['force']);
	
	if($bz){
		
		$keyword=urldecode($_GET['keyword']);
		$asc=intval($_GET['asc']);
		list($prex,$id)=explode('-',$sid);
		$container='icosContainer_folder_'.$id;
		$order=$asc>0?'asc':"desc";
		switch($_GET['disp']){
			case 0:
				$by='name';
				//$order='';
				break;
			case 1:
				$by='size';
				//$order='DESC';
				break;
			
			case 3:
				$by='time';
				//$order='DESC';
				break;
			
		}
		$limit=$start.'-'.($start+$perpage);
		//exit($bz);
		if(strpos($bz,'ALIOSS')===0 || strpos($bz,'JSS')===0 || strpos($bz,'qiniu')===0){
			 $order=$marker;
			 $limit=$perpage;
		}elseif( strpos($bz,'OneDrive')===0){
			 $limit=$perpage;
			 $force=$marker;
		}
		$icosdata=IO::listFiles($path,$by,$order,$limit,$force);
		
		if($icosdata['error']){
			exit(json_encode($icosdata));
		}
		$folderdata=array();
		$ignore=0;
		foreach($icosdata as $key => $value){
			if($value['error']){
				$ignore++;
				 continue;
			}
			if($value['type']=='folder'){
				$folder=IO::getFolderByIcosdata($value);
				$folderdata[$folder['fid']]=$folder;
			}
			if(strpos($bz,'ftp')===false){
				if(trim($value['path'],'/')==trim($path,'/')){
					 $ignore++;
					 continue;
				}
			}
			
			$userdata[$value['uid']]=$value['username'];
			$data[$key]=$value;
		}
		
		//$sid=md5(rawurldecode($sid));
		//$data=$icosdata;
		$bz=($bz);
	//print_r($data);	exit($sid);
	}else{
		list($prex,$id)=explode('-',$sid);
		if($prex=='f'){
			$container='icosContainer_folder_'.$id;
			
			$arr=array();
			if($folder=C::t('folder')->fetch_by_fid($id)){
				
					//判断如果目录的uid不等于$_G['uid'] 没有权限
				    //if($folder['uid'] && $folder['uid']!=$_G['uid']) exit(lang('message','no_privilege'));
					$folder['disp']=$disp=intval($_GET['disp'])?intval($_GET['disp']):intval($folder['disp']);
					$folder['iconview']=(isset($_GET['iconview'])?intval($_GET['iconview']):intval($folder['iconview']));
					$keyword=urldecode($_GET['keyword']);
					$asc=intval($_GET['asc']);
					$order=$asc>0?'ASC':"DESC";
					switch($disp){
						case 0:
							$orderby='name';
							//$order='';
							break;
						case 1:
							$orderby='size';
							//$order='DESC';
							break;
						case 2:
							$orderby=array('type','ext');
							//$order='';
							break;
						case 3:
							$orderby='dateline';
							//$order='DESC';
							break;
						
					}
				$folder['perm']=perm_check::getPerm($folder['fid']);
					//$folder['opened']=1;
				if($folder['flag']=='recycle'){
					/*if($total=C::t('icos')->fetch_all_isdelete(0,'','',0,'',true)){
						if($start>=$total){
							$page-=1;
							$start = ($page-1)*$perpage;
						}*/
						foreach(C::t('icos')->fetch_all_isdelete($perpage,$orderby,$order,$start) as $value){
							if($value['type']=='folder'){
								$folderids[]=$value['oid'];
							}elseif($value['type']=='shortcut'){
									foreach($value['tdata']['folderarr'] as $key=>$value1){
										 $folderdata[$key]=$value1;
									}
								//	$data[$value['tdata']['icoid']]=$value['tdata'];
							}
			
							$arr[$value['icoid']]=$value;
						}
					//}
				}else{
					/*if($total=C::t('icos')->fetch_all_by_pfid($folder['fid'],$folder,'',0,'','',0,true)){
							if($start>=$total){
								$page-=1;
								$start = ($page-1)*$perpage;
							}*/
							
							foreach(C::t('icos')->fetch_all_by_pfid($folder['fid'],$keyword,$perpage,$orderby,$order,$start) as $value){
								
								if($value['type']=='app' && $value['isshow']<1){
									
									continue;
								}elseif($value['type']=='shortcut'){
										foreach($value['tdata']['folderarr'] as $key=>$value1){
											 $folderdata[$key]=$value1;
										}
										//$arr[$value['tdata']['icoid']]=$value['tdata'];
								}
								$arr[$value['icoid']]=$value;
							}
						//}
					}
				
				foreach($arr as $key =>$value){
					$userdata[$value['uid']]=$value['username'];
					$data[$key]=$arr[$key];
					
				}
				$folderdata[$folder['fid']]=$folder;
				//print_r($folderdata);exit('ddddd');
				/*//目录数据
				if($folderids){
					$fids=$folderids;
					$folderids=array();
					foreach(C::t('folder')->fetch_all($fids) as $value){
						$value['perm']=getPerm($value['fid']);
						$folderdata[$value['fid']]=$value;
						$folderids[]=$value['fid'];
					}
				}*/
			}
		}
	}
	
	if(count($data)>=($perpage-$ignore)){
		$total=$start+$perpage*2-1;
	}else{
		$total=$start+count($data);
	}
	if(!$json_data=json_encode($data)) $data=array();
	if(!$json_data=json_encode($folderdata)) $folderdata=array();
	$return=array(	'sid'=>$sid,
					'total'=>$total,
					'data'=>$data?$data:array(),
					'folderdata'=>$folderdata?$folderdata:array(),
					'param'=>array(
									'page'=>$page,
									'perpage'=>$perpage,
									'winid'=>$winid,
									'container'=>$container,
									'bz'=>$bz,
									'total'=>$total,
									'asc'=>$asc,
									'keyword'=>$keyword,
									'localsearch'=>$bz?1:0
									)
				 );
	
	if(!$uid) $return['userdata']=$userdata?$userdata:array(); 
	//$json_folder=json_encode($folderdata);
	//$json=json_encode($data);
	echo (json_encode($return));
	exit();
}
?>
