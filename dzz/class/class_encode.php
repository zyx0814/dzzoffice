<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

define ('UTF32_BIG_ENDIAN_BOM'   , chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF));

define ('UTF32_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00));

define ('UTF16_BIG_ENDIAN_BOM'   , chr(0xFE) . chr(0xFF));

define ('UTF16_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE));

define ('UTF8_BOM'               , chr(0xEF) . chr(0xBB) . chr(0xBF));
class Encode_Core {

	/**
	* 文件分析方法来检查UNICODE文件，ANSI文件没有文件头，此处不分析
	*/
	private function detect_utf_encoding($text) {
		$first2 = substr($text, 0, 2);
		$first3 = substr($text, 0, 3);
		$first4 = substr($text, 0, 3);
		if ($first3 == UTF8_BOM) return 'UTF-8';
		
		elseif ($first4 == UTF32_BIG_ENDIAN_BOM) return 'UTF-32BE';
		
		elseif ($first4 == UTF32_LITTLE_ENDIAN_BOM) return 'UTF-32LE';
		
		elseif ($first2 == UTF16_BIG_ENDIAN_BOM) return 'UTF-16BE';
		
		elseif ($first2 == UTF16_LITTLE_ENDIAN_BOM) return 'UTF-16LE';
		
		return '';

	}
	/**
	 * 检测是否GB2312编码
	 * @param string $str 
	 * @since 2012-03-20
	 * @return boolean 
	 */
	private function is_gb2312($str)  {  
	    for($i=0; $i<strlen($str); $i++) {  
	        $v = ord( $str[$i] );  
	        if( $v > 127) {  
	            if( ($v >= 228) && ($v <= 233) ){  
	                if( ($i+2) >= (strlen($str) - 1)) return true;  // not enough characters  
	                $v1 = ord( $str[$i+1] );  
	                $v2 = ord( $str[$i+2] );  
	                if( ($v1 >= 128) && ($v1 <=191) && ($v2 >=128) && ($v2 <= 191) )  
	                    return false;   
	                else  
	                    return true;    //GB编码  
	            }  
	        }  
	    }  
	} 
	private function is_GBK($str){
		$s1 = iconv('gbk','utf-8',$str);
		$s0 = iconv('utf-8','gbk',$s1);
		if($s0 == $str){
			return true;
		}else{
			return false;
		}
	}
	/**
	* 取得编码
	* @param string $str
	* @return string $encoding
	*/
	public static function get_encoding($str){
		$ary = array();
		
		//$ary[] = "ASCII";
		$ary[] = "UTF-8";
		$ary[] = "GB18030";//简体码
		$ary[] = "BIG-5";//繁体码
		$ary[] = "EUC-CN";
		$ary[] = "JIS";//日文编码
		$ary[] = "EUC-JP";//日文编码
		$encoding= self::detect_utf_encoding($str);
		if(empty($encoding) && self::is_gb2312($str)) return 'GBK';
		if(empty($encoding)){
			$encoding=mb_detect_encoding($str,$ary);
		}
		if($encoding=='ASCII') $encoding='UTF-8';
		return $encoding;
	}
	
	public function utf16_to_utf8($str) {
		
		$len = strlen($str);
		$dec = '';
		for ($i = 0; $i < $len; $i += 2) {
			$c = ($be) ? ord($str[$i]) << 8 | ord($str[$i + 1]) : 
					ord($str[$i + 1]) << 8 | ord($str[$i]);
			if ($c >= 0x0001 && $c <= 0x007F) {
				$dec .= chr($c);
			} else if ($c > 0x07FF) {
				$dec .= chr(0xE0 | (($c >> 12) & 0x0F));
				$dec .= chr(0x80 | (($c >>  6) & 0x3F));
				$dec .= chr(0x80 | (($c >>  0) & 0x3F));
			} else {
				$dec .= chr(0xC0 | (($c >>  6) & 0x1F));
				$dec .= chr(0x80 | (($c >>  0) & 0x3F));
			}
		}
		return $dec;
	}
}

?>
