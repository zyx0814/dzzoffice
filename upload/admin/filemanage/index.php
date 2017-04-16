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
$typearr=array('image'=>lang('photo'),
			   'document'=>lang('type_attach'),
			   'link'=>lang('type_link'),
			   'video'=>lang('online_video'),
			   'dzzdoc'=>'DZZ'.lang('type_attach'),
			   'attach'=>lang('rest_attachment')
			   );
require libfile('function/organization');
if(submitcheck('delsubmit')){
	foreach($_GET['del'] as $icoid){
		C::t('icos')->delete_by_icoid($icoid,true);
	}
	showmessage('do_success',$_GET['refer']);
}elseif($_GET['do']=='delete'){
	C::t('icos')->delete_by_icoid(intval($_GET['icoid']),true);
	showmessage('do_success',$_GET['refer']);
}else{
		
	$type=trim($_GET['type']);
	$keyword=trim($_GET['keyword']);
	$orgid=intval($_GET['orgid']);
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	$perpage=20;
	$gets = array(
			'mod'=>'filemanage',
			'keyword'=>$keyword,
			'type' => $_GET['type'],
			'size'=>$_GET['size'],
			'dateline'=>$_GET['dateline'],
			'orgid'=>$orgid
		);
	$theurl = BASESCRIPT."?".url_implode($gets);
	$refer=$theurl.'&page='.$page;
	if($_GET['size']=='desc'){
		$order='ORDER BY size DESC';
	}elseif($_GET['size']=='asc'){
		$order='ORDER BY size ASC';
	}
	if($_GET['dateline']=='desc'){
		$order='ORDER BY size DESC';
	}elseif($_GET['dateline']=='asc'){
		$order='ORDER BY dateline ASC';
	}
	
	$start=($page-1)*$perpage;
	$sql=" type!='folder' and type!='app' and type!='shortcut'";
	
	$param=array();
	if($keyword) {
		$sql.=' and (name like %s OR username=%s)';
		$param[]='%'.$keyword.'%';
		$param[]=$keyword;
	}
	if($type){
		$sql.=' and type=%s';
		$param[]=$type;
	}
	if($org=C::t('organization')->fetch($orgid)){
		$fids=array($org['fid']);
		foreach(DB::fetch_all("select fid from %t where pfid=%d",array('folder',$org['fid'])) as $value){
			$fids[]=$value['fid'];
		}
		$sql.=' and  pfid IN(%n)';
		$param[]=$fids;
	}
	if($count=DB::result_first("SELECT COUNT(*) FROM ".DB::table('icos')." WHERE $sql",$param)){
		if(ceil($count/$perpage)<$page){
			$page=ceil($count/$perpage);
			$start=($page-1)*$perpage;
		}
		$data=DB::fetch_all("SELECT uid,icoid,oid,name,type,pfid,dateline,size,ext FROM ".DB::table('icos')." WHERE $sql $order limit $start,$perpage",$param);
		$multi=multi($count, $perpage, $page, $theurl,'pull-right');
	}
	$list=array();
	foreach($data as $value){
		$user=getuserbyuid($value['uid']);
		$value['size']=formatsize($value['size']);
		$value['dateline']=dgmdate($value['dateline'],'u');
		$value['ftype']=getFileTypeName($value['type'],$value['ext']);
		$value['path']=implode('/',array_reverse(getPathByPfid($value['pfid'])));
		$value['username']=$user['username'];
		if(!$sourcedata=C::t('icos')->getsourcedata($value['type'],$value['oid'])){
				 continue;
		}
		$value=array_merge($sourcedata,$value);
		if($value['type']=='image'){
			$value['img']=DZZSCRIPT.'?mod=io&op=thumbnail&width=256&height=256&path='.dzzencode($value['icoid']);
			$value['url']=DZZSCRIPT.'?mod=io&op=thumbnail&width=1440&height=900&original=1&path='.dzzencode($value['icoid']);
		}elseif($value['type']=='attach' || $value['type']=='document'){
			$value['img']=geticonfromext($value['ext'],$value['type']);
			$value['url']=DZZSCRIPT.'?mod=io&op=getStream&path='.dzzencode($value['icoid']);
		}elseif($value['type']=='dzzdoc'){	
			$value['url']=DZZSCRIPT.'?mod=document&icoid='.dzzencode($value['icoid']);
			$value['img']=isset($value['icon'])?$value['icon']:geticonfromext($value['ext'],$value['type']);
		}else{
			$value['img']=isset($value['icon'])?$value['icon']:geticonfromext($value['ext'],$value['type']);
		}
		
		
		$list[]=$value;
	}
	if($org=C::t('organization')->fetch($orgid)){
		$orgpath=getPathByOrgid($org['orgid']);
		$org['depart']=implode('-',array_reverse($orgpath));
	}else{
		$org=array();
		$org['depart']=lang('select_a_organization_or_department');
		$org['orgid']=$orgid;
	}
	include template('main');
}
?>
