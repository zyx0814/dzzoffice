<?php
/*
 * 此应用的通知接口
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
$appid=intval($_GET['appid']);
$uid=intval($_G['uid']);
$data=array();
$data['timeout']=1*60;//一分钟查询一次；
//获取应用的提醒数
if(isset($_GET['t'])) $lasttime=intval($_GET['t']);
else $lasttime=intval(DB::result_first("select lasttime from ".DB::table('app_user')." where uid='{$uid}' and appid='{$appid}'"));
$sql="lastpost>$lasttime ";
	
			$wherearr=array();
			//@我的
			if($at_tids=C::t('feed_at')->fetch_all_tids_by_uid($_G['uid'])){
			   $wherearr[]="tid IN (".dimplode($at_tids).")";
			}
			//我收藏的
			if($at_tids=C::t('feed_collection')->fetch_all_tids_by_uid($_G['uid'])){
			   $wherearr[]="tid IN (".dimplode($at_tids).")";
			}
			//我发表的
			if($r_tids=C::t('feed_reply')->fetch_all_tids_by_ruid($_G['uid'])){
			   $wherearr[]="tid IN (".dimplode($r_tids).")";
			}
			//回复我的
			$wherearr[]="(authorid = '{$_G[uid]}' and replies>0)";
			$sql.=" and (".implode(' or ',$wherearr).")";

$data['sum']=(DB::result_first("SELECT COUNT(*) FROM ".DB::table('feed_thread')." WHERE  $sql"));
//$data['notice']=array();
if(isset($_GET['t'])){
	//获取最新的atme 的数量
	$at_time=isset($_G['cookie']['feed_readtime_atme'])?intval($_G['cookie']['feed_readtime_atme']):TIMESTAMP-60*60*24;
	$data['sum_atme']=C::t('feed_at')->fetch_all_tids_by_uid($_G['uid'],$at_time,1);
	//获取最新的replyme 的数量
	$reply_time=isset($_G['cookie']['feed_readtime_replyme'])?intval($_G['cookie']['feed_readtime_replyme']):TIMESTAMP-60*60*24;
	$data['sum_replyme']=C::t('feed_reply')->fetch_all_tids_by_ruid($_G['uid'],$reply_time,1);
	
	
	$data['tids']=array();
	foreach(DB::fetch_all("select tid from ".DB::table('feed_thread')." where $sql") as $value){
		$data['tids'][]=$value['tid'];
	}
	$data['timestamp']=TIMESTAMP;
	echo json_encode($data);
}else{
	
	echo "noticeCallback(".json_encode($data).")";
}
exit();
?>
