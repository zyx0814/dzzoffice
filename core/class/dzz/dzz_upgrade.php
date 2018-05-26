<?php


if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class dzz_upgrade {

	var $upgradeurl = 'http://upgrade.dzzoffice.com/dzzoffice/';
	var $locale = 'SC';
	var $charset = 'UTF-8';

	public function fetch_updatefile_list($upgradeinfo) {

		$file = DZZ_ROOT.'./data/update/dzzoffice'.$upgradeinfo['latestversion'].'/updatelist.tmp';
		$upgradedataflag = true;
		$upgradedata = @file_get_contents($file);
		if(!$upgradedata) {
			$upgradedata = dfsockopen($this->upgradeurl.substr($upgradeinfo['upgradelist'], 0, -4).strtolower('_'.$this->locale.'_'.$this->charset).'.txt');
			$upgradedataflag = false;
		}
       //exit($this->upgradeurl.substr($upgradeinfo['upgradelist'], 0, -4).strtolower('_'.$this->locale.'_'.$this->charset).'.txt');
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

	public function compare_basefile($upgradeinfo, $upgradefilelist , $upgrademd5list) {
		if(!$dzzfiles = @file(DZZ_ROOT.'./admin/dzzofficefiles.md5')) {
			$modifylist = $showlist = $searchlist = $md5datanew = $md5data = array();
			foreach($upgradefilelist as $key => $file) {
				$md5datanew[$file] = $upgrademd5list[$key];
				$md5data[$file] = md5_file(DZZ_ROOT.'./'.$file);
				if(!file_exists(DZZ_ROOT.'./'.$file)){
					$newlist[$file] = $file;
				}elseif($md5datanew[$file] != $md5data[$file]) {
					if(!$upgradeinfo['isupdatetemplate'] && preg_match('/\.htm$/i', $file)) {
						$ignorelist[$file] = $file;
						$searchlist[] = "\r\n".$file;
						$searchlist[] = "\n".$file;
						continue;
					}
					$modifylist[$file] = $file;
				} else {
					$showlist[$file] = $file;
				}
			}
			if($searchlist) {
				$file = DZZ_ROOT.'./data/update/dzzoffice'.$upgradeinfo['latestversion'].'/updatelist.tmp';
				$upgradedata = file_get_contents($file);
				$upgradedata = str_replace($searchlist, '', $upgradedata);
				$fp = fopen($file, 'w');
				if($fp) {
					fwrite($fp, $upgradedata);
				}
			}
			return array($modifylist, $showlist, $ignorelist,$newlist);
		}

		$newupgradefilelist = $newlist = array();
		foreach($upgradefilelist as $v) {
			if(!file_exists(DZZ_ROOT.'./'.$v)){
				$newlist[$v]=$v;
			}else{
				$newupgradefilelist[$v] = md5_file(DZZ_ROOT.'./'.$v);
			}
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
						$searchlist[] = "\n".$file;
						continue;
					}
					$modifylist[$file] = $file;
				} else {
					$showlist[$file] = $file;
				}
			}
		}
		if($searchlist) {
			$file = DZZ_ROOT.'./data/update/dzzoffice'.$upgradeinfo['latestversion'].'/updatelist.tmp';
			$upgradedata = file_get_contents($file);
			$upgradedata = str_replace($searchlist, '', $upgradedata);
			$fp = fopen($file, 'w');
			if($fp) {
				fwrite($fp, $upgradedata);
			}
		}

		return array($modifylist, $showlist, $ignorelist,$newlist);
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

	public function check_upgrade() {

		include_once libfile('class/xml');
		include_once libfile('function/cache');

		$return = false;
		$upgradefile = $this->upgradeurl.$this->versionpath().'/upgrade.xml';
		$response = xml2array(dfsockopen($upgradefile, 0, '', '', FALSE, '', 1));
		if(isset($response['cross']) || isset($response['patch'])) {
			C::t('setting')->update('upgrade', $response);
			$return = true;
		} else {
			C::t('setting')->update('upgrade', '');
			$return = false;
		}
		updatecache('setting');
		$this->upgradeinformation();
		return $return;
	}

	public function check_folder_perm($updatefilelist) {
		foreach($updatefilelist as $file) {
			if(!file_exists(DZZ_ROOT.$file)) {
				if(!$this->test_writable(dirname(DZZ_ROOT.$file))) {
					return false;
				}
			} else {
				if(!is_writable(DZZ_ROOT.$file)) {
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

	public function download_file($upgradeinfo, $file, $folder = 'upload', $md5 = '', $position = 0, $offset = 0) {
		$dir = DZZ_ROOT.'./data/update/dzzoffice'.$upgradeinfo['latestversion'].'/';
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
		$response = dfsockopen($this->upgradeurl.$upgradeinfo['latestversion'].'/'.$folder.'/'.$file.'.dzz', $offset, '', '', FALSE, '', 120, TRUE, 'URLENCODE', FALSE, $position);
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

	public function copy_file($srcfile, $desfile, $type) {
		global $_G;

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
		$upgradeurl = APP_CHECK_URL."market/system/info/".rawurlencode(base64_encode($data))."/".TIMESTAMP; 
		dfsockopen($upgradeurl,0, '', '', FALSE, '',1);
	}
}
?>