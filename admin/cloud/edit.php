<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @version     DzzOffice 1.1 release  2014.7.05
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}

if($_GET['do']=='usercloud'){
	$bz=trim($_GET['bz']);
	$cloud=C::t('connect')->fetch($bz);
	$navtitle=$cloud['name'].' - '.lang('using_user');
	if(submitcheck('cloudsubmit')){
		$dids=$_GET['delete'];
		foreach($dids as $id){
			C::t($cloud['dname'])->delete_by_id($id);
		}
		showmessage('do_success',dreferer());
	}else{
		$list=array();
		
		$page = empty($_GET['page'])?1:intval($_GET['page']);
		$perpage=20;
		$start=($page-1)*$perpage;
		$theurl=BASESCRIPT.'?mod=cloud&op=edit&do=usercloud&bz='.$bz;
		$dname=$cloud['dname'];
		$count=DB::result_first("select COUNT(*) from ".DB::table($dname)." where bz='{$bz}' and uid>0");
		foreach(DB::fetch_all("select * from ".DB::table($dname)." where bz='{$bz}' and uid>0 order by dateline DESC limit $start,$perpage") as $value1){
			if($cloud['type']=='pan'){
					if(!$value1['cloudname']) $value1['cloudname']=$cloud['name'].':'.($value1['cusername']?$value1['cusername']:$value1['cuid']);
					$value1['bz']=$value['bz'];
					$value1['icoid']=md5($value['bz'].':'.$value1['id'].':'.$value['root']);
					$value1['img']='dzz/images/default/system/'.$cloud['bz'].'.png';
				
			}elseif($cloud['type']=='storage'){
					$value1['access_id']=authcode($value1['access_id'],'DECODE',$value1['type'])?authcode($value1['access_id'],'DECODE',$value1['type']):$value1['access_id'];
					if(!$value1['cloudname']) $value1['cloudname']=$cloud['name'].':'.($value1['bucket']?$value1['bucket']:cutstr($value1['access_id'], 4, $dot = ''));
					$value1['bz']=$value['bz'];
					$value1['img']='dzz/images/default/system/'.$cloud['bz'].'.png';
			}else{
					
					$value1['bz']=$value['bz'];
					$value1['img']='dzz/images/default/system/'.$cloud['bz'].'.png';		
			}	
			$user=getuserbyuid($value1['uid']);
			$value1['username']=$user['username'];
			$value1['dateline']=dgmdate($value1['dateline']);
			$list[]=$value1;
		}
		$multi=multi($count, $perpage, $page, $theurl,'pull-right');
		include template('edit');
	}
}elseif($_GET['do']=='getBucket'){
	$id=$_GET['id'];
	$key=$_GET['key'];
	if($re=io_ALIOSS::getBucketList($id,$key)){
		echo  json_encode($re);
	}else{
		echo  json_encode(array());
	}
	exit();

}else{
	$bz=$_GET['bz'];
	$cloud=C::t('connect')->fetch($bz);
	$navtitle=$cloud['name'].' - '.lang('set');
	if(submitcheck('editsubmit')){
		$_GET=dhtmlspecialchars($_GET);
		if($cloud['type']=='pan'){
			$setarr=array(
							'name'=>$_GET['name'],
							'root'=>trim($_GET['root']),
							'key'=>trim($_GET['key']),
							'secret'=>trim($_GET['secret']),
							'available'=>intval($_GET['available']),
						);
			if(empty($setarr['key']) || empty($setarr['secret'])) {
				$setarr['available']=0;
			}
			
		}elseif($cloud['type']=='storage' || $cloud['type']=='ftp'){
			$setarr=array(
							'name'=>$_GET['name'],
							'available'=>intval($_GET['available'])>1?2:1,
							);
		}elseif($cloud['type']=='local'){
			$setarr=array(
							'name'=>$_GET['name'],
							'available'=>1,
						);
		}else{
			$setarr=array(
							'name'=>$_GET['name'],
							'available'=>intval($_GET['available'])>1?2:1,
							);
		}
		if(!is_file(DZZ_ROOT.'./core/class/io/io_'.($cloud['bz']).'.php')){
			$setarr['available']=0;
		}
		C::t('connect')->update($bz,$setarr);
		/*if($cloud['type']=='local'){//更新缓存$_G['setting']['storage'];
			$settings['storage']=array('on'=>$setarr['available']>1?'1':'0',
						 'ACCESS_ID'=>$setarr['id'],
						 'ACCESS_KEY'=>$setarr['secret'],
						 'BUCKET'=>$setarr['root']
						 );
			if($settings) {
				C::t('setting')->update_batch($settings);
			}
			include libfile('function/cache');
			updatecache('setting');
		}*/
		showmessage('do_success',dreferer());
	}else{
		
		if(!is_file(DZZ_ROOT.'./core/class/io/io_'.($cloud['bz']).'.php')){
			$cloud['warning'] = lang('cloud_index_api') . ($cloud['bz']) . lang('cloud_edit_php');
		}
		include template('edit');
	}
}
?>
