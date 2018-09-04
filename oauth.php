<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
 
define('APPTYPEID', 9);
define('CURSCRIPT', 'dzz');
define('DZZSCRIPT', 'index.php');
require __DIR__.'/core/coreBase.php';
$dzz = C::app();
$dzz->cachelist =array();
$dzz->init();
session_start();
$_GET['state']=$_SESSION['onedrive.oauth.state']['state'];
$bz=$_SESSION['onedrive.oauth.state']['bz'];
IO::authorize($bz);
exit();