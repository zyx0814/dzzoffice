<?php
/*
 * 计划任务脚本 定时检测应用更新
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      qchlian(3580164@qq.com)
 */ 
if(!defined('IN_DZZ')) {
	exit('Access Denied');
} 
include_once DZZ_ROOT . './core/core_version.php';
$map=array();
$today = date("Ymd");
$map["check_upgrade_time"]=array("lt",$today); 
$applist =DB::fetch_all("select * from %t where check_upgrade_time<%d limit 10",array('app_market',$today)); //C::tp_t('app_market')->where($map)->limit(10)->select(); 
if( $applist ){
	$dzz_upgrade = new dzz_upgrade_app();
	//根据当前版本查询是否需要更新 
	$appinfo["mysqlversion"] = helper_dbtool::dbversion();
	$appinfo["phpversion"] = PHP_VERSION ;
	$appinfo["dzzversion"] = CORE_VERSION;
	foreach($applist as $k=>$v ){
		if(empty($v['app_path'])) $v['app_path']='dzz';
		$savedata=array();
		if( $v["mid"]>0){//云端检测
			$info=array_merge($v,$appinfo);
			$response = $dzz_upgrade->check_upgrade_byversion( $info );
			if( $response  ) {
				if( $response["status"]==1 ){
					$savedata=array( "upgrade_version"=>serialize($response["data"]), "check_upgrade_time"=>$today ); 
				}else{
					if( $response["status"]!=2 ){//云端应用未有新版本发布或找不到版本
						$savedata=array( "upgrade_version"=>"", "check_upgrade_time"=>$today );
					}else{//云端应用不存在
						$savedata=array( "upgrade_version"=>"", "check_upgrade_time"=>$today );
					}
				}
			}
		}else{//本地检测
			$file = DZZ_ROOT . './'.$v['app_path'].'/' . $v['identifier'] . '/dzz_app_' . $v['identifier'] . '.xml'; 
			if ( file_exists($file) ) {
				$importtxt = @implode('', file($file));
				$apparray = getimportdata('Dzz! app',0,0,$importtxt);
				if($apparray["app"]["version"]>$v["version"]){
					unset( $apparray["app"]['appico']);//ico base64太长暂时屏蔽应用icon更新
					$savedata=array( "upgrade_version"=>serialize($apparray["app"]), "check_upgrade_time"=>$today ); 
				} else{
					$savedata=array( "upgrade_version"=>"", "check_upgrade_time"=>$today );
				}
			}else{
				$savedata=array( "upgrade_version"=>"", "check_upgrade_time"=>$today );
			}
		} 
		if( $savedata ){
			$re= C::t('app_market')->update($appid,$savedata);//C::tp_t('app_market')->where("appid=".$v["appid"])->save( $savedata ); 
		} 
	}
}
?>