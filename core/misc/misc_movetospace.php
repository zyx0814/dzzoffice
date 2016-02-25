<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
ignore_user_abort(true);
@set_time_limit(0);
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
require_once DZZ_ROOT.'./dzz/function/dzz_core.php';
$remoteid=intval($_GET['remoteid']);
$aid=intval($_GET['aid']);
if($attach=C::t('attachment')->fetch($aid)){
	$re=io_remote::Migrate($attach,$remoteid);
	if($re['error']) exit($re['error']);
}
exit('success');
?>
