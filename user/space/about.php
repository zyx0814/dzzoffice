<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
$about=array();
$identify=$_GET['modname'];
$ismobile=helper_browser::ismobile();
$appConfig=DZZ_ROOT.'./dzz/'.$identify.'/config/config.php';
if($identify && file_exists($appConfig)){
	$config=include($appConfig);
	if(isset($config['about'])){
		$about=$config['about'];
		$about['sitelogo']=$_G['setting']['sitelogo']?'index.php?mod=io&op=thumbnail&size=small&path='.dzzencode('attach::'.$_G['setting']['sitelogo']):'static/image/common/logo.png';
		$appinfo=C::t('app_market')->fetch_by_identifier($identify);
		if(empty($about['logo'])){
			$about['logo']=$_G['setting']['attachurl'].$appinfo['appico'];
		}
		if(empty($about['version'])) $about['version']=$appinfo['version'];
	}
}
if(empty($about['name_en'])){
	$about['sitelogo']='';
	$about['name_zh']='';//中文名称:大桌子协同办公，留空不显示
	$about['name_en']='Office';//英文名称，注意前面的dzz去掉，留空不显示
	$about['version']='V'.CORE_VERSION;//版本信息，留空不显示
	//中间大图
	$about['logo']=$_G['setting']['sitelogo']?'index.php?mod=io&op=thumbnail&size=small&path='.dzzencode('attach::'.$_G['setting']['sitelogo']):'static/image/common/logo.png';
}

//站点logo,留空不显示
//
if ($ismobile && !$_GET['inajax']) {
	include template('mobile_about');
} else {
	include template('about');
}
exit();