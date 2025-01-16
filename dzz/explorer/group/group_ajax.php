<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/11/16
 * Time: 16:23
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
$operation = isset($_GET['operation']) ? trim($_GET['operation']):'';
if($operation == 'addgroupuser') {//添加群组成员
    $gid = isset($_GET['gid']) ? intval($_GET['gid']) : '';
    if (!$perm || !$group['type']) {
        return array('error' => lang('no_privilege'));
    }
    //添加或修改用户时
    if (submitcheck('selectsubmit')) {
        $uidarr = explode(',', trim($_GET['uids']));
        $uids = array();
        $userarr = array();
        foreach ($uidarr as $v) {
            $uids[] = preg_replace('/uid_/', '', $v);
        }
        $type=intval($_GET['type']) ? 1 : 0;
        //获取群组原用户数据
        $olduids = C::t('organization_user')->fetch_uids_by_orgid($gid);

        //获取管理员数据
        $adminer = C::t('organization_admin')->fetch_uids_by_orgid($gid);

        $getuserids = array_merge($olduids, $uids);

        //获取用户数据
        foreach (DB::fetch_all("select username,uid from %t where uid in(%n)", array('user', $getuserids)) as $v) {
            $userarr[$v['uid']] = $v['username'];
        }

        //删除用户
        $removeuser = array();
        $insertuser = array();

        foreach ($olduids as $v) {
            if (!in_array($v, $uids) && ($uid != $v ||($uid == $v && $_G['adminid'] == 1))) {
                $removeuser[] = $v;
            }
        }
        $delusers = array();
        //判断删除用户权限并删除用户
        if (count($removeuser) > 0) {
            foreach ($removeuser as $k => $v) {
                $uperm = C::t('organization_admin')->chk_memberperm($gid,$v);
                //如果是系统管理员
                if ($_G['adminid'] == 1 ) {
                    if(($group['type'] == 1 && $uperm > 1 && $_G['uid'] != $v)){
                        unset($removeuser[$k]);
                        continue;
                    }else{
                        $delusers[$v] = $userarr[$v];
                    }
                } else {
                    //如果操作对象是管理员,并且操作的是群组当前用户不是创建人或者机构,不允许操作
                    if (in_array($v, $adminer) && (($group['type'] == 1 && $perm < 2) || $group['type'] == 0)) {
                        unset($removeuser[$k]);
                        continue;
                    } else {
                        $delusers[$v] = $userarr[$v];
                    }
                }
            }
        }
        $appid = C::t('app_market')->fetch_appid_by_mod('{dzzscript}?mod='.MOD_NAME, 2);
        if(count($removeuser) > 0) {

            foreach(C::t('organization_user')->delete_by_uid_orgid($removeuser, $gid) as $v) {
                if ($v['uid'] != getglobal('uid')) {
                    $notevars = array(
                        'from_id' => $appid,
                        'from_idtype' => 'app',
                        // 'url' => getglobal('siteurl') . '/#group&gid='.$orgid,
                        'author' => getglobal('username'),
                        'authorid' => getglobal('uid'),
                        'dataline' => dgmdate(TIMESTAMP),
                        'fname' => getstr($group['orgname'], 31),
                    );
                    $action = 'explorer_user_remove';
                    $ntype = 'explorer_user_remove_' . $gid;

                    dzz_notification::notification_add($v['uid'], $ntype, $action, $notevars, 1, 'dzz/explorer');
                }
            }
            //增加事件
            $eventdata = array('username' => getglobal('username'), 'uid' => getglobal('uid'), 'orgname' => $group['orgname'], 'delusers' => implode(',', $delusers));
            C::t('resources_event')->addevent_by_pfid($group['fid'], 'delete_group_user', 'deleteuser', $eventdata, $gid, '', $group['orgname']);
        }
        //新添加用户
        $insertuserdata = array();
        $insertusername = array();
        foreach ($uids as $v) {
            if (!in_array($v, $olduids) && !empty($v)) {
                $insertuser[] = $v;
                $insertusername[]=$userarr[$v];
                $insertuserdata[] = array('uid'=>$v,'username'=>$userarr[$v],'ufirst'=>new_strsubstr(ucfirst($userarr[$v]),1,''));
            }

        }
        //添加用户
        if (count($insertuser) > 0) {
			$permtitle = lang('explorer_gropuperm');
            foreach(C::t('organization_user')->insert_by_orgid($gid,$insertuser) as $iu){
				//发送通知
				 if ($iu != getglobal('uid')) {
                    $notevars = array(
                        'from_id' => $appid,
                        'from_idtype' => 'app',
                        'url' => getglobal('siteurl') . MOD_URL . '#group&gid=' . $gid,
                        'author' => getglobal('username'),
                        'authorid' => getglobal('uid'),
                        'dataline' => dgmdate(TIMESTAMP),
                        'fname' => getstr($group['orgname'], 31),
                        'permtitle' => $permtitle[0]
                    );
                    $action = 'explorer_user_add';
                    $ntype = 'explorer_user_add_' . $gid;
                    dzz_notification::notification_add($iu, $ntype, $action, $notevars, 1, 'dzz/explorer');
                }
			}
            $insertuserdata = C::t('resources_event')->result_events_has_avatarstatusinfo($insertuser,$insertuserdata);
            //增加事件
            $eventdata = array('username' => getglobal('username'), 'uid' => getglobal('uid'), 'orgname' => $group['orgname'], 'insertusers' => implode(',', $insertusername));
            C::t('resources_event')->addevent_by_pfid($group['fid'], 'add_group_user', 'adduser', $eventdata, $gid, '', $group['orgname']);
        }
        if($type == 1){
            exit(json_encode(array('success' => true, 'insertuser' => $insertuserdata, 'delusers' => $delusers, 'adminid'=>($_G['adminid'] == 1)?1:0,'perm' => $perm, 'grouptype' => $group['type'])));
        }else{
            exit(json_encode(array('success' => true, 'fid' => $group['fid'])));
        }
    }
}elseif($operation == 'groupsetting'){
    $gid = $_GET['gid'];
    if (!$perm || !$group['type']) {
        return array('error' => lang('no_privilege'));
    }
    if(isset($_GET['setsubmit'])) {
        $arr = $_GET['arr'];
        $return = C::t('organization')->update_by_orgid($gid, $arr);
        if ($return['error']) {
            showTips(array('error' => $return['error']), 'json');
        } else {
            showTips(array('success' => true), 'json');
        }

    }else{
        //$group = C::t('organization')->fetch($gid);
        $grouppatharr = getpath($groupinfo['path']);
        $grouppathstr = implode('\\',$grouppatharr);
    }
}elseif($operation == 'getAtData'){
    $gid = isset($_GET['gid'])?intval($_GET['gid']):'';
    $fid = isset($_GET['fid']) ? intval($_GET['fid']):'';
    $keyword = isset($_GET['term']) ? trim($_GET['term']):'';
    if(!$fid){
        $rid = isset($_GET['rid']) ? trim($_GET['rid']):'';
        $fileinfo = C::t('resources')->fetch_info_by_rid($rid);
        if($fileinfo['type'] == 'folder'){
            $fid = $fileinfo['oid'];
        }else{
            $fid = $fileinfo['pfid'];
        }
    }
    $perm = DB::result_first("select perm_inherit from %t where fid = %d",array('folder',$fid));
    $powerarr = perm_binPerm::getPowerArr();
    $uids = array();
    if($perm&$powerarr['read2']){
        $members = C::t('organization_user')->fetch_parentadminer_andchild_uid_by_orgid($gid,true);
        $uids = $members['all'];
    }else{
        $members = C::t('organization_user')->fetch_parentadminer_andchild_uid_by_orgid($gid,false);
        $uids = $members['adminer'];
    }
  
    $params = array('user',$uids);
    $sql_user = 'where uid in(%n) ';
    if($keyword){
        $sql_user .= ' and username like %s';
        $params[] = '%'.$keyword.'%';
    }
    $list = array();
    foreach(DB::fetch_all("select uid,username  from %t   $sql_user",$params) as $value){
        if($value['uid'] == $uid) continue;
        $list[]=array('name'=>$value['username'],
            'searchkey'=> pinyin::encode($value['username'],'all').$value['username'],
            'id'=>'u'.$value['uid'],
            'title'=>$value['username'].':'.'u'.$value['uid'],
            'avatar'=>avatar_block($value['uid'])
        );
    }
    exit(json_encode($list));
}
include template('group/group_ajax');