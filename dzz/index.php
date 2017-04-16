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
$uid = $_G['uid'];
$space = dzzgetspace($_G['uid']);
$space['self'] = intval($space['self']);

$thame = getThames();
$thamejson = json_encode($thame['data']);
$space['thame'] = $thame['thame'];
if (isset($_G['setting']['dzz_iconview']) && $_G['setting']['dzz_iconview']) {
	$iconview = $_G['setting']['iconview'];
} else {
	$iconview = C::t('iconview') -> fetch_all();
}
$sitename = addslashes($_G['setting']['sitename']);
include DZZ_ROOT . './core/core_version.php';
include  template('dzz_index');
?>
