<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

if(!defined('FTP_ERR_SERVER_DISABLED')) {
	define('FTP_ERR_SERVER_DISABLED', -100);
	define('FTP_ERR_CONFIG_OFF', -101);
	define('FTP_ERR_CONNECT_TO_SERVER', -102);
	define('FTP_ERR_USER_NO_LOGGIN', -103);
	define('FTP_ERR_CHDIR', -104);
	define('FTP_ERR_MKDIR', -105);
	define('FTP_ERR_SOURCE_READ', -106);
	define('FTP_ERR_TARGET_WRITE', -107);
}
class dzz_ftp
{
	var $enabled = false;
	var $config = array();
	var $func;
	var $connectid;
	var $_error;
	var $systype='';
	function &instance($config = array()) {
		static $object;
		if(empty($object)) {
			$object = new dzz_ftp($config);
		}
		return $object;
	}
	function __construct($config = array()) {
		$this->set_error(0);
		$this->config = !$config ? getglobal('setting/ftp') : $config;
		$this->enabled = false;
		if(empty($this->config['on']) || empty($this->config['host'])) {
			$this->set_error(FTP_ERR_CONFIG_OFF);
		} else {
			$this->func = /*$this->config['ssl'] &&*/ function_exists('ssh2_connect') ? 'ssh2_connect' : 'ftp_connect';
			if($this->func == 'ftp_connect' && !function_exists('ftp_connect')) {
				$this->set_error(FTP_ERR_SERVER_DISABLED);
			} else {
				$this->config['host'] = dzz_ftp::clear($this->config['host']);
				$this->config['port'] = intval($this->config['port']);
				$this->config['ssl'] = intval($this->config['ssl']);
				$this->config['username'] = dzz_ftp::clear($this->config['username']);
				$this->config['password'] = authcode($this->config['password'], 'DECODE', md5(getglobal('config/security/authkey')));
				$this->config['timeout'] = intval($this->config['timeout']);
				$this->config['charset'] = ($this->config['charset']);
				$this->enabled = true;
			}
		}
	}
	function basename($path){
		$arr=explode('/',$path);
		return $arr[count($arr)-1];
	}
	function upload($source, $target,$mode=FTP_BINARY,$startpos=0) {
		if($this->error()) {
			return 0;
		}
		$old_dir = $this->ftp_pwd();
		$dirname = dirname($target);
		$filename =$this->basename($target);
		if(!$this->ftp_chdir($dirname)) {
			if($this->ftp_mkdir($dirname)) {
				$this->ftp_chmod($dirname);
				if(!$this->ftp_chdir($dirname)) {
					$this->set_error(FTP_ERR_CHDIR);
				}
				//$this->ftp_put('index.htm', getglobal('setting/attachdir').'/index.htm', FTP_BINARY);
			} else {
				$this->set_error(FTP_ERR_MKDIR);
			}
		}

		$res = 0;
		if(!$this->error()) {
			if($fp = @fopen($source, 'rb')) {
				
				$res = $this->ftp_fput($filename, $fp, $mode , $startpos);
				@fclose($fp);
				!$res && $this->set_error(FTP_ERR_TARGET_WRITE);
			} else {
				$this->set_error(FTP_ERR_SOURCE_READ);
			}
		}

		$this->ftp_chdir($old_dir);

		return $res ? 1 : 0;
	}
	function connect() {
		if(!$this->enabled || empty($this->config)) {
			return 0;
		} else {
			return $this->ftp_connect(
				$this->config['host'],
				$this->config['username'],
				$this->config['password'],
				$this->config['attachdir'],
				$this->config['port'],
				$this->config['timeout'],
				$this->config['ssl'],
				$this->config['pasv']
				);
		}
	}
	function ftp_connect($ftphost, $username, $password, $ftppath, $ftpport = 21, $timeout = 30, $ftpssl = 0, $ftppasv = 0) {
		$res = 0;
		$fun = $this->func;
		if($this->connectid = $fun($ftphost, $ftpport, 20)) {
			$timeout && $this->set_option(FTP_TIMEOUT_SEC, $timeout);
			if($this->ftp_login($username, $password)) {
				$this->ftp_pasv($ftppasv);
				if($ftppath){
					if($this->ftp_chdir($ftppath)) {
						$res =  $this->connectid;
					} else {
						$this->set_error(FTP_ERR_CHDIR);
					}
				}else{
					$res =  $this->connectid;
				}
			} else {
				$this->set_error(FTP_ERR_USER_NO_LOGGIN);
			}
			
		} else {
			$this->set_error(FTP_ERR_CONNECT_TO_SERVER);
		}
		if($res > 0) {
			$this->set_error();
			$this->enabled = 1;
			$this->systype=$this->ftp_systype();
		} else {
			$this->enabled = 0;
			$this->ftp_close();
		}
		return $res;

	}

	function set_error($code = 0) {
		$this->_error = $code;
	}

	function error() {
		return $this->_error;
	}

	function clear($str) {
		return str_replace(array( "\n", "\r", '..'), '', $str);
	}


	function set_option($cmd, $value) {
		if(function_exists('ftp_set_option')) {
			return @ftp_set_option($this->connectid, $cmd, $value);
		}
	}
	function ftp_mkdir($directory) {
		$directory = dzz_ftp::clear($directory);
		$epath = explode('/', $directory);
		$dir = '';$comma = '';
		foreach($epath as $path) {
			$dir .= $comma.$path;
			$comma = '/';
			$return = @ftp_mkdir($this->connectid, $dir);
			$this->ftp_chmod($dir);
		}
		return $return;
	}

	function ftp_rmdir($directory) {
		$directory = dzz_ftp::clear($directory);
		return @ftp_rmdir($this->connectid, $directory);
	}
	function ftp_rmdir_force($path){
		$path = dzz_ftp::clear($path);
		if(!@ftp_rmdir($this->connectid, $path)){
			//检查子目录
			if($list=self::ftp_list($path,0)){
				foreach($list as $value){
					if($value['type']=='folder'){
						self::ftp_rmdir_force($value['path']);
					}else{
						self::ftp_delete($value['path']);
					}
				}
			}
			return @ftp_rmdir($this->connectid, $path);
		}else{
			return true;
		}
	}

	function ftp_put($remote_file, $local_file, $mode = FTP_BINARY) {
		$remote_file = dzz_ftp::clear($remote_file);
		$local_file = dzz_ftp::clear($local_file);
		$mode = intval($mode);
		return @ftp_put($this->connectid, $remote_file, $local_file, $mode);
	}

	function ftp_fput($remote_file, $sourcefp, $mode = FTP_BINARY,$startpos=0) {
		$remote_file = dzz_ftp::clear($remote_file);
		$mode = intval($mode);
		return @ftp_fput($this->connectid, $remote_file, $sourcefp, $mode,$startpos);
	}

	function ftp_size($remote_file) {
		$remote_file = dzz_ftp::clear($remote_file);
		return @ftp_size($this->connectid, $remote_file);
	}

	function ftp_close() {
		return @ftp_close($this->connectid);
	}
	function ftp_rename($path,$newpath) {
		$path = dzz_ftp::clear($path);
		$newpath = dzz_ftp::clear($newpath);
		return @ftp_rename($this->connectid, $path,$newpath);
	}
	function ftp_delete($path) {
		$path = dzz_ftp::clear($path);
		return @ftp_delete($this->connectid, $path);
	}

	function ftp_get($local_file, $remote_file, $mode, $resumepos = 0) {
		$remote_file = dzz_ftp::clear($remote_file);
		$local_file = dzz_ftp::clear($local_file);
		$mode = intval($mode);
		$resumepos = intval($resumepos);
		return @ftp_get($this->connectid, $local_file, $remote_file, $mode, $resumepos);
	}

	function ftp_login($username, $password) {
		$username = $this->clear($username);
		$password = str_replace(array("\n", "\r"), array('', ''), $password);
		return @ftp_login($this->connectid, $username, $password);
	}

	function ftp_pasv($pasv) {
		return @ftp_pasv($this->connectid, $pasv ? true : false);
	}

	function ftp_chdir($directory) {
		$directory = dzz_ftp::clear($directory);
		return @ftp_chdir($this->connectid, $directory);
	}

	function ftp_site($cmd) {
		$cmd = dzz_ftp::clear($cmd);
		return @ftp_site($this->connectid, $cmd);
	}
	
	function ftp_chmod($filename, $chmod = 0777) {
		//$chmod = octdec ( str_pad ( $chmod, 4, '0', STR_PAD_LEFT ) );
		//$chmod = (int) $chmod;
		
		$filename = dzz_ftp::clear($filename);
		if(function_exists('ftp_chmod')) {
			return @ftp_chmod($this->connectid, $chmod, $filename);
		} else {
			return @ftp_site($this->connectid, 'CHMOD '.$chmod.' '.$filename);
		}
	}
	function ftp_chmod_son($filename,$chmod = 0777){
		//$chmod = octdec ( str_pad ( $chmod, 4, '0', STR_PAD_LEFT ) );
		//$chmod = (int) $chmod;
		
		$filename = dzz_ftp::clear($filename);
		//检查子目录
		if($list=self::ftp_list($filename,0)){
			foreach($list as $value){
				if($value['type']=='folder'){
					self::ftp_chmod_son($value['path'],$chmod);
				}else{
					self::ftp_chmod($value['path'],$chmod);
				}
			}
		}
		return self::ftp_chmod($filename,$chmod);
	}
	function ftp_meta($path){
		$path = dzz_ftp::clear($path);
		$ppath=substr($path,0,strrpos($path,'/'));
		$data=self::ftp_list($ppath,0);
		foreach($data as $value){
			if($value['path']==$path){
				$value['path']=diconv($value['path'],$this->config['charset'],CHARSET);
				$value['name']=diconv($value['name'],$this->config['charset'],CHARSET);
				 return $value;
			}
		}
		return false;
	}
	function ftp_mdtm($cmd) {
		$cmd = dzz_ftp::clear($cmd);
		return @ftp_mdtm($this->connectid, $cmd);
	}
	function ftp_pwd() {
		return @ftp_pwd($this->connectid);
	}
	function ftp_systype(){
		return @ftp_systype($this->connectid);
	}
	function ftp_isdir($dir){ //判断是否为目录
		if(@ftp_chdir($this->connectid,$dir)){ 
			@ftp_cdup($this->connectid); 
			return true; 
		}else{ 
			return false; 
		} 
	} 

	function ftp_list($path,$iconv=1) {
		$path = dzz_ftp::clear($path);
		if(empty($path)) $path=self::ftp_pwd();
		else self::ftp_chdir($path);
		$files = array();
		$rawList = ftp_rawlist($this->connectid, '');
        $data=self::parseRawList($rawList);
		foreach($data as $key => $value){
			$value['path']=$iconv?diconv(preg_replace("/\/+/",'/',$path.'/'.$value['name']),$this->config['charset'],CHARSET):preg_replace("/\/+/",'/',$path.'/'.$value['name']);
			$value['name']=$iconv?diconv($value['name'],$this->config['charset'],CHARSET):$value['name'];
			$data[$key]=$value;
		}
		return $data;
	}
	function byteconvert($bytes) {
		$symbol = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		$exp = floor( log($bytes) / log(1024) );
		return sprintf( '%.2f ' . $symbol[ $exp ], ($bytes / pow(1024, floor($exp))) );
	}
	
	function chmodnum($chmod) {
		$trans = array('-' => '0', 'r' => '4', 'w' => '2', 'x' => '1', 't' => '1', 's' => '1');
		$chmod = substr(strtr($chmod, $trans), 1);
		$array = str_split($chmod, 3);
		return array_sum(str_split($array[0])) . array_sum(str_split($array[1])) . array_sum(str_split($array[2]));
	}
	
	function parseRawList($rawList){ 
        $data=array();
        foreach($rawList as $key=>$value)  
        { 
			$temp=array();
            $parser = null; 
			if(preg_match("/Window/i",$this->systype)){
				$parser = explode(" ", preg_replace('!\s+!', ' ', $value)); 
				 if(isset($parser)){
					list($month,$day,$year)=explode('-',$parser[0]);
					$temp['mtime']=strtotime($year.'-'.$month.'-'.$day.' '.$parser[1]);
					$temp['type']=preg_match("/<DIR>/i",$parser[2])?'folder':'file';
					
					if($temp['type']=='folder'){
						$temp['size']=0;
						$temp['name']=substr($value,strrpos($value,$parser[3]));
					}else{
						$temp['size']=$parser[2];
						$temp['name']=substr($value,strrpos($value,$parser[3]));
					} 
					$temp['mod']=0;
					$data[] = $temp; 
				} 
			}else{
				
				$parser = explode(" ", preg_replace('!\s+!', ' ', $value)); 
				//echo $value;
				//print_r($parser);
				 if(isset($parser)){ 
				    
				 	$temp['mod']=self::chmodnum($parser[0]);
					$temp['mtime']=strtotime($parser[5].' '.$parser[6].' '.$parser[7]);
					$temp['type']=(substr($parser[0], 0, 1)=='d')?'folder':'file';
					$temp['size']=$parser[4];
					$temp['name']=substr($value,strrpos($value,$parser[8]));
					if($temp['name']!='.' && $temp['name']!='..'){
					// print_r($temp);
						$data[] = $temp; 
					}
				} 
			}
        } 
        return $data;
    } 
	
}
?>