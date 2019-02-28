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
require_once(DZZ_ROOT.'./core/api/OneDrive/autoload.php');
@set_time_limit(0);
@ini_set('max_execution_time',0);

class io_OneDrive extends io_api
{
	const T ='connect_onedrive';
	const BZ='OneDrive';
	private $icosdatas=array();
	private $_root = '';
	private $_rootname = '';
	private $perm = 0;
	public function __construct() {
		$cloud = C::t('connect')->fetch(self::BZ);
		$this->_rootname=$cloud['name'];
		$this->perm=perm_binPerm::getMyPower();
		//self::init($path);
		//print_r($arr);
		
	}
	
	public function MoveToSpace($path,$attach,$ondup='replace'){
		global $_G;
	/*
	 *移动附件到百度网盘
	 *
	 */
		$filename=substr($path,strrpos($path,'/')+1);;
		$fpath=substr($path,0,strrpos($path,'/'));
	 	if(($re=$this->makeDir($fpath)) && $re['error']){ //创建目录
			return $re;
		}
		
		$obz=io_remote::getBzByRemoteid($attach['remote']);
		if($obz=='dzz'){
			$opath='attach::'.$attach['aid'];
		}else{
			$opath=$obz.'/'.$attach['attachment'];
		}
		
		if($re=$this->multiUpload($opath,$fpath,$filename,$attach,$ondup)){
			if($re['error']) return $re;
			else{
				return true;
			}
		}
		return false;
	}
	public function createFolderByPath($path, $pfid = '',$noperm = false)
	{
		$data = array();
		if(self::makeDir($path)){
			$data = self::getMeta($path);
		}
		return $data;
	}
	protected  function makeDir($path){
		$bzarr=$this->parsePath($path);
		
		$patharr=explode('/',trim(preg_replace("/^".str_replace('/','\/',$this->_root)."/",'',urldecode($bzarr['path'])),'/'));
		$folderarr=array();
		$p=$bzarr['bz'].$this->_root;
		
		foreach($patharr as $value){
			$p.='/'.$value;
			if(($re=$this->_makeDir($p,$value)) && isset($re['error']) && $re['error']['code']!='nameAlreadyExists'){
				return $re;
			}else{
				continue;
			}
		}
		return true;
	}
	protected function _makeDir($path){
		global $_G;
		$bzarr=self::parsePath($path);
		$patharr=explode('/',$bzarr['path']);
		try {
			$onedrive=self::init($path);
			if(is_array($onedrive) && $onedrive['error']) return $onedrive;
			
			$ret=$onedrive->createFolder($patharr[0],$patharr[1]);
			if($ret['error'] && ($ret['code']=='nameAlreadyExists')){
				return true;
			}elseif($ret['error']){
				return $ret;
			}
			return true;
		}catch(Exception $e){
			//var_dump($e);
			return array('error'=>$e->getMessage());
		}
		
	}
	
	public function init($path,$isguest=0){
		global $_G;
		$bzarr=explode(':',$path);
		$id=trim($bzarr[1]);
		$cloud=C::t('connect')->fetch(self::BZ);
		if($token=C::t(self::T)->fetch($id)){
			if(!$isguest && $token['uid']>0 && $token['uid']!=$_G['uid']) return array('error'=>'need authorize to '.self::BZ);
			$access_token = $token['access_token'];
			if($token['cloudname']){
				$this->_rootname=$token['cloudname'];
			}else{
				$this->_rootname.=':'.$token['cusername'];
			}
		}else{
			return array('error'=>'need authorize to '.self::BZ);
		}
		$onedrive=new Client(array('client_id' => $cloud['key'],'state'=>array('redirect_uri' => $_G['siteurl'].'oauth.php','access_token' => $access_token,'expires_in'=>$token['expires_in'],'refreshtime'=>$token['refreshtime'])));
		if($onedrive->getAccessTokenStatus()<1){
			return self::refresh_token($path);
		}else{
			return $onedrive;
		}
	}
	public function refresh_token($path){
		$bzarr=explode(':',$path);
		$id=trim($bzarr[1]);
		$cloud=C::t('connect')->fetch(self::BZ);
		if($arr=C::t(self::T)->fetch($id)){
			$onedrive = new Client(array('client_id' => $cloud['key'],'state'=>array('redirect_uri' => getglobal('siteurl').'oauth.php')));
			$ret=$onedrive->refreshAccessToken($cloud['secret'], $arr['refresh_token']);
			if(is_array($ret) && isset($ret['error'])) return $ret;
			$token=array();
			if($ret['access_token']){
				$token['refresh_token']=$ret['refresh_token'];
				$token['access_token']=$ret['access_token'];
				$token['refreshtime']=TIMESTAMP;
				C::t(self::T)->update($arr['id'],$token);
				$onedrive->setState($token);
				return $onedrive;
			}else{
				return array('error'=>lang('refresh_access_token'));
			}
		}
		return false;
	}
	public function authorize($refer){
		global $_G,$_GET;
		if(empty($_G['uid'])) {
			dsetcookie('_refer', rawurlencode(BASESCRIPT.'?mod=connect&op=oauth&bz=OneDrive'));
			showmessage('to_login', '', array(), array('showmsg' => true, 'login' => 1));
		}
		$cloud=C::t('connect')->fetch(self::BZ);
		$clientid=$cloud['key'];
		$onedrive = new Client(array('client_id' => $clientid,'state'=>array('redirect_uri' => $_G['siteurl'].'oauth.php')));
		if(!empty($_GET['code'])){
			$ret=$onedrive->obtainAccessToken($cloud['secret'], $_GET['code']);
			if($ret['error']) exit($ret['error']);
			
			if((($state=authcode($_GET['state'],'DECODE'))==$cloud['key'] || $state=='in_admin_'.$cloud['key']) && $ret['access_token']){
				$token=array();
				$token['refreshtime']=TIMESTAMP;
				$token['uid']=strpos($state,'in_admin_')===0?0:$_G['uid'];
				$userinfo=$onedrive->fetchAccountInfo();
				if(isset($userinfo['owner'])){
					$token['cuid']=$userinfo['owner']['user']['id'];
					$token['cusername']=$userinfo['owner']['user']['displayName'];
				}else{
					$token['cuid']=$ret['user_id'];
					$token['cusername']=$ret['user_id'];
				}
				$token['scope']=$ret['scope'];
				$token['refresh_token']=$ret['refresh_token'];
				$token['access_token']=$ret['access_token'];
				$token['expires_in']=$ret['expires_in'];
			
			
				if($id=DB::result_first("select id from %t where uid=%d and cuid=%d and bz='OneDrive'",array(self::T,$token['uid'],$token['cuid']))){
					C::t(self::T)->update($id,$token);
				}else{
					$token['bz']=self::BZ;
					$token['dateline']=TIMESTAMP;
					$id=C::t(self::T)->insert($token,1);
				}
				if(strpos($state,'in_admin_')===0){ //插入企业盘空间库(local_storage);
					$setarr=array('name'=>'OneDrive：'.$token['cuid'],
								  'bz'=>self::BZ,
								  'isdefault'=>0,
								  'dname'=>self::T,
								  'did'=>$id,
								  'dateline'=>TIMESTAMP
								  );
					if(!DB::result_first("select COUNT(*) from %t where did=%d and dname=%s",array('local_storage',$id,self::T))){
						C::t('local_storage')->insert($setarr);
					}
				}
				
				if(strpos($state,'in_admin_')===0){
					$returnurl='admin.php?mod=cloud&op=space';
				}else{
					if(!$refer) $refer=DZZSCRIPT.'?mod=connect';
					$returnurl=$refer;
				}
				
				@header('Location: '. $returnurl);
				//include template('oauth');
				exit();
			}
		}else{
		
			$state=authcode(defined('IN_ADMIN')?'in_admin_'.$clientid:$clientid,'ENCODE');
			$url = $onedrive->getLogInUrl(array(
													'wl.signin',
													'wl.offline_access',
													'onedrive.readwrite'
												)
												,$_G['siteurl'].'oauth.php'
										  );	
			session_start();
			$_SESSION['onedrive.oauth.state']=array('state'=>$state,'bz'=>self::BZ);
			@header('Location: ' . $url);
			exit();
		}
	}
	public function parsePath($path){
		$path=urldecode($path);
		$bzarr=explode(':',$path);
		$patharr=explode('/',$bzarr[2]);
		foreach($patharr as $key=>  $value){
			$patharr[$key]=urlencode($value);
		}
		$path1=implode('/',$patharr);
		return array('bz'=>$bzarr[0].':'.$bzarr[1].':','path'=>$path1);
	}
	/**
	 * 获取当前用户空间配额信息
	 * @return string
	 * Array
		(
			[quota] => 2207613190144
			[used] => 189239854410
		)
	 */
	public function getQuota($bz) {
		$onedrive=self::init($bz,1);
		if(is_array($onedrive) && $onedrive['error']) return $onedrive;
		$ret=$onedrive->fetchAccountInfo();
		if(isset($ret['error'])) return $ret;
		$arr=array();
		if($ret['quota']['total']) $arr['quota']=$ret['quota']['total'];
		if($ret['quota']['used']) $arr['used']=$ret['quota']['used'];
		return $arr;
	}
	//根据路径获取目录树的数据；
	public function getFolderDatasByPath($path){ 
	
		$bzarr=self::parsePath($path);
		$spath=$bzarr['path'];
		
		if($this->_root){
			$reg=str_replace('/','\/',$this->_root);
			$spath=preg_replace("/^".$reg."/i",'',$spath);
		}
		//exit("/^".$reg."/i");
		$patharr=explode('/',$spath);
		if(empty($patharr[0])) unset($patharr[0]);
		
		//print_r($bzarr);exit($spath);
		$folderarr=array();
		for($i=0;$i<=count($patharr);$i++){
			$path1=$bzarr['bz'].$this->_root;
			for($j=0;$j<=$i;$j++){
				$path1.='/'.$patharr[$j];
			}
			if($arr=self::getMeta($path1)){
				if(isset($arr['error'])) continue;
				$folder=self::getFolderByIcosdata($arr);
				$folderarr[$folder['fid']]=$folder;
			}
		}
		//print_r($folderarr);exit($path);

		return $folderarr;
	}
	
	public function createLink($path,$type='view'){
		$bzarr=self::parsePath($path);
		 try{
			$onedrive=self::init($path,1); 
			$ret=$onedrive->createLink($bzarr['path'],$type);
		}catch(Exception $e){
			return array('error'=>$e->getMessage());
		}	
	    if(isset($ret['error'])) return $ret;
	    return $ret['link']['webUrl'];
	}
	
	//获取文件流；
	//$path: 路径
	public function getStream($path){
		$bzarr=self::parsePath($path); 
		$onedrive=self::init($path,1);
		if(is_array($onedrive) && $onedrive['error']) return $onedrive;
		try{
		  $ret=$onedrive->fetchObject($bzarr['path']);
		  if(isset($ret['error'])) return $ret;
		  return $ret['@content.downloadUrl'];
		}catch(Exception $e){
			return array('error'=>$e->getMessage());
		}	
	}
	//获取文件流地址；
	//$path: 路径
	public function getFileUri($path){
		return self::getStream($path);
	}
	public function deleteThumb($path){
		global $_G;
		$imgcachePath='./imgcache/';
		$cachepath=str_replace(urlencode('/'),'/',urlencode(str_replace(':','/',$path)));
		foreach($_G['setting']['thumbsize'] as $value){
			$target = $imgcachePath . ($cachepath) . '.' . $value['width'] . '_' . $value['height'] . '_1.jpeg';
			$target1 = $imgcachePath . ($cachepath) . '.' . $value['width'] . '_' . $value['height'] . '_2.jpeg';
			@unlink($_G['setting']['attachdir'].$target);
			@unlink($_G['setting']['attachdir'].$target1);
		}
	}
	public function createThumb($path,$size,$width=0,$height=0,$thumbtype = 1){
		global $_G;
		if(intval($width)<1) $width=$_G['setting']['thumbsize'][$size]['width'];
		if(intval($height)<1) $height=$_G['setting']['thumbsize'][$size]['height'];
		$imgcachePath='imgcache/';
		$cachepath=str_replace(':','/',$path);
		$cachepath=preg_replace("/\/+/",'/',str_replace(':','/',$path));
		$target = $imgcachePath . ($cachepath) . '.' . $width . '_' . $height. '_'.$thumbtype.'.jpeg';
		if(@getimagesize($_G['setting']['attachdir'].'./'.$target)){
			return 2;//已经存在缩略图
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
				return 1;
			}else{
				return 0;
			}
		}else{
			 $fileurls=array('fileurl'=>self::getFileUri($path),'filedir'=>self::getStream($path));
		}
		//非图片类文件的时候，直接获取文件后缀对应的图片
		if(!$imginfo = @getimagesize($fileurls['filedir'])){
			return -1; //非图片不能生成
	    }
		if(($imginfo[0]<$width && $imginfo[1]<$height) ) {
			return 3;//小于要求尺寸，不需要生成
		}
		//获取缩略图
		$bzarr=self::parsePath($path); 
		$onedrive=self::init($path,1);
		if(is_array($onedrive) && $onedrive['error']) return false;
		$quality = 80;
		$result = $onedrive->thumbnails($bzarr['path'], $width, $height);
		$targetpath = dirname($_G['setting']['attachurl'].$target);
		dmkdir($targetpath);
		@file_put_contents($_G['setting']['attachdir'].$target,$result);
		return 1;
		
	}
	public function getThumb($path,$width,$height,$original,$returnurl=false,$thumbtype = 1){
		global $_G;
		$imgcachePath='imgcache/';
		$cachepath=str_replace(':','/',$path);
		$cachepath=preg_replace("/\/+/",'/',str_replace(':','/',$path));
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
	    //获取缩略图
		$bzarr=self::parsePath($path); 
		$onedrive=self::init($path,1);
		if(is_array($onedrive) && $onedrive['error']) return $onedrive;
		$result = $onedrive->thumbnails($bzarr['path'], $width, $height,$type);
		if($imgurl=$result['value'][0]['c'.$width.'x'.$height.($type=='Crop'?'_Crop':'')]['url']){
			if($returnurl) return $imgurl;
			if($enable_cache){ 
				$data=file_get_contents($imgurl);
				if($data) file_put_contents($_G['setting']['attachdir'].'./'.$target,$data);
			}
			IO::output_thumb($imgurl);
		}else{
			
		}
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
	public function listFiles($path,$by='time',$order='desc',$limit=200,$nextMarker=''){ 
		global $_G,$_GET,$documentexts,$imageexts;
		if($limitarr=explode('-',$limit)) $limit=intval($limitarr[1]?$limitarr[1]:$limitarr[0]);
		if(empty($by) || $by=='time') $by='lastModifiedDateTime';
		if(empty($order)) $order='desc';
		
		try{	
			$bzarr=self::parsePath($path);
			$bz=$bzarr['bz'];
			$path1=$bzarr['path'];
			$onedrive=self::init($path,1);
			if(is_array($onedrive) && $onedrive['error']) return $onedrive;
			
			$data=array();
			$icosdata=array();
			$param=array('orderby'=>$by.' '.strtolower($order),
						 'top'=>$limit
						 );
			if($nextMarker) $param['skiptoken']=$nextMarker;
			if($result = $onedrive->fetchChildren($path1,$param)){
				
				if($result['error']){
					return $result;
				}
				$data=$result['value'];
				foreach($data as $key => $value){
					$icoarr=self::_formatMeta($value,$bz,$path.'/'.$value['name']);
					$icosdata[$icoarr['icoid']]=$icoarr;
				}
				
				if($result['@odata.nextLink'] && preg_match("/skiptoken=(.+?)$/i",$result['@odata.nextLink'],$matches)){
						 $skiptoken=$matches[1];
						 $ico=self::getMeta($path);
						 $ico['nextMarker']= $skiptoken;
						 $icosdata[$ico['icoid']]=$ico;
				}
			}	
			return $icosdata;
		}catch(Exception $e){
			return array('error'=>$e->getMessage());
		}
	}
	/*
	 *获取文件的meta数据
	 *返回标准的icosdata
	 *$force>0 强制刷新，不读取缓存数据；
	*/
	public function getMeta($path,$force=0){ 
		global $_G,$_GET,$documentexts,$imageexts;
		$icosdata=array();
		$bzarr=self::parsePath($path);
		$bz=$bzarr['bz'];
		$path1=$bzarr['path'];
		
		// Get the metadata for the file/folder specified in $path
		$onedrive=self::init($bz,1);
		if(is_array($onedrive) && $onedrive['error']) return $onedrive;
		//exit($path1.'==='.$path.'==='.$bz);
		$meta=$onedrive->fetchObject($path1);
		if($meta['error']) return array('error'=>$meta['error']);
		$icosdata=self::_formatMeta($meta,$bz,$path);
		return $icosdata;
	}
	//将api获取的meta数据转化为icodata
	public function _formatMeta($meta,$bz,$path){ 
		global $_G,$documentexts,$imageexts;
		//判断是否为根目录
		$icosdata=array();
		
		$meta['id']=md5($meta['id']);//str_replace('!','%',$meta['id']);
		if($meta['folder']){
			$icoarr=array(
				  'icoid'=>md5($path),
				  'path'=>$path,
				  'dpath'=>dzzencode($path),
				  'bz'=>($bz),
				  'gid'=>0,
				  'name'=>$meta['name'],
				  'username'=>$_G['username'],
				  'uid'=>$_G['uid'],
				  'oid'=>md5($path),
				  'img'=>'dzz/images/default/system/folder.png',
				  'type'=>'folder',
				  'ext'=>'',
				  'ppath'=>str_replace(strrchr($path, '/'), '',$path),
				  'pfid'=>md5(str_replace(strrchr($path, '/'), '',$path)),
				  'size'=>0,
				  'dateline'=>strtotime($meta['lastModifiedDateTime']),
				   'flag'=>'',
				   'childCount'=>$meta['folder']['childCount']
				 );
				 if($path==$bz){
					 $icoarr['name']=$this->_rootname;
					 $icoarr['flag']=self::BZ;
					 $icoarr['pfid']=0;
					 $icoarr['path']=$bz;
					 $icoarr['dpath']=dzzencode($bz);
				 }
				/* print_r($icoarr);
				echo (str_replace(strrchr($path, '/'), '',$path));*/
				$icoarr['fsize']=formatsize($icoarr['size']);
				$icoarr['ftype']=getFileTypeName($icoarr['type'],$icoarr['ext']);
				$icoarr['fdateline']=dgmdate($icoarr['dateline']);
				$icosdata=$icoarr;
			
		}else{
			$ext=strtoupper(substr(strrchr($meta['name'], '.'), 1));
			if(in_array($ext,$imageexts)) $type='image';
			elseif(in_array($ext,$documentexts)) $type='document';
			else $type='attach';
			
			if($type=='image'){
				$img=$_G['siteurl'].DZZSCRIPT.'?mod=io&op=thumbnail&size=small&path='.dzzencode($path);
				$url=$_G['siteurl'].DZZSCRIPT.'?mod=io&op=thumbnail&size=large&path='.dzzencode($path);
			}else{
				$img=geticonfromext($ext,$type);
				$url=$_G['siteurl'].DZZSCRIPT.'?mod=io&op=getStream&path='.dzzencode($path).'&n='.urlencode($meta['name']);
			}
			$icoarr=array(
						  'icoid'=>md5($path),
						  'path'=>$path,
						  'dpath'=>dzzencode($path),
						  'bz'=>($bz),
						  'gid'=>0,
						  'name'=>$meta['name'],
						  'username'=>$_G['username'],
						  'uid'=>$_G['uid'],
						  'oid'=>md5($path),
						  'img'=>$img,
						  'url'=>$url,
						  'type'=>$type,
						  'ext'=>strtolower($ext),
						   'ppath'=>str_replace(strrchr($path, '/'), '',$path),
						  'pfid'=>md5(str_replace(strrchr($path, '/'), '',$path)),
						  'size'=>$meta['size'],
						  'dateline'=>strtotime($meta['lastModifiedDateTime']),
						  'flag'=>''
						  );
				
			$icoarr['fsize']=formatsize($icoarr['size']);
			$icoarr['ftype']=getFileTypeName($icoarr['type'],$icoarr['ext']);
			$icoarr['fdateline']=dgmdate($icoarr['dateline']);
			$icosdata=$icoarr;
		}
				
		return $icosdata;
	}
	//通过icosdata获取folderdata数据
	public function getFolderByIcosdata($icosdata){
		global $_GET;
		$folder=array();
		if($icosdata['type']=='folder'){
			$folder=array('fid'=>$icosdata['oid'],
						  'path'=>$icosdata['path'],
						  'fname'=>$icosdata['name'],
						  'uid'=>$icosdata['uid'],
						  'pfid'=>$icosdata['pfid'],
						  'iconview'=>$_GET['iconview']?intval($_GET['iconview']):1,
						  'disp'=>$_GET['disp']?intval($_GET['disp']):1,
						  'perm'=>$this->perm,
						  'bz'=>$icosdata['bz'],
						  'gid'=>$icosdata['gid'],
						  'ppath'=>$icosdata['ppath'],
						  'childCount'=>$icosdata['childCount'],
						  'fsperm'=>perm_FolderSPerm::flagPower('external')
						);
			
		}
		return $folder;
	}
	//获得文件内容；
	public function getFileContent($path){
		$ret=self::getStream($path);
		if(is_array($ret) && $ret['error']) return $ret;
		return file_get_contents($ret);
	}
	//重写文件内容
	//@param number $path  文件的路径
	//@param string $data  文件的新内容
	public function setFileContent($path,$data){
		$bzarr=self::parsePath($path);
		$bz=$bzarr['bz'];
		
		$patharr=explode('/',urldecode($bzarr['path']));
		$filename=$patharr[count($patharr)-1];
		unset($patharr[count($patharr)-1]);
		$path1=urlencode(implode('/',$patharr));
		$onedrive=self::init($bz,1);
		if(is_array($onedrive) && $onedrive['error']) return $onedrive;
		 $meta=$onedrive->createFile($path1,$filename,$data);
		 if($meta['error']) return array('error'=>$meta['error']);
		 $icoarr=self::_formatMeta($meta,$bz,$path);
		 if($icoarr['type']=='image'){
			  self::deleteThumb($path);
			  $icoarr['img'].='&t='.TIMESTAMP;
		 }
		 return $icoarr;
	}
	
	public function rename($path,$name){//重命名
		$bzarr=self::parsePath($path);
		$bz=$bzarr['bz'];
		$path1=$bzarr['path'];
		$onedrive=self::init($path,1);
		if(is_array($onedrive) && $onedrive['error']) return $onedrive;
		//exit($path1.'==='.$path.'==='.$bz);
		$ext=strtolower(substr(strrchr($path, '.'), 1));
		$name=($ext?(str_replace('.','_',preg_replace("/\.\w+$/i",'',$name)).'.'.$ext):$name);
		$name=io_dzz::name_filter($name);
		$meta=$onedrive->updateObject($path1,array('name'=>$name));
		if($meta['error']) return array('error'=>$meta['error']);
		return self::_formatMeta($meta,$bz,$path);
	}
	
	//添加目录
	//$fname：目录路径;
	//$container：目标容器
	//$bz：api;
	public function CreateFolder($path,$fname,$ondup='rename'){
		global $_G;
		$bzarr=self::parsePath($path);
		$bz=$bzarr['bz'];
		$path1=$bzarr['path'];
		$return=array();
		try {
			$onedrive=self::init($bz);
			if(is_array($onedrive) && $onedrive['error']) return $onedrive;
			$fname=io_dzz::name_filter($fname);
			$ret=$onedrive->createFolder($path1,$fname,$ondup);
			if(is_array($ret) && $ret['error']){
				 return $ret;
			}
			$icoarr=self::_formatMeta($ret,$bz,$path.'/'.$fname);
			$folderarr=self::getFolderByIcosdata($icoarr);
			$return= array('folderarr'=>$folderarr,'icoarr'=>$icoarr);
		}catch(Exception $e){
			//var_dump($e);
			$return=array('error'=>$e->getMessage());
		}
		return $return;
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
		include_once libfile('class/ZipStream');
		
		$zip = new ZipStream($filename.".zip");
		$data=self::getFolderInfo($path,'',$zip);
		//$zip->setComment("$meta[name] " . date('l jS \of F Y h:i:s A'));
		/*foreach($data as $value){
			 $zip->addLargeFile(fopen($value['url'],'rb'), $value['position'], $value['dateline']);
		}*/
		$zip->finalize();
	}
	public function getFolderInfo($paths,$position='',&$zip){
		static $data=array();
		try{
			foreach($paths as $path){
				$onedrive=self::init($path,1); 
				if(is_array($onedrive) && $onedrive['error']) return $onedrive;
				$meta=self::getMeta($path);
				switch($meta['type']){
					case 'folder':
						 $lposition=$position.$meta['name'].'/';
						 $contents=self::listFiles($path);
						 $arr=array();
						 foreach($contents as $key=>$value){
							$arr[]=$value['path'];
						 }
						 if($arr) self::getFolderInfo($arr,$lposition,$zip);
						break;
					default:
						$meta['url']=self::getStream($meta['path']);
						$meta['position']=$position.$meta['name'];
						//$data[$meta['icoid']]=$meta;
						$zip->addLargeFile(fopen($meta['url'],'rb'), $meta['position'], $meta['dateline']);
				}
			}
		
		}catch(Exception $e){
			//var_dump($e);
			$data['error']=$e->getMessage();
			return $data;
		}
		//return $data;
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
		$url=self::getStream($path);
		try {
			// Download the file
			$file=self::getMeta($path);
			if($file['type']=='folder'){//目录压缩下载
				self::zipdownload($path);
				exit();
			}else{//文件直接跳转到文件源地址；不再通过服务器中转
				/*@header("Location: $url");
				exit();*/
				$file['name'] = '"'.(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($file['name']) : $file['name']).'"';
				$d=new FileDownload();
				$d->download($url,$file['name'],$file['size'],$file['dateline'],true);
				exit();
			}
			
		
		} catch (Exception $e) {
			// The file wasn't found at the specified path/revision
			//echo 'The file was not found at the specified path/revision';
			topshowmessage($e->getMessage());
		}
	}
	
	/* 
	 * 分块上传文件
	 * param $file:文件路径（可以是url路径，需要服务器开启allow_url_fopen);
	*/

	public function multiUpload($opath,$path,$filename,$attach=array(),$ondup="rename"){
		global $_G;
			
		$partsize=1024*1024*1; //分块大小2M
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
			if(!$handle=fopen($filepath, 'rb')){
				return array('error'=>lang('open_file_error'));
			}
			while(!feof($handle)){
				$fileContent.= fread($handle, 8192);
				//if(strlen($fileContent)==0) return array('error'=>'文件不存在');
			}
			return self::upload_by_content($fileContent,$path,$filename,array(),$ondup);
		}else{ //分片上传
			self::deleteCache($path.'/'.$filename);
			$partinfo=array('ispart'=>true,'partnum'=>0,'iscomplete'=>false);
			if(!$handle=fopen($filepath, 'rb')){
				return array('error'=>lang('open_file_error'));
			}
			$cachefile=$_G['setting']['attachdir'].'./cache/'.md5($opath).random(5).'.dzz';
			$start=0;
			while (!feof($handle)) {
				$fileContent.=fread($handle, 8192);
				//if(strlen($fileContent)==0) return array('error'=>'文件不存在');
				if(strlen($fileContent)>=$partsize){
					if($partinfo['partnum']*$partsize+strlen($fileContent)>=$size) $partinfo['iscomplete']=true;
					$partinfo['partnum']+=1;
					file_put_contents($cachefile,$fileContent);
					
					$end=$start+filesize($cachefile)-1;
					
					$partinfo['Content-Range']='bytes '.$start.'-'.$end.'/'.$size;
					
					$re=self::upload($cachefile,$path,$filename,$partinfo);
					if($re['error']) return $re;
					if($partinfo['iscomplete']){
						 @unlink($cachefile);
						 return $re;
					}
					$start+=(strlen($fileContent));
					$fileContent='';
					
				}
			}
			fclose($handle);
			if(!empty($fileContent)){
				$partinfo['partnum']+=1;
				$partinfo['iscomplete']=true;
				file_put_contents($cachefile,$fileContent);
				$end=$start+strlen($fileContent)-1;
				$partinfo['Content-Range']='bytes '.$start.'-'.$end.'/'.$size;
				$re=self::upload($cachefile,$path,$filename,$partinfo);
				@unlink($cachefile);
				return $re;
				
			}
		}
	}
	
	
	//删除原内容
	//$path: 删除的路径
	//$bz: 删除的api;
	//$data：可以删除的id数组（当剪切的时候，为了保证数据不丢失，目标位置添加成功后将此id添加到data数组，
	//删除时如果$data有数据，将会只删除id在$data中的数据；
	//如果删除的是目录或下级有目录，需要判断此目录内是否所有元素都在删除的id中，如果有未删除的元素，则此目录保留不会删除；
	//$force 真实删除，不放入回收站
	public function Delete($path,$force=false){
		//global $dropbox;
		$bzarr=self::parsePath($path);
		$bz=$bzarr['bz'];
		$path1=$bzarr['path'];
		try{
			$onedrive=self::init($path,$force);
			if(is_array($onedrive) && $onedrive['error']) return $onedrive;
			$response = $onedrive->deleteObject($path1);
			if($response['error']){
				return array('icoid'=>md5(($path)),'error'=>$response['error']);
			}
			return array('icoid'=>md5(($path)),
						 'name'=>substr(strrchr($path, '/'), 1),
						);
		}catch(Exception $e){
			return array('icoid'=>md5($path),'error'=>$e->getMessage());
		}
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
	private function getPartInfo($content_range){
		$arr=array();
		if(!$content_range){
			 $arr['ispart']=false;
			 $arr['iscomplete']=true;
		}elseif(is_array($content_range)){
			$arr['ispart']=true;
			$partsize=getglobal('setting/maxChunkSize');
			$arr['partnum']=ceil(($content_range[2]+1)/$partsize);
			$arr['Content-Range']='bytes '.$content_range[1].'-'.$content_range[2].'/'.$content_range[3];
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
	private function getCache($path){
		$cachekey='onedrive_upload_'.md5($path);
		$cache=C::t('cache')->fetch($cachekey);
		return (unserialize($cache['cachevalue']));
	}
	private function saveCache($path,$data){
		global $_G;
		$cachekey='onedrive_upload_'.md5($path);
		
		C::t('cache')->insert(array(
							'cachekey' => $cachekey,
							'cachevalue' => serialize($data),
							'dateline' => $_G['timestamp'],
						), false, true);
	}
	private function deleteCache($path){
		$cachekey='onedrive_upload_'.md5($path);
		C::t('cache')->delete($cachekey);
	}
		/**
	 * 移动文件到目标位置
	 * @param string $opath 被移动的文件路径
	 * @param string $path 目标位置（可能是同一api内或跨api，这两种情况分开处理）
	 * @return icosdatas
	 */
	public function CopyTo($opath,$path,$iscopy){
		$oarr=self::parsePath($opath);
		$arr=IO::parsePath($path);
	try{
		$onedrive=self::init($opath);
		if(is_array($onedrive) && $onedrive['error']) return $onedrive;
			if($arr['bz']==$oarr['bz'] ){ //同一api内
			    //$data=self::getMeta($opath);
				//$meta=$onedrive->updateObject($oarr['path'],array('parentReference'=>array('path'=>$arr['path'])));
				if(!$iscopy){
					$meta=$onedrive->moveObject($oarr['path'],$arr['path']);
				}else{
					$meta=$onedrive->copyObject($oarr['path'],$arr['path']);
				}
				if(is_array($meta) && $meta['error']) return $meta;
				
				$data['newdata']=self::_formatMeta($meta,$arr['bz'],$path.'/'.$data['name']);
				$data['success']=true;
				$data['moved']=true;//表示移动成功（不需要再删除原位置）；
				print_r($meta);
				return $data;
			}else{
				$data=self::getMeta($opath);
				
				switch($data['type']){
					case 'folder'://创建目录
						if($re=IO::CreateFolder($path,$data['name'])){
							if(isset($re['error']) && $re['code']!='nameAlreadyExists'){
								$data['success']=$re['error'];
							}else{
								$data['newdata']=$re['icoarr'];
								$data['success']=true;
								 $contents=self::listFiles($opath);
								 foreach($contents as $key=>$value){
									$data['contents'][$key]=self::CopyTo($value['path'],$re['folderarr']['path']);
								 }
							}
						}
						break;
					default:
						
						//$fileContent=IO::getFileContent($opath);
						//exit($opath.'==='.$path.'==='.$data['name']);
						if($re=IO::multiUpload($opath,$path,$data['name'])){
							if($re['error']) $data['success']=$re['error'];
							else{
								$data['newdata']=$re;
								$data['success']=true;
							}
						}
				}
				
			}
		}catch(Exception $e){
			//var_dump($e);
			$data['success']=$e->getMessage();
			return $data;
		}
		return $data;
	}
	
	public function uploadStream($file,$filename,$path,$relativePath,$content_range){
		$data=array();
		
		//处理目录(没有分片或者最后一个分片时创建目录
		$arr=self::getPartInfo($content_range);
		
		if($relativePath && ($arr['iscomplete'])){
			$path1=$path;
			$patharr=explode('/',$relativePath);
			foreach($patharr as $key=> $value){
				if(!$value){
					unset($patharr[$key]);
					 continue;
				}
				if($patharr[$key-1]) $path1.='/'.$patharr[$key-1];
				
				$re=self::CreateFolder($path1,$value);
				
				if(intval($re['code'])=='nameAlreadyExists'){
					continue;
				}else{
					if(isset($re['error'])){
						return $re;
					}else{
						if($key==0){
							$data['icoarr'][]=$re['icoarr'];
							$data['folderarr'][]=$re['folderarr'];
						}
					}
				}
			}
			//$path.='/'.implode('/',$patharr);
		}
		if($relativePath) $path=$path.'/'.$relativePath;
		
		if($arr['ispart']){
			if($re1=self::upload($file,$path,$filename,$arr)){
				if($re1['error']){
					return $re1;
				}
				if($arr['iscomplete']){
					$data['icoarr'][] = $re1;
					return $data;
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

	function upload_by_content($fileContent,$path,$filename,$partinfo=array(),$ondup='rename'){
		$cachefile=getglobal('setting/attachdir').'cache/'.random(10).'_'.$filename;
		file_put_contents($cachefile,$fileContent);
		$ret=self::upload($cachefile,$path,$filename,$partinfo,$ondup);
		@unlink($cachefile);
		return $ret;
	}

	public function upload($file,$path,$filename,$partinfo=array()){
		global $_G;
		//$path.=$filename;
		$arr=self::parsePath($path);
		try{
			$onedrive=self::init($path);
			if(is_array($onedrive) && $onedrive['error']) return $onedrive;
			
			if($partinfo['partnum']){
				if($partinfo['partnum']==1){//第一个分块时 初始化分块上传得到$uploadSession;并缓存住，留以后分块使用
					//初始化分块
					$response=$onedrive->createSession($arr['path'],$filename);
					if(empty($response['uploadUrl'])){
						return array('error'=>'upload.createSession failure '.$response['error']);
					}
					$uploadUrl=$response['uploadUrl'];
					//上传分块
					$ret=$onedrive->uploadFragment($uploadUrl,$file, $partinfo['Content-Range']);
					if($ret['error']){
						$onedrive->cancelSession($uploadUrl);
						return array('error'=>'upload partNember 1 error '.$ret['error']);
					}
					
				
					
					$data=array();
					$data['uploadUrl']=$uploadUrl;
					$data['filesize']=filesize($file);
					$data['partnum']=1;
					self::saveCache($path,$data);
					return true;
				}elseif($partinfo['iscomplete']){
					$cache=self::getCache($path);
					$uploadUrl=$cache['uploadUrl'];
					//上传分块
					$ret=$onedrive->uploadFragment($uploadUrl,$file, $partinfo['Content-Range']);
					
					if($ret['error']){
						self::deleteCache($path);
						$onedrive->cancelSession($uploadUrl);
						return array('error'=>'upload partNember '.$cache['partnum'].' error '.$ret['error']);
					}

					self::deleteCache($path);
					
					$icoarr=self::_formatMeta($ret,$arr['bz'],$path.'/'.$filename);
					
					return $icoarr;
				
				}else{
					$cache=self::getCache($path);
					$uploadUrl=$cache['uploadUrl'];
					$cache['partnum']+=1;
					//上传分块
					$ret=$onedrive->uploadFragment($uploadUrl,$file, $partinfo['Content-Range']);
					if($ret['error']){
						self::deleteCache($path);
						$onedrive->cancelSession($uploadUrl);
						return array('error'=>'upload partNember '.$cache['partnum'].' error '.$ret['error']);
					}

					//print_r($cache);
					$cache['filesize']+=filesize($file);
					
					//print_r($cache);exit('dddd');
					self::saveCache($path,$cache);
					return true;
				}
				
			}else{
				$meta = $onedrive->uploadFile($arr['path'],$filename,$file);
				
				if($meta['error']){
					return $meta;
				}

				$icoarr=self::_formatMeta($meta,$arr['bz'],$path.'/'.$filename);
				
				return $icoarr;
			}
		}catch(Exception $e){
			return array('error'=>$e->getMessage());
		}
		
	}
}
?>
