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
$navtitle='分享管理';
$sharestatus=array('-4'=>'已屏蔽','-3'=>'文件已删除','-2'=>'次数用尽','-1'=>'已过期','0'=>'正常');
$typearr=array('folder'=>'目录','image'=>'图片','app'=>'应用','link'=>'网址','video'=>'视频','attach'=>'文件','document'=>'文档','dzzdoc'=>'Dzz文档','url'=>'其他');
$keyword=trim($_GET['keyword']);
$page = empty($_GET['page'])?1:intval($_GET['page']);
$perpage=20;
$start=($page-1)*$perpage;
$gets = array(
		'mod'=>'share',
		'keyword'=>$keyword
	);
$sql="uid=%d";
$param=array('share',$_G['uid']);
if($keyword){
	$sql.=" and title LIKE %s";
	$param[]='%'.$keyword.'%';
}
$orderby="order by dateline DESC";
$theurl = BASESCRIPT."?".url_implode($gets);
$list=array();
if($count=DB::result_first("SELECT COUNT(*) FROM %t WHERE $sql",$param)){
	foreach(DB::fetch_all("SELECT * FROM %t WHERE $sql $orderby limit $start,$perpage",$param) as $value){
		if($value['dateline']) $value['fdateline']=dgmdate($value['dateline']);
		if($value['password']) $value['password']=dzzdecode($value['password']);
		if($value['endtime']) $value['fendtime']=dgmdate($value['endtime'],'Y-m-d');
		$value['fsize']=formatsize($value['size']);
		$value['ftype']=getFileTypeName($value['type'],$value['ext']);
		if($value['type']=='folder') $value['img']='dzz/images/extimg/folder.png';
		if($value['img']) $value['img']=str_replace('dzz/images/extimg/','dzz/images/extimg_small/',$value['img']);
		if($value['type']=='image' && $value['status']==-3) $value['img']='';
		
		$value['fstatus']=$sharestatus[$value['status']];
		$value['shareurl']=$_G['siteurl'].'s.php?sid='.$value['sid'];
		if(in_array($value['type'],array('image','attach','document'))) $value['downurl']=$_G['siteurl'].'s.php?sid='.$value['sid'].'&a=down';
		if(is_file($_G['setting']['attachdir'].'./qrcode/'.$value['sid'][0].'/'.$value['sid'].'.png')) $value['qrcode']=$_G['setting']['attachurl'].'./qrcode/'.$value['sid'][0].'/'.$value['sid'].'.png';
		$list[$value['sid']]=$value;
	}
	//$multi=multi($count, $perpage, $page, $theurl,'pull-right');
	if($count>$perpage*$page) $nextpage=$page+1;
	else $nextpage=0;
}
if($_GET['inajax']) include template('share_item');
else include template('share');

?>
