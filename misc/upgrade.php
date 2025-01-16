<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
include_once DZZ_ROOT.'./core/core_version.php';
if($_GET['action'] == 'checkupgrade') {
	header('Content-Type: text/javascript');
	if($_G['uid'] && $_G['member']['adminid'] == 1) {
		$dzz_upgrade = new dzz_upgrade();
		$dzz_upgrade->check_upgrade();
		dsetcookie('checkupgrade', 1, 60*60*24);
	}
	exit; 
}elseif($_GET['action'] == 'checkappupgrade') {
	header('Content-Type: text/javascript'); 
	if($_G['uid'] && $_G['member']['adminid'] == 1) { 
		$dzz_upgrade_app = new dzz_upgrade_app(); 
		$dzz_upgrade_app->check_upgrade();
		dsetcookie('checkappupgrade', 1, 3600);
	}
	exit; 
} elseif($_GET['action'] == 'upgradenotice') {
	$html='';
	$list = array();
	if($_G['setting']['upgradetis'] !== '3' && $_G['member']['adminid'] == 1) {
		if($_G['setting']['upgradetis'] !== '1'){
			//系统升级信息
			$dbversion = helper_dbtool::dbversion();
			if (is_array($_G['setting']['upgrade']) || is_object($_G['setting']['upgrade'])) {
				foreach($_G['setting']['upgrade'] as $type => $upgrade) {
					if(version_compare($upgrade['phpversion'], PHP_VERSION) > 0 || version_compare($upgrade['mysqlversion'], $dbversion) > 0) {
						$list[$type]['note']= lang('require_allocation_attain').' php v'.PHP_VERSION.'MYSQL v'.$dbversion;
					}
					$list[$type]['icon']='dzz/images/default/notice_system.png';
					$list[$type]['official']='admin.php?mod=system&op=systemupgrade';
					$list[$type]['title']='DzzOffice &nbsp;<b>'.$upgrade['latestversion'].'</b>';
					$list[$type]['appurl']= 'admin.php?mod=system&op=systemupgrade';
				}
			}
		}
		if($_G['setting']['upgradetis'] !== '2'){
			//查询所有待更新的应用
			$app_need_upgrade_list = DB::fetch_all("SELECT * FROM " . DB::table('app_market') . " WHERE 1 and upgrade_version!='' and available>0 ");
			foreach($app_need_upgrade_list as $type => $upgrade) {
				$upgrade['upgrade_version']=unserialize($upgrade['upgrade_version']);
				$list[$type]['icon']=$_G['setting']['attachurl'].$upgrade['appico'];
				$list[$type]['official']='admin.php?mod=appmarket&op=appupgrade';
				$list[$type]['title']=$upgrade['appname'].'&nbsp;<b>'.$upgrade['upgrade_version']['version'].'</b>';
				$list[$type]['appurl']= replace_canshu($upgrade['appurl']);
			}
		}
		if($list){
			$html=' <div class="panel panel-warning toast show" style="margin:0;min-width:300px;">';
			$html.=' <div class="panel-heading toast-header" style="border-radius:0"><strong class="me-auto">';
			$html.=     lang('upgrade_notice_title');
			$html.='     </strong><button type="button" class="btn-close" onclick="jQuery(\'#systemNotice\').hide();setcookie(\'upgradenotice\',1,3600);"></button>';
			$html.=' </div>';
			$html.=' <div class="panel-body" style="padding:0;max-height:500px;overflow-y:auto;width:100%;">';
			$html.='  <table class="table table-hover" style="margin:0">';
			foreach($list as $type =>$value){
				$html.=  '<tr><td><div style="line-height:30px;"><img src="'.$value['icon'].'" style="max-height:30px;" /><a class="dcolor" href="'.$value['official'].'" title="'.lang('examine_details').'">'.$value['title'].'</a></div>';
				if($value['note']){
					$html.= '<div class="text-muted" style="font-size:12px;margin-left:40px;">'.$value['note'].'</div>';
				}
				$html.=  '</td></tr>';
			}
			$html.=' </table>';
			$html.=' </div>';
			$html.='</div>';
		}
	}
	echo $html;
	exit;

} elseif($_GET['action'] == 'appnotice') {
	
} 



?>