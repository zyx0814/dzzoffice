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

$ext = trim($_GET['ext']);
$appid = intval($_GET['appid']);
$orderby = trim($_GET['s']);
$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
$perpage = 20;
$gets = array('mod' => 'appmarket', 'op' => 'extopen', 'ext' => $ext, 'appid' => $appid);
$theurl = BASESCRIPT . "?" . url_implode($gets);
$refer = urlencode($theurl . '&page=' . $page);
if ($orderby)
	$order = 'ORDER BY ' . $orderby;
else
	$order = 'order by disp DESC';
$start = ($page - 1) * $perpage;
$apps = array();

$sql = '1';
$param=array('app_open');
if ($appid) {
	$sql .= " and `appid` = '{$appid}'";
	$map["appid"]=$appid;
	$param[]=$appid;
} elseif ($ext) {
	$sql .= " and `ext` = '{$ext}'";
	$map["ext"]=$ext;
	$param[]=$ext;
}
 
$count = DB::result_first("select COUNT(*) from %t where 1",array('app_market'));//C::tp_t("app_open")->where($map)->count();
if($count){
	$appdatas =DB::fetch_all("select appid,appico,appname,appurl from %t where 1",array('app_market'),'appid');// C::tp_t('app_market')->getField("appid,appico,appname,appurl"); 
	$list = DB::fetch_all("select * from %t where $sql ORDER BY appid DESC",$param);//C::tp_t("app_open")->where($map)->order("appid desc")->select();
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
// print_r($list);exit;
include template('extopen');
?>
