<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

define('IN_ADMIN', TRUE);
define('NOROBOT', TRUE);
define('ADMINSCRIPT', basename(__FILE__));
define('BASESCRIPT', basename(__FILE__));
define('CURSCRIPT', 'admin');
define('APPTYPEID', 0);
require './core/class/class_core.php';
require './dzz/function/dzz_core.php';
require './user/function/function_user.php';
require './core/function/function_misc.php';
require './core/core_version.php';
$dzz = C::app();
$dzz->init();
$admincp = new dzz_admincp();
$admincp->core  =  $dzz;
$admincp->init();

$mod = !empty($_GET['mod']) ? $_GET['mod'] :  '';
if(!$mod) dheader("location: index.php");
$op = !empty($_GET['op']) ? $_GET['op'] : 'index';
$modarr_1=array('login','setting','system','organization','wallpaper','app','appdefault','cloud','thame','icon','filemanage','share');
$modarr_0=array('orguser','member');
if(!in_array($mod,$modarr_0) && !in_array($mod,$modarr_1)) showmessage('undefined_action', '', array('mod' => $mod));
define('CURMODULE',$mod);

if($_G['adminid']!=1 && !in_array($mod,$modarr_0)) showmessage('undefined_action', '', array('mod' => $mod));

if(strpos(strtolower($mod),':')!==false){
	$modfile='./admin/'.str_replace(':','/',$mod).'/'.($op?$op:'index').'.php';
	if(@!file_exists(DZZ_ROOT.$modfile)){
		showmessage('file_nonexistence');
	}
}else{
	if(@!file_exists(DZZ_ROOT.($modfile = './admin/'.$mod.'/'.$op.'.php'))) {
		showmessage('undefined_action', '', array('mod' => $mod));
	}
}

include DZZ_ROOT.$modfile;
?>
