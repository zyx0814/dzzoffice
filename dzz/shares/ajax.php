<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ')) {
	exit('Access Denied');
}
$open = false;
if(isset($_GET['path'])){//打开文件夹
	$morepath = $_GET['path'];
	$rids = array();
    if(preg_match('/^\d+$/',$morepath)){
    	foreach (DB::fetch_all('select rid from %t where pfid = %d and isdelete < 1',array('resources',$morepath)) as $v){
    		$rids[] = $v['rid'];
		}
	}else{
        $dpath = explode(',',$_GET['path']);
        foreach($dpath as $v){
            $rids[] = dzzdecode($v);
        }
	}
	$open = true;
}elseif(isset($_GET['morepath'])){//加载更多
	$morepath = $_GET['morepath'];
    if(preg_match('/^\d+$/',$morepath)){
        foreach (DB::fetch_all('select rid from %t where pfid = %d and isdelete < 1',array('resources',$morepath)) as $v){
            $rids[] = $v['rid'];
        }
    }
	/*if(preg_match('/,/',$morepath)){
		$dpath = explode(',',$morepath);
		$rids = array();
		foreach($dpath as $v){
			$rids[] = dzzdecode($v);
		}
	}*/else{
		$sid=dzzdecode($_GET['morepath']);
		$share=C::t('shares')->fetch($sid);
		$filepaths = $share['filepath'];
		$rids  = explode(',',$filepaths);
	}
}
if(isset($_GET['currentfolder']) && $_GET['currentfolder']){
    $currentfolder = true;
}else{
    $currentfolder = false;
}
$page = (isset($_GET['page'])) ? intval($_GET['page']):1;
$perpage = 20;
$start = ($page - 1) * $perpage;
$gets = array('mod' => 'shares', 'sid' => $sid, );
$theurl = BASESCRIPT . "?" . url_implode($gets);
$ordersql = '';
$asc = (isset($_GET['asc'])) ? intval($_GET['asc']):1;
$disp = (isset($_GET['disp'])) ? intval($_GET['disp']):0;
$order = ($asc > 0) ? 'ASC':'DESC';
switch ($disp) {
	case 0:
		$orderby = 'name';
		break;
	case 1:
		$orderby = 'size';
		break;
	case 2:
		$orderby = array('type', 'ext');
		break;
	case 3:
		$orderby = 'dateline';
		break;
}
if(is_array($orderby)){
	foreach($orderby as $key=>$value){
		$orderby[$key]=$value.' '.$order;
	}
	$ordersql=' ORDER BY '.implode(',',$orderby);
}elseif($orderby){
	$ordersql=' ORDER BY '.$orderby.' '.$order;
}
$limitsql = 'limit '.$start .','. ($perpage);
$params = array('resources',$rids);
$wheresql = " where rid in(%n)  and isdelete < 1";
$list = array();


$foldername = '';
$allrids = '';
if($currentfolder){
    $fileinfo = DB::fetch_first("select * from %t where rid = %s",array('resources',$rids[0]));
    $foldername = $fileinfo['name'];
    $allrids = dzzencode($fileinfo['rid']);
    $list = array();
}else{
    //获取分享数据
    foreach(DB::fetch_all("select rid from %t $wheresql $ordersql $limitsql",$params) as $v){
        $fileinfo = getfileinfo($v['rid']);
        if($open && !$foldername){
            $foldername = DB::result_first("select fname from %t where fid = %d",array('folder',$fileinfo['pfid']));
        }
        if($fileinfo['type'] == 'folder' && $fileinfo['oid']) {
            $oid = $fileinfo['oid'];
            $fileinfo['dhpath'] = $oid;
        }
        $list[] = $fileinfo;
        $allrids .= dzzencode($val['rid']).',';
    }
    $allrids = substr($allrids,0,-1);
}

if (count($list) >= $perpage) {
	$nextpage = $page + 1;
} else {
	$naxtpage = 0;
}
include template('list_item');
?>
