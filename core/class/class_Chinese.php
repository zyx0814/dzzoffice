<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

define('CODETABLE_DIR', DZZ_ROOT.'./core/class/chinesetable/');

class Chinese {

	var $table = '';
	var $iconv_enabled = false;
	var $convertbig5 = false;
	var $unicode_table = array();
	var $config  =  array (
		'SourceLang'		=> '',
		'TargetLang'		=> '',
		'GBtoUnicode_table'	=> 'gb-unicode.table',
		'BIG5toUnicode_table'	=> 'big5-unicode.table',
		'GBtoBIG5_table'   	=> 'gb-big5.table',
	);

	function Chinese($SourceLang, $TargetLang, $ForceTable = FALSE) {
		$this->config['SourceLang'] = $this->_lang($SourceLang);
		$this->config['TargetLang'] = $this->_lang($TargetLang);

		if(ICONV_ENABLE && $this->config['TargetLang'] != 'BIG5' && !$ForceTable) {
			$this->iconv_enabled = true;
		} else {
			$this->iconv_enabled = false;
			$this->OpenTable();
		}
	}

	function _lang($LangCode) {
		$LangCode = strtoupper($LangCode);

		if(substr($LangCode, 0, 2) == 'GB') {
			return 'GBK';
		} elseif(substr($LangCode, 0, 3) == 'BIG') {
			return 'BIG5';
		} elseif(substr($LangCode, 0, 3) == 'UTF') {
			return 'UTF-8';
		} elseif(substr($LangCode, 0, 3) == 'UNI') {
			return 'UNICODE';
		}
	}

	function _hex2bin($hexdata) {
		for($i=0; $i < strlen($hexdata); $i += 2) {
			$bindata .= chr(hexdec(substr($hexdata, $i, 2)));
		}
		return $bindata;
	}

	function OpenTable() {
		$this->unicode_table = array();
		if(!$this->iconv_enabled && $this->config['TargetLang'] == 'BIG5') {
			$this->config['TargetLang'] = 'GBK';
			$this->convertbig5 = TRUE;
		}
		if($this->config['SourceLang'] == 'GBK' || $this->config['TargetLang'] == 'GBK') {
			$this->table = CODETABLE_DIR.$this->config['GBtoUnicode_table'];
		} elseif($this->config['SourceLang'] == 'BIG5' || $this->config['TargetLang'] == 'BIG5') {
			$this->table = CODETABLE_DIR.$this->config['BIG5toUnicode_table'];
		}
		$fp = fopen($this->table, 'rb');
		$tabletmp = fread($fp, filesize($this->table));
		for($i = 0; $i < strlen($tabletmp); $i += 4) {
			$tmp = unpack('nkey/nvalue', substr($tabletmp, $i, 4));
			if($this->config['TargetLang'] == 'UTF-8') {
				$this->unicode_table[$tmp['key']] = '0x'.dechex($tmp['value']);
			} elseif($this->config['SourceLang'] == 'UTF-8') {
				$this->unicode_table[$tmp['value']] = '0x'.dechex($tmp['key']);
			} elseif($this->config['TargetLang'] == 'UNICODE') {
				$this->unicode_table[$tmp['key']] = dechex($tmp['value']);
			}
		}
	}

	function CHSUtoUTF8($c) {
		$str = '';
		if($c < 0x80) {
			$str .= $c;
		} elseif($c < 0x800) {
			$str .= (0xC0 | $c >> 6);
			$str .= (0x80 | $c & 0x3F);
		} elseif($c < 0x10000) {
			$str .= (0xE0 | $c >> 12);
			$str .= (0x80 | $c >> 6 & 0x3F);
			$str .=( 0x80 | $c & 0x3F);
		} elseif($c < 0x200000) {
			$str .= (0xF0 | $c >> 18);
			$str .= (0x80 | $c >> 12 & 0x3F);
			$str .= (0x80 | $c >> 6 & 0x3F);
			$str .= (0x80 | $c & 0x3F);
		}
		return $str;
	}

	function GB2312toBIG5($c) {
		$f = fopen(CODETABLE_DIR.$this->config['GBtoBIG5_table'], 'r');
		$max=strlen($c)-1;
		for($i = 0;$i < $max;$i++){
			$h=ord($c[$i]);
			if($h>=160) {
				$l=ord($c[$i+1]);
				if($h==161 && $l==64){
					$gb="  ";
				} else{
					fseek($f,($h-160)*510+($l-1)*2);
					$gb=fread($f,2);
				}
				$c[$i]=$gb[0];
				$c[$i+1]=$gb[1];
				$i++;
			}
		}
		$result = $c;
		return $result;
	}

	function Convert($SourceText) {
		if($this->config['SourceLang'] == $this->config['TargetLang']) {
			return $SourceText;
		} elseif($this->iconv_enabled) {
			if($this->config['TargetLang'] <> 'UNICODE') {
				return iconv($this->config['SourceLang'], $this->config['TargetLang'], $SourceText);
			} else {
				$return = '';
				while($SourceText != '') {
					if(ord(substr($SourceText, 0, 1)) > 127) {
						$return .= "&#x".dechex($this->Utf8_Unicode(iconv($this->config['SourceLang'],"UTF-8", substr($SourceText, 0, 2)))).";";
						$SourceText = substr($SourceText, 2, strlen($SourceText));
					} else {
						$return .= substr($SourceText, 0, 1);
						$SourceText = substr($SourceText, 1, strlen($SourceText));
					}
				}
				return $return;
			}

		} elseif($this->config['TargetLang'] == 'UNICODE') {
			$utf = '';
			while($SourceText != '') {
				if(ord(substr($SourceText, 0, 1)) > 127) {
					if($this->config['SourceLang'] == 'GBK') {
						$utf .= '&#x'.$this->unicode_table[hexdec(bin2hex(substr($SourceText, 0, 2))) - 0x8080].';';
					} elseif($this->config['SourceLang'] == 'BIG5') {
						$utf .= '&#x'.$this->unicode_table[hexdec(bin2hex(substr($SourceText, 0, 2)))].';';
					}
					$SourceText = substr($SourceText, 2, strlen($SourceText));
				} else {
					$utf .= substr($SourceText, 0, 1);
					$SourceText = substr($SourceText, 1, strlen($SourceText));
				}
			}
			return $utf;
		} else {
			$ret = '';
			if($this->config['SourceLang'] == 'UTF-8') {
				$out = '';
				$len = strlen($SourceText);
				$i = 0;
				while($i < $len) {
					$c = ord(substr($SourceText, $i++, 1));
					switch($c >> 4) {
						case 0: case 1: case 2: case 3: case 4: case 5: case 6: case 7:
							$out .= substr($SourceText, $i - 1, 1);
							break;
						case 12: case 13:
							$char2 = ord(substr($SourceText, $i++, 1));
							$char3 = $this->unicode_table[(($c & 0x1F) << 6) | ($char2 & 0x3F)];
							if($this->config['TargetLang'] == 'GBK') {
								$out .= $this->_hex2bin(dechex($char3 + 0x8080));
							} elseif($this->config['TargetLang'] == 'BIG5') {
								$out .= $this->_hex2bin($char3);
							}
							break;
						case 14:
							$char2 = ord(substr($SourceText, $i++, 1));
							$char3 = ord(substr($SourceText, $i++, 1));
							$char4 = $this->unicode_table[(($c & 0x0F) << 12) | (($char2 & 0x3F) << 6) | (($char3 & 0x3F) << 0)];
							if($this->config['TargetLang'] == 'GBK') {
								$out .= $this->_hex2bin(dechex($char4 + 0x8080));
							} elseif($this->config['TargetLang'] == 'BIG5') {
								$out .= $this->_hex2bin($char4);
							}
							break;
					}
				}
				return !$this->convertbig5 ? $out : $this->GB2312toBIG5($out);
			} else {
				while($SourceText != '') {
					if(ord(substr($SourceText, 0, 1)) > 127) {
						if($this->config['SourceLang'] == 'BIG5') {
							$utf8 = $this->CHSUtoUTF8(hexdec($this->unicode_table[hexdec(bin2hex(substr($SourceText, 0, 2)))]));
						} elseif($this->config['SourceLang'] == 'GBK') {
							$utf8=$this->CHSUtoUTF8(hexdec($this->unicode_table[hexdec(bin2hex(substr($SourceText, 0, 2))) - 0x8080]));
						}
						for($i = 0; $i < strlen($utf8); $i += 3) {
							$ret .= chr(substr($utf8, $i, 3));
						}
						$SourceText = substr($SourceText, 2, strlen($SourceText));
					} else {
						$ret .= substr($SourceText, 0, 1);
						$SourceText = substr($SourceText, 1, strlen($SourceText));
					}
				}
				$SourceText = '';
				return $ret;
			}
		}
	}

	function Utf8_Unicode($char) {
		switch(strlen($char)) {
			case 1:
				return ord($char);
			case 2:
				$n = (ord($char[0]) & 0x3f) << 6;
				$n += ord($char[1]) & 0x3f;
				return $n;
			case 3:
				$n = (ord($char[0]) & 0x1f) << 12;
				$n += (ord($char[1]) & 0x3f) << 6;
				$n += ord($char[2]) & 0x3f;
				return $n;
			case 4:
				$n = (ord($char[0]) & 0x0f) << 18;
				$n += (ord($char[1]) & 0x3f) << 12;
				$n += (ord($char[2]) & 0x3f) << 6;
				$n += ord($char[3]) & 0x3f;
				return $n;
		}
	}

}
?>