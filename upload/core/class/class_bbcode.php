<?php
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class bbcode {

	var $search_exp = array();
	var $replace_exp = array();
	var $search_str = array();
	var $replace_str = array();
	var $html_s_exp = array();
	var $html_r_exp = array();
	var $html_s_str = array();
	var $html_r_str = array();

	function &instance() {
		static $object;
		if(empty($object)) {
			$object = new bbcode();
		}
		return $object;
	}

	function bbcode() {
	}

	function bbcode2html($message, $parseurl=0) {
		if(empty($this->search_exp)) {
			$this->search_exp = array(
				"/\s*\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s*/is",
				"/\[url\]\s*(https?:\/\/|ftp:\/\/|gopher:\/\/|news:\/\/|telnet:\/\/|rtsp:\/\/|mms:\/\/|callto:\/\/|ed2k:\/\/){1}([^\[\"']+?)\s*\[\/url\]/i",
				"/\[em:([0-9]+):\]/i",
			);
			$this->replace_exp = array(
				"<div class=\"quote\"><blockquote>\\1</blockquote></div>",
				"<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>",
				" <img src=\"".STATICURL."image/smiley/comcom/\\1.gif\" class=\"vm\"> "
			);
			$this->replace_exp[] = '$this->bb_img(\'\\1\')';
			$this->search_str = array('[b]', '[/b]','[i]', '[/i]', '[u]', '[/u]');
			$this->replace_str = array('<b>', '</b>', '<i>','</i>', '<u>', '</u>');
		}

		if($parseurl==2) {
			$message = bbcode::parseurl($message);
		}

		@$message = preg_replace($this->search_exp, $this->replace_exp, $message, 20);

		if($parseurl==2) {
			@$message = preg_replace_callback("/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is", array($this, 'bbcode2html_callback_bb_img_1'), $message, 20);
		}

		@$message = str_replace($this->search_str, $this->replace_str, $message);
		return nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));
	}

	function bbcode2html_callback_bb_img_1($matches) {
		return $this->bb_img($matches[1]);
	}

	function parseurl($message) {
		return preg_replace("/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp|gopher|news|telnet|mms|rtsp):\/\/)([a-z0-9\/\-_+=.~!%@?#%&;:$\\()|]+)/i", "[url]\\1\\3[/url]", ' '.$message);
	}

	function html2bbcode($message) {

		if(empty($this->html_s_exp)) {
			$this->html_s_exp = array(
					"/\<div class=\"quote\"\>\<blockquote\>(.*?)\<\/blockquote\>\<\/div\>/is",
					"/\<a href=\"(.+?)\".*?\<\/a\>/is",
					"/(\r\n|\n|\r)/",
					"/<br.*>/siU",
					"/[ \t]*\<img src=\"static\/image\/smiley\/comcom\/(.+?).gif\".*?\>[ \t]*/is",
					"/\s*\<img src=\"(.+?)\".*?\>\s*/is"
				);
				$this->html_r_exp = array(
					"[quote]\\1[/quote]",
					"\\1",
					'',
					"\n",
					"[em:\\1:]",
					"\n[img]\\1[/img]\n"
			);
			$this->html_s_str = array('<b>', '</b>', '<i>','</i>', '<u>', '</u>', '&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;', '&lt;', '&gt;', '&amp;');
			$this->html_r_str = array('[b]', '[/b]','[i]', '[/i]', '[u]', '[/u]', "\t", '   ', '  ', '<', '>', '&');
		}

		@$message = str_replace($this->html_s_str, $this->html_r_str,
		preg_replace($this->html_s_exp, $this->html_r_exp, $message));

		$message = dhtmlspecialchars($message);

		return trim($message);
	}

	function bb_img($url) {
		global $_G;

		if(!in_array(strtolower(substr($url, 0, 6)), array('http:/', 'https:', 'ftp://', 'rtsp:/', 'mms://'))) {
			$url = isset($_G['siteurl']) && !empty($_G['siteurl']) ? $_G['siteurl'].$url : 'http://'.$url;
		}
		$url = addslashes($url);
		return "<img src=\"$url\" class=\"vm\">";
	}
}

?>