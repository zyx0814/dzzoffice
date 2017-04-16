<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ')) {
	exit('Access Denied');
}
if (!$path = dzzdecode($_GET['path'])) {
	exit(lang('parameter_error'));
}
$stream = $_G['siteurl'] . DZZSCRIPT . '?mod=io&op=getStream&path=' . rawurldecode($_GET['path']);
include template('play');
?>
