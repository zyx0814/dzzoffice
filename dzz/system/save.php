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
$do = empty($_GET['do'])?'':trim($_GET['do']);
if(empty($_G['uid'])) {
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		dsetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
	} else {
		dsetcookie('_refer', rawurlencode(DZZSCRIPT.'?mod=system&op=dzzcp='.$de));
	}
	exit('needlogin');
}

$uid =$_G['uid'];
$space = dzzgetspace($_G['uid']);
$space['self']=intval($space['self']);
$refer=dreferer();	
if($do=='userfield'){

	//$dataname=$_GET['dataname'];
	//$val=trim($_POST[$dataname]);
	$data=array();
	$datanamearr=array( 'margintop', 'marginright', 'marginbottom', 'marginleft', 'iconview', 'iconposition', 'direction',  'autolist','taskbar');
	foreach($_GET as $key=>$val){
		if($key=='taskbar'){
			$data[$key]=in_array($val,array('bottom','left','top','right'))?$val:'';
		}elseif(in_array($key,$datanamearr)) $data[$key]=intval($val);
	}
	//if(perm_check::checkperm_Container('icosContainer_body_'.$space['typefid']['desktop'],'admin')){
		C::t('user_field')->update($_G['uid'],$data);
	//}
	exit('success');
}elseif($do=='folder'){
	$fid=intval($_GET['fid']);
	$data=array();
	if(isset($_GET['iconview'])) $data['iconview']=intval($_GET['iconview']);
	if(isset($_GET['disp'])) $data['disp']=intval($_GET['disp']);
	if($data && perm_check::checkperm_Container($fid,'admin')){
		C::t('folder')->update($fid,$data);
	}
	exit('success');
}elseif($do=='clearIcoposition'){
	$icoids=$_GET['icoid'];
	$pfid=trim($_POST['fid']);
	if(!$icoids) exit();
	if(!is_array($icoids)){
		$icoids=array($icoids);	
	}
	if(!perm_check::checkperm_Container($pfid,'admin')) exit(lang('no_privilege'));
	C::t("dzz_icos")->update($icoids,array('position'=>''));
	exit();
	
}elseif($do=='icoposition'){
	$icoid=intval($_POST['icoid']);
	$dataname=$_GET['dataname'];
	$val=trim($_POST[$dataname]);
	if($icoarr=C::t('icos')->fetch($icoid)){
		if(perm_check::checkperm('admin',$icoarr)){
			C::t('icos')->update($icoid,array($dataname=>$val));
		}
	}
	exit();
}elseif($_GET['do']=='move'){
	$obz=trim($_GET['obz']);
	$tbz=trim($_GET['tbz']);
	$sourcetype=trim($_GET['sourcetype']);
	$icoids=explode(',',$_GET['icoid']);
	$ticoid=intval(dzzdecode($_GET['ticoid']));
	$container=trim($_GET['container']);
	$iscut=isset($_GET['iscut'])?intval($_GET['iscut']):0;
	$data=array();
	$icoarr=array();
	$folderarr=array();
	
	if(!$icoids){
		$data=array('error'=>lang('data_error'));
		echo json_encode($data);
		exit();
	}
	
	//判断目标容器$container;
	if($ticoid){
		if(!$ticoarr=C::t('icos')->fetch_by_icoid($ticoid)){
			$data=array('error'=>lang('target_not_exist'));
			echo json_encode($data);
			exit();
		}
		
		if($ticoarr['type']=='folder'){
			$container='icosContainer_folder_'.$ticoarr['oid'];
			$ticoid=0;
		}else{
			$container='icosContainer_folder_'.$ticoarr['pfid'];
		}
	}
	
	//处理目标位置
	
	
	//判断是否为复制状态；
	$iscopy=checkCopy(0,$sourcetype,$iscut,$obz,$tbz);
	$gid=getGidByContainer($container);
	if(!$tbz && !$pfid=getFidByContainer($container)){
		$data=array('error'=>lang('folder_not_exist'));
			echo json_encode($data);
			exit();
	}
	
	if($sourcetype=='icoid'){//是ico时
		
		$data=array();
		$totalsize=0;
		$data['gid']=$gid;
		$data['iscopy']=$iscopy;
		$icos=$folderids=array();
		
		//分4种情况：a：本地到api；b：api到api；c：api到本地；d：本地到本地；
		
		foreach($icoids as $icoid){
			//在目标位置创建
			$icoid=dzzdecode(rawurldecode($icoid));
			if(empty($icoid)){
				$data['error'][]=$icoid.'：'.lang('forbid_operation');
				continue; 
			}
			$opath=rawurldecode($icoid);
			$path=rawurldecode(str_replace(array('_dock_','icosContainer_folder_','icosContainer_body_'),'',$container));
			$return=IO::CopyTo($opath,$path,$iscopy);
			
			if($return['success']===true){
				if(!$iscopy && $return['moved']!==true){
					 IO::DeleteByData($return);
				 }
				$data['icoarr'][]=$return['newdata'];
				if(!$tbz){
					addtoconfig($return['newdata'],$ticoid);
				}
				
				if($return['newdata']['type']=='folder') $data['folderarr'][]=IO::getFolderByIcosdata($return['newdata']);
				$data['successicos'][$return['icoid']]=$return['newdata']['icoid'];
				
			}else{
				$data['error'][]=$return['name'].':'.$return['success'];
			}
		}
		if($data['successicos']){
			$data['msg']='success';
			if(isset($data['error'])) $data['error']=implode(';',$data['error']);
			echo json_encode($data);
			exit();
		}else{
			$data['error']=implode(';',$data['error']);
			echo json_encode($data);
			exit();
		}
	}
}

?>
