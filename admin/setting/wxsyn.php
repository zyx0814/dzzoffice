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
//error_reporting(E_ALL);
include_once libfile('function/organization');
$navtitle = lang('data_synchronization');
$do = $_GET['do'];
$op = $_GET['op']?$_GET['op']:' ';
if (submitcheck('synsubmit')) {

}
elseif ($do == 'qiwechat_syn_org') {
	$i = intval($_GET['i']);
	$wx = new qyWechat( array('appid' => $_G['setting']['CorpID'], 'appsecret' => $_G['setting']['CorpSecret'], 'agentid' => 0));
	if ($i < 1) {
		$data = array("id" => 1, "name" => $_G['setting']['sitename'], //部门名称
		);
		if (!$wx -> updateDepartment($data)) {
			runlog('wxlog', lang('update_top_department_name').', errCode:' . $wx -> errCode . '; errMsg:' . $wx -> errMsg);
		}
	}
	$wd = array();
	if ($wxdepart = $wx -> getDepartment()) {
		foreach ($wxdepart['department'] as $value) {
			$wd[$value['id']] = $value;
		}
	} else {
		exit(json_encode(array('error' => lang('setting_wxsyn_weixin') . $wx -> errCode . ':' . $wx -> errMsg . '</p>')));
	}

	if ($org = DB::fetch_first("select * from %t where type=0 and orgid>%d  order by orgid ", array('organization', $i))) {//type=0排除群组
		if ($org['forgid']) {
			if (($forg = C::t('organization') -> fetch($org['forgid'])) && !$forg['worgid']) {
				if ($worgid = C::t('organization') -> wx_update($forg['orgid'])) {
					$forg['worgid'] = $worgid;
				} else {
					exit(json_encode(array('msg' => 'continue', 'start' => $org['orgid'], 'message' => $org['orgname'] . lang('setting_wxsyn_organization'))));
				}
			}
		}
		$parentid = ($org['forgid'] == 0 ? 1 : $forg['worgid']);
		if ($org['worgid'] && $wd[$org['worgid']] && $parentid == $wd[$org['worgid']]['parentid']) {//更新机构信息
			$data = array("id" => $org['worgid']);
			if ($wd[$org['worgid']]['name'] != $org['orgname'])
				$data['name'] = $org['orgname'];
			if ($wd[$org['worgid']]['parentid'] != $parentid)
				$data['parentid'] = $parentid;
			if ($wd[$org['worgid']]['order'] != $org['order'])
				$data['order'] = $org['order'];
			if ($data)
				$data['id'] = $org['worgid'];
			if ($data) {
				if (!$wx -> updateDepartment($data)) {
					exit(json_encode(array('msg' => 'continue', 'start' => $org['orgid'], 'message' => $org['orgname'] . ' <span class="danger">' . $wx -> errCode . ':' . $wx -> errMsg . '</span>')));
				}
			}
			exit(json_encode(array('msg' => 'continue', 'start' => $org['orgid'], 'message' => $org['orgname'] . ' <span class="success">'.lang('update_success').'</span>')));
		} else {//创建机构信息
			$data = array("name" => $org['orgname'], //部门名称
			"parentid" => $org['forgid'] == 0 ? 1 : $forg['worgid'], //父部门id
			"order" => $org['disp'] + 1, //(非必须)在父部门中的次序。从1开始，数字越大排序越靠后
			);
			if ($ret = $wx -> createDepartment($data)) {
				C::t('organization') -> update($org['orgid'], array('worgid' => $ret['id']));
				exit(json_encode(array('msg' => 'continue', 'start' => $org['orgid'], 'message' => $org['orgname'] . ' <span class="success">'.lang('creation_success').'</span>')));
			} else {
				if ($wx -> errCode == '60008') {//部门的worgid不正确导致的问题
					foreach ($wd as $value) {
						if ($value['name'] == $data['name'] && $value['parentid'] = $data['parentid']) {
							C::t('organization') -> update($org['orgid'], array('worgid' => $value['id']));
							exit(json_encode(array('msg' => 'continue', 'start' => $org['orgid'], 'message' => $org['orgname'] . ' <span class="success">'.lang('update_success').'</span>')));
						}
					}
				}
				exit(json_encode(array('msg' => 'continue', 'start' => $org['orgid'], 'message' => $org['orgname'] . ' <span class="danger">' . $wx -> errCode . ':' . $wx -> errMsg . '</span>')));
			}
		}
	} else {
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

	if ($user = DB::fetch_first("select u.*,o.orgid from " . DB::table('user') . " u LEFT JOIN " . DB::table('organization_user') . " o ON o.uid=u.uid where u.uid>$i and o.orgid>0 order by uid")) {

		$worgids = array();
		if ($orgids = C::t('organization_user') -> fetch_orgids_by_uid($user['uid'])) {
			if ($syngids) {
				$orgids = array_intersect($orgids, $syngids);
			}
			if ($orgids) {
				foreach (C::t('organization')->fetch_all($orgids) as $value) {
					if( $value['type']>0 ){//群主类型不同步至微信
                        continue;
                    }
					if ($value['worgid'])
						$worgids[] = $value['worgid'];
					else {
						if ($worgid = C::t('organization') -> wx_update($value['orgid'])) {
							$worgids[] = $worgid;
						}
					}
				}
			}
		}
		
		if( !$worgids ) $worgids=array(1);//默认同步到企业微信的跟部门下
		
		if (!$worgids) {
			$data = array("userid" => "dzz-" . $user['uid'], "enable" => 0, "department" => 1, );
			if ($wx -> updateUser($data)) {
				exit(json_encode(array('msg' => 'continue', 'start' => $user['uid'], 'message' => $user['username'] . '<span class="info">'.lang('setting_wxsyn_synchronization1').'</span>')));
			} else {
				exit(json_encode(array('msg' => 'continue', 'start' => $user['uid'], 'message' => $user['username'] . ' <span class="info">'.lang('setting_wxsyn_synchronization2').'</span>')));
			}

		}
		$profile = C::t('user_profile') -> fetch_all($user['uid']);
		$wxuser =array();
		if( $user["wechat_userid"] ){
			$wxuser = $wx->getUserInfo( $user["wechat_userid"] );
		}
		
		if ( $wxuser ) {//更新用户信息

			$data = array(
				"userid" => $user["wechat_userid"],
				"name" => $user['username'], 
				//"position" => '',
				"email" => $user['email'],
				"enable" => $user['status'] ? 0 : 1
			);
			if (array_diff($wxuser['department'], $worgids)) {
				$data['department'] = $worgids;
			}
			if ($user['phone'] && $user['phone'] != $wxuser['mobile']) {
				$data['mobile'] = $user['phone'];
			}
			/*if ($user['weixinid'] && $wxuser['wechat_status'] == 4) {
				$data['weixinid'] = $user['weixinid'];
			}*/
			if ($profile['telephone'] && $profile['telephone'] != $wxuser['telephone']) {
				$data['telephone'] = $profile['telephone'];
			}
			if ($profile['gender'] && ($profile['gender'] - 1) != $wxuser['gender']) {
				$data['gender'] = $profile['gender'] - 1;
			}

			if ($wx -> updateUser($data)) {
				//$setarr = array('wechat_status' => $wxuser['status']);
				//$setarr['weixinid'] = empty($wxuser['weixinid']) ? $user['weixinid'] : $wxuser['weixinid'];
				$setarr['phone'] = empty($user['phone']) ? $wxuser['phone'] : $user['phone'];
				//$setarr['wechat_userid'] = 'dzz-' . $user['uid'];
				C::t('user') -> update($user['uid'], $setarr);
				exit(json_encode(array('msg' => 'continue', 'start' => $user['uid'], 'message' => $user['username'] . ' <span class="success">'.lang('update_success').'</span>')));
			} else {
				exit(json_encode(array('msg' => 'continue', 'start' => $user['uid'], 'message' => $user['username'] . ' <span class="danger">' . $wx -> errCode . ':' . $wx -> errMsg . '</span>')));
			}

		} else {//创建用户信息
			$data = array(
				"userid" => 10000+ $user['uid'],//"dzz-" . $user['uid'],
				"name" => $user['username'],
				"department" => $worgids,
				//"position" => '',
				"email" => $user['email'],
				//"weixinid" => $user['wechat'],
				"enable" => $user['status'] ? 0 : 1
			);
			if ($user['phone']) {
				$data['mobile'] = $user['phone'];
			}
			if ($profile['telephone']) {
				$data['telephone'] = $profile['telephone'];
			}
			if ($profile['gender']) {
				$data['gender'] = $profile['gender'] - 1;
			}
			
			//创建用户前查询企业微信端所有用户，判断是否微信账户重名 如email 或者 mobile相同视为同一用户　则更新信息
			$userlist =$wx->getUserListall(1,1);
			$wxuser=array();
			if( $userlist["userlist"] ){
				foreach($userlist["userlist"] as $k=>$v ){
					if($v["email"] && $data["email"]==$v["email"] ){
						$wxuser=$v;
						break;
					}
					if($v["mobile"] && $data["mobile"]==$v["mobile"] ){
						$wxuser=$v;
						break;
					}
				}
			}
			if( $wxuser ){//判断是否已存在手机号或者邮箱，如果又则认定为是同一个账户，不需要重新创建
				$data["userid"]=$wxuser["userid"];
				$result = $wx->updateUser($data);
			}else{//查询不到企业微信，重新创建 重新创建时判断是否重名，如果重名重新命名，直到不重名
				if( $userlist["userlist"] ){
					$nowuserid = $data["userid"];
					$noneunion=true;
					$i=1;
					while( $noneunion ){//检查是否和企业微信断已有用户重名
						$isunion=false;
						foreach($userlist["userlist"] as $k=>$v ){
							if($v["userid"] == $nowuserid ){
								$isunion=true;
								break;
							}
						}
						if( $isunion ){
							$nowuserid = $data["userid"]."_".$i;
						}else{
							$data['userid']=$nowuserid;
							$noneunion=false;
						}
						$i++;
					}
				}
				$result = $wx->createUser($data);
			}
			
			if ( $result ) {
				C::t('user') -> update($user['uid'], array('wechat_userid' => 'dzz-' . $user['uid']));
				exit(json_encode(array('msg' => 'continue', 'start' => $user['uid'], 'message' => $user['username'] . '  <span class="success">'.lang('creation_success').'</span>')));
			} else {
				exit(json_encode(array('msg' => 'continue', 'start' => $user['uid'], 'message' => $user['username'] . ' <span class="danger">' . $wx -> errCode . ':' . $wx -> errMsg . '</span>')));
			}
		}
	} else {
		exit(json_encode(array('msg' => 'success')));
	}
} else {

	include template('wxsyn');
}
?>
