<?php
 /*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
define('APPTYPEID', 0);
define('CURSCRIPT', 'user');
require './core/class/class_core.php';
require libfile('class/user');
require libfile('function/user');
require libfile('function/mail');
require libfile('function/profile');
$dzz = C::app();
$modarray = array('activate', 'clearcookies', 'getpasswd','logging', 'lostpasswd','seccode','secqaa','register','ajax', 'regverify', 'switchstatus','profile','password','avatar','qqlogin','qqcallback');
$mod = !in_array($dzz->var['mod'], $modarray) && (!preg_match('/^\w+$/', $dzz->var['mod']) || !file_exists(DZZ_ROOT.'./member/member_'.$dzz->var['mod'].'.php')) ? 'space' : $dzz->var['mod'];
define('CURMODULE', $mod);
$cachelist=array('usergroup','fields_register');
$dzz->cachelist = $cachelist;
$dzz->init();
include_once libfile('function/cache'); 
updatecache('fields_register');
if(@!file_exists(DZZ_ROOT.'./user/user_'.$mod.'.php')) {
	system_error(lang('undefined_action'));
}

require DZZ_ROOT.'./user/user_'.$mod.'.php';

?>