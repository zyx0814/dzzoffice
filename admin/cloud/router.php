<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}
$navtitle=lang('routing_management');
if(submitcheck('routersubmit')){
	$_GET=dhtmlspecialchars($_GET);
	$delete=$_GET['delete'];
	foreach($_GET['name'] as $routerid => $value){
		if(in_array($routerid,$delete)) continue;
		$setarr=array(
					  'priority'=>intval($_GET['priority'][$routerid]),
					  'available'=>intval($_GET['available'][$routerid]),
					  );
		if(!empty($value)) $setarr['name']=$value;
		C::t('local_router')->update($routerid,$setarr);
	}
	C::t('local_router')->delete($delete);
	showmessage('do_success',dreferer());
}else{
	
	$storage=array();
	foreach(C::t('local_storage')->fetch_all_orderby_disp() as $key=>$value){
			$value['fusesize']=formatsize($value['usesize']);
			if($value['totalsize']) $value['ftotalsize']=formatsize($value['totalsize']);
			else $value['ftotalsize']=lang('unlimited');
		$storage[$value['remoteid']]=$value;
	}
	$list=array();
	foreach(C::t('local_router')->fetch_all_orderby_priority() as $value){
		$value['position']=$storage[$value['remoteid']]['name'];
		$value['bz_available']=$storage[$value['remoteid']]['available'];
		$value['bz']=$storage[$value['remoteid']]['bz'];
		$list[$value['routerid']]=$value;
	}
}
include template('router');


?>
