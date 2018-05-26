<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

if(!defined('SFTP_ERR_SERVER_DISABLED')) {
	define('SFTP_ERR_SERVER_DISABLED', -100);
	define('SFTP_ERR_CONFIG_OFF', -101);
	define('SFTP_ERR_CONNECT_TO_SERVER', -102);
	define('SFTP_ERR_USER_NO_LOGGIN', -103);
	define('SFTP_ERR_CHDIR', -104);
	define('SFTP_ERR_MKDIR', -105);
	define('SFTP_ERR_SOURCE_READ', -106);
	define('SFTP_ERR_TARGET_WRITE', -107);
}
class dzz_sftp
{
	var $enabled = false;
	var $config = array();
	var $func;
	var $connectid;
	var $_error;
	var $systype='';
	var $root='';
	var $sftp=NULL;
	function &instance($config = array()) {
		static $object;
		if(empty($object)) {
			$object = new dzz_sftp($config);
		}
		return $object;
	}
	function __construct($config = array()) {
		$this->set_error(0);
		$this->config = !$config ? getglobal('setting/ftp') : $config;
		$this->enabled = false;
		if(empty($this->config['on']) || empty($this->config['host'])) {
			$this->set_error(SFTP_ERR_CONFIG_OFF);
		} else {
			$this->func = 'ssh2_connect';
			if(!function_exists('ssh2_connect')) {
				$this->set_error(SFTP_ERR_SERVER_DISABLED);
			} else {
				$this->config['host'] = $this->clear($this->config['host']);
				$this->config['port'] = intval($this->config['port']);
				//$this->config['ssl'] = intval($this->config['ssl']);
				$this->config['username'] = $this->clear($this->config['username']);
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
    function upload($source, $target, $mode = 0777)
    {
        if ($this->error()) {
            return 0;
        }

        $res = 0;
        if (!$this->error()) {
            $target = $this->clear($target);
            $source = $this->clear($source);
            if (!ssh2_scp_send($this->connectid, $source, $target, $mode)) {
                $sftp = ssh2_sftp($this->connectid);
                $tdir = dirname($target);
                $this->ftp_mkdir($tdir);
                $sftpStream = fopen('ssh2.sftp://' . $sftp . $target, 'w');
                try {

                    if (!$sftpStream) {
                        //throw new Exception("Could not open remote file: $source");
                        $this->set_error("Could not open remote file: $target");
                        return 0;
                    }

                    $data_to_send = file_get_contents($source);

                    if ($data_to_send === false) {
                        // throw new Exception("Could not open local file: $source.");
                        $this->set_error("Could not open local file: $source.");
                        return 0;
                    }

                    if (fwrite($sftpStream, $data_to_send) === false) {
                        //throw new Exception("Could not send data from file: $source.");
                        $this->set_error("Could not send data from file: $source.");
                        return 0;
                    }

                    fclose($sftpStream);
                    return 1;

                } catch (Exception $e) {
                    //error_log('Exception: ' . $e->getMessage());
                    $this->set_error('Exception: ' . $e->getMessage());
                    fclose($sftpStream);
                }
            }
            ssh2_exec($this->connectid, 'exit');
            return 1;
        }

        return 0;
    }

    function connect() {
		if(!$this->enabled || empty($this->config)) {
			return 0;
		} else {
			return $this->sftp_connect(
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
	function sftp_connect($ftphost, $username, $password, $ftppath, $ftpport = 22, $timeout = 30, $ftpssl = 0, $ftppasv = 0) {
		$res = 0;
		$fun = $this->func;
		
		if($this->connectid = $fun($ftphost, $ftpport)) {
			
			if($this->ftp_login($username, $password)) {
				$res =  $this->connectid;
			} else {
				$this->set_error(FTP_ERR_USER_NO_LOGGIN);
			}
		} else {
			$this->set_error(SFTP_ERR_CONNECT_TO_SERVER);
		}
		if($res) {
			$this->set_error();
			$this->enabled = 1;
			return $this->sftp=ssh2_sftp($res);
			//$this->systype=$this->SFTP_systype();
		} else {
			$this->enabled = 0;
			$this->ftp_close();
		}
		return 0;

	}

	function set_error($code = 0) {
		$this->_error = $code;
	}

	function error() {
		return $this->_error;
	}

	function clear($str) {
		return str_replace(array('/./','//'),'/',str_replace(array( "\n", "\r", '..'), '', $str));
	}


	/*function set_option($cmd, $value) {
		if(function_exists('SFTP_set_option')) {
			return @SFTP_set_option($this->connectid, $cmd, $value);
		}
	}*/

	function ftp_mkdir($directory) {
		$directory = $this->clear($directory);
		//return  @ssh2_sftp_mkdir($this->connectid, $dir,true);
		$epath = explode('/', $directory);
		$dir = '';$comma = '';
		foreach($epath as $path) {
			$dir .= $comma.$path;
			$comma = '/';
			$return = @ssh2_sftp_mkdir($this->sftp, $dir);
			$this->ftp_chmod($dir);
		}
		return $return;
	}

	function ftp_rmdir($directory) {
		$directory = $this->clear($directory);
		return @ssh2_sftp_rmdir($this->sftp, $directory);
	}
	function ftp_rmdir_force($path){
		$path = $this->clear($path);
		if(!ssh2_sftp_rmdir($this->sftp, $path)){
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
			return ssh2_sftp_rmdir($this->sftp, $path);
		}else{
			return true;
		}
	}

	function ftp_put($remote_file, $local_file , $mode = 0777) {
		
		$remote_file = $this->clear($remote_file);
		$local_file = $this->clear($local_file);
		//$mode = intval($mode);
		//echo $this->connectid.'===='.$remote_file.'===='.$local_file;
		return ssh2_scp_send($this->connectid, $local_file, $remote_file);
	}

	/*function sftp_fput($remote_file, $sourcefp, $mode = 0644,$startpos=0) {
		$remote_file = $this->clear($remote_file);
		$mode = intval($mode);
		return @SFTP_fput($this->connectid, $remote_file, $sourcefp, $mode,$startpos);
	}*/

	function ftp_size($remote_file) {
		$remote_file = $this->clear($remote_file);
		$statinfo = ssh2_sftp_stat($this->sftp, $remote_file);
		return $statinfo['size'];
	}

	function ftp_close() {
		@ssh2_exec($this->connection,'echo "EXITING" && exit;'); 
        $this->connection = NULL; 
		return true;
	}
	function ftp_rename($path,$newpath) {
		$path = $this->clear($path);
		$newpath = $this->clear($newpath);
		return @ssh2_sftp_rename($this->sftp, $path,$newpath);
	}
	function ftp_delete($path) {
		$path = $this->clear($path);
		return ssh2_sftp_unlink($this->sftp, $path);
	}

	function sftp_get($local_file, $remote_file) {
		$remote_file = $this->clear($remote_file);
		$local_file = $this->clear($local_file);
		return @ssh2_scp_recv($this->connectid, $remote_file, $local_file);
	}

	function ftp_login($username, $password) {
		//$username = $this->clear($username);
		//$password = str_replace(array("\n", "\r"), array('', ''), $password);
		if(@ssh2_auth_password($this->connectid,$username, $password)) {
			return true;
		} else {
			$this->set_error(SFTP_ERR_USER_NO_LOGGIN);
			return false;
		}
	}
	/*
	function SFTP_pasv($pasv) {
		return @SFTP_pasv($this->connectid, $pasv ? true : false);
	}*/

	/*function SFTP_chdir($directory) {
		$directory = $this->clear($directory);
		return @SFTP_chdir($this->connectid, $directory);
	}*/

	/*function SFTP_site($cmd) {
		$cmd = $this->clear($cmd);
		return @SFTP_site($this->connectid, $cmd);
	}*/
	
	function ftp_chmod($filename, $chmod = 0777) {
		//$chmod = octdec ( str_pad ( $chmod, 4, '0', STR_PAD_LEFT ) );
		//$chmod = (int) $chmod;
		
		$filename = $this->clear($filename);
		if(function_exists('ssh2_sftp_chmod')) {
			return @ssh2_sftp_chmod($this->sftp, $chmod, $filename);
		} else {
			return @ssh2_exec($this->connectid, 'CHMOD '.$chmod.' '.$filename);
		}
	}
	function ftp_chmod_son($filename,$chmod = 0777){
		//$chmod = octdec ( str_pad ( $chmod, 4, '0', STR_PAD_LEFT ) );
		//$chmod = (int) $chmod;
		
		$filename = $this->clear($filename);
		//检查子目录
		if($list=self::ftp_list($filename,0)){
			foreach($list as $value){
				if($value['type']=='folder'){
					self::ftp_chmod_son($value['path'],$chmod);
				}else{
					self::sftp_chmod($value['path'],$chmod);
				}
			}
		}
		return self::ftp_chmod($filename,$chmod);
	}
	function ftp_meta($path){
		$path = $this->clear($path);
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
	function ftp_mdtm($remote_file) {
		$cmd = $this->clear($remote_file);
		$statinfo = ssh2_sftp_stat($this->sftp, $remote_file);
		return $statinfo['mtime'];
	}
	/*function SFTP_pwd() {
		return @SFTP_pwd($this->connectid);
	}*/
	/*function SFTP_systype(){
		return @SFTP_systype($this->connectid);
	}*/
	function ftp_isdir($dir){ //判断是否为目录
		if($meta=self::ftp_meta($dir)){
			if($meta['type']=='folder') return true;
		}
	} 

	function ftp_list($path,$iconv=0) {
		$path = $this->clear($path);
		if(empty($path)) $path='/';
	
		$stdout_stream= ssh2_exec($this->connectid, '/usr/bin/ls -l '.$path);
		stream_set_blocking($stdout_stream,true);
		$rawList=array();
		while($line = fgets($stdout_stream)) {
			$rawList[]=$line;
		}
		fclose($stdout_stream);
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
			/*if(preg_match("/Window/i",$this->systype)){
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
			}else{*/
				
				$parser = explode(" ", preg_replace('!\s+!', ' ', $value)); 
				//echo $value;
				
				 if(count($parser)>8){ 
				    
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
			//}
        } 
        return $data;
    } 
	
}
?>