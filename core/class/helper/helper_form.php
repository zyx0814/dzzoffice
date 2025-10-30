<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class helper_form {
    private static function _create($cookie, $code = '') {
		global $_G;
		$ssid = C::t('seccheck')->insert(array(
		    'dateline' => TIMESTAMP,
		    'code' => $code,
		    'succeed' => 0,
		    'verified' => 0,
		), true);
		dsetcookie($cookie, $ssid.'.'.substr(md5($ssid.$_G['uid'].$_G['authkey']), 8, 18));
	}
    public static function make_seccode($idhash){
		global $_G;
        $seccode = random(6, 1);
        $seccodeunits = '';
        if($_G['setting']['seccodedata']['type'] == 1) {
            $len = strtoupper(CHARSET) == 'GBK' ? 2 : 3;
            $code = array(substr($seccode, 0, 3), substr($seccode, 3, 3));
            $seccode = '';
            for($i = 0; $i < 2; $i++) {
                $seccode .= substr(lang('chn'), $code[$i] * $len, $len);
            }
        } elseif($_G['setting']['seccodedata']['type'] == 3) {
            $s = sprintf('%04s', base_convert($seccode, 10, 20));
            $seccodeunits = 'CEFHKLMNOPQRSTUVWXYZ';
        } else {
            $s = sprintf('%04s', base_convert($seccode, 10, 24));
            $seccodeunits = 'BCEFGHJKMPQRTVWXY2346789';
        }
        if($seccodeunits) {
            $seccode = '';
            for($i = 0; $i < 4; $i++) {
                $unit = ord($s[$i]);
                $seccode .= ($unit >= 0x30 && $unit <= 0x39) ? $seccodeunits[$unit - 0x30] : $seccodeunits[$unit - 0x57];
            }
        }
		self::_create('seccode'.$idhash, $seccode);
		return $seccode;
	}

	public static function make_secqaa() {
		global $_G;
		loadcache('secqaa');
		$secqaakey = max(1, random(1, 1));
		if ($_G['cache']['secqaa'][$secqaakey]['type']) {

            $qaafile = libfile('secqaa/' . $_G['cache']['secqaa'][$secqaakey]['question'], 'class');
            $class = $_G['cache']['secqaa'][$secqaakey]['question'];

            if (file_exists($qaafile)) {
                @include_once $qaafile;
                $class = 'secqaa_' . $class;
                if (class_exists($class)) {
                    $qaa = new $class();
                    if (method_exists($qaa, 'make')) {
                        $_G['cache']['secqaa'][$secqaakey]['answer'] = md5($qaa->make($_G['cache']['secqaa'][$secqaakey]['question']));
                    }
                }
            }
        }
		self::_create('secqaa'.$idhash, substr($_G['cache']['secqaa'][$secqaakey]['answer'], 0, 6));
		return $_G['cache']['secqaa'][$secqaakey]['question'];
	}

    public static function submitcheck($var, $allowget = 0, $seccodecheck = 0, $secqaacheck = 0) {
        if (!getgpc($var)) {
            return FALSE;
        } else {
            global $_G;
            if ($allowget || ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_GET['formhash']) && $_GET['formhash'] == formhash() && empty($_SERVER['HTTP_X_FLASH_VERSION']) && (empty($_SERVER['HTTP_REFERER']) ||
                        preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])))) {
                //if(checkperm('seccode')) {
                if ($secqaacheck && !check_secqaa($_GET['secanswer'], $_GET['sechash'])) {
                    showmessage('login_question_invalid');
                }
                if ($seccodecheck && !check_seccode($_GET['seccodeverify'], $_GET['sechash'])) {
                    showmessage('submit_seccode_invalid');
                }
                //}
                return TRUE;
            } else {
                showmessage('submit_invalid');
            }
        }
    }

    public static function check_seccode($value, $idhash, $verifyonly = false) {
        global $_G;
        if (!$_G['setting']['seccodestatus']) {
            return true;
        }
        if (!isset($_G['cookie']['seccode' . $idhash])) {
            return false;
        }

		list($ssid, $sign) = explode('.', $_G['cookie']['seccode' . $idhash]);
		if($sign != substr(md5($ssid.$_G['uid'].$_G['authkey']), 8, 18)) {
			return false;
		}
		$seccheck = C::t('seccheck')->fetch($ssid);
		if(!$seccheck) {
			return false;
		}
		if(TIMESTAMP - $seccheck['dateline'] > 600 || $seccheck['verified'] > 4) {
			C::t('seccheck')->delete_expiration($ssid);
			return false;
		}
        $ssid = $seccheck['ssid'];
        if(!is_numeric($_G['setting']['seccodedata']['type'])) {
            $codefile = libfile('seccode/' . $_G['setting']['seccodedata']['type'], 'class');
            $class = $_G['setting']['seccodedata']['type'];
			if(file_exists($codefile)) {
				@include_once $codefile;
				$class = 'seccode_'.$class;
				if(class_exists($class)) {
					$code = new $class();
					if(method_exists($code, 'check')) {
						$return = $code->check($value, $idhash);
					}
				}
			} else {
				$return = false;
			}
		} else {
			$return = $seccheck['code'] == strtoupper($value);
		}
        if($return) {
			C::t('seccheck')->update_succeed($ssid);
		} else {
			C::t('seccheck')->update_verified($ssid);
		}
		if(!$verifyonly) {
			C::t('seccheck')->delete($ssid);
		}
		return $return;
    }

    public static function check_secqaa($value, $idhash) {
        global $_G;
        if (!$_G['setting']['secqaa']) {
            return true;
        }
        if (!isset($_G['cookie']['secqaa' . $idhash])) {
            return false;
        }
        loadcache('secqaa');
        list($checkvalue, $checktime, $checkidhash, $checkformhash) = explode("\t", authcode($_G['cookie']['secqaa' . $idhash], 'DECODE', $_G['config']['security']['authkey']));
        return $checkvalue == md5($value) && TIMESTAMP - 180 > $checktime && $checkidhash == $idhash && FORMHASH == $checkformhash;
    }


    public static function get_url_list($message) {
        $return = array();

        (strpos($message, '[/img]') || strpos($message, '[/flash]')) && $message = preg_replace("/\[img[^\]]*\]\s*([^\[\<\r\n]+?)\s*\[\/img\]|\[flash[^\]]*\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/is", '', $message);
        if (preg_match_all("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.)[^ \[\]\"']+/i", $message, $urllist)) {
            foreach ($urllist[0] as $key => $val) {
                $val = trim($val);
                $return[0][$key] = $val;
                if (!preg_match('/^https?:\/\//is', $val)) $val = 'http://' . $val;
                $tmp = parse_url($val);
                $return[1][$key] = $tmp['host'];
                if ($tmp['port']) {
                    $return[1][$key] .= ":{$tmp['port']}";
                }
            }
        }
        return $return;
    }
}

?>