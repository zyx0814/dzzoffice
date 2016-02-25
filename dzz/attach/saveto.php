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
if(empty($_G['uid'])) {
	include template('common/header_reload');
	echo "<script type=\"text/javascript\">";
	echo "try{top._login.logging();}catch(e){}";
	echo "</script>";	
	include template('common/footer_reload');
	topshowmessage('没有登录，无法操作');
}
$pfid=DB::result_first("select fid from %t where flag='document' and uid= %d",array('folder',$_G['uid']));
if($_GET['type']=='link'){
	$link=empty($_GET['link'])?'':trim($_GET['link']);
	if(!$link) topshowmessage('参数错误！');
	//检查网址合法性
	if(!preg_match("/^(http|ftp|https|mms)\:\/\/.{5,300}$/i", ($link))){
		$parseurl=parse_url($link);
		if(!$parseurl['host']) $link=$_G['siteurl'].$link;
		else $link='http://'.preg_replace("/^(http|ftp|https|mms)\:\/\//i",'',$link);
	}
	if(!preg_match("/^(http|ftp|https|mms)\:\/\/.{4,300}$/i",($link))) topshowmessage('网址格式错误！');
	$icoarr=io_dzz::linktourl($link,$pfid);
}elseif($_GET['type']=='dzzdoc'){
	$aid=empty($_GET['aid'])?0:intval($_GET['aid']);
	$attach=C::t('attachment')->fetch($aid);
	if(!$attach){
		topshowmessage(lang('message','attachment_nonexistence'));
	}
	if(!empty($_GET['filename'])) $attach['filename']=trim($_GET['filename']);
	$icoarr=IO::upload_by_content(IO::getFileContent('attach::'.$attach['aid']),$pfid,(trim($attach['filename'],'.dzzdoc').'.dzzdoc'));
	
}else{
	$aid=empty($_GET['aid'])?0:intval($_GET['aid']);
	$attach=C::t('attachment')->fetch($aid);
	if(!$attach){
		topshowmessage(lang('message','attachment_nonexistence'));
	}
	if(!empty($_GET['filename'])) $attach['filename']=trim($_GET['filename']);
	$icoarr=io_dzz::uploadToattachment($attach,$pfid);
}
if(isset($icoarr['error'])) topshowmessage($icoarr['error']);
	 include template('common/header_simple');
		echo "<script type=\"text/javascript\">";
		echo "try{top._ico.createIco(".json_encode($icoarr).");}catch(e){alert('已保存到我的文档!')}";
		echo "try{top.Alert('”".$attach['filename']."“ 成功添加到桌面“我的文档”中！',3,'','','info');}catch(e){}";
		echo "</script>";
	include template('common/footer');
	exit();
?>
