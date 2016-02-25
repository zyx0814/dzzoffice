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

if($_GET['do']=='reversion'){ //恢复到以前版本
	$did=intval($_GET['did']);
	$version=intval($_GET['v']);
	if(C::t('document_reversion')->reversion($did,$version,$_G['uid'],$_G['username'])){
		showmessage('do_success',DZZSCRIPT.'?mod=document&did='.$did);
	}else{
		showmessage('使用版本'.$version.'失败',DZZSCRIPT.'?mod=document&did='.$did);
	}
}elseif($_GET['do']=='delete'){
	$did=intval($_GET['did']);
	$refer=dreferer();
	if(strpos($refer,'did='.$did)){ //在内页删除时，根据$doc[area]来决定返回的地址；
		$refer=$refer;
	}
	if(!$doc=C::t('document')->fetch($did)){
		showmessage('文档不存在',$refer);
	}
   if(C::t('document')->delete_by_did($did)){
	   showmessage('文档删除成功',$refer);
   }
}
?>
