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



class dzz_io
{
	protected function initIO($path){
		$bzarr=explode(':',$path);
		$allowbz=C::t('connect')->fetch_all_bz();//array('baiduPCS','ALIOSS','dzz','JSS');
		
		if(strpos($path,'dzz::')!==false){
			$classname= 'io_dzz';
		}elseif(strpos($path,'attach::')!==false){
			$classname= 'io_dzz';
		}elseif(is_numeric($bzarr[0])){
			$classname= 'io_dzz';
		}elseif(in_array($bzarr[0],$allowbz)){
			$classname= 'io_'.$bzarr[0];
		}else{
			return false;
		}
		return new $classname($path);
	}
	function MoveToSpace($path,$attach,$ondup='overwrite'){
		if($io=self::initIO($path)){
			return $io->MoveToSpace($path,$attach,$ondup);
		}else{
			return false;
		}
	}
	function authorize($bz,$refer=''){
		if($io=self::initIO($bz)){
			$io->authorize($refer);
		}
	}
	function getQuota($bz){
		if($io=self::initIO($bz)){
			return $io->getQuota($bz);
		}else{
			return false;
		}
	}
	function chmod($path,$chmod,$son=0){
		if($io=self::initIO($path)){
			return $io->chmod($path,$chmod,$son);
		}else{
			return false;
		}
	}
	function parsePath($path){
		if($io=self::initIO($path)){
			return $io->parsePath($path);
		}else{
			return false;
		}
	}
	//获取缩略图
	function getThumb($path,$width,$height,$original,$returnurl=false){
		if($io=self::initIO($path)) return $io->getThumb($path,$width,$height,$original,$returnurl);
	}
	/*
	 *通过icosdata获取folderdata数据	
	*/
	function getFolderByIcosdata($icosdata){ 
		if($io=self::initIO($icosdata['path']))	return $io->getFolderByIcosdata($icosdata);
		else return false;
	}
	//获取icosdata数组；
	//$path: 路径
	//$force==1时不使用api的缓存数据，强制重新获取api数据；
	//$bz==''是表示获取的是本地；此时path为icoid；
	function getMeta($path,$force=0){ 
		if($io=self::initIO($path))	return $io->getMeta($path,$force);
		else return false;
	}
	//重命名文件
	function rename($path,$newname){ 
		if($io=self::initIO($path))	return $io->rename($path,$newname);
		else return false;
	}
	
	
	//根据路径获取目录树的数据；
	function getFolderDatasByPath($path){ 
		if($io=self::initIO($path))	return $io->getFolderDatasByPath($path);
		else return false;
	}
	
	
	//获取文件流；
	//$path: 路径
	public function getStream($path,$fop){ 
		$io=self::initIO($path);
		if($io)	return $io->getStream($path,$fop);
		else return $path;
	}
	//获取文件地址；
	//$path: 路径
	function getFileUri($path,$fop){ 
	
		if($io=self::initIO($path))	return $io->getFileUri($path,$fop);
		else return $path;
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
	function listFiles($path,$by='time',$order='DESC',$limit='',$force=0){  
		if($io=self::initIO($path))	return $io->listFiles($path,$by,$order,$limit,$force);
		else return false;
	}
	
	//目标位置新建内容
	//$path:原路径
	//$container:目标位置；
	//$tbz:目标api；
	//返回：
	//$icosdata数组；
	function CopyTo($opath,$path,$iscopy=0){
	
		if($io=self::initIO($opath)) return $io->CopyTo($opath,$path,$iscopy);
		else return false;
	}

	/**
	 * 删除指定的元素
	 * @param string $opath 被移动的文件路径
	 * @param string $path 目标位置（可能是同一api内或跨api，这两种情况分开处理）
	 * @return icosdatas
	 */
	public function DeleteByData($data){
		$havesubitem=0;
		if(isset($data['contents'])){
			foreach($data['contents'] as $key => $value){
				/*print_r($value);
				echo '=====<br>';*/
				$return=self::DeleteByData($value);
				if(intval($return['delete'])<1) {
					$havesubitem=1;
				}
				$data['contents'][$key]=$return;
			}
		}
		if($data['success']===true && !$havesubitem){
			
			if($data['icoid']==$data['newdata']['icoid']){
				$data['newdata']['move']=1;
			}else{
				$arr=IO::Delete($data['path'],true);
				if($arr['icoid']) $data['delete']=1;
				else $data['success']=$arr['error'];
			}
		}
		return $data;
	}
	
	//添加目录
	//$fname：目录名称;
	//$path：目录位置路径，如果是本地，$path 为pfid
	function CreateFolder($path,$fname,$perm=0,$ondup='newcopy'){
		if($io=self::initIO($path))	return $io->CreateFolder($path,$fname,$perm,$ondup);
		else return false;
	}
	
	/*将文件缓存到本地,并且返回本地的访问地址*/
	function cacheFile($data){
		global $_G;
		$subdir = $subdir1 = $subdir2 = '';
		$subdir1 = date('Ym');
		$subdir2 = date('d');
		$subdir = $subdir1.'/'.$subdir2.'/';
		$target1='dzzcache/'.$subdir.'index.html';
		$target='dzzcache/'.$subdir.random(10);
		$target_attach=$_G['setting']['attachdir'].$target1;
		$targetpath = dirname($target_attach);
		dmkdir($targetpath);
		if(file_put_contents($target,$data)){
			return $target;
		}else{
			return false;
		}
	}
	
	//获取文件数据
	//$data：文件的信息数组 
	//返回我文件data；
	function getFileContent($path){
		if($io=self::initIO($path))	return $io->getFileContent($path);
		else return false;
	}
	//覆盖文件内容
	//$data：文件的信息数组 
	//返回我文件data；
	function setFileContent($path,$data,$force=false){
		if($io=self::initIO($path))	return $io->setFileContent($path,$data,$force);
		else return false;
	}
	
	//分片上传文件；
	//$path: 路径
	function multiUpload($file,$path,$filename,$attach=array(),$ondup="newcopy"){ 
		if($io=self::initIO($path))	return $io->multiUpload($file,$path,$filename,$attach,$ondup);
		else return false;
	}
	
	//添加文件
	//$fileContent：源文件数据;
	//$container：目标位置;
	//$bz：api;
	function upload($fileContent,$path,$filename){
		if($io=self::initIO($path))		return $io->upload($fileContent,$path,$filename);
		else return false;
	}
	
	function upload_by_content($fileContent,$path,$filename){
		if($io=self::initIO($path))		return $io->upload_by_content($fileContent,$path,$filename);
		else return false;
	}
	
	public function uploadStream($file,$name,$path,$relativePath,$content_range){
	  $path=urldecode($path);
	  $relative=urldecode($relative);
	 if( $io=self::initIO($path))  return $io->uploadStream($file,$name,$path,$relativePath,$content_range);
	 else return false;
  }
	
	function Delete($path,$force=false){
		if($io=self::initIO($path))	{
				return $io->Delete($path,$force);
		}
		else return false;
	}
	
 //获取不重复的目录名称
  public function getFolderName($fname,$path){
	  if($io=self::initIO($path))	  return $io->getFolderName($fname,$path);
	  else return false;
  }
  
 
  public function download($path,$filename=''){
		  $path=urldecode($path);
		 if($io=self::initIO($path))  $io->download($path,$filename); 
		 else return false;
		 
	 }
	 
	public function getCloud($bz){
		$bzarr=explode(':',$bz);
		$cloud=DB::fetch_first("select * from ".DB::table('connect')." where bz='{$bzarr[0]}'");
		if($cloud['type']=='pan'){
			$root=DB::fetch_first("select * from ".DB::table($cloud['dname'])." where  id='{$bzarr[1]}'");
			if(!$root['cloudname']) $root['cloudname']=$cloud['name'].':'.($root['cusername']?$root['cusername']:$root['cuid']);
		}elseif($cloud['type']=='storage'){
			$root=DB::fetch_first("select * from ".DB::table($cloud['dname'])." where id='{$bzarr[1]}'");
			$root['access_id']=authcode($root['access_id'],'DECODE',$root['bz']);
			if(!$root['cloudname']) $root['cloudname']=$cloud['name'].':'.($root['bucket']?$root['bucket']:cutstr($root['access_id'], 4, $dot = ''));
		}elseif($cloud['type']=='ftp'){
			$root=DB::fetch_first("select * from ".DB::table($cloud['dname'])." where id='{$bzarr[1]}'");
		}
		$root['cloudtype']=$cloud['type'];
		return $root;
	}
	
}
?>
