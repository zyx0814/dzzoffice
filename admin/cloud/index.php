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
if(empty($operation)) $operation='setting';
if($operation=='setting'){
	if(!submitcheck('cloudsubmit')){
		$list=array();
		foreach(DB::fetch_all("select * from ".DB::table('connect')." where 1 order by disp") as $value){
			
			if($value['type']=='pan' && (empty($value['key']) || empty($value['secret']))){
				$value['available']=0;
				$value['warning']='请设置后开启';
			}
			if(!is_file(DZZ_ROOT.'./core/class/io/io_'.($value['bz']).'.php')){
				$value['warning']='api文件：io_'.($value['bz']).'.php不存在！';
			}
			$list[]=$value;
		}
	}else{
		
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
		showmessage('标志为'.$bz.'的api正在使用，不能删除，如果确信此api不再使用，请ftp删除'.DZZ_ROOT.'./core/class/io/io_'.($bz).'.php后重试',dreferer());
	}
	C::t('connect')->delete_by_bz($bz);
	showmessage('删除成功！',dreferer());

}

include template('cloud');

?>
