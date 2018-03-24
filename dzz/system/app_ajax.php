<?php
/* @authorcode  codestrings
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
global $_G;
$operation = $_GET['operation'] ? $_GET['operation'] : '';

if($operation == 'app'){
	$config = array();
	if($_G['uid']){
		$config = C::t('user_field')->fetch($_G['uid']);
		
		if(!$config){
			$config= dzz_userconfig_init();
			if($config['applist']){
				$applist=explode(',',$config['applist']);
			}else{
				$applist=array();
			}
		 }else{//检测不允许删除的应用,重新添加进去
			if($config['applist']){
				$applist=explode(',',$config['applist']);
			}else{
				$applist=array();
			}
			if($applist_n =array_keys(C::t('app_market')->fetch_all_by_notdelete($_G['uid']))) {
			
				$newappids = array();
				foreach ($applist_n as $appid) {
					if (!in_array($appid, $applist)) {
						$applist[] = $appid;
						$newappids[] = $appid;
					}
				}
				if ($newappids) C::t('app_user')->insert_by_uid($_G['uid'], $newappids);
				C::t('user_field')->update($_G['uid'], array('applist' => implode(',', $applist)));
			}
		 }

	}else{
		 $applist =array_keys(C::t('app_market')->fetch_all_by_default());
	}
	//获取已安装应用
	$app=C::t('app_market')->fetch_all_by_appid($applist); 
	$applist_1=array();
	
	foreach($app as $key => $value){
		if($value['isshow']<1) continue;
		if($value['available']<1) continue;
		if($value['position']<1) continue;//位置为无的忽略
		//判断管理员应用
		if($_G['adminid']!=1 && $value['group']==3){
			continue;
		}
		//if($value['system'] == 2) continue;
		$applist_1[] = $value; 
	}
	//对应用根据disp 排序
	if($applist_1){
		$sort = array(
			  'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
			  'field'     => 'disp', //排序字段
		);
		$arrSort = array();
		foreach($applist_1 AS $uniqid => $row){
			foreach($row AS $key=>$value){
				$arrSort[$key][$uniqid] = $value;
			}
		}
		if($sort['direction']){
			array_multisort($arrSort[$sort['field']], constant($sort['direction']), $applist_1);
		} 
	}
	
	include template('app_ajax');
	exit();
}



