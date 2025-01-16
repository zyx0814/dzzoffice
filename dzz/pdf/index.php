<?php
if(!defined('IN_DZZ')||!$_GET['path']) {
	exit('Access Denied');
}
$path=dzzdecode($_GET['path']);
$meta=IO::getMeta($path);
if(!perm_check::checkperm('download',$meta)){
	$perm_download=0;
	$perm_print=0;
}else{
	$perm_download=1;
	$perm_print=1;
}
//$file=$_G['siteurl'].'index.php?mod=io&op=getStream&path='.$_GET['path'].'&filename='.$meta['name'];
$file=IO::getFileUri($path);
/*header("Location: /dzz/pdf/web/viewer.html?file=".urlencode($file));
exit();*/
include template('viewer');
exit();