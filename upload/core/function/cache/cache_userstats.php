<?php
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

function build_cache_userstats() {
	global $_G;
	$totalmembers = C::t('user')->count();
	$member = C::t('user')->range(0, 1, 'DESC');
	$member = current($member);
	$newsetuser = $member['username'];
	$data = array('totalmembers' => $totalmembers, 'newsetuser' => $newsetuser);
	savecache('userstats', $data);
}

?>