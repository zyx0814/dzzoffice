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
if(submitcheck('movesubmit')){
   $gets = array(
		'mod'=>'cloud',
		'op'=>'movetool_run',
		'oremoteid'=>intval($_GET['oremoteid']),
		'remoteid' =>intval($_GET['remoteid']),
		'exts'=>trim($_GET['router']['exts']),
		'sizelt'=>$_GET['router']['size']['lt'],
		'sizegt'=>$_GET['router']['size']['gt'],
	);
	$runurl = BASESCRIPT."?".url_implode($gets);
	if(!$sourcedata=C::t('local_storage')->fetch_by_remoteid($gets['oremoteid'])){
		showmessage('原存储位置不存在',dreferer());
	}
	$sourcedata['fusesize']=formatsize($sourcedata['usesize']);
	if($sourcedata['totalsize']) $sourcedata['ftotalsize']=formatsize($sourcedata['totalsize']);
	else $sourcedata['ftotalsize']='无限制';
	if(!$targetdata=C::t('local_storage')->fetch_by_remoteid($gets['remoteid'])){
		showmessage('目标存储位置不存在',dreferer());
	}
	
	$targetdata['fusesize']=formatsize($targetdata['usesize']);
	if($targetdata['totalsize']) $targetdata['ftotalsize']=formatsize($targetdata['totalsize']);
	else $targetdata['ftotalsize']='无限制';
	
	//获取需要迁移的数据量
	$movesize=C::t('attachment')->getAttachByFilter($gets,1);
	$fmovesize=formatsize($movesize);
	if(!$first=C::t('attachment')->getAttachByFilter($gets)){
		showmessage('没有需要移动的数据',dreferer());
	}
	$first['fsize']=formatsize($first['filesize']);
	include template('movetool_run');
}else{
	$spaces=array();
	foreach(C::t('local_storage')->fetch_all_orderby_disp() as $key=>$value){
		if($arr=C::t('local_storage')->update_sizecount_by_remoteid($value['remoteid'])){
			$value['fusesize']=formatsize($arr['usesize']);
			if($arr['totalsize']) $value['ftotalsize']=formatsize($arr['totalsize']);
			else $value['ftotalsize']='无限制';
		}
		$spaces[$value['remoteid']]=$value;
	}
	$spaces_json=json_encode($spaces);
	include template('movetool');
}
?>
