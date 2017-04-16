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
$dzz->reject_robot(); //阻止机器人访问
if(empty($_G['uid']) && !C::t('setting')->fetch('feed_guest_allow')){
	include template('common/header_reload');
	echo "<script type=\"text/javascript\">";
	echo "try{top._login.logging();win.Close();}catch(e){location.href='user.php?mod=logging'}";
	echo "</script>";	
	include template('common/footer_reload');
	exit();
}
$ismobile=helper_browser::ismobile();
include libfile('function/code');


$space=dzzgetspace($_G['uid']);
$space['attachextensions'] = $space['attachextensions']?explode(',',$space['attachextensions']):array();
$space['maxattachsize'] =intval($space['maxattachsize']);
$feedType=empty($_GET['feedType'])?($_G['uid']?($_G['setting']['feed_feedType_default']?$_G['setting']['feed_feedType_default']:'aboutme'):'all'):$_GET['feedType'];
	$gets = array(
		'mod'=>'feed',
		'feedType'=>$feedType,
	);

	$theurl = DZZSCRIPT."?".url_implode($gets);
    $page = empty($_GET['page'])?1:intval($_GET['page']);
	$perpage=10;
	$start=($page-1)*$perpage;
	$orderby=' order by t.lastpost DESC';
	$sql='p.`first`>0';
	switch($feedType){
		case 'aboutme':
			$navtitle=lang('related_me');
			$wherearr=array();
			//@我的
			if($at_tids=C::t('feed_at')->fetch_all_tids_by_uid($_G['uid'])){
			   $wherearr[]="t.tid IN (".dimplode($at_tids).")";
			}
			//我收藏的
			if($at_tids=C::t('feed_collection')->fetch_all_tids_by_uid($_G['uid'])){
			   $wherearr[]="t.tid IN (".dimplode($at_tids).")";
			}
			//我发表的
			$wherearr[]="t.authorid = '{$_G[uid]}'";
			//回复我的
			if($r_tids=C::t('feed_reply')->fetch_all_tids_by_ruid($_G['uid'])){
			   $wherearr[]="t.tid IN (".dimplode($r_tids).")";
			}
			
			$sql.=" and (".implode(' or ',$wherearr).")";
			break;
		case 'atme':
			$navtitle='@'.lang('mine');
			if($at_tids=C::t('feed_at')->fetch_all_tids_by_uid($_G['uid'])){
				
			   $sql.=" and t.tid IN (".dimplode($at_tids).")";
			}else{
				$sql.=" and 0";
			}
			dsetcookie('feed_readtime_atme',$_G['timestamp'],60*60*24*7);
			break;
		case 'collect':
		    $navtitle=lang('my_collection');
			if($at_tids=C::t('feed_collection')->fetch_all_tids_by_uid($_G['uid'])){
			   $sql.=" and t.tid IN (".dimplode($at_tids).")";
			}else{
				$sql.=" and 0";
			}
			break;
		case 'fromme':
			$sql.=" and t.authorid = '{$_G[uid]}'";
			break;
		case 'replyme':
		     $navtitle=lang('reply_my');
			if($r_tids=C::t('feed_reply')->fetch_all_tids_by_ruid($_G['uid'])){
			   $sql.=" and t.tid IN (".dimplode($r_tids).")";
			}else{
				$sql.=" and 0";
			}
			dsetcookie('feed_readtime_replyme',$_G['timestamp'],60*60*24*7);
			break;
		case 'all':
		    $navtitle=lang('all_dynamic');
			$sql.=" and readperm='0'";
			$orderby=' order by t.top DESC,t.lastpost DESC';
			break;
		
	}
	
	
	
	$count=DB::result_first("select COUNT(*) from ".DB::table('feed_thread')." t LEFT JOIN ".DB::table('feed_post')." p on p.tid=t.tid    where $sql ");
	$threads=DB::fetch_all("select t.*,p.message,p.useip,p.pid from ".DB::table('feed_thread')." t LEFT JOIN ".DB::table('feed_post')." p on p.tid=t.tid  where $sql $orderby limit $start,$perpage");
	$list=array();
	foreach($threads as $value){
		$value['iscollect']=DB::result_first("select COUNT(*) from %t where uid=%d and tid=%d",array('feed_collection',$_G['uid'],$value['tid']));
		
		$value['message']=dzzcode($value['message']);
		$value['attachs']=C::t('feed_attach')->fetch_all_by_pid($value['pid']);
		
		$value['dateline']=dgmdate($value['dateline'],'u');
		$list[$value['tid']]=$value;
	}
	$multi=multi($count, $perpage, $page, $theurl,'pull-right');

include template('feed');

?>
