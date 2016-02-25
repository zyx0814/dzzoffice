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
define('NOROBOT', TRUE);
$path = empty($_GET['icoid'])?trim($_GET['path']):$_GET['icoid'];
$path=dzzdecode($path);
if($path) IO::download($path);
exit();
?>
