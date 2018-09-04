<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
class table_resources_attr extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'resources_attr';
        $this->_pre_cache_key = 'resourcesattr_';
        $this->_cache_ttl = 60 * 60;
        parent::__construct();
    }

    public function fetch_by_rid($rid,$vid = 0){
        $cachekey = 'resourcesattrdata_'.$rid.$vid;
        if($returndata = $this->fetch_cache($cachekey)){
            return $returndata;
        }
        $returndata = array();
        foreach(DB::fetch_all("select * from %t where rid = %s and vid = %d",array($this->_table,$rid,$vid)) as $val ){
            $returndata[$val['skey']] = $val['sval'];
        }
        $this->store_cache($cachekey,$returndata);
        return $returndata;
    }
    public function insert_attr($rid,$vid,$attrs){
        $insertsql = "insert into ".DB::table('resources_attr') ."(rid,skey,sval,vid) values ";
        $i = 0;
        foreach($attrs as $k=>$v){
            $insertsql .= "('{$rid}','{$k}','{$v}','{$vid}'),";
            $i++;
        }
        $insertsql = substr($insertsql,0,strlen($insertsql) - 1);
        DB::query($insertsql);
        if(DB::result_first("select count(*) from %t where rid = %s and vid = %d",array($this->_table,$rid,$vid)) == $i){
            return true;
        }
        return false;
    }
    public function delete_by_rvid($rid,$vid){
        $cachekey = 'resourcesattrdata_'.$rid.$vid;
        if(DB::delete($this->_table,array('rid'=>$rid,'vid'=>$vid))){
            $this->clear_cache($cachekey);
            return true;
        }
        return false;
    }
    public function delete_by_rid($rid){
        if(!is_array($rid)) $rid = (array)$rid;
        $cachkeys = array();
        foreach(DB::fetch_all("select vid from %t where rid in(%n)",array($this->_table,$rid)) as $v){
            $cachkeys[] = 'resourcesattrdata_'.$rid.$v['vid'];
        }
        if(DB::delete($this->_table,'rid in('.dimplode($rid).')')){
            $this->clear_cache($cachkeys);
            return true;
        }
        return false;
    }

    public function update_by_skey($rid,$vid,$skeyarr){
        $setarr = array();
        $cachekey = 'resourcesattrdata_'.$rid.$vid;
        foreach($skeyarr as $k=>$v){
            DB::update($this->_table,array('sval'=>$v),array('rid'=>$rid,'vid'=>$vid,'skey'=>$k));
            $this->clear_cache($cachekey);
        }

    }
}