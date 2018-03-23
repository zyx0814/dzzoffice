<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}
$navtitle = lang('enterprise_little_helper_set');
$op = $_GET['op']?$_GET['op']:' ';
if (empty($_G['setting']['token_0']))$_G['setting']['token_0'] = random(8);
if (empty($_G['setting']['encodingaeskey_0']))$_G['setting']['encodingaeskey_0'] = random(43);
$host = $_SERVER['HTTP_HOST'];
$callback = $_G['siteurl'] . 'index.php?mod=system&op=wxreply';

include template('assistant');
?>
