<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/11/16
 * Time: 15:20
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
$fid = isset($_GET['fid']) ? intval($_GET['fid']):'';
if(!$fid) $fid = $group['fid'];
$folderinfo = C::t('folder')->fetch_folderinfo_by_fid($fid);
$folderpatharr = getpath($folderinfo['path']);
$folderpathstr= implode('\\',$folderpatharr);

//统计打开次数,如果当前文件夹在resources表无数据，则记录其文件夹id对应数据
if( $rid = C::t('resources')->fetch_rid_by_fid($fid)){
    $rid = C::t('resources')->fetch_rid_by_fid($fid);
    $setarr = array(
        'uid'=>$uid,
        'views'=>1,
        'opendateline'=>TIMESTAMP,
        'fid'=>$fid
    );
    C::t('resources_statis')->add_statis_by_rid($rid,$setarr);
}else{
    $setarr = array(
        'uid'=>$uid,
        'views'=>1,
        'opendateline'=>TIMESTAMP,
    );
    C::t('resources_statis')->add_statis_by_fid($fid,$setarr);
}