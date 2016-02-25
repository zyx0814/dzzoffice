<?php
/* @authorcode  codestrings
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
$ext=strtolower(trim($_GET['ext']));
$extall=C::t('app_open')->fetch_all_ext();
$exts=array();
foreach($extall as $value){
	if($value['ext']=='mp3'){//mp3只使用简易播放器预览
		if(strpos($value['url'],'?mod=sound')!==false) $exts[$value['ext']]=$value;
	}else{
		if(!isset($exts[$value['ext']]) ||$value['isdefault']) $exts[$value['ext']]=$value;
	}
}
exit(json_encode($exts));
?>