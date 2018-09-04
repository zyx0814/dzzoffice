<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}

class table_user_setting extends dzz_table
{
    public function __construct()
    {
        $this->_table = 'user_setting';
        $this->_pk = 'id';
		$this->_pre_cache_key = 'user_setting_';
		$this->_cache_ttl = 60*60;
        parent::__construct();

    }

    public function insert($skeyarr,$uid = 0){//插入用户设置
        if(!$uid)$uid = getglobal('uid');
        $cachkeys=array();
        foreach($skeyarr as $key=>$value){
            $setarr=array('uid'=>$uid,
                'skey'=>$key,
                'svalue'=>$value
            );
            $cachkeys[] = $uid.'_'.$key;
            DB::insert($this->_table,$setarr,0,1);
        }
		//更新缓存
		$this->clear_cache($cachkeys);
		$this->clear_cache($uid);
        return true;
    }
    public function update($skeyarr,$uid = 0){//更新用户设置
        if(!$uid) $uid = getglobal('uid');
		$cachkeys=array();
        foreach($skeyarr as $key=>$value){
            $setarr=array('uid'=>$uid,
                'skey'=>$key,
                'svalue'=>$value,
            );
			$cachkeys[]=$uid.'_'.$key;
            DB::insert($this->_table,$setarr,0,1);
        }
		//更新缓存
		$this->clear_cache($cachkeys);
		$this->clear_cache($uid);
        return true;
    }
    public function update_by_skey($skey,$val,$uid = 0){
            if(!$uid)$uid = getglobal('uid');
            if(!DB::update($this->_table,array('svalue'=>$val),array('uid'=>$uid,'skey'=>$skey))){
				 $setarr=array('uid'=>$uid,
					'uid'=>$uid,
					'skey'=>$skey,
					'svalue'=>$val
				);
				 DB::insert($this->_table,$setarr,0,1);
			}
			//更新缓存
			$this->clear_cache($uid.'_'.$skey);
			$this->clear_cache($uid);
            return true;
    }
    public function insert_by_skey($skey,$val,$uid = 0){
        if(!$uid) $uid = getglobal('uid');
        $setarr = array(
            'uid'=>$uid,
            'skey'=>$skey,
            'svalue'=>$val
        );
        parent::insert($setarr,0,1);
		//更新缓存
		$this->clear_cache($uid.'_'.$skey);
		$this->clear_cache($uid);
        return true;
    }
    public function fetch_by_skey($skey,$uid= 0){ //获取用户某项设置值
		static $vals=array();
        if(!$uid) $uid = getglobal('uid');
		$cachekey=$uid.'_'.$skey;//增加缓存
		if($ret=$this->fetch_cache($cachekey)){
			return $ret;
		}else{
			$val=DB::result_first("select svalue from %t where uid=%d and skey=%s",array($this->_table,$uid,$skey));
			$this->store_cache($cachekey,$val);
			return $val;
		}
		
    }
    public function delete_by_field($skeys,$uid=0){ //删除用户某项设置值
        if(!$uid)$uid = getglobal('uid');
        $skeys=(array)$skeys;
		$cachekeys=array();
		foreach($skeys as $skey){
			$cachekeys[]=$uid.'_'.$skey;
		}
        if($ret= DB::delete($this->_table,"skey IN (".dimplode($skeys).") and uid=".$uid)){
			$this->clear_cache($cachekeys);
			$this->clear_cache($uid);
			return $ret;
		}
		return false;
    }
    public function delete_by_uid($uids){ //删除设置
        $uids=(array)$uids;
		$cachekeys=array();
	
		foreach(DB::fetch_all("select skey,uid from %t where uid IN (%n)",array($this->_table,$uids)) as $value){
			$cachekeys[]=$value['uid'].'_'.$value['skey'];
		}
        if($ret= DB::delete($this->_table,"uid IN (".dimplode($uids).")")){
			$this->clear_cache($cachekeys);
			$this->clear_cache($uids);
			return $ret;
		}
		return false;	
			
    }
    //获取当前用户所有设置项
    public function fetch_all_user_setting($uid = 0){
        if(!$uid) $uid = getglobal('uid');
        $settings = array();
		$cachekey='settings_'.$uid;
		if($settings = $this->fetch_cache($cachekey)){
			return $settings;
		}else{
			foreach(DB::fetch_all("select * from %t where uid = %d",array($this->_table,$uid)) as $v){
				$settings[$v['skey']] = $v['svalue'];
			}
			$this->store_cache($cachekey,$settings);
			 return $settings;
		}
    }
}