<?php
class filesock {
	public static function open($param = array()) {
		$allowcurl = true;
		if(isset($param['allowcurl']) && $param['allowcurl'] == false) {
			$allowcurl = false;
		}
		if(function_exists('curl_init') && function_exists('curl_exec') && $allowcurl) {
			return new filesock_curl($param);
		} else {
			return new filesock_stream($param);
		}
	}
}
