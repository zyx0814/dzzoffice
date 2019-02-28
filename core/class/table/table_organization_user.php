<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class table_organization_user extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'organization_user';
        $this->_pk = '';
        parent::__construct();
    }

    public function insert_by_orgid($orgid,$uids,$jobid = 0)
    {
        if (!$uids || !$orgid) return array();
		if(!$org = C::t('organization')->fetch($orgid)) {
			return array();
		}
		if (!is_array($uids)) $uids=array($uids);
        $ret=array();
		foreach ($uids as $v) {
		   if(parent::insert(array("orgid" => $orgid, 'uid' => $v, 'jobid' => $jobid, 'dateline' => TIMESTAMP), 1, 1)){
			   $ret[$v]=$v;
		   }
		}
      if( $org["type"]==0){//非群组才同步
         self::syn_user( $ret );
      } 
      return $ret;
    }

    public function fetch_by_uid_orgid($uid, $orgid)
    {
        return DB::fetch_first("select * from %t where uid=%d and orgid=%d", array($this->_table, $uid, $orgid));
    }

    public function replace_orgid_by_uid($uid, $orgarr)
    {
        $orgids = array();
        foreach ($orgarr as $key => $value) {
            $orgids[] = $key;
        }

        $Oorgids = self::fetch_orgids_by_uid($uid);
        if (!is_array($orgids)) $orgids = array($orgids);
        $insertids = array_diff($orgids, $Oorgids);
        $delids = array_diff($Oorgids, $orgids);
        $updateids = array_diff($orgids, $delids, $insertids);
        if ($delids) DB::delete($this->_table, "uid='{$uid}' and orgid IN (" . dimplode($delids) . ")");
        foreach ($insertids as $orgid) {
            if ($orgid > 0) self::insert_by_orgid($orgid,$uid,$orgarr[$orgid]);
        }
        foreach ($updateids as $orgid) {
            if ($orgid > 0) DB::update($this->_table, array('jobid' => $orgarr[$orgid]), "orgid='{$orgid}' and uid='{$uid}'");
        }
        return true;
    }
    
    public function bind_uid_and_orgid($uid, $orgarr){
         $orgids = array();
         foreach ($orgarr as $key => $value) {
             $orgids[] = $key;
         }
 
         $Oorgids = self::fetch_orgids_by_uid($uid);
         if (!is_array($orgids)) $orgids = array($orgids);
         $insertids = array_diff($orgids, $Oorgids);
         $delids = array_diff($Oorgids, $orgids);
         $updateids = array_diff($orgids, $delids, $insertids);
         //if ($delids) DB::delete($this->_table, "uid='{$uid}' and orgid IN (" . dimplode($delids) . ")");
         
         foreach ($insertids as $orgid) {
             if ($orgid > 0) self::insert_by_orgid($orgid,$uid,$orgarr[$orgid]);
         }
         foreach ($updateids as $orgid) {
             if ($orgid > 0) DB::update($this->_table, array('jobid' => $orgarr[$orgid]), "orgid='{$orgid}' and uid='{$uid}'");
         }
 
         return true;
    }

    public function delete_by_uid($uid, $wxupdate = 1)
    {
        if ($return = DB::delete($this->_table, "uid='{$uid}'")) {
             
            self::syn_user( $uid );
            return $return;
        } else return false;
    }

    public function delete_by_uid_orgid($uids, $orgid, $wxupdate = 1)
    {
        $uids = (array)$uids;
        $uidarr = array();
        //获取管理员用户
        foreach (DB::fetch_all("select uid,admintype from %t where uid in(%n) and orgid = %d ", array('organization_admin', $uids, $orgid)) as $v) {
            $uidarr[] = array('uid' => $v['uid'], 'perm' => $v['admintype']);
            $key = array_search($v['uid'], $uids);
            unset($uids[$key]);
        }
        //如果有管理员用户,忽略无权限删除的用户
        if (count($uidarr) > 0) {
            //获取当前操作用户权限
            $perm = C::t('organization_admin')->chk_memberperm($orgid, getglobal('uid'));
            foreach ($uidarr as $val) {
                if ($perm > $val['perm']) {
                    $uids[] = $val['uid'];
                }
            }
        }
        if ($return = DB::delete($this->_table, "uid IN (" . dimplode($uids) . ") and orgid='{$orgid}'")) {
            //删除管理员表数据
            DB::delete('organization_admin', "uid IN (" . dimplode($uids) . ") and orgid='{$orgid}'");
            include_once libfile('function/cache');
            updatecache('organization'); 
            self::syn_user( $uids );
            return $uids;
        } else return false;
    }

    public function delete_by_orgid($orgids)
    {
         if (!$orgids) return;
         $orgids = (array)$orgids;
         //$uids = self::fetch_uids_by_orgid($orgids);
         $syn_uid =array();
         foreach ($orgids as $orgid) {
            $org = DB::fetch_first("select orgid,type from %t where orgid=%d", array('organization', $orgid));
            if( $org ){
               $query = DB::query("select uid from %t where orgid=%d", array($this->_table, $orgid));
               while ($value = DB::fetch($query)) { 
                   if( $org["type"]==0 ) $syn_uid[]=$value['uid'];
               }
            }
         }
         
         if( $syn_uid ) $syn_uid = array_unique($syn_uid);
        
        if ($return = DB::delete($this->_table, "orgid IN (" . dimplode($orgids) . ")")) {
            if ($syn_uid) self::syn_user( $syn_uid );
            return $return;
        } else return false;
    }

    public function fetch_org_by_uid($uids,$orgtype=0)
    {
       $uids=(array)$uids;
		$orgids=array();
	
		$param=array($this->_table);
		if($orgtype>-1){
			$sql = "select u.orgid from %t u LEFT JOIN %t o ON u.orgid=o.orgid where u.uid IN(%n) and o.type=%d";
			$param[]='organization';
			$param[]=$uids;
			$param[]=$orgtype;
		}else{
			$sql = "select orgid from %t where uid IN(%n)";
			$param[]=$uids;
		}
		foreach(DB::fetch_all($sql,$param) as $value){
			$orgids[$value['orgid']]=$value['orgid'];
		}
		return $orgids;
    }

    public function fetch_uids_by_orgid($orgids)
    {
        $uids = array();
        if (!is_array($orgids)) $orgids = array($orgids);
        $query = DB::query("select uid from %t where orgid IN(%n)", array($this->_table, $orgids));
        while ($value = DB::fetch($query)) {
            $uids[] = $value['uid'];
        }
        unset($query);
        return $uids;
    }

    public function fetch_user_not_in_orgid($limit = 10000)
    {
      $limitsql='';
		if($limit) $limitsql="limit $limit";
		//获取属于机构和部门的用户
		$uids_org=array();
		foreach(DB::fetch_all("SELECT u.uid from %t u LEFT JOIN %t o ON u.orgid=o.orgid where o.type='0'",array($this->_table,'organization')) as $value){
			 $uids_org[$value['uid']]=$value['uid'];
		}
		//获取不属于所有机构和部门的用户
		return DB::fetch_all("select username,uid,email,groupid from %t where uid NOT IN(%n) order by username $limitsql ",array('user',$uids_org),'uid');
    }

    public function fetch_user_by_orgid($orgids, $limit = 0, $count = false)
    {
        if (!is_array($orgids)) $orgids = array($orgids);
        $limitsql = '';
        if ($limit) $limitsql = "limit $limit";
		
        if ($count) return DB::result_first("select COUNT(*) %t where orgid IN(%n)", array($this->_table, $orgids));
        return DB::fetch_all("select o.* ,u.username,u.email,u.groupid from " . DB::table('organization_user') . " o LEFT JOIN " . DB::table('user') . " u ON o.uid=u.uid where o.orgid IN(" . dimplode($orgids) . ") order by dateline DESC $limitsql ");
    }

   public function fetch_orgids_by_uid($uids,$orgtype = 0){
		$uids=(array)$uids;
		$orgids=array();
	
		$param=array($this->_table);
		if($orgtype>-1){
			$sql = "select u.orgid from %t u LEFT JOIN %t o ON u.orgid=o.orgid where u.uid IN(%n) and o.type=%d";
			$param[]='organization';
			$param[]=$uids;
			$param[]=$orgtype;
		}else{
			$sql = "select orgid from %t where uid IN(%n)";
			$param[]=$uids;
		}
		foreach(DB::fetch_all($sql,$param) as $value){
			$orgids[$value['orgid']]=$value['orgid'];
		}
		return $orgids;
	}

    //判断用户是否为当前部门成员
    public function fetch_num_by_orgid_uid($orgid, $uid)
    {
        if (!$orgid || !$uid) return false;
        if (DB::result_first("select count(*) from %t where uid = %d and orgid = %d", array($this->_table, $uid, $orgid))) {
            return true;
        } else {
            return false;
        }

    }

    public function fetch_usernums_by_orgid($orgid)
    {
        if (!$orgid) return '';
        $numbers = DB::result_first("select count(*) from %t where orgid = %d", array($this->_table, $orgid));
        return $numbers;
    }

    public function fetch_usernum_by_orgid($orgid)
    {
        if (!$orgid) return false;
        $orgid = intval($orgid);
        if ($result = DB::result_first("select count(*) from %t where orgid = %d", array($this->_table, $orgid))) {
            return $result;
        }
        return false;
    }

    public function fetch_all_by_uid($uids)
    {
        $uids = (array)$uids;
        return DB::fetch_all("select * from %t where uid IN(%n) ", array($this->_table, $uids));
    }


    public function move_to_forgid_by_orgid($forgid, $orgid)
    {//移动用户到上级部门
        if (!$org = C::t('organization')->fetch($forgid)) return false;
        if (!$org['forgid']) {
            foreach (DB::fetch_all("select * from %t where orgid=%d", array($this->_table, $orgid)) as $value) {
                C::t('organization_admin')->delete_by_uid_orgid($value['uid'], $orgid);
            }

            return self::delete_by_orgid($orgid);
        }
        foreach (DB::fetch_all("select * from %t where orgid=%d", array($this->_table, $orgid)) as $value) {
            if (DB::result_first("select COUNT(*) from %t where orgid=%d and uid=%d", array($this->_table, $org['forgid'], $value['uid']))) {
                C::t('organization_admin')->delete_by_uid_orgid($value['uid'], $orgid);
                DB::delete($this->_table, "orgid='{$org[forgid]}' and uid='{$value[uid]}'");
            } else {
                $value['orgid'] = $org['forgid'];
                parent::insert($value);
            }
        }

        return true;
    }

    public function move_to_by_uid_orgid($uid, $orgid, $torgid, $copy)
    {
        if ($orgid == $torgid) return true;
        if ($torgid == 0) {
            C::t('organization_admin')->delete_by_uid_orgid($uid, $orgid);
            return self::delete_by_uid_orgid($uid, $orgid);
        }
        if (!$copy && DB::result_first("select COUNT(*) from %t where orgid=%d and uid=%d", array($this->_table, $torgid, $uid))) {
            C::t('organization_admin')->delete_by_uid_orgid($uid, $orgid);
            return self::delete_by_uid_orgid($uid, $orgid, 0);
        } else {
            self::insert_by_orgid($torgid,$uid);
            if (!$copy) self::delete_by_uid_orgid($uid, $orgid, 0);
            return true;
        }
    }
    //查询成员
    public function fetch_user_byorgid($orgid, $username = '')
    {
        $where = " and 1=1";
        $params = array($this->_table, 'user', $orgid);
        if ($username) {
            $uid = DB::result_first("select uid from %t where username like %s", array('user', '%' . $username . '%'));
            $where .= " and o.uid = %d";
            $params[] = $uid;
        }
        $userinfo = array();
        foreach (DB::fetch_all("select o.*,u.username,u.email from %t o left join %t u on o.uid = u.uid where o.orgid = %d $where", $params) as $v) {
            $admintype = DB::result_first("select admintype from %t where orgid = %d and uid = %d", array('organization_admin', $orgid, $v['uid']));
            if (!$admintype) {
                $v['perm'] = 0;
            } else {
                $v['perm'] = $admintype;
            }
            $userinfo[] = $v;
        }
        return $userinfo;
    }
    //获取当前机构或部门及下级所有的用户
    public function get_all_user_byorgid($orgid){
       $pathkey = DB::result_first("select pathkey from %t where orgid = %d",array('organization',$orgid));
       $params = array('organization','organization_user','user','^'.$pathkey.'.*');
        $userinfo = array();
        foreach (DB::fetch_all("select o.orgid,ou.*,u.username,u.email from %t o 
          left join %t ou on ou.orgid = o.orgid
          left join %t u on ou.uid = u.uid  where o.pathkey regexp %s", $params) as $v) {
            $admintype = DB::result_first("select admintype from %t where orgid = %d and uid = %d", array('organization_admin', $orgid, $v['uid']));
            if (!$admintype) {
                $v['perm'] = 0;
            } else {
                $v['perm'] = $admintype;
            }
            $userinfo[] = $v;
        }
        return $userinfo;
    }

    //设置成员权限
    public function set_admin_by_giduid($uid, $gid, $perm = 0)//perm,0为协作成员，1为管理员,2为创始人
    {
        global $_G;
        $uid = intval($uid);
        $gid = intval($gid);
        if (!$group = C::t('organization')->fetch($gid)) array('error' => lang('group_not_exists'));

        //获取操作用户权限
        $doperm = C::t('organization_admin')->chk_memberperm($gid, $_G['uid']);
        if ($perm == 2 && $doperm != 2) return array('error' => lang('no_privilege'));//检查权限

        $permtitle = lang('explorer_gropuperm');
        //查詢用戶是否存在
        if ($result = DB::fetch_first("select ou.*,u.username from %t ou 
            left join %t u on ou.uid=u.uid where ou.orgid=%d and ou.uid = %d", array($this->_table, 'user', $gid, $uid))) {
            //转让创始人
            if ($perm == 2) {
                $olduser = DB::fetch_first("select u.uid,u.username from %t ou
                left join %t u on ou.uid = u.uid where ou.orgid = %d and ou.admintype = %d", array('organization_admin', 'user', $gid, 2));
                if (C::t('organization_admin')->update_perm($uid, $gid, $perm) && DB::delete('organization_admin', array('orgid' => $gid, 'uid' => $olduser['uid']))) {
                    return array('success' => lang('change_creater_succeed'), 'perm' => $perm, 'olduid' => $olduser['uid'],'olduser'=>$olduser,'member'=>$result['username']);
                }
            } elseif (C::t('organization_admin')->update_perm($uid, $gid, $perm)) {//设置管理员
                return array('success' => true, 'perm' => $perm,'member'=>$result['username']);
            }
        }
        return array('error' => lang('explorer_do_failed'));
    }

    //查询机构下成员数
    public function fetch_num_by_toporgid($orgid)
    {
        $pathkey = DB::result_first("select pathkey from %t where orgid = %d", array('organization', $orgid));
        $orgidarr = array();
        foreach (DB::fetch_all("select orgid from %t where pathkey regexp %s", array('organization', '^' . $pathkey . '.*')) as $v) {
            $orgidarr[] = $v['orgid'];
        }
        $uidarr = array();
        foreach (DB::fetch_all("select uid from %t where orgid in (%n)", array($this->_table, $orgidarr)) as $v) {
            $uidarr[] = $v['uid'];
        }
        $uidarr = array_unique($uidarr);
        return count($uidarr);
    }

    public function fetch_parentadminer_andchild_uid_by_orgid($orgid,$partget=true)
    {
        $uid = getglobal('uid');
        $uids = array('adminer'=>array(),'partmember'=>array());
        $orgid = intval($orgid);
        $parentadminer = array();
        //获取具有管理员权限的用户
        $pathkey = DB::result_first("select pathkey from %t where orgid = %d", array('organization', $orgid));
        $gids = explode('-',str_replace('_','',$pathkey));
        foreach(DB::fetch_all("select uid from %t where orgid in(%n)",array('organization_admin',$gids)) as $v){
            $uids['adminer'][] = $v['uid'];
        }
        if($partget){
            //获取没有管理员权限的用户
            $childgids = array($orgid);
            $like = $pathkey.'.+';
            foreach (DB::fetch_all("select orgid from %t where pathkey REGEXP %s", array('organization', $like)) as $value) {
                $childgids[] = $value['orgid'];
            }
            $childuids = array();
            foreach(DB::fetch_all("select uid from %t  where orgid in(%n)",array($this->_table,$childgids)) as $v){
                $childuids[] = $v['uid'];
            }
            $uids['partmember'] = array_diff(array_unique($childuids),$uids['adminer']);
        }
        $uids['all'] = array_merge($uids['partmember'],$uids['adminer']);
        return $uids;
    }
   
   public function syn_user( $data=array() ){
      Hook::listen('syntoline_user',$data);//注册绑定到钉钉用户表
      //Hook::listen('dzztowxwork_synuser',$data);//注册绑定到企业微信用户表
   } 
   
}

