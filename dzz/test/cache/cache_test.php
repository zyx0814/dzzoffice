<?php

if (!defined('IN_DZZ')) {
	exit('Access Denied');
}

function build_cache_app_test() {
	$data = DB::fetch_all("select * from %t where 1", ['test'], 'testid');
	savecache('testdatas', $data);
}