<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
$uid = $_G['uid'];
$oldhash = isset($_GET['oldhash']) ? trim($_GET['oldhash']):'';
$fid = isset($_GET['fid']) ? trim($_GET['fid']):'';
if($fid){
    $folderdata = C::t('folder')->fetch($fid);
}
include template('mobilefileselection/search');