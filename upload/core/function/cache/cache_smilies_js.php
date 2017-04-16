<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

function build_cache_smilies_js() {
	global $_G;

	$fastsmiley = C::t('setting')->fetch('fastsmiley', true);
	$return_type = 'var smilies_type = new Array();';
	$return_array = 'var smilies_array = new Array();var smilies_fast = new Array();';
	$spp = $_G['setting']['smcols'] * $_G['setting']['smrows'];
	$fpre = '';
	foreach(C::t('imagetype')->fetch_all_by_type('smiley', 1) as $type) {
		$return_data = array();
		$return_datakey = '';
			$i = 0;$j = 1;$pre = '';
			$return_type .= 'smilies_type[\'_'.$type['typeid'].'\'] = [\''.str_replace('\'', '\\\'', $type['name']).'\', \''.str_replace('\'', '\\\'', $type['directory']).'\'];';
			$return_datakey .= 'smilies_array['.$type['typeid'].'] = new Array();';
			foreach(C::t('smiley')->fetch_all_by_type_code_typeid('smiley', $type['typeid']) as $smiley) {
				if($i >= $spp) {
					$return_data[$j] = 'smilies_array['.$type['typeid'].']['.$j.'] = ['.$return_data[$j].'];';
					$j++;$i = 0;$pre = '';
				}
				if($size = @getimagesize(DZZ_ROOT.'./static/image/smiley/'.$type['directory'].'/'.$smiley['url'])) {
					$smiley['code'] = str_replace('\'', '\\\'', $smiley['code']);
					$smileyid = $smiley['id'];
					$s = smthumb($size, $_G['setting']['smthumb']);
					$smiley['w'] = $s['w'];
					$smiley['h'] = $s['h'];
					$l = smthumb($size);
					$smiley['lw'] = $l['w'];
					unset($smiley['id'], $smiley['directory']);
					$return_data[$j] .= $pre.'[\''.$smileyid.'\', \''.$smiley['code'].'\',\''.str_replace('\'', '\\\'', $smiley['url']).'\',\''.$smiley['w'].'\',\''.$smiley['h'].'\',\''.$smiley['lw'].'\',\''.$smiley['title'].'\']';
					if(is_array($fastsmiley[$type['typeid']]) && in_array($smileyid, $fastsmiley[$type['typeid']])) {
						$return_fast .= $fpre.'[\''.$type['typeid'].'\',\''.$j.'\',\''.$i.'\']';
						$fpre = ',';
					}
					$pre = ',';
				}
				$i++;
			}
			$return_data[$j] = 'smilies_array['.$type['typeid'].']['.$j.'] = ['.$return_data[$j].'];';
		$return_array .= $return_datakey.implode('', $return_data);
	}
	$cachedir = DZZ_ROOT.'./data/cache/';
	if(@$fp = fopen($cachedir.'smilies_var.js', 'w')) {
		fwrite($fp, 'var smthumb = \''.$_G['setting']['smthumb'].'\';'.$return_type.$return_array.'var smilies_fast=['.$return_fast.'];');
		fclose($fp);
	} else {
		exit('Can not write to cache files, please check directory ./data/ and ./data/cache/ .');
	}

}

?>