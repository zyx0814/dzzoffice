<?php

class Wopi
{
    static function CheckFileInfo($path,$lock)
    {
		global $_G;
        $meta=IO::getMeta($path);
		$editperm=perm_check::checkperm('edit',$meta);
		$code=self::checkLock($path,$lock);
		if(intval($code)==409) $editperm=0;//文件被锁定，不能编辑
		$FileInfoDto = array(
			'BaseFileName' => $meta['name'],
			'OwnerId' => $meta['uid'].'_'.TIMESTAMP,
			'ReadOnly' => $editperm?false:true,
			'SupportsCoauth'=>true,//，表示WOPI服务器支持多个用户同时对文件进行修改
			
			'UserFriendlyName'=>getglobal('username'),//是用户的名称，如果被锁定，WOPI客户端在某些场景可能会配置一个替代的字符串，或者展示没有名称
			'UserId'=>getglobal('uid'),//用于WOPI服务器唯一标识用户。
			'UserCanWrite'=>$editperm?true:false,//表示用户有权限改变文件
			
			'UserCanAttend'=>'true',//表示用户有权限查看这个文件的广播。广播是一个文件的活动，涉及控制一组参加者的文件的视图的一个或多个呈现者。比如一个传播者能够通过广播将幻灯片广播给多个接受者。
			'UserCanPresent'=>true,//表示用户有权限广播这个文件给那些有权限浏览文件的人。广播是一个文件的活动，涉及控制一组参加者的文件的视图的一个或多个呈现者。比如一个传播者能够通过广播将幻灯片广播给多个接受者。
			
			'SupportsCobalt'=>true,//表示WOPI服务器支持ExecuteCellStorageRequest 和ExcecuteCellStorageRelativeRequest 的操作
			
			'SHA256' => base64_encode(hash_file('sha256', IO::getStream($path), true)),
			'Size' => $meta['size'],//filesize($_SERVER['DOCUMENT_ROOT'] . '/' . $fileName),
			'Version' => $meta['md5'],//代表基于WOPI服务器的版本模式，文件的当前版本。当文件改变时，这个值一定要改变，同时对于一个给定的文件，版本的值应该从不重复。
			
		);
		//判断是否支持文件锁
		if($editperm && in_array( $meta['ext'],array('docm','docx','odt'))){
			$FileInfoDto['SupportsLocks']=true;//表示WOPI服务器支持对于文件Lock 、Unlock 、RefreshLock 和UnlockAndRelock 操作
			$FileInfoDto['SupportsGetLock']=true;//表示WOPI服务器提供了GetLock 
			
		}else{
			$FileInfoDto['SupportsLocks']=false;
		}
		//判断是否支持文件更新
		if($editperm && in_array( $meta['ext'],array('docm','docx','odt','dotx','ods','xlsb','xlsm','xlsx','odp','ppsx','pptx','odp','pptx'))){
			$FileInfoDto['SupportsUpdate']=true;//表示WOPI服务器支持对于文件的PutFile 和PutRelativeFile 操作
		}

		$jsonString = json_encode($FileInfoDto);
		header('Content-Type: application/json');
		echo $jsonString;
        
       
    }
	
    static function GetFile($path)
    {
        $filepath=IO::getStream($path);
		$meta=IO::getMeta($path);
		if(!$filesize=filesize($filepath)) $filesize=$meta['size'];
		$chunk = 10 * 1024 * 1024; 
		if(!$fp = @fopen($filepath, 'rb')) {
			exit();
		}
		dheader('Content-Disposition: attachment; filename='.$meta['name']);
		dheader('Content-Type: application/octet-stream');
		dheader('Content-Length: '.$filesize);
		@ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
		while (!feof($fp)) { 
			echo fread($fp, $chunk);
			@ob_flush();  // flush output
			@flush();
		}
		fclose($fp);
		exit();
      
    }
	 static function PutFile($path,$lock='')
    {
		$code=self::checkLock($path,$lock);
		if(intval($code)==409){
			$arr=explode('|',$code);
			header("HTTP/1.1 409 Conflict",409);
			header('X-WOPI-LOCK: '.$arr[1]);
		}else{
			$content=file_get_contents("php://input");
			IO::setFileContent($path,$content,true);
		}
		return $code;
    }
	static function Lock($path,$lock='',$oldlock='')
	{
	
		$code=self::setLock($path,$lock,$oldlock);
		if(intval($code)==409){
			$arr=explode('|',$code);
			header("HTTP/1.1 409 Conflict",409);
			header('X-WOPI-LOCK: '.$arr[1]);
		}
		return $code;
	}
	static function unLock($path,$lock='')
	{
		$code=self::checkLock($path,$lock);
		if(intval($code)==409){
			$arr=explode('|',$code);
			header("HTTP/1.1 409 Conflict",409);
			header('X-WOPI-LOCK: '.$arr[1]);
		}else{
			self::delLock($path);
		}
		return $code;
	}
	static function setLock($path,$lock='',$oldlock='')
	{
		$rid=md5($path);
		$uid=getglobal('uid');
		$code=self::checkLock($path,$lock,$oldlock);
		if($code==200){	
			$filepath=getglobal('setting/attachdir').'/cache/'.$rid.'.lock';
			if(!file_put_contents($filepath,$lock)){
				$code=500;
			}
		}
		return $code;
	}
	static function getLock($path)
	{
		$lock=self::getLockStr($path);
		header('X-WOPI-LOCK: '.$lock);
		return $lock;
	}
	
	static function checkLock($path,$lock='',$oldlock=''){
		$code=200;
		if(empty($lock)){
			$code=400;
		}elseif($lock1=self::getLockStr($path)){
			if($lockarr1=json_decode($lock1,true)){
				$lockarr=json_decode(($oldlock?$oldlock:$lock),true);
				if($lockarr['S']!=$lockarr1['S']){
					$code='409'.'|'.$lock1;
				}
			}elseif($lock1!=$lock){
				$code='409'.'|'.$lock1;
			}
		}
		return $code;
	}
	static function getLockStr($path,$decode=false)
	{
		$rid=md5($path);
		$filepath=getglobal('setting/attachdir').'/cache/'.$rid.'.lock';
		$mtime=filemtime($filepath);
		if($mtime && TIMESTAMP-$mtime>30*60) return '';//超过30分钟自动失效
		if($json=file_get_contents($filepath)){
			return $decode?json_decode($json):$json;
		}
		return '';
	}
	static function delLock($path)
	{
		$rid=md5($path);
		$filepath=getglobal('setting/attachdir').'./cache/'.$rid.'.lock';
		if(@unlink($filepath)) return true;
		return false;
	}
	
	/*
		$lock        : 文件锁内容，后续通过内容来标识是否有解锁权限；
		$ooServerURL : 文档服务器地址；如http://oos.dzz.com
		$path        : 文件路径;
	*/
	static function GenerateFileLink($path,$ooServerURL,$lock='',$internalUrl='',$action='')
	{
		$code=200;
		$meta=IO::getMeta($path);
		if(empty($internalUrl)) $internalUrl=getglobal('siteurl');
		if($meta['error']) return $meta;
		$editperm=perm_check::checkperm('edit',$meta);
		$code=self::checkLock($path,$lock);
		if(intval($code)==409) $editperm=0;//文件被锁定，不能编辑
		$ooServerURL=rtrim($ooServerURL,'/').'/hosting/discovery';
		
		$fileExtension = $meta['ext'];
		$guid = dzzencode(getglobal('uid').'|'.$lock);
		$wopi_url_temlpate = "WOPISrc={0}&access_token={1}";
		$fileID=dzzencode($meta['path']);
	    $discovery=self::getActionByDiscovery($ooServerURL);
		if($discovery['error']) return  $discovery;
		
		if(!in_array($fileExtension,$discovery['exts'])){
			return array('error'=>'filetype error');
		}
		
		if(empty($action) || empty($discovery['actions'][$action])){
			
			if($editperm){
				if(in_array($fileExtension,array_keys($discovery['actions']['edit']))){
					$action='edit';
				/*}elseif(in_array($fileExtension,array_keys($discovery['actions']['convert']))){
					$action='convert';*/
				}elseif(in_array($fileExtension,array_keys($discovery['actions']['view']))){
					$action='view';
				}
			}else{
				if(in_array($fileExtension,array_keys($discovery['actions']['view']))){
					$action='view';
				}elseif(in_array($fileExtension,array_keys($discovery['actions']['edit']))){
					$action='edit';
				}
			}
			if( defined('IN_MOBILE') && in_array($fileExtension,array_keys($discovery['actions']['mobileView']))){
				$action='mobileView';
			}
		}
		$urlsrc=$discovery['actions'][$action][$fileExtension];
		
		$parts = parse_url($ooServerURL);
		if (strtolower($parts['scheme'])=='https') {
		
			$webSocketProtocol = "wss://";
		} else {
		
			$webSocketProtocol = "ws://";
		}
		$protocol=$_SERVER['SERVER_PROTOCOL'];
		$parts = parse_url($ooServerURL);
		if (strtolower($parts['scheme'])=='https') {
			$protocol = "https";
			$webSocketProtocol = "wss://";
		} else {
			$protocol = "http";
			$webSocketProtocol = "ws://";
		}
		
		$webSocket = sprintf("%s%s%s",$webSocketProtocol,$parts['host'],isset($parts['port']) ? ":" . $parts['port'] : "");
																		 
		$fileUrl = urlencode($internalUrl. "wopi/files/" . $fileID);
		$requestUrl = preg_replace("/<.*>/", "", $urlsrc);
		$requestUrl = $requestUrl . str_replace('{1}', $guid, $wopi_url_temlpate);
		$requestUrl = str_replace("{0}", $fileUrl, $requestUrl).'&ui=zh-CN&rs=zh-CN'; 
		$wopiSrc=$internalUrl. "wopi/files/$fileID?access_token=$guid&ui=zh-CN&rs=zh-CN";
		$ret=array(
			'fileID'=>$fileID,
			'protocol'=>$protocol,
			'wopiSrc'=>$wopiSrc,
			'urlsrc'=>$urlsrc,
			'webSocket'=>$webSocket,
			'fullsrc'=>$requestUrl,
			'access_token'=>$guid,
			'action'=>$action,
			'lockstatus'=>$code,//检测锁状态
			//'discovery'=>$discovery
		);
		return $ret; //LINK SHOW TEST
	}
	private function getActionByDiscovery($oosDiscoveryUrl){
		$cachefile=getglobal('setting/attachdir').'./cache/'.md5($oosDiscoveryUrl).'.cache';
		if(file_exists($cachefile)){
			$sourceXml=file_get_contents($cachefile);
		}
		if(!$sourceXml){
			$arrContextOptions = array(
				"ssl" => array(
					"verify_peer" => false,
					"verify_peer_name" => false,
				),
			);
			if(!$sourceXml = file_get_contents($oosDiscoveryUrl, false, stream_context_create($arrContextOptions))) {
				$error = error_get_last();
				return array('error'=>"HTTP request failed. Error was: " . $error['message']);
			}
			str_replace('"', "'", $sourceXml);
			@file_put_contents($cachefile,$sourceXml);
		}
		
		$xml = simplexml_load_string($sourceXml);
		$elements = $xml->xpath("net-zone/app/action");
		$actions=$exts=array();
		foreach($elements as  $value){
			$temparr=array(
				'ext'=>(string)$value['ext'],
				'action'=>(string)$value['name'],
				'urlsrc'=>(string)$value['urlsrc']
			);
			if(empty($temparr['ext'])) continue;
			//if(isset($actions[$temparr['action']][$temparr['ext']])) continue;
			$exts[$temparr['ext']]=$temparr['ext'];
			$actions[$temparr['action']][$temparr['ext']]=$temparr['urlsrc'];
		}
		$ret= array(
					'exts'=>array_keys($exts),
					'actions'=>$actions
				   );
		return $ret;
	}
}
