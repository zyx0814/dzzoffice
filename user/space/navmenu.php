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
$template = $_GET['template'] ?? '';
$langList = $_G['config']['output']['language_list'];
if ($template == '1') {
    include template('lyear_navmenu', 'lyear');
} else {
    include template('navmenu');
}
exit();