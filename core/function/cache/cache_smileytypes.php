<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

function build_cache_smileytypes() {
	$data = array();
	foreach(C::t('imagetype')->fetch_all_by_type('smiley', 1) as $type) {
		$typeid = $type['typeid'];
		unset($type['typeid']);
		if(C::t('smiley')->count_by_type_code_typeid('smiley', $typeid)) {
			$data[$typeid] = $type;
		}
	}

	savecache('smileytypes', $data);
}

?>