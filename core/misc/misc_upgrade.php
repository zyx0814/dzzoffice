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
				$list[$type]['note']= '要求配置达到 php v'.PHP_VERSION.'MYSQL v'.$dbversion;
			}
			$list[$type]['appid']=$appid;
			$list[$type]['official']='<a class="btn btn-link" href="'.$upgrade['official'].'" target="_blank" onclick="jQuery(\'#notice\').hide();">查看详细</a>';
			$list[$type]['title']='DzzOffice'.$upgrade['latestversion'];
			$list[$type]['appurl']= 'admin.php?mod=system&op=upgrade';
			//&operation='.$type.'&version='.$upgrade['latestversion'].'&locale='.$locale.'&charset='.$charset.'&release='.$upgrade['latestrelease'];
		}
	 if($list){
		 $html=' <div class="panel panel-success" style="margin:0;min-width:250px;">';
		 $html.=' <div class="panel-heading" style="border-radius:0">';
		 $html.='   <h3 class="panel-title">';
		 $html.='      程序有新版本';
		 $html.='     <button type="button" class="close" onclick="jQuery(\'#notice\').hide();setcookie(\'upgradenotice\',1,3600);"><span aria-hidden="true">×</span></button>';
		 $html.='   </h3>';
		 $html.=' </div>';
		 $html.=' <div class="panel-body text-center" style="padding:0">';
		 $html.='  <table class="table" style="margin:0">';
		 foreach($list as $type =>$value){
		 $html.=  '<tr><td><b>'.$value['title'].'</b><br><a class="btn btn-link" href="javascript:;" onclick="jQuery(\'#notice\').hide();OpenApp(\''.$value['appid'].'\',\''.$value['appurl'].'\');return false;">现在升级</a>&nbsp;&nbsp;'.$value['official'].'</td></tr>';
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
	require_once libfile('function/admincp');
	require_once libfile('function/plugin');
	require_once libfile('function/cloudaddons');
	$pluginarray = C::t('common_plugin')->fetch_all_data();
	$addonids = $vers = array();
	foreach($pluginarray as $row) {
		if(ispluginkey($row['identifier'])) {
			$addonids[] = $row['identifier'].'.plugin';
			$vers[$row['identifier'].'.plugin'] = $row['version'];
		}
	}
	$checkresult = dunserialize(cloudaddons_upgradecheck($addonids));
	savecache('addoncheck_plugin', $checkresult);
	$newversion = 0;
	foreach($checkresult as $addonid => $value) {
		list(, $newver, $sysver) = explode(':', $value);
		if($sysver && $sysver > $vers[$addonid] || $newver) {
			$newversion++;
		}
	}
	include template('common/header_ajax');
	if($newversion) {
		$lang = lang('forum/misc');
		echo '<div class="bm"><div class="bm_h cl"><a href="javascript:;" onclick="$(\'plugin_notice\').style.display=\'none\';setcookie(\'pluginnotice\', 1, 86400)" class="y" title="'.$lang['patch_close'].'">'.$lang['patch_close'].'</a>';
		echo '<h2 class="i">'.$lang['plugin_title'].'</h2></div><div class="bm_c">';
		echo '<div class="cl bbda pbm">'.lang('forum/misc', 'plugin_memo', array('number' => $newversion)).'</div>';
		echo '<div class="ptn cl"><a href="admin.php?action=plugins" class="xi2 y">'.$lang['plugin_link'].' &raquo;</a></div>';
		echo '</div></div>';
	}
	include template('common/footer_ajax');
	exit;
} 



?>