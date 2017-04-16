<?php
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
/*
//创建用户所在机构的快捷方式

if($_G['uid']){
	include_once libfile('function/organization');
	foreach($orgids=C::t('organization_user')->fetch_orgids_by_uid($_G['uid']) as $orgid){
		$toporgid=C::t('organization')->getTopOrgid($orgid);
		$topfid=DB::result_first("select fid from %t where orgid=%d",array('organization',$toporgid));
		if(!$org=C::t('organization')->fetch($topfid)) continue;
		$path='fid_'.$topfid;
		
		if(!$cutid=DB::result_first("select cutid from %t where path=%s ",array('source_shortcut',$path))){
			$tdata=array();
			$tdata=C::t('source_shortcut')->getDataByPath($path);
			if($tdata['error']){
				continue;
			}
			$shortcut=array(
							'path'=>$path,
							'data'=>serialize($tdata),
							);
			$cutid=C::t('source_shortcut')->insert($shortcut,1);
		}
		if($cutid<1) continue;
		if($icoid=DB::result_first("select icoid from %t where type='shortcut' and oid=%d and uid=%d",array('icos',$cutid,$_G['uid']))){
			C::t('icos')->update($icoid,array('name'=>$org['orgname'],'isdelete'=>0));
			continue;	
		}
		$pfid=DB::result_first("select fid from ".DB::table('folder')." where uid='{$_G[uid]}' and flag='desktop'");
	
		$icoarr=array(
					'uid'=>$_G['uid'],
					'username'=>$_G['username'],
					'oid'=>$cutid,
					'name'=>$org['orgname'],
					'flag'=>'organization',
					'type'=>'shortcut',
					'dateline'=>$_G['timestamp'],
					'pfid'=>$pfid,
					'gid'=>0,
					'ext'=>'',
					'size'=>0
				);
		
		if($icoarr['icoid']=DB::insert('icos',($icoarr),1,1)){
			addtoconfig($icoarr);
		}
	}
}*/
?> 