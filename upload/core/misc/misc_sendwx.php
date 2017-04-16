<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

header('Content-Type: text/javascript');

$pernum = 10;

dsetcookie('sendwx', '1', 5);
$lockfile = DZZ_ROOT.'./data/sendwx.lock';
@$filemtime = filemtime($lockfile);

if($_G['timestamp'] - $filemtime < 5) exit();

touch($lockfile);

@set_time_limit(0);
foreach(DB::fetch_all("select * from %t where wx_new>0 and wx_note!='' order by dateline DESC,wx_new ASC  limit %d",array('notification',$pernum)) as $value) {
	dzz_notification::wx_sendMsg($value);
}
?>