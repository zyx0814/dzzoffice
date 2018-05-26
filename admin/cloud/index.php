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
$operation=trim($_GET['operation']);
$navtitle=lang('cloud_set');
if(empty($operation)) $operation='setting';
if($operation=='setting'){
	if(!submitcheck('cloudsubmit')){
		$list=array();
		foreach(DB::fetch_all("select * from ".DB::table('connect')." where 1 order by disp") as $value){
			
			if($value['type']=='pan' && (empty($value['key']) || empty($value['secret']))){
				$value['available']=0;
				$value['warning'] = lang('please_open_after_setting');
			}
			if(!is_file(DZZ_ROOT.'./core/class/io/io_'.($value['bz']).'.php')){
				$value['warning'] = lang('cloud_index_api') . ($value['bz']) . lang('cloud_index_php');
			}
			$list[]=$value;
		}
	}else{
		$_GET=dhtmlspecialchars($_GET);
		foreach($_GET['name'] as $bz => $value){
			if(empty($value)) continue;
			$setarr=array('name'=>$value,
						  'disp'=>intval($_GET['disp'][$bz]),
						  'available'=>intval($_GET['available'][$bz])
						  );
			if($bz=='dzz' && $setarr['available']<1) $setarr['available']=1;
			//没有定义api文件不能开启
			if(!is_file(DZZ_ROOT.'./core/class/io/io_'.($bz).'.php')) $setarr['available']=0;
			$connect=C::t('connect')->fetch($bz);
			//网盘类没有设置key或secret 不能启用
			if($connect['type']=='pan' && (empty($connect['key']) || empty($connect['secret']))){
				$setarr['available']=0;
			}
			C::t('connect')->update($bz,$setarr);
		}
		showmessage('do_success',dreferer());
	}
}elseif($operation=='delete'){
	$bz=trim($_GET['bz']);
	if(is_file(DZZ_ROOT.'./core/class/io/io_'.($bz).'.php')){
		showmessage(lang('cloud_index_sign') . $bz . lang('cloud_index_sign1') . DZZ_ROOT . './core/class/io/io_' . ($bz) . lang('cloud_index_sig2'), dreferer());
	}
	C::t('connect')->delete_by_bz($bz);
	showmessage('delete_success', dreferer());

}

include template('cloud');
?>
