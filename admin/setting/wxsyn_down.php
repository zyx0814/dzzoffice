<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}
require_once libfile('function/user', '', 'user');
include_once libfile('function/organization');
$navtitle = lang('data_synchronization');
$do = $_GET['do'];
$op = $_GET['op']?$_GET['op']:' ';
if (submitcheck('synsubmit')) {

}
elseif ($do == 'qiwechat_syn_org') {
	$i = intval($_GET['i']); 
	$wx = new qyWechat( array('appid' => $_G['setting']['CorpID'], 'appsecret' => $_G['setting']['CorpSecret'], 'agentid' => 0));
	$wd = $wdp = array();
	
	//首次查询微信端所有用户，第二次读取缓存
	if( $i!=0 ){
		$wxdepart = getglobal('cache/wxsyn_donw_departmentlist'); 
		if(!$wxdepart){
			$wxdepart = $wx -> getDepartment(); 
			savecache("wxsyn_donw_departmentlist",$wxdepart);
		}
	}else{
		$wxdepart = $wx -> getDepartment();
		savecache("wxsyn_donw_departmentlist",$wxdepart);
	} 
	//$wxdepart = $wx -> getDepartment();
	if ($wxdepart) {
		//递归整理数组
		$wd = $wxdepart['department'];
		$newwd=array();
		//print_r($wd);
		$idorders=array();
		foreach ($wd as $key=> $value) {
			if( $value["parentid"]==0 ){//微信顶级排除不计入同步
				continue;
			}
			$wdarr[$value["id"]]=$value;
			if( $value["parentid"]==1){//顶级
				$newwd[$key]=$value;
				$newwd[$key]["children"]=getchildren($wd,$value,$idorders);
			} 
		} 
	} else {
		exit(json_encode(array('error' => lang('setting_wxsyn_weixin') . $wx -> errCode . ':' . $wx -> errMsg . '</p>')));
	}
	//按照 $idorders 无需判断父节点是否已经本地存在
	//print_r($idorders);
	//print_r($wdarr);exit;
	//if ($org = DB::fetch_first("select * from %t where type=0 and orgid>%d  order by orgid ", array('organization', $i))) {//type=0排除群组
	if( isset($wdarr[$idorders[$i]]) && $wdarr[$idorders[$i]] ){
		$wdorg=$wdarr[$idorders[$i]]; 
		if( $wdorg["parentid"]==1 ){
			$forgid = 0;//父级部门id
		}else{
			$forgid = DB::result_first("select orgid from %t where type=0 and worgid=%d ", array('organization', $wdorg['parentid']));//父级部门id
		}
		 
		if($porgid=DB::result_first("select orgid from %t where type=0 and forgid=%d and orgname=%s",array('organization',$forgid,$wdorg['name']))){//查询本地是否已存在
			//更新 
			$setarr=array(
				'forgid'=>$forgid,
				'orgname'=>$wdorg["name"],
				'worgid'=>$wdorg['id'], 
				'dateline'=>TIMESTAMP,
			);
			if($porgid=C::t('organization')->update_by_orgid($porgid,$setarr,0)){
				exit(json_encode(array('msg' => 'continue', 'start' => $i+1, 'message' => $wdorg['name'] . ' <span class="success">'.lang('update_success').'</span>'))); 
			}else{
				exit(json_encode(array('msg' => 'continue', 'start' => $i+1, 'message' => $wdorg['name'] . lang('setting_wxsyn_organization'))));
			}
		}else{
			//新增
			$setarr=array(
				'forgid'=>$forgid,
				'orgname'=>$wdorg["name"],
				'worgid'=>$wdorg['id'],
				'fid'=>0,
				'disp'=>100,
				'indesk'=>0,
				'dateline'=>TIMESTAMP,
			);
			if($porgid=C::t('organization')->insert_by_orgid($setarr,0)){
				exit(json_encode(array('msg' => 'continue', 'start' => $i+1, 'message' => $wdorg['name'] . ' <span class="success">'.lang('creation_success').'</span>'))); 
			}else{
				exit(json_encode(array('msg' => 'continue', 'start' => $i+1, 'message' => $wdorg['name'] . lang('setting_wxsyn_organization'))));
			}
		}
		exit;
	} else {
		savecache("wxsyn_donw_departmentlist","");
		exit(json_encode(array('msg' => 'success')));
	}
}
elseif ($do == 'qiwechat_syn_user') {
	$i = intval($_GET['i']); 
	$syngids = array();
	if ($syngid = getglobal('setting/synorgid')) {//设置的需要同步的部门
		$syngids = getOrgidTree($syngid);
	}
	$wx = new qyWechat( array('appid' => $_G['setting']['CorpID'], 'appsecret' => $_G['setting']['CorpSecret'], 'agentid' => 0));
	//首次查询微信端所有用户，第二次读取缓存
	if( $i!=0 ){
		$wxuserlist = getglobal('cache/wxsyn_donw_userlist'); 
		if(!$wxuserlist){
			$userlist =$wx->getUserListall(1,1);
			$wxuserlist = $userlist["userlist"];
			savecache("wxsyn_donw_userlist",$wxuserlist);
		}
	}else{
		$userlist =$wx->getUserListall(1,1);
		$wxuserlist = $userlist["userlist"];
		savecache("wxsyn_donw_userlist",$wxuserlist);
	}
	 
	//获取企业微信端用户前后查询本地用户，判断是否微信账户重名 如wechat_userid ,email, mobile相同视为同一用户　则更新信息 
	 
	if( $wxuserlist && isset($wxuserlist[$i]) ){
		$wxuser=$wxuserlist[$i];
		//微信端禁用账户不同步
		if( $wxuser["status"]==2){
			exit(json_encode(array('msg' => 'continue', 'start' => $i+1, 'message' => $wxuser['name'] . ' <span class="info">'.lang('setting_wxsyn_synchronization1').'</span>')));
		}
		//查询本地企业微信账号是否存在
		$needupdate=0;
		if(  $uid = DB::result_first("select uid from %t where wechat_userid=%s", array('user', $wxuser['userid'])) ){
			$needupdate=1; 
		}else if( $wxuser["email"] && $uid = DB::result_first("select uid from %t where email=%s ", array('user', $wxuser['email'])) ){	
			$needupdate=2; 
		}else if( $wxuser["mobile"] && $uid = DB::result_first("select uid from %t where phone=%s ", array('user', $wxuser['mobile'])) ){	
			$needupdate=3; 
		}
		 
		if($needupdate==0){//更新用户信息 
			/*if ($syngids) {//暂时取消向下同步时的同步设置限制
				$orgids = array_intersect($localorgid, $syngids);
			}*/  
			//新增用户
			if($wxuser["email"]){//有邮箱的才同步
				$password=rand(100000,9999999);
				//error_reporting(E_ALL);
				$result = uc_user_register( $wxuser["name"] , $password, $wxuser["email"], "", "", "", $_G['clientip'], 0);
				//print_r($wxuser);exit;
				if (is_array($result)) {
					$uid = $result['uid']; 
				} else {
					$uid = $result;
				}
				//echo $uid;exit;
			}
			else{
				exit(json_encode(array('msg' => 'continue', 'start' => $i+1, 'message' => $wxuser['name'] . ' <span class="info">邮箱为空同步失败</span>'))); 
			} 
		}
		 
		if( $uid>0){//判断uid大于0才执行
			$setarr=array(); 
			if( $wxuser["mobile"] ) $setarr["phone"]=$setarr["mobile"];
			if( $wxuser["email"] ) $setarr["email"]=$wxuser["email"];
			$setarr["wechat_userid"]=$wxuser["userid"];
			$setarr["wechat_status"]=$wxuser["status"];
			$setarr["username"]=$wxuser["name"]; 
			C::t('user')->update($uid, $setarr);//更新用户信息
			$wxorgids=$wxuser["department"];
			//处理用户部门和职位
			$orgids = array();
			foreach ($wxorgids as $key => $wxorgid) {
				if ( $wxorgid==1 ) continue;//顶级部门不同步
				$localorgid = DB::result_first("select orgid from %t where worgid=%d", array('organization', $wxorgid));
				if ($localorgid && C::t('organization_admin') -> ismoderator_by_uid_orgid($localorgid, $uid, 1)) {
					$orgids[$localorgid] = 0;//职位设置为0
				}
			} 
			if ($orgids) C::t('organization_user') -> replace_orgid_by_uid($uid, $orgids);
			exit(json_encode(array('msg' => 'continue', 'start' => $i+1, 'message' => $wxuser['name'] . ' <span class="info">'.lang('update_success').'</span>'))); 
		}else{
			exit(json_encode(array('msg' => 'continue', 'start' => $i+1, 'message' => $wxuser['name'] . ' <span class="info">同步错误</span>'))); 
		}
	}
	else{//微信端暂无需要同步的用户数据
		savecache("wxsyn_donw_userlist","");
		exit(json_encode(array('msg' => 'success')));
	} 
} else { 
	include template('wxsyn');
}

function getchildren( $list,$arr,&$arr2){
	$arr2[]=$arr["id"];
	$newlist=array();
	if( $list ){
		foreach($list as $k=>$v){
			if( $v["parentid"]==$arr["id"] ){
				$newlist[]=$v;
			}
		}
	} 
	if($newlist){
		foreach( $newlist as $key=>$val ){
			$newlist[$key]["children"]=getchildren($list,$val,$arr2);
		}
	}
	return $newlist;
}
?>
