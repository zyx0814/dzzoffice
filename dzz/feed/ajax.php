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
include libfile('function/organization');
$do=trim($_GET['do']);
$guests=array('getThread','getNewThreads','getReply','getReplyForm','getReplys');
if(empty($_G['uid']) && !in_array($do,$guests)) {
	include template('common/header_reload');
	echo "<script type=\"text/javascript\">";
	echo "try{top._login.logging();}catch(e){}";
	echo "try{win.Close();}catch(e){}";
	echo "</script>";	
	echo '<a href="user.php?mod=logging&action=login">'.lang('need_login').'</a>';
	include template('common/footer_reload');
	exit();
}


if(submitcheck('feedsubmit')){
	include libfile('function/code');
	$appid=C::t('app_market')->fetch_appid_by_mod('{dzzscript}?mod=feed',1);
	$message=censor($_GET['message']);
	if(empty($message) && empty($_GET['votestatus'])){
		showmessage('please_share_content',DZZSCRIPT.'?mod=feed',array(),array('showdialog'=>true,'timeout'=>1));
	}
	//处理@
	$at_users=array();
	$message=preg_replace_callback("/@\[(.+?):(.+?)\]/i","atreplacement",$message);
	$thread=array(  'author'=>$_G['username'],
					'authorid'=>$_G['uid'],
					'subject'=>'',
					'readperm'=>intval($_GET['readperm']),
					'lastpost'=>TIMESTAMP,
					'lastposter'=>$_G['username'],
					'dateline'=>TIMESTAMP,
					'special'=>0,
					'attachment'=>0,
					'votestatus'=>intval($_GET['votestatus'])
				 );
	
	if(!$tid=C::t('feed_thread')->insert($thread,1)){
		showmessage('internal_server_error',DZZSCRIPT.'?mod=feed',array('message'=>$message));
	}
	$post=array('tid'=>$tid,
				'first'=>1,
				
				'author'=>$_G['username'],
				'authorid'=>$_G['uid'],
				'subject'=>'',
				'message'=>$message,
				'useip'=>$_G['clientip'],
				'dateline'=>TIMESTAMP,
				'attachment'=>0
				);
	if(!$post['pid']=C::t('feed_post')->insert($post,1)){
		C::t('feed_thread')->delete($post['tid']);
		showmessage('internal_server_error',DZZSCRIPT.'?mod=feed',array('message'=>$message));
	}
	//处理@
	if($at_users){
		
		C::t('feed_at')->insert_by_pid($post['pid'],$post['tid'],($at_users));
		//发送通知
		foreach($at_users as $value){
			$notevars=array(
							'from_id'=>$appid,
							'from_idtype'=>'app',
							'url'=>DZZSCRIPT.'?mod=feed&feedType=atme',
							'author'=>$_G['username'],
							'authorid'=>$_G['uid'],
							'message'=>stripsAT($message),
							'dataline'=>dgmdate(TIMESTAMP)
							
						   );
			dzz_notification::notification_add($value, 'feed_at', 'feed_at', $notevars, 0);
		}
	}
	//处理附件
	if($_GET['attach']){
			foreach($_GET['attach']['title'] as $key=>$value){
				$setarr=array('pid'=>$post['pid'],
							  'tid'=>$post['tid'],
							  'dateline'=>TIMESTAMP,
							  'aid'=>intval($_GET['attach']['aid'][$key]),
							  'title'=>trim($value),
							  'type'=>trim($_GET['attach']['type'][$key]),
							  'img'=>trim($_GET['attach']['img'][$key]),
							  'url'=>trim($_GET['attach']['url'][$key]),
							  'ext'=>trim($_GET['attach']['ext'][$key])
							  );
				
				if(C::t('feed_attach')->insert($setarr)){
					if($setarr['aid']>0) C::t('attachment')->addcopy_by_aid($setarr['aid']);
					if($setarr['type']=='link'){
						 $imgarr=$setarr['img']?explode('icon',$setarr['img']):array();
						 if(isset($imgarr[1]) && ($did=DB::result_first("select did from %t where pic=%s",array('icon','icon'.$imgarr[1])))) C::t('icon')->update_copys_by_did($did);
					}
				}
			}
	}
		
	
	$post['attachs']=C::t('feed_attach')->fetch_all_by_pid($post['pid']);
	$post['dateline']=dgmdate($post['dateline'],'u');
	$post['readperm']=intval($_GET['readperm']);
	$post['message']=dzzcode($message);
	if($_G['adminid']==1 || $_G['uid']==$post['authorid']) $post['haveperm']=1;
	if($tid){
		
		//处理投票
		if($thread['votestatus']){
			$voteid=empty($_GET['voteid'])?0:intval($_GET['voteid']);
			
			$vote=$_GET['vote'];
			$vote['type']=$vote['type']?intval($vote['type']):1;
			$vote['endtime']=strtotime($vote['endtime']);
			$vote['subject']=getstr($_GET['vote_subject_'.$vote['type']]);
			$vote['module']='feed';
			$vote['idtype']='feed';
			$vote['id']=$tid;
			$vote['uid']=$_G['uid'];
			
			
			//过滤投票项目
			$item=$_GET['voteitem'];
			$itemnew=array();
			foreach($_GET['voteitemnew']['content'] as $key =>$value){
				if(empty($value) && $vote['type']==1) continue; //文字投票时项目文本为空，略过；
				elseif($vote['type']==2 && !$_GET['voteitemnew']['aid'][$key]) continue;
				$itemnew[]=array('content'=>getstr($value),
								 'aid'=>intval($_GET['voteitemnew']['aid'][$key])
								 );
			}
			
			if($voteid){ //编辑时
				C::t('vote')->update_by_voteid($voteid,$vote,$item,$itemnew);
				
			}else{ //新增加
				$vote['starttime']=TIMESTAMP;
				C::t('vote')->insert_by_voteid($vote,$itemnew);
			}
		}
	}
	$post['votestatus']=$thread['votestatus'];
	showmessage('do_success',DZZSCRIPT.'?mod=feed',array('data'=>rawurlencode(json_encode($post))));
}elseif(submitcheck('replysubmit')){	
	include libfile('function/code');
	$appid=C::t('app_market')->fetch_appid_by_mod('{dzzscript}?mod=feed',1);
	$message=censor($_GET['message']);
	if(empty($message)){
		showmessage('please_share_content',DZZSCRIPT.'?mod=feed',array());
	}
	$at_users=array();
	$message=preg_replace_callback("/@\[(.+?):(.+?)\]/i","atreplacement",$message);
	$tid=intval($_GET['tid']);
	$rpid=intval($_GET['pid']);
	
	$post=array('tid'=>$tid,
				'first'=>0,
				'author'=>$_G['username'],
				'authorid'=>$_G['uid'],
				'subject'=>'',
				'message'=>$message,
				'useip'=>$_G['clientip'],
				'dateline'=>TIMESTAMP,
				'attachment'=>0,
			
				);
	
	if(!$post['pid']=C::t('feed_post')->insert($post,1)){
		showmessage('internal_error',DZZSCRIPT.'?mod=feed',array());
	}
	
	//更新thread表
	//DB::query("update  %t set lastpost=%d , lastposter=%s , replies=replies+1 where tid=%d",array('feed_thread',TIMESTAMP,$_G['username'],$tid));
	C::t('feed_thread')->increase($tid,array('replies'=>1,'lastpost'=>array(TIMESTAMP),'lastposter'=>array($_G['username'])));
	//更新reply事件表
	if($rpid){
		$rpost=C::t('feed_post')->fetch($rpid);
	}else{
		$rpost=DB::fetch_first("select * from %t where `first`>0 and tid=%d",array('feed_post',$tid));
	}
	
	if($rpost['authorid']!=$_G['uid']){
		$replyarr=array('uid'=>$_G['uid'],
						'pid'=>$post['pid'],
						'tid'=>$tid,
						'rpid'=>$rpost['pid'],
						'ruid'=>$rpost['authorid'],
						'dateline'=>$_G['timestamp']
					);
		DB::insert('feed_reply',$replyarr,1,1);
		//发送通知
		$notevars=array(
						'from_id'=>$appid,
						'from_idtype'=>'app',
						'url'=>DZZSCRIPT.'?mod=feed&feedType=replyme',
						'author'=>$_G['username'],
						'authorid'=>$_G['authorid'],
						'message'=>stripsAT($message),
						'dataline'=>dgmdate(TIMESTAMP)
						
						);
		dzz_notification::notification_add($rpost['authorid'], 'feed_reply', 'feed_reply', $notevars, 0);
		
	}
	//处理@
	if($at_users){
		C::t('feed_at')->insert_by_pid($post['pid'],$post['tid'],($at_users));
		//发送通知
		foreach($at_users as $value){
			$notevars=array(
							'from_id'=>$appid,
							'from_idtype'=>'app',
							'url'=>DZZSCRIPT.'?mod=feed',
							'author'=>$_G['username'],
							'authorid'=>$_G['uid'],
							'message'=>stripsAT($message),
							'dataline'=>dgmdate(TIMESTAMP)
							
							);
			dzz_notification::notification_add($value, 'feed_at', 'feed_at', $notevars, 0);
		}
	}
	//处理附件
	if($_GET['attach']){
			foreach($_GET['attach']['title'] as $key=>$value){
				$setarr=array('pid'=>$post['pid'],
							  'tid'=>$post['tid'],
							  'dateline'=>TIMESTAMP,
							  'aid'=>intval($_GET['attach']['aid'][$key]),
							  'title'=>trim($value),
							  'type'=>trim($_GET['attach']['type'][$key]),
							  'img'=>trim($_GET['attach']['img'][$key]),
							  'url'=>trim($_GET['attach']['url'][$key]),
							  'ext'=>trim($_GET['attach']['ext'][$key])
							  );
				if(C::t('feed_attach')->insert($setarr)){
					if($setarr['aid']>0) C::t('attachment')->addcopy_by_aid($setarr['aid']);
					if($setarr['type']=='link'){
						$imgarr=$setarr['img']?explode('icon',$setarr['img']):array();
						 if(isset($imgarr[1]) && ($did=DB::result_first("select did from %t where pic=%s",array('icon','icon'.$imgarr[1])))) C::t('icon')->update_copys_by_did($did);
					}
				}
			}
	}
	$post['attachs']=C::t('feed_attach')->fetch_all_by_pid($post['pid']);
	$post['dateline']=dgmdate($post['dateline'],'u');
	$post['message']=dzzcode($message);
	if($rpid) $post['rpost']=$rpost;
	if($_G['adminid']==1 || $_G['uid']==$post['authorid']) $post['haveperm']=1;
	
	showmessage('do_success',DZZSCRIPT.'?mod=feed',array('data'=>rawurlencode(json_encode($post))));

}elseif($do=='getThread'){
	include libfile('function/code');
	$tid=intval($_GET['tid']);
	$value=DB::fetch_first("select t.*,p.message,p.useip,p.pid from ".DB::table('feed_thread')." t LEFT JOIN ".DB::table('feed_post')." p on p.tid=t.tid and p.`first`>0 where tid='{$tid}'");
	$value['message']=dzzcode($value['message']);
	$value['attachs']=C::t('feed_attach')->fetch_all_by_pid($value['pid']);
	$value['dateline']=dgmdate($value['dateline'],'u');

}elseif($do=='getNewThreads'){
	include libfile('function/code');
	$orderby=' order by t.lastpost DESC';
	$lasttime=intval($_GET['t']);
	$sql="p.`first`>0 and t.lastpost>$lasttime";
	
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
			$wherearr[]="(t.authorid = '{$_G[uid]}' and t.replies>0)";
			$sql.=" and (".implode(' or ',$wherearr).")";

	$threads=DB::fetch_all("select t.*,p.message,p.useip,p.pid from ".DB::table('feed_thread')." t LEFT JOIN ".DB::table('feed_post')." p on p.tid=t.tid  where $sql $orderby");
	$list=array();
	foreach($threads as $value){
		$value['iscollect']=DB::result_first("select COUNT(*) from %t where uid=%d and tid=%d",array('feed_collection',$_G['uid'],$value['tid']));
		$value['message']=dzzcode($value['message']);
		if($_G['adminid']==1 || $_G['uid']==$value['authorid']) $value['haveperm']=1;
		$value['attachs']=C::t('feed_attach')->fetch_all_by_pid($value['pid']);
		
		$value['dateline']=dgmdate($value['dateline'],'u');
		$list[]=$value;
	}
	
	//echo json_encode(array('timestamp'=>$_G['timestamp'],'list'=>$list));
	//exit();
	
}elseif($do=='getReply'){
	
}elseif($do=='getReplys'){
	include libfile('function/code');
	$tid=intval($_GET['tid']);
	$limit=intval($_GET['limit']);
	if($limit) $limit=" limit $limit";
	else $limit='';
	$orderby=' order by dateline DESC';
	$sql="tid='{$tid}' and `first`<1";
	$thread=C::t('feed_thread')->fetch($tid);
	$count=DB::result_first("select COUNT(*) from ".DB::table('feed_post')." where $sql ");
	$replys=DB::fetch_all("select * from ".DB::table('feed_post')." where  $sql $orderby $limit");
	
	$list=array();
	foreach($replys as $value){
		$value['readperm']=$thread['readperm'];
		$value['message']=dzzcode($value['message']);
		if($value['rpid']) $value['rpost']=C::t('feed_post')->fetch($value['rpid']);
		if($_G['adminid']==1 || $_G['uid']==$value['authorid']) $value['haveperm']=1;
		$value['attachs']=C::t('feed_attach')->fetch_all_by_pid($value['pid']);
		
		$value['dateline']=dgmdate($value['dateline'],'u');
		$list[$value['pid']]=$value;
	}
	$list=array_reverse($list);
}elseif($do=='delete'){
	$pid=intval($_GET['pid']);
	
	$post=C::t('feed_post')->fetch($pid);
	if($_G['adminid']!=1 && $_G['uid']==$value['authorid']) exit(json_encode(array('msg'=>lang('privilege'))));
	if($post['first']>0){
		C::t('feed_thread')->delete_by_tid($post['tid']);
	}else{
		C::t('feed_post')->delete_by_pid($post['pid']);
		 //更新回复数
		 C::t('feed_thread')->increase($post['tid'],array('replies'=>-1));
	}
	
	exit(json_encode(array('msg'=>'success')));
}elseif($do=='attachdel'){
	$qid=intval($_GET['qid']);
	if(!$attach=C::t('feed_attach')->fetch($qid)){
		exit(json_encode(array('error'=>lang('attachment_not_exist_deleted'))));
	}
	if($_G['adminid']!=1){
		$thread=C::t('feed_thread')->fetch($attach['tid']);
		if($_G['uid']!=$thread['authorid']) exit(json_encode(array('error'=>lang('privilege'))));
	}
	if(C::t('feed_attach')->delete_by_qid($qid)){
		exit(json_encode(array('msg'=>'success')));
	}else{
		exit(json_encode(array('error'=>lang('delete_error1'))));
	}
	
}elseif($do=='collect'){
	$tid=intval($_GET['tid']);
	if($tid){
		if(DB::result_first("select COUNT(*) from %t where uid=%d and tid=%d",array('feed_collection',$_G['uid'],$tid))){
			if(C::t('feed_collection')->delete_by_tid_uid($tid,$_G['uid'])) $msg='success';
		}else{
			if(C::t('feed_collection')->insert_by_tid_uid($tid,$_G['uid'])) $msg='success';
		}
	}
	if(!$msg) $msg='error';
	echo json_encode(array('msg'=>$msg));
	exit();
}elseif($do=='top'){
	$tid=intval($_GET['tid']);
	if($thread=C::t('feed_thread')->fetch($tid)){
		if(C::t('feed_thread')->update($thread['tid'],array('top'=>!$thread['top']))) $msg='success';
	}
	if(!$msg) $msg='error';
	echo json_encode(array('msg'=>$msg));
	exit();
	
}elseif($do=='upload'){
	include libfile('class/uploadhandler');
	$space=dzzgetspace($_G['uid']);
	$allowedExtensions = $space['attachextensions']?explode(',',$space['attachextensions']):array();
	
	// max file size in bytes
	$sizeLimit =intval($space['maxattachsize']);
	
	$options=array('accept_file_types'=>$allowedExtensions?("/(\.|\/)(".implode('|',$allowedExtensions).")$/i"):"/.+$/i",
					'max_file_size'=>$sizeLimit?$sizeLimit:null,
					'upload_dir' =>$_G['setting']['attachdir'].'cache/',
					'upload_url' => $_G['setting']['attachurl'].'cache/',
					);
	$upload_handler = new uploadhandler($options);

	exit();
}elseif($do=='uploadfromdesktop'){
	$icoid=intval($_GET['icoid']);
	
}elseif($do=='getReplyForm'){
	$space=dzzgetspace($_G['uid']);
	$space['attachextensions'] = $space['attachextensions']?explode(',',$space['attachextensions']):array();
	$space['maxattachsize'] =intval($space['maxattachsize']);
	$tid=intval($_GET['tid']);
	
}elseif($do=='edit'){ 
	include_once libfile('function/code');
	$pid=intval($_GET['pid']);
  if($data=C::t('feed_post')->fetch($pid)){
	  if(!$_G['adminid']==1 && $_G['uid']!=$data['authorid']) showmessage('privilege');
  }else{
	 showmessage('message_does_exist_deleted'); 
  }
  if(!submitcheck('editsubmit')){
	 $data['message']=dhtmlspecialchars($data['message']);
	$data['attachs']=C::t('feed_attach')->fetch_all_by_pid($pid);
	$data['votestatus']=DB::result_first("select votestatus from %t where tid=%d",array('feed_thread',$data['tid']));
	$space=dzzgetspace($_G['uid']);
	$space['attachextensions'] = $space['attachextensions']?explode(',',$space['attachextensions']):array();
	$space['maxattachsize'] =intval($space['maxattachsize']);
  }else{
	$message=censor($_GET['message']);
	if(empty($message) && empty($_GET['votestatus'])){
		showmessage('please_share_content');
	}
	 $post=array('message'=>$message);
	 C::t('feed_post')->update($pid,$post);
	 //处理附件
	C::t('feed_attach')->update_by_pid($pid,$data['tid'],$_GET['attach']);
	if($_GET['tid']){
		$voteid=empty($_GET['voteid'])?0:intval($_GET['voteid']);
		$votestatus=intval($_GET['votestatus']);
		//处理投票
		if($votestatus){
			$vote=$_GET['vote'];
			$vote['type']=$vote['type']?intval($vote['type']):1;
			$vote['endtime']=strtotime($vote['endtime']);
			$vote['subject']=getstr($_GET['vote_subject_'.$vote['type']]);
			$vote['module']='feed';
			$vote['idtype']='feed';
			$vote['id']=intval($_GET['tid']);
			$vote['uid']=$_G['uid'];
			//过滤投票项目
			$item=$_GET['voteitem'];
			$itemnew=array();
			foreach($_GET['voteitemnew']['content'] as $key =>$value){
				if(empty($value) && $vote['type']==1) continue; //文字投票时项目文本为空，略过；
				elseif($vote['type']==2 && !$_GET['voteitemnew']['aid'][$key]) continue;
				$itemnew[]=array('content'=>getstr($value),
								 'aid'=>intval($_GET['voteitemnew']['aid'][$key])
								 );
			}
			if($voteid){ //编辑时
				C::t('vote')->update_by_voteid($voteid,$vote,$item,$itemnew);
				
			}else{ //新增加
				$vote['starttime']=TIMESTAMP;
				C::t('vote')->insert_by_voteid($vote,$itemnew);
			}
		}elseif($voteid){
			C::t('vote')->delete_by_voteid($voteid);
		}
		C::t('feed_thread')->update(intval($_GET['tid']),array('votestatus'=>$votestatus));
	}
	
	showmessage('do_success',DZZSCRIPT.'?mod=feed',array('data'=>rawurlencode(json_encode($data))));
  }
 }elseif($do=='getfeedbypid'){
	$pid=intval($_GET['pid']);
	include_once libfile('function/code');
	
	if($value=C::t('feed_post')->fetch($pid)){
		$thread=C::t('feed_thread')->fetch($value['tid']);
		$value=array_merge($thread,$value);
		$value['message']=dzzcode($value['message']);
		$value['dateline']=dgmdate($value['dateline'],'u');
		$value['attachs']=C::t('feed_attach')->fetch_all_by_pid($value['pid']);
	}
}elseif($do=='getAtData'){
  include_once dzz_libfile('class/pinyin');
 	
  $py = new PinYin();
  $data=array();
  $term=trim($_GET['term']);
  $filter=intval($_GET['filter']);//0:机构和用户；1：仅用户；2：仅机构
  if($filter==1 || !$filter){
	  $param_user=array('user','user_status');
	  $sql_user="where status<1";
	  if($term){
		   $sql_user.=" and username LIKE %s";
		   $param_user[]='%'.$term.'%';
	  }
	  foreach(DB::fetch_all("select u.uid,u.username  from %t u LEFT JOIN %t s on u.uid=s.uid  $sql_user order by s.lastactivity DESC limit 10",$param_user) as $value){
		 if($value['uid']!=$_G['uid']){
			 $data[]=array('name'=>$value['username'],
						   'searchkey'=> $py->getAllPY($value['username']).$value['username'],
						   'id'=>'u'.$value['uid'],
						   'icon'=>'avatar.php?uid='.$value['uid'].'&size=small',
						   'title'=>$value['username'].':'.'u'.$value['uid']
						);
			
		 }
	  }
	 
  }
  
   if($filter==2 || !$filter){
	    $param_org=array('organization');
		$sql_org="where 1";
		if($term){
			   $sql_org.=" and orgname LIKE %s";
			   $param_org[]='%'.$term.'%';
		}
	    $_G['setting']['feed_at_range']=unserialize($_G['setting']['feed_at_range']);
 		$range=$_G['setting']['feed_at_range'][$_G['groupid']];
	  	$orgids=array();
		 switch($range){
			  case 1: //本部门
				foreach(C::t('organization_user')->fetch_orgids_by_uid($_G['uid']) as $orgid){
					$orgids=array_merge($orgids,getOrgidTree($orgid));
				}
				if($orgids){
					$sql_org.=" and orgid IN(%n)";
					$param_org[]=$orgids;
					foreach(DB::fetch_all("select orgname,orgid,forgid from %t $sql_org limit 10",$param_org) as $org){	
						$porgids=array_reverse(getUpOrgidTree($org['orgid']));
						$titles=array();
						foreach($porgids as $porgid){
							if($porg=C::t('organization')->fetch($porgid)) $titles[]=$porg['orgname'];
						}
						 $data[]=array('name'=>$org['orgname'],
									   'title'=>implode('-',$titles),
									   'searchkey'=> $py->getAllPY($org['orgname']).$org['orgname'],
									   'id'=>'g'.$org['orgid'],
									   'icon'=>$org['forgid']?'dzz/system/images/department.png':'dzz/system/images/organization.png'
									);
						
					}
				}
				break;
			 case 2: //本机构
				  foreach(C::t('organization_user')->fetch_orgids_by_uid($_G['uid']) as $orgid){
						$orgids=array_merge($orgids,getOrgidTree($orgid));
						$orgids=array_merge($orgids,getUpOrgidTree($orgid));
					}
					if($orgids){
						$sql_org.=" and orgid IN(%n)";
						$param_org[]=$orgids;
						foreach(DB::fetch_all("select orgname,orgid,forgid from %t $sql_org limit 10",$param_org) as $org){	
							$porgids=array_reverse(getUpOrgidTree($org['orgid']));
							$titles=array();
							foreach($porgids as $porgid){
								if($porg=C::t('organization')->fetch($porgid)) $titles[]=$porg['orgname'];
							}
							$data[]=array('name'=>$org['orgname'],
										   'title'=>implode('-',$titles),
										   'searchkey'=> $py->getAllPY($org['orgname']).$org['orgname'],
										   'id'=>'g'.$org['orgid'],
										   'icon'=>$org['forgid']?'dzz/system/images/department.png':'dzz/system/images/organization.png'
										);
						}
					}
				break;
			 case 3: //全部
					foreach(DB::fetch_all("select orgname,orgid,forgid from %t $sql_org limit 10",$param_org) as $org){
						$porgids=array_reverse(getUpOrgidTree($org['orgid']));
						$titles=array();
						foreach($porgids as $porgid){
							if($porg=C::t('organization')->fetch($porgid)) $titles[]=$porg['orgname'];
						}
						$data[]=array( 'name'=>$org['orgname'],
									   'title'=>implode('-',$titles),
									   'searchkey'=> $py->getAllPY($org['orgname']).$org['orgname'],
									   'id'=>'g'.$org['orgid'],
									   'icon'=>$org['forgid']?'dzz/system/images/department.png':'dzz/system/images/organization.png'
									);
						
					}
					
				break;
		 }
   }
  exit(json_encode($data));
}
function atreplacement($matches){
	global $at_users;
	include_once libfile('function/code');
	include_once libfile('function/organization');
	if(strpos($matches[2],'g')!==false){
		$gid=str_replace('g','',$matches[2]);
		if(($org=C::t('organization')->fetch($gid)) && checkFeedAtPerm($gid)){//判定用户有没有权限@此部门
			$uids=getUserByOrgid($gid,true,array(),true);
			foreach($uids as $uid){
				if($uid!=$_G['uid']) $at_users[]=$uid;
			}
			return '[org='.$gid.'] @'.$org['orgname'].'[/org]';
		}else{
			return '';
		}
	}else {
		$uid=str_replace('u','',$matches[2]);
		if(($user=C::t('user')->fetch($uid)) && $user['uid']!=$_G['uid']){
			$at_users[]=$user['uid'];
			return '[uid='.$user['uid'].']@'.$user['username'].'[/uid]';
		}else{
			return $matches[0];
		}
	}
}
function stripsAT($message,$all=0){ //$all>0 时去除包裹的内容
	if($all) {
		$message= preg_replace("/\[uid=(\d+)\](.+?)\[\/uid\]/i", '', $message);
		$message= preg_replace("/\[org=(\d+)\](.+?)\[\/org\]/i", '', $message);
	}else {
		$message= preg_replace("/\[uid=(\d+)\]/i", '', $message);
		$message= preg_replace("/\[\/uid\]/i", '', $message);
		$message= preg_replace("/\[org=(\d+)\]/i", '', $message);
		$message= preg_replace("/\[\/org\]/i", '', $message);
	}
	return $message;
}
function checkFeedAtPerm($gid){
	global $_G;
	include_once libfile('function/organization');
	$rangearr=unserialize($_G['setting']['feed_at_range']);
	$range=$rangearr[$_G['groupid']];	
	if($range==3){//所有机构
		return true;
	}elseif($range==2){//机构
		$orgids=C::t('organization_user')->fetch_orgids_by_uid($_G['uid']);
		if(in_array($gid,$orgids)) return true;
		foreach($orgids as $orgid){
			$toporgids=getUpOrgidTree($orgid);
			if(in_array($gid,$toporgids)) return true;
		}
		foreach($orgids as $orgid){
			$suborgids=getOrgidTree($orgid);
			if(in_array($gid,$suborgids)) return true;
		}
		return false;
		
	}elseif($range==1){//部门
		$orgids=C::t('organization_user')->fetch_orgids_by_uid($_G['uid']);
		if(in_array($gid,$orgids)) return true;
		foreach($orgids as $orgid){
			$suborgids=getOrgidTree($orgid);
			if(in_array($gid,$suborgids)) return true;
		}
		return false;
	}
	return false;
}
include template('ajax');
?>
