<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}
require_once  libfile('function/admin');
$do = trim($_GET['do']);
$op=$_GET['op'];
if ($do == 'export') {//应用导出
	$appid = intval($_GET['appid']);
	$app = C::t('app_market') -> fetch($appid);
	if (!$app) {
		showmessage('application_nonentity');
	}
	if ($app['appico'] != 'dzz/images/default/icodefault.png' && !preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $app['appico'])) {
		$app['appico'] = $_G['setting']['attachdir'] . $app['appico'];
	}
	$app['appico'] = imagetobase64($app['appico']);
	$app['extra'] = array();
	$appid=$app['appid'];
	//unset($app['mid']);
	unset($app['appid']);
	unset($app['orgid']);
	unset($app['available']);
	unset($app['dateline']);
	unset($app['upgrade_version']);
	unset($app['check_upgrade_time']);
	$apparray = array();
	if ($app['identifier']) {
		if(empty($app['app_path'])) $app['app_path']='dzz';
		$entrydir = DZZ_ROOT . './'.$app['app_path'].'/' . $app['identifier'];
		if (file_exists($entrydir . '/install.php')) {
			$app['extra']['installfile'] = 'install.php';
		}
		if (file_exists($entrydir . '/uninstall.php')) {
			$app['extra']['uninstallfile'] = 'uninstall.php';
		}
		if (file_exists($entrydir . '/upgrade.php')) {
			$app['extra']['upgradefile'] = 'upgrade.php';
		}
		/*
		if (file_exists($entrydir . '/check.php')) {
			$app['extra']['checkfile'] = 'check.php';
		}*/
		if (file_exists($entrydir . '/enable.php')) {
			$app['extra']['enablefile'] = 'enable.php';
		}
		if (file_exists($entrydir . '/disable.php')) {
			$app['extra']['disablefile'] = 'disable.php';
		}
	}
	$apparray['app'] = $app;
	$apparray['version'] = strip_tags($_G['setting']['version']);
	$hooks=array();
	 
	foreach(DB::fetch_all("SELECT * FROM %t where `status`='1' and app_market_id='".$appid."' ORDER BY priority",array('hooks')) as $value) {
        $hooks[$value['name']]=$value['addons'];
		$hooks['_attributes'][$value['name']]=array("priority"=>$value['priority'],'description'=>$value['description']);
    }
	
	if($hooks) $apparray['hooks']=$hooks;
	exportdata('Dzz! app', $app['identifier'] ? $app['identifier'] : random(5), $apparray);
	exit();

}
elseif ($do == 'import') {//导入应用
	if (!submitcheck('importsubmit')) {
		include template('import');
	} else {
		$apparray = getimportdata('Dzz! app');
		if ($apparray['app']['identifier']) {
			if(empty($apparray['app']['app_path'])) $apparray['app']['app_path']='dzz';
			if (!is_dir(DZZ_ROOT . './'.$apparray['app']['app_path'].'/' . $apparray['app']['identifier'])) {
				showmessage(lang('list_cp_Application_directory_exist',array('app_path'=>$app['app_path'],'identifier'=>$app['identifier'])));
			}
			$extra = $apparray['app']['extra'];
			$filename = $extra['installfile']; 
			if (!empty($filename) && preg_match('/^[\w\.]+$/', $filename)) {
				$filename = DZZ_ROOT . './'.$apparray['app']['app_path'].'/' . $apparray['app']['identifier'] . '/' . $filename; 
				if (file_exists($filename)) {
					@include_once $filename;
				} else {
					$finish = TRUE;
				}
			} else { 
				$finish = TRUE;
			}
			if ($finish) {
				if ($app = importByarray($apparray, 1)) {
					cron_create($app);
				}

				showmessage(lang('application_import_successful'), ADMINSCRIPT . '?mod=appmarket', array(), array('alert' => 'right'));
			}
		} else {
			$app = importByarray($apparray, 0);
			showmessage('application_import_successful', ADMINSCRIPT . '?mod=appmarket', array(), array('alert' => 'right'));
		}
	}
}
elseif ($do == 'disable') {//关闭应用
	$appid = intval($_GET['appid']);
	if (!$app = C::t('app_market') -> fetch($appid)) {
		showmessage('list_cp_Application_delete');
	}
	if(empty($app['app_path'])) $app['app_path']='dzz';
	//system=2为系统应用禁止进行关闭等操作
	if ($app["system"]==2) {
		showmessage('system_cant_disable');
	}
	if ($app['identifier']) {
		$entrydir = DZZ_ROOT . './'.$app['app_path'].'/' . $app['identifier'];
		$file = $entrydir . '/dzz_app_' . $app['identifier'] . '.xml';
		if (!file_exists($file)) {
			$apparray['disablefile'] = $app['extra']['disablefile'];
			$apparray['app']['version'] = $app['version'];
		} else {
			$importtxt = @implode('', file($file));
			$apparray = getimportdata('Dzz! app');
		}
		if (!empty($apparray['disablefile']) && preg_match('/^[\w\.]+$/', $apparray['disablefile'])) {
			$filename = $entrydir . '/' . $apparray['disablefile'];
			if (file_exists($filename)) {
				@include $filename;
			} else {
				$finish = TRUE;
			}
		} else {
			$finish = TRUE;
		}
	} else {
		$finish = TRUE;
	}
	if ($finish) {
		C::t('app_market') -> update($appid, array('available' => 0));
		writelog('otherlog', "关闭应用 ".$app['appname']);
		showmessage('application_close_successful', $_GET['refer'], array(), array('alert' => 'right'));
	}

}
elseif ($do == 'enable') {//开启应用
	$appid = intval($_GET['appid']);
	if (!$app = C::t('app_market') -> fetch($appid)) {
		showmessage('list_cp_Application_delete');
	}
	if(empty($app['app_path'])) $app['app_path']='dzz';
	$finish = FALSE;
	if ($app['identifier']) {
		$entrydir = DZZ_ROOT . './'.$app['app_path'].'/' . $app['identifier'];
		$file = $entrydir . '/dzz_app_' . $app['identifier'] . '.xml';
		$app['extra'] && $app['extra']=unserialize($app['extra']);
		$app['appadminurl'] && $app['appadminurl'] = replace_canshu($app['appadminurl']);
		if( isset($app['extra']['enablefile']) && $app['extra']['enablefile'] ){
			$apparray['app']['extra'] = $app['extra'] ;
			$apparray['app']['version'] = $app['version']; 
		}else{
			if ( file_exists($file)) {
				$importtxt = @implode('', file($file));
				$apparray = getimportdata('Dzz! app'); 
			} else { 
				$apparray['app']['extra']['enablefile'] = "";
				$apparray['app']['version'] = $app['version'];
			}
		}
		if (!empty($apparray['app']['extra']['enablefile']) && preg_match('/^[\w\.]+$/', $apparray['app']['extra']['enablefile'])) {
			$filename = $entrydir . '/' . $apparray['app']['extra']['enablefile'];
			if (file_exists($filename)) {
				//调用语言包
				if( file_exists ($entrydir.'/language/'. $_G['language'].'/'.'lang.php') ){							
					include $entrydir.'/language/'. $_G['language'].'/'.'lang.php';
					$_G['lang']['template']=array_merge($_G['lang']['template'],$lang); 
				} 
				@include $filename;
			} else {
				//防止开启文件执行脚本丢失导致绕过开启脚本
				showmessage( 'enable_file_disappear' );
				//$finish = TRUE;
			}
		} else { 
			$finish = TRUE;
		}
	} else {
		$finish = TRUE;
	}
	if ($finish) {
		C::t('app_market') -> update($appid, array('available' => 1));
		writelog('otherlog', "开启应用 ".$app['appname']);
		showmessage('application_start_successful', $_GET['refer'], array(), array('alert' => 'right'));
	}

}
elseif ($do == 'install') {//安装应用
	$finish = FALSE;
	$dir = $_GET['app_path'];
	$appname = $_GET['app_name'];
	$xmlfile = 'dzz_app_' . $appname . '.xml';
	$importfile = DZZ_ROOT . './'.$dir.'/' . $appname . '/' . $xmlfile;
	if (!file_exists($importfile)) {
		showmessage('list_cp_Application_allocation' . '：' . $xmlfile, $_GET['refer']);
	}

	$importtxt = @implode('', file($importfile));

	$apparray = getimportdata('Dzz! app');
    if(empty($apparray['app']['app_path'])) $apparray['app']['app_path']=$dir;
	$filename = $apparray['app']['extra']['installfile'];

	$request_uri = ADMINSCRIPT . '?mod=appmarket';
	if (!empty($filename) && preg_match('/^[\w\.]+$/', $filename)) {
		$filename = DZZ_ROOT . './'.$dir.'/' . $appname . '/' . $filename;
		if (file_exists($filename)) {
			@include_once $filename;
		} else {
			$finish = TRUE;
		}
	} else {
		$finish = TRUE;
	}
	if ($finish) {
		if ($app = importByarray($apparray, 1)) {
			cron_create($app);
		}
		writelog('otherlog', "安装应用 ".$apparray['app']['appname']);
		showmessage('application_install_successful', ADMINSCRIPT . '?mod=appmarket', array(), array('alert' => 'right'));
	}

}
elseif ($do == 'uninstall') {//卸载应用
	$appid = intval($_GET['appid']); 
	if (!$app = C::t('app_market') -> fetch($appid)) {
		showmessage('list_cp_Application_delete', '', array(), array('alert' => 'right'));
	}
	$app['extra'] = unserialize($app['extra']);
	$finish = FALSE;
	$request_uri = ADMINSCRIPT . '?mod=app';
	$refer = $_GET['refer']; 
	$appinfo=$app;
	$msg='';
	if ($app['identifier']) {
		$entrydir = DZZ_ROOT . './'.$app['app_path'].'/' . $app['identifier'];
		$file = $entrydir . '/dzz_app_' . $app['identifier'] . '.xml';
		if (!file_exists($file)) {
			$apparray['app']['extra']['uninstallfile'] = $app['extra']['uninstallfile'];
			$apparray['app']['version'] = $app['version'];
		} else {
			$importtxt = @implode('', file($file));
			$apparray = getimportdata('Dzz! app');
		}

		if (!empty($apparray['app']['extra']['uninstallfile']) && preg_match('/^[\w\.]+$/', $apparray['app']['extra']['uninstallfile'])) {
			$filename = $entrydir . '/' . $apparray['app']['extra']['uninstallfile'];
			if (file_exists($filename)) {
				$confirm_uninstall_url= outputurl( $_G['siteurl'].MOD_URL.'&op=cp&do=uninstall_confirm&appid='.$appid.'&refer='.urlencode($refer) );
				@include $filename;
			} else {
				$finish = TRUE;
			}
		} else {
			$finish = TRUE;
		}
		$msg .= lang('list_cp_del_Application') . $app['identifier'];
	} else {
		$finish = TRUE;
	}
	$msg = lang('app_upgrade_uninstall_successful', array('upgradeurl' => upgradeinformation_app(-10))).$msg;
	if ($finish) { 
		C::t('app_market') -> delete_by_appid($appid);
		cron_delete($app);
		//删除安装临时文件
		$temp_install=DZZ_ROOT.'./data/update/app/'.$app['app_path'].'/'.$app['identifier'];
		removedirectory($temp_install);
		writelog('otherlog', "卸载应用 ".$app['appname']);
		showmessage($msg, ADMINSCRIPT . '?mod=appmarket', array(), array('alert' => 'right'));
	}
}
elseif ($do == 'uninstall_confirm') {//卸载应用
	$refer = $_GET['refer']; 
	$appid=intval($_GET['appid']);
	if(!$app=C::t('app_market')->fetch($appid)){
		exit('Access Denied');
	}
	$app_delete_confirm=lang('app_delete_confirm', array('appname' => $app['appname'])); 
	include template('uninstall_confirm');
	exit;
}
elseif ($do == 'upgrade') {//本地升级应用
	$appid = intval($_GET['appid']);
	if (!$app = C::t('app_market') -> fetch($appid)) {
		showmessage('list_cp_Application_delete');
	}
	$finish = FALSE;
	$msg = lang('application_upgrade_successful');

	$entrydir = DZZ_ROOT . './'.$app['app_path'].'/' . $app['identifier'];
	$file = $entrydir . '/dzz_app_' . $app['identifier'] . '.xml';
	if (!file_exists($file)) {
		showmessage('list_cp_Application_tautology');
	}
	$importtxt = @implode('', file($file));
	$apparray = getimportdata('Dzz! app', 0, 0, $importtxt);

	$filename = $apparray['app']['extra']['upgradefile'];
	$toversion = $apparray['app']['version'];
	if (!empty($apparray['app']['extra']['upgradefile']) && preg_match('/^[\w\.]+$/', $apparray['app']['extra']['upgradefile'])) {
		$filename = $entrydir . '/' . $apparray['app']['extra']['upgradefile'];
		if (file_exists($filename)) {
			@include $filename;
		} else {
			$finish = TRUE;
		}
	} else {
		$finish = TRUE;
	}
	if ($finish) {
		C::t('app_market') -> update($appid, array('version' => $toversion));
		showmessage('application_upgrade_successful', ADMINSCRIPT . '?mod=appmarket', array(), array('alert' => 'right'));
	}

} 
?>
