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
require_once DZZ_ROOT.'./core/api/jss_sdk/JingdongStorageService.php';
class io_JSS extends io_api
{
	const T ='connect_storage';
	
	const BZ ='JSS';
	private $icosdatas=array();
	private $bucket='';
	private $_root='';
	private $_rootname='';
	private $perm=0;
	public function __construct($path) {
		$arr = DB::fetch_first("SELECT root,name FROM %t WHERE bz=%s",array('connect',self::BZ));
		$this->_root=$arr['root'];
		$this->_rootname=$arr['name'];
		$this->perm=perm_binPerm::getMyPower();
		//self::init($path);
	}
	public function MoveToSpace($path,$attach){
		global $_G;
	/*
	 *移动附件到百度网盘
	 *
	 */
	
		$arr=self::parsePath($path);
		$arr['object']=str_replace('/','_',$arr['object']);
		
		$jss=self::init($arr['bz']);
		if(is_array($jss) && $jss['error']) return $jss;
		//exit($fpath);
		$obz=io_remote::getBzByRemoteid($attach['remote']);
		if($obz=='dzz'){
			$opath='dzz::'.'./'.$attach['attachment'];
		}else{
			$opath=$obz.'/'.$attach['attachment'];
		}
		
		if(is_array($url=IO::getStream($opath))){
			return array('error'=>$filepath['error']);
		}
		$file=fopen($url,'rb');
		if(!$file){
			return array('error'=>lang('open_file_error'));
		}
	    try{$jss->delete_object($arr['bucket'],$arr['object']);}catch(Exception $e){}
		try{	
		
			$response = $jss->put_mpu_object($arr['bucket'],$arr['object'],$file);
			
		}catch(Exception $e){
			return array('error'=>$e->getMessage());
		}	
		if(!$response->is_ok()){
			return array('error'=>$response->get_code());
		}
		
		return true;
	}
	
	/*
	*初始化OSS 返回oss 操作符
	*/
	public function init($bz,$isguest=0){
		global $_G;
		$bzarr=explode(':',$bz);
		$id=trim($bzarr[1]);
		if(!$root=DB::fetch_first("select access_id,bucket,cloudname,bz,access_key,uid from ".DB::table(self::T)." where id='{$id}'")){
			return array('error'=>'need authorize to '.$bzarr[0]);
		}
		if(!$isguest && $root['uid']>0 && $root['uid']!=$_G['uid']) return array('error'=>'need authorize to JSS');
	
		$access_id=authcode($root['access_id'],'DECODE',$root['bz']);
		if(empty($access_id)) $access_id=$root['access_id'];
		$access_key=authcode($root['access_key'],'DECODE',$root['bz']);
		if($root['cloudname']){
			$this->_rootname=$root['cloudname'];
		}else{
			$this->_rootname.=':'.($root['bucket']?$root['bucket']:cutstr($access_id, 4, $dot = ''));
		}
		$this->bucket=$root['bucket'];
		
		try{
			return new JingdongStorageService($access_id,$access_key);
		}catch(Exception $e){
			return array('error'=>$e->getMessage());
		}
	}
	public function getBucketList($access_id,$access_key){
		$re=array();
		if(!$access_id || !$access_key) return array();
		try{
			$jss = new JingdongStorageService($access_id,$access_key);
			$bucketslist=$jss->list_buckets(); 
			foreach($bucketslist as $jss_bucket) {
			  $re[]= $jss_bucket->get_name() ;
		   }
		   return $re;
		}catch(Exception $e) {
			return array('error'=>$e->getMessage());
		}
	}
	public function authorize(){
		global $_G,$_GET,$clouds;
		if(empty($_G['uid'])) {
			dsetcookie('_refer', rawurlencode(BASESCRIPT.'?mod=connect&op=oauth&bz=JSS'));
			showmessage('to_login', '', array(), array('showmsg' => true, 'login' => 1));
		}
		if(submitcheck('jsssubmit')){
			$access_id=$_GET['access_id'];
			$access_key=$_GET['access_key'];
			$bucket=$_GET['bucket'];
			if(!$access_id || !$access_key) {
				showmessage('input_jd_acc_sec',dreferer());
			}
			
			$jss = new JingdongStorageService($access_id,$access_key);
			try{
				$bucketslist=$jss->list_buckets();
			}catch(Exception $e){
				showmessage('input_jd_acc_sec1',dreferer());
				//showmessage($e->getMessage(),dreferer());
			}
			$type='JSS';
			$uid=defined('IN_ADMIN')?0:$_G['uid'];
			$setarr=array(	'uid'=>$uid,
							'access_id'=>$access_id,
							'access_key'=>authcode($access_key,'ENCODE',$type),
							'bz'=>$type,
							'bucket'=>$bucket,
							'dateline'=>TIMESTAMP,					
						);
			if($id=DB::result_first("select id from ".DB::table(self::T)." where uid='{$uid}' and access_id='{$access_id}' and bucket='{$bucket}'")){
				DB::update(self::T,$token,"id ='{$id}'");
			}else{
				$id=DB::insert(self::T,$setarr,1);
			}
			if(defined('IN_ADMIN')){
				
				$setarr=array('name'=>$clouds[$type]['name'].':'.($bucket?$bucket:cutstr($access_id,4,'')),
								  'bz'=>$type,
								  'isdefault'=>0,
								  'dname'=>self::T,
								  'did'=>$id,
								  'dateline'=>TIMESTAMP
								  );
					if(!DB::result_first("select COUNT(*) from %t where did=%d and dname=%s ",array('local_storage',$id,self::T))){
						C::t('local_storage')->insert($setarr);
					}
				showmessage('do_success',BASESCRIPT.'?mod=cloud&op=space');
			}else{
				showmessage('do_success',BASESCRIPT.'?mod=connect');
			}
		}else{
			include template('oauth_JSS');
		}
	}
	public function Path_Base64encode($object){
		//$object=str_replace(FOLDERBZ,'/',$object);
		/*$arr=explode('/',$object);
		
		foreach($arr as $key=> $value){
			if($key==count($arr)-1) contine;
			if($value) $arr[$key]=str_replace('%','_0-0_',rawurlencode($value));
		}*/
		return $object;
		return str_replace('%','_0-0_',str_replace(rawurlencode('/'),'/',rawurlencode($object)));
		return implode('/',$arr);
	}
	public function Path_Base64decode($object){
		/*$object=str_replace(FOLDERBZ,'/',$object);
		$arr=explode('/',$object);
		
		foreach($arr as $key=> $value){
			if($key==count($arr)-1) contine;
			
			if($temp=rawurldecode($value,true)){
				$arr[$key]=$temp;
			}
		}*/
		return $object;
		return rawurldecode(str_replace(rawurlencode('/'),'/',str_replace('_0-0_','%',$object)));
		return implode('/',$arr);
	}
	public function getBzByPath($path){
		$bzarr=explode(':',$path);
		return $bzarr[0].':'.$bzarr[1].':';
	}
	public function getFileUri($path){
		$arr=self::parsePath($path);
		$jss=self::init($path,1);
		if(is_array($jss) && $jss['error']) return $jss;
		try{
		    return  $jss->get_object_resource($arr['bucket'],$arr['object'],60*60*2);
		}catch(Exception $e){
			return array('error'=>$e->getMessage());
		}	
	}
	//获取缩略图
	public function getThumb($path,$width,$height,$original){
		global $_G;
		$imgcachePath='./imgcache/';
		$cachepath=str_replace(':','/',$path);
		if($original){
			$imgurl=self::getFileUri($path);
			$imginfo=@getimagesize($imgurl);
			header("Content-Type: " . image_type_to_mime_type($imginfo[2]));
			@readfile($imgurl);
			@flush(); @ob_flush();
			exit();
		}
		$target=$imgcachePath.($cachepath).'.'.$width.'_'.$height.'.jpeg';
		if(@getimagesize($_G['setting']['attachdir'].$target)){
			header('Content-Type: image/JPEG');
			@ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
			@readfile($_G['setting']['attachdir'].$target);
			@flush(); @ob_flush();
			exit();
		}
		//生成缩略图
		$imgurl=self::getFileUri($path);
		$imginfo=@getimagesize($imgurl);
		if(is_array($imginfo) && $imginfo[0]<$width && $imginfo[1]<$height) {
			header("Content-Type: " . image_type_to_mime_type($imginfo[2]));
			@ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
			@readfile($imgurl);
			@flush(); @ob_flush();
			exit();
		}
		require_once libfile('class/image');
		$image = new image();
		if($thumb = $image->Thumb($imgurl,$target,$width, $height,1) ){
			header('Content-Type: image/JPEG');
			@ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
			@readfile($_G['setting']['attachdir'].$target);
			@flush(); @ob_flush();
			exit();
		}else{
			header("Content-Type: " . image_type_to_mime_type($imginfo[2]));
			@ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
			@readfile($imgurl);
			@flush(); @ob_flush();
			exit();
		}

	}
	//获取文件流；
	//$path: 路径
	public function getStream($path){ 
		$arr=self::parsePath($path);
		$jss=self::init($path,1);
		if(is_array($jss) && $jss['error']) return $jss;
		try{
		    return  $jss->get_object_resource($arr['bucket'],$arr['object'],60*60*2);
		}catch(Exception $e){
			return array('error'=>$e->getMessage());
		}	
	}
	public function parsePath($path){
		$arr=explode(':',$path);
		$bz=$arr[0].':'.$arr[1].':';
		$arr1=explode('/',$arr[2]);
		//if(count($arr1)>1){
		 $bucket=$arr1[0];
		 unset($arr1[0]);
		//}else $bucket='';
		//if(!$bucket) return array('error'=>'bucket不能为空');
		$object=implode('/',$arr1);
		$object=str_replace('/','_',$object);
		return array('bucket'=>$bucket,'object'=>$object,'bz'=>$bz);
	}
	//重写文件内容
	//@param number $path  文件的路径
	//@param string $data  文件的新内容
	public function setFileContent($path,$data){
		$patharr=explode('/',$path);
		$filename=$patharr[count($patharr)-1];
		unset($patharr[count($patharr)-1]);
		$path1=implode('/',$patharr).'/';
		return self::upload_by_content($data,$path1,$filename,'overwrite');
	}
	
	/**
		 * 上传文件
		 * @param string $fileContent 文件内容字符串
		 * @param string $path 上传文件的目标保存路径
		 * @param string $fileName 文件名
		 * @param string $newFileName 新文件名
		 * @param string $ondup overwrite：目前只支持覆盖。 
		 * @return string
		 */
	public function upload_by_content($fileContent,$path,$filename,$ondup='overwrite'){
		global $_G;
		$path.=$filename;
		$arr=self::parsePath($path);
		
		//生成流
		$temp=tmpfile();
		fwrite ( $temp ,  $fileContent);
		fseek ( $temp ,  0 );
		
		$jss=self::init($path);
		if(is_array($jss) && $jss['error']) return $jss;
		
		try{$jss->delete_object($arr['bucket'],$arr['object']);}catch(Exception $e){}
		try{	
			$response = $jss->put_mpu_object($arr['bucket'],$arr['object'],$temp);
			
		}catch(Exception $e){
			return array('error'=>$e->getMessage());
		}	
		if(!$response->is_ok()){
			return array('error'=>$response->get_code());
		}
		$meta=array(
					'key'=>$arr['object'],
					'size'=>strlen($fileContent),
					'last-modified'=>$response->header['date'],
					);
	
		$icoarr=self::_formatMeta($meta,$arr);
		return $icoarr;
		
	}
	/**
	 * 获取当前用户空间配额信息
	 * @return string
	 */
	public function getQuota($bz) {
		
		return 0;
	}
	/**
	 * 获取指定文件夹下的文件列表
	 * @param string $path 文件路径
	 * @param string $by 排序字段，缺省根据文件类型排序，time（修改时间），name（文件名），size（大小，注意目录无大小）
	 * @param string $order asc或desc，缺省采用降序排序
	 * @param string $limit 返回条目控制，参数格式为：n1-n2。返回结果集的[n1, n2)之间的条目，缺省返回所有条目。n1从0开始。
	 * @param string $force 读取缓存，大于0：忽略缓存，直接调用api数据，常用于强制刷新时。
	 * @return icosdatas
	 */
	public function listFiles($path,$by='time',$marker='',$limit=100,$force=0){ 
		global $_G,$_GET,$documentexts,$imageexts;
			$arr=self::parsePath($path);
			
			$icosdata=array();
			$jss=self::init($path,1);
			if(is_array($jss) && $jss['error']) return $jss;
			if(!$arr['bucket']){
				$bucketslist=$jss->list_buckets();
				
				$icosdata=array();
				foreach($bucketslist as $jss_bucket) {
			
					$arr['bucket']=$jss_bucket->get_name();
					$value['key']='';
					$value['last_modified']=$jss_bucket->get_ctime();
					$value['isdir']=true;
					$value['nextMarker']='';
					$value['IsTruncated']=false;
					$icoarr=self::_formatMeta($value,$arr);
					$icosdata[$icoarr['icoid']]=$icoarr;
				}
				//print_r($arr);exit($path);
				//print_r($folderarr);exit('ddddd');
			}else{
				$icos=array();
				$jssentity=$jss->list_objects($arr['bucket'],array('prefix'=>$arr['object'],'marker'=>$marker,'max-keys'=>$limit,'delimiter'=>FOLDERBZ));
				$objects = $jssentity->get_object();  
				
				  foreach($objects as $object) {
					 $icos[]=$object->to_array();
				  }
				foreach($icos as $key => $value){
					//print_r($value);
						$value['key']=self::Path_Base64decode(str_replace(FOLDERBZ,'/',$value['key']));
					//	print_r($value);exit('dddf');
						$icoarr=self::_formatMeta($value,$arr);
						$icosdata[$icoarr['icoid']]=$icoarr;
					
				}
				if($jssentity->get_commonPrefix()) $folders=$jssentity->get_commonPrefix();
				$folder=$value=array();
				foreach($folders as $key => $value){
						$folder['isdir']=true;
						
						$folder['key']=self::Path_Base64decode(str_replace(FOLDERBZ,'/',$value));
						//print_r($folder);exit();
						$icoarr=self::_formatMeta($folder,$arr);
						
						$icosdata[$icoarr['icoid']]=$icoarr;
						
					
				}
				/*print_r($jssentity);
				print_r($icos);
				print_r($folders);exit();*/
				///*
				
				$value=array();
				$value['isdir']=true;
				$value['key']=rtrim($jssentity->get_prefix(),'%');
				$value['key']=self::Path_Base64decode(str_replace(FOLDERBZ,'/',$value['key']));
				$value['nextMarker']=$jssentity->get_nextmarker();
				$value['IsTruncated']=$jssentity->get_hasNext();
				$icoarr=self::_formatMeta($value,$arr);
				if($icosdata[$icoarr['icoid']]){
					$icosdata[$icoarr['icoid']]['nextMarker'] =$icoarr['nextMarker'];
					$icosdata[$icoarr['icoid']]['IsTruncated'] =$icoarr['IsTruncated'];
				}else{
					$icosdata[$icoarr['icoid']]=$icoarr;
				}
				
			}
		
	/*	print_r($jssentity);
		print_r($icosdata);
		exit('dfdsf');*/
		return $icosdata;	
	}
	/**
	 * 获取指定文件夹下的文件列表
	 * @param string $path 文件路径
	 * @param string $by 排序字段，缺省根据文件类型排序，time（修改时间），name（文件名），size（大小，注意目录无大小）
	 * @param string $order asc或desc，缺省采用降序排序
	 * @param string $limit 返回条目控制，参数格式为：n1-n2。返回结果集的[n1, n2)之间的条目，缺省返回所有条目。n1从0开始。
	 * @param string $force 读取缓存，大于0：忽略缓存，直接调用api数据，常用于强制刷新时。
	 * @return icosdatas
	 */
	function listFilesAll(&$jss,$path,$limit='1000',$marker='',$icosdata=array()){ 
			$arr=self::parsePath($path);
				$icosdata=array();
			$jss=self::init($path,1);
			if(is_array($jss) && $jss['error']) return $jss;
			if(!$arr['bucket']){
				$bucketslist=$jss->list_buckets();
				
				$icosdata=array();
				foreach($bucketslist as $jss_bucket) {
			
					$arr['bucket']=$jss_bucket->get_name();
					$value['key']='';
					$value['last_modified']=$jss_bucket->get_ctime();
					$value['isdir']=true;
					$value['nextMarker']='';
					$value['IsTruncated']=false;
					$icoarr=self::_formatMeta($value,$arr);
					$icosdata[$icoarr['icoid']]=$icoarr;
				}
				//print_r($arr);exit($path);
				//print_r($folderarr);exit('ddddd');
			}else{
				$icos=array();
				$jssentity=$jss->list_objects($arr['bucket'],array('prefix'=>$arr['object'],'marker'=>$marker,'maxkeys'=>$limit,'delimiter'=>FOLDERBZ));
				
				$objects = $jssentity->get_object();  
				
				  foreach($objects as $object) {
					 $icos[]=$object->to_array();
				  }
				foreach($icos as $key => $value){
						$value['key']=self::Path_Base64decode(str_replace(FOLDERBZ,'/',$value['key']));
						$icoarr=self::_formatMeta($value,$arr);
						$icosdata[$icoarr['icoid']]=$icoarr;
					
				}
				if($jssentity->get_commonPrefix()) $folders=$jssentity->get_commonPrefix();
				$value=array();
				foreach($folders as $key => $value){
					
				
						$folders['isdir']=true;
						$folders['key']=$value;
						$folders['key']=self::Path_Base64decode(str_replace(FOLDERBZ,'/',$folders['key']));
						
						$icoarr=self::_formatMeta($folders,$arr);
						
						$icosdata[$icoarr['icoid']]=$icoarr;
						break;
					
				}
				
				$value=array();
				$value['isdir']=true;
				$value['key']=rtrim($jssentity->get_prefix(),'%');
				$value['key']=self::Path_Base64decode(str_replace(FOLDERBZ,'/',$value['key']));
				$value['nextMarker']=$jssentity->get_nextmarker();
				$value['IsTruncated']=$jssentity->get_hasNext();
				$icoarr=self::_formatMeta($value,$arr);
				if($icosdata[$icoarr['icoid']]){
					$icosdata[$icoarr['icoid']]['nextMarker'] =$icoarr['nextMarker'];
					$icosdata[$icoarr['icoid']]['IsTruncated'] =$icoarr['IsTruncated'];
				}else{
					$icosdata[$icoarr['icoid']]=$icoarr;
				}
				
			}
				
		//exit($data['ListBucketResult']['IsTruncated']);		
		if($jssentity->get_hasNext()){
			$icosdata=self::listFilesAll($jss,$path,1000,$jssentity->get_nextMarker(),$icosdata);
			//self::getFolderObjects($path,1000,$data['ListBucketResult']['nextMarker']);
		}
		return $icosdata;	
	}
	/*
	 *获取文件的meta数据
	 *返回标准的icosdata
	 *$force>0 强制刷新，不读取缓存数据；
	*/
	function getMeta($path,$force=0){ 
		global $_G,$_GET,$documentexts,$imageexts;
		$arr=self::parsePath($path);
		$icosdata=array();
		$jss=self::init($path,1);
		if(is_array($jss) && $jss['error']) return $jss;
		if(empty($arr['object']) || empty($arr['bucket'])){
			$meta=array(
						'key'=>'',
						'size'=>0,
						'last_modified'=>'',
						'isdir'=>true
						);
		}else{
			try{
				$response=$jss->head_object($arr['bucket'],$arr['object']);
			}catch(Exception $e){
				return array('error'=>$e->getMessage());
			}
			if(!$response->is_ok()){
				return array('error'=>$response->get_code());
			}
			$return=$response->get_headers();
		
			$meta=array(
						'key'=>self::Path_Base64decode(str_replace($arr['bz'].$arr['bucket'].'/','',$path)),
						'size'=>$return['content-length'],
						'last_modified'=>$return['last-modified'],
						);
		}
		$icosdata=self::_formatMeta($meta,$arr);
		return $icosdata;
	}
	//将api获取的meta数据转化为icodata
	function _formatMeta($meta,$arr){ 
		global $_G,$documentexts,$imageexts;
		$icosdata=array();
		//print_r($meta);print_r($arr);
		
		
		if(strrpos($meta['key'],'/')==(strlen($meta['key'])-1)) $meta['isdir']=true;
		
		if($meta['isdir']){
			if(!$meta['key']){
				if($this->bucket){
					$name=$this->bucket;
					$pfid=0;
					$pf='';
					$flag='';
				}elseif($arr['bucket']){
					$name=$arr['bucket'];
					$pfid=md5($arr['bz']);
					$pf='';
					$flag='';
				}else{
					$name=$this->_rootname;
					$pfid=0;
					$pf='';
					$flag=self::BZ;
				}
				if($arr['bucket']) $arr['bucket'].='/';
			}else{
				if($arr['bucket']) $arr['bucket'].='/';
				$namearr=explode('/',$meta['key']);
				$name=$namearr[count($namearr)-2];
				$pf='';
				for($i=0;$i<(count($namearr)-2);$i++){
					$pf.=$namearr[$i].'/';
				}
				$pf=$arr['bucket'].$pf;
				$pfid=md5($arr['bz'].$pf);
				$flag='';
			}
			
			//print_r($namearr);
			
			$icoarr=array(
				  'icoid'=>md5(($arr['bz'].$arr['bucket'].$meta['key'])),
				  'path'=>$arr['bz'].$arr['bucket'].$meta['key'],
				  'dpath'=>dzzencode($arr['bz'].$arr['bucket'].$meta['key']),
				  'bz'=>($arr['bz']),
				  'gid'=>0,
				  'name'=>$name,
				  'username'=>$_G['username'],
				  'uid'=>$_G['uid'],
				  'oid'=>md5($arr['bz'].$arr['bucket'].$meta['key']),
				  'img'=>'dzz/images/default/system/folder.png',
				  'type'=>'folder',
				  'ext'=>'',
				  'pfid'=>$pfid,
				  'ppath'=>$arr['bz'].$pf,
				  'size'=>0,
				  'dateline'=>strtotime($meta['last_modified']),
				  'flag'=>$flag,
				  'nextMarker'=>$meta['nextMarker'],
				  'IsTruncated'=>$meta['IsTruncated'],
				 );
				
				$icoarr['fsize']=formatsize($icoarr['size']);
				$icoarr['ftype']=getFileTypeName($icoarr['type'],$icoarr['ext']);
				$icoarr['fdateline']=dgmdate($icoarr['dateline']);
				$icosdata=$icoarr;
			//print_r($icosdata);
			//exit($meta['Key']);
		}else{
			if($arr['bucket']) $arr['bucket'].='/';
			$namearr=explode('/',$meta['key']);
			$name=$namearr[count($namearr)-1];
			$pf='';
			for($i=0;$i<count($namearr)-1;$i++){
				$pf.=$namearr[$i].'/';
			}
			$ext=strtoupper(substr(strrchr($meta['key'], '.'), 1));
			if(in_array($ext,$imageexts)) $type='image';
			elseif(in_array($ext,$documentexts)) $type='document';
			else $type='attach';
			if($type=='image'){
				$img=$_G['siteurl'].DZZSCRIPT.'?mod=io&op=thumbnail&width=256&height=256&path='.dzzencode($arr['bz'].$arr['bucket'].$meta['key']);
				$url=$_G['siteurl'].DZZSCRIPT.'?mod=io&op=thumbnail&width=1440&height=900&path='.dzzencode($arr['bz'].$arr['bucket'].$meta['key']);
			}else{
				$img=geticonfromext($ext,$type);
				$url=$_G['siteurl'].DZZSCRIPT.'?mod=io&op=getStream&path='.dzzencode($arr['bz'].$arr['bucket'].$meta['key']);;
			}
			
			$icoarr=array(
						  'icoid'=>md5(($arr['bz'].$arr['bucket'].$meta['key'])),
						  'path'=>($arr['bz'].$arr['bucket'].$meta['key']),
						  'dpath'=>dzzencode($arr['bz'].$arr['bucket'].$meta['key']),
						  'bz'=>($arr['bz']),
						  'gid'=>0,
						  'name'=>$name,
						  'username'=>$_G['username'],
						  'uid'=>$_G['uid'],
						  'oid'=>md5(($arr['bz'].$arr['bucket'].$meta['key'])),
						  'img'=>$img,
						  'url'=>$url,
						  'type'=>$type,
						  'ext'=>strtolower($ext),
						  'pfid'=>md5($arr['bz'].$arr['bucket'].$pf),
						  'ppath'=>$arr['bz'].$arr['bucket'].$pf,
						  'size'=>$meta['size'],
						  'dateline'=>strtotime($meta['last_modified']),
						  'flag'=>''
						  );
			$icoarr['fsize']=formatsize($icoarr['size']);
			$icoarr['ftype']=getFileTypeName($icoarr['type'],$icoarr['ext']);
			$icoarr['fdateline']=dgmdate($icoarr['dateline']);
			$icosdata=$icoarr;
		}
		
		return $icosdata;
	}
	//根据路径获取目录树的数据；
	public function getFolderDatasByPath($path){ 
		$bzarr=self::parsePath($path); 
		$spath=$bzarr['object'];
		
		if(!$this->bucket && $bzarr['bucket']){
			$spath=$bzarr['bucket'].'/'.$spath;
			$bzarr['bucket']='';
		}
		$spath='/'.trim($spath,'/');
		$spath=rtrim($spath,'/');
		$patharr=explode('/',$spath);
		$folderarr=array();
		for($i=0;$i<count($patharr);$i++){
			$path1=$bzarr['bz'].$bzarr['bucket'];
			for($j=0;$j<=$i;$j++){
				$path1.=$patharr[$j];
			}
			if($arr=self::getMeta($path1)){
				
				if(isset($arr['error'])) continue;
				$folder=self::getFolderByIcosdata($arr);
				$folderarr[$folder['fid']]=$folder;
			}
		}
		return $folderarr;
	}
	//通过icosdata获取folderdata数据
	function getFolderByIcosdata($icosdata){
		global $_GET;
		$folder=array();
		//通过path判断是否为bucket
		$path=$icosdata['path'];
		$arr=self::parsePath($path);
		if(!$arr['bucket']){ //根目录
			$fsperm=perm_FolderSPerm::flagPower('bucklist');
		}else{
			$fsperm=perm_FolderSPerm::flagPower('external');
		}
		if($icosdata['type']=='folder'){
			$folder=array('fid'=>$icosdata['oid'],
						  'path'=>$icosdata['path'],
						  'fname'=>$icosdata['name'],
						  'uid'=>$icosdata['uid'],
						  'pfid'=>$icosdata['pfid'],
						  'ppath'=>$icosdata['ppath'],
						  'iconview'=>$_GET['iconview']?intval($_GET['iconview']):0,
						  'disp'=>$_GET['disp']?intval($_GET['disp']):0,
						  'perm'=>$this->perm,
						  'hash'=>$icosdata['hash'],
						  'bz'=>$icosdata['bz'],
						  'gid'=>$icosdata['gid'],
						  'fsperm'=>$fsperm,
						  'nextMarker'=>$icosdata['nextMarker'],
				  		  'IsTruncated'=>$icosdata['IsTruncated'],
						);
			//print_r($folder);
		}
		return $folder;
	}
	//获得文件内容；
	function getFileContent($path){
		$arr=self::parsePath($path);
		$url=self::getFileUri($path);
		if(is_array($url)) return '';
		return file_get_contents($url);
	}
	//打包下载文件
	public function zipdownload($path){
		global $_G;
		set_time_limit(0);
		$meta=self::getMeta($path);
		include_once libfile('class/ZipStream');
		$filename=(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($meta['name']) : $meta['name']);
		$zip = new ZipStream($filename.".zip");
		$data=self::getFolderInfo($path,'',$zip);
		/*if($data['error']){
			topshowmessage($data['error']);
			exit();
		}*/
		/*foreach($data as $value){
			 $zip->addLargeFile(fopen($value['url'],'rb'), $value['position'], $value['dateline']);
		}*/
		$zip->finalize();
	}
	public function getFolderInfo($path,$position='',$zip){
		static $data=array();
		try{
			$arr=IO::parsePath($path);
			$jss=self::init($path,1); 
			if(is_array($jss) && $jss['error']) return $jss;
			$meta=self::getMeta($path);
			switch($meta['type']){
				case 'folder':
					  $position.=$meta['name'].'/';
					 
					   $contents=self::listFilesAll($jss,$path);
					 foreach($contents as $key=>$value){
						 if($value['path']!=$path){
							self::getFolderInfo($value['path'],$position,$zip);
						 }
					 }
					break;
				default:
				 $meta['url']=self::getStream($meta['path']);
				 $meta['position']=$position.$meta['name'];
				 //$data[$meta['icoid']]=$meta;
				  $zip->addLargeFile(fopen($meta['url'],'rb'), $meta['position'], $meta['dateline']);
			}
		
		}catch(Exception $e){
			//var_dump($e);
			$data['error']=$e->getMessage();
			return $data;
		}
		return $data;
	}
	//下载文件
	public function download($path){
		global $_G;
		$path=rawurldecode($path);
		
		//header("location: $url");
		try {
			$url=self::getStream($path);
			// Download the file
			$file=self::getMeta($path);
			if($file['type']=='folder'){
				self::zipdownload($path);
				exit();
			}
			//$file['data'] = self::getFileContent($path);
			//if($file['data']['error']) topshowmessage($file['data']['error']);
			$file['name'] = '"'.(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($file['name']) : $file['name']).'"';
			
			dheader('Date: '.gmdate('D, d M Y H:i:s', $file['dateline']).' GMT');
			dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', $file['dateline']).' GMT');
			dheader('Content-Encoding: none');
			dheader('Content-Disposition: attachment; filename='.$file['name']);
			dheader('Content-Type: application/octet-stream');
			dheader('Content-Length: '.$file['size']);
			
			@ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
			/*$handle=fopen($url, 'r');
			while (!feof($handle)) {
			  echo fread($handle, 8192);@flush(); @ob_flush();
			}
			fclose($handle);*/
			@readfile($url);
			//echo $file['data'];
			@flush(); @ob_flush();
			exit();
		} catch (Exception $e) {
			// The file wasn't found at the specified path/revision
			//echo 'The file was not found at the specified path/revision';
			topshowmessage($e->getMessage());
		}
	}
	
	
	
	
	//获取目录的所有下级和它自己的object
	public function getFolderObjects(&$jss,$path,$limit='1000',$marker=''){
		static $objects=array();
		$arr=self::parsePath($path);
		//echo( $path.'---------');
		$jssentity=$jss->list_objects($arr['bucket'],array('prefix'=>$arr['object'],'marker'=>$marker,'maxkeys'=>$limit,'delimiter'=>''));
		
		$objects = $jssentity->get_object();  
		foreach($objects as $object) {
		 $icos[]=$object->to_array();
		}
		if($jssentity->get_commonPrefix()) $folders=$jssentity->get_commonPrefix();
		$value=array();
		foreach($icos as $key => $value){
			if(is_array($value)){
				$objects[]=self::Path_Base64decode(str_replace(FOLDERBZ,'/',$value['key']));
			}else{
				$objects[]=self::Path_Base64decode(str_replace(FOLDERBZ,'/',$icos['key']));
				break;
			}
		}
		$value=array();
		foreach($folders as $key => $value){
			$objects[]=self::Path_Base64decode(str_replace(FOLDERBZ,'/',$value));
		}
		if($jssentity->get_hasNext()){
			self::getFolderObjects($jss,$path,1000,$jssentity->get_nextMarker());
		}
		return $objects;
	}
	
	//删除原内容
	//$path: 删除的路径
	//$bz: 删除的api;
	//$data：可以删除的id数组（当剪切的时候，为了保证数据不丢失，目标位置添加成功后将此id添加到data数组，
	//删除时如果$data有数据，将会只删除id在$data中的数据；
	//如果删除的是目录或下级有目录，需要判断此目录内是否所有元素都在删除的id中，如果有未删除的元素，则此目录保留不会删除；
	//
	public function Delete($path,$false=false){
		//global $dropbox;
		$arr=self::parsePath($path);
		try{
			$jss=self::init($path);
			if(is_array($jss) && $jss['error']) return $jss;
			//判断删除的对象是否为文件夹
			if(strrpos($arr['object'],'/')==(strlen($arr['object'])-1)){ //是文件夹
				$objects=self::getFolderObjects($jss,$path);
				foreach($objeces as $object){
					$response = $jss->delete_object($arr['bucket'],self::Path_Base64encode(str_replace('/',FOLDERBZ,$object)));
					if(!$response->is_ok()){
						return array('error'=>$response->get_code());
					}
				}
			}else{
				$response = $jss->delete_object($arr['bucket'],$arr['object']);
			}
			if(!$response->is_ok()){
				return array('error'=>$response->get_code());
			}

			return array('icoid'=>md5(($path)),
						 'name'=>substr(strrchr($path, '/'), 1),
						);
		}catch(Exception $e){
			return array('icoid'=>md5($path),'error'=>$e->getMessage());
		}
	}
	//添加目录
	//$fname：目录路径;
	//$container：目标容器
	//$bz：api;
	public function CreateFolder($path,$fname){
		global $_G;
		$arr=self::parsePath($path);
		//exit('createrfolder==='.$fname.'===='.$path1.'===='.$bz);
		//exit($path.$fname.'vvvvvvvvvvv');
		$return=array();
		return array('error'=>lang('temporary_support_directory'));
		try {
			$jss=self::init($path);
			if(is_array($jss) && $jss['error']) return $jss;
			//exit(str_replace('/',FOLDERBZ,$arr['object'].$fname));
			$response=$jss->put_object_dir($arr['bucket'],self::Path_Base64encode(str_replace('/',FOLDERBZ,$arr['object'].$fname)));
			//print_r($response);
			if(!$response->is_ok()){
				return array('error'=>$response->get_code());
			}
			$meta=array('isdir'=>true,
						'key'=>self::Path_Base64decode($arr['object'].$fname.'/'),
						'size'=>0,
						'last-modified'=>$response->get_header['date'],
						);
			$icoarr=self::_formatMeta($meta,$arr);
			
			$folderarr=self::getFolderByIcosdata($icoarr);
			$return= array('folderarr'=>$folderarr,'icoarr'=>$icoarr);
		}catch(Exception $e){
			//var_dump($e);
			$return=array('error'=>$e->getMessage());
		}
		return $return;
	}
	//获取不重复的目录名称
	public function getFolderName($name,$path){
		static $i=0;
		if(!$this->icosdatas) $this->icosdatas=self::listFiles($path);
		$names=array();
		foreach($icosdatas as $value){
			$names[]=$value['name'];
		}
		if(in_array($name,$names)){
			$name=str_replace('('.$i.')','',$name).'('.($i+1).')';
			$i+=1;
			return self::getFolderName($name,$path);
		}else {
			return $name;
		}
	}
	private function getCache($path){
		$cachekey='JSS_uploadID_'.md5($path);
		$cache=C::t('cache')->fetch($cachekey);
		return unserialize($cache['cachevalue']);
	}
	private function saveCache($path,$data){
		global $_G;
		$cachekey='JSS_uploadID_'.md5($path);
		C::t('cache')->insert(array(
							'cachekey' => $cachekey,
							'cachevalue' => serialize($data),
							'dateline' => $_G['timestamp'],
						), false, true);
	}
	private function deleteCache($path){
		$cachekey='JSS_uploadID_'.md5($path);
		C::t('cache')->delete($cachekey);
	}
	private function getPartInfo($content_range){
		$arr=array('partsize'=>getglobal('setting/maxChunkSize'),'size'=>$content_range[3]);
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
	public function uploadStream($file,$filename,$path,$relativePath,$content_range){
		$data=array();
		$arr=self::getPartInfo($content_range);
		//echo ($relativePath).'vvvvvvvv';
		//if($arr['partnum']>1) print_r($arr);
		/*if($relativePath && ($arr['iscomplete'])){
			$path1=$path;
			$patharr=explode('/',$relativePath);
			//print_r($patharr);
			foreach($patharr as $key=> $value){
				if(!$value){
					continue;
				}
		//	echo $path1.'---'.$value.'------';
				$re=self::CreateFolder($path1,$value);
				if(isset($re['error'])){
					return $re;
				}else{
					if($key==0){
						$data['icoarr'][]=$re['icoarr'];
						$data['folderarr'][]=$re['folderarr'];
					}
				}
				$path1=$path1.$value.'/';
			}
		}*/
		$path.=$relativePath;
		if($arr['ispart']){
			if($re1=self::upload($file,$path,$filename,$arr)){
				
				if($re1['error']){
					return $re1;
				}
				if($arr['iscomplete']){
					if(empty($re1['error'])){
						$data['icoarr'][] = $re1;
						return $data;
					}else{
						$data['error'] = $re1['error'];
						return $data;
					}
				}else{
					return true;
				}
			}
		}else{
			$re1=self::upload($file,$path,$filename);
			if(empty($re1['error'])){
				$data['icoarr'][] = $re1;
				return $data;
			}else{
				$data['error'] = $re1['error'];
				return $data;
			}
		}
	}
	function upload($file,$path,$filename,$partinfo=array(),$ondup='overwrite'){
		global $_G;
		$path.=$filename;
		$arr=self::parsePath($path);
	
		try{
			$jss=self::init($path);
			if(is_array($jss) && $jss['error']) return $jss;
			
			if($partinfo['partnum']){
				
				if($partinfo['partnum']==1){//第一个分块时 初始化分块上传得到$uploadID;并缓存住，留以后分块使用
					
					$filepath=$_G['setting']['attachdir'].'./cache/'.random(5).$filename;
					
					if(!$handle=fopen($file,'rb')){
						return array('error'=>lang('open_file_error'));
					}
					file_put_contents($filepath,fread($handle,filesize($file)),FILE_APPEND);
					fclose($handle);
					$data['filepath']=$filepath;
					/*//初始化分块
					$response=$jss->init_multipart_upload($arr['bucket'],self::Path_Base64encode($arr['object']));
					
					$upload_id=$response->get_uploadid();
					$request_headers = array(
						SEEK_TO_TAG => 0,
						CONTENT_LENGTH_TAG => intval($partinfo['partsize']),
						'expect' => '100-continue',
					);
					//上传分块
					$response=$jss->upload_part($arr['bucket'],self::Path_Base64encode($arr['object']), $upload_id, $partinfo['partnum'],$file,$request_headers);
					if(!$response->is_ok()){
						return array('error'=>'upload partNember '.$partinfo['partnum'].' error');
					}
					$data=array();
					$data['upload_id']=$upload_id;
					$data['parts'][]=array('PartNumber'=>$partinfo['partnum'],'ETag'=>trim($response->get_header('etag'),'"'));
					//print_r($data);exit('ddddddddddddddd');*/
					
					self::saveCache($path,$data);
				}else{
					$cache=self::getCache($path);
					$filepath=$cache['filepath'];
					if(!$handle=fopen($file,'rb')){
						return array('error'=>lang('open_file_error'));
					}
					file_put_contents($filepath,fread($handle,filesize($file)),FILE_APPEND);
					fclose($handle);
					/*$upload_id=$cache['upload_id'];
					//上传分块
					$request_headers = array(
						SEEK_TO_TAG => intval($partinfo['partsize']*($partinfo['partnum']-1)),
						CONTENT_LENGTH_TAG =>$partinfo['iscomplete']?intval($partinfo['size']-$partinfo['partsize']*($partinfo['partnum']-1)):$partinfo['partsize'] ,
						'expect' => '100-continue',
					);
					$response=$jss->upload_part($arr['bucket'],$arr['object'], $upload_id, $partinfo['partnum'],$file,$request_headers);
					
					if(!$response->is_ok()){
						return array('error'=>'upload partNember '.$partinfo['partnum'].' error');
					}
					//print_r($cache);
					
					$cache['parts'][]=array('PartNumber'=>$partinfo['partnum'],'ETag'=>trim($response->get_header('etag'),'"'));
					//print_r($cache);exit('dddd');
					self::saveCache($path,$cache);*/
				}
				
				if($partinfo['iscomplete']){
					$cache=self::getCache($path);
					$filepath=$cache['filepath'];
					if(!$handle=fopen($filepath,'rb')){
						return array('error'=>lang('open_file_error'));
					}
					try{	
						$response = $jss->put_mpu_object($arr['bucket'],$arr['object'],$handle);
					}catch(Exception $e){
						return array('error'=>$e->getMessage());
					}	
					if(!$response->is_ok()){
						return array('error'=>$response->get_code());
					}
					/*//print_r($cache);
					$response = $jss->complete_multipartupload($arr['bucket'],$arr['object'], $cache['upload_id'],json_encode(($cache['parts'])));
					//print_r($response);exit(json_encode($cache['parts']));
					if(!$response->is_ok()){
						return array('error'=>$response->get_code());
					}*/
					self::deleteCache($path);
					$meta=array(
								'key'=>$arr['object'],
								'size'=>filesize($filepath),
								'last-modified'=>$response->get_header['date'],
								);
				
					$icoarr=self::_formatMeta($meta,$arr);
					return $icoarr;
				}else{
					return true;
				}
			}else{
				//exit((str_replace('/',FOLDERBZ,rawurldecode($arr['object']))));
				$response = $jss->put_mpu_object($arr['bucket'],$arr['object'],$file);
				
				if(!$response->is_ok()){
					return array('error'=>$response->get_code());
				}
				$meta=array(
							 'key'=>self::Path_Base64decode($arr['object']),
							 'size'=>filesize($file),
							 'last-modified'=>$response->get_header['date'],
							);
				$icoarr=self::_formatMeta($meta,$arr);
				return $icoarr;
			}
		}catch(Exception $e){
			return array('error'=>$e->getMessage());
		}
		
	}
	/**
	 * 移动文件到目标位置
	 * @param string $opath 被移动的文件路径
	 * @param string $path 目标位置（可能是同一api内或跨api，这两种情况分开处理）
	 * @return icosdatas
	 */
	public function CopyTo($opath,$path,$iscopy){
		static $i=0;
		$i++;
		$oarr=self::parsePath($opath);
		$arr=IO::parsePath($path);
		
		$jss=self::init($opath);
		if(is_array($jss) && $jss['error']) return $jss;
		try{
			$data=self::getMeta($opath);
			switch($data['type']){
				case 'folder'://创建目录
					//exit($arr['path'].'===='.$data['name']);
					if($re=IO::CreateFolder($path,$data['name'])){
						if(isset($re['error'])){
								$data['success']=$arr['error'];
						}else{
							
							$data['newdata']=$re['icoarr'];
							$data['success']=true;
							//echo $opath.'<br>';
							 $contents=self::listFilesAll($jss,$opath);
							$value=array();
							 foreach($contents as $key=>$value){
								if($value['path']!=$opath){
									$data['contents'][$key]=self::CopyTo($value['path'],$re['folderarr']['path']);
								}
								$value=array();
							 }
						}
					}else{
						$data['success']='create folder failure';
					}
					
					break;
				
				default:
					/*if($arr['bz']==$oarr['bz']){//同一个api时
						$arr=self::parsePath($path.$data['name']);
						$response=$jss->copy_object($oarr['bucket'],$oarr['object'],$arr['bucket'],$arr['object']);
						if(!$response->is_ok()){
							$data['success']=$response->status;
						}
						$meta=array(
									'Key'=>$arr['object'],
									'Size'=>$data['size'],
									'LastModified'=>$response->header['date'],
									);
						$data['newdata']=self::_formatMeta($meta,$arr);
						
						$data['success']=true;
					}else{*/
						
						if($re=IO::multiUpload($opath,$path,$data['name'])){
							if($re['error']) $data['success']=$re['error'];
							else{
								$data['newdata']=$re;
								$data['success']=true;
							}
						}
					//}
					break;
			}
		//	}
		}catch(Exception $e){
			//var_dump($e);
			$data['success']=$e->getMessage();
			return $data;
		}
		return $data;
	}
	public function multiUpload($opath,$path,$filename,$attach=array(),$ondup="newcopy"){
		global $_G;
		/* 
	 * 分块上传文件
	 * param $file:文件路径（可以是url路径，需要服务器开启allow_url_fopen);
	*/
		@set_time_limit(0);
		$partsize=1024*1024*2; //分块大小2M
		if($attach){
			$data=$attach;
			$data['size']=$attach['filesize'];
		}else{
			$data=IO::getMeta($opath);
			if($data['error']) return $data;
		}
		if($data['error']) return $data;
		$size=$data['size'];
		
		
		if(is_array($filepath=IO::getStream($opath))){
			return array('error'=>$filepath['error']);
		}
		
		//exit(($size<$partsize).'===='.$size.'==='.$filepath.'===='.$path);
		if($size<$partsize){
			//获取文件内容
			$fileContent='';
			if(!$handle=fopen($filepath, 'rb')){
				return array('error'=>lang('open_file_error'));
			}
			while (!feof($handle)) {
			  $fileContent .= fread($handle, 8192);
			  //if(strlen($fileContent)==0) return array('error'=>'文件不存在');
			}
			fclose($handle);
			//exit('upload');
			return self::upload_by_content($fileContent,$path,$filename);
		}else{ //分片上传		

			$partinfo=array('ispart'=>true,'partnum'=>0,'iscomplete'=>false);
			if(!$handle=fopen($filepath, 'rb')){
				return array('error'=>lang('open_file_error'));
			}
			//stream_set_timeout($handle,5);
		    $ext=strtolower(substr(strrchr($filename, '.'), 1));
			$cachefile=$_G['setting']['attachdir'].'./cache/'.md5($opath).'.'.$ext;
			while (!feof($handle)) {
				$fileContent.=fread($handle, 8192);
				//if(strlen($fileContent)==0) return array('error'=>'文件不存在');
				if(strlen($fileContent)>$partsize){
					$partinfo['partnum']+=1;
					if($partinfo['partnum']*$partsize>=$size) $partinfo['iscomplete']=true;
					file_put_contents($cachefile,$fileContent);
					$re=self::upload($cachefile,$path,$filename,$partinfo);
					if($re['error']) return $re;
					if($partinfo['iscomplete']){
						 @unlink($cachefile);
						 return $re;
					}
					$fileContent='';
				}
			}
			fclose($handle);
			if(!empty($fileContent)){
				$partinfo['partnum']+=1;
				$partinfo['iscomplete']=true;
				file_put_contents($cachefile,$fileContent);
				$re=self::upload($cachefile,$path,$filename,$partinfo);
				if($re['error']) return $re;
				if($partinfo['iscomplete']){
					 @unlink($cachefile);
					 return $re;
				}
			}
		}
	}
}
?>
