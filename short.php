<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
require './core/coreBase.php';
$dzz = C::app();
$dzz->init_session = false;
$dzz->init_setting=false;
$dzz->init_user=false;
$dzz->init_misc=false;
$dzz->init();
$sid=$_GET['sid'];
$short=C::t('shorturl')->fetch($sid);
C::t('shorturl')->addview($sid);
@header("Location: ". outputurl($short['url']));
exit();
?>