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
	unset($app['appid']);
	unset($app['orgid']);
	unset($app['available']);
	unset($app['dateline']);
	$apparray = array();
	if ($app['identifier']) {
		$entrydir = DZZ_ROOT . './dzz/' . str_replace(':','/',$app['identifier']);

		if (file_exists($entrydir . '/install.php')) {
			$app['extra']['installfile'] = 'install.php';
		}
		if (file_exists($entrydir . '/uninstall.php')) {
			$app['extra']['uninstallfile'] = 'uninstall.php';
		}
		if (file_exists($entrydir . '/upgrade.php')) {
			$app['extra']['upgradefile'] = 'upgrade.php';
		}
		if (file_exists($entrydir . '/check.php')) {
			$app['extra']['checkfile'] = 'check.php';
		}
		if (file_exists($entrydir . '/enable.php')) {
			$app['extra']['enablefile'] = 'enable.php';
		}
		if (file_exists($entrydir . '/disable.php')) {
			$app['extra']['disablefile'] = 'disable.php';
		}
	}
	$apparray['app'] = $app;
	$apparray['version'] = strip_tags($_G['setting']['version']);
	exportdata('Dzz! app', $app['identifier'] ? str_replace(':','_',$app['identifier']) : random(5), $apparray);
	exit();

} elseif ($do == 'import') {//导入应用
	if (!submitcheck('importsubmit')) {
		include template('import');
	} else {
		$apparray = getimportdata('Dzz! app');
		if ($apparray['app']['identifier']) {
			if (!is_dir(DZZ_ROOT . './dzz/' . str_replace(':','/',$apparray['app']['identifier']))) {
				showmessage('list_cp_Application_directory_exist');
			}
			$extra = unserialize($apparray['app']['extra']);
			$filename = $extra['installfile'];
			if (!empty($filename) && preg_match('/^[\w\.]+$/', $filename)) {
				$filename = DZZ_ROOT . './dzz/' . str_replace(':','/',$apparray['app']['identifier']) . '/' . $filename;
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
					cron_create($app['identifier']);
				}

				showmessage(lang('application_import_successful'), ADMINSCRIPT . '?mod=app&op=list', array(), array('alert' => 'right'));
			}
		} else {
			$app = importByarray($apparray, 0);
			showmessage('application_import_successful', ADMINSCRIPT . '?mod=app&op=list', array(), array('alert' => 'right'));
		}
	}
} elseif ($do == 'disable') {//关闭应用
	$appid = intval($_GET['appid']);
	if (!$app = C::t('app_market') -> fetch($appid)) {
		showmessage('list_cp_Application_delete');
	}
	if ($app['identifier']) {
		$entrydir = DZZ_ROOT . './dzz/' . str_replace(':','/',$app['identifier']);
		$file = $entrydir . '/dzz_app_' . str_replace(':','_',$app['identifier']) . '.xml';
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
		showmessage('application_close_successful', $_GET['refer'], array(), array('alert' => 'right'));
	}

} elseif ($do == 'enable') {//开启应用
	$appid = intval($_GET['appid']);
	if (!$app = C::t('app_market') -> fetch($appid)) {
		showmessage('list_cp_Application_delete');
	}
	$finish = FALSE;
	if ($app['identifier']) {
		$entrydir = DZZ_ROOT . './dzz/' . str_replace(':','/',$app['identifier']);
		$file = $entrydir . '/dzz_app_' . str_replace(':','_',$app['identifier']) . '.xml';
		if (!file_exists($file)) {
			$apparray['app']['extra']['enablefile'] = $app['extra']['enablefile'];
			$apparray['app']['version'] = $app['version'];
		} else {
			$importtxt = @implode('', file($file));
			$apparray = getimportdata('Dzz! app');
		}
		if (!empty($apparray['app']['extra']['enablefile']) && preg_match('/^[\w\.]+$/', $apparray['app']['extra']['enablefile'])) {
			$filename = $entrydir . '/' . $apparray['app']['extra']['enablefile'];
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
		C::t('app_market') -> update($appid, array('available' => 1));
		showmessage('application_start_successful', $_GET['refer'], array(), array('alert' => 'right'));
	}

} elseif ($do == 'install') {//安装应用
	$finish = FALSE;
	$dir = $_GET['dir'];
	$xmlfile = 'dzz_app_' . str_replace(':','_',$dir) . '.xml';
	$importfile = DZZ_ROOT . './dzz/' . str_replace(':','/',$dir) . '/' . $xmlfile;
	if (!file_exists($importfile)) {
		showmessage('list_cp_Application_allocation' . ':' . $xmlfile, $_GET['refer']);
	}
	$importtxt = @implode('', file($importfile));
	$apparray = getimportdata('Dzz! app');
	$filename = $apparray['app']['extra']['installfile'];
	$request_uri = ADMINSCRIPT . '?mod=app';
	if (!empty($filename) && preg_match('/^[\w\.]+$/', $filename)) {
		$filename = DZZ_ROOT . './dzz/' . str_replace(':','/',$dir) . '/' . $filename;
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
			cron_create($app['identifier']);
		}
		showmessage('application_install_successful', ADMINSCRIPT . '?mod=app&op=list&do=available', array(), array('alert' => 'right'));
	}

} elseif ($do == 'uninstall') {//卸载应用
	$appid = intval($_GET['appid']);
	if (!$app = C::t('app_market') -> fetch($appid)) {
		showmessage('list_cp_Application_delete', ADMINSCRIPT . '?mod=app&op=list&do=available', array(), array('alert' => 'right'));
	}
	$app['extra'] = unserialize($app['extra']);
	$finish = FALSE;
	$request_uri = ADMINSCRIPT . '?mod=app';
	$msg = lang('application_uninstall_successful');
	if ($app['identifier']) {
		$entrydir = DZZ_ROOT . './dzz/' . str_replace(':','/',$app['identifier']);
		$file = $entrydir . '/dzz_app_' . str_replace(':','_',$app['identifier']) . '.xml';
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
	if ($finish) {
		C::t('app_market') -> delete_by_appid($appid);
		cron_delete($app['identifier']);
		showmessage('application_uninstall_successful', ADMINSCRIPT . '?mod=app&op=list&do=available', array(), array('alert' => 'right'));
	}
} elseif ($do == 'upgrade') {
	$appid = intval($_GET['appid']);
	if (!$app = C::t('app_market') -> fetch($appid)) {
		showmessage('list_cp_Application_delete');
	}
	$finish = FALSE;
	$msg = lang('application_upgrade_successful');

	$entrydir = DZZ_ROOT . './dzz/' . str_replace(':','/',$app['identifier']);
	$file = $entrydir . '/dzz_app_' . str_replace(':','_',$app['identifier']) . '.xml';
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
		showmessage('application_upgrade_successful', ADMINSCRIPT . '?mod=app&op=list&do=updatelist', array(), array('alert' => 'right'));
	}

}
//include template('cp');
?>
