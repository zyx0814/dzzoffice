<?php
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class helper_form {


	public static function submitcheck($var, $allowget = 0, $seccodecheck = 0, $secqaacheck = 0) {
		if(!getgpc($var)) {
			return FALSE;
		} else {
			global $_G;
			if($allowget || ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_GET['formhash']) && $_GET['formhash'] == formhash() && empty($_SERVER['HTTP_X_FLASH_VERSION']) && (empty($_SERVER['HTTP_REFERER']) ||
			preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])))) {
				//if(checkperm('seccode')) {
					if($secqaacheck && !check_secqaa($_GET['secanswer'], $_GET['sechash'])) {
						showmessage('login_question_invalid');
					}
					if($seccodecheck && !check_seccode($_GET['seccodeverify'], $_GET['sechash'])) {
						showmessage('submit_seccode_invalid');
					}
				//}
				return TRUE;
			} else {
				showmessage('submit_invalid');
			}
		}
	}

	public static function check_seccode($value, $idhash) {
		global $_G;
		if(!$_G['setting']['seccodestatus']) {
			return true;
		}
		if(!is_numeric($_G['setting']['seccodedata']['type'])) {
		
			$codefile = libfile('seccode/'.$_G['setting']['seccodedata']['type'], 'class');
			$class = $_G['setting']['seccodedata']['type'];
			
			if(file_exists($codefile)) {
				@include_once $codefile;
				$class = 'seccode_'.$class;
				if(class_exists($class)) {
					$code = new $class();
					if(method_exists($code, 'check')) {
						return $code->check($value, $idhash);
					}
				}
			}
			return false;
		}
		if(!isset($_G['cookie']['seccode'.$idhash])) {
			return false;
		}
		list($checkvalue, $checktime, $checkidhash, $checkformhash) = explode("\t", authcode($_G['cookie']['seccode'.$idhash], 'DECODE', $_G['config']['security']['authkey']));
		return $checkvalue == strtoupper($value) && TIMESTAMP - 180 > $checktime && $checkidhash == $idhash && FORMHASH == $checkformhash;
	}

	public static function check_secqaa($value, $idhash) {
		global $_G;
		if(!$_G['setting']['secqaa']) {
			return true;
		}
		if(!isset($_G['cookie']['secqaa'.$idhash])) {
			return false;
		}
		loadcache('secqaa');
		list($checkvalue, $checktime, $checkidhash, $checkformhash) = explode("\t", authcode($_G['cookie']['secqaa'.$idhash], 'DECODE', $_G['config']['security']['authkey']));
		return $checkvalue == md5($value) && TIMESTAMP - 180 > $checktime && $checkidhash == $idhash && FORMHASH == $checkformhash;
	}


	public static function get_url_list($message) {
		$return = array();

		(strpos($message, '[/img]') || strpos($message, '[/flash]')) && $message = preg_replace("/\[img[^\]]*\]\s*([^\[\<\r\n]+?)\s*\[\/img\]|\[flash[^\]]*\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/is", '', $message);
		if(preg_match_all("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.)[^ \[\]\"']+/i", $message, $urllist)) {
			foreach($urllist[0] as $key => $val) {
				$val = trim($val);
				$return[0][$key] = $val;
				if(!preg_match('/^http:\/\//is', $val)) $val = 'http://'.$val;
				$tmp = parse_url($val);
				$return[1][$key] = $tmp['host'];
				if($tmp['port']){
					$return[1][$key] .= ":$tmp[port]";
				}
			}
		}
		return $return;
	}
}

?>