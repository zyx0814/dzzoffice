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
}
?>