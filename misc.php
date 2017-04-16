<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

define('APPTYPEID', 100);
define('CURSCRIPT', 'core');
define('CURMODULE', 'misc');

require './core/class/class_core.php';

$dzz = C::app();

$dzz->reject_robot();
$modarray = array('seccode','sendmail', 'stat','initsys', 'userstatus', 'signin','updatecache','seluser','ajax','syscache','movetospace','setunrun','upgrade','sendwx');

$modcachelist = array(
	
);

$mod = getgpc('mod');
$mod = (empty($mod) || !in_array($mod, $modarray)) ? 'error' : $mod;
if(in_array($mod, array('seccode', 'secqaa', 'initsys','movetospace','setunrun','ajax','syscache','stat','sendmail','sendwx'))) {
	define('ALLOWGUEST', 1);
}
$cachelist = array();
if(isset($modcachelist[$mod])) {
	$cachelist = $modcachelist[$mod];
}
$dzz->cachelist = $cachelist;
switch ($mod) {
	case 'secqaa':
	case 'userstatus':
	case 'seccode':
	case 'updatecache':
	case 'movetospace':
		$dzz->init_cron = false;
		$dzz->init_session = false;
	default:
		break;
}
$dzz->init();

require DZZ_ROOT.'./core/misc/misc_'.$mod.'.php';

?>
