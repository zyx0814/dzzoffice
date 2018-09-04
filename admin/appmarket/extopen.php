<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}
$navtitle=lang('open_way').' - '.lang('appname');
$op='extopen';
$do=$_GET['do'];

if ( $do =="setdefault" ) {
	$extid = intval( $_GET["extid"] );
	if($extid){
		$result = C::t('app_open') -> setDefault($extid);
		if($result){
			success( lang('set_default').lang('success'));
		} 
	}
	error(lang('set_default').lang('failure'));
}else if( $do =="setorder"){
	$extid = $_GET["extid"];
	if( $extid ){
		$result = C::t('app_open') -> setOrders($extid);
		if($result){
			success( lang('set_default').lang('success'));
		} 
	}
	error(lang('set_default').lang('failure'));
}
$sql_app="`available`>0";
$param_app=array('app_market');
$sql = '1';
$param=array('app_open');
if(preg_match("/[a-zA-Z0-9]{1,10}/i",$_GET['ext'])){
	$ext = trim($_GET['ext']);
	$sql .= " and `ext` = %s";
	$param[]=$ext;
}elseif($_GET['ext']){
	$appname=trim($_GET['ext']);
	$sql_app.=' and appname LIke %s';
	$param_app[]='%'.$appname.'%';
}
$ext = trim($_GET['ext']);
$appid = intval($_GET['appid']);

$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
$perpage = 20;
$gets = array('mod' => 'appmarket', 'op' => 'extopen', 'ext' => $ext, 'appid' => $appid);
$theurl = BASESCRIPT . "?" . url_implode($gets);
$refer = urlencode($theurl . '&page=' . $page);

$start = ($page - 1) * $perpage;
$apps = array();


if ($appid) {
	$sql .= " and `appid` = %d";
	$param[]=$appid;
} 
 
$count = DB::result_first("select COUNT(*) from %t where $sql_app ",$param_app);
if($count){
	$appdatas =DB::fetch_all("select appid,appico,appname,appurl from %t where $sql_app ",$param_app,'appid');
	$sql .= ' and `appid` IN(%n)';
	$param[] = array_keys($appdatas);
	$list = DB::fetch_all("select * from %t where  $sql  ORDER BY ext DESC",$param);
	$newlist=array();
	foreach($list as $k=>$v ){
		$appdata = $appdatas[$v["appid"]];
		if ($appdata['appico'] != 'dzz/images/default/icodefault.png' && !preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $appdata['appico'])) {
			$appdata['appico'] = $_G['setting']['attachurl'] . $appdata['appico'];
		}
		$appdata['appurl'] = replace_canshu($appdata['appurl']); 
		$v["appdata"]=$appdata;
		$newlist[$v["ext"]][]=$v;
	}
	$count = count($newlist);
}
$multi = multi($count, $perpage, $page, $theurl );
//根据分页截取数组
ksort($newlist,SORT_STRING );
$list = array_slice($newlist,$start,$perpage);
foreach($list as $k=>$nlist){ 
	$sort = array(
          'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
          'field'     => 'disp', //排序字段
	);
	$arrSort = array();
	foreach($nlist AS $uniqid => $row){
		foreach($row AS $key=>$value){
			$arrSort[$key][$uniqid] = $value;
		}
	}
	if($sort['direction']){
		array_multisort($arrSort[$sort['field']], constant($sort['direction']), $nlist);
	}
	$list[$k]=$nlist;
}
//print_r($list);exit;
include template('extopen');
?>
