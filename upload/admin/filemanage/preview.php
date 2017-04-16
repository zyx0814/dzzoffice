<?php
/*
 * 下载
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
define('DZZSCRIPT','index.php');
$icoid=intval($_GET['icoid']);
$icoarr=C::t('icos')->fetch_by_icoid($icoid);
 include template('common/header_common');
	echo "<script type=\"text/javascript\">";
	//echo "top._config.sourcedata.icos['feed_attach_".$attach['qid']."']=".json_encode($icoarr).";";
	echo "try{top._api.Open(".json_encode($icoarr).");}catch(e){alert(".lang('filemanage_desktop').");}";
	echo "</script>";
include template('common/footer');
exit();
?>
