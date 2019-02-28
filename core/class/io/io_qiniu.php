<?php
/* @七牛云存储接口文件
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
 
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
//error_reporting(E_ALL);
@set_time_limit(0);
@ini_set('max_execution_time',0);
include_once DZZ_ROOT.'./core/api/qiniu/rs.php';
require_once(DZZ_ROOT.'./core/api/qiniu/io.php');
require_once(DZZ_ROOT.'./core/api/qiniu/rs.php');
require_once(DZZ_ROOT.'./core/api/qiniu/fop.php');
require_once(DZZ_ROOT.'./core/api/qiniu/rsf.php');
require_once(DZZ_ROOT.'./core/api/qiniu/resumable_io.php');

class io_qiniu extends io_api
{
	const T ='connect_storage';
	const BZ ='qiniu';
	private $icosdatas=array();
	private $bucket='';
	private $_root='';
	private $_rootname='';
	private $_domain='';
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
	 *移动附件	 *
	 */
		$filename=substr($path,strrpos($path,'/')+1);;
		$fpath=substr($path,0,strrpos($path,'/')).'/';
	 	if($re=$this->makeDir($fpath)){ //创建目录
			if($re['error']) return $re;
		}
		$obz=io_remote::getBzByRemoteid($attach['remote']);
		if($obz=='dzz'){
			$opath='attach::'.$attach['aid'];
		}else{
			$opath=$obz.'/'.$attach['attachment'];
		}
		if($re=$this->multiUpload($opath,$fpath,$filename,$attach,'overwrite')){
			if($re['error']) return $re;
			else{
				return true;
			}
		}
		return false;
	}
	public  function makeDir($path){
		$arr=$this->parsePath($path);
		$patharr=explode('/',trim($arr['object'],'/'));
		$folderarr=array();
		$p=$arr['bz'].$arr['bucket'];
		foreach($patharr as $value){
			$p.='/'.$value;
			$re=$this->_makeDir($p);
			if(isset($re['error'])){
				return $re;
			}else{
				continue;
			}
			
		}
		return true;
	}
	protected function _makeDir($path){
		global $_G;
		$arr=self::parsePath($path);
		try {
			$client=self::init($path,1);
			$putPolicy = new Qiniu_RS_PutPolicy($arr['bucket']);
			$upToken = $putPolicy->Token(null);
			list($ret, $err) = Qiniu_Put($upToken, $arr['object'].'/', '', null);
			if ($err !== null) {
				return array('error'=>$err->Code.':'.$err->Err);
			}
			return true;
		}catch(Exception $e){
			//var_dump($e);
			return array('error'=>$e->getMessage());
		}
		
	}

	/*
	*初始化OSS 返回oss 操作符
	*/
	public function init($bz,$isguest=0){
		global $_G,$HOSTS,$QINIU_UP_HOST;
		$bzarr=explode(':',$bz);
		$id=trim($bzarr[1]);
		if(!$root=DB::fetch_first("select * from ".DB::table(self::T)." where  id='{$id}'")){
			return array('error'=>'need authorize to '.$bzarr[0]);
		}
		if(!$isguest && $root['uid']>0 && $root['uid']!=$_G['uid']) return array('error'=>'need authorize to qiniu');
		$access_id=authcode($root['access_id'],'DECODE',$root['bz']);
		if(empty($access_id)) $access_id=$root['access_id'];
		$access_key=authcode($root['access_key'],'DECODE',$root['bz']);
		list($hostname,$region)=explode('|',$root['hostname']);
		$this->_domain=$hostname;
		if($root['cloudname']){
			$this->_rootname=$root['cloudname'];
		}else{
			$this->_rootname.=':'.($root['bucket']?$root['bucket']:cutstr($root['access_id'], 4, $dot = ''));
		}
		$this->bucket=$root['bucket'];
		if(!isset($HOSTS[$region]))  $region='huadong';
		Qiniu_SetKeys($access_id, $access_key);
		Qiniu_UploadHost($HOSTS[$region]['up_http']);
		return new Qiniu_MacHttpClient(null);
	}
	public function authorize($refer){
		global $_G,$_GET,$clouds,$HOSTS;
		if(empty($_G['uid'])) {
			dsetcookie('_refer', rawurlencode(BASESCRIPT.'?mod=connect&op=oauth&bz=qiniu'));
			showmessage('to_login', '', array(), array('showmsg' => true, 'login' => 1));
		}
		if(submitcheck('qiniusubmit')){
			$access_id=$_GET['access_id'];
			$access_key=$_GET['access_key'];
			$hostname=$_GET['hostname'];
			$bucket=$_GET['bucket'];
			$region=$_GET['region'];
			if(!$access_id || !$access_key) {
				showmessage('please input qiniu AK and SK',dreferer());
			}
			if(!$bucket || !$hostname) showmessage(lang('set_bucket_name_region'),dreferer());
			Qiniu_setKeys($access_id,$access_key);
			if(!isset($HOSTS[$region]))  $region='huadong';
			Qiniu_UploadHost($HOSTS[$region]['up_http']);
			//print_r($_GET);exit('dddd');
			try{
				$putPolicy = new Qiniu_RS_PutPolicy($bucket);
				$upToken = $putPolicy->Token(null);
				$putExtra = new Qiniu_PutExtra();
				$putExtra->Crc32 = 1;
				$file = DZZ_ROOT.'./dzz/images/b.gif';
				list($ret, $err) = Qiniu_PutFile($upToken, 'b.gif', $file, $putExtra);
				if($err){
					showmessage($err->Code.':'.$err->Err,dreferer());
				}
				//测试读取图片
				$baseUrl = Qiniu_RS_MakeBaseUrl($hostname,'b.gif');
				$getPolicy = new Qiniu_RS_GetPolicy();
				$privateUrl = $getPolicy->MakeRequest($baseUrl, null);
				if(!getimagesize($privateUrl)){
					showmessage('test_read_failure',dreferer());
				}
			}catch(Exception $e){
				showmessage($e->getMessage(),dreferer());
			}
			//删除测试图片
			$client = new Qiniu_MacHttpClient(null);
			Qiniu_RS_Delete($client, $bucket, 'b.gif');
			
			$type='qiniu';
			$uid=defined('IN_ADMIN')?0:$_G['uid'];
			$setarr=array(	'uid'=>$uid,
							'access_id'=>$access_id,
							'access_key'=>authcode($access_key,'ENCODE',$type),
							'bz'=>$type,
							'bucket'=>$bucket,
							'hostname'=>$hostname.'|'.$region,
							'dateline'=>TIMESTAMP,					
						);
			if($id=DB::result_first("select id from ".DB::table(self::T)." where uid='{$uid}' and access_id='{$access_id}' and bucket='{$bucket}'")){
				DB::update(self::T,$setarr,"id ='{$id}'");
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
				showmessage('do_success',$refer?$refer:BASESCRIPT.'?mod=connect');
			}
		}else{
			include template('oauth_qiniu');
		}
	}
	public function getBzByPath($path){
		$bzarr=explode(':',$path);
		return $bzarr[0].':'.$bzarr[1].':';
	}
	public function getFileUri($path,$fop=''){
		$arr=self::parsePath($path);
		$client=self::init($path,1);
		try{
			$baseUrl = Qiniu_RS_MakeBaseUrl($this->_domain, $arr['object']).($fop?'?'.$fop:'');
			$getPolicy = new Qiniu_RS_GetPolicy();
			return $getPolicy->MakeRequest($baseUrl, null);
		}catch(Exception $e){
			return $e->getMessage();
		}	
	}
	public function deleteThumb($path){
		global $_G;
		$imgcachePath='imgcache/';
		$cachepath=str_replace(urlencode('/'),'/',urlencode(str_replace(':','/',$path)));
		foreach($_G['setting']['thumbsize'] as $value){
			$target = $imgcachePath . ($cachepath) . '.' . $value['width'] . '_' . $value['height'] . '_1.jpeg';
			$target1 = $imgcachePath . ($cachepath) . '.' . $value['width'] . '_' . $value['height'] . '_2.jpeg';
			@unlink($_G['setting']['attachdir'].$target);
			@unlink($_G['setting']['attachdir'].$target1);
		}
	}
	public function createThumb($path,$size,$width=0,$height=0,$thumbtype  = 1){
		if(intval($width)<1) $width=$_G['setting']['thumbsize'][$size]['width'];
		if(intval($height)<1) $height=$_G['setting']['thumbsize'][$size]['height'];
		return;
	}
	//获取缩略图
	public function getThumb($path,$width,$height,$original,$returnurl=false,$thumbtype = 1){
		global $_G;
		$imgcachePath='imgcache/';
		$cachepath=str_replace(urlencode('/'),'/',urlencode(str_replace('//','/',str_replace(':','/',$path))));
		$target = $imgcachePath . ($cachepath) . '.' . $width . '_' . $height. '_'.$thumbtype.'.jpeg';
		if(!$original && @getimagesize($_G['setting']['attachdir'].'./'.$target)){
			if($returnurl) return $_G['setting']['attachurl'].'/'.$target;
			IO::output_thumb($_G['setting']['attachdir'].'./'.$target);
		}
		//调用挂载点程序生成缩略图绝对和相对地址；
        $fileurls=array();
		Hook::listen('thumbnail',$fileurls,$path);
		if($fileurls){
			//生成图片缩略图
			$imgurl = $fileurls['filedir'];
			$target_attach = $_G['setting']['attachdir'] .'./'. $target;
			$targetpath = dirname($target_attach);
			dmkdir($targetpath);
			require_once libfile('class/image');
			$image = new image();
			if($thumb = $image->Thumb($imgurl, $target, $width, $height,$thumbtype)){
				if($returnurl) return $_G['setting']['attachurl'].'/'.$target;
				IO::output_thumb($_G['setting']['attachdir'].'./'.$target);
			}else{
				if($returnurl) return $imgurl;
				IO::output_thumb($imgurl);
			}
		}else{
			 $fileurls=array('fileurl'=>self::getFileUri($path),'filedir'=>self::getStream($path));
		}
		
		//非图片类文件的时候，直接获取文件后缀对应的图片
		if(!$imginfo = @getimagesize($fileurls['filedir'])){
		   $imgurl= geticonfromext($data['ext'],$data['type']);
		   if ($returnurl) return $imgurl;
           IO::output_thumb($imgurl);
	    }
		//返回原图的时候或图片小于缩略图宽高的不生成直接返回原图
		if ($original || ($imginfo[0] < $width && $imginfo[1] < $height)) {
            if ($returnurl) return $fileurls['fileurl'];
          	IO::output_thumb($fileurls['filedir']);
        }
		
		$client=self::init($path,1);
		$arr=self::parsePath($path);
		$targetpath=dirname($_G['setting']['attachdir'].'./'.$target);
		dmkdir($targetpath);
		//获取缩略图保存到本地
		$getPolicy = new Qiniu_RS_GetPolicy();
		$imgView = new Qiniu_ImageView;
		$imgView->Mode = 2;
		$imgView->Width = $width;
		$imgView->Height = $height;
		$baseUrl = Qiniu_RS_MakeBaseUrl($this->_domain, $arr['object']);
		//生成fopUrl
		$imgViewUrl = $imgView->MakeRequest($baseUrl);
		//对fopUrl 进行签名，生成privateUrl。 公有bucket 此步可以省去。
		$imgViewPrivateUrl = $getPolicy->MakeRequest($imgViewUrl, null);
		if($returnurl) return $imgViewPrivateUrl;
		@file_put_contents($_G['setting']['attachdir'].'./'.$target,fopen($imgViewPrivateUrl,'rb'));
		if($imginfo=@getimagesize($_G['setting']['attachdir'].'./'.$target)){
			if($returnurl) return $_G['setting']['attachurl'].'/'.$target;
			$file=$_G['setting']['attachdir'].'./'.$target;
			IO::output_thumb($file);
		}else{
			@header("Location:$imgViewPrivateUrl");
			exit();
			//本地中转时
			IO::output_thumb($imgViewPrivateUrl);
			
		}
	}
	//获取文件流；
	//$path: 路径
	function getStream($path,$fop){ 
		$arr=self::parsePath($path);
		$client=self::init($path,1);
		try{
			$baseUrl = Qiniu_RS_MakeBaseUrl($this->_domain, $arr['object']).($fop?'?'.$fop:'');
			$getPolicy = new Qiniu_RS_GetPolicy();
			return ($getPolicy->MakeRequest($baseUrl, null));
		}catch(Exception $e){
			return $e->getMessage();
		}
	}
	public function parsePath($path){
		$arr=explode(':',$path);
		$bz=$arr[0].':'.$arr[1].':';
		$arr1=explode('/',$arr[2]);
		 $bucket=$arr1[0];
		 unset($arr1[0]);
		$object=implode('/',$arr1);
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
		$icoarr=self::upload_by_content($data,$path1,$filename,'overwrite');
		if($icoarr['type']=='image'){
			  self::deleteThumb($path);
			  $icoarr['img'].='&t='.TIMESTAMP;
		 }
		return $icoarr;
	}
	public function rename($path,$name){//重命名
	
		$arr=self::parsePath($path);
		//判断是否为目录
		$patharr=explode('/',$arr['object']);
		$arr['object1']='';
		if(strrpos($path,'/')==(strlen($path)-1)){//是目录
			return array('error'=>lang('folder_not_allowed_rename'));
		}else{
			$ext=strtolower(substr(strrchr($arr['object'], '.'), 1));
			foreach($patharr as $key =>$value){
				if($key>=count($patharr)-1) break;
				$arr['object1'].=$value.'/';
			}
			$arr['object1'].=$ext?(preg_replace("/\.\w+$/i",'.'.$ext,$name)):$name;
		}
		$client=self::init($path);
		$err = Qiniu_RS_Move($client, $arr['bucket'], $arr['object'], $arr['bucket'], $arr['object1']);
		if ($err !== null) {
			return array('error'=>$err->Code.':'.$err->Err);
		}
		return self::getMeta($arr['bz'].$arr['bucket'].'/'.$arr['object1']);
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
	function upload_by_content($fileContent,$path,$filename,$ondup='overwrite'){
		global $QINIU_UP_HOST;
		$path.=$filename;
		$arr=self::parsePath($path);
		$client=self::init($path);
		
		$putPolicy = new Qiniu_RS_PutPolicy($arr['bucket'].':'.$arr['object']);
		if($ondup=='overwrite') $putPolicy->InsertOnly=0;
		else $putPolicy->InsertOnly=1;
		$upToken = $putPolicy->Token(null);
		$putExtra = new Qiniu_PutExtra();
		$putExtra->Crc32 = 1;
		list($ret, $err) = Qiniu_Put($upToken, $arr['object'], $fileContent, $putExtra);
		if ($err !== null) {
			return array('error'=>$err->Code.':'.$err->Err);
		} else {
			$meta=$ret;
		}
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
	function listFiles($path,$by='time',$marker='',$limit=100,$force=0){ 
		global $_G,$_GET,$documentexts,$imageexts;
			$arr=self::parsePath($path);
			
			$icosdata=array();
			$client=self::init($path,1);
			$data=array();
			list($commonPrefixes,$iterms, $markerOut, $err)=Qiniu_RSF_ListPrefix($client, $arr['bucket'],$arr['object'],$marker,'/',$limit);
			
			if ($err != null) {
				
				if ($err === Qiniu_RSF_EOF) {
					$data['items']=$iterms;
					$data['CommonPrefixes']=$commonPrefixes;
					
				} else {
					//runlog('qiniu_log',$err);
					return array('error'=>$err->Code.':'.$err->Err);
				}
			} else {
				$data['items']=$iterms;
				$data['CommonPrefixes']=$commonPrefixes;
			}
				
			if($data['items']) $icos=$data['items'];
			if($data['CommonPrefixes']) $folders=$data['CommonPrefixes'];
			$value=array();
			foreach($icos as $key => $value){
				if(is_array($value)){
					$icoarr=self::_formatMeta($value,$arr);
					$icosdata[$icoarr['icoid']]=$icoarr;
				}else{
					$icoarr=self::_formatMeta($icos,$arr);
					$icosdata[$icoarr['icoid']]=$icoarr;
					break;
				}
			}
			$value=array();
			foreach($folders as $key => $value){
				$value1=array('isdir'=>true,'key'=>$value);
				$icoarr=self::_formatMeta($value1,$arr);
				$icosdata[$icoarr['icoid']]=$icoarr;
			}
			
			$value=array();
			$value['isdir']=true;
			$value['key']=$arr['object']?$arr['object']:'';
			$value['nextMarker']=$markerOut;
			$value['IsTruncated']=$markerOut?1:0;
			
			$icoarr=self::_formatMeta($value,$arr);
			if($icosdata[$icoarr['icoid']]){
				$icosdata[$icoarr['icoid']]['nextMarker'] =$icoarr['nextMarker'];
				$icosdata[$icoarr['icoid']]['IsTruncated'] =$icoarr['IsTruncated'];
			}else{
				$icosdata[$icoarr['icoid']]=$icoarr;
			}
		return $icosdata;	
	}
	
	function listFilesAll(&$client,$path,$limit='100',$marker='',$icosdata=array()){ 
		//static $icosdata=array();
			$arr=self::parsePath($path);
			$data=array();
			list($commonPrefixes,$iterms, $markerOut, $err)= Qiniu_RSF_ListPrefix($client, $arr['bucket'],$arr['object'],$marker,'/',$limit);
			if ($err != null) {
				if ($err === Qiniu_RSF_EOF) {
					$data['items']=$iterms;
					$data['CommonPrefixes']=$commonPrefixes;
					
				} else {
					runlog('qiniu_log',$err);
					return array('error'=>$err->Code.':'.$err->Err);
				}
			} else {
				$data['items']=$iterms;
				$data['CommonPrefixes']=$commonPrefixes;
			}
			
				
			if($data['items']) $icos=$data['items'];
			if($data['CommonPrefixes']) $folders=$data['CommonPrefixes'];
			$value=array();
		
			foreach($icos as $key => $value){
				if(is_array($value)){
					$icoarr=self::_formatMeta($value,$arr);
					$icosdata[$icoarr['icoid']]=$icoarr;
				}else{
					$icoarr=self::_formatMeta($icos,$arr);
					$icosdata[$icoarr['icoid']]=$icoarr;
					break;
				}
			}
			$value=array();
			foreach($folders as $key => $value){
				$value1=array('isdir'=>true,'key'=>$value);
				$icoarr=self::_formatMeta($value1,$arr);
				$icosdata[$icoarr['icoid']]=$icoarr;
			}
			$value=array();
			$value['isdir']=true;
			$value['key']=$arr['object']?$arr['object']:'';
			$value['nextMarker']=$markerOut;
			$value['IsTruncated']=$markerOut?1:0;
			
			$icoarr=self::_formatMeta($value,$arr);
			if($icosdata[$icoarr['icoid']]){
				$icosdata[$icoarr['icoid']]['nextMarker'] =$icoarr['nextMarker'];
				$icosdata[$icoarr['icoid']]['IsTruncated'] =$icoarr['IsTruncated'];
			}else{
				$icosdata[$icoarr['icoid']]=$icoarr;
			}
				
		//exit($data['ListBucketResult']['IsTruncated']);		
		if($markerOut){
			$icosdata=self::listFilesAll($client,$path,1000,$markerOut,$icosdata);
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
		if(empty($arr['object'])){
			$meta['key']='';
			$meta['isdir']=true;
			$meta['putTime']=0;
		}else{
			$client=self::init($path,1);
			$icosdata=array();
			list($meta, $err) = Qiniu_RS_Stat($client, $arr['bucket'], $arr['object']);
			if ($err !== null) {
				runlog('qiniu_log',$err->Code.':'.$err->Err);
				return array('error'=>$err->Code.':'.$err->Err);
			}
			$meta['key']=substr($arr['object'],strrchr($meta['key'], '/'));
		}
		
		$icosdata=self::_formatMeta($meta,$arr);
		return $icosdata;
	}
	//将api获取的meta数据转化为icodata
	function _formatMeta($meta,$arr){ 
		global $_G,$documentexts,$imageexts;
		$icosdata=array();
		if(strrpos($meta['key'],'/')==(strlen($meta['key'])-1)) $meta['isdir']=true;
		if($meta['isdir']){
			if(!$meta['key']){
				if($this->bucket){
					$name=$this->bucket;
					$pfid=0;
					$pf='';
					$flag=self::BZ;
				}elseif($arr['bucket']){
					$name=$arr['bucket'];
					$pfid=md5($arr['bz']);
					$pf='';
					$flag=self::BZ;
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
				  'dateline'=>ceil($meta['putTime']/10000000),
				  'flag'=>$flag,
				  'nextMarker'=>$meta['nextMarker'],
				  'IsTruncated'=>$meta['IsTruncated'],
				 );
				
				$icoarr['fsize']=formatsize($icoarr['size']);
				$icoarr['ftype']=getFileTypeName($icoarr['type'],$icoarr['ext']);
				$icoarr['fdateline']=dgmdate($icoarr['dateline']);
				$icosdata=$icoarr;
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
				$img=$_G['siteurl'].DZZSCRIPT.'?mod=io&op=thumbnail&size=small&path='.dzzencode($arr['bz'].$arr['bucket'].$meta['key']);
				$url=$_G['siteurl'].DZZSCRIPT.'?mod=io&op=thumbnail&size=large&path='.dzzencode($arr['bz'].$arr['bucket'].$meta['key']);
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
						  'size'=>$meta['fsize'],
						  'dateline'=>ceil($meta['putTime']/10000000),
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
		/*if(!$this->bucket && $bzarr['bucket']){
			$spath=$bzarr['bucket'].'/'.$spath;
			$bzarr['bucket']='';
		}else{
			$bzarr['bucket'].='/';
		}*/
		$spath=trim($spath,'/');
		$patharr=explode('/',$spath);
		$folderarr=array();
		$path1=$bzarr['bz'].$bzarr['bucket'].'/';
		if($arr=self::getMeta($path1)){
			if(!isset($arr['error'])) {
				$folder=self::getFolderByIcosdata($arr);
				$folderarr[$folder['fid']]=$folder;
			}
		}
		for($i=0;$i<count($patharr);$i++){
			if(empty($patharr[$i])) continue;
			$path1=$bzarr['bz'].$bzarr['bucket'].'/';
			for($j=0;$j<=$i;$j++){
				$path1.=$patharr[$j].'/';
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
			$fsperm=perm_FolderSPerm::flagPower('qiniu_root');
		}else{
			$fsperm=perm_FolderSPerm::flagPower('qiniu');
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
						  'icon'=>$icosdata['flag']?('dzz/images/default/system/'.$icosdata['flag'].'.png'):'',
						  'nextMarker'=>$icosdata['nextMarker'],
				  		  'IsTruncated'=>$icosdata['IsTruncated'],
						);
		
		}
		return $folder;
	}
	//获得文件内容；
	function getFileContent($path){
		$arr=self::parsePath($path);
		$url=self::getFileUri($path);
		return file_get_contents($url);
	}
	//打包下载文件
	public function zipdownload($paths,$filename){
		global $_G;
		$paths=(array)$paths;
		set_time_limit(0);
		
		if(empty($filename)){
			$meta=self::getMeta($paths[0]);
			$filename=$meta['name'].(count($paths)>1?'等':'');
		}
		$filename=(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($filename) : $filename);
		include_once libfile('class/ZipStream');
		$zip = new ZipStream($filename.".zip");
		$data=self::getFolderInfo($path,'',$zip);
		$zip->finalize();
	}
	public function getFolderInfo($paths,$position='',&$zip){
		static $data=array();
		try{
			foreach($paths as $path){
				$arr=IO::parsePath($path);
				$client=self::init($path,1); 
				$meta=self::getMeta($path);
				switch($meta['type']){
					case 'folder':
						 $lposition=$position.$meta['name'].'/';
						 $contents=self::listFilesAll($client,$path);
						 $arr=array();
						 foreach($contents as $key=>$value){
							 if($value['path']!=$path){
								$arr[]=$value['path'];
							 }
						 }
						 if($arr) self::getFolderInfo($arr,$lposition,$zip);
						break;
					default:
					 $meta['url']=self::getStream($meta['path']);
					 $meta['position']=$position.$meta['name'];
					 //$data[$meta['icoid']]=$meta;
					 $zip->addLargeFile(@fopen($meta['url'],'rb'), $meta['position'], $meta['dateline']);
				}
			}
		
		}catch(Exception $e){
			//var_dump($e);
			$data['error']=$e->getMessage();
			return $data;
		}
		return $data;
	}
	//下载文件
	public function download($paths,$filename){
		global $_G;
		$paths=(array)$paths;
		if(count($paths)>1){
			self::zipdownload($paths,$filename);
			exit();
		}else{
			$path=$paths[0];
		}
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
			if(!$fp = @fopen($url, 'rb')) {
				topshowmessage(lang('file_not_exist1'));
			}
		
			$chunk = 10 * 1024 * 1024; 
			//$file['data'] = self::getFileContent($path);
			//if($file['data']['error']) topshowmessage($file['data']['error']);
			$file['name'] = '"'.(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($file['name']) : $file['name']).'"';
			$d=new FileDownload();
			$d->download($url,$file['name'],$file['size'],$file['dateline'],true);
			exit();
			dheader('Date: '.gmdate('D, d M Y H:i:s', $file['dateline']).' GMT');
			dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', $file['dateline']).' GMT');
			dheader('Content-Encoding: none');
			dheader('Content-Disposition: attachment; filename='.$file['name']);
			dheader('Content-Type: application/octet-stream');
			dheader('Content-Length: '.$file['size']);
			
			@ob_end_clean();
			if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
			while (!feof($fp)) { 
				echo fread($fp, $chunk);
				@ob_flush();  // flush output
				@flush();
			}
			@fclose($fp);
			exit();
			
		} catch (Exception $e) {
			// The file wasn't found at the specified path/revision
			//echo 'The file was not found at the specified path/revision';
			topshowmessage($e->getMessage());
		}
	}
	
	
	
	
	//获取目录的所有下级和它自己的object
	public function deleteFolder(&$client,$path,$limit='100',$marker=''){
		static $objects=array();
		$arr=self::parsePath($path);
		//echo( $path.'---------');
		list($commonPrefixes,$iterms, $markerOut, $err)= Qiniu_RSF_ListPrefix($client, $arr['bucket'],$arr['object'],$marker,'',$limit);
		if ($err != null) {
			if ($err === Qiniu_RSF_EOF) {
				$data['items']=$iterms;
			} else {
				runlog('qiniu_log',$err->Code.':'.$err->Err);
				return array('error'=>$err->Code.':'.$err->Err);
			}
		} else {
			$data['items']=$iterms;
		}
		if($data['items']) $icos=$data['items'];
		$value=array();
		$entries = array();
		foreach($icos as $key => $value){
			if(is_array($value)){
				$entries[]=new Qiniu_RS_EntryPath($arr['bucket'], $value['key']);
				//Qiniu_RS_Delete($client, $arr['bucket'],$value['key']);
			}else{
				$entries[]=new Qiniu_RS_EntryPath($arr['bucket'], $value);
				//Qiniu_RS_Delete($client, $arr['bucket'],$value);
			}
		}
		Qiniu_RS_BatchDelete($client, $entries);
		
		if($markerOut){
			self::deleteFolder($client,$path,100,$markerOut);
		}
	
		return true;
	}
	
	//删除原内容
	//$path: 删除的路径
	//$bz: 删除的api;
	//$data：可以删除的id数组（当剪切的时候，为了保证数据不丢失，目标位置添加成功后将此id添加到data数组，
	//删除时如果$data有数据，将会只删除id在$data中的数据；
	//如果删除的是目录或下级有目录，需要判断此目录内是否所有元素都在删除的id中，如果有未删除的元素，则此目录保留不会删除；
	//
	public function Delete($path,$force=false){
		//global $dropbox;
		$arr=self::parsePath($path);
		try{
			$client=self::init($path,$force);
			//判断删除的对象是否为文件夹
			if(strrpos($arr['object'],'/')==(strlen($arr['object'])-1)){ //是文件夹
				self::deleteFolder($client,$path);
			}else{
				Qiniu_RS_Delete($client, $arr['bucket'],$arr['object']);
			}
			return array('icoid'=>md5(($path)),
						 'name'=>substr(strrchr($path, '/'), 1),
						);
		}catch(Exception $e){
			return array('icoid'=>md5($path),'error'=>$e->getMessage());
		}
	}
	public function createFolderByPath($path, $pfid = '',$noperm = false)
	{
		$data = array();
		if(self::makeDir($path)){
			$data = self::getMeta($path);
		}
		return $data;
	}
	//添加目录
	//$fname：目录路径;
	//$container：目标容器
	//$bz：api;
	public function CreateFolder($path,$fname){
		global $_G;
		$arr=self::parsePath($path);
		
			$client=self::init($path,true);
			$putPolicy = new Qiniu_RS_PutPolicy($arr['bucket']);
			$upToken = $putPolicy->Token(null);
			list($ret, $err) = Qiniu_Put($upToken, $arr['object'].$fname.'/', '', null);
			
			if ($err !== null) {
				return array('error'=>$err->Code.':'.$err->Err);
			} else {
				$ret['isdir']=true;
			}
			$icoarr=self::_formatMeta($ret,$arr);
			
			$folderarr=self::getFolderByIcosdata($icoarr);
			return array('folderarr'=>$folderarr,'icoarr'=>$icoarr);
		
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
		$cachekey='qiniu_uploadID_'.md5($path);
		$cache=C::t('cache')->fetch($cachekey);
		return unserialize($cache['cachevalue']);
	}
	private function saveCache($path,$data){
		global $_G;
		$cachekey='qiniu_uploadID_'.md5($path);
		C::t('cache')->insert(array(
							'cachekey' => $cachekey,
							'cachevalue' => serialize($data),
							'dateline' => $_G['timestamp'],
						), false, true);
	}
	private function deleteCache($path){
		$cachekey='qiniu_uploadID_'.md5($path);
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
	public function uploadStream($file,$filename,$path,$relativePath,$content_range){
		global $_G;
		$data=array();
		$arr=self::getPartInfo($content_range);
		if($relativePath && ($arr['iscomplete'])){
			$path1=$path;
			$patharr=explode('/',$relativePath);
			foreach($patharr as $key=> $value){
				if(!$value){
					continue;
				}
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
		}
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
		/*if($arr['ispart']){
			if($arr['partnum']==1){
				file_put_contents(
                        $cachefile,
                        fopen($file, 'rb')
                    );
			}else{
				file_put_contents(
                        $cachefile,
                        fopen($file, 'rb'),
                        FILE_APPEND
                    );
			}
			@unlink($file);
			if($arr['iscomplete']){
				$re1=self::upload($cachefile,$path,$filename,$arr);
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
			
		}else{
			$re1=self::upload($file,$path,$filename);
			if(empty($re1['error'])){
				$data['icoarr'][] = $re1;
				return $data;
			}else{
				$data['error'] = $re1['error'];
				return $data;
			}
		}*/
	}
	
	function upload($file,$path,$filename,$partinfo=array(),$ondup='overwrite'){
		global $_G;
		$path.=$filename;
		$partsize=4*1024*1024;//默认分块大小4M;
		$arr=self::parsePath($path);
		$client=self::init($path);
		$putExtra = new Qiniu_Rio_PutExtra($arr['bucket']);
		$putPolicy = new Qiniu_RS_PutPolicy($arr['bucket']);
		$upToken = $putPolicy->Token(null);
		$cachefile=$_G['setting']['attachdir'].'./cache/'.md5($path);
		if($partinfo['partnum']){
				if($arr['partnum']==1){
					file_put_contents(
							$cachefile,
							fopen($file, 'rb')
						);
				}else{
					file_put_contents(
							$cachefile,
							fopen($file, 'rb'),
							FILE_APPEND
						);
				}
				$filesize=filesize($cachefile);
				
				if(!$data=self::getCache($path)){
					$data=array();
				}
				$partnum=count($data);
				if(!$handle=fopen($cachefile,'rb')){
					return array('error'=>lang('upload_error_not_file'));
				}
				$tried=0;$tryTimes=3;
				for($i=$partnum;$i<floor($filesize/$partsize);$i++){
					fseek($handle,$i*$partsize);
					$fileContent=fread($handle,$partsize);
					
					while ($tried < $tryTimes) {
						list($blkputRet, $err) = dzz_Qiniu_Rio_Mkblock($upToken, $fileContent,strlen($fileContent));
						if ($err === null) {
							break;
						}
						$tried += 1;
						continue;
					}
					if ($err !== null) return array('error'=>$err->Code.':'.$err->Err);
					$data[]=$blkputRet;
				}
				
				
				if($partinfo['iscomplete']){
					$partnum=count($data);
					$tried=0;$tryTimes=3;
					for($i=$partnum;$i<ceil($filesize/$partsize);$i++){
						fseek($handle,$i*$partsize);
						$fileContent=fread($handle,$partsize);
						while ($tried < $tryTimes) {
							list($blkputRet, $err) = dzz_Qiniu_Rio_Mkblock($upToken, $fileContent,strlen($fileContent));
							if ($err === null) {
								break;
							}
							$tried += 1;
							continue;
						}
						if ($err !== null) return array('error'=>$err->Code.':'.$err->Err);
						$data[]=$blkputRet;
					}
					fclose($handle);
					self::deleteCache($path);
					$putExtra->Progresses=$data;
					list($ret,$err)=dzz_Qiniu_Rio_Mkfile($upToken, $arr['object'], $filesize, $putExtra);
					if ($err !== null) {
						return array('error'=>$err->Code.':'.$err->Err);
					} 
					$ret['putTime']=TIMESTAMP*10000000;
					@unlink($cachefile);
					return self::_formatMeta($ret,$arr);
				}else{
					self::saveCache($path,$data);
					fclose($handle);
					return true;
				}
		}else{
			//$response = $oss->upload_file_by_file($arr['bucket'],$arr['object'],$file);
			$putPolicy = new Qiniu_RS_PutPolicy($arr['bucket']);
			if($ondup=='overwrite') $putPolicy->InsertOnly=0;
			else $putPolicy->InsertOnly=1;
			$upToken = $putPolicy->Token(null);
			$putExtra = new Qiniu_PutExtra();
			$putExtra->Crc32 = 1;
			list($ret, $err) = Qiniu_PutFile($upToken, $arr['object'], $file, $putExtra);
			
			if ($err !== null) {
				return array('error'=>$err->Code.':'.$err->Err);
			} 
			$ret['putTime']=TIMESTAMP*10000000;
			$icoarr=self::_formatMeta($ret,$arr);
			
			return $icoarr;
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
		$client=self::init($opath);
		$data=self::getMeta($opath);
		$arr=self::parsePath($path);
		switch($data['type']){
			case 'folder'://创建目录
				//exit($arr['path'].'===='.$data['name']);
				if($re=IO::CreateFolder($path,$data['name'])){
					if(isset($re['error']) && intval($re['error_code'])!=31061){
							$data['success']=$arr['error'];
					}else{
						
						$data['newdata']=$re['icoarr'];
						$data['success']=true;
						//echo $opath.'<br>';
						 $contents=self::listFilesAll($client,$opath);
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
			   
				if($arr['bz']==$oarr['bz']){//同一个api时
					$arr=self::parsePath($path.$data['name']);
					$err = Qiniu_RS_Move($client, $arr['bucket'], $arr['object'], $oarr['bucket'], $oarr['object']);
					if ($err !== null) {
						$data['success']=$err->Code.':'.$err->Err;
					}else{
						$data['newdata']=self::getMeta($path.$data['name']);
						$data['success']=true;
					}
				}else{
					
					if($re=IO::multiUpload($opath,$path,$data['name'])){
						if($re['error']) $data['success']=$re['error'];
						else{
							$data['newdata']=$re;
							$data['success']=true;
						}
					}
				}
				break;
		}
		
		return $data;
	}
	public function multiUpload($opath,$path,$filename,$attach=array(),$ondup="newcopy"){
		global $_G;
		@set_time_limit(0);
		/* 
	 * 分块上传文件
	 * param $file:文件路径（可以是url路径，需要服务器开启allow_url_fopen);
	*/
		$partsize=1024*1024*4; //分块大小2M
		if($attach){
			$data=$attach;
			$data['size']=$attach['filesize'];
		}else{
			$data=IO::getMeta($opath);
			if($data['error']) return $data;
		}
		$size=$data['size'];
		if(is_array($filepath=IO::getStream($opath))){
			return array('error'=>$filepath['error']);
		}
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
			return self::upload_by_content($fileContent,$path,$filename);
		}else{ //分片上传		
			if(!$handle=fopen($filepath, 'rb')){
				return array('error'=>lang('open_file_error'));
			}
			$partinfo=array('ispart'=>true);
			$cachefile=$_G['setting']['attachdir'].'./cache/'.md5($opath);
			while (!feof($handle)) {
			  file_put_contents($cachefile,fread($handle, 8192),FILE_APPEND);
			}
			$re=self::upload($cachefile,$path,$filename);
			@unlink($cachefile);
			return $re;
		}
	}
}
?>
