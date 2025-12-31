<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class filesock_base {
	public $url;
	public $limit = 0;
	public $method = '';
	public $post = '';
	public $cookie = '';
	public $ip = '';
	public $conntimeout = 5;
	public $timeout = 15;
	public $block = true;
	public $encodetype = 'URLENCODE';
	public $position = 0;
	public $files = [];
	public $unsafe = false;
	public $useragent = '';
	public $header = [];
	public $rawdata = '';
	public $returnbody = true;
	public $failonerror = true;

	public $errno = 0;
	public $errstr = '';
	public $filesockheader = '';
	public $filesockbody = '';

	public $scheme;
	public $host;
	public $port;
	public $path;
	public $safequery = true;
	public $allowmultiip = false;
	public $primaryip;
	public $iplist;
	public $verifypeer;

	public function __construct($param = []) {
		global $_G;
		$this->verifypeer = $_G['config']['security']['fsockopensafe']['verifypeer'] ?? false;
		if(!empty($param)) {
			$this->set($param);
		}
	}

	private function _query_safecheck($tmp) {
		global $_G;
		$this->primaryip = '';
		if(!$tmp || empty($tmp['host'])) return false;
		$isip = false;
		if(filter_var(str_replace(['[',']'], '', $tmp['host']), FILTER_VALIDATE_IP)) {
			$isip = true;
		}
		if(isset($tmp['user']) || isset($tmp['pass'])) return false;
		if(!$isip && strpbrk($tmp['host'], ':#?[]') !== false) return false;
		if($this->ip) {
			if(!(filter_var($this->ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false)) {
				return false;
			} elseif(filter_var($this->ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
				$this->primaryip = '['.$this->ip.']';
			} else {
				$this->primaryip = $this->ip;
			}
		} elseif($isip) {
			$this->primaryip = $tmp['host'];
		}
		if(!in_array(strtolower($tmp['scheme']), ['http', 'https'])) {
			return false;
		}
		$port = $_G['config']['security']['fsockopensafe']['port'] ?? [80, 443];

		if(!empty($port) && isset($tmp['port'])) {
			if(isset($_SERVER['SERVER_PORT']) && !in_array($_SERVER['SERVER_PORT'], $port)) {
				$port[] = $_SERVER['SERVER_PORT'];
			}
			if(!in_array($tmp['port'], $port)) {
				return false;
			}
		}

		if(!isset($tmp['port'])) {
			$tmp['port'] = strtolower($tmp['scheme']) == 'https' ? 443 : 80;
		}
		if($this->primaryip) {
			return $tmp;
		}
		$this->_dns_query($tmp['host']);
		if(!$this->primaryip) return false;
		if(!$this->allowmultiip) {
			$this->_connectivity_check($tmp['port']);
		}

		return $tmp;
	}
	private function _parse_url() {
		$tmp = parse_url($this->url);
		if(!$this->unsafe) {
			$tmp = $this->_query_safecheck($tmp);
			if($tmp === false) {
				$this->safequery = false;
				$this->scheme = '';
				$this->host = '';
				$this->path = '/';
				$this->port = '80';
				return;
			}
		} elseif($this->ip) {
			if(filter_var($this->ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
				$this->primaryip = '['.$this->ip.']';
			} else {
				$this->primaryip = $this->ip;
			}
		}
		$this->scheme = $tmp['scheme'];
		$this->host = $tmp['host'];
		$this->path = !empty($tmp['path']) ? $tmp['path'].(empty($tmp['query']) ? '' : '?'.$tmp['query']) : '/';
		$this->port = empty($tmp['port']) ? ($this->scheme == 'https' ? '443' : '80') : $tmp['port'];
	}
	private function _dns_query($host) {
		global $_G;
		if(!$host) {
			return;
		}
		$dnsresult = [[],[]];
		$recordtype = 0;
		$ipversion = $_G['config']['security']['fsockopensafe']['ipversion'] ?? ['ipv6', 'ipv4'];
		if(in_array('ipv4', $ipversion)) {
			$recordtype += DNS_A;
		}
		if(in_array('ipv6', $ipversion)) {
			$recordtype += DNS_AAAA;
		}
		$dnsq = dns_get_record($host, $recordtype);
		foreach($dnsq as $v) {
			if($v['type'] == 'A' && filter_var($v['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
				$dnsresult[0][] = $v['ip'];
			} elseif($v['type'] == 'AAAA' && filter_var($v['ipv6'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
				$dnsresult[1][] = $v['ipv6'];
			}
		}
		$this->iplist = $dnsresult;
		$this->primaryip = '';
		if(!empty($dnsresult[0])) {
			$this->primaryip = $dnsresult[0][0];
		} elseif(!empty($dnsresult[1])) {
			$this->primaryip = '['.$dnsresult[1][0].']';
		}
	}
	private function _connectivity_check($port) {
		foreach($this->iplist[1] as $v) {
			if($this->_ipcheck('['.$v.']', $port)) {
				$this->primaryip = '['.$v.']';
				return;
			}
		}
		foreach($this->iplist[0] as $v) {
			if($this->_ipcheck($v, $port)) {
				$this->primaryip = $v;
				return;
			}
		}
	}
	private function _ipcheck($ip, $port) {
		$errstr = '';
		$ch = fsocketopen($ip, $port, $errno, $errstr, 0.6);
		if(!$ch) {
			return false;
		} else {
			fclose($ch);
			return true;
		}
	}
	private function _format_postkey($post, &$result, $key = '') {
		foreach($post as $k => $v) {
			$_k = $key ? $key.'['.$k.']' : $k;
			if(is_array($v)) {
				$this->_format_postkey($v, $result, $_k);
			} else {
				$result[$_k] = $v;
			}
		}
	}
	private function _format_encodetype() {
		if($this->encodetype === 'URLENCODE') {
			$this->encodetype = 'application/x-www-form-urlencoded';
		} elseif($this->encodetype === 'FORMDATA') {
			$this->encodetype = 'multipart/form-data';
		} elseif($this->encodetype === 'JSON') {
			$this->encodetype = 'application/json';
		}
	}
	private function _detect_method() {
		if($this->post || $this->files) {
			$this->method = 'POST';
		} else {
			$this->method = 'GET';
		}
	}
	public function set($param) {
		if(is_string($param)) {
			$param = ['url' => $param];
		}
		foreach($param as $key => $value) {
			if(property_exists($this, $key)) {
				$this->$key = $value;
			}
		}
	}
	public function request($param = []) {
		if(!empty($param)) {
			$this->set($param);
		}
		$this->_parse_url();
		$this->_format_encodetype();
		if(!$this->useragent) {
			$this->useragent = $_SERVER['HTTP_USER_AGENT'];
		}
		if(!$this->method) {
			$this->_detect_method();
		}
		if($this->post) {
			if(!is_array($this->post)) {
				parse_str($this->post, $this->post);
			}
			$this->_format_postkey($this->post, $postnew);
			$this->post = $postnew;
		}
	}
	public function get($param = []) {
		$this->method = 'GET';
		return $this->request($param);
	}
	public function post($param = []) {
		$this->method = 'POST';
		return $this->request($param);
	}
	public function head($param = []) {
		$this->method = 'HEAD';
		return $this->request($param);
	}
	public function patch($param = []) {
		$this->method = 'PATCH';
		return $this->request($param);
	}
	public function put($param = []) {
		$this->method = 'PUT';
		return $this->request($param);
	}
	public function delete($param = []) {
		$this->method = 'DELETE';
		return $this->request($param);
	}
}
