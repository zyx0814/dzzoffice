<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      qchlian 3580164@qq.com
 */
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class dzz_upgrade_app {
	public  $upgradeurl = "";
	public  $checknextversionurl= "";
	public  $locale = 'SC';
	public  $charset = 'UTF-8';
	public function __construct(){ 
		$this->upgradeurl = APP_CHECK_URL."data/yunapp/"; 
		$this->checknextversionurl= APP_CHECK_URL; 
	}
	//检测应用是否有更新
	public function check_upgrade() {
		/*include_once libfile('class/xml');
		include_once libfile('function/cache');

		$return = false;
		$upgradefile = $this->upgradeurl.$appinfo["identifier"]."/".$appinfo["version"].'/upgrade.xml';
		$response = xml2array(dfsockopen($upgradefile));
		return $response;*/
		
		include_once libfile('function/cache');
		include_once DZZ_ROOT . './core/core_version.php';
		$map=array();
		$today =dgmdate(TIMESTAMP,'Ymd');
		$map["available"]=1;
		$map["check_upgrade_time"]=array("lt",$today); 
		$applist = DB::fetch_all("select * from %t where `available`>0 and check_upgrade_time<%d ",array('app_market',$today));//C::tp_t('app_market')->where($map)->limit(10)->select(); 
		if( $applist ){
			//根据当前版本查询是否需要更新 
			$appinfo["mysqlversion"] = helper_dbtool::dbversion();
			$appinfo["phpversion"] = PHP_VERSION ;
			$appinfo["dzzversion"] = CORE_VERSION;
			foreach($applist as $k=>$v ){
				if(empty($v['app_path'])) $v['app_path']='dzz';
				$savedata=array();
				if( $v["mid"]>0){//云端检测
					$info=array_merge($v,$appinfo);
					$response = $this->check_upgrade_byversion( $info );
					if( $response  ) {
						if( $response["status"]==1 ){
							$savedata=array( "upgrade_version"=>serialize($response["data"]), "check_upgrade_time"=>$today ); 
						}else{
							if( $response["status"]!=2 ){//云端应用未有新版本发布或找不到版本
								$savedata=array( "upgrade_version"=>"", "check_upgrade_time"=>$today );
							}else{//云端应用不存在
								$savedata=array( "upgrade_version"=>"", "check_upgrade_time"=>$today );
							}
						}
					}
				}else{//本地检测
					$file = DZZ_ROOT . './'.$v['app_path'].'/' . $v['identifier'] . '/dzz_app_' . $v['identifier'] . '.xml'; 
					if ( file_exists($file) ) {
						$importtxt = @implode('', file($file));
						$apparray = getimportdata('Dzz! app',0,0,$importtxt);
						if($apparray["app"]["version"]>$v["version"]){
							unset( $apparray["app"]['appico']);//ico base64太长暂时屏蔽应用icon更新
							$savedata=array( "upgrade_version"=>serialize($apparray["app"]), "check_upgrade_time"=>$today );
							 
						} else{
							$savedata=array( "upgrade_version"=>"", "check_upgrade_time"=>$today );
						}
					}else{
						$savedata=array( "upgrade_version"=>"", "check_upgrade_time"=>$today );
					}
				} 
				if( $savedata ){
					$re= C::t('app_market')->update($appid,$savedata);//C::tp_t('app_market')->where("appid=".$v["appid"])->save( $savedata ); 
				} 
			}
		}
		$map=array(); 
		$map["available"]=1;
		$map["upgrade_version"]=array("neq",""); 
		$need_upgrade_num = DB::result_first("select COUNT(*) from %t where `available`>0 and upgrade_version!=''",array('app_market'));// C::tp_t('app_market')->where($map)->count();
	 
		if( $need_upgrade_num>0 ) {
			C::t('setting')->update('upgrade_app_num', $need_upgrade_num);
			$return = true;
		} else {
			C::t('setting')->update('upgrade_app_num', '');
			$return = false;
		} 
		updatecache('setting');
		//echo $need_upgrade_num;exit;
		//$this->upgradeinformation();
		return $return; 
	}
	
	public function check_upgrade_byversion( $appinfo ){ 
		$url=$this->checknextversionurl."market/app/nextversion";//."index.php?mod=dzzmarket&op=index_ajax&operation=nextversion";
		$post_data = array(
			"mid"=>$appinfo['mid'],
			"version"=>$appinfo['version'],
			"dzzversion"=>$appinfo['dzzversion'],
			"phpversion"=>$appinfo['phpversion'],
			"mysqlversion"=>$appinfo['mysqlversion'], 
			"identifier"=>$appinfo['identifier'],
			"app_path"=>$appinfo["app_path"]
		);
		 
		$json = $this->curlcloudappmarket($url,$post_data); 
		$json = json_decode($json,true); 
		/*$data=array();
		if( $json["status"]==1){
			$data = $json["data"];
		}*/
		return $json;
	}
	
	public function fetch_updatefile_list( $appinfo=array() ) {
		$upgradeinfo=$appinfo["upgradeinfo"];
		$file = DZZ_ROOT .$appinfo['app_path'].'/' . $appinfo['identifier'].'/updatelist.tmp'; 
		$upgradedataflag = true;
		$upgradedata = @file_get_contents($file); 
		if(!$upgradedata) {
			$upgradedata = dfsockopen($this->upgradeurl.$appinfo['app_path'].'/'.$appinfo['identifier'].'/'.substr($upgradeinfo['upgradelist'], 0, -4).strtolower('_'.$this->locale.'_'.$this->charset).'.txt');
			$upgradedataflag = false;
		}
        
		$return = array();
		 
		$upgradedataarr = explode("\n", str_replace("\r\n","\n",$upgradedata));
		foreach($upgradedataarr as $k => $v) {
			if(!$v) {
				continue;
			}
			$return['file'][$k] = trim(substr($v, 34));
			$return['md5'][$k] = substr($v, 0, 32);
			if(trim(substr($v, 32, 2)) != '*') {
				@unlink($file);
				return array();
			}

		}
		if(!$upgradedataflag) {
			$this->mkdirs(dirname($file));
			$fp = fopen($file, 'w');
			if(!$fp) {
				return array();
			}
			fwrite($fp, $upgradedata);
		}

		return $return;
	}
	
	public function fetch_updatefile_list_bymd5( $appinfo=array() ) {
		$upgradeinfo=$appinfo["upgradeinfo"];
		$file = DZZ_ROOT .$appinfo['app_path'].'/' . $appinfo['identifier'].'/updatelist.tmp'; 
		$upgradedataflag = true; 
		$upgradedata = @file_get_contents($file); 
		if(!$upgradedata) {
			$upgradedata = dfsockopen($this->upgradeurl.$upgradeinfo['app_path'].'/'.$upgradeinfo['identifier'].'/'.$upgradeinfo['version'].'/'.$upgradeinfo['identifier'].'.md5.dzz' );  
			$upgradedataflag = false;
		}
        
		$return = array();
		$upgradedataarr = explode("\n", str_replace("\r\n","\n",$upgradedata));
		foreach($upgradedataarr as $k => $v) {
			if(!$v) {
				continue;
			}
			$return['file'][$k] = trim(substr($v, 34));
			$return['md5'][$k] = substr($v, 0, 32);
			if(trim(substr($v, 32, 2)) != '*') {
				@unlink($file);
				return array();
			}

		}
		if(!$upgradedataflag) {
			$this->mkdirs(dirname($file));
			$fp = fopen($file, 'w');
			if(!$fp) {
				return array();
			}
			fwrite($fp, $upgradedata);
		}

		return $return;
	}
	
	public function fetch_installapp_zip( $appinfo ){
		$file = DZZ_ROOT.'data/update/app/'.$appinfo['app_path'].'/'.$appinfo['identifier'].'/'.$appinfo['version'].'/'.$appinfo['identifier'].'.zip.md5.tmp';
		$upgradedataflag = true;
		$upgradedata = @file_get_contents($file); 
		if(!$upgradedata) { 
			$upgradedata = dfsockopen($this->upgradeurl.$appinfo['app_path'].'/'.$appinfo['identifier'].'/'.$appinfo['latestversion'].'/'.$appinfo['identifier'].'.zip.md5.dzz' );  
			$upgradedataflag = false;
		}
        
		$return = array();
		$upgradedataarr = explode("\n", str_replace("\r\n","\n",$upgradedata));
		foreach($upgradedataarr as $k => $v) {
			if(!$v) {
				continue;
			}
			$return['file'][$k] = trim(substr($v, 34));
			$return['md5'][$k] = substr($v, 0, 32);
			if(trim(substr($v, 32, 2)) != '*') {
				@unlink($file);
				return array();
			}

		}
		if(!$upgradedataflag) {
			$this->mkdirs(dirname($file));
			$fp = fopen($file, 'w');
			if(!$fp) {
				return array();
			}
			fwrite($fp, $upgradedata);
		}
		return $return;
	}
	
	public function fetch_installfile_list( $appinfo ){
		$file = DZZ_ROOT.'data/update/app/'.$appinfo['app_path'].'/'.$appinfo['identifier'].'/'.$appinfo['version'].'/'.$appinfo['identifier'].'.md5.tmp';
		$upgradedataflag = true;
		$upgradedata = @file_get_contents($file); 
		if(!$upgradedata) { 
			$upgradedata = dfsockopen($this->upgradeurl.$appinfo['app_path'].'/'.$appinfo['identifier'].'/'.$appinfo['latestversion'].'/'.$appinfo['identifier'].'.md5.dzz' );  
			$upgradedataflag = false;
		}
        
		$return = array();
		$upgradedataarr = explode("\n", str_replace("\r\n","\n",$upgradedata));
		foreach($upgradedataarr as $k => $v) {
			if(!$v) {
				continue;
			}
			$return['file'][$k] = trim(substr($v, 34));
			$return['md5'][$k] = substr($v, 0, 32);
			if(trim(substr($v, 32, 2)) != '*') {
				@unlink($file);
				return array();
			}

		}
		if(!$upgradedataflag) {
			$this->mkdirs(dirname($file));
			$fp = fopen($file, 'w');
			if(!$fp) {
				return array();
			}
			fwrite($fp, $upgradedata);
		}

		return $return;
	}

	public function compare_basefile($appinfo=array() , $upgradefilelist) {
		$upgradeinfo=$appinfo["upgradeinfo"];
		$source_file_md5 = DZZ_ROOT .$upgradeinfo['app_path'].'/' . $appinfo['identifier'].'/'.$appinfo['identifier'].'.md5'; 
		 
		if(!$dzzfiles = @file($source_file_md5)) {
			return array();
		} 
		$newupgradefilelist = array();
		foreach($upgradefilelist as $v) {
			$newupgradefilelist[$v] = md5_file(DZZ_ROOT .$appinfo['app_path'].'/' . $appinfo['identifier'].'./'. $v);
		}

		$modifylist = $showlist = $searchlist = array();
		foreach($dzzfiles as $line) {
			$file = trim(substr($line, 34));
			$md5datanew[$file] = substr($line, 0, 32);
			if(isset($newupgradefilelist[$file])) {
				if($md5datanew[$file] != $newupgradefilelist[$file]) {
					if(!$upgradeinfo['isupdatetemplate'] && preg_match('/\.htm$/i', $file)) {
						$ignorelist[$file] = $file;
						$searchlist[] = "\r\n".$file; 
						continue;
					}
					$modifylist[$file] = $file;
				} else {
					$showlist[$file] = $file;
				}
			}
		}
		if($searchlist) {
			$file = DZZ_ROOT .$appinfo['app_path'].'/' . $appinfo['identifier'].'/updatelist.tmp';
			$upgradedata = file_get_contents($file);
			$upgradedata = str_replace($searchlist, '', $upgradedata);
			$fp = fopen($file, 'w');
			if($fp) {
				fwrite($fp, $upgradedata);
			}
		} 
		return array($modifylist, $showlist, $ignorelist);
	}
	
	public function compare_basefile_bymd5($appinfo=array() , $upgradefilelist,$updatemd5filelist) {
		$upgradeinfo=$appinfo["upgradeinfo"]; 
		$upgradelist = $md5list = array();
		foreach($upgradefilelist as $k=>$v) {
			$md5 = md5_file(DZZ_ROOT .$upgradeinfo['app_path'].'/' . $appinfo['identifier'].'./'. $v); //此处路劲以最新版本为准
			if($md5!=$updatemd5filelist[$k]){
				$upgradelist[]=$v;
				$md5list[]=$updatemd5filelist[$k];
			}
		}
		return array($upgradelist, $md5list);
	}

	public function compare_file_content($file, $remotefile) {
		if(!preg_match('/\.php$|\.htm$/i', $file)) {
			return false;
		}
		$content = preg_replace('/\s/', '', file_get_contents($file));
		$ctx = stream_context_create(array('http' => array('timeout' => 60)));
		$remotecontent = preg_replace('/\s/', '', file_get_contents($remotefile, false, $ctx));
		if(strcmp($content, $remotecontent)) {
			return false;
		} else {
			return true;
		}
	} 

	public function check_folder_perm($appinfo=array(),$updatefilelist) {
		$path = DZZ_ROOT . $appinfo['app_path'].'/' . $appinfo['identifier'].'/';
		if( isset( $appinfo["new_identifier"])   &&  $appinfo["new_identifier"] ){
			$dir = DZZ_ROOT . $appinfo['app_path'].'/' . $appinfo['new_identifier'].'/';
		} 
		foreach($updatefilelist as $file) {
			if(!file_exists($path.$file)) {
				if(!$this->test_writable(dirname($path.$file))) {
					return false;
				}
			} else {
				if(!is_writable($path.$file)) {
					return false;
				}
			}
		}
		return true;
	}

	public function test_writable($dir) {
		$writeable = 0;
		$this->mkdirs($dir);
		if(is_dir($dir)) {
			if($fp = @fopen("$dir/test.txt", 'w')) {
				@fclose($fp);
				@unlink("$dir/test.txt");
				$writeable = 1;
			} else {
				$writeable = 0;
			}
		}
		return $writeable;
	}

	public function download_file($appinfo, $file, $folder = '', $md5 = '', $position = 0, $offset = 0) { 
		$upgradeinfo=$appinfo["upgradeinfo"];
		$dir = DZZ_ROOT.'data/update/app/'.$upgradeinfo['app_path'].'/'.$upgradeinfo['identifier'].'/'.$upgradeinfo['latestversion'].'/';
		 
		//判断是否包含空格
		$downfile = str_replace(' ','%20',$file);
		//判断是否包含中文
		if (preg_match("/[\x7f-\xff]/", $file)) {
			$file=iconv("UTF-8","gb2312", $file);
			$downfile = $file;
		}
		 
		$this->mkdirs(dirname($dir.$file));
		$downloadfileflag = true;

		if(!$position) {
			$mode = 'wb';
		} else {
			$mode = 'ab';
		}
		$fp = fopen($dir.$file, $mode);
		if(!$fp) {
			return 0;
		}
		 
		$response = dfsockopen($this->upgradeurl.$upgradeinfo['app_path'].'/'.$upgradeinfo['identifier'].'/'.$upgradeinfo['latestversion'].'/'.$downfile.'.dzz', $offset, '', '', FALSE, '', 120, TRUE, 'URLENCODE', FALSE, $position);
		if($response) {
			if($offset && strlen($response) == $offset) {
				$downloadfileflag = false;
			}
			fwrite($fp, $response);
		}
		fclose($fp);

		if($downloadfileflag) {
			if(md5_file($dir.$file) == $md5) {
				return 2;
			} else {
				return 0;
			}
		} else {
			return 1;
		}
	}

	public function mkdirs($dir) {
		if(!is_dir($dir)) {
			if(!self::mkdirs(dirname($dir))) {
				return false;
			}
			if(!@mkdir($dir, 0777)) {
				return false;
			}
			@touch($dir.'/index.htm'); @chmod($dir.'/index.htm', 0777);
		}
		return true;
	}

	public function copy_file($srcfile, $desfile, $type='file') {
		global $_G;
		//判断是否包含中文
		if (preg_match("/[\x7f-\xff]/", $srcfile)) {
			$srcfile=iconv("UTF-8","gb2312", $srcfile);
		}
		if (preg_match("/[\x7f-\xff]/", $srcfile)) {
			$desfile=iconv("UTF-8","gb2312", $desfile);
		}
		if(!is_file($srcfile)) {
			return false;
		}
		if($type == 'file') {
			$this->mkdirs(dirname($desfile));
			copy($srcfile, $desfile);
		} elseif($type == 'ftp') {
			$siteftp = $_GET['siteftp'];
			$siteftp['on'] = 1;
			$siteftp['password'] = authcode($siteftp['password'], 'ENCODE', md5($_G['config']['security']['authkey']));
			$ftp = & dzz_ftp::instance($siteftp);
			$ftp->connect();
			$ftp->upload($srcfile, $desfile);
			if($ftp->error()) {
				return false;
			}
		}
		return true;
	}

	public function versionpath() {
		$versionpath = '';
		foreach(explode(' ',CORE_VERSION) as $unit) {
			$versionpath = $unit;
			break;
		}
		return $versionpath;
	}

	function copy_dir($srcdir, $destdir) {
		$dir = @opendir($srcdir);
		while($entry = @readdir($dir)) {
			$file = $srcdir.$entry;
			if($entry != '.' && $entry != '..') {
				if(is_dir($file)) {
					self::copy_dir($file.'/', $destdir.$entry.'/');
				} else {
					self::mkdirs(dirname($destdir.$entry));
					copy($file, $destdir.$entry);
				}
			}
		}
		closedir($dir);
	}

	function rmdirs($srcdir) {
		$dir = @opendir($srcdir);
		while($entry = @readdir($dir)) {
			$file = $srcdir.$entry;
			if($entry != '.' && $entry != '..') {
				if(is_dir($file)) {
					self::rmdirs($file.'/');
				} else {
					@unlink($file);
				}
			}
		}
		closedir($dir);
		rmdir($srcdir);
	}
	function upgradeinformation() {
		global $_G;
		include_once DZZ_ROOT.'./core/core_version.php';
		$update = array();
		$update['uniqueid'] = C::t('setting')->fetch('siteuniqueid');
		$update['usum']=DB::result_first("select COUNT(*) from %t where 1",array('user'));
		$update['siteurl']=$_G['siteurl'];
		$update['sitename']=$_G['setting']['sitename'];
		$update['version'] = CORE_VERSION;
		$update['release'] = CORE_RELEASE;
		$update['fixbug'] = CORE_FIXBUG;
		$data = '';
		foreach($update as $key => $value) {
			$data .= $key.'='.rawurlencode($value).'&';
		}
		$upgradeurl =  'ht'.'tp:/'.'/dev'.'.'.'d'.'zzo'.'ffice.'.'c'.'om/co'.'unt'.'.p'.'hp?'.'os=d'.'zzoff'.'ice&update='.rawurlencode(base64_encode($data)).'&timestamp='.TIMESTAMP;
		dfsockopen($upgradeurl,0, '', '', FALSE, '',1);
	}
	
	public function curlcloudappmarket( $url="",$post_data="",  $token="" ){ 
		$curl = curl_init(); 
		curl_setopt($curl, CURLOPT_URL, $url); 
		curl_setopt($curl, CURLOPT_HEADER, 0); 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($curl, CURLOPT_POST, 1); 
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
		$response = curl_exec($curl); 
		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$errorno = curl_errno($curl);
		if ($errorno) {
			return($errorno);  
		}
		return($response); 
	}
}
?>