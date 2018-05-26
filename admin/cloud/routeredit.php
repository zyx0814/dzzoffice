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
if(submitcheck('addroutersubmit')){
	$routerid=intval($_GET['routerid']);
	$router=dhtmlspecialchars($_GET['router']);
	if(empty($router['name'])){
		showmessage('name_cannot_empty', dreferer());

	}
	$router['router']['exts']=empty($router['router']['exts'])?array():explode(',',trim($router['router']['exts']));
	$router['router']['size']['lt']=is_numeric($router['router']['size']['lt'])?intval($router['router']['size']['lt']):'';
	$router['router']['size']['gt']=is_numeric($router['router']['size']['gt'])?intval($router['router']['size']['gt']):'';
	$router['remoteid']=intval($router['remoteid']);
	$router['priority']=empty($router['priority'])?100:intval($router['priority']);
	if($routerid){
		C::t('local_router')->update($routerid,$router);
	}else{
		$router['dateline']=TIMESTAMP;
		C::t('local_router')->insert($router);
	}
	
	showmessage('do_success',BASESCRIPT.'?mod=cloud&op=router');
}else{
	$routerid=intval($_GET['routerid']);
	$router=C::t('local_router')->fetch_by_routerid($routerid);
	$spaces=C::t('local_storage')->fetch_all_orderby_disp();
	$navtitle=lang('routing_management').' - '.lang('add_routing');
	include template('routeredit');
}
?>
