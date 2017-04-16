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

$qid=intval($_GET['qid']);
$attach=C::t('comment_attach')->fetch_by_qid($qid);
if(!$attach){
	topshowmessage(lang('attachment_nonexistence'));
}
$attach['filename']=$attach['title'];
if($attach['aid']){
	$shareurl=$_G['siteurl'].'share.php?a=view&s='.dzzencode('attach::'.$attach['aid']).'&n='.rawurlencode(trim($attach['filename'],'.'.$attach['filetype'])).'.'.$attach['filetype'];
}else{
	$shareurl=$attach['url'];
}
$icoarr=array( 'icoid'=>'preview_'.$qid,
		'uid'=>$_G['uid'],
		'username'=>$_G['username'],
		'oid'=>0,
		'name'=>$attach['title'],
		'img'=>$attach['img'],
		'dateline'=>$_G['timestamp'],
		'pfid'=>0,
		'type'=>$attach['type'],
		'flag'=>'',
		'gid'=>0,
		'path'=>$attach['aid']?'attach::'.$attach['aid']:'',
		'dpath'=>$attach['aid']?dzzencode('attach::'.$attach['aid']):dzzencode('preview_'.$qid),
		'url'=>$attach['aid']?getAttachUrl($attach):$attach['url'],
		'bz'=>'feed_preview',
		'ext'=>($attach['type']=='video')?'swf':($attach['filetype']?$attach['filetype']:$attach['ext']),
		'size'=>$attach['filesize']
);

 include template('common/header_common');
	echo "<script type=\"text/javascript\">";
	//echo "top._config.sourcedata.icos['feed_attach_".$attach['qid']."']=".json_encode($icoarr).";";
	echo "try{top._api.Open(".json_encode($icoarr).");}catch(e){location.href='".$shareurl."';}";
	echo "</script>";
 include template('common/footer');
exit();
?>
