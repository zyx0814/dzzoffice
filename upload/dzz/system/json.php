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

error_reporting(E_ERROR);

$space = dzzgetspace($_G['uid']);
$space['self']=intval($space['self']);
	
if($_GET['do']=='checklogin'){
	$uid=intval($_GET['uid']);
	if($uid!=$_G['uid']) $change_login_status=1;
	else $change_login_status=0;
	echo json_encode(array('needfresh'=>$change_login_status));


}elseif($_GET['do']=='save'){
	if(!$_G['uid']){
		$arr=array();
		$arr['msg']= lang('no_privilege');
		echo json_encode($arr);
		exit();
	}
	$arr=array('msg'=>'success');
	
	$imconfig=array();
	if(isset($_GET['imstyle'])){
		$imconfig['style']=trim(diconv($_GET['imstyle'],'UTF-8'));
	}
	if(isset($_GET['imstate'])){
		$imconfig['state']=trim($_GET['imstate']);
	}
	if(isset($_GET['imsound'])){
		$imconfig['sound']=intval($_GET['imsound']);
	}
	if($imconfig){
		if(DB::result_first("select COUNT(*) from ".DB::table('dim_config')." where uid='{$_G[uid]}'")){
			DB::update('dim_config',$imconfig,"uid='{$_G[uid]}'");
		}else{
			$imconfig['uid']=$_G['uid'];
			DB::insert('dim_config',$imconfig);
		}
	}
		
	$setarr=array();
	//if($_GET['current']){$setarr['current']=trim($_GET['current']);}
	if($_GET['thame']){
		 $setarr['thame']=trim($_GET['thame']);
		 $setarr['custom_backimg']=trim($_GET['custom_backimg']);
		 $setarr['custom_url']=trim($_GET['custom_url']);
		 $setarr['custom_btype']=trim($_GET['custom_btype']);
		 $setarr['custom_color']=trim($_GET['custom_color']);
		 $setarr['uid']=intval($_G['uid']);
		 DB::insert('user_thame',($setarr),0,1);
	}
	
	$data=array();
	if(isset($_GET['applist'])){
		$data['applist']=implode(',',$_GET['applist']);
	}
	if(isset($_GET['noticebanlist'])){
		$data['noticebanlist']=implode(',',$_GET['noticebanlist']);
	}
	if($data) C::t('user_field')->update($_G['uid'],$data);
	echo json_encode($arr);
	exit();
}else{
	@include DZZ_ROOT.'./core/core_version.php';
	$arr=array();
	//$arr['start']=microtime(true);
	
	$data=array();
	$data['version']=CORE_VERSION;
	$data['release']=CORE_RELEASE;
	//获取用户桌面的设置,没有则初始化,存在则检查默认
	$config=array();
	if(!($config=C::t('user_field')->fetch($_G['uid']))){
		$config=dzz_desktop_init();//初始化用户桌面
		$space=dzzgetspace($_G['uid']);
	}else{
		$config=dzz_check_default();
	}
	//$arr['config']=microtime(true);
	
	if($_G['setting']['upgrade']) $space['upgrade']=1;
	else $space['upgrade']=0;
	
	//获取打开方式
	$data['extopen']['all']=C::t('app_open')->fetch_all_ext();
	$data['extopen']['ext']=C::t('app_open')->fetch_all_orderby_ext($_G['uid'],$data['extopen']['all']);
	$data['extopen']['user']=C::t('app_open_default')->fetch_all_by_uid($_G['uid']);
	//获取用户的默认打开方式
	$data['extopen']['userdefault']=C::t('app_open_default')->fetch_all_by_uid($_G['uid']);
	//图标排列方式
	if($_G['setting']['dzz_iconview']){
		$iconview=$_G['setting']['iconview'];
	}else{
		$iconview=C::t('iconview')->fetch_all();
	}
	
	$data['iconview']=$iconview;
	//$arr['extopen']=microtime(true);
	
	//获取系统桌面设置信息
	$screenlist=$navids=array();
	$icosdata=$icoids=$folderids=$fids=$userids=$appids=array();
	$data['noticebanlist']=$config['noticebanlist']?explode(',',$config['noticebanlist']):array();
	$space=array_merge($config,$space);
	if(!$_G['uid'] && is_array($_G['setting']['desktop_default']))	$space=array_merge($space,$_G['setting']['desktop_default']);
	
	//$config['fid']=$space['typefid']['desktop'];
	//开始菜单应用列表
	$applist=$config['applist']?explode(',',$config['applist']):array();
	//桌面icoid列表
	$screenlist=$config['screenlist']?explode(',',$config['screenlist']):array();
	//任务栏icoid列表
	$docklist=$config['docklist']?explode(',',$config['docklist']):array();
	
	$fidtype=array_flip($space['typefid']);
	$space['fidtype']=$fidtype;
	
	////获取用户桌面图标数据
	$fids=array($space['typefid']['desktop'],$space['typefid']['dock']);
	$folderids=$fids;
	$icos_all=C::t('icos')->fetch_all_by_pfid($fids);
	/*if($icos_home=C::t('icos')->fetch_by_icoid($space['homeicoid'])){
		$icos_all[$icos_home['icoid']]=$icos_home;
	}*/
	//$arr['icos_all']=microtime(true);
	//获取默认打开的icoid数据
	if($_GET['openid']){
		$openarr=explode(':',$_GET['openid']);
		$openicoids=array();
		foreach($openarr as $val){
			if(strpos($val,'icoid_')===0){
				$openicoids[]=intval(str_replace('icoid_','',$val));
			}
		}
		$openicoids=array_unique($openicoids);
		foreach($openicoids as $icoid){
			$arr=C::t('icos')->fetch_by_icoid($icoid);
			if($arr['pfid']>0){
				if($parents=C::t('icos')->fetch_parents_by_pfid($arr['pfid'])){
					$icos_all=array_merge($icos_all,$parents);
				}
			}
			$icos_all[$arr['icoid']]=$arr;
		}
	}
	$shortcutFolderdata=array();
	foreach($icos_all as $value){
		if($value['type']=='folder'){
			 $folderids[]=$value['oid'];
		}elseif($value['type']=='app'){
			 $applist[]=$value['oid']; 
		}elseif($value['type']=='user') {
			$userids[]=$value['oid']; 
		}elseif($value['type']=='shortcut'){
				foreach($value['tdata']['folderarr'] as $key=>$value1){
					 $shortcutFolderdata[$key]=$value1;
				}
				$icosdata[$value['tdata']['icoid']]=$value['tdata'];
		}
		
		//if(!$value['ext'] && $start =strrchr($value['url'], '.')) $value['ext']=strtolower(substr($start, 1, 10));
		//为了安全原因，不加载文件的实际存放位置
		unset($value['attachment']);
		$icosdata[$value['icoid']]=$value;
		//标记为删除的应用只在回收站显示
		if($value['isdelete']) continue;
		if($value['type']=='app' && $value['isshow']<1){
			continue;
		}
		
		//根据ico的desktop字段判断此ico存在于哪个容器，如果容器ids中不包含则添加；
			if($fidtype[$value['pfid']]=='dock'){
				$docklist_1[]=$value['icoid'];
		
			}elseif($fidtype[$value['pfid']]=='desktop'){
				$screenlist_1[]=$value['icoid'];
			}
			$icoids[]=$value['icoid'];
	}
	
	//应用数据
	$appdata=array();
	$appdata=C::t('app_market')->fetch_all_by_appid($applist);
	//$arr['appdata']=microtime(true);
	$applist_1=array();
	foreach($appdata as $value){
		if($value['isshow']<1) continue;
		if($value['available']<1) continue;
		$applist_1[]=$value['appid'];
	}
	
	/*//处理applist
	$data['applist']=$apparr=array();
	foreach($applist as $key =>$value){
		if(in_array($value,$applist_1)){
			$apparr[$value]=$value;
		}
	}
	
	foreach($applist_1 as $key=>$value){
		if(!in_array($value,$apparr)){
			$apparr[$value]=$value;
		}
	}*/
	$data['applist']=array_values($applist_1);
	//处理screenlist
	$data['screenlist']=array();
	foreach($screenlist as $key =>$value){
		if(in_array($value,$screenlist_1)){
			$data['screenlist'][]=$value;
		}
	}
	foreach($screenlist_1 as $key =>$value){
		if(!in_array($value,$data['screenlist'])){
			$data['screenlist'][]=$value;
		}
	}
	
	//处理docklist
	$data['docklist']=array();
	foreach($docklist as $key =>$value){
		if(in_array($value,$docklist_1)){
			$data['docklist'][]=$value;
		}
	}
	foreach($docklist_1 as $key =>$value){
		if(!in_array($value,$data['docklist'])){
			$data['docklist'][]=$value;
		}
	}
	C::t('user_field')->update($_G['uid'],array('screenlist'=>implode(',',$data['screenlist']),'docklist'=>implode(',',$data['docklist'])));
	
	
	//目录数据
	$folderdata=array();
	
	//默认目录全部调用
	foreach($space['typefid'] as $fid){
		$folderids[]=$fid;
	}
	//机构目录全部调用
	foreach(DB::fetch_all("select fid from %t where pfid=0 and gid>0 and flag='organization'",array('folder')) as $value){
		$folderids[]=$value['fid'];
	}
	
	$folderids=array_unique($folderids);
	if($folderids){
		$folderdata=C::t('folder')->fetch_all_by_fid($folderids);
	}
	
	//$arr['folderdata']=microtime(true);
	//获取云盘目录数据
	$pandata=C::t('connect')->fetch_all_folderdata($_G['uid']);
	
	foreach($pandata as $key =>$value){
		$folderdata[$key]=$value;
	}
	//快捷方式目录数据
	foreach($shortcutFolderdata as $key=>$value1){
		if(empty($folderdata[$key])) $folderdata[$key]=$value1;
	}
	
	$data['formhash']=$_G['formhash'];
	
	//$data['navids']=array_merge($navids_0,$navids_u);
	
	
	$data['sourceids']=array(
							'icos'=>$icoids,
						);
	$data['sourcedata']=array(
							'icos'=>$icosdata?$icosdata:array(),
							'folder'=>$folderdata?$folderdata:array(),
							'app'=>$appdata?$appdata:array(),
							);
	$space['attachextensions']=$space['attachextensions']?explode(',',$space['attachextensions']):array();
	
	$data['myspace']=$data['space']=$space;
	//$arr['end']=microtime(true);
	//$data['microtime']=$arr;
	echo json_encode($data);
	exit();
}




?>
