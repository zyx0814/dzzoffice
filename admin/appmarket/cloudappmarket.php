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
//define('DZZ_OUTPUTED', 1);
$navtitle=lang('appname');
include libfile('function/organization');
$op='cloudappmarket';
$cloudurl = APP_CHECK_URL."index.php";
$url=APP_CHECK_URL."market/app/list";//$cloudurl."?mod=dzzmarket&op=index_ajax";
$type=empty($_GET['type'])?1:intval($_GET['type']);
$page = empty($_GET['page'])?1:intval($_GET['page']);
$keyword=trim($_GET['keyword']);
$classid=intval($_GET['classid']);
$post_data = array("siteuniqueid"=>$_G["setting"]["siteuniqueid"],"page"=>$page,"type"=>1 );
$json = curlcloudappmarket($url,$post_data);
$json = json_decode($json,true);
$list=array();
$total=0;
if( $json["status"]==1){
	$list = $json["data"]["list"];
	$total = $json["data"]["total"];
	$perpage = $json["data"]["perpage"]; 
	//$perpage =1;
	$theurl = BASESCRIPT."?mod=".MOD_NAME."&op=cloudappmarket"; 
	$multi=multi($total, $perpage, $page, $theurl);
}
//print_r($list);exit;
$local_applist=DB::fetch_all("select * from %t where 1",array('app_market'));//C::tp_t("app_market")->select();
if( $list ){ 
	foreach($list as $k=>$v){
		$list[$k]["local_appinfo"]=array();
		$list[$k]["baseinfo"]=base64_encode( serialize($v) );
		if( $v["identifier"] ){
			foreach($local_applist as $k2=>$v3){
				if($v["identifier"]==$v3["identifier"]){
					$list[$k]["local_appinfo"]=$v3;
					break;
				}
			}
		}
	}
} 
include template('cloudappmarket');
exit;
function curlcloudappmarket( $url="",$post_data="",  $token="" ){ 
    $curl = curl_init(); 
    curl_setopt($curl, CURLOPT_URL, $url); 
    curl_setopt($curl, CURLOPT_HEADER, 0); 
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($curl, CURLOPT_POST, 1); 
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    $response = curl_exec($curl); 
	$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	$errorno = curl_errno($curl);
	if ($errorno) {
		return($errorno);  
	}
	return($response); 
}


?>
