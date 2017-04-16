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

function parseflv($url, $width = 0, $height = 0) {
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
				/*$str=file_get_contents($api);
				if(!empty($str) && preg_match("/\"logo\":\"(.+?)\"/i", $str, $image)) {
					$url = substr($image[1], 0, strrpos($image[1], '/')+1);
					$filename = substr($image[1], strrpos($image[1], '/')+2);
					$imgurl = $url.'0'.$filename;
				}*/
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


?>
