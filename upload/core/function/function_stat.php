<?php
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

function updatestat($type, $primary=0, $num=1) {
	$uid = getglobal('uid');
	$updatestat = getglobal('setting/updatestat');
	if(empty($uid) || empty($updatestat)) return false;
	C::t('stat')->updatestat($uid, $type, $primary, $num);
}
?>