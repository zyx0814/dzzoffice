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

/*if(!($_G['adminid'] == 1 && $_GET['formhash'] == formhash()) && $_G['setting']) {
	exit('Access Denied');
}*/

require_once libfile('function/cache');
updatecache();

?>
