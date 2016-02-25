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
require_once libfile('function/admin');
$do=trim($_GET['do']);
if($do=='export'){//应用导出
	$appid=intval($_GET['appid']);
	$app = C::t('app_market')->fetch($appid);
	if(!$app) {
		showmessage('应用不存在');
	}
	if($app['appico']!='dzz/images/default/icodefault.png' && !preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $app['appico'])){
		$app['appico']=$_G['setting']['attachdir'].$app['appico'];
	}
	$app['appico']=imagetobase64($app['appico']);
	$app['extra']=array();
	unset($app['appid']);unset($app['orgid']);unset($app['available']);unset($app['dateline']);
	$apparray = array();
	if($app['identifier']){
		$entrydir = DZZ_ROOT.'./dzz/'.$app['identifier'];
	
		if(file_exists($entrydir.'/install.php')) {
			$app['extra']['installfile'] = 'install.php';
		}
		if(file_exists($entrydir.'/uninstall.php')) {
			$app['extra']['uninstallfile'] = 'uninstall.php';
		}
		if(file_exists($entrydir.'/upgrade.php')) {
			$app['extra']['upgradefile'] = 'upgrade.php';
		}
		if(file_exists($entrydir.'/check.php')) {
			$app['extra']['checkfile'] = 'check.php';
		}
		if(file_exists($entrydir.'/enable.php')) {
			$app['extra']['enablefile'] = 'enable.php';
		}
		if(file_exists($entrydir.'/disable.php')) {
			$app['extra']['disablefile'] = 'disable.php';
		}
	}
	$apparray['app'] = $app;
	$apparray['version'] = strip_tags($_G['setting']['version']);
	exportdata('Dzz! app', $app['identifier']?$app['identifier']:random(5), $apparray);
	exit();
	
}elseif($do=='import'){//导入应用
	if(!submitcheck('importsubmit')){
		include template('import');
	}else{
		$apparray = getimportdata('Dzz! app');
		if($apparray['app']['identifier']){
			if(!is_dir(DZZ_ROOT.'./dzz/'.$apparray['app']['identifier'])){
				showmessage('应用目录不存在，请将应用文件放入dzz/下后重试!');
			}
			$extra=unserialize($apparray['app']['extra']);
			$filename = $extra['installfile'];
			if(!empty($filename) && preg_match('/^[\w\.]+$/', $filename)) {
				$filename = DZZ_ROOT.'./dzz/'.$$apparray['app']['identifier'].'/'.$filename;
				if(file_exists($filename)) {
					@include_once $filename;
				}else{
					$finish=TRUE;
				}
			}else{
				$finish=TRUE;
			}
			if($finish){
				if($app=importByarray($apparray,1)){
					cron_create($app['identifier']);
				}
				
				showmessage('应用导入成功',ADMINSCRIPT.'?mod=app&op=list',array(),array('alert'=>'right'));
			}
		}else{
			$app=importByarray($apparray,0);
			showmessage('应用导入成功',ADMINSCRIPT.'?mod=app&op=list',array(),array('alert'=>'right'));
		}
	}
}elseif($do=='disable'){//关闭应用
	$appid=intval($_GET['appid']);
	if(!$app = C::t('app_market')->fetch($appid)){
		showmessage('应用不存在,或已删除');
	}
	if($app['identifier']){
		$entrydir = DZZ_ROOT.'./dzz/'.$app['identifier'];
		$file = $entrydir.'/dzz_app_'.$value['identifier'].'.xml';
		if(!file_exists($file)) {
			$apparray['disablefile'] = $app['extra']['disablefile'];
			$apparray['app']['version'] = $app['version'];
		} else {
			$importtxt = @implode('', file($file));
			$apparray = getimportdata('Dzz! app');
		}
		if(!empty($apparray['disablefile']) && preg_match('/^[\w\.]+$/', $apparray['disablefile'])) {
			$filename =entrydir.'/'.$apparray['disablefile'];
			if(file_exists($filename)) {
				@include $filename;
			}else{
				$finish=TRUE;
			}
		}else{
			$finish = TRUE;
		}
	}else{
		$finish = TRUE;
	}
	if($finish) {
		C::t('app_market')->update($appid,array('available'=>0));
		showmessage('应用关闭成功',$_GET['refer'],array(),array('alert'=>'right'));
	}
	
}elseif($do=='enable'){//开启应用
	$appid=intval($_GET['appid']);
	if(!$app = C::t('app_market')->fetch($appid)){
		showmessage('应用不存在,或已删除');
	}
	$finish = FALSE;
	if($app['identifier']){
		$entrydir = DZZ_ROOT.'./dzz/'.$app['identifier'];
		$file = $entrydir.'/dzz_app_'.$value['identifier'].'.xml';
		if(!file_exists($file)) {
			$apparray['app']['extra']['enablefile'] = $app['extra']['enablefile'];
			$apparray['app']['version'] = $app['version'];
		} else {
			$importtxt = @implode('', file($file));
			$apparray = getimportdata('Dzz! app');
		}
		if(!empty($apparray['app']['extra']['enablefile']) && preg_match('/^[\w\.]+$/', $apparray['app']['extra']['enablefile'])) {
			$filename =entrydir.'/'.$apparray['app']['extra']['enablefile'];
			if(file_exists($filename)) {
				@include $filename;
			}else{
				$finish=TRUE;
			}
		}else{
			$finish = TRUE;
		}
	}else{
		$finish = TRUE;
	}
	if($finish) {
		C::t('app_market')->update($appid,array('available'=>1));
		showmessage('应用启用成功',$_GET['refer'],array(),array('alert'=>'right'));
	}

}elseif($do=='install'){//安装应用
	$finish = FALSE;
	$dir = $_GET['dir'];
	$xmlfile = 'dzz_app_'.$dir.'.xml';
	$importfile = DZZ_ROOT.'./dzz/'.$dir.'/'.$xmlfile;
	if(!file_exists($importfile)) {
		showmessage('应用目录内没有应用的配置文件：'.$xmlfile,$_GET['refer']);
	}
	$importtxt = @implode('', file($importfile));
	$apparray = getimportdata('Dzz! app');
	$filename = $apparray['app']['extra']['installfile'];
	$request_uri=ADMINSCRIPT.'?mod=app';
	if(!empty($filename) && preg_match('/^[\w\.]+$/', $filename)) {
		$filename = DZZ_ROOT.'./dzz/'.$dir.'/'.$filename;
		if(file_exists($filename)) {
			@include_once $filename;
		}else{
			$finish=TRUE;
		}
	}else{
		$finish=TRUE;
	}
	if($finish){
		if($app=importByarray($apparray,1)){
			cron_create($app['identifier']);
		}
		showmessage('应用安装成功',ADMINSCRIPT.'?mod=app&op=list&do=available',array(),array('alert'=>'right'));
	}

}elseif($do=='uninstall'){//卸载应用
	$appid=intval($_GET['appid']);
	if(!$app = C::t('app_market')->fetch($appid)){
		showmessage('应用不存在,或已删除',ADMINSCRIPT.'?mod=app&op=list&do=available',array(),array('alert'=>'right'));
	}
	$app['extra']=unserialize($app['extra']);
	$finish = FALSE;
	$request_uri=ADMINSCRIPT.'?mod=app';
	$msg='应用卸载成功!';
	if($app['identifier']){
		$entrydir = DZZ_ROOT.'./dzz/'.$app['identifier'];
		$file = $entrydir.'/dzz_app_'.$app['identifier'].'.xml';
		if(!file_exists($file)) {
			$apparray['app']['extra']['uninstallfile'] = $app['extra']['uninstallfile'];
			$apparray['app']['version'] = $app['version'];
		} else {
			$importtxt = @implode('', file($file));
			$apparray = getimportdata('Dzz! app');
		}
		
		if(!empty($apparray['app']['extra']['uninstallfile']) && preg_match('/^[\w\.]+$/', $apparray['app']['extra']['uninstallfile'])) {
			$filename =$entrydir.'/'.$apparray['app']['extra']['uninstallfile'];
			if(file_exists($filename)) {
				@include $filename;
			}else{
				$finish=TRUE;
			}
		}else{
			$finish=TRUE;
		}
		$msg.=',请手工删除应用文件目录：dzz/'.$app['identifier'];
	}else{
		$finish=TRUE;
	}
	if($finish){
		C::t('app_market')->delete_by_appid($appid);
		cron_delete($app['identifier']);
		showmessage('应用卸载成功',ADMINSCRIPT.'?mod=app&op=list&do=available',array(),array('alert'=>'right'));
	}
}elseif($do=='upgrade'){
	$appid=intval($_GET['appid']);
	if(!$app = C::t('app_market')->fetch($appid)){
		showmessage('应用不存在,或已删除');
	}
	$finish = FALSE;
	$msg='应用升级成功!';
	
	$entrydir = DZZ_ROOT.'./dzz/'.$app['identifier'];
	$file = $entrydir.'/dzz_app_'.$app['identifier'].'.xml';
	if(!file_exists($file)) {
		showmessage('应用配置文件不存在,请将应用配置文件放入应用目录后重试');
	} 
	$importtxt = @implode('', file($file));
	$apparray = getimportdata('Dzz! app',0,0,$importtxt);
	
	$filename = $apparray['app']['extra']['upgradefile'];
	$toversion = $apparray['app']['version'];
	if(!empty($apparray['app']['extra']['upgradefile']) && preg_match('/^[\w\.]+$/', $apparray['app']['extra']['upgradefile'])) {
		$filename =$entrydir.'/'.$apparray['app']['extra']['upgradefile'];
		if(file_exists($filename)) {
			@include $filename;
		}else{
			$finish=TRUE;
		}
	}else{
		$finish=TRUE;
	}
	if($finish){
		C::t('app_market')->update($appid,array('version'=>$toversion));
		showmessage('应用升级成功',ADMINSCRIPT.'?mod=app&op=list&do=updatelist',array(),array('alert'=>'right'));
	}
	
}
//include template('cp');

?>
