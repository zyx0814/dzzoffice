<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class seccode {

	var $code;
	var $type 	= 0;
	var $width 	= 150;
	var $height 	= 60;
	var $background	= 1;
	var $adulterate	= 1;
	var $ttf 	= 0;
	var $angle 	= 0;
	var $warping 	= 0;
	var $scatter	= 0;
	var $color 	= 1;
	var $size 	= 0;
	var $shadow 	= 1;
	var $animator 	= 0;
	var $fontpath	= '';
	var $datapath	= '';
	var $includepath= '';

	var $fontcolor;
	var $im;

	function display() {
		$this->type == 2 && !extension_loaded('ming') && $this->type = 0;
		if($this->type < 2 && function_exists('imagecreate') && function_exists('imagecolorset') && function_exists('imagecopyresized') &&
			function_exists('imagecolorallocate') && function_exists('imagechar') && function_exists('imagecolorsforindex') &&
			function_exists('imageline') && function_exists('imagecreatefromstring') && (function_exists('imagegif') || function_exists('imagepng') || function_exists('imagejpeg'))) {
			$this->image();
		} elseif($this->type == 2 && extension_loaded('ming')) {
			$this->flash();
		} elseif($this->type == 3) {
			$this->audio();
		} else {
			$this->bitmap();
		}
	}

	function image() {
		$bgcontent = $this->background();

		if($this->animator == 1 && function_exists('imagegif')) {
			include_once $this->includepath.'class_gifmerge.php';
			$trueframe = mt_rand(1, 9);

			for($i = 0; $i <= 9; $i++) {
				$this->im = imagecreatefromstring($bgcontent);
				$x[$i] = $y[$i] = 0;
				$this->adulterate && $this->adulterate();
				if($i == $trueframe) {
					$this->ttf && function_exists('imagettftext') || $this->type == 1 ? $this->ttffont() : $this->giffont();
					$d[$i] = mt_rand(250, 400);
					$this->scatter && $this->scatter($this->im);
				} else {
					$this->adulteratefont();
					$d[$i] = mt_rand(5, 15);
					$this->scatter && $this->scatter($this->im, 1);
				}
				ob_start();
				imagegif($this->im);
				imagedestroy($this->im);
				$frame[$i] = ob_get_contents();
				ob_end_clean();
			}
			$anim = new GifMerge($frame, 255, 255, 255, 0, $d, $x, $y, 'C_MEMORY');
			header('Content-type: image/gif');
			echo $anim->getAnimation();
		} else {
			$this->im = imagecreatefromstring($bgcontent);
			$this->adulterate && $this->adulterate();
			$this->ttf && function_exists('imagettftext') || $this->type == 1 ? $this->ttffont() : $this->giffont();
			$this->scatter && $this->scatter($this->im);

			if(function_exists('imagepng')) {
				header('Content-type: image/png');
				imagepng($this->im);
			} else {
				header('Content-type: image/jpeg');
				imagejpeg($this->im, '', 100);
			}
			imagedestroy($this->im);
		}
	}

	function background() {
		$this->im = imagecreatetruecolor($this->width, $this->height);
		$backgrounds = $c = array();
		if($this->background && function_exists('imagecreatefromjpeg') && function_exists('imagecolorat') && function_exists('imagecopymerge') &&
			function_exists('imagesetpixel') && function_exists('imageSX') && function_exists('imageSY')) {
			if($handle = @opendir($this->datapath.'background/')) {
				while($bgfile = @readdir($handle)) {
					if(preg_match('/\.jpg$/i', $bgfile)) {
						$backgrounds[] = $this->datapath.'background/'.$bgfile;
					}
				}
				@closedir($handle);
			}
			if($backgrounds) {
				$imwm = imagecreatefromjpeg($backgrounds[array_rand($backgrounds)]);
				$colorindex = imagecolorat($imwm, 0, 0);
				$c = imagecolorsforindex($imwm, $colorindex);
				$colorindex = imagecolorat($imwm, 1, 0);
				imagesetpixel($imwm, 0, 0, $colorindex);
				$c[0] = $c['red'];$c[1] = $c['green'];$c[2] = $c['blue'];
				imagecopymerge($this->im, $imwm, 0, 0, mt_rand(0, 200 - $this->width), mt_rand(0, 80 - $this->height), imageSX($imwm), imageSY($imwm), 100);
				imagedestroy($imwm);
			}
		}
		if(!$this->background || !$backgrounds) {
			for($i = 0;$i < 3;$i++) {
				$start[$i] = mt_rand(200, 255);$end[$i] = mt_rand(100, 150);$step[$i] = ($end[$i] - $start[$i]) / $this->width;$c[$i] = $start[$i];
			}
			for($i = 0;$i < $this->width;$i++) {
				$color = imagecolorallocate($this->im, $c[0], $c[1], $c[2]);
				imageline($this->im, $i, 0, $i, $this->height, $color);
				$c[0] += $step[0];$c[1] += $step[1];$c[2] += $step[2];
			}
			$c[0] -= 20;$c[1] -= 20;$c[2] -= 20;
		}
		ob_start();
		if(function_exists('imagepng')) {
			imagepng($this->im);
		} else {
			imagejpeg($this->im, '', 100);
		}
		imagedestroy($this->im);
		$bgcontent = ob_get_contents();
		ob_end_clean();
		$this->fontcolor = $c;
		return $bgcontent;
	}

	function adulterate() {
		$linenums = $this->height / 10;
		for($i = 0; $i <= $linenums;$i++) {
			$color = $this->color ? imagecolorallocate($this->im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)) : imagecolorallocate($this->im, $this->fontcolor[0], $this->fontcolor[1], $this->fontcolor[2]);
			$x = mt_rand(0, $this->width);
			$y = mt_rand(0, $this->height);
			if(mt_rand(0, 1)) {
				$w = mt_rand(0, $this->width);
				$h = mt_rand(0, $this->height);
				$s = mt_rand(0, 360);
				$e = mt_rand(0, 360);
				for($j = 0;$j < 3;$j++) {
					imagearc($this->im, $x + $j, $y, $w, $h, $s, $e, $color);
				}
			} else {
				$xe = mt_rand(0, $this->width);
				$ye = mt_rand(0, $this->height);
				imageline($this->im, $x, $y, $xe, $ye, $color);
				for($j = 0;$j < 3;$j++) {
					imageline($this->im, $x + $j, $y, $xe, $ye, $color);
				}
			}
		}
	}

	function adulteratefont() {
		$seccodeunits = 'BCEFGHJKMPQRTVWXY2346789';
		$x = $this->width / 4;
		$y = $this->height / 10;
		$text_color = imagecolorallocate($this->im, $this->fontcolor[0], $this->fontcolor[1], $this->fontcolor[2]);
		for($i = 0; $i <= 3; $i++) {
			$adulteratecode = $seccodeunits[mt_rand(0, 23)];
			imagechar($this->im, 5, $x * $i + mt_rand(0, $x - 10), mt_rand($y, $this->height - 10 - $y), $adulteratecode, $text_color);
		}
	}

	function ttffont() {
		$seccode = $this->code;
		$seccoderoot = $this->type ? $this->fontpath.'ch/' : $this->fontpath.'en/';
		$dirs = opendir($seccoderoot);
		$seccodettf = array();
		while($entry = readdir($dirs)) {
			if($entry != '.' && $entry != '..' && in_array(strtolower(fileext($entry)), array('ttf', 'ttc'))) {
				$seccodettf[] = $entry;
			}
		}
		if(empty($seccodettf)) {
			$this->giffont();
			return;
		}
		$seccodelength = 4;
		if($this->type && !empty($seccodettf)) {
			if(strtoupper(CHARSET) != 'UTF-8') {
				include $this->includepath.'class_chinese.php';
				$cvt = new Chinese(CHARSET, 'utf8');
				$seccode = $cvt->Convert($seccode);
			}
			$seccode = array(substr($seccode, 0, 3), substr($seccode, 3, 3));
			$seccodelength = 2;
		}
		$widthtotal = 0;
		for($i = 0; $i < $seccodelength; $i++) {
			$font[$i]['font'] = $seccoderoot.$seccodettf[array_rand($seccodettf)];
			$font[$i]['angle'] = $this->angle ? mt_rand(-30, 30) : 0;
			$font[$i]['size'] = $this->type ? $this->width / 7 : $this->width / 6;
			$this->size && $font[$i]['size'] = mt_rand($font[$i]['size'] - $this->width / 40, $font[$i]['size'] + $this->width / 20);
			$box = imagettfbbox($font[$i]['size'], 0, $font[$i]['font'], $seccode[$i]);
			$font[$i]['zheight'] = max($box[1], $box[3]) - min($box[5], $box[7]);
			$box = imagettfbbox($font[$i]['size'], $font[$i]['angle'], $font[$i]['font'], $seccode[$i]);
			$font[$i]['height'] = max($box[1], $box[3]) - min($box[5], $box[7]);
			$font[$i]['hd'] = $font[$i]['height'] - $font[$i]['zheight'];
			$font[$i]['width'] = (max($box[2], $box[4]) - min($box[0], $box[6])) + mt_rand(0, $this->width / 8);
			$font[$i]['width'] = $font[$i]['width'] > $this->width / $seccodelength ? $this->width / $seccodelength : $font[$i]['width'];
			$widthtotal += $font[$i]['width'];
		}
		$x = mt_rand($font[0]['angle'] > 0 ? cos(deg2rad(90 - $font[0]['angle'])) * $font[0]['zheight'] : 1, $this->width - $widthtotal);
		!$this->color && $text_color = imagecolorallocate($this->im, $this->fontcolor[0], $this->fontcolor[1], $this->fontcolor[2]);
		for($i = 0; $i < $seccodelength; $i++) {
			if($this->color) {
				$this->fontcolor = array(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
				$this->shadow && $text_shadowcolor = imagecolorallocate($this->im, 0, 0, 0);
				$text_color = imagecolorallocate($this->im, $this->fontcolor[0], $this->fontcolor[1], $this->fontcolor[2]);
			} elseif($this->shadow) {
				$text_shadowcolor = imagecolorallocate($this->im, 0, 0, 0);
			}
			$y = $font[0]['angle'] > 0 ? mt_rand($font[$i]['height'], $this->height) : mt_rand($font[$i]['height'] - $font[$i]['hd'], $this->height - $font[$i]['hd']);
			$this->shadow && imagettftext($this->im, $font[$i]['size'], $font[$i]['angle'], $x + 1, $y + 1, $text_shadowcolor, $font[$i]['font'], $seccode[$i]);
			imagettftext($this->im, $font[$i]['size'], $font[$i]['angle'], $x, $y, $text_color, $font[$i]['font'], $seccode[$i]);
			$x += $font[$i]['width'];
		}
		$this->warping && $this->warping($this->im);
	}

	function warping(&$obj) {
		$rgb = array();
		$direct = rand(0, 1);
		$width = imagesx($obj);
		$height = imagesy($obj);
		$level = $width / 20;
		for($j = 0;$j < $height;$j++) {
			for($i = 0;$i < $width;$i++) {
				$rgb[$i] = imagecolorat($obj, $i , $j);
			}
			for($i = 0;$i < $width;$i++) {
				$r = sin($j / $height * 2 * M_PI - M_PI * 0.5) * ($direct ? $level : -$level);
				imagesetpixel($obj, $i + $r , $j , $rgb[$i]);
			}
		}
	}

	function scatter(&$obj, $level = 0) {
		$rgb = array();
		$this->scatter = $level ? $level : $this->scatter;
		$width = imagesx($obj);
		$height = imagesy($obj);
		for($j = 0;$j < $height;$j++) {
			for($i = 0;$i < $width;$i++) {
				$rgb[$i] = imagecolorat($obj, $i , $j);
			}
			for($i = 0;$i < $width;$i++) {
				$r = rand(-$this->scatter, $this->scatter);
				imagesetpixel($obj, $i + $r , $j , $rgb[$i]);
			}
		}
	}

	function giffont() {
		$seccode = $this->code;
		$seccodedir = array();
		if(function_exists('imagecreatefromgif')) {
			$seccoderoot = $this->datapath.'gif/';
			$dirs = opendir($seccoderoot);
			while($dir = readdir($dirs)) {
				if($dir != '.' && $dir != '..' && file_exists($seccoderoot.$dir.'/9.gif')) {
					$seccodedir[] = $dir;
				}
			}
		}
		$widthtotal = 0;
		for($i = 0; $i <= 3; $i++) {
			$this->imcodefile = $seccodedir ? $seccoderoot.$seccodedir[array_rand($seccodedir)].'/'.strtolower($seccode[$i]).'.gif' : '';
			if(!empty($this->imcodefile) && file_exists($this->imcodefile)) {
				$font[$i]['file'] = $this->imcodefile;
				$font[$i]['data'] = getimagesize($this->imcodefile);
				$font[$i]['width'] = $font[$i]['data'][0] + mt_rand(0, 6) - 4;
				$font[$i]['height'] = $font[$i]['data'][1] + mt_rand(0, 6) - 4;
				$font[$i]['width'] += mt_rand(0, $this->width / 5 - $font[$i]['width']);
				$widthtotal += $font[$i]['width'];
			} else {
				$font[$i]['file'] = '';
				$font[$i]['width'] = 8 + mt_rand(0, $this->width / 5 - 5);
				$widthtotal += $font[$i]['width'];
			}
		}
		$x = mt_rand(1, $this->width - $widthtotal);
		for($i = 0; $i <= 3; $i++) {
			$this->color && $this->fontcolor = array(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
			if($font[$i]['file']) {
				$this->imcode = imagecreatefromgif($font[$i]['file']);
				if($this->size) {
					$font[$i]['width'] = mt_rand($font[$i]['width'] - $this->width / 20, $font[$i]['width'] + $this->width / 20);
					$font[$i]['height'] = mt_rand($font[$i]['height'] - $this->width / 20, $font[$i]['height'] + $this->width / 20);
				}
				$y = mt_rand(0, $this->height - $font[$i]['height']);
				if($this->shadow) {
					$this->imcodeshadow = $this->imcode;
					imagecolorset($this->imcodeshadow, 0, 0, 0, 0);
					imagecopyresized($this->im, $this->imcodeshadow, $x + 1, $y + 1, 0, 0, $font[$i]['width'], $font[$i]['height'], $font[$i]['data'][0], $font[$i]['data'][1]);
				}
				imagecolorset($this->imcode, 0 , $this->fontcolor[0], $this->fontcolor[1], $this->fontcolor[2]);
				imagecopyresized($this->im, $this->imcode, $x, $y, 0, 0, $font[$i]['width'], $font[$i]['height'], $font[$i]['data'][0], $font[$i]['data'][1]);
			} else {
				$y = mt_rand(0, $this->height - 20);
				if($this->shadow) {
					$text_shadowcolor = imagecolorallocate($this->im, 0, 0, 0);
					imagechar($this->im, 5, $x + 1, $y + 1, $seccode[$i], $text_shadowcolor);
				}
				$text_color = imagecolorallocate($this->im, $this->fontcolor[0], $this->fontcolor[1], $this->fontcolor[2]);
				imagechar($this->im, 5, $x, $y, $seccode[$i], $text_color);
			}
			$x += $font[$i]['width'];
		}
	}

	function flash() {
		$spacing = 5;
		$codewidth = ($this->width - $spacing * 5) / 4;
		$strforswdaction = '';
		for($i = 0; $i <= 3; $i++) {
			$strforswdaction .= $this->swfcode($codewidth, $spacing, $this->code[$i], $i+1);
		}

		ming_setScale(20.00000000);
		ming_useswfversion(6);
		$movie = new SWFMovie();
		$movie->setDimension($this->width, $this->height);
		$movie->setBackground(255, 255, 255);
		$movie->setRate(31);

		$fontcolor = '0x'.(sprintf('%02s', dechex (mt_rand(0, 255)))).(sprintf('%02s', dechex (mt_rand(0, 128)))).(sprintf('%02s', dechex (mt_rand(0, 255))));
		$strAction = "
		_root.createEmptyMovieClip ( 'triangle', 1 );
		with ( _root.triangle ) {
		lineStyle( 3, $fontcolor, 100 );
		$strforswdaction
		}
		";
		$movie->add(new SWFAction( str_replace("\r", "", $strAction) ));
		header('Content-type: application/x-shockwave-flash');
		$movie->output();
	}

	function swfcode($width, $d, $code, $order) {
		$str = '';
		$height = $this->height - $d * 2;
		$x_0 = ($order * ($width + $d) - $width);
		$x_1 = $x_0 + $width / 2;
		$x_2 = $x_0 + $width;
		$y_0 = $d;
		$y_2 = $y_0 + $height;
		$y_1 = $y_2 / 2;
		$y_0_5 = $y_2 / 4;
		$y_1_5 = $y_1 + $y_0_5;
		switch($code) {
			case 'B':$str .= 'moveTo('.$x_1.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_1.', '.$y_2.');lineTo('.$x_2.', '.$y_1_5.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_0_5.');lineTo('.$x_1.', '.$y_0.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');';break;
			case 'C':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');';break;
			case 'E':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');';break;
			case 'F':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');';break;
			case 'G':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_2.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');';break;
			case 'H':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');moveTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_2.', '.$y_1.');';break;
			case 'J':$str .= 'moveTo('.$x_1.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_0.', '.$y_1_5.');';break;
			case 'K':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_0.', '.$y_1.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');moveTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_2.');';break;
			case 'M':$str .= 'moveTo('.$x_0.', '.$y_2.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');';break;
			case 'P':$str .= 'moveTo('.$x_0.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_0_5.');lineTo('.$x_1.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');';break;
			case 'Q':$str .= 'moveTo('.$x_2.', '.$y_2.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_1.', '.$y_1.');';break;
			case 'R':$str .= 'moveTo('.$x_0.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_0_5.');lineTo('.$x_1.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');moveTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_2.');';break;
			case 'T':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');moveTo('.$x_1.', '.$y_0.');lineTo('.$x_1.', '.$y_2.');';break;
			case 'V':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_1.', '.$y_2.');lineTo('.$x_2.', '.$y_0.');';break;
			case 'W':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_2.', '.$y_0.');';break;
			case 'X':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');';break;
			case 'Y':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_0.');moveTo('.$x_1.', '.$y_1.');lineTo('.$x_1.', '.$y_2.');';break;
			case '2':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_1.');lineTo('.$x_0.', '.$y_1.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');';break;
			case '3':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_0.', '.$y_2.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_2.', '.$y_1.');';break;
			case '4':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');moveTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_1.');lineTo('.$x_2.', '.$y_1.');';break;
			case '6':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_2.', '.$y_1.');lineTo('.$x_0.', '.$y_1.');';break;
			case '7':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');';break;
			case '8':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_2.', '.$y_1.');';break;
			case '9':$str .= 'moveTo('.$x_2.', '.$y_1.');lineTo('.$x_0.', '.$y_1.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_0.', '.$y_2.');';break;
		}
		return $str;
	}

	function audio() {
		header('Content-type: audio/mpeg');
		for($i = 0;$i <= 3; $i++) {
			readfile($this->datapath.'sound/'.strtolower($this->code[$i]).'.mp3');
		}
	}

	function bitmap() {
		$numbers = array
			(
			'B' => array('00','fc','66','66','66','7c','66','66','fc','00'),
			'C' => array('00','38','64','c0','c0','c0','c4','64','3c','00'),
			'E' => array('00','fe','62','62','68','78','6a','62','fe','00'),
			'F' => array('00','f8','60','60','68','78','6a','62','fe','00'),
			'G' => array('00','78','cc','cc','de','c0','c4','c4','7c','00'),
			'H' => array('00','e7','66','66','66','7e','66','66','e7','00'),
			'J' => array('00','f8','cc','cc','cc','0c','0c','0c','7f','00'),
			'K' => array('00','f3','66','66','7c','78','6c','66','f7','00'),
			'M' => array('00','f7','63','6b','6b','77','77','77','e3','00'),
			'P' => array('00','f8','60','60','7c','66','66','66','fc','00'),
			'Q' => array('00','78','cc','cc','cc','cc','cc','cc','78','00'),
			'R' => array('00','f3','66','6c','7c','66','66','66','fc','00'),
			'T' => array('00','78','30','30','30','30','b4','b4','fc','00'),
			'V' => array('00','1c','1c','36','36','36','63','63','f7','00'),
			'W' => array('00','36','36','36','77','7f','6b','63','f7','00'),
			'X' => array('00','f7','66','3c','18','18','3c','66','ef','00'),
			'Y' => array('00','7e','18','18','18','3c','24','66','ef','00'),
			'2' => array('fc','c0','60','30','18','0c','cc','cc','78','00'),
			'3' => array('78','8c','0c','0c','38','0c','0c','8c','78','00'),
			'4' => array('00','3e','0c','fe','4c','6c','2c','3c','1c','1c'),
			'6' => array('78','cc','cc','cc','ec','d8','c0','60','3c','00'),
			'7' => array('30','30','38','18','18','18','1c','8c','fc','00'),
			'8' => array('78','cc','cc','cc','78','cc','cc','cc','78','00'),
			'9' => array('f0','18','0c','6c','dc','cc','cc','cc','78','00')
			);

		foreach($numbers as $i => $number) {
			for($j = 0; $j < 6; $j++) {
				$a1 = substr('012', mt_rand(0, 2), 1).substr('012345', mt_rand(0, 5), 1);
				$a2 = substr('012345', mt_rand(0, 5), 1).substr('0123', mt_rand(0, 3), 1);
				mt_rand(0, 1) == 1 ? array_push($numbers[$i], $a1) : array_unshift($numbers[$i], $a1);
				mt_rand(0, 1) == 0 ? array_push($numbers[$i], $a1) : array_unshift($numbers[$i], $a2);
			}
		}

		$bitmap = array();
		for($i = 0; $i < 20; $i++) {
			for($j = 0; $j <= 3; $j++) {
				$bytes = $numbers[$this->code[$j]][$i];
				$a = mt_rand(0, 14);
				array_push($bitmap, $bytes);
			}
		}

		for($i = 0; $i < 8; $i++) {
			$a = substr('012345', mt_rand(0, 2), 1) . substr('012345', mt_rand(0, 5), 1);
			array_unshift($bitmap, $a);
			array_push($bitmap, $a);
		}

		$image = pack('H*', '424d9e000000000000003e000000280000002000000018000000010001000000'.
				'0000600000000000000000000000000000000000000000000000FFFFFF00'.implode('', $bitmap));

		header('Content-Type: image/bmp');
		echo $image;
	}

}
?>