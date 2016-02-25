<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}
define('DZZSCRIPT','index.php');
$sharestatus=array('-4'=>'已屏蔽','-3'=>'文件已删除','-2'=>'次数用尽','-1'=>'已过期','0'=>'正常');
$typearr=array('folder'=>'目录','image'=>'图片','document'=>'文档','dzzdoc'=>'Dzz文档','video'=>'视频','attach'=>'附件','link'=>'网址','url'=>'其他');
$type=trim($_GET['type']);
$keyword=trim($_GET['keyword']);
$username=trim($_GET['username']);
$asc=isset($_GET['asc'])?intval($_GET['asc']):1;
$uid=intval($_GET['uid']);
$order=in_array($_GET['order'],array('title','dateline','type','size','count'))?trim($_GET['order']):'dateline';
$page = empty($_GET['page'])?1:intval($_GET['page']);
$perpage=20;
$start=($page-1)*$perpage;
$gets = array(
		'mod'=>'share',
		'type'=>$type,
		'keyword'=>$keyword,
		'order'=>$order,
		'asc'=>$asc,
		'uid'=>$uid,
		'username'=>$username
	);
$theurl = BASESCRIPT."?".url_implode($gets);
$orderby=" order by $order ".($asc?'DESC':'');

$sql="1";
$param=array('share');
if($type){
	$sql.=" and type=%s";
	$param[]=$type;
}
if($keyword){
	$sql.=" and title LIKE %s";
	$param[]='%'.$keyword.'%';
}
if($username){
	$sql.=" and username=%s";
	$param[]=$username;
}
if($uid){
	$sql.=" and uid=%d";
	$param[]=$uid;
}
$list=array();
if($count=DB::result_first("SELECT COUNT(*) FROM %t WHERE $sql",$param)){
	foreach(DB::fetch_all("SELECT * FROM %t WHERE $sql $orderby limit $start,$perpage", $param) as $value){
		if($value['dateline']) $value['fdateline']=dgmdate($value['dateline']);
		if($value['password']) $value['password']=dzzdecode($value['password']);
		if($value['endtime']) $value['fendtime']=dgmdate($value['endtime'],'Y-m-d');
		$value['fsize']=formatsize($value['size']);
		$value['ftype']=getFileTypeName($value['type'],$value['ext']);
		if($value['type']=='folder') $value['img']='dzz/images/extimg/folder.png';
		if($value['img']) $value['img']=str_replace('dzz/images/extimg/','dzz/images/extimg_small/',$value['img']);
		if($value['type']=='image' && $value['status']==-3) $value['img']='';
		$value['fstatus']=$sharestatus[$value['status']];
		if(is_file($_G['setting']['attachdir'].'./qrcode/'.$value['sid'][0].'/'.$value['sid'].'.png')) $value['qrcode']=$_G['setting']['attachurl'].'./qrcode/'.$value['sid'][0].'/'.$value['sid'].'.png';
		$value['shareurl']=$_G['siteurl'].'s.php?sid='.$value['sid'];
		$list[$value['sid']]=$value;
	}
	$multi=multi($count, $perpage, $page, $theurl,'pull-right');
}
include template('share');

?>
