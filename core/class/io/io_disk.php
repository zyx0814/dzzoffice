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
if(!defined('DS')) define('DS',DIRECTORY_SEPARATOR);
class io_disk extends io_api
{
	
	const T ='connect_disk';
	const BZ='disk';
	var $perm=0;
	var $icosdatas=array();
	var $error='';
	var $conn=null;
	var $encode='GBK';
	var $_root='';
	var $_rootname='';
	var $attachdir='';
	public function __construct($path) {
		$bzarr=explode(':',$path);
		$did=trim($bzarr[1]);
		if($config=DB::fetch_first("select * from ".DB::table(self::T)." where  id='{$did}'")){
			$this->_root='disk:'.$config['id'].':';
			$this->encode=$config['charset'];
			$this->_rootname=$config['cloudname'];
			$this->attachdir=diconv($config['attachdir'],CHARSET,$config['charset']).DS;
			
		}else{
			$this->error='need authorize';
		}
		$this->perm=perm_binPerm::getMyPower();
		return $this;
	}
	
	public function MoveToSpace($path,$attach){
		global $_G;
		$filename=substr($path,strrpos($path,'/')+1);;
		$fpath=substr($path,0,strrpos($path,'/'));
		$obz=io_remote::getBzByRemoteid($attach['remote']);
		if($obz=='dzz'){
			$opath='attach::'.$attach['aid'];
		}else{
			$opath=$obz.'/'.$attach['attachment'];
		}
		if($re=self::multiUpload($opath,$fpath,$filename,$attach,'overwrite')){
			//print_r($ret);exit($path);
			if($re['error']) return $re;
			else{
				return true;
			}
		}
		return false;
	}
	//根据路径获取目录树的数据；
	public function getFolderDatasByPath($path){ 
	
		$bzarr=self::parsePath($path);
		
		$spath=preg_replace("/\/+/",'/',$bzarr['path1']);
		if($spath){
			$patharr=explode('/',trim($spath,'/'));
		}else{
			$patharr=array();
		}
		//if(empty($patharr[0])) unset($patharr[0]);
		$folderarr=array();
		for($i=0;$i<=count($patharr);$i++){
			$path1='';
			for($j=0;$j<$i;$j++){
				if($patharr[$j]) $path1.=$path1?'/'.$patharr[$j]:$patharr[$j];
			}
			$path1=$bzarr['bz'].$path1;
			if($arr=self::getMeta($path1)){
				if(isset($arr['error'])) continue;
				$folder=self::getFolderByIcosdata($arr);
				$folderarr[$folder['fid']]=$folder;
			}
		}
		return $folderarr;
	}
	private function checkdisk($config){//测试磁盘是否可以读写正常
		$str='test read write';
		$filename='新建文本文档__test.txt';
		$filename_encode=diconv($filename,CHARSET,$config['charset']);
		//$filename_encode=$filename;
		$filepath=diconv($config['attachdir'],CHARSET,$config['charset']);
		if(!$attachdir=realpath($filepath)) return array('error'=>'folder not exist! or no permission');
		if(!file_put_contents($attachdir.DIRECTORY_SEPARATOR.$filename_encode,$str)){
			return array('error'=>'folder '.$config['attachdir'].' not writable');
		}
		//exit($attachdir.DIRECTORY_SEPARATOR.$filename_encode);
		if($str!=file_get_contents($attachdir.DIRECTORY_SEPARATOR.$filename_encode)){
			return array('error'=>'folder '.$config['attachdir'].' not readable');
		}
		@unlink($attachdir.DIRECTORY_SEPARATOR.$filename_encode);
		return true;
	}
	public function authorize($refer){
		global $_G,$_GET,$clouds;
		if(empty($_G['uid'])) {
			dsetcookie('_refer', rawurlencode(BASESCRIPT.'?mod=connect&op=oauth&bz=disk'));
			showmessage('to_login', '', array(), array('showmsg' => true, 'login' => 1));
		}
		if(submitcheck('disksubmit')){
			$config=$_GET['config'];
			$config['bz']='disk';
			$uid=defined('IN_ADMIN')?0:$_G['uid'];
			if($ret = self::checkdisk($config)){
				if($ret['error']) showmessage($ret['error'],BASESCRIPT.'?mod=cloud&op=space');
			}
			$config['uid']=$uid;
			if($id=DB::result_first("select id from %t where uid=%d and attachdir=%s",array(self::T,$uid,$config['attachdir']))){
				DB::update(self::T,$config,"id ='{$id}'");
			}else{
				$config['dateline']=TIMESTAMP;
				$id=DB::insert(self::T,$config,1);
			}
			if(defined('IN_ADMIN')){
				$setarr=array('name'=>$config['cloudname'],
							  'bz'=>'disk',
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
			include template('oauth_disk');
		}
	}
	public function parsePath($path){
		$bzarr=explode(':',$path);
		$bzarr[2]=trim($bzarr[2],'/');
		return array('bz'=>$bzarr[0].':'.$bzarr[1].':','path'=>diconv($bzarr[2],CHARSET,$this->encode),'path1'=>$bzarr[2]);
	}
	
	//获取文件流；
	//$path: 路径
	function getStream($path){
		$arr=self::parsePath($path);
		if(!$ret=realpath($this->attachdir.$arr['path'])){
			return array('error'=>lang('file_not_exist'));
		}
		return $ret;
	}
	//获取文件流地址；
	//$path: 路径
	function getFileUri($path){
		$filename=basename($path);
		return getglobal('siteurl').'index.php?mod=io&op=getStream&path='.dzzencode($path).'&n='.$filename;
	}
	public function deleteThumb($path){
		global $_G;
		$imgcachePath='./imgcache/';
		$cachepath=preg_replace("/\/+/",'/',str_replace(':','/',$path));
		foreach($_G['setting']['thumbsize'] as $value){
			$target = $imgcachePath . ($cachepath) . '.' . $value['width'] . '_' . $value['height'] . '_1.jpeg';
			$target1 = $imgcachePath . ($cachepath) . '.' . $value['width'] . '_' . $value['height'] . '_2.jpeg';
			@unlink($_G['setting']['attachdir'].$target);
			@unlink($_G['setting']['attachdir'].$target1);
		}
	}
	public function createThumb($path,$size,$width = 0,$height = 0,$thumbtype = 1){
		global $_G;
		$imgcachePath = 'imgcache/';
		$cachepath=preg_replace("/\/+/",'/',str_replace(':','/',$path));
	    $target = $imgcachePath . ($cachepath) . '.' . $width . '_' . $height . '.jpeg';
		$target = $imgcachePath . ($cachepath) . '.' . $width . '_' . $height . '_'.$thumbtype.'.jpeg';
		if (@getimagesize($_G['setting']['attachdir'] .'./'. $target)) {
            return 2;//已经存在缩略图
        }
		$fileurls=array();
		Hook::listen('thumbnail',$fileurls,$path);//生成缩略图绝对和相对地址；
		if(!$fileurls){
			 $fileurls=array('fileurl'=>self::getFileUri($path),'filedir'=>self::getStream($path));
		}
        $filepath = $fileurls['filedir'];
        if (intval($width) < 1) $width = $_G['setting']['thumbsize'][$size]['width'];
        if (intval($height) < 1) $height = $_G['setting']['thumbsize'][$size]['height'];
      
        if (!$imginfo = @getimagesize($filepath)) {
            return -1; //非图片不能生成
        }

        if (($imginfo[0] < $width && $imginfo[1] < $height)) {
            return 3;//小于要求尺寸，不需要生成
        }
       
        //生成缩略图
        include_once libfile('class/image');
        $target_attach = $_G['setting']['attachdir'] .'./'. $target;
        $targetpath = dirname($target_attach);
        dmkdir($targetpath);
        $image = new image();

        if ($thumb = $image->Thumb($filepath, $target, $width, $height,$thumbtype)) {
            return 1;//生成缩略图成功
        } else {
            return 0;//生成缩略图失败
        }
		
	}
	public function getThumb($path,$width,$height,$original,$returnurl = false,$thumbtype = 0){
		global $_G;
		$imgcachePath='imgcache/';
		$cachepath=str_replace(':','/',$path);
		$cachepath=preg_replace("/\/+/",'/',str_replace(':','/',$path));
		if(!$data=IO::getMeta($path)) return false;
        $enable_cache = true; //是否启用缓存
        $quality = 80;
		$target = $imgcachePath . ($cachepath) . '.' . $width . '_' . $height . '_'.$thumbtype.'.jpeg';
        if (!$original && $enable_cache && @getimagesize($_G['setting']['attachdir'] .'./'. $target)) {
            if ($returnurl) return $_G['setting']['attachurl'] .'/'. $target;
            $file = $_G['setting']['attachdir'] . './' . $target;
            IO::output_thumb($file);
        } 
		
		
        $fileurls=array();
		Hook::listen('thumbnail',$fileurls,$path);//调用挂载点程序生成缩略图绝对和相对地址；
		if(!$fileurls){
			 $fileurls=array('fileurl'=>self::getFileUri($path),'filedir'=>self::getStream($path));
		}
		//非图片类文件的时候，直接获取文件后缀对应的图片
		if(!$imginfo = @getimagesize($fileurls['filedir'])){
		   $imgurl= geticonfromext($data['ext'],$data['type']);
		   if ($returnurl) return $imgurl;//$_G['setting']['attachurl'].'./'.$data['attachment'];
            $file = $imgurl;//$_G['setting']['attachdir'].'./'.$data['attachment'];
           IO::output_thumb($file);
	    }
		//返回原图的时候
		if ($original) {
            if ($returnurl) return $fileurls['fileurl'];//$_G['setting']['attachurl'].'./'.$data['attachment'];
            $file = $fileurls['filedir'];//$_G['setting']['attachdir'].'./'.$data['attachment'];
          	IO::output_thumb($file);
        }
	   //图片小于缩略图宽高的不生成直接返回原图
        if (($imginfo[0] < $width && $imginfo[1] < $height)) {
            if ($returnurl) return $fileurls['fileurl'];
            $file = $fileurls['filedir'];//$_G['setting']['attachdir'].'./'.$data['attachment'];
            IO::output_thumb($file);
        }
		
		 //生成缩略图
        include_once libfile('class/image');
        $target_attach = $_G['setting']['attachdir'].'./'. $target;
        $targetpath = dirname($target_attach);
        dmkdir($targetpath);
		$filepath = $fileurls['filedir'];
        $image = new image();
        if($thumb = $image->Thumb($filepath, $target, $width, $height,$thumbtype)){
        //if ($thumb = $image->Thumb($file, $target, $width, $height, 1)) {
           if ($returnurl) return $_G['setting']['attachurl'] .'/'. $target;
           $file = $target_attach;
           IO::output_thumb($file);
        } else {
            if ($returnurl) return $fileurls['fileurl'];
			$file = $fileurls['filedir'];
			IO::output_thumb($file);
        }
	}
	//重写文件内容
	//@param number $path  文件的路径
	//@param string $data  文件的新内容
	public function setFileContent($path,$data){
		
		$bzarr=self::parsepath($path);
		
		$file=$this->attachdir.$bzarr['path'];
		if(!file_put_contents($file,$data)){
			return array('error'=>'写入失败');
		}
		
		 $icoarr=self::getMeta($path);
		 if($icoarr['type']=='image'){
			  $icoarr['img'].='&t='.TIMESTAMP;
		 }
		 return $icoarr;
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
		$data=self::getMeta($opath);
		switch($data['type']){
			case 'folder'://创建目录
				if($re=IO::CreateFolder($path,$data['name'])){
					$data['newdata']=$re['icoarr'];
					$data['success']=true;
					 $contents=self::listFiles($opath);
					 foreach($contents as $key=>$value){
						$data['contents'][$key]=self::CopyTo($value['path'],$re['folderarr']['path']);
					 }
				}
				break;
			default:
				
				if($re=IO::multiUpload($opath,$path,$data['name'])){
					if($re['error']) $data['success']=$re['error'];
					else{
						$data['newdata']=$re;
						$data['success']=true;
					}
				}
		}
			
		
		return $data;
	}
	/* 
	 * 分块上传文件
	 * param $file:文件路径（可以是url路径，需要服务器开启allow_url_fopen);
	*/
	public function multiUpload($opath,$path,$filename,$attach=array(),$ondup="newcopy"){
		
	/* 
	 * 分块上传文件
	 * param $file:文件路径（可以是url路径，需要服务器开启allow_url_fopen);
	*/
		if(is_array($filepath=IO::getStream($opath))){
			return array('error'=>$filepath['error']);
		}
		//判断大小
		//判断空间大小
		$filename=self::name_filter($filename);
		
		if(!$handle=fopen($filepath, 'rb')){
			return array('error'=>lang('open_file_error'));
		}
		$arr=self::parsePath($path.'/'.$filename);
			
		$file=$this->attachdir.$arr['path'];
		$dirpath=dirname($file);
		if(!is_dir($dirpath)) dmkdir($dirpath,0777,false);
		while (!feof($handle)) {
		  $fileContent= fread($handle, 8192);
		  if(!file_put_contents($file,$fileContent,FILE_APPEND)){
			  return array('error'=>lang('written_file_correct'));
		  }
		  unset($fileContent);
		}
		fclose($handle);
		return self::getMeta($arr['bz'].$arr['path1']);
		
		
	}
	function getTextEncode($str,$encode){
		include_once DZZ_ROOT.'./dzz/class/class_encode.php';
		$p = new Encode_Core();
		$code = $p -> get_encoding($str); 
		return diconv($str,$code,CHARSET);
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
	function listFiles($path,$by='time',$order='desc',$limit='',$force=0){
		if($this->error)  return array('error'=>$this->error);
		$bzarr=self::parsePath($path);
		$filepath=$this->attachdir.($bzarr['path']?('./'.$bzarr['path']):'');
		$icosdata=array();
		foreach(new DirectoryIterator($filepath) as  $file){
			if ($file->isDot()) {
                    continue;
            }
			$filename=diconv($file->getFilename(),$this->encode,CHARSET);
			if($file->isDir()){
				
				$fileinfo=array(
					'path'=>($bzarr['path1']?($bzarr['path1'].'/'):'').$filename,
					'name'=>$filename,
					'type'=>'folder',
					'size'=>'-'
				);
			}else{
				$fileinfo=array(
					'path'=>($bzarr['path1']?($bzarr['path1'].'/'):'').$filename,
					'name'=>$filename,
					'type'=>'file',
					'size'=>$file->getSize(),
					'ctime'=>$file->getCTime(),
					'mtime'=>$file->getMTime()
				);
			}
			$icoarr=self::_formatMeta($fileinfo,$bzarr['bz']);
			$icosdata[$icoarr['icoid']]=$icoarr;
		}
		return $icosdata;
	}
	
	/*
	 *获取文件的meta数据
	 *返回标准的icosdata
	 *$force>0 强制刷新，不读取缓存数据；
	*/
	function getMeta($path,$force=0){ 
		$bzarr=self::parsePath($path);
		$meta=array();
		if($path==$this->_root){
			$meta['path']='';
			$meta['name']=$this->_rootname;
			$meta['type']='folder';
			$meta['size']='-';
			$meta['flag']=self::BZ;
		}else{
		
			$meta['path']=$bzarr['path1'];
			if(strpos($bzarr['path1'],'/')!==false){
				$meta['name']=substr($bzarr['path1'],strrpos($bzarr['path1'],'/')+1);
			}else{
				$meta['name']=$bzarr['path1'];
			}
			
			$file=$this->attachdir.$bzarr['path'];
			if(is_dir($file)){
				$meta['type']='folder';
				$meta['size']='-';
			}else{
				$meta['type']='file';
				$meta['size']=filesize($file);
				$meta['mtime']=filectime($file);
				if($meta['mtime']<0) $meta['mtime']=0;
			}
			
		}

		$icosdata=self::_formatMeta($meta,$bzarr['bz']);
		return $icosdata;
	}
	//将api获取的meta数据转化为icodata
	function _formatMeta($meta,$bz){ 
		global $_G,$documentexts,$imageexts;
		//判断是否为根目录
		$icosdata=array();
		if($meta['path']==''){
			$pfid=0;
		}elseif(strpos($meta['path'],'/')===false){
			$pfid=md5($bz);
		}else{
			$pfid=md5(str_replace(strrchr($meta['path'], '/'), '',$bz.$meta['path']));
		}
		if($meta['type']=='folder'){
			$icoarr=array(
				  'icoid'=>md5(($bz.$meta['path'])),
				  'path'=>$bz.$meta['path'],
				  'dpath'=>dzzencode($bz.$meta['path']),
				  'bz'=>($bz),
				  'gid'=>0,
				  'name'=>$meta['name'],
				  'username'=>$_G['username'],
				  'uid'=>$_G['uid'],
				  'oid'=>md5(($bz.$meta['path'])),
				  'img'=>'dzz/images/default/system/folder.png',
				  'type'=>'folder',
				  'ext'=>'',
				  'pfid'=>$pfid,
				  'size'=>'-',
				  'dateline'=>intval($meta['mtime']),
				  'flag'=>$meta['flag']?$meta['flag']:'',
				  'mod'=>$meta['mod']
				 );
				 
				$icoarr['fsize']='-';
				$icoarr['ftype']=getFileTypeName($icoarr['type'],$icoarr['ext']);
				if(!$icoarr['dateline']) $icoarr['fdateline']='-';
				else $icoarr['fdateline']=dgmdate($icoarr['dateline']);
				$icosdata=$icoarr;
			
		}else{
			$pathinfo = pathinfo($meta['path']);
			$ext = strtoupper($pathinfo['extension']);
			if(in_array($ext,$imageexts)) $type='image';
			elseif(in_array($ext,$documentexts)) $type='document';
			else $type='attach';
			if($type=='image'){
				$img=$_G['siteurl'].DZZSCRIPT.'?mod=io&op=thumbnail&size=small&path='.dzzencode($bz.$meta['path']);
				$url=$_G['siteurl'].DZZSCRIPT.'?mod=io&op=thumbnail&size=large&path='.dzzencode($bz.$meta['path']);
			}else{
				$img=geticonfromext($ext,$type);
				$url=$_G['siteurl'].DZZSCRIPT.'?mod=io&op=getStream&path='.dzzencode($bz.$meta['path']);
			}
			$icoarr=array(
						  'icoid'=>md5(($bz.$meta['path'])),
						  'path'=>($bz.$meta['path']), 
						  'dpath'=>dzzencode($bz.$meta['path']),
						  'bz'=>($bz),
						  'gid'=>0,
						  'name'=>$meta['name'],
						  'username'=>$_G['username'],
						  'uid'=>$_G['uid'],
						  'oid'=>md5(($bz.$meta['path'])),
						  'img'=>$img,
						  'url'=>$url,
						  'type'=>$type,
						  'ext'=>strtolower($ext),
						  'pfid'=>$pfid,
						  'size'=>$meta['size'],
						  'dateline'=>intval($meta['mtime']),
						  'flag'=>'',
						  'mod'=>$meta['mod']
						  );
					  
			$icoarr['fsize']=formatsize($icoarr['size']);
			$icoarr['ftype']=getFileTypeName($icoarr['type'],$icoarr['ext']);
			if(!$icoarr['dateline']) $icoarr['fdateline']='-';
			else $icoarr['fdateline']=dgmdate($icoarr['dateline']);
			$icosdata=$icoarr;
		}
		return $icosdata;
	}
	//通过icosdata获取folderdata数据
	function getFolderByIcosdata($icosdata){
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
						  'hash'=>$icosdata['hash'],
						  'bz'=>$icosdata['bz'],
						  'gid'=>$icosdata['gid'],
						  'fsperm'=>perm_FolderSPerm::flagPower('external')
						);
			
		}
		return $folder;
	}
	//获得文件内容；
	function getFileContent($path){
		$url=self::getStream($path);
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
		//$zip->setComment("$meta[name] " . date('l jS \of F Y h:i:s A'));
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
				$arr=self::parsePath($path);
				
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
		try {
			// Download the file
			$file=self::getMeta($path);
			if($file['type']=='folder'){
				self::zipdownload($path);
				exit();
			}
			$url=self::getStream($path);
			if(!$fp = @fopen($url, 'rb')) {
				topshowmessage(lang('file_not_exist'));
			}
			$db = DB::object();
			$db->close();
			
			$chunk = 10 * 1024 * 1024; 
			//$file['data'] = self::getFileContent($path);
			//if($file['data']['error']) IO::topshowmessage($file['data']['error']);
			$file['name'] = '"'.(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($file['name']) : $file['name']).'"';
			$d=new FileDownload();
			$d->download($url,$file['name'],$file['size'],$file['dateline'],true);
			exit();
		
		} catch (Exception $e) {
			// The file wasn't found at the specified path/revision
			//echo 'The file was not found at the specified path/revision';
			topshowmessage($e->getMessage());
		}
	}
	

	public function rename($path,$name){
		$arr=self::parsePath($path);
		$name=self::name_filter($name);
		$patharr=explode('/',$arr['path1']);
		array_pop($patharr);
		$path2=implode('/',$patharr).'/'.$name;
		$arr['path2']=diconv($path2,CHARSET,$this->encode);
		if($arr['path1']!=$arr['path2']){
			$oldfile=$this->attachdir.$arr['path'];
			
			$newfile=$this->attachdir.$arr['path2'];
			if(rename($oldfile,$newfile)){
				return self::getMeta($arr['bz'].$path2);
			}else{
				return array('error'=>lang('failure'));
			}
		}
		return self::getMeta($arr['bz'].$arr['path1']);
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
		$file=$this->attachdir.$bzarr['path'];
		if(is_dir($file)){
			$ret=self::removedir($file);
		}else{
			$ret=@unlink($file);
		}
		if($ret){
		return array(
						'icoid'=>md5(($path)),
						'name'=>substr(strrchr($path, '/'), 1),
					  );
		}else{
			return array('error'=>lang('failure'));
		}
	}
	private function removedir($dirname, $keepdir = false) {
		$dirname = str_replace(array( "\n", "\r", '..'), array('', '', ''), $dirname);
	
		if(!is_dir($dirname)) {
			return FALSE;
		}
		$handle = opendir($dirname);
		while(($file = readdir($handle)) !== FALSE) {
			if($file != '.' && $file != '..') {
				$dir = $dirname . DIRECTORY_SEPARATOR . $file;
				$mtime=filemtime($dir);
				is_dir($dir) ? self::removedir($dir) : (((TIMESTAMP-$mtime)>$time)? unlink($dir):'');
			}
		}
		closedir($handle);
		return !$keepdir ? (@rmdir($dirname) ? TRUE : FALSE) : TRUE;
	}
	public function createFolderByPath($path, $pfid='',$noperm = false)
	{
		$datas = array();
		$arr=$this->parsePath($path);
		$patharr=explode('/',trim($arr['path'],'/'));
		$folderarr=array();
		$path = '';
		foreach($patharr as $value){
			$p =$path.DS.$value;
			$path .= $value.DS;
			$folder=$this->attachdir.$p;
			if(dmkdir($folder,0777,false)){
				$meta['path']=$path;
				$meta['name']=$value;
				$meta['type']='folder';
				$meta['size']='-';
				$icoarr=self::_formatMeta($meta,$arr['bz']);
				$folderarr=self::getFolderByIcosdata($icoarr);
				$datas[]= array('folderarr'=>$folderarr,'icoarr'=>$icoarr);
			}

		}
		return $datas;
	}
	//添加目录
	//$fname：目录路径;
	//$container：目标容器
	//$bz：api;
	public function CreateFolder($path,$fname){
		global $_G;
		$fname=self::name_filter($fname);
		$path=$path.'/'.$fname;
		$bzarr=self::parsePath($path);
		$return=array();
		$folder=$this->attachdir.DS.$bzarr['path'];
		if(dmkdir($folder,0777,false)){
			$meta['path']=$bzarr['path1'];
			$meta['name']=$fname;
			$meta['type']='folder';
			$meta['size']='-';
			$icoarr=self::_formatMeta($meta,$bzarr['bz']);
			$folderarr=self::getFolderByIcosdata($icoarr);
			return array('folderarr'=>$folderarr,'icoarr'=>$icoarr);
		}
	}
	//获取不重复的目录名称
	public function getFolderName($name,$path){
		static $i=0;
		if(!$this->icosdatas) $this->icosdatas=self::listFiles($path);
		$names=array();
		foreach($this->icosdatas as $value){
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
		//exit($path.'===='.$filename);
		
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
				if(isset($re['error'])){
					return $re;
				}else{
					if($key==0){
						$data['icoarr'][]=$re['icoarr'];
						$data['folderarr'][]=$re['folderarr'];
					}
				}
				
			}
			//$path.='/'.implode('/',$patharr);
		}
		if($relativePath) $path=$path.'/'.trim($relativePath,'/');
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
				/*if($relativePath){
					$icoarr=self::getMeta($path);
					$data['icoarr'][]=$icoarr;
					$data['folderarr'][]=self::getFolderByIcosdata($icoarr);
				}*/
				$data['icoarr'][] = $re1;
				return $data;
			}else{
				$data['error'] = $re1['error'];
				return $data;
			}
		}
	}
	function upload($file,$path,$filename,$partinfo=array(),$ondup='newcopy'){
		global $_G;
		
		$bzarr=self::parsePath($path.'/'.$filename);
		//获取文件内容
		$fileContent='';
		if(!$handle=fopen($file, 'rb')){
			return array('error'=>'打开文件错误');
		}
		$target=$this->attachdir.DS.$bzarr['path'];
		$dirpath=dirname($target);
		if(!is_dir($dirpath)) dmkdir($dirpath,0777,false);
		while (!feof($handle)) {
		  $fileContent= fread($handle, 8192);
		  file_put_contents($target,$fileContent,FILE_APPEND);
		}
		fclose($handle);
		return self::getMeta($path.'/'.$filename);
	}
	public function upload_by_content($content,$path,$filename){
		global $_G;
		
		$bzarr=self::parsePath($path.'/'.$filename);
		//获取文件内容
		 $file=$this->attachdir.DS.$bzarr['path'];
		 $dirpath=dirname($file);
		 if(!is_dir($dirpath)) dmkdir($dirpath,0777,false);
		 @file_put_contents($file,$content);
		 return self::getMeta($path.'/'.$filename);
	}
	//过滤文件名称
	public function name_filter($name){
		return str_replace(array('/','\\',':','*','?','<','>','|','"',"\n"),'',$name);
	}
}
?>
