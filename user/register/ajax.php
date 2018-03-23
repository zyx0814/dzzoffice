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

if($_GET['do']=='bbrule'){
	$setting = $_G['setting'];
	$bbrules = $_G['setting']['bbrules'];
	$bbrulestxt = $_G['setting']['bbrulestxt'];
	$bbrulestxt = nl2br("\n$bbrulestxt\n\n");
	include template('register_bbrule');
	exit();
}