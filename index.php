<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
define('APPTYPEID', 1);
define('CURSCRIPT', 'dzz');
define('DZZSCRIPT', basename(__FILE__));
define('BASESCRIPT', basename(__FILE__));
require './core/class/class_core.php';
require './dzz/function/dzz_core.php';
$dzz = C::app();
$cachelist = array();
$dzz->cachelist = $cachelist;
$dzz->init();
$mod = $_GET['mod'];
$mod = !empty($mod) ? $mod :  '';
$op = !empty($_GET['op']) ? $_GET['op'] : 'index';
//调用各自的模块
if(empty($mod)){
	if($_G['uid']<1 && $_G['setting']['loginset']['available']){
		@header("Location: user.php?mod=logging".($_GET['referer']?'&referer='.$_GET['referer']:''));
		exit();
	}
	define('CURMODULE', 'dzzindex');
	require DZZ_ROOT.'./dzz/index.php';
}else{
	
	if(strpos(strtolower($mod),':')!==false){
		$patharr=explode(':',$mod);
		foreach($patharr as $path){
			if(!preg_match("/\w+/i",$path)) showmessage('undefined_action');
		}
		define('CURMODULE', str_replace(':','/',$mod));
		$modfile='./dzz/'.str_replace(':','/',$mod).'/'.($op?$op:'index').'.php';
		if(@!file_exists(DZZ_ROOT.$modfile)){
			showmessage($modefile.lang('message','file_nonexistence',array('modfile'=>$modfile)));
		}
	}else{
		if(!preg_match("/\w+/i",$mod)) showmessage('undefined_action');
		if(!preg_match("/\w+/i",$op)) showmessage('undefined_action');
		define('CURMODULE', $mod);
		if(@!file_exists(DZZ_ROOT.($modfile = './dzz/'.$mod.'/'.$op.'.php'))) {
			showmessage('undefined_action', '', array('mod' => $mod));
		}
	}
	include DZZ_ROOT.$modfile;
}
?>
