<?php
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class helper_security {

	public function removeXSS($val) {
		$val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $val);
		$search = 'abcdefghijklmnopqrstuvwxyz';
		$search.= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$search.= '1234567890!@#$%^&*()';
		$search.= '~`";:?+/={}[]-_|\'\\';
		for ($i = 0; $i < strlen($search); $i++) {
			$val = preg_replace('/(&#[x|X]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val);
			$val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val);
		}
		
		$ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
		$ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
		$ra = array_merge($ra1, $ra2);
		$found = true;
		while ($found == true) {
			$val_before = $val;
			for ($i = 0; $i < sizeof($ra); $i++) {
				$pattern = '/';
				for ($j = 0; $j < strlen($ra[$i]); $j++) {
					if ($j > 0) {
						$pattern .= '(';
						$pattern .= '(&#[x|X]0{0,8}([9][a][b]);?)?';
						$pattern .= '|(&#0{0,8}([9][10][13]);?)?';
						$pattern .= ')?';
					}
					$pattern .= $ra[$i][$j];
				}
				$pattern .= '/i';
				$replacement = substr($ra[$i], 0, 2).''.substr($ra[$i], 2);
				$val = preg_replace($pattern, $replacement, $val);
				if ($val_before == $val) {
					$found = false;
				}
			}
		}
		return $val;
	}
	
	public function checkhtml($html) {
	
			preg_match_all("/\<([^\<]+)\>/is", $html, $ms);
	
			$searchs[] = '<';
			$replaces[] = '&lt;';
			$searchs[] = '>';
			$replaces[] = '&gt;';
			
			if($ms[1]) {
				$allowtags = 'img|a|font|div|table|tbody|caption|tr|td|th|br|p|b|strong|i|u|em|span|ol|ul|li|blockquote|pre|hr|embed|h1|h2|h3|h4|h5|h6';
				$ms[1] = array_unique($ms[1]);
				foreach ($ms[1] as $value) {
					$searchs[] = "&lt;".$value."&gt;";
					
					$value = str_replace('&amp;', '_uch_tmp_str_', $value);
					$value = dhtmlspecialchars($value);
					$value = str_replace('_uch_tmp_str_', '&amp;', $value);
					
	
					$value = str_replace(array('\\','/*'), array('.','/.'), $value);
					$skipkeys = array('onabort','onactivate','onafterprint','onafterupdate','onbeforeactivate','onbeforecopy','onbeforecut','onbeforedeactivate',
							'onbeforeeditfocus','onbeforepaste','onbeforeprint','onbeforeunload','onbeforeupdate','onblur','onbounce','oncellchange','onchange',
							'onclick','oncontextmenu','oncontrolselect','oncopy','oncut','ondataavailable','ondatasetchanged','ondatasetcomplete','ondblclick',
							'ondeactivate','ondrag','ondragend','ondragenter','ondragleave','ondragover','ondragstart','ondrop','onerror','onerrorupdate',
							'onfilterchange','onfinish','onfocus','onfocusin','onfocusout','onhelp','onkeydown','onkeypress','onkeyup','onlayoutcomplete',
							'onload','onlosecapture','onmousedown','onmouseenter','onmouseleave','onmousemove','onmouseout','onmouseover','onmouseup','onmousewheel',
							'onmove','onmoveend','onmovestart','onpaste','onpropertychange','onreadystatechange','onreset','onresize','onresizeend','onresizestart',
							'onrowenter','onrowexit','onrowsdelete','onrowsinserted','onscroll','onselect','onselectionchange','onselectstart','onstart','onstop',
							'onsubmit','onunload','javascript:;','javascript','script','eval','behaviour','expression');
					$skipstr = implode('|', $skipkeys);
					$value = preg_replace(array("/($skipstr)/i"), '.', $value);
					if(!preg_match("/^[\/|\s]?($allowtags)(\s+|$)/is", $value) && !preg_match("/^(br|hr)?(\s+|\/|$)/is", $value)) {
						$value = '';
					}
					$replaces[] = empty($value)?'':"<".str_replace('&quot;', '"', $value).">";
				}
			}
			$html = str_replace($searchs, $replaces, $html);
			
		    return getstr($html, 0, 0, 0, 0, 1);
	}
	/*public function htmlpurifier($html){
		require_once DZZ_ROOT.'./core/class/htmlpurifier/HTMLPurifier.auto.php';
		$config = HTMLPurifier_Config::createDefault();
		$purifier = new HTMLPurifier($config);
		return  $purifier->purify($html);
	}*/
}
?>