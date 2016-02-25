<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

function build_cache_profilesetting() {
	$data = C::t('user_profile_setting')->fetch_all_by_available(1);

	savecache('profilesetting', $data);
}

?>