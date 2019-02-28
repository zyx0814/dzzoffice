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

class table_user_profile extends dzz_table
{

	public function __construct() {

		$this->_table = 'user_profile';
		$this->_pk    = 'uid';
		//$this->_pre_cache_key = 'user_profile_';
		parent::__construct();
	}
    public function get_userprofile_by_uid($uid){
		global $_G;
		$uid = $uid ? intval($uid):'';
		if(!$uid) return false;
		$values = array();
		if($values = C::t('user')->get_user_by_uid($uid)){
			$var = 'user_{$uid}_profile';
			if(($_G[$var] = self::fetch($uid)) !== false){
				if(!empty($_G[$var]['department'])){
					$_G[$var]['department_tree']=C::t('organization')->getPathByOrgid(intval($_G[$var]['department']));
				}else{
					$_G[$var]['department_tree']='请选择机构或部门';
				}
			}else{
				$_G[$var] = array();
			}
			$values =  array_merge($values,$_G[$var]);
		}
		return $values;
	}
	public function get_user_info_by_uid($uid){
	    global $_G;
        $uid = $uid ? intval($uid):'';
        $info = array();
       foreach($result = DB::fetch_all("select * from %t where uid =%d",array($this->_table,$uid)) as $value){
           $info[$value['fieldid']] = $value['value'];
           $info['privacy']['profile'][$value['fieldid']] = $value['privacy'];
       }
        $var = "user_{$uid}_profile";
        if(!empty($_G[$var]['department'])){
            $info['department_tree'] = $_G[$var]['department_tree']=C::t('organization')->getPathByOrgid(intval($_G[$var]['department']));
        }else{
            $info['department_tree'] = $_G[$var]['department_tree']='请选择机构或部门';
        }
        if($user = C::t('user')->get_user_by_uid($uid)){
            $info = array_merge($user,$info);
        }
        $field = DB::fetch_first("select attachextensions,maxattachsize,usesize,addsize,buysize,wins,perm from %t where uid = %d",array('user_field',$uid));
        $info = array_merge($field,$info);
        return $info;
    }

    public function fetch($uid){
        $data=array('uid'=>$uid);
        foreach(DB::fetch_all("select * from %t where uid =%d",array($this->_table,$uid)) as $value) {
            $data[$value['fieldid']]=$value['value'];
        }
        return $data;
    }
    public function fetch_by_field($uid,$field){ //获取用户某项资料的值
        return DB::result_first("select value from %t where uid=%d and fieldid=%s",array($this->_table,$uid,$field));
    }
    public function update($uid,$fieldarr){//插入用户资料
        foreach($fieldarr as $key=>$value){
			if(is_array($value)){
				$setarr=array(  'uid'=>$uid,
								'fieldid'=>$key,
								'value'=>$value['value'],
								'privacy'=>$value['privacy']
							);
			}else{
				$setarr=array(  'uid'=>$uid,
								'fieldid'=>$key,
								'value'=>$value
							);
			}
            
            DB::insert($this->_table,$setarr,0,1);
        }
        return true;
    }
    public function update_by_skey($fieldid,$val,$uid = 0){
        if(!$uid)$uid = getglobal('uid');
        if(!DB::update($this->_table,array('value'=>$val),array('uid'=>$uid,'fieldid'=>$fieldid))){
            $setarr=array('uid'=>$uid,
                'fieldid'=>$fieldid,
                'value'=>$val
            );
            DB::insert($this->_table,$setarr,0,1);
        }
        return true;
    }
    public function fetch_phone($phone){
        return DB::fetch_first("select * from %t where `fieldid` = %s and `value` = %s",array($this->_table,'phone',$phone));
    }
    public function fetch_weixinid($weixinid){
        return DB::fetch_first("select * from %t where `fieldid` = %s and `value` = %s",array($this->_table,'weixinid',$weixinid));
    }
    public function insert($fieldarr){//插入用户资料
        $uid=$fieldarr['uid'];
        unset($fieldarr['uid']);
        foreach($fieldarr as $key=>$value){
            $setarr=array('uid'=>$uid,
                'fieldid'=>$key,
                'value'=>$value
            );
            DB::insert($this->_table,$setarr,0,1);
        }
        return true;
    }
    public function fetch_privacy_by_uid($uid){
        $uid = intval($uid);
        $privacys = array();
       foreach(DB::fetch_all("select privacy,fieldid from %t where uid = %d",array($this->_table,$uid)) as $val){
           $privacys[$val['fieldid']] = $val['privacy'];
       }
       return $privacys;
    }
    public function delete($uid){
        $uid=(array)$uid;
        return DB::delete($this->_table,"uid IN (".dimplode($uid).")");
    }
    public function delete_by_field($fieldids){ //删除用户资料项
        $fieldids=(array)$fieldids;
        return DB::delete($this->_table,"fieldid IN (".dimplode($fieldids).")");
    }
    public function delete_by_uid($uids){ //删除用户资料
        $uids=(array)$uids;
        return DB::delete($this->_table,"uid IN (".dimplode($uids).")");
    }
    public function fetch_all($uids) {
        $data = array();
        $uids=(array)$uids;
        if(!empty($uids)) {
            foreach(DB::fetch_all("select * from %t where uid IN (%n)",array($this->_table,$uids)) as $value) {
                $data[$value['uid']][$value['fieldid']]=$value['value'];
                $data[$value['uid']]['uid']=$value['uid'];
            }
        }
        return $data;
    }

    public function count_by_field($field, $val) {

        return DB::result_first('SELECT COUNT(*) as cnt FROM '.DB::table($this->_table).' WHERE '.DB::field($field, $val));
    }

    public function fetch_all_field_value($field) {
        return DB::fetch_all('SELECT DISTINCT(`'.$field.'`) FROM '.DB::table($this->_table), null, $field);
    }

    public function fetch_all_will_birthday_by_uid($uids) {
        $birthlist = array();
        if(!empty($uids)) {
            $uids = explode(',', (string)$uids);
            $uids = dimplode(dintval($uids, true));
            list($s_month, $s_day) = explode('-', dgmdate(TIMESTAMP-3600*24*3, 'n-j'));
            list($n_month, $n_day) = explode('-', dgmdate(TIMESTAMP, 'n-j'));
            list($e_month, $e_day) = explode('-', dgmdate(TIMESTAMP+3600*24*7, 'n-j'));
            if($e_month == $s_month) {
                $wheresql = "sf.birthmonth='$s_month' AND sf.birthday>='$s_day' AND sf.birthday<='$e_day'";
            } else {
                $wheresql = "(sf.birthmonth='$s_month' AND sf.birthday>='$s_day') OR (sf.birthmonth='$e_month' AND sf.birthday<='$e_day' AND sf.birthday>'0')";
            }
            $data=array();
            foreach(DB::fetch_all("select sf.*,u.username,u.email from %t sf LEFT JOIN %t u USING(uid) ON sf.uid=u.uid where sf.uid IN (%n) and $wheresql",array($this->_table,'user',$uids)) as $value){
                $data[$value['uid']][$value['fileid']]=$value['value'];
                $data[$value['uid']]['username']=$value['username'];
            }
            foreach($data as $value){
                $value['istoday'] = 0;
                if($value['birthmonth'] == $n_month && $value['birthday'] == $n_day) {
                    $value['istoday'] = 1;
                }
                $key = sprintf("%02d", $value['birthmonth']).sprintf("%02d", $value['birthday']);
                $birthlist[$key][] = $value;
                ksort($birthlist);
            }

        }
        return $birthlist;
    }
}
