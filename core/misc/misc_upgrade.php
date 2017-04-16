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

} elseif($_GET['action'] == 'upgradenotice') {
   
	$html='';
	$list = array();
	if($_G['member']['adminid'] == 1) {
		$notelist='';
		$dbversion = helper_dbtool::dbversion();
		$appid=C::t('app_market')->fetch_appid_by_mod('mod=system');
		foreach($_G['setting']['upgrade'] as $type => $upgrade) {
			if(version_compare($upgrade['phpversion'], PHP_VERSION) > 0 || version_compare($upgrade['mysqlversion'], $dbversion) > 0) {
				$list[$type]['note']= lang('require_allocation_attain').' php v'.PHP_VERSION.'MYSQL v'.$dbversion;
			}
			$list[$type]['appid']=$appid;
			$list[$type]['official']='<a class="btn btn-link" href="'.$upgrade['official'].'" target="_blank" onclick="jQuery(\'#notice\').hide();">'.lang('examine_details').'</a>';
			$list[$type]['title']='DzzOffice'.$upgrade['latestversion'];
			$list[$type]['appurl']= 'admin.php?mod=system&op=upgrade';
			//&operation='.$type.'&version='.$upgrade['latestversion'].'&locale='.$locale.'&charset='.$charset.'&release='.$upgrade['latestrelease'];
		}
	 if($list){
		 $html=' <div class="panel panel-success" style="margin:0;min-width:250px;">';
		 $html.=' <div class="panel-heading" style="border-radius:0">';
		 $html.='   <h3 class="panel-title">';
		 $html.=     lang('the_program_has_a_new_version');
		 $html.='     <button type="button" class="close" onclick="jQuery(\'#notice\').hide();setcookie(\'upgradenotice\',1,3600);"><span aria-hidden="true">×</span></button>';
		 $html.='   </h3>';
		 $html.=' </div>';
		 $html.=' <div class="panel-body text-center" style="padding:0">';
		 $html.='  <table class="table" style="margin:0">';
		 foreach($list as $type =>$value){
		 $html.=  '<tr><td><b>'.$value['title'].'</b><br><a class="btn btn-link" href="javascript:;" onclick="jQuery(\'#notice\').hide();OpenApp(\''.$value['appid'].'\',\''.$value['appurl'].'\');return false;">'.lang('Upgrade_Now').'</a>&nbsp;&nbsp;'.$value['official'].'</td></tr>';
		 }
		 $html.=' </table>';
		 $html.=' </div>';
		 $html.='</div>';
	 }
	}
	include template('common/header_ajax');
	echo $html;
	include template('common/footer_ajax');
	exit;

} elseif($_GET['action'] == 'appnotice') {
	
} 



?>