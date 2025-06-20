<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
require __DIR__ . '/core/coreBase.php';
define('CURSCRIPT', 'dzz');
define('APPTYPEID', 0);
$dzz = C::app();
$dzz->init_session = false;
$dzz->init_setting = false;
$dzz->init_user = false;
$dzz->init_misc = false;
$dzz->init();
$sid = $_GET['sid'] ?? '';
if (!$sid) {
    exit('Access Denied');
}
$short = C::t('shorturl')->fetch($sid);
if (!$short) {
    @header('HTTP/1.1 404 Not Found');
    @header('Status: 404 Not Found');
    exit('Access Denied');
}
C::t('shorturl')->addview($sid);
@header("Location: " . outputurl($short['url']));
exit();
?>