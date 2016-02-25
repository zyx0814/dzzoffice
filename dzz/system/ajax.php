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
//print_r($_GET);exit('dfdfs');
$uid =isset($_GET['uid'])?intval($_GET['uid']):$_G['uid'];
$space = dzzgetspace($uid);
$space['self']=intval($space['self']);
$do = empty($_GET['do'])?'':$_GET['do'];

//判断数据唯一
$refer=dreferer();

if($do=='newlink'){
	if(!submitcheck('newlinksubmit')){
		$pfid=intval($_GET['path']);
	}else{
		$link=trim($_GET['link']);
		//$link = dhtmlspecialchars(trim($_POST['link']));
		$pfid=intval($_GET['pfid']);
		//判断有没有权限添加
		
		//检查网址合法性
		if(!preg_match("/^(http|ftp|https|mms)\:\/\/.{5,300}$/i", ($link))){
			$link='http://'.preg_replace("/^(http|ftp|https|mms)\:\/\//i",'',$link);
		}
		if(!preg_match("/^(http|ftp|https|mms)\:\/\/.{4,300}$/i",($link))) showmessage(lang('message','网址格式错误！'));
	
		$ext=strtolower(substr(strrchr($link, '.'), 1, 10));
		//static $videoext  = array('swf', 'flv');
		//static $videohost  = array('tudou.com', 'youku.com','56.com','ku6.com');
		$isimage= in_array(strtoupper($ext), $imageexts) ? 1 : 0;
		$ismusic= /*in_array(strtoupper($ext), MUSICEXTS) ? 1 :*/ 0;
		//是图片时处理
		if($isimage){
			if(!perm_check::checkperm_Container($pfid,'newtype')){
					showmessage(lang('message','target_not_accept_image'));
				}
			if($data=io_dzz::linktoimage($link,$pfid)){
				if($data['error']) showmessage($data['error']);
				showmessage('do_success',$refer.'',array('data'=>rawurlencode(json_encode_gbk($data))),array('showdialog'=>1, 'showmsg' => true, 'closetime' => 1));
			}
		
		}else{
			//试图作为视频处理
			
			if($data=io_dzz::linktovideo($link,$pfid)){
				if(!perm_check::checkperm_Container($pfid,'video')){
					showmessage(lang('message','target_not_accept_video'));
				}
				if($data['error']) showmessage($data['error']);
				
				showmessage('do_success',$refer.'',array('data'=>rawurlencode(json_encode_gbk($data))),array('showdialog'=>1, 'showmsg' => true, 'closetime' => 1));
			}
			//作为网址处理
			if(!perm_check::checkperm_Container($pfid,'link')){
					showmessage(lang('message','target_not_accept_link'));
			}
			if($data=io_dzz::linktourl($link,$pfid)){
				if($data['error']) showmessage($data['error']);
				showmessage('do_success',$refer.'',array('data'=>rawurlencode(json_encode_gbk($data))),array('showdialog'=>1, 'showmsg' => true, 'closetime' => 1));
			}else{
				showmessage(lang('message','network_error'));
			}
		}
	}
}elseif($_GET['do']=='setExtopenDefault'){
	$extid=$_GET['extid'];
	if($extdata=C::t('app_open')->fetch($extid)){
		C::t('app_open_default')->insert_default_by_uid($_G['uid'],$extid,$extdata['ext']);
	}
    exit();	

}elseif($_GET['do']=='cache_api_expire'){
	$cachekey=$_GET['cacheid'];
	DB::delete('api_cache',"cachekey='{$cachekey}'");
    exit();
}elseif($_GET['do']=='updateview'){
	$type=trim($_GET['type']);
	$id=intval($_GET['id']);
	
	if($_G['coockie']['view_'.$type.'_'.$id]){
		exit();
	}else{
		if(C::t('count')->update_viewnum_by_type($id,$type))dsetcookie('view_'.$type.'_'.$id,1,86400);
	}
	exit();
}elseif($_GET['do']=='updateAppView'){
	$appid=intval($_GET['appid']);
	C::t('app_user')->update_lasttime($_G['uid'],$appid,$_G['timestamp']);
	exit();
}elseif($_GET['do']=='updatelike'){
	if(!$_G['uid']) exit();
	$icoid=intval($_GET['icoid']);
	$val=intval($_GET['val']);
	updateLike($icoid,$val);
	exit();
}elseif($do=='newdoc' || $do=='newtxt'){
	$path=($_GET['path']);
	if(!submitcheck('newdocsubmit')){
		if($do=='newdoc'){
			 $ext='dzzdoc';
		}else{
			 $ext='txt';
		}
		$name=lang('message','new_'.$ext);
	}else{
		$filename=$_GET['filename'].'.'.$_GET['ext'];
		if($arr=IO::upload_by_content(' ',$path,$filename)){
			if($arr['error']){
				showmessage($arr['error']);
		  }else{
				$arr['msg']='success';
				showmessage('do_success',dreferer(),array('data'=>rawurlencode(json_encode_gbk($arr))));
			}
		}else{
			$arr=array();
			$arr['msg']=lang('template','failure_newfolder');
			showmessage($arr['msg']);
		}
	}
		
}elseif($do=='newfolder'){
	$ismoderator=0;
	//error_reporting(E_ALL);
	if(!submitcheck('newfoldersubmit')){
		$path=($_GET['path']);
		if(($fid=intval($path))>0 && ($folder=C::t('folder')->fetch_by_fid($fid)) && $folder['gid']>0  /*&& (C::t('organization_admin')->ismoderator_by_uid_orgid($folder['gid'],$_G['uid']) || $_G['adminid']==1 )*/){
			//if(perm_binPerm::havePower('read2',$folder['perm1'])){
				$ismoderator=1;
				$permtitle=perm_binPerm::getGroupTitleByPower($folder['perm1']);
				$permarr=perm_binPerm::groupPowerPack();
			//}
		}
		$foldername=IO::getFolderName(lang('template','newfolder',null,'dzz'),$path);
		
	}else{
		$perm=intval($_GET['perm']);
		$fname=io_dzz::name_filter(getstr($_GET['name'],80));
		$path=trim($_GET['path']);
		if($arr=IO::CreateFolder($path,$fname,$perm)){
			if($arr['error']){
				showmessage($arr['error']);
		  }else{
				$arr['msg']='success';
				showmessage('do_success',dreferer(),array('data'=>rawurlencode(json_encode_gbk($arr))));
			}
		}else{
			$arr=array();
			$arr['msg']=lang('template','failure_newfolder');
			showmessage($arr['msg']);
		}
	}
}elseif($do=='getDataByFid'){
	$fid=intval($_GET['fid']);
	$folderarr=$icoarr=array();
	if($fids=C::t('folder')->fetch_path_by_fid($fid)){
		foreach($fids as $fid){
			$folderarr[]=C::t('folder')->fetch_by_fid($fid);
			if($icoid=DB::result_first("select icoid from %t where oid=%d and type='folder'",array('icos',$fid))){
				$icoarr[]=C::t('icos')->fetch_by_icoid($icoid);
			}
		}
	}
	exit(json_encode(array('icoarr'=>$icoarr,'folderarr'=>$folderarr)));
}elseif($do=='share'){
	$sharestatus=array('-4'=>'已屏蔽','-3'=>'分享文件删除','-2'=>'次数用尽','-1'=>'已过期','0'=>'正常');
	if(!submitcheck('sharesubmit')){
		$path=dzzdecode($_GET['path']);
		$icoarr=IO::getMeta($path);
		if($icoarr['type']=='shortcut'){
			$icoarr['type']=$icoarr['ttype'];
			$icoarr['size']=$icoarr['tdate']['size'];
			$icoarr['ext']=$icoarr['tdate']['ext'];
		}
		if($share=C::t('share')->fetch_by_path($path.'&uid='.$_G['uid'])){
			if(is_file($_G['setting']['attachdir'].'./qrcode/'.$share['sid'][0].'/'.$share['sid'].'.png')) $share['qrcode']=$_G['setting']['attachurl'].'./qrcode/'.$share['sid'][0].'/'.$share['sid'].'.png';
			if($share['password']) $share['password']=dzzdecode($share['password'],'DECODE');
			if($share['endtime']){
				 if($share['endtime']<TIMESTAMP && $share['status']>-1) $share['status']=-1;
				 $share['endtime']=dgmdate($share['endtime'],'Y-m-d');
			}else $share['endtime']='';
			if($share['times']) {
				if($share['times']>=$share['count'] && $share['status']>-1) $share['status']=-2;
			}else{
				$share['times']='';
			}
			$share['stitle']=$sharestatus[$share['status']];
			$share['shareurl']=$_G['siteurl'].'s.php?sid='.$share['sid'];
		}else{
			$share=array('title'=>$icoarr['name']);
		}	
	}else{
		$share=$_GET['share'];
		$share['title']=getstr($share['title']);
		if($share['endtime']) $share['endtime']=strtotime($share['endtime']);
		if($share['password']) $share['password']=dzzencode($share['password']);
		$share['times']=intval($share['times']);
		$share['count']=0;
		if($ret=C::t('share')->insert_by_sid($share)){
			$ret['msg']='success';
			exit(json_encode($ret));
		}else{
			exit(json_encode(array('error'=>'创建失败！')));
		}
	}
}elseif($do=='property'){
	
	$icoid=rawurldecode($_GET['icoid']);
	
	$icoarr=IO::getMeta($icoid);
	$perm=perm_check::checkperm('rename',$icoarr);
	if($icoarr['error']) showmessage($icoarr['error']);
	if(submitcheck('propertysubmit')){
		$return=array();
		if(!$icoarr['bz']){
			$ret=0;
			$name=io_dzz::name_filter(trim($_GET['name']));
			if($perm && $icoarr['name']!=$name){
				C::t('icos')->update_by_name($icoid,$name);
				$ret=1;
			}
			if($icoarr['type']=='folder' && $icoarr['gid']>0){
				$ismoderator=0;
				if((C::t('organization_admin')->ismoderator_by_uid_orgid($icoarr['gid'],$_G['uid']) || $_G['adminid']==1) ){//是部门管理员或系统管理员
					$ismoderator=1;	
				}elseif(($pfolder=C::t('folder')->fetch_by_fid($icoarr['pfid'])) && (perm_binPerm::havePower('edit2',$pfolder['perm1']) || (perm_binPerm::havePower('edit1',$pfolder['perm1']) && $icoarr['uid']==$_G['uid']))){//上级目录
					$ismoderator=1;	
				}
				if($ismoderator){
					C::t('folder')->update($icoarr['fid'],array('perm'=>intval($_GET['perm'])));
					$ret=1;
				}
			}
			if($ret){
				$return=C::t('icos')->fetch_by_icoid($icoid);
				$return['msg']='success';
			}
		}else{
			$name=io_dzz::name_filter(trim($_GET['name']));
			if($icoarr['name']!=$name){
				$return=IO::rename($icoid,$name);
				if(empty($return['error'])){
					if($return['type']=='folder') $return['folderdata']=IO::getFolderByIcosdata($return);
					$return['msg']='success';
					$return['oicoid']=$icoarr['icoid'];
				}
			}
		}
		showmessage('do_success',$refer.'',$return,array());
	}else{
		
		$info=array();
		
		$info['icon']=$icoarr['img']?$icoarr['img']:geticonfromext($icoarr['ext'],$icoarr['type']);
		$info['name']=getstr($icoarr['name'],30);
		$info['ftype']=$icoarr['ftype'];
		
		//获取路径
		if($icoarr['bz']){
			$bzarr=explode(':',$icoarr['path']);
			$info['path']=$icoarr['path'];
			$info['size']=lang('template','property_info_size',array('fsize'=>$icoarr['size']>0?formatsize($icoarr['size']):$icoarr['size'],'size'=>$icoarr['size']));
		}else{
			
			$arr=getPathByPfid($icoarr['pfid']);
			$patharr=array();
			while($arr){
				$patharr[]=array_pop($arr);
			}
			$info['path']=implode('/',$patharr).'/'.$icoarr['name'];
			
			switch($icoarr['type']){
				case 'shortcut':
					$icoarr['bz']=$icoarr['tdata']['bz'];
					$icoarr['path']=$icoarr['tdata']['path'];
					$info['path']=implode('/',$patharr).'/'.$icoarr['name'];
					if($icoarr['bz']){
						$info['path']=$icoarr['path'];
					}else{
						$arr1=getPathByPfid($icoarr['tdata']['pfid']);
						
						$patharr1=array();
						while($arr1){
							$patharr1[]=array_pop($arr1);
						}
						$info['path']=implode('/',$patharr1).'/'.$icoarr['tdata']['name'];
					}
					$info['size']='-';
					if($icoarr['tdata']['type']=='folder'){
						$info['icon']='dzz/images/default/system/folder.png';
					}
					break;
				case 'folder':
					if($icoarr['gid']>0){
						if((C::t('organization_admin')->ismoderator_by_uid_orgid($icoarr['gid'],$_G['uid']) || $_G['adminid']==1) ){//是部门管理员或系统管理员
							$ismoderator=1;	
						}elseif(($pfolder=C::t('folder')->fetch_by_fid($icoarr['pfid'])) && (perm_binPerm::havePower('edit2',$pfolder['perm1']) || (perm_binPerm::havePower('edit1',$pfolder['perm1']) && $icoarr['uid']==$_G['uid']))){//上级目录
							$ismoderator=1;	
						}
						if($ismoderator){
							$folder=C::t('folder')->fetch($icoarr['oid']);
							$permtitle=perm_binPerm::getGroupTitleByPower($icoarr['perm1']);
							$permarr=perm_binPerm::groupPowerPack();
						}
					}
					
					$info['icon']=$icoarr['img']?$icoarr['img']:'dzz/images/default/system/folder.png';
					$contains=getContainsByFid($icoarr['oid']);
					$info['size']=lang('template','property_info_size',array('fsize'=>formatsize($contains['size']),'size'=>$contains['size']));
					$info['contain']=lang('template','property_info_contain',array('filenum'=>$contains['contain'][0],'foldernum'=>$contains['contain'][1]));
					break;
				case 'dzzdoc':
					$info['path']=implode('/',$patharr).'/'.$icoarr['name'];
					$info['size']=lang('template','property_info_size',array('fsize'=>formatsize($icoarr['size']),'size'=>$icoarr['size']));
					break;
				case 'link':
					$info['path']=implode('/',$patharr).'/'.$icoarr['name'];
					$info['size']='-';
					break;
				case 'video':
					$info['path']=implode('/',$patharr).'/'.$icoarr['name'];
					$info['size']='-';
					break;
				case 'app':
					$info['path']=implode('/',$patharr).'/'.$icoarr['name'];
					$info['size']='-';
					break;	
						
				default:
					$info['path']=implode('/',$patharr).'/'.$icoarr['name'];
					$info['size']=lang('template','property_info_size',array('fsize'=>formatsize($icoarr['size']),'size'=>$icoarr['size']));
					
			}
		}
		$info['username']=$icoarr['username'];
		$info['uid']=$icoarr['uid'];
		$info['fdateline']=($icoarr['fdateline']);
	}
}elseif($do=='chmod'){

	$path=rawurldecode($_GET['path']);
	if(submitcheck('chmodsubmit')){
		$son=intval($_GET['son']);
		$chmod=$_GET['chmod'];
		
		$mod='0'.array_sum(array($chmod[8]*4,$chmod[7]*2,$chmod[6]*1)).array_sum(array($chmod[5]*4,$chmod[4]*2,$chmod[3]*1)).array_sum(array($chmod[2]*4,$chmod[1]*2,$chmod[0]*1));
		if($return=IO::chmod($path,$mod,$son)){
			if($return['error']) showmessage($return['error']);
			showmessage('do_success',dreferer(),array(),array('showdialog'=>1, 'showmsg' => true, 'closetime' => 1));
		}
		showmessage('权限修改失败,Windows服务器不支持此操作');
	}else{
		$meta=IO::getMeta($path);
		$meta['name']=getstr($meta['name'],30);
		list($owner,$group,$comm)=str_split($meta['mod'],1);
		$owner=sprintf("%b",$owner);
		$comm=sprintf("%b",$comm);
		$group=sprintf("%b",$group);
		$chmod=array();
		for($i=0;$i<3;$i++){
			if($owner[$i]) $chmod[8-$i]=$owner[$i];
			else $chmod[8-$i]=0;
			if($group[$i]) $chmod[5-$i]=$group[$i];
			else $chmod[5-$i]=0;
			if($comm[$i]) $chmod[2-$i]=$comm[$i];
			else $chmod[2-$i]=0;
		}
		krsort($chmod);
	}
	
}elseif($do=='getColorCss'){
	$color=rawurldecode(trim($_GET['color']));
	$class=trim($_GET['css']);
	$background='';
	if(strpos($color,'#')===0 || strpos(strtolower($color),'rgb')){
		$background=$color;
	}else{
		$background="url($color)";
	}
	$content=file_get_contents(DZZ_ROOT.'./dzz/styles/thame/'.$class.'/color.css');
	$content=preg_replace("/_BACKGROUND_/i",$background,$content);
	$content=preg_replace("/_CLASS_/i",$class,$content);
	@header('Content-Type: text/css');
	@ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
	echo $content;
	@flush();@ob_flush();
	exit();
}

include template('system_ajax');

?>
