<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class filesock_stream extends filesock_base {
	public function request($param = []) {
		parent::request($param);
		if(!$this->safequery) {
			return '';
		}
		$boundary = $this->encodetype == 'application/x-www-form-urlencoded' ? '' : '----WebKitFormBoundary'.random(16);
		$header = '';
		$headerlist = [];
		$headerlist['Accept'] = '*/*';
		$headerlist['Accept-Language'] = 'zh-CN';
		$headerlist['User-Agent'] = $this->useragent;
		$headerlist['Host'] = $this->host.':'.$this->port;
		$headerlist['Connection'] = 'Close';
		if($this->method == 'POST') {
			if($this->encodetype == 'application/x-www-form-urlencoded') {
				$data = http_build_query($this->post);
			} elseif($this->encodetype == 'multipart/form-data') {
				$data = '';
				foreach($this->post as $k => $v) {
					$data .= "--$boundary\r\n";
					$data .= 'Content-Disposition: form-data; name="'.$k.'"'.(isset($this->files[$k]) ? '; filename="'.basename($this->files[$k])."\"\r\n".'Content-Type: application/octet-stream' : '')."\r\n\r\n";
					$data .= $v."\r\n";
				}
				foreach($this->files as $k => $file) {
					if(!isset($this->post[$k]) && file_exists($file)) {
						if($fp = @fopen($file, 'r')) {
							$v = fread($fp, filesize($file));
							fclose($fp);
							$data .= "--$boundary\r\n";
							$data .= 'Content-Disposition: form-data; name="'.$k.'"; filename="'.basename($file)."\"\r\n".'Content-Type: application/octet-stream'."\r\n\r\n";
							$data .= $v."\r\n";
						}
					}
				}
				$data .= "--$boundary--\r\n";
			} else {
				$data = $this->rawdata;
			}
			if($this->encodetype == 'multipart/form-data') {
				$headerlist['Content-Type'] = 'multipart/form-data; boundary='.$boundary;
			} else {
				$headerlist['Content-Type'] = $this->encodetype;
			}
			$headerlist['Content-Length'] = strlen($data);
			$headerlist['Cache-Control'] = 'no-cache';
		} elseif(!in_array($this->method, ['GET', 'HEAD']) && $this->rawdata) {
			$data = $this->rawdata;
			$headerlist['Content-Type'] = $this->encodetype;
			$headerlist['Content-Length'] = strlen($data);
			$headerlist['Cache-Control'] = 'no-cache';
		}
		$out = $this->method.' '.$this->path." HTTP/1.0\r\n";
		if($this->header) {
			foreach($this->header as $k => $v) {
				$headerlist[$k] = $v;
			}
		}
		if($this->cookie) {
			$headerlist['Cookie'] = $this->cookie;
		}
		$header = $this->_build_header($headerlist);
		$out .= $header;
		if(isset($data) && $data) {
			$out .= $data;
		}
	
		$fpflag = 0;
		$context = [];
		if($this->scheme == 'https') {
			$context['ssl'] = [
				'verify_peer' => false,
				'verify_peer_name' => false,
				'peer_name' => $this->host,
            ];
			if($this->verifypeer) {
				$context['ssl']['verify_peer'] = true;
				$context['ssl']['verify_peer_name'] = true;
				$context['ssl']['allow_self_signed'] = false;
				if(is_string($this->verifypeer)) {
					$context['ssl']['cafile'] = $this->verifypeer;
				}
			}
		}
		if(ini_get('allow_url_fopen')) {
			$context['http'] = [
				'method' => $this->method,
				'header' => $header,
				'timeout' => $this->conntimeout,
				'ignore_errors' => !$this->failonerror,
            ];
			if(isset($data)) {
				$context['http']['content'] = $data;
			}
			$context = stream_context_create($context);
			$errno = 0;
			$errstr = '';
			set_error_handler(function($eno, $est) use (&$errno, &$errstr) {
				$errno = $eno;
				$errstr = $est;
				return true;
			});
			$fp = @fopen($this->scheme.'://'.($this->primaryip ?: $this->host).':'.$this->port.$this->path, 'b', false, $context);
			$fpflag = 1;
			restore_error_handler();
		} elseif(function_exists('stream_socket_client')) {
			$context = stream_context_create($context);
			$fp = @stream_socket_client(($this->scheme == 'https' ? 'ssl://' : '').($this->primaryip ?: $this->host).':'.$this->port, $errno, $errstr, $this->conntimeout, STREAM_CLIENT_CONNECT, $context);
		} else {
			$fp = @fsocketopen(($this->scheme == 'https' ? 'ssl://' : '').($this->scheme == 'https' ? $this->host : ($this->ip ?: $this->host)), $this->port, $errno, $errstr, $this->conntimeout);
		}
	
		if(!$fp) {
			$this->errno = $errno;
			$this->errstr = $errstr;
			return;
		} else {
			stream_set_blocking($fp, $this->block);
			stream_set_timeout($fp, $this->timeout);
			if(!$fpflag) {
				@fwrite($fp, $out);
			}
			$status = stream_get_meta_data($fp);
			if(!$status['timed_out']) {
				$headers = '';
				if($fpflag) {
					$headers = implode("\r\n", $http_response_header)."\r\n\r\n";
				} else {
					while(!feof($fp) && !$fpflag) {
						$header = @fgets($fp);
						$headers .= $header;
						if($this->failonerror && $header && substr($header, 0, 6) == 'HTTP/1' && intval(substr($header, 9, 3)) > 400) {
							$this->errno = 2;
							$this->errstr = 'Failed to open stream: HTTP request failed! '.$header;
							return;
						}
						if($header && ($header == "\r\n" ||  $header == "\n")) {
							break;
						}
					}
				}
				$GLOBALS['filesockheader'] = $this->filesockheader = $headers;
	
				if($this->position) {
					for($i=0; $i<$this->position; $i++) {
						fgetc($fp);
					}
				}
	
				if($this->limit) {
					$return = stream_get_contents($fp, $this->limit);
				} else {
					$return = stream_get_contents($fp);
				}
			}
			@fclose($fp);
			$this->filesockbody = $return;
			if($this->returnbody) {
				return $return;
			} else {
				return;
			}
		}
	}
	private function _build_header($param) {
		$output = '';
		foreach($param as $k => $v) {
			$output .= $k.': '.$v."\r\n";
		}
		return $output."\r\n";
	}
}
