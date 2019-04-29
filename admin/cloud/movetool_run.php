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
@ini_set('max_execution_time',0);
@set_time_limit(0);
   $gets = array(
		'mod'=>'cloud',
		'op'=>'movetool_run',
		'oremoteid'=>intval($_GET['oremoteid']),
		'remoteid' =>intval($_GET['remoteid']),
		'exts'=>trim($_GET['exts']),
		'sizelt'=>$_GET['sizelt'],
		'sizegt'=>$_GET['sizegt'],
		
	);
	$gets['aid']=intval($_GET['aid']);
	$runurl = BASESCRIPT."?".url_implode($gets);
	$gets['aid1']=intval($_GET['aid1']);
	$gets['dateline']=intval($_GET['dateline']);
	

	//获取需要迁移的数据量
	if($attach=C::t('attachment')->getAttachByFilter($gets)){
		//print_r($attach);exit($runurl);
		$runurl.='&dateline='.$attach['dateline'].'&aid1='.$attach['aid'];
	 try{
		updatesession();
		if($re=io_remote::Migrate($attach,$gets['remoteid'])){
			//print_r($re);exit();
			include template('common/header_common');
			echo "<script type=\"text/javascript\">";
			echo "parent.setProgress(".json_encode($re).");";
			if(!$re['error']){
			  echo "window.location.href='".$runurl."'";
			}
			echo "</script>";	
			include template('common/footer');
			exit();
		 }
		}catch(Exception $e){
			$attach['error']=$e->getMessage();
			include template('common/header_common');
			echo "<script type=\"text/javascript\">";
			echo "parent.setProgress(".json_encode($attach).");";
			echo "</script>";	
			include template('common/footer');
			exit();
		}
	}else{
		//完成；
		include template('common/header_common');
		echo "<script type=\"text/javascript\">";
		echo "parent.setComplete();";
		echo "</script>";	
		include template('common/footer');
		exit();
	}


?>