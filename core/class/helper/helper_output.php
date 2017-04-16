<?php
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class helper_output {

	protected static function _header() {
		global $_G;
		ob_end_clean();
		$_G['gzipcompress'] ? ob_start('ob_gzhandler') : ob_start();
		@header("Expires: -1");
		@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
		@header("Pragma: no-cache");
		@header("Content-type: text/xml; charset=".CHARSET);
	}

	public static function xml($s) {
		self::_header();
		echo '<?xml version="1.0" encoding="'.CHARSET.'"?>'."\r\n", '<root><![CDATA[', $s, ']]></root>';
		exit();
	}

	public static function json($data) {
		self::_header();
		echo helper_json::encode($data);
		exit();
	}

	public static function html($s) {
		self::_header();
		echo $s;
		exit();
	}
	public static function compressed($string) {
		$search = array ("'<script[^>]*?>.*?</script>'si",  // 去掉 javascript
		                 "'<[/!]*?[^<>]*?>'si",           // 去掉 HTML 标记
		                 "'([rn])[s]+'",                 // 去掉空白字符
		                 "'&(quot|#34);'i",                 // 替换 HTML 实体
		                 "'&(amp|#38);'i",
		                 "'&(lt|#60);'i",
		                 "'&(gt|#62);'i",
		                 "'&(nbsp|#160);'i",
		                 "'&(iexcl|#161);'i",
		                 "'&(cent|#162);'i",
		                 "'&(pound|#163);'i",
		                 "'&(copy|#169);'i",
		                 "'&#(d+);'e");                    // 作为 PHP 代码运行
		$replace = array ("",
		"",
		"",
		"",
		"\1",
		"\"",
		"&",
		"<", 
		">",
		" ",
		chr(161),
		chr(162),
		chr(163),
		chr(169),
		"chr(\1)");
		$text = preg_replace ($search, $replace, $string);
	}	
}
?>