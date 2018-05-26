<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

header('Content-Type: text/javascript');

dsetcookie('sendwx', '1', 5);
$lockfile = DZZ_ROOT.'./data/sendwx.lock';
@$filemtime = filemtime($lockfile);

if($_G['timestamp'] - $filemtime < 5) exit();

touch($lockfile);

@set_time_limit(0);
$noteid=0;
Hook::listen('online_notification', $noteid);//第三方发送提醒
?>