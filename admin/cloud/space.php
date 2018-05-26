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
$cloud=DB::fetch_first("select * from %t where bz='dzz'",array('connect'));
$navtitle=$cloud['name'].' - '.lang('space_management');
if($_GET['do']=='checkspace'){
	$remoteid=intval($_GET['remoteid']);
	if($arr=C::t('local_storage')->update_sizecount_by_remoteid($remoteid)){
		$arr['fusesize']=formatsize($arr['usesize']);
		if ($arr['totalsize'])
			$arr['ftotalsize'] = formatsize($arr['totalsize']);
		else
			$arr['ftotalsize'] = lang('unlimited');
	}
	echo json_encode($arr);
	exit();
}elseif($_GET['do']=='delete'){
	$remoteid=intval($_GET['remoteid']);
	$re=C::t('local_storage')->delete_by_remoteid($remoteid);
	if($re['error']) showmessage($re['error'],dreferer());
	showmessage('do_success',dreferer());
}else{
	if(submitcheck('cloudsubmit')){
		$isdefault=intval($_GET['isdefault']);
		foreach($_GET['name'] as $remoteid => $value){
				$setarr=array(
							  'disp'=>intval($_GET['disp'][$remoteid]),
							  'isdefault'=>($remoteid==$isdefault)?1:0
							  );
				if(!empty($value)) $setarr['name']=getstr($value);
				C::t('local_storage')->update($remoteid,$setarr);
			}
		showmessage('do_success',dreferer());
	}else{
		$list=array();
		foreach(C::t('local_storage')->fetch_all_orderby_disp() as $key=>$value){
			    
			if($arr=C::t('local_storage')->update_sizecount_by_remoteid($value['remoteid'])){
				$value['fusesize']=formatsize($value['usesize']);
				if ($value['totalsize'])
					$value['ftotalsize'] = formatsize($value['totalsize']);
				else
					$value['ftotalsize'] = lang('unlimited');
			}
			
			$list[]=$value;
		}
	}
	include template('space');
}
?>
