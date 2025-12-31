<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class filesock_curl extends filesock_base {
	public $curlstatus;
	public function __construct($param = []) {
		parent::__construct($param);
		if(version_compare(PHP_VERSION, '7.2', '>=')) {
			$this->allowmultiip = true;
		}
	}
	public function request($param = []) {
		parent::request($param);
		if(!$this->safequery) {
			return '';
		}
		$ch = curl_init();
		$headerlist = $httpheader = [];
		if($this->primaryip) {
			$headerlist['Host'] = $this->host;
		}
		$headerlist['User-Agent'] = $this->useragent;
		if($this->primaryip) {
			if($this->allowmultiip && $this->iplist) {
				$iplist = [];
				foreach($this->iplist[1] as $v) {
					$iplist[] = '['.$v.']';
				}
				foreach($this->iplist[0] as $v) {
					$iplist[] = $v;
				}
				curl_setopt($ch, CURLOPT_RESOLVE, [$this->host.':'.$this->port.':'.implode(',', $iplist)]);
			} else {
				curl_setopt($ch, CURLOPT_RESOLVE, [$this->host.':'.$this->port.':'.$this->primaryip]);
			}
		}
		curl_setopt($ch, CURLOPT_URL, $this->scheme.'://'.$this->host.($this->port ? ':'.$this->port : '').$this->path);
		if($this->scheme == 'https') {
			if($this->verifypeer) {
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
				if(is_string($this->verifypeer)) {
					curl_setopt($ch, CURLOPT_CAINFO, $this->verifypeer);
				}
			} else {
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			}
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_FAILONERROR, $this->failonerror);
		$usetmpfile = false;
		if($this->method == 'POST') {
			curl_setopt($ch, CURLOPT_POST, 1);
			if($this->encodetype == 'application/x-www-form-urlencoded') {
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->post));
			} elseif($this->encodetype == 'multipart/form-data') {
				foreach($this->post as $k => $v) {
					if(isset($this->files[$k])) {
						$usetmpfile = true;
						$tmpnam = tempnam(DZZ_ROOT.'./data/attachment/temp', 'cU');
						file_put_contents($tmpnam, $v);
						$this->post[$k] = curl_file_create($tmpnam, 'application/octet-stream', $this->files[$k]);
					}
				}
				foreach($this->files as $k => $file) {
					if(!isset($this->post[$k]) && file_exists($file)) {
						$this->post[$k] = curl_file_create($file);
					}
				}
				curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post);
			} else {
				$headerlist['Content-Type'] = $this->encodetype;
				curl_setopt($ch, CURLOPT_POSTFIELDS, $this->rawdata);
			}
		} elseif(!in_array($this->method, ['GET', 'HEAD']) && $this->rawdata) {
			$headerlist['Content-Type'] = $this->encodetype;
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->rawdata);
		} elseif($this->method != 'GET') {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
		}
		if($this->header) {
			foreach($this->header as $k => $v) {
				$headerlist[$k] = $v;
			}
		}
		foreach($headerlist as $k => $v) {
			$httpheader[] = $k.': '.$v;
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
		if($this->cookie) {
			curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
		}
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->conntimeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		$data = curl_exec($ch);
		$this->curlstatus = curl_getinfo($ch);
		$this->errno = curl_errno($ch);
		$this->errstr = curl_error($ch);
		curl_close($ch);
		if($usetmpfile && $dh = opendir(DZZ_ROOT.'./data/attachment/temp')) {
			while(($fil = readdir($dh)) !== false) {
				if(substr($fil, 0, 2) == 'cU') {
					unlink(DZZ_ROOT.'./data/attachment/temp/'.$fil);
				}
			}
			closedir($dh);
		}
		$GLOBALS['filesockheader'] = $this->filesockheader = substr($data, 0, $this->curlstatus['header_size']);
		$data = substr($data, $this->curlstatus['header_size'] + $this->position);
		$this->filesockbody = !$this->limit ? $data : substr($data, 0, $this->limit);
		if(!$this->returnbody || $this->errno) {
			return;
		} else {
			return $this->filesockbody;
		}
	}

}
