<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
loadcache(array('smilies', 'smileytypes'));

function dzzcode($message , $allowat = 1, $allowsmilies = 1, $allowbbcode = 1,  $allowmediacode = 1, $allowhtml = 0) {
	global $_G;
	
	$msglower = strtolower($message);
	if(!$allowhtml) {
		$message = dhtmlspecialchars($message);
	} else {
		$message = preg_replace("/<script[^\>]*?>(.*?)<\/script>/i", '', $message);
	}
	
	if($allowat) {
		if(strpos($msglower, '[/uid]') !== FALSE) {
			//$message = preg_replace("/\[uid=(\d+)\](.+?)\[\/uid\]/ies", "parseat('\\1', '\\2' ,'uid')", $message);
			$message = preg_replace_callback("/\[uid=(\d+)\](.+?)\[\/uid\]/is", function($matches){ return parseat($matches[1], $matches[2],'uid'); }, $message);
		}
		if(strpos($msglower, '[/org]') !== FALSE) {
			//$message = preg_replace("/\[org=(\d+)\](.+?)\[\/org\]/ies", "parseat('\\1', '\\2','gid')", $message);
			$message = preg_replace_callback("/\[org=(\d+)\](.+?)\[\/org\]/is", function($matches){ return parseat($matches[1], $matches[2],'uid'); }, $message);
		}
	}
	if($allowsmilies) {
		$message = parsesmiles($message);
	}

	/*if($allowbbcode) {
		if(strpos($msglower, 'ed2k://') !== FALSE) {
			$message = preg_replace("/ed2k:\/\/(.+?)\//e", "parseed2k('\\1')", $message);
		}
	}*/

	if($allowbbcode) {
		if(strpos($msglower, '[/url]') !== FALSE) {
			//$message = preg_replace("/\[url(=((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.|mailto:)?([^\r\n\[\"']+?))?\](.+?)\[\/url\]/ies", "parseurl('\\1', '\\5', '\\2')", $message);
			$message = preg_replace_callback("/\[url(=((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.|mailto:)?([^\r\n\[\"']+?))?\](.+?)\[\/url\]/is", function($matches) { return parseurl($matches[1], $matches[5], $matches[2]); }, $message);
 		
 		
		}
		if(strpos($msglower, '[/email]') !== FALSE) {
			//$message = preg_replace("/\[email(=([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+))?\](.+?)\[\/email\]/ies", "parseemail('\\1', '\\4')", $message);
			$message = preg_replace_callback("/\[email(=([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+))?\](.+?)\[\/email\]/is", function($matches) { return parseemail($matches[1], $matches[4]); }, $message);
 		
		}

		$nest = 0;
		while(strpos($msglower, '[table') !== FALSE && strpos($msglower, '[/table]') !== FALSE){
			//$message = preg_replace("/\[table(?:=(\d{1,4}%?)(?:,([\(\)%,#\w ]+))?)?\]\s*(.+?)\s*\[\/table\]/ies", "parsetable('\\1', '\\2', '\\3')", $message);
			$message = preg_replace_callback("/\[table(?:=(\d{1,4}%?)(?:,([\(\)%,#\w ]+))?)?\]\s*(.+?)\s*\[\/table\]/is", function($matches) { return parsetable($matches[1], $matches[2], $matches[3]); }, $message);
 			
			if(++$nest > 4) break;
		}
		//修复UBB标签不闭合造成的问题，理论上所有标签都可以以此方法处理
		$message=preg_replace(array(
										"/\[u\](.+?)\[\/u\]/i",
										"/\[b\](.+?)\[\/b\]/i",
										"/\[s\](.+?)\[\/s\]/i",
										"/\[i\](.+?)\[\/i\]/i"
									),
							  array(
										"[uu]\\1[/uu]",
										"[bb]\\1[/bb]",
										"[ss]\\1[/ss]",
										"[ii]\\1[/ii]",
							       ),$message);
	
		$message = str_replace(array(
			'[/color]', '[/backcolor]', '[/size]', '[/font]', '[/align]', '[bb]', '[/bb]', '[ss]', '[/ss]', '[hr]', '[/p]',
			'[i=s]', '[ii]', '[/ii]', '[uu]', '[/uu]', '[list]', '[list=1]', '[list=a]',
			'[list=A]', "\r\n[*]", '[*]', '[/list]', '[indent]', '[/indent]', '[/float]'
			), array(
			'</font>', '</font>', '</font>', '</font>', '</div>', '<strong>', '</strong>', '<strike>', '</strike>', '<hr class="l" />', '</p>', '<i class="pstatus">', '<i>',
			'</i>', '<u>', '</u>', '<ul>', '<ul type="1" class="litype_1">', '<ul type="a" class="litype_2">',
			'<ul type="A" class="litype_3">', '<li>', '<li>', '</ul>', '<blockquote>', '</blockquote>', '</span>'
			), preg_replace(array(
			"/\[color=([#\w]+?)\]/i",
			"/\[color=((rgb|rgba)\([\d\s,]+?\))\]/i",
			"/\[backcolor=([#\w]+?)\]/i",
			"/\[backcolor=((rgb|rgba)\([\d\s,]+?\))\]/i",
			"/\[size=(\d{1,2}?)\]/i",
			"/\[size=(\d{1,2}(\.\d{1,2}+)?(px|pt)+?)\]/i",
			"/\[font=([^\[\<]+?)\]/i",
			"/\[align=(left|center|right)\]/i",
			"/\[p=(\d{1,2}|null), (\d{1,2}|null), (left|center|right)\]/i",
			"/\[float=left\]/i",
			"/\[float=right\]/i"

			), array(
			"<font color=\"\\1\">",
			"<font style=\"color:\\1\">",
			"<font style=\"background-color:\\1\">",
			"<font style=\"background-color:\\1\">",
			"<font size=\"\\1\">",
			"<font style=\"font-size:\\1\">",
			"<font face=\"\\1\">",
			"<div align=\"\\1\">",
			"<p style=\"line-height:\\1px;text-indent:\\2em;text-align:\\3\">",
			"<span style=\"float:left;margin-right:5px\">",
			"<span style=\"float:right;margin-left:5px\">"
			), $message));

		if($allowmediacode){
			if(!defined('IN_MOBILE')) {
				if(strpos($msglower, '[/media]') !== FALSE) {
					//$message = preg_replace("/\[media=([\w,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/ies", $allowmediacode ? "parsemedia('\\1', '\\2')" : "bbcodeurl('\\2', '<a href=\"{url}\" target=\"_blank\">{url}</a>')", $message);
					$message = preg_replace_callback("/\[media=([\w,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/is", function($matches) use($allowmediacode) { return $allowmediacode ? parsemedia($matches[1], $matches[2]) : bbcodeurl($matches[2], '<a href="{url}" target="_blank">{url}</a>'); }, $message);
 			
				}
				if(strpos($msglower, '[/audio]') !== FALSE) {
					//$message = preg_replace("/\[audio(=1)*\]\s*([^\[\<\r\n]+?)\s*\[\/audio\]/ies", $allowmediacode ? "parseaudio('\\2', 400)" : "bbcodeurl('\\2', '<a href=\"{url}\" target=\"_blank\">{url}</a>')", $message);
					$message = preg_replace_callback("/\[audio(=1)*\]\s*([^\[\<\r\n]+?)\s*\[\/audio\]/is", function($matches) use($allowmediacode) { return $allowmediacode ? parseaudio($matches[2], 400) : bbcodeurl($matches[2], '<a href="{url}" target="_blank">{url}</a>'); }, $message);
 			
				}
				if(strpos($msglower, '[/flash]') !== FALSE) {
					//$message = preg_replace("/\[flash(=(\d+),(\d+))?\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/ies", $allowmediacode ? "parseflash('\\2', '\\3', '\\4');" : "bbcodeurl('\\4', '<a href=\"{url}\" target=\"_blank\">{url}</a>')", $message);
					$message = preg_replace_callback("/\[flash(=(\d+),(\d+))?\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/is", function($matches) use($allowmediacode) { return $allowmediacode ? parseflash($matches[2], $matches[3], $matches[4]) : bbcodeurl($matches[4], '<a href="{url}" target="_blank">{url}</a>'); }, $message);
 			
				}
			} else {
				if(strpos($msglower, '[/media]') !== FALSE) {
					$message = preg_replace("/\[media=([\w,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/is", "[media]\\2[/media]", $message);
				}
				if(strpos($msglower, '[/audio]') !== FALSE) {
					$message = preg_replace("/\[audio(=1)*\]\s*([^\[\<\r\n]+?)\s*\[\/audio\]/is", "[media]\\2[/media]", $message);
				}
				if(strpos($msglower, '[/flash]') !== FALSE) {
					$message = preg_replace("/\[flash(=(\d+),(\d+))?\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/is", "[media]\\4[/media]", $message);
				}
			}
		}
		$attrsrc =  'src';
		$allowimgcode=1;
		if(strpos($msglower, '[/img]') !== FALSE) {
			/*$message = preg_replace(array(
				"/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies",
				"/\[img=(\d{1,4})[x|\,](\d{1,4})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies"
			), $allowimgcode ? array(
				"parseimg(0, 0, '\\1')",
				"parseimg('\\1', '\\2', '\\3')"
			) : ($allowbbcode ? array(
				(!defined('IN_MOBILE') ? "bbcodeurl('\\1', '<a href=\"{url}\" target=\"_blank\">{url}</a>')" : "bbcodeurl('\\1', '')"),
				(!defined('IN_MOBILE') ? "bbcodeurl('\\3', '<a href=\"{url}\" target=\"_blank\">{url}</a>')" : "bbcodeurl('\\3', '')"),
			) : array("bbcodeurl('\\1', '{url}')", "bbcodeurl('\\3', '{url}')")), $message);*/
			
			
			$message = preg_replace_callback("/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is", function($matches) { return  parseimg(0, 0, $matches[1]); }, $message);
			$message = preg_replace_callback("/\[img=(\d{1,4})[x|\,](\d{1,4})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is", function($matches) { return parseimg($matches[1], $matches[2], $matches[3]);}, $message);
 		
		}
	}
	

	
	unset($msglower);

	
	return $allowhtml ? $message : nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));
}
function parseurl($url, $text, $scheme) {
	global $_G;
	if(!$url && preg_match("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.)[^\[\"']+/i", trim($text), $matches)) {
		$url = $matches[0];
		$length = 65;
		if(strlen($url) > $length) {
			$text = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3));
		}
		return '<a href="'.(substr(strtolower($url), 0, 4) == 'www.' ? 'http://'.$url : $url).'" target="_blank">'.$text.'</a>';
	} else {
		$url = substr($url, 1);
		if(substr(strtolower($url), 0, 4) == 'www.') {
			$url = 'http://'.$url;
		}
		$url = !$scheme ? $_G['siteurl'].$url : $url;
		return '<a href="'.$url.'" target="_blank">'.$text.'</a>';
	}
}
function parseat($uid,$text,$idtype='uid'){
	if($idtype=='uid')	return '<a href="user.php?uid='.$uid.'">'.$text.'</a>';
	elseif($idtype=='gid'){
		return '<a href="javascript:;">'.$text.'</a>';
	}
}
function parseed2k($url) {
	global $_G;
	list(,$type, $name, $size,) = explode('|', $url);
	$url = 'ed2k://'.$url.'/';
	$name = addslashes($name);
	if($type == 'file') {
		$ed2kid = 'ed2k_'.random(3);
		return '<a id="'.$ed2kid.'" href="'.$url.'" target="_blank">'.dhtmlspecialchars(urldecode($name)).' ('.sizecount($size).')</a><script language="javascript">$(\''.$ed2kid.'\').innerHTML=htmlspecialchars(unescape(decodeURIComponent(\''.$name.'\')))+\' ('.sizecount($size).')\';</script>';
	} else {
		return '<a href="'.$url.'" target="_blank">'.$url.'</a>';
	}
}
function parseflash($w, $h, $url) {
	$w = !$w ? 550 : $w;
	$h = !$h ? 400 : $h;
	preg_match("/((https?){1}:\/\/|www\.)[^\r\n\[\"'\?]+(\.swf|\.flv)(\?[^\r\n\[\"'\?]+)?/i", $url, $matches);
	$url = $matches[0];
	$randomid = 'swf_'.random(3);
	if(fileext($url) != 'flv') {
		return '<span id="'.$randomid.'"></span><script type="text/javascript" reload="1">$(\''.$randomid.'\').innerHTML=AC_FL_RunContent(\'width\', \''.$w.'\', \'height\', \''.$h.'\', \'allowNetworking\', \'internal\', \'allowScriptAccess\', \'never\', \'src\', encodeURI(\''.$url.'\'), \'quality\', \'high\', \'bgcolor\', \'#ffffff\', \'wmode\', \'transparent\', \'allowfullscreen\', \'true\');</script>';
	} else {
		return '<span id="'.$randomid.'"></span><script type="text/javascript" reload="1">$(\''.$randomid.'\').innerHTML=AC_FL_RunContent(\'width\', \''.$w.'\', \'height\', \''.$h.'\', \'allowNetworking\', \'internal\', \'allowScriptAccess\', \'never\', \'src\', \''.STATICURL.'image/common/flvplayer.swf\', \'flashvars\', \'file='.rawurlencode($url).'\', \'quality\', \'high\', \'wmode\', \'transparent\', \'allowfullscreen\', \'true\');</script>';
	}
	
}
function parseemail($email, $text) {
	$text = str_replace('\"', '"', $text);
	if(!$email && preg_match("/\s*([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\s*/i", $text, $matches)) {
		$email = trim($matches[0]);
		return '<a href="mailto:'.$email.'">'.$email.'</a>';
	} else {
		return '<a href="mailto:'.substr($email, 1).'">'.$text.'</a>';
	}
}
function parsetable($width, $bgcolor, $message) {
	
	if(strpos($message, '[/tr]') === FALSE && strpos($message, '[/td]') === FALSE) {
		$rows = explode("\n", $message);
		$s = !defined('IN_MOBILE') ? '<table cellspacing="0" class="t_table" '.
			($width == '' ? NULL : 'style="width:'.$width.'"').
			($bgcolor ? ' bgcolor="'.$bgcolor.'">' : '>') : '<table>';
		foreach($rows as $row) {
			$s .= '<tr><td>'.str_replace(array('\|', '|', '\n'), array('&#124;', '</td><td>', "\n"), $row).'</td></tr>';
		}
		$s .= '</table>';
		return $s;
	} else {
		if(!preg_match("/^\[tr(?:=([\(\)\s%,#\w]+))?\]\s*\[td([=\d,%]+)?\]/", $message) && !preg_match("/^<tr[^>]*?>\s*<td[^>]*?>/", $message)) {
			return str_replace('\\"', '"', preg_replace("/\[tr(?:=([\(\)\s%,#\w]+))?\]|\[td([=\d,%]+)?\]|\[\/td\]|\[\/tr\]/", '', $message));
		}
		if(substr($width, -1) == '%') {
			$width = substr($width, 0, -1) <= 98 ? intval($width).'%' : '98%';
		} else {
			$width = intval($width);
			$width = $width ? ($width <= 560 ? $width.'px' : '98%') : '';
		}
		$message = preg_replace_callback("/\[tr(?:=([\(\)\s%,#\w]+))?\]\s*\[td(?:=(\d{1,4}%?))?\]/i", function($matches){
			return parsetrtd($matches[1], 0, 0, $matches[2]);
		}, $message);
		$message = preg_replace_callback("/\[\/td\]\s*\[td(?:=(\d{1,4}%?))?\]/i", function($matches){
			return parsetrtd('td', 0, 0, $matches[1]);
		}, $message);
		$message = preg_replace_callback("/\[tr(?:=([\(\)\s%,#\w]+))?\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/i", function($matches){
			return parsetrtd($matches[1], $matches[2], $matches[3], $matches[4]);
		}, $message);
		$message = preg_replace_callback("/\[\/td\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/i", function($matches){
			return parsetrtd('td', $matches[1], $matches[2], $matches[3]);	
		}, $message);
		$message = preg_replace("/\[\/td\]\s*\[\/tr\]\s*/i", '</td></tr>', $message);
		return (!defined('IN_MOBILE') ? '<table cellspacing="0" class="t_table" '.
			($width == '' ? NULL : 'style="width:'.$width.'"').
			($bgcolor ? ' bgcolor="'.$bgcolor.'">' : '>') : '<table>').
			str_replace('\\"', '"', $message).'</table>';
	}
}

function parsetrtd($bgcolor, $colspan, $rowspan, $width) {
	return ($bgcolor == 'td' ? '</td>' : '<tr'.($bgcolor && !defined('IN_MOBILE') ? ' style="background-color:'.$bgcolor.'"' : '').'>').'<td'.($colspan > 1 ? ' colspan="'.$colspan.'"' : '').($rowspan > 1 ? ' rowspan="'.$rowspan.'"' : '').($width && !defined('IN_MOBILE') ? ' width="'.$width.'"' : '').'>';
}

function parsesmiles($message) {
	global $_G;
	static $enablesmiles;
	if($enablesmiles === null) {
		$enablesmiles = false;
		if(!empty($_G['cache']['smilies']) && is_array($_G['cache']['smilies'])) {
			foreach($_G['cache']['smilies']['replacearray'] as $key => $smiley) {
				$_G['cache']['smilies']['replacearray'][$key] = '<img class="img-emotion" src="'.STATICURL.'image/smiley/'.$_G['cache']['smileytypes'][$_G['cache']['smilies']['typearray'][$key]]['directory'].'/'.$smiley.'" smilieid="'.$key.'" border="0" alt="" />';
			}
			$enablesmiles = true;
		}
	}
	
	$enablesmiles && $message = preg_replace($_G['cache']['smilies']['searcharray'], $_G['cache']['smilies']['replacearray'], $message);
	
	return $message;
}
function parseimg($width, $height, $src) {
	
	$img = '<img'.($width > 0 ? ' width="'.$width.'"' : '').($height > 0 ? ' height="'.$height.'"' : '').' src="{url}" border="0" alt="" />';
	
	$code = bbcodeurl($src, $img);
	
	return $code;
}
function parsemedia($params, $url) {
	$params = explode(',', $params);
	$width = intval($params[1]) > 800 ? 800 : intval($params[1]);
	$height = intval($params[2]) > 600 ? 600 : intval($params[2]);

	$url = addslashes($url);
        if(!in_array(strtolower(substr($url, 0, 6)), array('http:/', 'https:', 'ftp://', 'rtsp:/', 'mms://')) && !preg_match('/^static\//', $url) && !preg_match('/^data\//', $url)) {
		$url = 'http://'.$url;
	}
	
	if($flv = parseflv($url, $width, $height)) {
		return $flv;
	} 
	if(in_array(count($params), array(3, 4))) {
		$type = $params[0];
		$url = htmlspecialchars(str_replace(array('<', '>'), '', str_replace('\\"', '\"', $url)));
		switch($type) {
			case 'mp3':
			case 'wma':
			case 'ra':
			case 'ram':
			case 'wav':
			case 'mid':
				return parseaudio($url, $width);
			case 'rm':
			case 'rmvb':
			case 'rtsp':
				$mediaid = 'media_'.random(3);
				return '<object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="'.$width.'" height="'.$height.'"><param name="autostart" value="0" /><param name="src" value="'.$url.'" /><param name="controls" value="imagewindow" /><param name="console" value="'.$mediaid.'_" /><embed src="'.$url.'" autostart="0" type="audio/x-pn-realaudio-plugin" controls="imagewindow" console="'.$mediaid.'_" width="'.$width.'" height="'.$height.'"></embed></object><br /><object classid="clsid:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA" width="'.$width.'" height="32"><param name="src" value="'.$url.'" /><param name="controls" value="controlpanel" /><param name="console" value="'.$mediaid.'_" /><embed src="'.$url.'" autostart="0" type="audio/x-pn-realaudio-plugin" controls="controlpanel" console="'.$mediaid.'_" width="'.$width.'" height="32"></embed></object>';
			case 'flv':
				$randomid = 'flv_'.random(3);
				return '<span id="'.$randomid.'"></span><script type="text/javascript" reload="1">$(\''.$randomid.'\').innerHTML=AC_FL_RunContent(\'width\', \''.$width.'\', \'height\', \''.$height.'\', \'allowNetworking\', \'internal\', \'allowScriptAccess\', \'never\', \'src\', \''.STATICURL.'image/common/flvplayer.swf\', \'flashvars\', \'file='.rawurlencode($url).'\', \'quality\', \'high\', \'wmode\', \'transparent\', \'allowfullscreen\', \'true\');</script>';
			case 'swf':
				$randomid = 'swf_'.random(3);
				return '<span id="'.$randomid.'"></span><script type="text/javascript" reload="1">$(\''.$randomid.'\').innerHTML=AC_FL_RunContent(\'width\', \''.$width.'\', \'height\', \''.$height.'\', \'allowNetworking\', \'internal\', \'allowScriptAccess\', \'never\', \'src\', encodeURI(\''.$url.'\'), \'quality\', \'high\', \'bgcolor\', \'#ffffff\', \'wmode\', \'transparent\', \'allowfullscreen\', \'true\');</script>';
			case 'asf':
			case 'asx':
			case 'wmv':
			case 'mms':
			case 'avi':
			case 'mpg':
			case 'mpeg':
				return '<object classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="'.$width.'" height="'.$height.'"><param name="invokeURLs" value="0"><param name="autostart" value="1" /><param name="url" value="'.$url.'" /><embed src="'.$url.'" autostart="0" type="application/x-mplayer2" width="'.$width.'" height="'.$height.'"></embed></object>';
			case 'mov':
				return '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" width="'.$width.'" height="'.$height.'"><param name="autostart" value="false" /><param name="src" value="'.$url.'" /><embed src="'.$url.'" autostart="false" type="video/quicktime" controller="true" width="'.$width.'" height="'.$height.'"></embed></object>';
			default:
				return '<a href="'.$url.'" target="_blank">'.$url.'</a>';
		}
	}
	return;
}
function parseflv($url, $width = 0, $height = 0) {
    global $_G;
    $lowerurl = strtolower($url);
    $flv = '';
    $imgurl = '';
    if($lowerurl != str_replace(array('player.youku.com/player.php/sid/','tudou.com/v/','player.ku6.com/refer/'), '', $lowerurl)) {
        $flv = $url;
    } elseif(strpos($lowerurl, 'v.youku.com/v_show/') !== FALSE) {
        if(preg_match("/http:\/\/v.youku.com\/v_show\/id_([\w=]+)(.html)(.*?)$/i", $url, $matches)) {
            $flv = 'http://player.youku.com/player.php/sid/'.$matches[1].'/v.swf';
            //exit($flv.'==='.'http://v.youku.com/player/getPlayList/VideoIDS/'.$matches[1]);
            if(!$width && !$height) {
                $api='https://openapi.youku.com/v2/videos/show_basic.json?video_id='.$matches[1].'&client_id=b10ab8588528b1b1';
                //$api = 'http://v.youku.com/player/getPlayList/VideoIDS/'.$matches[1];
                $json = json_decode(dzz_file_get_contents($api),true);

                if(is_array($json)){
                    $imgurl=$json['thumbnail'];
                }

            }
        }
        //http://www.tudou.com/programs/view/TCwDFnpZuH8/
    } elseif(strpos($lowerurl, 'tudou.com/programs/view/') !== FALSE) {
        if(preg_match("/http:\/\/(www.)?tudou.com\/programs\/view\/([^\/]+)/i", $url, $matches)) {
            $flv = 'http://www.tudou.com/v/'.$matches[2];
            if(!$width && !$height) {
                $str = dzz_file_get_contents($url);
                if(!empty($str) && preg_match("/pic:\s\'(.+?)\'/i", $str, $image)) {
                    $imgurl = trim($image[1]);
                }
            }
        }
    } elseif(strpos($lowerurl, 'v.ku6.com/show/') !== FALSE) {
        if(preg_match("/http:\/\/v.ku6.com\/show\/([^\/]+).html/i", $url, $matches)) {
            $flv = 'http://player.ku6.com/refer/'.$matches[1].'/v.swf';
            if(!$width && !$height) {
                $api = 'http://vo.ku6.com/fetchVideo4Player/1/'.$matches[1].'.html';
                $str = dzz_file_get_contents($api);
                if(!empty($str) && preg_match("/\"picpath\":\"(.+?)\"/i", $str, $image)) {
                    $imgurl = str_replace(array('\u003a', '\u002e'), array(':', '.'), $image[1]);
                }
            }
        }
    } elseif(strpos($lowerurl, 'v.ku6.com/special/show_') !== FALSE) {
        if(preg_match("/http:\/\/v.ku6.com\/special\/show_\d+\/([^\/]+).html/i", $url, $matches)) {
            $flv = 'http://player.ku6.com/refer/'.$matches[1].'/v.swf';
            if(!$width && !$height) {
                $api = 'http://vo.ku6.com/fetchVideo4Player/1/'.$matches[1].'.html';
                $str = dzz_file_get_contents($api);
                if(!empty($str) && preg_match("/\"picpath\":\"(.+?)\"/i", $str, $image)) {
                    $imgurl = str_replace(array('\u003a', '\u002e'), array(':', '.'), $image[1]);
                }
            }
        }
    } elseif(strpos($lowerurl, 'www.youtube.com/watch?') !== FALSE) {
        if(preg_match("/http:\/\/www.youtube.com\/watch\?v=([^\/&]+)&?/i", $url, $matches)) {
            $flv = 'http://www.youtube.com/v/'.$matches[1].'&hl=zh_CN&fs=1';
            if(!$width && !$height) {
                $str = dzz_file_get_contents($url);
                if(!empty($str) && preg_match("/'VIDEO_HQ_THUMB':\s'(.+?)'/i", $str, $image)) {
                    $url = substr($image[1], 0, strrpos($image[1], '/')+1);
                    $filename = substr($image[1], strrpos($image[1], '/')+3);
                    $imgurl = $url.$filename;
                }
            }
        }
    } elseif(strpos($lowerurl, 'tv.mofile.com/') !== FALSE) {
        if(preg_match("/http:\/\/tv.mofile.com\/([^\/]+)/i", $url, $matches)) {
            $flv = 'http://tv.mofile.com/cn/xplayer.swf?v='.$matches[1];
            if(!$width && !$height) {
                $str = dzz_file_get_contents($url);
                if(!empty($str) && preg_match("/thumbpath=\"(.+?)\";/i", $str, $image)) {
                    $imgurl = trim($image[1]);
                }
            }
        }
    } elseif(strpos($lowerurl, 'v.mofile.com/show/') !== FALSE) {
        if(preg_match("/http:\/\/v.mofile.com\/show\/([^\/]+).shtml/i", $url, $matches)) {
            $flv = 'http://tv.mofile.com/cn/xplayer.swf?v='.$matches[1];
            if(!$width && !$height) {
                $str = dzz_file_get_contents($url);
                if(!empty($str) && preg_match("/thumbpath=\"(.+?)\";/i", $str, $image)) {
                    $imgurl = trim($image[1]);
                }
            }
        }
        //http://you.video.sina.com.cn/b/9809684-1268992255.html
    } elseif(strpos($lowerurl, 'you.video.sina.com.cn/b/') !== FALSE) {
        if(preg_match("/http:\/\/you.video.sina.com.cn\/b\/(\d+)-(\d+).html/i", $url, $matches)) {
            $flv = 'http://vhead.blog.sina.com.cn/player/outer_player.swf?vid='.$matches[1];
            if(!$width && !$height) {
                $api = 'http://interface.video.sina.com.cn/interface/common/getVideoImage.php?vid='.$matches[1];
                $str = dzz_file_get_contents($api);
                if(!empty($str)) {
                    $imgurl = str_replace('imgurl=', '', trim($str));
                }
            }
        }
    } elseif(strpos($lowerurl, 'http://v.blog.sohu.com/u/') !== FALSE) {
        if(preg_match("/http:\/\/v.blog.sohu.com\/u\/[^\/]+\/(\d+)/i", $url, $matches)) {
            $flv = 'http://v.blog.sohu.com/fo/v4/'.$matches[1];
            if(!$width && !$height) {
                $api = 'http://v.blog.sohu.com/videinfo.jhtml?m=view&id='.$matches[1].'&outType=3';
                $str = dzz_file_get_contents($api);
                if(!empty($str) && preg_match("/\"cutCoverURL\":\"(.+?)\"/i", $str, $image)) {
                    $imgurl = str_replace(array('\u003a', '\u002e'), array(':', '.'), $image[1]);
                }
            }
        }
    } elseif(strpos($lowerurl, 'http://www.ouou.com/fun_funview') !== FALSE) {
        $str = dzz_file_get_contents($url);
        if(!empty($str) && preg_match("/var\sflv\s=\s'(.+?)';/i", $str, $matches)) {
            $flv = $_G['style']['imgdir'].'/flvplayer.swf?&autostart=true&file='.urlencode($matches[1]);
            if(!$width && !$height && preg_match("/var\simga=\s'(.+?)';/i", $str, $image)) {
                $imgurl = trim($image[1]);
            }
        }
    } elseif(strpos($lowerurl, 'http://www.56.com') !== FALSE) {

        if(preg_match("/http:\/\/www.56.com\/\S+\/play_album-aid-(\d+)_vid-(.+?).html/i", $url, $matches)) {
            $flv = 'http://player.56.com/v_'.$matches[2].'.swf';
            $matches[1] = $matches[2];
        } elseif(preg_match("/http:\/\/www.56.com\/\S+\/([^\/]+).html/i", $url, $matches)) {
            $flv = 'http://player.56.com/'.$matches[1].'.swf';
        }
        if(!$width && !$height && !empty($matches[1])) {
            $api = 'http://vxml.56.com/json/'.str_replace('v_', '', $matches[1]).'/?src=out';
            $str = dzz_file_get_contents($api);
            if(!empty($str) && preg_match("/\"img\":\"(.+?)\"/i", $str, $image)) {
                $imgurl = trim($image[1]);
            }
        }
    }
    if($flv) {
        if(!$width && !$height) {
            return array('url' => $flv, 'img' => $imgurl);
        } else {
            $width = addslashes($width);
            $height = addslashes($height);
            $flv = addslashes($flv);
            $randomid = 'flv_'.random(3);
            return '<span id="'.$randomid.'"></span><script type="text/javascript" reload="1">$(\''.$randomid.'\').innerHTML=AC_FL_RunContent(\'width\', \''.$width.'\', \'height\', \''.$height.'\', \'allowNetworking\', \'internal\', \'allowScriptAccess\', \'never\', \'src\', \''.$flv.'\', \'quality\', \'high\', \'bgcolor\', \'#ffffff\', \'wmode\', \'transparent\', \'allowfullscreen\', \'true\');</script>';
        }
    } else {
        return FALSE;
    }
}
function bbcodeurl($url, $tags) {
	if(!preg_match("/<.+?>/s", $url)) {
		if(!in_array(strtolower(substr($url, 0, 6)), array('http:/', 'https:', 'ftp://', 'rtsp:/', 'mms://')) && !preg_match('/^static\//', $url) && !preg_match('/^data\//', $url)) {
			$url = 'http://'.$url;
		}
		return str_replace(array('submit', 'member.php?mod=logging'), array('', ''), str_replace('{url}', addslashes($url), $tags));
	} else {
		return '&nbsp;'.$url;
	}
}
function parseaudio($url, $width = 400) {
	$ext = strtolower(substr(strrchr($url, '.'), 1, 5));
	switch($ext) {
		case 'mp3':
			$randomid = 'mp3_'.random(3);
			return '<span id="'.$randomid.'"></span><script type="text/javascript" reload="1">$(\''.$randomid.'\').innerHTML=AC_FL_RunContent(\'FlashVars\', \'soundFile='.urlencode($url).'\', \'width\', \'290\', \'height\', \'24\', \'allowNetworking\', \'internal\', \'allowScriptAccess\', \'never\', \'src\', \''.STATICURL.'image/common/player.swf\', \'quality\', \'high\', \'bgcolor\', \'#FFFFFF\', \'menu\', \'false\', \'wmode\', \'transparent\', \'allowNetworking\', \'internal\');</script>';
		case 'wma':
		case 'mid':
		case 'wav':
			return '<object classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="'.$width.'" height="64"><param name="invokeURLs" value="0"><param name="autostart" value="0" /><param name="url" value="'.$url.'" /><embed src="'.$url.'" autostart="0" type="application/x-mplayer2" width="'.$width.'" height="64"></embed></object>';
		case 'ra':
		case 'rm':
		case 'ram':
			$mediaid = 'media_'.random(3);
			return '<object classid="clsid:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA" width="'.$width.'" height="32"><param name="autostart" value="0" /><param name="src" value="'.$url.'" /><param name="controls" value="controlpanel" /><param name="console" value="'.$mediaid.'_" /><embed src="'.$url.'" autostart="0" type="audio/x-pn-realaudio-plugin" controls="ControlPanel" console="'.$mediaid.'_" width="'.$width.'" height="32"></embed></object>';
	}
}


?>
