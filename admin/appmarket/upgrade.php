<?php
/* @authorcode  codestrings
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      qchlian(3580164@qq.com)
 */

if(!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}
@set_time_limit(0);
//卸载程序；
$applist_midnone=DB::fetch_all("select * from %t where mid=0 ",array('app_market'));

if( $applist_midnone ){
	$dzz_upgrade = new dzz_upgrade_app();
	foreach( $applist_midnone as $value){
		$url=APP_CHECK_URL."market/app/getmid";//."index.php?mod=dzzmarket&op=index_ajax&operation=getmid";
		$post_data = array( 
			"version"=>$value['version'],
			"identifier"=>$value['identifier'],
			"app_path"=>$value["app_path"]
		);
		$json = $dzz_upgrade->curlcloudappmarket($url,$post_data); 
		$json = json_decode($json,true);
		if( $json["status"]==1){
			$mid = $json["mid"];
			DB::update('app_market',array('mid'=>$mid),"appid=".$value["appid"]);
		}
	}
}

$finish = true;
