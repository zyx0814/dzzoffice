<?php
/*
 * 检测应用更新
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
require_once  libfile('function/admin');
include_once libfile('function/cache');
$map=array();
$today = dgmdate(TIMESTAMP,'Ymd');
$map["available"]=1;
$applist =DB::fetch_all("select * from %t where `available`>0",array('app_market'));
 
$return = array("sum"=>0);
$num=0;
if( $applist ){
	$dzz_upgrade = new dzz_upgrade_app();
	$appinfo=array();
	$appinfo["mysqlversion"] = helper_dbtool::dbversion();
	$appinfo["phpversion"] = PHP_VERSION ;
	$appinfo["dzzversion"] = CORE_VERSION; 
	foreach($applist as $k=>$v ){
		if(empty($v['app_path'])) $v['app_path']='dzz';
		$savedata=array();
		if( $v["mid"]>0){//云端检测
			if( $v["upgrade_version"] ){
				$num++;
			}else{ 
				//if( $v["mid"]==80 ){ 
					//根据当前版本查询是否需要更新 
					$info=array_merge($v,$appinfo);
					$response = $dzz_upgrade->check_upgrade_byversion( $info ); 
					if($response && $response["status"]==1 ) {
						$map=array( 
							"upgrade_version"=>serialize($response["data"]),
							"check_upgrade_time"=>dgmdate(TIMESTAMP,'Ymd')
						);
						$re=C::t('app_market')->update($v['appid'],$map);//C::tp_t('app_market')->where("appid=".$v['appid'])->save( $map );
						$num++;
					}
				//}
			}
		}else{//本地检测
			$file = DZZ_ROOT . './'.$v['app_path'].'/' . $v['identifier'] . '/dzz_app_' . $v['identifier'] . '.xml'; 
			if ( file_exists($file) ) {
				$importtxt = @implode('', file($file));
				$apparray = getimportdata('Dzz! app',0,0,$importtxt);
				if($apparray["app"]["version"]>$v["version"]){
					$num++;
					$savedata=array( "upgrade_version"=>serialize($apparray["app"]), "check_upgrade_time"=>$today );
					$re=C::t('app_market')->update($v['appid'],$savedata);
					//$re= C::tp_t('app_market')->where("appid=".$v["appid"])->save( $savedata ); 
				}
			} 
		} 
	}
}
$return["sum"]=$num;
exit( json_encode( $return ) );
?>