<?php


if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

function build_cache_smileycodes() {
	$data = array();
	foreach(C::t('imagetype')->fetch_all_by_type('smiley', 1) as $type) {
		foreach(C::t('smiley')->fetch_all_by_type_code_typeid('smiley', $type['typeid']) as $smiley) {
			if($size = @getimagesize('./static/image/smiley/'.$type['directory'].'/'.$smiley['url'])) {
				$data[$smiley['id']] = $smiley['code'];
			}
		}
	}

	savecache('smileycodes', $data);
}

?>