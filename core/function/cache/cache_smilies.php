<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

function build_cache_smilies() {
	$data = array();

	$data = array('searcharray' => array(), 'replacearray' => array(), 'typearray' => array());
	foreach(C::t('smiley')->fetch_all_cache() as $smiley) {
		$data['searcharray'][$smiley['id']] = '/'.preg_quote(dhtmlspecialchars($smiley['code']), '/').'/';
		$data['replacearray'][$smiley['id']] = $smiley['url'];
		$data['typearray'][$smiley['id']] = $smiley['typeid'];
	}

	savecache('smilies', $data);
}

?>