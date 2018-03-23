<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class GifMerge {
	var $ver			= '1.1';
	var $dly			= 50;
	var $mod			= 'C_FILE';
	var $first			= true;
	var $use_loop			= false;
	var $transparent		= false;
	var $use_global_in		= false;
	var $x				= 0;
	var $y				= 0;
	var $ch				= 0;
	var $fin			= 0;
	var $fout			= '';
	var $loop			= 0;
	var $delay			= 0;
	var $width			= 0;
	var $height			= 0;
	var $trans1 			= 255;
	var $trans2 			= 255;
	var $trans3 			= 255;
	var $disposal			= 2;
	var $out_color_table_size	= 0;
	var $local_color_table_flag	= 0;
	var $global_color_table_size	= 0;
	var $out_color_table_sizecode	= 0;
	var $global_color_table_sizecode= 0;
	var $gif			= array(0x47, 0x49, 0x46);
	var $buffer			= array();
	var $local_in			= array();
	var $global_in			= array();
	var $global_out			= array();
	var $logical_screen_descriptor	= array();

	function GifMerge($images, $t1, $t2, $t3, $loop, $dl, $xpos, $ypos, $model) {
		if($model) {
			$this->mod = $model;
		}
		if($loop > -1) {
			$this->loop = floor($loop - 1);
			$this->use_loop = true;
		}
		if($t1 > -1 && $t2 > -1 && $t3 > -1) {
			$this->trans1 = $t1;
			$this->trans2 = $t2;
			$this->trans3 = $t3;
			$this->transparent = true;
		}
		for($i = 0; $i < count($images); $i++) {
			$dl[$i]	? $this->delay = $dl[$i] : $this->delay = $this->dly;
			$xpos[$i] ? $this->x = $xpos[$i] : $this->x = 0;
			$ypos[$i] ? $this->y = $ypos[$i] : $this->y = 0;
			$this->start_gifmerge_process($images[$i]);
		}
		$this->fout .= "\x3b";
	}

	function start_gifmerge_process($fp) {
		if($this->mod == 'C_FILE') {
			if(!$this->fin = fopen($fp, 'rb')) {
				return;
			}
		} elseif($this->mod == 'C_MEMORY') {
			$this->ch = 0;
			$this->fin = $fp;
		}
		$this->getbytes(6);
		if(!$this->arrcmp($this->buffer, $this->gif, 3)) {
			return;
		}
		$this->getbytes(7);
		if($this->first) $this->logical_screen_descriptor = $this->buffer;
		$this->global_color_table_sizecode = $this->buffer[4] & 0x07;
		$this->global_color_table_size = 2 << $this->global_color_table_sizecode;
		if($this->buffer[4] & 0x80) {
			$this->getbytes((3 * $this->global_color_table_size));
			for($i = 0; $i < ((3 * $this->global_color_table_size)); $i++) {
				$this->global_in[$i] = $this->buffer[$i];
			}
			if($this->out_color_table_size == 0) {
				$this->out_color_table_size = $this->global_color_table_size;
				$this->out_color_table_sizecode = $this->global_color_table_sizecode;
				$this->global_out = $this->global_in;
			}
			if($this->global_color_table_size != $this->out_color_table_size || $this->arrcmp($this->global_out, $this->global_in, (3 * $this->global_color_table_size))) {
				$this->use_global_in = true;
			}
		}
		for($loop = true; $loop;) {
			$this->getbytes(1);
			switch($this->buffer[0]) {
				case 0x21:
					$this->read_extension();
					break;
				case 0x2c:
					$this->read_image_descriptor();
					break;
				case 0x3b:
					$loop = false;
				break;
				default:
					$loop = false;
			}
		}
		if($this->mod == 'C_FILE') {
			fclose($this->fin);
		}
	}

	function read_image_descriptor() {
		$this->getbytes(9);
		$head = $this->buffer;
		$this->local_color_table_flag = ($this->buffer[8] & 0x80) ? true : false;
		if($this->local_color_table_flag) {
			$sizecode = $this->buffer[8] & 0x07;
			$size = 2 << $sizecode;
			$this->getbytes(3 * $size);
			for($i = 0; $i < (3 * $size); $i++) {
				$this->local_in[$i] = $this->buffer[$i];
			}
			if($this->out_color_table_size == 0) {
				$this->out_color_table_size = $size;
				$this->out_color_table_sizecode = $sizecode;
				for($i = 0; $i < (3 * $size); $i++) {
					$this->global_out[$i] = $this->local_in[$i];
				}
			}
		}
		if($this->first) {
		$this->first = false;
		$this->fout .= "\x47\x49\x46\x38\x39\x61";
		if($this->width && $this->height) {
			$this->logical_screen_descriptor[0] = $this->width & 0xFF;
			$this->logical_screen_descriptor[1] = ($this->width & 0xFF00) >> 8;
			$this->logical_screen_descriptor[2] = $this->height & 0xFF;
			$this->logical_screen_descriptor[3] = ($this->height & 0xFF00) >> 8;
		}
		$this->logical_screen_descriptor[4] |= 0x80;
		$this->logical_screen_descriptor[5] &= 0xF0;
		$this->logical_screen_descriptor[6] |= $this->out_color_table_sizecode;
		$this->putbytes($this->logical_screen_descriptor, 7);
		$this->putbytes($this->global_out, ($this->out_color_table_size * 3));
		if($this->use_loop) {
			$ns[0] = 0x21;
			$ns[1] = 0xFF;
			$ns[2] = 0x0B;
			$ns[3] = 0x4e;
			$ns[4] = 0x45;
			$ns[5] = 0x54;
			$ns[6] = 0x53;
			$ns[7] = 0x43;
			$ns[8] = 0x41;
			$ns[9] = 0x50;
			$ns[10] = 0x45;
			$ns[11] = 0x32;
			$ns[12] = 0x2e;
			$ns[13] = 0x30;
			$ns[14] = 0x03;
			$ns[15] = 0x01;
			$ns[16] = $this->loop & 255;
			$ns[17] = $this->loop >> 8;
			$ns[18] = 0x00;
			$this->putbytes($ns, 19);
		}
	}
	if($this->use_global_in) {
		$outtable = $this->global_in;
		$outsize = $this->global_color_table_size;
		$outsizecode = $this->global_color_table_sizecode;
	} else {
		$outtable = $this->global_out;
		$outsize = $this->out_color_table_size;
	}
	if($this->local_color_table_flag) {
		if($size == $this->out_color_table_size && !$this->arrcmp($this->local_in, $this->global_out, $size)) {
			$outtable = $this->global_out;
			$outsize = $this->out_color_table_size;
		} else {
			$outtable = $this->local_in;
			$outsize = $size;
			$outsizecode = $sizecode;
		}
	}
	$use_trans = false;
	if($this->transparent) {
		for($i = 0; $i < $outsize; $i++) {
			if($outtable[3 * $i] == $this->trans1 && $outtable [3 * $i + 1] == $this->trans2 && $outtable [3 * $i + 2] == $this->trans3) {
				break;
			}
		}
		if($i < $outsize) {
			$transindex = $i;
			$use_trans = true;
		}
	}
	if($this->delay || $use_trans) {
		$this->buffer[0] = 0x21;
		$this->buffer[1] = 0xf9;
		$this->buffer[2] = 0x04;
		$this->buffer[3] = ($this->disposal << 2) + ($use_trans ? 1 : 0);
		$this->buffer[4] = $this->delay & 0xff;
		$this->buffer[5] = ($this->delay & 0xff00) >> 8;
		$this->buffer[6] = $use_trans ? $transindex : 0;
		$this->buffer[7] = 0x00;
		$this->putbytes($this->buffer,8);
	}
	$this->buffer[0] = 0x2c;
	$this->putbytes($this->buffer,1);
	$head[0] = $this->x & 0xff;
	$head[1] = ($this->x & 0xff00) >> 8;
	$head[2] = $this->y & 0xff;
	$head[3] = ($this->y & 0xff00) >> 8;
	$head[8] &= 0x40;
	if($outtable != $this->global_out) {
		$head[8] |= 0x80;
		$head[8] |= $outsizecode;
	}
	$this->putbytes($head,9);
	if($outtable != $this->global_out) {
		$this->putbytes($outtable, (3 * $outsize));
	}
	$this->getbytes(1);
	$this->putbytes($this->buffer,1);
	for(;;) {
		$this->getbytes(1);
		$this->putbytes($this->buffer,1);
		if(($u = $this->buffer[0]) == 0) {
			break;
		}
		$this->getbytes($u);
		$this->putbytes($this->buffer, $u);
	}
	}

	function read_extension() {
		$this->getbytes(1);
		switch($this->buffer[0]) {
			case 0xf9:
				$this->getbytes(6);
	   			break;
			case 0xfe:
				for(;;) {
					$this->getbytes(1);
					if(($u = $this->buffer[0]) == 0) {
						break;
					}
					$this->getbytes($u);
				}
				break;
			case 0x01:
				$this->getbytes(13);
				for(;;) {
					$this->getbytes(0);
					if(($u = $this->buffer[0]) == 0) {
						break;
					}
					$this->getbytes($u);
				}
				break;
			case 0xff:
				$this->getbytes(9);
				$this->getbytes(3);
				for(;;) {
					$this->getbytes(1);
					if(!$this->buffer[0]) {
						break;
					}
					$this->getbytes($this->buffer[0]);
				}
				break;
			default:
				for(;;) {
					$this->getbytes(1);
					if(!$this->buffer[0]) {
						break;
					}
					$this->getbytes($this->buffer[0]);
				}
		}
	}

	function arrcmp($b, $s, $l) {
		for($i = 0; $i < $l; $i++) {
			if($s{$i} != $b{$i}) {
				return false;
			}
		}
		return true;
	}

	function getbytes($l) {
		for($i = 0; $i < $l; $i++) {
			if($this->mod == 'C_FILE') {
				$bin = unpack('C*', fread($this->fin, 1));
				$this->buffer[$i] = $bin[1];
			} elseif($this->mod == 'C_MEMORY') {
				$bin = unpack('C*', substr($this->fin, $this->ch, 1));
				$this->buffer[$i] = $bin[1];
				$this->ch++;
			}
		}
		return $this->buffer;
	}

	function putbytes($s, $l) {
		for($i = 0; $i < $l; $i++) {
			$this->fout .= pack('C*', $s[$i]);
		}
	}

	function getAnimation() {
		return $this->fout;
	}
}

?>