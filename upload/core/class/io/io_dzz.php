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
@set_time_limit(0);
@ini_set('max_execution_time',0);
class io_dzz extends io_api
{
	public function listFiles($icoid,$by='time',$asc='DESC',$limit=0,$force=0){ 
		$data=array();	
		$icoarr=C::t('icos')->fetch($icoid);	
		switch($by){
			case 'name':
				$orderby='name';
				//$order='';
				break;
			case 'size':
				$orderby='size';
				//$order='DESC';
				break;
			case 'type':
				$orderby=array('type','ext');
				//$order='';
				break;
			case 'time':
				$orderby='dateline';
				//$order='DESC';
				break;
			
		}
		foreach(C::t('icos')->fetch_all_by_pfid($icoarr['oid'],'',0,$orderby,$order) as $value){
			$data[$value['icoid']]=$value;
		}
		return $data;
	}
	public function getContains($fid,$suborg=false,$contains=array('size'=>0,'contain'=>array(0,0))){
		if(!$folder=C::t('folder')->fetch($fid)) return $contains;
		$fids[]=$fid;
		if($suborg && ($folder['flag']=='organization')){
			foreach(DB::fetch_all("select fid from %t where flag='organization' and pfid=%d and isdelete<1",array('folder',$fid)) as $value){
				$fids[]=$value['fid'];
			}
		}
		if(empty($folder['default']) && $folder['flag']!='folder' && $folder['flag']!='organization'){//没有生成icos表的 单独查出来
			foreach(DB::fetch_all("select fid,fname from %t where `default`='' and flag!='folder' and pfid=%d and isdelete<1",array('folder',$fid)) as $value){
				$fids[]=$value['fid'];
			}
		}
		foreach($fids as $fid){
			foreach(C::t('icos')->fetch_all_by_pfid($fid) as $value){
				if($value['type']=='folder'){
					$contains=self::getContains($value['oid'],false,$contains);
					$contains['contain'][1]+=1;
				}else{
					$contains['size']+=$value['size'];
					$contains['contain'][0]+=1;
				}
			}
		}
		return $contains;
	}
	/**
	 * 获取空间配额信息
	 * @return string
	 */
	public function MoveToSpace($path,$attach){
		global $_G;
		$obz=io_remote::getBzByRemoteid($attach['remote']);
		
		if($obz=='dzz'){
			return array('error'=>lang('same_storage_area'));
		}else{
			$url=IO::getFileUri($obz.'/'.$attach['attachment']) ;
			if(is_array($url)) return array('error'=>$url['error']);
			$target=$_G['setting']['attachdir'].'./'.$attach['attachment'];
			$targetpath=dirname($target);
			dmkdir($targetpath);
			try{
				if(file_put_contents($target,fopen($url,'rb'))===false){
					return array('error'=>lang('error_occurred_written_local'));
				}
			}catch(Exception $e){
				return array('error'=>$e->getMessage());
			}
			if(md5_file($target)!=$attach['md5']){
				return array('error'=>lang('file_transfer_errors'));
			}
		}
		return true;
		
	}
	public function rename($icoid,$text){
		//查找当前目录下是否有同名文件
		$icoarr=C::t('icos')->fetch_by_icoid($icoid);
		if($icoarr['name']!=$text && ($ricoid=io_dzz::getRepeatIDByName($text,$icoarr['pfid'],($icoarr['type']=='folder')?true:false))){//如果目录下有同名文件
			return array('error'=>lang('filename_already_exists'));
		}
		if(!$arr=C::t('icos')->update_by_name($icoid,$text)){
			return array('error'=>'Not modified!');
		}
		$icoarr['name']=$text;
		return $icoarr;
	}
	public function parsePath($path){
		return $path;
	}
	//根据路径获取目录树的数据；
	function getFolderDatasByPath($fid){ 
		$fidarr=getTopFid($fid);
		$folderarr=array();
		foreach($fidarr as $fid){
			$folderarr[$fid]=C::t('folder')->fetch_by_fid($fid);
		}
		return $folderarr;
	}
	//获取文件流地址
	public function getStream($path,$fop=''){
		global $_G;
		if(strpos($path,'attach::')===0){
			$attach=C::t('attachment')->fetch(intval(str_replace('attach::','',$path)));
			$bz=io_remote::getBzByRemoteid($attach['remote']);
			if($bz=='dzz'){
				if($icoarr['type']=='video' || $icoarr['type']=='dzzdoc' || $icoarr['type']=='link'){
					return $icoarr['url'];
				}
				return $_G['setting']['attachdir'].$attach['attachment'];
			}else{
				return IO::getStream($bz.'/'.$attach['attachment'],$fop);
			}
		}elseif(strpos($path,'dzz::')===0){
			if(strpos($icoid,'../')!==false) return '';
			return $_G['setting']['attachdir'].preg_replace("/^dzz::/",'',$path);
		}elseif(strpos($path,'TMP::')===0){
			$tmp=str_replace('\\','/',sys_get_temp_dir());
			return str_replace('TMP::',$tmp.'/',$path);
		}elseif(is_numeric($path)){
			$icoarr=C::t('icos')->fetch_by_icoid($path);
			$bz=io_remote::getBzByRemoteid($icoarr['remote']);
			if($bz=='dzz'){
				if($icoarr['type']=='video' || $icoarr['type']=='dzzdoc' || $icoarr['type']=='link'){
					return $icoarr['url'];
				}
				return $_G['setting']['attachdir'].$icoarr['attachment'];
			}else{
				return IO::getStream($bz.'/'.$icoarr['attachment'],$fop);
			}
		}else{
			return $path;
		}
		return '';
	}
	//获取文件的真实地址
	public function getFileUri($path,$fop){
		global $_G;
		if(strpos($path,'attach::')===0){
			$attach=C::t('attachment')->fetch(intval(str_replace('attach::','',$path)));
			$bz=io_remote::getBzByRemoteid($attach['remote']);
			if($bz=='dzz'){
				return $_G['siteurl'].$_G['setting']['attachurl'].$attach['attachment'];
			}else{
				return IO::getFileUri($bz.'/'.$attach['attachment'],$fop);
			}
			return IO::getFileUri($path);
		}elseif(strpos($path,'dzz::')===0){
			if(strpos($icoid,'../')!==false) return '';
			return $_G['siteurl'].$_G['setting']['attachurl'].preg_replace("/^dzz::/",'',$path);
		}elseif(strpos($path,'TMP::')===0){
			return $_G['siteurl'].'index.php?mod=io&op=getStream&path='.dzzencode($path);
		}elseif(is_numeric($path)){
			$icoarr=C::t('icos')->fetch_by_icoid($path);
			$bz=io_remote::getBzByRemoteid($icoarr['remote']);
			if($bz=='dzz'){
				if($icoarr['type']=='video' || $icoarr['type']=='dzzdoc' || $icoarr['type']=='link'){
					return $icoarr['url'];
				}
				return $_G['siteurl'].$_G['setting']['attachurl'].$icoarr['attachment'];
			}else{
				return IO::getFileUri($bz.'/'.$icoarr['attachment'],$fop);
			}
		}
		return '';
	}
	//获取文件内容
	public function getFileContent($path){
		$url=self::getStream($path);
		return file_get_contents($url);
	}
	
	public function deleteThumb($icoid,$width=0,$height=0){
		global $_G;
		$data=C::t('icos')->fetch_by_icoid($path);
		$imgcachePath='./imgcache/';
		$cachepath=str_replace('//','/',str_replace(':','/',$data['attachment']));
		foreach($_G['setting']['thumbsize'] as $value){
			$target=$imgcachePath.($cachepath).'.'.$value['width'].'_'.$value['height'].'.jpeg';
			@unlink($_G['setting']['attachdir'].$target);
		}
	}
	public function createThumb($path,$size,$width=0,$height=0){
		global $_G;
		if(is_numeric($path)){
			$data=C::t('icos')->fetch_by_icoid($path);
			$bz=io_remote::getBzByRemoteid($data['remote']);
			if($bz!='dzz'){
				$path=$bz.'/'.$data['attachment'];
				return IO::createThumb($path,$size,$width,$height);
			}
		}else{
			if(strpos($path,'attach::')===0){
				$data=C::t('attachment')->fetch(intval(str_replace('attach::','',$path)));
				$bz=io_remote::getBzByRemoteid($data['remote']);
				if($bz!='dzz'){
					return IO::createThumb($bz.'/'.$data['attachment'],$size,$width,$height);
					
				}
			}elseif(strpos($path,'TMP::')===0){
				$tmp=str_replace('\\','/',sys_get_temp_dir());
			    $data=array('attachment'=>str_replace('TMP::',$tmp.'/',$path));
			
			}elseif(strpos($path,'dzz::')===0){
				$data=array('attachment'=>str_replace('dzz::','',$path));
			}else{
				
				return -2;//$path路径不正确
			}
		}
		$filepath=self::getStream($path);
		if(intval($width)<1) $width=$_G['setting']['thumbsize'][$size]['width'];
		if(intval($height)<1) $height=$_G['setting']['thumbsize'][$size]['height'];
		
		$enable_cache=true; //是否启用缓存
		$imgcachePath='imgcache/';
		$cachepath=str_replace('//','/',str_replace(':','/',$data['attachment']));
		if(!$imginfo=@getimagesize($filepath)){
			return -1; //非图片不能生成
		}
	
		if(($imginfo[0]<$width && $imginfo[1]<$height) ) {
			return 3;//小于要求尺寸，不需要生成
		}
		
		$target=$imgcachePath.($cachepath).'.'.$width.'_'.$height.'.jpeg';
		if(@getimagesize($_G['setting']['attachdir'].$target)){
			return 2;//已经存在缩略图
		}
		//生成缩略图
		include_once libfile('class/image');
		$target_attach=$_G['setting']['attachdir'].$target;
		$targetpath = dirname($target_attach);
		dmkdir($targetpath);
		$image=new image();
		if($thumb = $image->Thumb($filepath,$target,$width, $height,1)){
			return 1;//生成缩略图成功
		}else{
			return 0;//生成缩略图失败
		}
		
	}
	//获取缩略图
	public function getThumb($path,$width,$height,$original=false,$returnurl=false){
		global $_G;
		//$path:可能的值 icoid,'dzz::dzz/201401/02/wrwsdfsdfasdsf.txt'等dzzPath格式；
		
		if(is_numeric($path)){
			$data=C::t('icos')->fetch_by_icoid($path);
			$bz=io_remote::getBzByRemoteid($data['remote']);
			if($bz!='dzz'){
				$path=$bz.'/'.$data['attachment'];
				$ret=IO::getThumb($path,$width,$height,$original,$returnurl);
				if($returnurl) return $ret;
				exit();
			}
		}else{
			if(strpos($path,'attach::')===0){
				$data=C::t('attachment')->fetch(intval(str_replace('attach::','',$path)));
				$bz=io_remote::getBzByRemoteid($data['remote']);
				if($bz!='dzz'){
					$ret=IO::getThumb($bz.'/'.$data['attachment'],$width,$height,$original,$returnurl);
					if($returnurl) return $ret;
					exit();
				}
			}elseif(strpos($path,'TMP::')===0){
				$tmp=str_replace('\\','/',sys_get_temp_dir());
			    $data=array('attachment'=>str_replace('TMP::',$tmp.'/',$path));
			}elseif(strpos($path,'dzz::')===0){
					$data=array('attachment'=>str_replace('dzz::','',$path));
			}else{
				$ret=IO::getThumb($path,$width,$height,$original,$returnurl);
				if($returnurl) return $ret;
				exit();
			}
		}
		$filepath=self::getStream($path);
		$enable_cache=true; //是否启用缓存
		$quality = 80;
		$imgcachePath='imgcache/';
		$cachepath=str_replace('//','/',str_replace(':','/',$data['attachment']));
		$imginfo=@getimagesize($filepath);
		
		if($original){
			if($returnurl) return self::getFileUri($path);//$_G['setting']['attachurl'].'./'.$data['attachment'];
			$file=self::getStream($path);//$_G['setting']['attachdir'].'./'.$data['attachment'];
			$last_modified_time = filemtime($file); 
			$etag = md5_file($file); 
			header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT"); 
			header("Etag: $etag"); 
			
			if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time || 
				trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) { 
				header("HTTP/1.1 304 Not Modified"); 
				exit; 
			}
			@header('cache-control:public');  
			@header("Content-Type: " . image_type_to_mime_type($imginfo[2]));
			@ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
			@readfile($file);
			@flush(); @ob_flush();
			exit();
		}
		if(($imginfo[0]<$width && $imginfo[1]<$height) ) {
			if($returnurl) return self::getFileUri($path);
			$file=self::getStream($path);//$_G['setting']['attachdir'].'./'.$data['attachment'];
			$last_modified_time = filemtime($file); 
			$etag = md5_file($file); 
			header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT"); 
			header("Etag: $etag"); 
			
			if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time || 
				trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) { 
				header("HTTP/1.1 304 Not Modified"); 
				exit; 
			}
			@header('cache-control:public');  
			header("Content-Type: " . image_type_to_mime_type($imginfo[2]));
			@ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
			@readfile($file);
			@flush(); @ob_flush();
			exit();
		}
		$target=$imgcachePath.($cachepath).'.'.$width.'_'.$height.'.jpeg';
		if($enable_cache && @getimagesize($_G['setting']['attachdir'].$target)){
			if($returnurl) return $_G['setting']['attachurl'].'./'.$target;
			$file=$_G['setting']['attachdir'].'./'.$target;
			$last_modified_time = filemtime($file); 
			$etag = md5_file($file); 
			header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT"); 
			header("Etag: $etag"); 
			
			if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time || 
				trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) { 
				header("HTTP/1.1 304 Not Modified"); 
				exit; 
			}
			@header('cache-control:public');  
			header('Content-Type: image/JPEG');
			@ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
			@readfile($_G['setting']['attachdir'].$target);
			@flush(); @ob_flush();
			exit();
		}
		//获取缩略图
		include_once libfile('class/image');
		$target_attach=$_G['setting']['attachdir'].$target;
		$targetpath = dirname($target_attach);
		$file=self::getStream($path);
		dmkdir($targetpath);
		$image=new image();
		if($thumb = $image->Thumb($file,$target,$width, $height,1)){
			if($returnurl) return $_G['setting']['attachurl'].'./'.$target;
			$file=$target_attach;
			$last_modified_time = filemtime($file); 
			$etag = md5_file($file); 
			header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT"); 
			header("Etag: $etag"); 
			
			if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time || 
				trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) { 
				header("HTTP/1.1 304 Not Modified"); 
				exit; 
			}
			@header('cache-control:public');   
			@header('Content-Type: image/JPEG');
			@ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
			@readfile($_G['setting']['attachdir'].$target);
			@flush(); @ob_flush();	
		}else{
			if($returnurl) return self::getFileUri($path);;
			
			$last_modified_time = filemtime($file); 
			$etag = md5_file($file); 
			header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT"); 
			header("Etag: $etag"); 
			
			if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time || 
				trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) { 
				header("HTTP/1.1 304 Not Modified"); 
				exit; 
			}
			@header('cache-control:public');  
			@header("Content-Type: " . image_type_to_mime_type($imginfo[2]));
			@ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
			@readfile($file);
			@flush(); @ob_flush();
		}
		exit();
	}
	
	//重写文件内容
	//@param number $icoid  文件的icoid
	//@param string $message  文件的新内容
	public function setFileContent($icoid,$fileContent,$force=false){
		global $_G;
		
		if(!$icoarr=C::t('icos')->fetch_by_icoid($icoid)){
			return array('error' => lang('file_not_exist'));
		}
		if($icoarr['type']!='document' && $icoarr['type']!='attach' && $icoarr['type']!='image'){
			return array('error' => lang('no_privilege'));
		}
		$gid=DB::result_first("select gid from %t where fid=%d",array('folder',$icoarr['pfid']));
		if(!$force && !perm_check::checkperm('edit',$icoarr)){
			return array('error' => lang('no_privilege'));
		}
	
		if(!$attach=getTxtAttachByMd5($fileContent,$icoarr['name'],$icoarr['ext'])){
			return array('error' => lang('file_save_failure'));
		}
		
		//计算用户新的空间大小
		$csize=$attach['filesize']-$icoarr['size'];
		//重新计算用户空间
		if($csize){
			if(!SpaceSize($csize,$gid,0,$icoarr['uid'])){
				return array('error' => lang('inadequate_capacity_space'));
			}
			SpaceSize($csize,$gid ,1,$icoarr['uid']);
		}
		$oldaid=$icoarr['aid'];
		//更新附件数量
		if($oldaid !=$attach['aid']){
			C::t('icos')->update($icoid,array('dateline'=>TIMESTAMP));
			if($icoarr['type']=='document'){
				C::t('source_document')->update($icoarr['did'],array('aid'=>$attach['aid']));
			}elseif($icoarr['type']=='image'){
				C::t('source_image')->update($icoarr['picid'],array('aid'=>$attach['aid']));
			}else{
				C::t('source_attach')->update($icoarr['qid'],array('aid'=>$attach['aid']));
			}
			C::t('attachment')->update($attach['aid'],array('copys'=>$attach['copys']+1));
			C::t('attachment')->delete_by_aid($oldaid);
		}
		
		return C::t('icos')->fetch_by_icoid($icoid);
	}
	//查找目录下的同名文件
	//@param string $filename  文件名称
	//@param number $fid  目录id
	//@param bool $isfolder  查找同名目录
	//return icoid  返回icoid
	public function getRepeatIDByName($filename,$fid,$isfolder=false){
		$sql="pfid=%d and name=%s and isdelete<1";
		if($isfolder) $sql.=" and type='folder'";
		else $sql.=" and type!='folder'";
		if($icoid=DB::result_first("select icoid from %t where $sql ",array('icos',$fid,$filename))){
			 return $icoid;
		}else return false;
	}
	//获取icosdata
	public function getMeta($icoid){
		if(strpos($icoid,'dzz::')===0){
			$attachment=preg_replace('/^dzz::/i','',$icoid);
			$name=array_pop(explode('/',$icoid));
			$ext=array_pop(explode('.',$name));
			return array( 'icoid'=>$icoid,
						  'name'=>$name,
						  'ext'=>$ext,
						  'size'=>filesize(getglobal('setting/attachdir').$attachment),
						  'url'=>getglobal('setting/attachurl').$attachment
						  );
			
		}elseif(strpos($icoid,'attach::')===0){
			$attach=C::t('attachment')->fetch(intval(str_replace('attach::','',$icoid)));
			return array( 'icoid'=>$icoid,
						  'name'=>$attach['filename'],
						  'ext'=>$attach['filetype'],
						  'apath'=>dzzencode('attach::'.$attach['aid']),
						  'dpath'=>dzzencode('attach::'.$attach['aid']),
						  'size'=>$attach['filesize'],
						  'url'=>getAttachUrl($attach),
						  'bz'=>io_remote::getBzByRemoteid($attach['remote'])
						 );
		}elseif(strpos($icoid,'TMP::')===0){
			$file=self::getStream($icoid);
			$pathinfo=pathinfo($file);
			return array( 'icoid'=>md5($icoid),
						  'name'=>$pathinfo['basename'],
						  'ext'=>$pathinfo['extension'],
						  'size'=>filesize($file),
						  'url'=>'',
						  'bz'=>''
						 );
		}else{
			return C::t('icos')->fetch_by_icoid($icoid);
		}
	}
	public function getFolderByIcosdata($data){
		if($data['type']=='folder'){
			return C::t('folder')->fetch_by_fid($data['oid']);	
		}
		return array();
	}
	//打包下载文件
	public function zipdownload($paths,$filename){
		global $_G;
		$paths=(array)$paths;
		set_time_limit(0);
		
		if(empty($filename)){
			$meta=self::getMeta($paths[0]);
			$filename=$meta['name'].(count($paths)>1?lang('wait'):'');
		}
		$filename=(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($filename) : $filename);
		//$data=self::getFolderInfo($path);
		include_once libfile('class/ZipStream');	
		$zip = new ZipStream($filename.".zip");
		$data=self::getFolderInfo($paths,'',$zip);
		/*foreach($data as $value){
			 $zip->addLargeFile(fopen($value['url'],'rb'), $value['position'], $value['dateline']);
		}*/
		$zip->finalize();
	}
	public function getFolderInfo($paths,$position='',&$zip){
		static $data=array();
		try{
			foreach($paths as $path){
				$meta=self::getMeta($path);
				
				switch($meta['type']){
					case 'folder':
						 $lposition=$position.$meta['name'].'/';
						 $contents=C::t('icos')->fetch_all_by_pfid($meta['oid']);
						 foreach($contents as $key=>$value){
							self::getFolderInfo(array($value['icoid']),$lposition,$zip);
						 }
						break;
					case 'discuss':case 'dzzdoc':case 'shortcut':case 'user':case 'link':case 'music':case 'video':case 'topic':case 'app'://这些内容不能移动到api网盘内；
							break;
					default:
						$meta['url']=IO::getStream($meta['path']);
						$meta['position']=$position.$meta['name'];
						/*$data[$meta['icoid']]=$meta;*/
						$zip->addLargeFile(fopen($meta['url'],'rb'), $meta['position'], $meta['dateline']);
				}
			}
		}catch(Exception $e){
			$data['error']=$e->getMessage();
			return $data;
		}
		return $data;
	}
	//下载
	public function download($paths,$filename){
		global $_G;
		$paths=(array)$paths;
		if(count($paths)>1){
			self::zipdownload($paths,$filename);
			exit();
		}else{
			$path=$paths[0];
		}
		@set_time_limit(0);
		$attachexists = FALSE;
		if(strpos($path,'attach::')===0){
			$attachment=C::t('attachment')->fetch(intval(str_replace('attach::','',$path)));
			 $attachment['name']=$filename?$filename:$attachment['filename'];
			$path=getDzzPath($attachment);
			$attachurl=IO::getStream($path);
		}elseif(strpos($path,'dzz::')===0){
			$attachment=array('attachment'=>preg_replace("/^dzz::/i",'',$path),'name'=>$filename?$filename:substr(strrpos($path, '/')));
			$attachurl=$_G['setting']['attachdir'].$attachment['attachment'];
		}elseif(strpos($path,'TMP::')===0){
			$tmp=str_replace('\\','/',sys_get_temp_dir());
			$attachurl= str_replace('TMP::',$tmp.'/',$path);
			$pathinfo=pathinfo($attachurl);
			$attachment=array('attachment'=>$attachurl,'name'=>$filename?$filename:$pathinfo['basename']);
			
		}elseif(is_numeric($path)){
			$icoid=intval($path);
			$icoarr = C::t('icos')->fetch_by_icoid($path);
			
			if(!$icoarr['icoid']){
				topshowmessage(lang('attachment_nonexistence'));
			}elseif($icoarr['type']=='folder'){
				self::zipdownload($path);
				exit();
			}
			if(!$icoarr['aid']){
				topshowmessage(lang('attachment_nonexistence'));
			}
			$attachment=$icoarr;
			$attachurl=IO::getStream($path);
		}
		
		//$filename = $_G['setting']['attachdir'].$attachment['attachment'];
		
		$filesize = !$attachment['remote'] ? filesize($attachurl) : $attachment['filesize'];
		if($attachment['ext'] && strpos(strtolower($attachment['name']),$attachment['ext'])===false){
			$attachment['name'].='.'.$attachment['ext'];
		}
		
		 $attachment['name'] = '"'.(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($attachment['name']) : ($attachment['name'])).'"';
		$d=new FileDownload();
		$d->download($attachurl,$attachment['name'],$filesize,$attachment['dateline'],true);
		exit();
		$chunk = 10 * 1024 * 1024; 
		if(!$fp = @fopen($attachurl, 'rb')) {
			topshowmessage(lang('file_not_exist1'));
		}
		$db = DB::object();
		$db->close();
		$chunk = 10 * 1024 * 1024; 
		
		dheader('Date: '.gmdate('D, d M Y H:i:s', $attach['dateline']).' GMT');
		dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', $attach['dateline']).' GMT');
		dheader('Content-Encoding: none');
		dheader('Content-Disposition: attachment; filename='.$attachment['name']);
		dheader('Content-Type: application/octet-stream');
		dheader('Content-Length: '.$filesize);
		@ob_end_clean();
		if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
		while (!feof($fp)) { 
			echo fread($fp, $chunk);
			@ob_flush();  // flush output
			@flush();
		}
		fclose($fp);
		//@readfile($attachurl);
		// @ob_flush();@flush();
		exit();
	
	}
	
	//删除
	//当文件在回收站时，彻底删除；
	//$force 真实删除，不放入回收站
	public function Delete($icoid,$force=false){
		global $_G;
		if(strpos($icoid,'dzz::')===0){
			if(strpos($icoid,'../')!==false) return false;
			@unlink($_G['setting']['attachdir'].preg_replace('/^dzz::/i','',$icoid));
			return true;
		
		}elseif(strpos($icoid,'attach::')===0){
			if(strpos($icoid,'../')!==false) return false;
			return C::t('attachment')->delete_by_aid(intval(str_replace('attach::','',$icoid)));
		}elseif(strpos($icoid,'TMP::')===0){
			$tmp=str_replace('\\','/',sys_get_temp_dir());
			return @unlink(str_replace('TMP::',$tmp.'/',$path));
		}else{
			try{
				if(!$icoarr= C::t('icos')->fetch($icoid)){
					return array('icoid'=>$icoid,'error'=>lang('file_longer_exists'));
				}
				if($force || $icoarr['isdelete']){
					if(perm_check::checkperm('delete',$icoarr)){
						C::t('icos')->delete_by_icoid($icoid,true);
					}else{
						return array('icoid'=>$icoarr['icoid'],'error'=>lang('no_privilege'));
					}
				}else{
					
					if(perm_check::checkperm('delete',$icoarr)){
						 C::t('icos')->update($icoid,array('uid'=>getglobal('uid'),'username'=>getglobal('username'),'isdelete'=>1,'deldateline'=>TIMESTAMP));
						 if($icoarr['type']=='folder') C::t('folder')->update($icoarr['oid'],array('uid'=>getglobal('uid'),'username'=>getglobal('username'),'isdelete'=>1,'deldateline'=>TIMESTAMP));
					}else{
						return array('icoid'=>$icoarr['icoid'],'error'=>lang('no_privilege'));
					}
				}
				return array('icoid'=>$icoarr['icoid'],'name'=>$icoarr['name']);
			}catch(Exception $e){
				return array('error'=>$e->getMessage());
			}
		}
	}
	//检查名称是否重复
	public function check_name_repeat($name,$pfid){
		return DB::result_first("select icoid from ".DB::table('icos')." where name='{$name}' and  pfid='{$pfid}'");
	}
	//过滤文件名称
	public function name_filter($name){
		return str_replace(array('/','\\',':','*','?','<','>','|','"',"\n"),'',$name);
	}
	
	//获取不重复的目录名称
	public function getFolderName($name,$pfid){
		static $i=0;
		$name=self::name_filter($name);
		//echo("select COUNT(*) from ".DB::table('folder')." where fname='{$name}' and  pfid='{$pfid}'");
		if(DB::result_first("select COUNT(*) from %t where fname=%s and  pfid=%d and isdelete<1",array('folder',$name,$pfid))){
			$name=preg_replace("/\(\d+\)/i",'',$name).'('.($i+1).')';
			$i+=1;
			return self::getFolderName($name,$pfid);
		}else{
			return $name;
		}
	}
	//获取不重复的目录名称
	public function getFileName($name,$pfid){
		static $i=0;
		$name=self::name_filter($name);
		//echo("select COUNT(*) from ".DB::table('folder')." where fname='{$name}' and  pfid='{$pfid}'");
		if(DB::result_first("select COUNT(*) from %t where type!='folder' and name=%s and isdelete<1 and pfid=%d",array('icos',$name,$pfid))){
			$ext='';
			$namearr=explode('.',$name);
			if(count($namearr)>1){
				$ext=$namearr[count($namearr)-1];
				unset($namearr[count($namearr)-1]);
				$ext=$ext?('.'.$ext):'';
			}
			$tname=implode('.',$namearr);
			$name=preg_replace("/\(\d+\)/i",'',$tname).'('.($i+1).')'.$ext;
			$i+=1;
			return self::getFileName($name,$pfid);
		}else{
			return $name;
		}
	}
	 //获取系统默认目录图标
	public function get_folder_sysicon(){
			$icon=DB::result_first("select icon from ".DB::table('icon_sys')." where  `default`=1 LIMIT 1");
			if(!$icon){
				$icon=DB::result_first("select icon from ".DB::table('icon_sys')." where 1 ORDER BY disp LIMIT 1");
			}
			if($icon){
				return SYSICON_FOLDER.$icon;
			}else{
				return 'dzz/images/default/folder.png';
			}
	}
	
	//创建目录
	public function CreateFolder($pfid,$fname,$perm,$ondup='newcopy'){
		global $_G,$_GET;
		
		$fname=self::name_filter($fname);
		if(!$folder=DB::fetch_first("select fid,pfid,iconview,disp,gid from %t where fid=%d",array('folder',$pfid))){
			return array('error'=>lang('parent_directory_not_exist'));
		}
		
		if(!perm_check::checkperm_Container($pfid,'folder')){
			return array('error'=>lang('no_privilege'));
		}
		
		if(($ondup=='overwrite') && ($icoid=self::getRepeatIDByName($fname,$pfid,true))){//如果目录下有同名目录
				$data=array();
				$data['icoarr']=C::t('icos')->fetch_by_icoid($icoid);
				$data['folderarr']=self::getFolderByIcosdata($data['icoarr']);
				 return $data;
		}else $fname=self::getFolderName($fname,$pfid); //重命名
		$setarr=array('fname'=>$fname,
					  'uid'=>$_G['uid'],
					  'username'=>$_G['username'],
					  'pfid'=>$folder['fid'],
					  'iconview'=>$folder['iconview'],
					  'disp'=>$folder['disp'],
					  'perm'=>$perm,
					  'flag'=>'folder',
					  'dateline'=>$_G['timestamp'],
					  'gid'=>$folder['gid'],
					 );
		if($setarr['fid']=C::t('folder')->insert($setarr,true)){
			$setarr['path']=$setarr['fid'];
			$setarr['perm']=perm_check::getPerm($setarr['fid']);
			$setarr['perm1']=perm_check::getPerm1($setarr['fid']);
			
			$setarr['title']=$setarr['fname'];
			$setarr['ext']='';
			$setarr['size']=0;
			
			$setarr1=array(
							'uid'=>$_G['uid'],
							'username'=>$_G['username'],
							'oid'=>$setarr['fid'],
							'name'=>$setarr['fname'],
							'type'=>'folder',
							'flag'=>'',
							'dateline'=>$_G['timestamp'],
							'pfid'=>$folder['fid'],
							'gid'=>$folder['gid'],
							'ext'=>'',
							'size'=>0,
							);
			if($setarr1['icoid']=DB::insert('icos',($setarr1),1)){
				$setarr1['path']=$setarr1['icoid'];
				$setarr1['dpath']=dzzencode($setarr1['icoid']);
				$setarr1['bz']='';
				addtoconfig($setarr1);
				$setarr1['fsize']=formatsize($setarr1['size']);
				$setarr1['ftype']=getFileTypeName($setarr1['type'],$setarr1['ext']);
				$setarr1['fdateline']=dgmdate($setarr1['dateline']);
				if($setarr['gid']){
					$permtitle=perm_binPerm::getGroupTitleByPower($setarr['perm1']);
					if(file_exists('dzz/images/default/system/folder-'.$permtitle['flag'].'.png')){
						$setarr['icon']=$setarr1['img']='dzz/images/default/system/folder-'.$permtitle['flag'].'.png';
					}else{
						$setarr['icon']=$setarr1['img']='dzz/images/default/system/folder-read.png';
					}
				}
				return array('icoarr'=>$setarr1,'folderarr'=>$setarr);
			}
		}
		return false;
	}
	public function getPath($ext,$dir='dzz'){
		global $_G;
			if($ext && in_array(trim($ext,'.'),$_G['setting']['unRunExts'])){
				$ext='.dzz';
			}
		    $subdir = $subdir1 = $subdir2 = '';
			$subdir1 = date('Ym');
			$subdir2 = date('d');
			$subdir = $subdir1.'/'.$subdir2.'/';
			$target1=$dir.'/'.$subdir.'index.html';
			$target=$dir.'/'.$subdir;
			$target_attach=$_G['setting']['attachdir'].$target1;
			$targetpath = dirname($target_attach);
			dmkdir($targetpath);
			return $target.date('His').''.strtolower(random(16)).$ext;
	 }
	public function save($target,$filename) {
	 global $_G;
	 	$filepath=$_G['setting']['attachdir'].$target;
        $md5=md5_file($filepath);
		$filesize=fix_integer_overflow(filesize($filepath));
		if($md5 && $attach=DB::fetch_first("select * from %t where md5=%s and filesize=%d",array('attachment',$md5,$filesize))){
			$attach['filename']=$filename;
			$pathinfo = pathinfo($filename);
			$ext = $pathinfo['extension']?$pathinfo['extension']:'';
			$attach['filetype']=strtolower($ext);
			@unlink($filepath);
			unset($attach['attachment']);
			return $attach;
		}else{
			$pathinfo = pathinfo($filename);
			$ext = $pathinfo['extension']?$pathinfo['extension']:'';
			
			$pathinfo1 = pathinfo($target);
			$ext_dzz = strtolower($pathinfo1['extension']);
			if($ext_dzz=='dzz'){
				$unrun=1;
			}else{
				$unrun=0;
			}
			$filesize=filesize($filepath);
			$remote=0;
			
        	$attach=array(
			
				'filesize'=>$filesize,
				'attachment'=>$target,
				'filetype'=>strtolower($ext),
				'filename' =>$filename,
				'remote'=>$remote,
				'copys' => 0,
				'md5'=>$md5,
				'unrun'=>$unrun,
				'dateline' => $_G['timestamp'],
			);
			if($attach['aid']=DB::insert('attachment',($attach),1)){
				$remoteid=io_remote::getRemoteid($attach);
				if($_G['setting']['thumb_active'] && $remoteid<2 && in_array($attach['filetype'],array('jpg','jpeg','png'))){//主动模式生成缩略图
					try{
						foreach($_G['setting']['thumbsize'] as $key => $value){
							self::createThumb('dzz::'.$attach['attachment'],$key);
						}
						/*self::createThumb('dzz::'.$attach['attachment'],256,256);
						self::createThumb('dzz::'.$attach['attachment'],1440,900);*/
					}catch(Exception $e){}
				}
				C::t('local_storage')->update_usesize_by_remoteid($attach['remote'],$attach['filesize']);
				if($remoteid>1) dfsockopen($_G['siteurl'].'misc.php?mod=movetospace&aid='.$attach['aid'].'&remoteid=0',0, '', '', false, '',1);
				unset($attach['attachment']);
				return $attach;
			}else{
				return false;
			}
		}
    }
	public function uploadToattachment($attach,$fid){
		global $_G,$documentexts,$space;
		
		$gid=DB::result_first("select gid from %t where fid=%d",array('folder',$fid));
		
		$attach['filename']=self::getFileName($attach['filename'],$fid);
		
		$imgexts  = array('jpg', 'jpeg', 'gif', 'png', 'bmp');
		//图片文件时
		if(in_array(strtolower($attach['filetype']),$imgexts)){
			
			//$attachment=getAttachUrl($attach);
			//$imginfo=@getimagesize($attachment);
			$sourcedata=array(
								'uid'=>$_G['uid'],
								'username'=>$_G['username'],
								'dateline' => $_G['timestamp'],
								'postip' => $_G['clientip'],
								'title' => $attach['filename'],
								'desc'=>'',
								'aid'=>$attach['aid'],
								//'width'=>$imginfo[0],
								//'height'=>$imginfo[1],
								'gid'=>$gid,
								
			);
			if($sourcedata['picid']=DB::insert('source_image',($sourcedata),1)){
				C::t('attachment')->update($attach['aid'],array('copys'=>$attach['copys']+1));
				//$sourcedata['url']=getAttachUrl($attach);
				$icoarr=array(
						'uid'=>$_G['uid'],
						'username'=>$_G['username'],
						'oid'=>$sourcedata['picid'],
						'name'=>$sourcedata['title'],
						'dateline'=>$_G['timestamp'],
						'pfid'=>$fid,
						'type'=>'image',
						'flag'=>'',
						'opuid'=>$_G['uid'],
						'gid'=>$gid,
						'ext'=>$attach['filetype'],
						'size'=>$attach['filesize']
				);
				if($icoarr['icoid']=DB::insert('icos',($icoarr),1)){
					$icoarr=array_merge($attach,$sourcedata,$icoarr);
					$icoarr['img']=DZZSCRIPT.'?mod=io&op=thumbnail&size=small&path='.dzzencode($icoarr['icoid']);
				    $icoarr['url']=DZZSCRIPT.'?mod=io&op=thumbnail&size=large&path='.dzzencode($icoarr['icoid']);
					$icoarr['bz']='';
					$icoarr['rbz']=io_remote::getBzByRemoteid($attach['remote']);
					$icoarr['path']=$icoarr['icoid'];
					$icoarr['dpath']=dzzencode($icoarr['icoid']);
					$icoarr['apath']=dzzencode('attach::'.$attach['aid']);
				}else{
					C::t('source_image')->delete_by_picid($sourcedata['picid']);
				}
			}
		}elseif(in_array(strtoupper($attach['filetype']),array('DZZDOC'))){
			$sourcedata=array(
								'uid'=>$_G['uid'],
								'username'=>$_G['username'],
								'aid'=>$attach['aid'],
							 );
			
			if($sourcedata['did']=C::t('document')->insert($sourcedata)){
				$icoarr=array(
								'uid'=>$_G['uid'],
								'username'=>$_G['username'],
								'oid'=>$sourcedata['did'],
								'name'=>$attach['filename'],
								'type'=>'dzzdoc',
								'opuid'=>$_G['uid'],
								'dateline'=>$_G['timestamp'],
								'pfid'=>$fid,
								'flag'=>'',
								'gid'=>$gid,
								'ext'=>$attach['filetype'],
								'size'=>$attach['filesize']
								
				);
			
				if($icoarr['icoid']=DB::insert('icos',($icoarr),1)){
					$icoarr=array_merge($sourcedata,$attach,$icoarr);
					$icoarr['img']=geticonfromext($icoarr['ext'],$icoarr['type']);
					$icoarr['url']=DZZSCRIPT.'?mod=document&did='.dzzencode($sourcedata['did']).'&icoid='.dzzencode($icoarr['icoid']);
					$icoarr['bz']='';
					$icoarr['rbz']=io_remote::getBzByRemoteid($attach['remote']);;
					$icoarr['path']=$icoarr['icoid'];
					
					$icoarr['apath']=$icoarr['dpath']=dzzencode($icoarr['icoid']);
					$icoarr['ddid']=dzzencode($sourcedata['did']);
				}else{
					C::t('document')->delete_by_did($sourcedata['did'],true);
				}
					
			}
		}elseif(in_array(strtoupper($attach['filetype']),$documentexts)){
			$sourcedata=array(
								'uid'=>$_G['uid'],
								'username'=>$_G['username'],
								'title'=>$attach['filename'],
								'desc'=>'',
								'dateline' => $_G['timestamp'],
								'aid'=>$attach['aid'],
								'gid'=>$gid
			
			);
			if($sourcedata['did']=DB::insert('source_document',($sourcedata),1)){
				C::t('attachment')->update($attach['aid'],array('copys'=>$attach['copys']+1));
				$icoarr=array(
								'uid'=>$_G['uid'],
								'username'=>$_G['username'],
								'oid'=>$sourcedata['did'],
								'name'=>$attach['filename'],
								'type'=>'document',
								'opuid'=>$_G['uid'],
								'dateline'=>$_G['timestamp'],
								'pfid'=>$fid,
								'flag'=>'',
								'gid'=>$gid,
								'ext'=>$attach['filetype'],
								'size'=>$attach['filesize']
								
				           );
			
				if($icoarr['icoid']=DB::insert('icos',($icoarr),1)){
					$icoarr=array_merge($sourcedata,$attach,$icoarr);
					$icoarr['img']=geticonfromext($icoarr['ext'],$icoarr['type']);
					$icoarr['url']=DZZSCRIPT.'?mod=io&op=getStream&path='.dzzencode($icoarr['icoid']);
					$icoarr['bz']='';
					$icoarr['rbz']=io_remote::getBzByRemoteid($attach['remote']);;
					$icoarr['path']=$icoarr['icoid'];
					$icoarr['dpath']=dzzencode($icoarr['icoid']);
					$icoarr['apath']=dzzencode('attach::'.$attach['aid']);
				}else{
					C::t('source_document')->delete_by_did($sourcedata['did']);
				}
					
			}
		}else{
			$sourcedata=array(
								'uid'=>$_G['uid'],
								'username'=>$_G['username'],
								'title'=>$attach['filename'],
								'desc'=>'',
								'dateline' => $_G['timestamp'],
								'aid'=>$attach['aid'],
								'gid'=>$gid
			
			);
			if($sourcedata['qid']=DB::insert('source_attach',($sourcedata),1)){
				C::t('attachment')->update($attach['aid'],array('copys'=>$attach['copys']+1));
				
				$icoarr=array(
								'uid'=>$_G['uid'],
								'username'=>$_G['username'],
								'oid'=>$sourcedata['qid'],
								'name'=>$attach['filename'],
								'type'=>'attach',
								'flag'=>'',
								'opuid'=>$_G['uid'],
								'dateline'=>$_G['timestamp'],
								'pfid'=>$fid,
								'gid'=>$gid,
								'ext'=>$attach['filetype'],
								'size'=>$attach['filesize']
								
				);
				
				if($icoarr['icoid']=DB::insert('icos',($icoarr),1)){
					$icoarr=array_merge($sourcedata,$attach,$icoarr);
					$icoarr['img']=geticonfromext($icoarr['ext'],$icoarr['type']);
					$icoarr['url']=DZZSCRIPT.'?mod=io&op=getStream&path='.dzzencode($icoarr['icoid']);
					$icoarr['bz']='';
					$icoarr['rbz']=io_remote::getBzByRemoteid($attach['remote']);;
					$icoarr['path']=$icoarr['icoid'];
					$icoarr['dpath']=dzzencode($icoarr['icoid']);
					$icoarr['apath']=dzzencode('attach::'.$attach['aid']);
				}else{
					C::t('source_attach')->delete_by_qid($sourcedata['qid']);
				}
			}
		}
		
		if($icoarr['icoid'] ){
			if($icoarr['size']) SpaceSize($icoarr['size'],$gid,true);
			addtoconfig($icoarr);
			$icoarr['fsize']=formatsize($icoarr['size']);
			$icoarr['ftype']=getFileTypeName($icoarr['type'],$icoarr['ext']);
			$icoarr['fdateline']=dgmdate($icoarr['dateline']);
			return $icoarr;
		}else{
			return array('error' => lang('data_error'));			
		}
	}
	protected function createFolderByPath($path,$pfid){
		$data=array('pfid'=>$pfid);
		if(!$path){
			$data['pfid']=$pfid;
		}else{
			$patharr=explode('/',$path);
			//生成目录
			
			foreach($patharr as $fname){
				if(!$fname) continue;
				//判断是否含有此目录
				if($fid=DB::result_first("select fid from %t where pfid=%d and isdelete<1 and fname=%s",array('folder',$pfid,$fname))){
					$data['pfid']=$fid;
				}else{
					if($re=$this->CreateFolder($data['pfid'],$fname,0,'overwrite')){
						$data['icoarr'][]=$re['icoarr'];
						$data['folderarr'][]=$re['folderarr'];
						$data['pfid']=$re['folderarr']['fid'];
					}else{
						$data['error']='create folder error!';
						return $data;
					}
				}
			}
		}
		return $data;
	}
	private function getCache($path){
		$cachekey='dzz_upload_'.md5($path);
		if($cache=C::t('cache')->fetch($cachekey)){
			return $cache['cachevalue'];
		}else{
			return false;
		}
	}
	private function saveCache($path,$str){
		global $_G;
		$cachekey='dzz_upload_'.md5($path);
		C::t('cache')->insert(array(
							'cachekey' => $cachekey,
							'cachevalue' => $str,
							'dateline' => $_G['timestamp'],
						), false, true);
	}
	private function deleteCache($path){
		$cachekey='dzz_upload_'.md5($path);
		C::t('cache')->delete($cachekey);
	}
	private function getPartInfo($content_range){
		$arr=array();
		if(!$content_range){
			 $arr['ispart']=false;
			 $arr['iscomplete']=true;
		}elseif(is_array($content_range)){
			$arr['ispart']=true;
			$partsize=getglobal('setting/maxChunkSize');
			$arr['partnum']=ceil(($content_range[2]+1)/$partsize);
			if(($content_range[2]+1)>=$content_range[3]){
			 	$arr['iscomplete']=true;
			}else{
				$arr['iscomplete']=false;
			}
		}else{
			return false;
		}
		return $arr;
	}
	public function uploadStream($file,$filename,$pfid,$relativePath,$content_range){
		$filename=self::name_filter($filename);
		$data=array();
		//处理目录(没有分片或者最后一个分片时创建目录
		$arr=self::getPartInfo($content_range);
		$data['pfid']=$pfid;
		if($relativePath && $arr['iscomplete']){
			$data=self::createFolderByPath($relativePath,$pfid);
			if(isset($data['error'])){
				return array('error'=>$data['error']);
			}
		}
		$arr['flag']=$pfid.'_'.$relativePath; 
		
		//获取文件内容
		$fileContent='';
		if(!$handle=fopen($file, 'rb')){
				return array('error'=>lang('open_file_error'));
			}
		while (!feof($handle)) {
		  $fileContent .= fread($handle, 8192);
		}
		fclose($handle);
		if($arr['ispart']){
			$re=self::upload($fileContent,$data['pfid'],$filename,$arr);
			if($arr['iscomplete']){
				if(empty($re['error'])){
					$data['icoarr'][] = $re;
					return $data;
				}else{
					$data['error'] = $re['error'];
					return $data;
				}
			}else{
				return true;	
			}
		}else{
			$re=self::upload($fileContent,$data['pfid'],$filename);
			if(empty($re['error'])){
				$data['icoarr'][] = $re;
				return $data;
			}else{
				$data['error'] = $re['error'];
				return $data;
			}
		}
	}
	
	public function upload_by_content($fileContent,$path,$filename){
		return self::upload($fileContent,$path,$filename);
	}
	/**
		 * 上传文件
		 * 注意：此方法适用于上传不大于2G的单个文件。
		 * @param string $fileContent 文件内容字符串
		 * @param string $fid 上传文件的目标保存目录fid
		 * @param string $fileName 文件名
		 * @param string $ondup overwrite：表示覆盖同名文件；newcopy：表示生成文件副本并进行重命名，命名规则为“文件名_日期.后缀”。 
		 * @param boolean $isCreateSuperFile 是否分片上传
		 * @return string
		 */
	public function upload($fileContent,$fid,$filename,$partinfo=array(),$ondup='newcopy'){
		global $_G;
		/*if(!$fileContent){
			return array('error'=>'文件内容不能为空');
		}*/
		$filename=self::name_filter($filename);
		if(($ondup=='overwrite') && ($icoid=self::getRepeatIDByName($filename,$fid))){//如果目录下有同名文件
			 return self::overwriteUpload($fileContent,$icoid,$filename,$partinfo);//覆盖
		}else $nfilename=self::getFileName($filename,$fid); //重命名
		
		 if($partinfo['ispart']){
			 if($partinfo['partnum']==1){
				if($target=self::getCache($partinfo['flag'].'_'.md5($filename))){
					file_put_contents($_G['setting']['attachdir'].$target,'');
				}else{
					$pathinfo = pathinfo($filename);
					$ext = strtolower($pathinfo['extension']);
					$target=$this->getPath($ext?('.'.$ext):'','dzz');
					self::saveCache($partinfo['flag'].'_'.md5($filename),$target);
				}
			 }else{
				 $target=self::getCache($partinfo['flag'].'_'.md5($filename));
			 }
			
			if(!file_put_contents(
                        $_G['setting']['attachdir'].$target,
                        $fileContent,
                        FILE_APPEND
                    )
				){
					return array('error'=>lang('cache_file_error'));
				}
			
			if(!$partinfo['iscomplete']) return true;
			else{
				self::deleteCache($partinfo['flag'].'_'.md5($filename));
			
			}
		 }else{
			    $pathinfo = pathinfo($filename);
				$ext = strtolower($pathinfo['extension']);
				$target=$this->getPath($ext?('.'.$ext):'','dzz');
				
				if(!empty($fileContent) && !file_put_contents($_G['setting']['attachdir'].$target,$fileContent)){
					return array('error'=>lang('cache_file_error'));
				}
		}
		
		//判断空间大小
		$gid=DB::result_first("select gid from %t where fid=%d",array('folder',$fid));
		if(!SpaceSize(filesize($_G['setting']['attachdir'].$target),$gid)){
			  @unlink($_G['setting']['attachdir'].$target);
			 return array('error' => lang('inadequate_capacity_space'));
		 }
	  
		if($attach=$this->save($target,$nfilename)){
			//return array('error'=>json_encode($attach));
			if($attach['error']){
				  return array('error'=>$attach['error']);
			}else{
				return $this->uploadToattachment($attach,$fid);
			}
		} else {
			return array('error'=>'Could not save uploaded file. The upload was cancelled, or server error encountered');
		}
		
	}
	public function overwriteUpload($fileContent,$icoid,$filename,$partinfo=array()){
		global $_G,$space;
		
		if(!$fileContent){
			return array('error'=>lang('file_content_cannot_empty'));
		}
		if(!$icoarr=C::t('icos')->fetch_by_icoid($icoid)){
			return array('error' => lang('file_not_exist1'));
		}
		$gid=DB::result_first("select gid from %t where fid=%d",array('folder',$icoarr['pfid']));
		
		if(in_array($icoarr['type'],array('folder','link','video','dzzdoc','shortcut'))){
			if(!perm_check::checkperm_Container($icoarr['pfid'],$icoarr['type'])) {
				return array('error'=>lang('privilege'));
			}
		}elseif(!perm_check::checkperm_Container($icoarr['pfid'],'newtype')){
			return array('error'=>lang('privilege'));
		}
		$target=$icoarr['attachment'];
		if($partinfo['ispart']){
			 if($partinfo['partnum']==1){
				  file_put_contents( $_G['setting']['attachdir'].'./'.$target, $fileContent);
			 }else{
				 file_put_contents(
							$_G['setting']['attachdir'].'./'.$target,
							$fileContent,
							FILE_APPEND
						);
				if(!$partinfo['iscomplete']) return true;
			 }
		}else{
		   file_put_contents($_G['setting']['attachdir'].'./'.$target,$fileContent);
		}
	
		
		if(!$attach=self::save($target,$icoarr['name'])){
			return array('error' => lang('file_save_exist'));
		}
		//计算用户新的空间大小
		$csize=$attach['filesize']-$icoarr['size'];
		//重新计算用户空间
		if($csize){
			if(!SpaceSize($csize,$gid)){
				return array('error' => lang('inadequate_capacity_space'));
			}
			SpaceSize($csize,$gid ,1);
		}
		$oldaid=$icoarr['aid'];
		//更新附件数量
		if($oldaid !=$attach['aid']){
			if($icoarr['type']=='document'){
				C::t('source_document')->update($icoarr['did'],array('aid'=>$attach['aid']));
			}else{
				C::t('source_attach')->update($icoarr['qid'],array('aid'=>$attach['aid']));
			}
			C::t('attachment')->update($attach['aid'],array('copys'=>$attach['copys']+1));
			C::t('attachment')->delete_by_aid($oldaid);
		}
		$icoarr['size']=$attach['filesize'];
		$icoarr['aid']=$attach['aid'];
		return $icoarr;
	}
	
	//判断附件是否已经存在，返回附件数组
	public function dzz_imagetoattach($link,$gid){
		global $_G;
		
		$md5=md5_file($link);
		if($md5 && $attach=C::t('attachment')->fetch_by_md5($md5)){
			  //判断空间大小
			  if(!SpaceSize($attach['filesize'],$gid)){
				 return array('error' => lang('inadequate_capacity_space'));
			 }
			return $attach;
		}else{
			if($target=imagetolocal($link,'dzz')){
				//判断空间大小
				$size=@filesize($_G['setting']['attachdir'].$target);
				//判断空间大小
				 if(!SpaceSize($size,$gid)){
					@unlink($_G['setting']['attachdir'].$target);
					return array('error' => lang('inadequate_capacity_space'));
				 }
				 $object=str_replace('/','-',$target);
				 $remote=0;
				
				$attach=array(
								'filesize'=>intval($size),
								'attachment'=>$target,
								'filetype'=>strtolower(substr(strrchr($link, '.'), 1, 10)),
								'filename' =>substr(strrchr($link, '/'), 1, 50),
								'remote'=>$remote,
								'copys' => 1,
								'md5'=>$md5,
								'dateline' => $_G['timestamp'],
				);
				if($attach['aid']=DB::insert('attachment',($attach),1)){
					C::t('local_storage')->update_usesize_by_remoteid($attach['remote'],$attach['filesize']);
					dfsockopen($_G['siteurl'].'misc.php?mod=movetospace&aid='.$attach['aid'].'&remoteid=0',0, '', '', FALSE, '',1);
					
					return $attach;
				}
			}
		}
		return false;
	}
	public function linktoimage($link,$pfid){
		global $_G,$space;
		$fid=$pfid;
		$gid=DB::result_first("select gid from %t where fid =%d",array('folder',$pfid));
		if(!$cimage=DB::fetch_first("select * from ".DB::table('cai_image')." where ourl='{$link}'")){
			if($attach=self::dzz_imagetoattach($link,$gid)){
				if($attach['error']) return $attach;
				
				$cimage=array(	
								'ourl'=>$link,
								'aid'=>$attach['aid'],
								'copys'=>0,
								'dateline'=>$_G['timestamp']
								);
				$cimage['cid']=DB::insert('cai_image',($cimage),1);
				
			}else{
				return array('error' => lang('image_to_local_error'));
			}
		}else{
			$attach=C::t('attachment')->fetch($cimage['aid']);
		}
		//判断空间大小
		  if(!SpaceSize($attach['filesize'],$gid)){
			 return array('error' => lang('inadequate_capacity_space'));
		 }
		$attachment=$_G['setting']['attachdir'].'./'.$attach['attachment'];
		$imginfo=@getimagesize($attachment);
		$sourcedata=array(
					'uid'=>$_G['uid'],
					'username'=>$_G['username'],
					'dateline' => $_G['timestamp'],
					'title' =>$attach['filename'],
					'desc'=>'',
					'postip' => $_G['clientip'],
					'desc' => $cimage['title'],
					'cid'=>$cimage['cid'],
					'aid'=>$cimage['aid'],
					'width'=>$imginfo[0],
					'height'=>$imginfo[1],
					'gid'=>$gid
		);
		if($sourcedata['picid']=DB::insert('source_image',($sourcedata),1)){
			C::t('cai_image')->update($cimage['cid'],array('copys'=>$cimage['copys']+1));
			if($cimage['aid']) C::t('attachment')->update($cimage['aid'],array('copys'=>$attach['copys']+1));
			
			$icoarr=array(
							'uid'=>$_G['uid'],
							'username'=>$_G['username'],
							'oid'=>$sourcedata['picid'],
							'name'=>self::getFileName(strtolower(substr(strrchr($link, '/'), 1, 50)),$fid),
							'flag'=>'',
							'type'=>'image',
							'dateline'=>$_G['timestamp'],
							'pfid'=>$fid,
							'opuid'=>$_G['uid'],
							'gid'=>$gid,
							'ext'=>$attach['filetype'],
							'size'=>$attach['filesize'],
			);
			
			if($icoarr['icoid']=DB::insert('icos',($icoarr),1)){
				$icoarr['img']=DZZSCRIPT.'?mod=io&op=thumbnail&&size=small&path='.rawurlencode($icoarr['icoid']);
				$icoarr['url']=DZZSCRIPT.'?mod=io&op=thumbnail&&size=large&path='.rawurlencode($icoarr['icoid']);
				$icoarr['bz']='';
				$icoarr['aid']=$sourcedata['aid'];
				$data['rbz']=io_remote::getBzByRemoteid($icoarr['remote']);
				$icoarr['path']=$icoarr['icoid'];
				$icoarr['dpath']=dzzencode($icoarr['icoid']);
				$icoarr['apath']=dzzencode('attach::'.$icoarr['aid']);
				//$icoarr=array_merge($sourcedata,$icoarr);
				if($icoarr['size']) SpaceSize($icoarr['size'],$gid,true);
				addtoconfig($icoarr);
				$icoarr['fsize']=formatsize($icoarr['size']);
				$icoarr['ftype']=getFileTypeName($icoarr['type'],$icoarr['ext']);
				$icoarr['fdateline']=dgmdate($icoarr['dateline']);
			}else{
				C::t('source_image')->delete_by_picid($sourcedata['picid']);
			}
		}
		if($icoarr['icoid'] ){
			return $icoarr;
		}else{
			return array('error' => lang('linktoimage_error'));
		}
	}
	public function linktomusic($link,$pfid){
		global $_G;
		@set_time_limit(60);
		$fid=$pfid;
		$gid=DB::result_first("select gid from %t where fid =%d",array('folder',$pfid));
		if(!$cmusic=DB::fetch_first("select * from ".DB::table('cai_music')." where ourl='{$link}'")){
				$cmusic=array(	
								'url'=>$link,
								'ourl'=>$link,
								'img'=>'',
								'desc' =>'',
								'title' => strtolower(substr(strrchr($link, '/'), 1, 50)),
								'copys' => 0,
								'dateline'=>$_G['timestamp']
								);
				$cmusic['cid']=DB::insert('cai_music',($cmusic),1);	
		}
		$sourcedata=array(
							'uid'=>$_G['uid'],
							'username'=>$_G['username'],
							'icon'=>$cmusic['img'],
							'desc' =>$cmusic['desc'],
							'title' =>$cmusic['title'],
							'cid'=>$cmusic['cid'],
							'dateline' => $_G['timestamp'],
							'gid'=>$gid
		
		);
		if($sourcedata['mid']=DB::insert('source_music',($sourcedata),1)){
			C::t('cai_music')->update($cmusic['cid'],array('copys'=>$cmusic['copys']+1));
			$sourcedata['icon']=$sourcedata['icon']?$sourcedata['icon']:geticonfromext('','music');
			$icoarr=array(
							'uid'=>$_G['uid'],
							'username'=>$_G['username'],
							'oid'=>$sourcedata['mid'],
							'name'=>self::getFileName($sourcedata['title'],$fid),
							'flag'=>'',
							'type'=>'music',
							'pfid'=>$fid,
							'opuid'=>$_G['uid'],
							'dateline'=>$_G['timestamp'],
							'gid'=>$gid,
							'ext'=>'',
							'size'=>0
			);
			if($icoarr['icoid']=DB::insert('icos',($icoarr),1)){
				$icoarr['url']=$sourcedata['url'];
				$icoarr['img']=$sourcedata['icon'];
				$icoarr['bz']='';
				$icoarr['path']=$icoarr['icoid'];
				$icoarr['dpath']=dzzencode($icoarr['icoid']);
				addtoconfig($icoarr);
				$icoarr['fsize']=formatsize($icoarr['size']);
				$icoarr['ftype']=getFileTypeName($icoarr['type'],$icoarr['ext']);
				$icoarr['fdateline']=dgmdate($icoarr['dateline']);
				//if($gid) C::t('group_log')->setLog($gid,$_G['uid'],'addmusic',lang('message','xiezuo_log_addmusic',array('username'=>$_G['username'],'position'=>getPositionName($fid),'name'=>$icoarr['name'])));
			}else{
				C::t('source_music')->delete($sourcedata['mid']);
			}
		}
		
		if($icoarr['icoid'] ){
			return $icoarr;
		}else{
			return array('error' => lang('linktomusic_error'));
		}
	}
	public function linktovideo($link,$pfid){
		global $_G;
		@set_time_limit(60);
		$videoext  = array('swf', 'flv');
		$fid=$pfid;
		$gid=DB::result_first("select gid from %t where fid =%d",array('folder',$pfid));
		if(!$cvideo=DB::fetch_first("select * from ".DB::table('cai_video')." where ourl='{$link}'")){
			$arr=array();
			require_once dzz_libfile('function/video');
			if(!$arr=parseflv($link)){
				return false;
			}
			//采集标题和描述
			if(!$arr['title'] || !$arr['description']){
				require_once dzz_libfile('class/caiji');
				$caiji=new caiji($link);
				$arr['title']=$caiji->getTitle();
				$arr['description']=$caiji->getDescription();
			}
			$cvideo=array(	
							'url'=>$arr['url'],
							'ourl'=>$link,
							'img'=>$arr['img'],
							'desc' =>$arr['description'],
							'title' => $arr['title'],
							'copys' => 0,
							'dateline'=>$_G['timestamp']
							);
			$cvideo['cid']=DB::insert('cai_video',($cvideo),1);
		}
		//如果原先的标题和描述没采集到，重新采集
		if(!$cvideo['title'] || !$cvideo['desc']){
			require_once dzz_libfile('class/caiji');
			$caiji=new caiji($link);
			$cvideo['title']=$caiji->getTitle();
			$cvideo['description']=$caiji->getDescription();
			 C::t('cai_video')->update($cvideo['cid'],array('title'=>$cvideo['title'],'desc'=>$cvideo['desc']));
		}
		$sourcedata=array(
				'uid'=>$_G['uid'],
				'username'=>$_G['username'],
				'url'=>$cvideo['url'],
				'icon'=>$cvideo['img'],
				'desc' =>$cvideo['desc'],
				'title' =>$cvideo['title'],
				'cid'=>$cvideo['cid'],
				'dateline' => $_G['timestamp'],
				'gid'=>$gid
		);
		if($sourcedata['vid']=DB::insert('source_video',($sourcedata),1)){
			C::t('cai_video')->update($cvideo['cid'],array('copys'=>$cvideo['copys']+1));
			$sourcedata['icon']=$sourcedata['icon']?$sourcedata['icon']:geticonfromext('','video');
			
			$icoarr=array(
							'uid'=>$_G['uid'],
							'username'=>$_G['username'],
							'oid'=>$sourcedata['vid'],
							'name'=>self::getFileName($sourcedata['title'],$fid),
							'type'=>'video',
							'dateline'=>$_G['timestamp'],
							'pfid'=>$fid,
							'opuid'=>$_G['uid'],
							'gid'=>$gid,
							'ext'=>'swf',
							'flag'=>'',
							'size'=>0
						  );
			if($icoarr['icoid']=DB::insert('icos',($icoarr),1)){
				$icoarr['url']=$sourcedata['url'];
				$icoarr['img']=$sourcedata['icon'];
				$icoarr['bz']='';
				$icoarr['path']=$icoarr['icoid'];
				$icoarr['dpath']=dzzencode($icoarr['icoid']);
				addtoconfig($icoarr);
				$icoarr['fsize']=formatsize($icoarr['size']);
				$icoarr['ftype']=getFileTypeName($icoarr['type'],$icoarr['ext']);
				$icoarr['fdateline']=dgmdate($icoarr['dateline']);
				//if($gid) C::t('group_log')->setLog($gid,$_G['uid'],'addvideo',lang('message','xiezuo_log_addvideo',array('username'=>$_G['username'],'position'=>getPositionName($fid),'name'=>$icoarr['name'])));
			}else{
				C::t('source_video')->delete_by_vid($sourcedata['vid']);
			}
		}
		if($icoarr['icoid'] ){
			return $icoarr;
		}else{
			return array('error' => lang('linktovideo_error'));
		}
	}
	
	public function linktourl($link,$pfid){
		global $_G;
		
		$fid=$pfid;
		$gid=DB::result_first("select gid from %t where fid =%d",array('folder',$pfid));
		$clink=array();
		if(!$clink=DB::fetch_first("select * from ".DB::table("cai_link")." where url='{$link}'")){
			$arr=array();
			require_once dzz_libfile('class/caiji');
			$caiji=new caiji($link);
			$arr['title']=$caiji->getTitle();
			$arr['description']=$caiji->getDescription();
			if($arr['title']){
				$clink=array(
					'url'=>$link,
					'img'=>'',
					'desc' =>$arr['description'],
					'title' => $arr['title'],
					'copys' => 0,
					'dateline'=>$_G['timestamp']
				);
				$clink['cid']=DB::insert('cai_link',($clink),1);
			}
		}
		
		$parseurl=parse_url($link);
		$clink['title']=self::getFileName($clink['title']?$clink['title']:$parseurl['host'],$fid);
		$icondata=getUrlIcon($link);
		$sourcedata=array(
					'uid'=>$_G['uid'],
					'username'=>$_G['username'],
					'url'=>$link,
					'desc' =>$clink['desc'],
					'title' => $clink['title'],
					'cid'=>$clink['cid'],
					'did'=>$icondata['did'],
					'icon'=>$icondata['img'],
					'dateline'=>$_G['timestamp'],
					'gid'=>$gid,
					'ext'=>$icondata['ext']
					
					);
		if($sourcedata['lid']=DB::insert('source_link',($sourcedata),1)){
			if($sourcedata['did']) C::t('icon')->update_copys_by_did($sourcedata['did'],1);
			if($sourcedata['cid']) C::t('cai_link')->update($clink['cid'],array('copys'=>$clink['copys']+1));
			
			$icoarr=array(
							'uid'=>$_G['uid'],
							'username'=>$_G['username'],
							'oid'=>$sourcedata['lid'],
							'name'=>$sourcedata['title'],
							'flag'=>'',
							'type'=>'link',
							'dateline'=>$_G['timestamp'],
							'pfid'=>$fid,
							'opuid'=>$_G['uid'],
							'gid'=>$gid,
							'ext'=>$sourcedata['ext'],
							'size'=>0
						);
			if($icoarr['icoid']=DB::insert('icos',($icoarr),1)){
				//$icoarr=array_merge($sourcedata,$icoarr);
				$icoarr['url']=$sourcedata['url'];
				$icoarr['img']=$sourcedata['icon'];
				$icoarr['bz']='';
				$icoarr['path']=$icoarr['icoid'];
				$icoarr['dpath']=dzzencode($icoarr['icoid']);
				addtoconfig($icoarr);
				$icoarr['container']=$container;
				
				$icoarr['fsize']=formatsize($icoarr['size']);
				$icoarr['ftype']=getFileTypeName($icoarr['type'],$icoarr['ext']);
				$icoarr['fdateline']=dgmdate($icoarr['dateline']);
				//if($gid) C::t('group_log')->setLog($gid,$_G['uid'],'addlink',lang('message','xiezuo_log_addlink',array('username'=>$_G['username'],'position'=>getPositionName($fid),'name'=>$icoarr['name'])));
			}else{
				C::t('soouce_link')->delete($sourcedata['lid']);	
			}
		}
		if($icoarr['icoid'] ){
			return $icoarr;
		}else{
			return array('error' => lang('linktourl_error'));
		}
	}
		
	/**
	 * 移动文件到目标位置
	 * @param string $opath 被移动的文件路径
	 * @param string $path 目标位置（可能是同一api内或跨api，这两种情况分开处理）
	 * @return icosdatas
	 */
	public function CopyTo($icoid,$path,$iscopy){
		try{
			$data=self::getMeta($icoid);
			if(is_numeric($path)){//如果目标位置也是本地
				
				if(!$iscopy){
					$re=self::FileMove($icoid,$path,true);
					$data['newdata']=$re['icoarr'];
					$data['success']=true;
					$data['moved']=true;
				}else{
					$re=self::FileCopy($icoid,$path,true);
					$data['newdata']=$re['icoarr'];
					$data['success']=true;
				}
				if($re['error']) $data['success']=$re['error'];
				
			}else{
				
				switch($data['type']){
					case 'folder'://创建目录
						if($re=IO::CreateFolder($path,$data['name'])){
							if(isset($re['error']) && intval($re['error_code'])!=31061){
								$data['success']=$arr['error'];
							}else{
								
								$data['newdata']=$re['icoarr'];
								$data['success']=true;
								 $contents=C::t('icos')->fetch_all_by_pfid($data['oid']);
								 foreach($contents as $key=>$value){
									$data['contents'][$key]=self::CopyTo($value['icoid'],$re['folderarr']['path']);
								 }
							}
						}
						break;
					case 'shortcut':case 'discuss':case 'dzzdoc':case 'user':case 'link':case 'music':case 'video':case 'topic':case 'app'://这些内容不能移动到api网盘内；
						$data['success']=lang('document_only_stored_enterprise');
						break;
					default:
						$re=IO::multiUpload($icoid,$path,$data['name']);
						if($re['error']) $data['success']=$re['error'];
						else{
							$data['newdata']=$re;
							$data['success']=true;
						}
						break;
				}
			}
		}catch(Exception $e){
			$data['success']=$e->getMessage();
		}
		$data['iscopy']=$iscopy;
		return $data;
	}
	//本地文件移动到本地其它区域
	public function FileMove($icoid,$pfid,$first=true){
		global $_G,$_GET;
		@set_time_limit(0);
		@ini_set("memory_limit","512M");
		if(!$tfolder=DB::fetch_first("select * from ".DB::table('folder')." where fid='{$pfid}'")){
			return array('error'=>lang('target_location_not_exist'));
		}
		if($icoarr=C::t('icos')->fetch($icoid)){
			if($icoarr['pfid']!=$tfolder['fid']){
				//判断有无删除权限
				if(!perm_check::checkperm('delete',$icoarr)){
					return array('error'=>lang('privilege'));
				}
				if(in_array($icoarr['type'],array('folder','link','video','dzzdoc','shortcut'))){
					if(!perm_check::checkperm_Container($pfid,$icoarr['type'])) {
						return array('error'=>lang('privilege'));
					}
				}elseif(!perm_check::checkperm_Container($pfid,'newtype')){
					return array('error'=>lang('privilege'));
				}
			}
			//判断重复
			if($icoarr['pfid']!=$tfolder['fid'] || $icoarr['isdelete']>0){
				if($icoarr['type']=='folder')	$icoarr['name']=self::getFolderName($icoarr['name'],$tfolder['fid']);
				else $icoarr['name']=self::getFileName($icoarr['name'],$tfolder['fid']);
			}
			//if($icoarr['pfid']!=$tfolder['fid']){
				//判断空间大小
				$ogid=$icoarr['gid'];
				$gid=DB::result_first("select gid from ".DB::table('folder')." where fid='{$pfid}'");
				if($orgid!=$gid && $icoarr['size'] && !SpaceSize($icoarr['size'],$gid)){ 
					return array('error' => lang('inadequate_capacity_space'));
				}
				if($icoarr['type']=='folder'){
					if($folder=C::t('folder')->fetch($icoarr['oid'])){
				
						$folder['uid']=$_G['uid'];
						$folder['username']=$_G['username'];
						$folder['gid']=$gid;
						$folder['pfid']=$pfid;
						$folder['fname']=$icoarr['name'];
						if(C::t('folder')->update($folder['fid'],$folder)){
							foreach(C::t('icos')->fetch_all_by_pfid($folder['fid']) as $value){
								try{
									self::FileMove($value['icoid'],$folder['fid'],false);
									unset($value);
									unset($folder);
								}catch(Exception $e){}
							}
						}
					}else{
						return array('error',lang('folder_not_exist'));
					}
				}
					
				$icoarr['gid']=$gid;
				$icoarr['uid']=$_G['uid'];
				$icoarr['username']=$_G['username'];
				$icoarr['pfid']=$pfid;
				$icoarr['isdelete']=0;
				
				if(C::t('icos')->update($icoarr['icoid'],$icoarr)){
					if($ogid!=$gid){
						if($icoarr['size']>0 ){
							SpaceSize(-$icoarr['size'],$ogid,1);
							SpaceSize($icoarr['size'],$gid,1);
						}
					}
					if(!$first){
						addtoconfig($icoarr);
					}
				}
				
			}else{
				C::t('icos')->update($icoarr['icoid'],array('isdelete'=>0));
				addtoconfig($icoarr);
			}
			if($icoarr['type']=='folder') C::t('folder')->update($icoarr['oid'],array('isdelete'=>0));
			$return['icoarr']=C::t('icos')->fetch_by_icoid($icoarr['icoid']);
			unset($icoarr);
			return $return;
		//}
		return array('error'=>lang('movement_error').'！');
	}
	
	//本地文件复制到本地其它区域
	public function FileCopy($icoid,$pfid,$first=true){
		global $_G,$_GET;
		
		if(!$tfolder=DB::fetch_first("select * from ".DB::table('folder')." where fid='{$pfid}'")){
			return array('error'=>lang('target_location_not_exist'));
		}
		if($icoarr=C::t('icos')->fetch($icoid)){
			
			//判断当前文件有没有拷贝权限；
			if(!perm_check::checkperm('copy',$icoarr)){
				return array('error'=>lang('privilege'));
			}
			//判断目录目录有无当前类型的添加权限
			if(in_array($icoarr['type'],array('folder','link','video','dzzdoc','shortcut'))){
				if(!perm_check::checkperm_Container($pfid,$icoarr['type'])) {
					return array('error'=>lang('privilege'));
				}
			}elseif(!perm_check::checkperm_Container($pfid,'newtype')){
				return array('error'=>lang('privilege'));
			}
			$success=0;
			if($gid=DB::result_first("select gid from ".DB::table('folder')." where fid='{$pfid}'")){
				//判断空间大小
				if(!SpaceSize($icoarr['size'],$gid)){ 
					return array('error' => lang('inadequate_capacity_space'));
				}
			}
			//判断重复
			if($ricoid=self::getRepeatIDByName($icoarr['name'],$pfid,($icoarr['type']=='folder')?true:false)){//如果目录下有同名文件
				
					if($icoarr['type']=='folder')	$icoarr['name']=self::getFolderName($icoarr['name'],$pfid);
					else $icoarr['name']=self::getFileName($icoarr['name'],$pfid);
				
			}
		
			switch($icoarr['type']){
				case 'folder':
					if($folder=C::t('folder')->fetch($icoarr['oid'])){
						$oldfid=$folder['fid'];
						$oldfolder=$folder;
						unset($folder['fid']);
						$folder['uid']=$_G['uid'];
						$folder['username']=$_G['username'];
						$folder['pfid']=$pfid;
						$folder['fname']=$icoarr['name'];
						$folder['gid']=$gid;
						//$folder['perm']=0;
						$folder['dateline']=TIMESTAMP;
						if($folder['fid']=C::t("folder")->insert($folder,1)){
							foreach(C::t('icos')->fetch_all_by_pfid($oldfid) as $value){
								try{
									self::FileCopy($value['icoid'],$folder['fid'],false);
								}catch(Exception $e){}
							}
							$return['folderarr']=$folder;
							$icoarr['oid']=$folder['fid'];
							$success=1;
						}
					}else{
						return array('error',lang('folder_not_exist'));
					}
					break;
				case 'user':
					$success=1;
					break;
				case 'shortcut':
					$shortcut=C::t('source_shortcut')->fetch($icoarr['oid']);
					unset($shortcut['cutid']);
					if($cutid=C::t('source_shortcut')->insert($shortcut,1)){
						$icoarr['oid']=$cutid;
						$success=1;
					}
					break;
				case 'app':
					$success=1;
					break;
				
				case 'image':
					if($image=C::t('source_image')->fetch($icoarr['oid'])){
						$opicid=$image['picid'];
						unset($image['picid']);
						$image['title']=$icoarr['name'];
						$image['uid']=$_G['uid'];
						$image['gid']=$gid;
						$image['username']=$_G['username'];
						if($image['picid']=DB::insert('source_image',$image,1)){
							$image['aid'] && C::t('attachment')->addcopy_by_aid($image['aid']);
							$icoarr['oid']=$image['picid'];
							$success=1;
						}
					}else{
						return array('error',lang('image_not_exist'));
					}
					
					break;
				case 'video':
					if($video=C::t('source_video')->fetch($icoarr['oid'])){
						$video['uid']=$_G['uid'];
						$video['gid']=$gid;
						unset($video['vid']);
						$video['title']=$icoarr['name'];
						$video['username']=$_G['username'];
						if($video['vid']=DB::insert('source_video',$video,1)){
							if($video['cid'] ) DB::query("update ".DB::table('cai_video')." set copys=copys+1 where cid='{$video[cid]}'");
								
							$icoarr['oid']=$video['vid'];
							$success=1;
						}
					}else{
						return array('error',lang('video_not_exist'));
					}
					break;
				case 'music':
					if($music=C::t('source_music')->fetch($icoarr['oid'])){
						unset($music['mid']);
						$music['uid']=$_G['uid'];
						$music['gid']=$gid;
						$music['title']=$icoarr['name'];
						$music['username']=$_G['username'];
						if($music['mid']=DB::insert('source_music',$music,1)){
							if($music['cid']) DB::query("update ".DB::table('cai_music')." set copys=copys+1 where cid='{$music[cid]}'");
								
							$icoarr['oid']=$music['mid'];
							$success=1;
						}
					}else{
						return array('error',lang('video_not_exist'));
					}
					break;
				case 'link':
					if($link=C::t('source_link')->fetch($icoarr['oid'])){
						$olink=$link['lid'];
						unset($link['lid']);
						$link['uid']=$_G['uid'];
						$link['username']=$_G['username'];
						$link['title']=$icoarr['name'];
						$link['gid']=$gid;
						if($link['lid']=DB::insert('source_link',$link,1)){
							if($link['cid']) DB::query("update ".DB::table('cai_link')." set copys=copys+1 where cid='{$link[cid]}'");
							if($link['did']) C::t('icon')->update_copys_by_did($link['did']);	
							$icoarr['oid']=$link['lid'];
							$success=1;
						}
					}else{
						return array('error',lang('link_not_exist'));
					}
					break;
				case 'attach':
					if($attach1=C::t('source_attach')->fetch($icoarr['oid'])){
						$oqid=$attach1['qid'];
						unset($attach1['qid']);
						$attach1['uid']=$_G['uid'];
						$attach['title']=$icoarr['name'];
						$attach1['username']=$_G['username'];
						$attach1['gid']=$gid;
						if($attach1['qid']=DB::insert('source_attach',$attach1,1)){
							$attach1['aid'] && C::t('attachment')->addcopy_by_aid($attach1['aid']);
							$icoarr['oid']=$attach1['qid'];
							$success=1;
						}
					}else{
						return array('error',lang('attach_not_exist'));
					}
					break;
				case 'document':
					if($document=C::t('source_document')->fetch($icoarr['oid'])){
						$odid=$document['did'];
						unset($document['did']);
						$document['uid']=$_G['uid'];
						$document['title']=$icoarr['name'];
						$document['username']=$_G['username'];
						$document['gid']=$gid;
						if($document['did']=DB::insert('source_document',$document,1)){
							$document['aid'] && C::t('attachment')->addcopy_by_aid($document['aid']);
							$icoarr['oid']=$document['did'];
							$success=1;
						}
					}else{
						return array('error',lang('document_not_exist'));
					}
					break;
				case 'dzzdoc':
					if($did=C::t('document')->copy_by_did($icoarr['oid'])){
							$icoarr['oid']=$did;
							$success=1;
					}else{
						return array('error',lang('failed_create_Dzz_document'));
					}
					break;
				default:
					$success=1;
					
			}
				
			if($success){
				unset($icoarr['icoid']);
				$icoarr['gid']=$gid;
				$icoarr['uid']=$_G['uid'];
				$icoarr['username']=$_G['username'];
				$icoarr['pfid']=$pfid;
				$icoarr['dateline']=TIMESTAMP;
				if($icoarr['icoid']=DB::insert('icos',$icoarr,1) ){
					if($icoarr['size']>0){
						SpaceSize($icoarr['size'],$gid,1);
					}
					if(!$first){
						addtoconfig($icoarr);
					}else{
						$return['icoarr']=C::t('icos')->fetch_by_icoid($icoarr['icoid']);
						
						return $return;
					}
				}else{
					return array('error'=>lang('files_allowed_copy'));
				}
			}
		}
		return array('error'=>'copy error');
	}
	
	/*
		表单上传文件保存到attachment表，返回attach数组
	*/
	 function UploadSave($FILE) {
	 global $_G;
		 $ext= strtolower(substr(strrchr($FILE['name'], '.'), 1));
		 $target=self::getPath($ext?('.'.$ext):'','dzz');
		 if($ext && in_array(strtolower($ext),$_G['setting']['unRunExts'])){
			 $unrun=1;
		 }else{
			 $unrun=0;
		 } 
		 $filepath=$_G['setting']['attachdir'].$target;
		if(!save_to_local($FILE['tmp_name'], $filepath)){
			return false;
		}
		$md5=md5_file($filepath);
	
		if($md5 && $attach=DB::fetch_first("select * from ".DB::table('attachment')." where md5='{$md5}'")){
			$attach['filename']=$FILE['name'];
			@unlink($filepath);
			unset($attach['attachment']);
			return $attach;
		}else{
			$remote=0;
			
        	$attach=array(
				'filesize'=>$FILE['size'],
				'attachment'=>$target,
				'filetype'=>strtolower($ext),
				'filename' =>$FILE['name'],
				'remote'=>$remote,
				'copys' => 0,
				'md5'=>$md5,
				'unrun'=>$unrun,
				'dateline' => $_G['timestamp'],
			);
			if($attach['aid']=DB::insert('attachment',($attach),1)){
				C::t('local_storage')->update_usesize_by_remoteid($attach['remote'],$attach['filesize']);
				dfsockopen($_G['siteurl'].'misc.php?mod=movetospace&aid='.$attach['aid'].'&remoteid=0',0, '', '', FALSE, '',1);
				unset($attach['attachment']);
				return $attach;
			}else{
				return false;
			}
		}
    }


	public function multiUpload($opath,$path,$filename,$attach=array(),$ondup="newcopy"){
	/* 
	 * 分块上传文件
	 * param $file:文件路径（可以是url路径，需要服务器开启allow_url_fopen);
	*/
		
		$data=IO::getMeta($opath);
		if($data['error']) return $data;
		$size=$data['size'];
		if(is_array($filepath=IO::getStream($opath))){
			return array('error'=>$filepath['error']);
		}
		//判断大小
		//判断空间大小
		$filename=self::name_filter($filename);
					
		if(strpos($path,'dzz::')===false && strpos($path,'TMP::')===false){
			$gid=DB::result_first("select gid from %t where fid=%d",array('folder',$path));
			if(!SpaceSize($size,$gid)){
				 return array('error' => lang('inadequate_capacity_space'));
			 }
		}
		if(!$handle=fopen($filepath, 'rb')){
			return array('error'=>lang('open_file_error'));
		}
		if(strpos($path,'dzz::')!==false || strpos($path,'TMP::')!==false){
			$file=self::getStream($path.'/'.$filename);
			while (!feof($handle)) {
			  $fileContent= fread($handle, 8192);
			  file_put_contents($file,$fileContent,FILE_APPEND);
			  unset($fileContent);
			}
			fclose($handle);
			return true;
		}else{
			$pathinfo = pathinfo($filename);
			$ext = strtolower($pathinfo['extension']);
			$target=$this->getPath($ext?('.'.$ext):'','dzz');
			$file=getglobal('setting/attachdir').'/'.$target;
			while (!feof($handle)) {
			  $fileContent= fread($handle, 8192);
			  file_put_contents($file,$fileContent,FILE_APPEND);
			  unset($fileContent);
			}
			fclose($handle);
		}
		
		$nfilename=self::getFileName($filename,$path); //重命名
	  
		if($attach=$this->save($target,$nfilename)){
			//return array('error'=>json_encode($attach));
			if($attach['error']){
				  return array('error'=>$attach['error']);
			}else{
				return $this->uploadToattachment($attach,$path);
			}
		} else {
			return array('error'=>'failure');
		}
		
	}
	 public function shenpiCreateFile($fid,$path,$attach){
		$data = self::createFolderByPath($path,$fid);
		return self::uploadToattachment($attach,$data['pfid']);	
	 }
}
?>
