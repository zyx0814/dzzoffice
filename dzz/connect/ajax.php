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

if($_GET['do']=='delete'){
	$bz=trim($_GET['bz']);
	$id=intval($_GET['id']);
	if($bz=='dzz'){
		echo json_encode(array('error'=>lang('builtin_dish_allowed_delete')));
		exit();
	}
	$cloud=DB::fetch_first("select * from %t where bz=%s",array('connect',$bz));
	if(!$item=C::t($cloud['dname'])->fetch($id)){
		echo json_encode(array('error'=>lang('object_exist_been_deleted')));
		exit();
	}
	//查找icoid
	//判断删除权限
	if($item['uid']!=$_G['uid'] && $_G['admimid']!=1){
		echo json_encode(array('error'=>lang('privilege')));exit();
	}
	if($re=C::t($cloud['dname'])->delete_by_id($item['id'])){
		echo  json_encode($re);
	}else{
		echo json_encode(array('error'=>lang('delete_unsuccess')));
	}
	exit();
}elseif($_GET['do']=='getBucket'){
	$id=trim(rawurldecode($_GET['id']));
	$key=trim(rawurldecode($_GET['key']));
	$bz=empty($_GET['bz'])?'ALIOSS':$_GET['bz'];
	switch($bz){
		case 'ALIOSS':
			$re=io_ALIOSS::getBucketList($id,$key);
			break;
		case 'JSS':
			$re=io_JSS::getBucketList($id,$key);
			break;
	}
	if($re){
		echo  json_encode($re);
	}else{
		echo  json_encode(array());
	}
	exit();
}elseif($_GET['do']=='rename'){
	$return =array();
	$bz=trim($_GET['bz']);
	$id=intval($_GET['id']);
	$name=trim($_GET['name']);
	if($bz=='dzz'){
		if($_G['adminid']){
			C::t('connect')->update($bz,array('name'=>$name));
			echo json_encode(array('msg'=>'success'));exit();
		}else{
			echo json_encode(array('error'=>lang('privilege')));exit();
		}
	}else{
		$cloud=DB::fetch_first("select * from %t where bz=%s",array('connect',$bz));
		if($mycloud=C::t($cloud['dname'])->fetch($id)){
			if($mycloud['uid']!=$_G['uid'] && $_G['adminid']!=1){
				echo json_encode(array('error'=>lang('privilege')));exit();
			}elseif(C::t($cloud['dname'])->update($id,array('cloudname'=>$name))){
				echo json_encode(array('msg'=>'success'));exit();
			}
		}
		echo json_encode(array('error'=>lang('rechristen_failure')));exit();
	}
		
}elseif($_GET['do']=='todesktop'){
	$return =array();
	$bz=trim($_GET['bz']);
	$id=intval($_GET['id']);
	$cloud=DB::fetch_first("select * from %t where bz=%s",array('connect',$bz));
	$pfid=DB::result_first("select fid from ".DB::table('folder')." where uid='{$_G[uid]}' and flag='desktop'");
	if($bz=='dzz'){
		
		$icoarr=array(
					'uid'=>$_G['uid'],
					'username'=>$_G['username'],
					'oid'=>DB::result_first("select fid from ".DB::table('folder')." where flag='home' and uid='{$_G[uid]}'"),
					'name'=>$cloud['name'],
					'type'=>'folder',
					'flag'=>'home',
					'dateline'=>$_G['timestamp'],
					'pfid'=>$pfid,
					'size'=>0,
					'gid'=>0,
					'ext'=>'',
					'isdelete'=>0
				);
		if($icoid=DB::result_first("select icoid from %t where oid=%d and uid=%d and type=%s",array('icos',$icoarr['oid'],$_G['uid'],$icoarr['type']))){
			C::t('icos')->update($icoid,$icoarr);	
			//$icoarr['oid']=$item['fid'];
			$icoarr['icoid']=$icoid;
		
		}elseif($icoarr['icoid']=DB::insert('icos',($icoarr),1,1)){
			addtoconfig($icoarr);
		}else{
			echo json_encode(array('error'=>lang('added_desktop')));exit();
		}
		$icoarr['bz']='';
		
		$icoarr['fsize']=formatsize($icoarr['size']);
		$icoarr['ftype']=getFileTypeName($icoarr['type'],$icoarr['ext']);
		$icoarr['fdateline']=dgmdate($icoarr['dateline']);
		echo json_encode($icoarr);
		exit();
	}
	$cloud=DB::fetch_first("select * from %t where bz=%s",array('connect',$bz));
	if(!$item=C::t($cloud['dname'])->fetch_by_id($id)){
		echo  json_encode(array('error'=>lang('object_exist_been_deleted')));
		exit();
	}
	$pfid=DB::result_first("select fid from ".DB::table('folder')." where uid='{$_G[uid]}' and flag='desktop'");
	$icoarr=array(
					'uid'=>$_G['uid'],
					'username'=>$_G['username'],
					'oid'=>$item['id'],
					'name'=>$item['fname'],
					'type'=>$cloud['type'],
					'flag'=>$cloud['bz'],
					'dateline'=>$_G['timestamp'],
					'pfid'=>$pfid,
					'size'=>0,
					'gid'=>0,
					'ext'=>'',
					'isdelete'=>0
					
				);
				
	if($icoid=DB::result_first("select icoid from %t where oid=%d and uid=%d and type=%s",array('icos',$item['id'],$_G['uid'],$icoarr['type']))){
		C::t('icos')->update($icoid,$icoarr);	
		$icoarr['oid']=$item['fid'];
		$icoarr['icoid']=$icoid;
		
	
	}elseif($icoarr['icoid']=DB::insert('icos',($icoarr),1,1)){
		addtoconfig($icoarr);
		
	}else{
		echo json_encode(array('error'=>lang('added_desktop')));exit();
	}
	$icoarr['oid']=$item['fid'];
	$icoarr['bz']='';
	$icoarr['img']=$item['ficon'];
	$icoarr['fsize']=formatsize($icoarr['size']);
	$icoarr['ftype']=getFileTypeName($icoarr['type'],$icoarr['ext']);
	$icoarr['fdateline']=dgmdate($icoarr['dateline']);
	echo json_encode($icoarr);
	exit();
}
include template("addcloud");
?>
