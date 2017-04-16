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
	include template('common/header_reload');
	echo "<script type=\"text/javascript\">";
	echo "top._login.logging();";
	echo "win.Close();";
	echo "</script>";	
	include template('common/footer_reload');
	exit();
}

$uid =$_G['uid'];
$space = dzzgetspace($_G['uid']);
$space['self']=intval($space['self']);
$refer=dreferer();	

if($do=='upload'){
	$container=trim($_GET['container']);
	$bz=trim($_GET['bz']);
	//$pfid=getFidByContainer($container);
	require_once dzz_libfile('class/UploadHandler');
	//$gid=getGidByContainer($container);
	//上传类型
	 $allowedExtensions = $space['attachextensions']?explode(',',$space['attachextensions']):array();
	// max file size in bytes
	$sizeLimit =($space['maxattachsize']);
	
	$options=array('accept_file_types'=>$allowedExtensions?("/(\.|\/)(".implode('|',$allowedExtensions).")$/i"):"/.+$/i",
					'max_file_size'=>$sizeLimit?$sizeLimit:null,
					'upload_dir' =>$_G['setting']['attachdir'].'cache/',
					'upload_url' => $_G['setting']['attachurl'].'cache/',
				  );
					
	$upload_handler = new UploadHandler($options);
	exit();
}elseif($do=='createShortCut'){ //创建快捷方式到指定的目录
	$path=rawurldecode($_GET['path']);
	$pfid=intval($_GET['pfid']);
	
	$sperm=intval($_GET['sperm']);
	
	$tdata=C::t('source_shortcut')->getDataByPath($path);
	if($tdata['error']){
		echo json_encode(array('error'=>lang('failed_create_shortcut')));exit();
	}
	$flag=trim($tdata['flag']);
	$shortcut=array(
					'path'=>$path,
					'data'=>serialize($tdata),
					'bz'=>($tdata['bz'] && $tdata['bz']!='dzz')?$tdata['bz']:$tdata['type'].'_'.$tdata['oid']
					);
	
	if($cutid=C::t('source_shortcut')->insert($shortcut,1)){
		$icoarr=array(
					'uid'=>$_G['uid'],
					'username'=>$_G['username'],
					'oid'=>$cutid,
					'name'=>$tdata['name'],
					'flag'=>$flag,
					'type'=>'shortcut',
					'dateline'=>$_G['timestamp'],
					'pfid'=>$pfid,
					'gid'=>0,
					'ext'=>$tdata['ext'],
					'size'=>$tdata['size'],
				);
	
	
		if($icoarr['icoid']=DB::insert('icos',($icoarr),1)){
			//$icoarr=array_merge($sourcedata,$icoarr);
			$icoarr['path']=$icoarr['icoid'];
		    $icoarr['dpath']=dzzencode($icoarr['icoid']);
			$icoarr['url']=$app['url'];
			$icoarr['tdata']=$tdata;
			addtoconfig($icoarr);
			$icoarr['img']=$tdata['img'];
			$icoarr['ttype']=$tdata['type'];
			$icoarr['bz']='';
			$icoarr['fsize']=formatsize($icoarr['size']);
			$icoarr['ftype']=getFileTypeName($icoarr['type'],$icoarr['ext']);
			$icoarr['fdateline']=dgmdate($icoarr['dateline']);
			$icoarr['sperm']=perm_FileSPerm::typePower($icoarr['type'],'');
			echo json_encode($icoarr);exit();
		}
	}
	echo json_encode(array('error'=>lang('add_shortcuts_fail')));exit();
	
}elseif($do=='applinkto'){ //添加应用快捷方式到特定容器
	$appid=intval($_GET['appid']);
	$pfid=intval($_GET['pfid']);
	if(!$app=C::t('app_market')->fetch_by_appid($appid)){
		echo json_encode(array('error'=>lang('app_longer_exists')));exit();
	}
	$icoarr=array(
					'uid'=>$_G['uid'],
					'username'=>$_G['username'],
					'oid'=>$app['appid'],
					'name'=>$app['title'],
					'flag'=>'',
					'type'=>'app',
					'dateline'=>$_G['timestamp'],
					'pfid'=>$pfid,
					'gid'=>0,
					'ext'=>'',
					'size'=>0,
					
				);
	if($icoid=DB::result_first("select icoid from %t where oid=%d and uid=%d and type='app'",array('icos',$icoarr['oid'],$_G['uid']))){
		C::t('icos')->update($icoid,$icoarr);	
		$icoarr['icoid']=$icoid;
		$icoarr['path']=$icoid;
		$icoarr['dpath']=dzzencode($icoid);
		$icoarr['url']=$app['url'];
		$icoarr['img']=$app['appico'];
		$icoarr['bz']='';
		$icoarr=array_merge($app,$icoarr);
		$icoarr['fsize']=formatsize($icoarr['size']);
		$icoarr['ftype']=getFileTypeName($icoarr['type'],$icoarr['ext']);
		$icoarr['fdateline']=dgmdate($icoarr['dateline']);
		echo json_encode($icoarr);exit();
	}elseif($icoarr['icoid']=DB::insert('icos',($icoarr),1)){
		//$icoarr=array_merge($sourcedata,$icoarr);
		$icoarr['url']=$app['url'];
		$icoarr=array_merge($app,$icoarr);
		addtoconfig($icoarr);
		$icoarr['img']=$app['appico'];
		$icoarr['bz']='';
		$icoarr['fsize']=formatsize($icoarr['size']);
		$icoarr['ftype']=getFileTypeName($icoarr['type'],$icoarr['ext']);
		$icoarr['fdateline']=dgmdate($icoarr['dateline']);
		$icoarr['sperm']=perm_FileSPerm::typePower($icoarr['type'],'');
		echo json_encode($icoarr);exit();
	}else{
		echo json_encode(array('error'=>lang('add_shortcuts_fail')));exit();
	}
}elseif($do=='appuninstall'){ //删除用户应用
	$appid=intval($_GET['appid']);
	$return=array();
	if($icoid=DB::result_first("select icoid from %t where oid=%d and uid=%d and type='app'",array('icos',$appid,$_G['uid']))){
		C::t('icos')->delete($icoid);
		$return['icoid']=$icoid;
	}
	$applist=DB::result_first("select applist from %t where uid=%d",array('user_field',$_G['uid']));
	$applist_arr=explode(',',$applist);
	$applist=array();
	foreach($applist_arr as $value){
		if($value!=$appid) $applist[]=$value;
	}
	C::t('app_user')->delete_by_uid_appid($_G['uid'],$appid);
	if(C::t('user_field')->update($_G['uid'],array('applist'=>implode(',',$applist)))){
		echo json_encode($return);exit();
	}else{
		echo json_encode(array('error'=>lang('delete_unsuccess')));exit();
	}

}elseif($do=='rename'){
	$path=dzzdecode($_GET['path']);
	$text=str_replace('...','',getstr(io_dzz::name_filter($_GET['text']),80));
	
	$ret=IO::rename($path,$text);
	exit(json_encode($ret));
}elseif($do=='NewIco'){
	//error_reporting(E_ALL);
	$type=trim($_GET['type']);
	$path=trim($_GET['path']);
	$filename='';
	$bz=getBzByPath($path);
	switch ($type){
		case 'NewTxt':
		 	$filename=lang('new_txt').'.txt';
			if(!perm_check::checkperm_Container($path,'newtype',$bz)){
				exit(json_encode(array('error'=>lang('privilege'))));
			}
			$content=' ';
			break;
		case 'NewDzzDoc':
		 	$filename=lang('new_dzzdoc').'.dzzdoc';
			if(!perm_check::checkperm_Container($path,'dzzdoc',$bz)){
				exit(json_encode(array('error'=>lang('privilege'))));
			}
			$content=' ';
			break;
		case 'newdoc':
		 	$filename=lang('new_word').'.docx';
			if(!perm_check::checkperm_Container($path,'newtype',$bz)){
				exit(json_encode(array('error'=>lang('privilege'))));
			}
			$content=file_get_contents(DZZ_ROOT.'./dzz/images/newfile/word.docx');
			break;
		case 'newexcel':
		 	$filename=lang('new_excel').'.xlsx';
			if(!perm_check::checkperm_Container($path,'newtype',$bz)){
				exit(json_encode(array('error'=>lang('privilege'))));
			}
			$content=file_get_contents(DZZ_ROOT.'./dzz/images/newfile/excel.xlsx');
			break;
		case 'newpowerpoint':
		 	$filename=lang('new_PowerPoint').'.pptx';
			if(!perm_check::checkperm_Container($path,'newtype',$bz)){
				exit(json_encode(array('error'=>lang('privilege'))));
			}
			$content=file_get_contents(DZZ_ROOT.'./dzz/images/newfile/ppt.pptx');
			break;
	}
	
	if($arr=IO::upload_by_content($content,$path,$filename)){
		if($arr['error']){
		}else{
			$arr['msg']='success';
		}
	}else{
		$arr=array();
		$arr['error']=lang('new_failure');
		
	}
	echo json_encode($arr);
	exit();
}elseif($do=='deleteIco'){
	$arr=array();
	$names=array();
	$i=0;
	$icoids=$_GET['icoids'];
	$bz=trim($_GET['bz']);
	foreach($icoids as $icoid){
		$icoid=dzzdecode($icoid);
		if(empty($icoid)){
			continue;
		}
		if(strpos($icoid,'../')!==false){
			$arr['msg'][$return['icoid']]=lang('illegal_calls');
		}else{
			$return=IO::Delete($icoid);
			if(!$return['error']){
				//处理数据
				$arr['sucessicoids'][$return['icoid']]=$return['icoid'];
				$arr['msg'][$return['icoid']]='success';
				$i++;
			}else{
				$arr['msg'][$return['icoid']]=$return['error'];
			}
		}
	}
	echo json_encode($arr);
	exit();

}elseif($do=='emptyFolder'){
	$arr=array();
	$fid=intval($_GET['fid']);
	if($return = C::t('folder')->empty_by_fid($fid)){
		if(!isset($return['error'])){
			$arr['msg']='success';
		}else{
			$arr['error']=$return['error'];
		}
	}
	echo json_encode($arr);
	exit();
}elseif($do=='restore'){
	$icoids=explode(',',trim($_GET['icoid']));
	foreach(C::t('icos')->fetch_all($icoids) as $value){
		if(C::t('icos')->update($value['icoid'],array('isdelete'=>0))){
			addtoconfig($value);
		}
	}
	echo json_encode(array('msg'=>'success'));
	exit();


}elseif($do=='linktodesktop'){
	if(!$_G['uid']){
		exit(json_encode(array('error'=>lang('you_not_recorded'))));
	}
	$data=array();
	$link=(trim($_GET['link']));
	//检查网址合法性
	if(!preg_match("/^(http|ftp|https|mms)\:\/\//i", $link)){
		$link='http://'.$link;
	}
	if(!preg_match("/^(http|ftp|https|mms)\:\/\/.{4,300}$/i", $link)){
			$data['error']=lang('href_illegal');
			echo json_encode($data);
			exit();
	}
	$pfid=DB::result_first("select fid from %t where uid=%d and flag='desktop'",array('folder',$_G['uid']));
	if($data=io_dzz::linktourl($link,$pfid)){
		echo json_encode($data);
		exit();
	}else{
		$data['error']=lang('added_desktop_failure');
		echo json_encode($data);
		exit();
	}
}elseif($do=='newlink'){
	$link=(trim($_GET['link']));
	//$link = dhtmlspecialchars(trim($_POST['link']));
	$pfid=intval($_GET['path']);
	//判断有没有权限添加
	$data=array();
	//检查网址合法性
	if(!preg_match("/^(http|ftp|https|mms)\:\/\//i", $link)){
		$link='http://'.preg_replace("/^(http|ftp|https|mms)\:\/\//i",'',$link);
	}
	if(!preg_match("/^(http|ftp|https|mms)\:\/\/.{4,300}$/i", $link)){
			$data['error']=lang('href_illegal');
			echo json_encode($data);
			exit();
	}
	// 首先判断网址是否存在；
	$ext=strtolower(substr(strrchr($link, '.'), 1, 10));
	//static $videoext  = array('swf', 'flv');
	//static $videohost  = array('tudou.com', 'youku.com','56.com','ku6.com');
	$isimage= in_array(strtoupper($ext), $imageexts) ? 1 : 0;
	$ismusic= /*in_array(strtoupper($ext), MUSICEXTS) ? 1 :*/ 0;
	//if($link!= str_replace(array('player.youku.com/player.php/sid/','tudou.com/v/','player.ku6.com/refer/'), '', $link)) $isvideo=1;
	//是图片时处理
	
	if($isimage){
		if(!perm_check::checkperm_Container($pfid,'newtype')){
			
			$data['error']=lang('target_not_accept_image');
			echo json_encode($data);
			exit();
		}
		if($data=io_dzz::linktoimage($link,$pfid)){
				echo json_encode($data);
				exit();
			
			showmessage('do_success',$refer.'',$data,array('showdialog'=>1, 'showmsg' => true, 'closetime' => 1));
		}
	}elseif($ismusic){
		if(!perm_check::checkperm_Container($pfid,'newtype')){
			
			$data['error']=lang('target_not_accept_music');
			echo json_encode($data);
			exit();
		}
		if($data=io_dzz::linktomusic($link,$pfid)){
			echo json_encode($data);
			exit();
			showmessage('do_success',$refer.'',$data,array('showdialog'=>1, 'showmsg' => true, 'closetime' => 1));
		}
	}elseif($data=io_dzz::linktovideo($link,$pfid)){//试图作为视频处理
		//作为网址处理
		if(!perm_check::checkperm_Container($pfid,'video')){
			$data['error']=lang('target_not_accept_link');
			echo json_encode($data);
			exit();
		}
		echo json_encode($data);
		exit();
	}else{
		if(!perm_check::checkperm_Container($pfid,'link')){
			$data['error']=lang('target_not_accept_link');
			echo json_encode($data);
			exit();
		}
		if($data=io_dzz::linktourl($link,$pfid)){
			
			echo json_encode($data);
			exit();
			
			showmessage('do_success',$refer.'',$data,array('showdialog'=>1, 'showmsg' => true, 'closetime' => 1));
		}else{
			
			$data['error']=lang('network_error');
			echo json_encode($data);
			exit();
			
		}
	}
}
?>
