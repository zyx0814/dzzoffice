<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
class table_resources_attr extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'resources_attr';
        parent::__construct();
    }

    public function fetch_by_rid($rid,$vid = 0){
        $returndata = array();
        foreach(DB::fetch_all("select * from %t where rid = %s and vid = %d",array($this->_table,$rid,$vid)) as $val ){
            $returndata[$val['skey']] = $val['sval'];
        }
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
        return DB::delete($this->_table,array('rid'=>$rid,'vid'=>$vid));
    }
    public function delete_by_rid($rid){
        if(!is_array($rid)) $rid = (array)$rid;
        if(DB::delete($this->_table,'rid in('.dimplode($rid).')')){
            return true;
        }
        return false;
    }

    public function update_by_skey($rid,$vid,$skeyarr){
        $setarr = array();
        foreach($skeyarr as $k=>$v){
            DB::update($this->_table,array('sval'=>$v),array('rid'=>$rid,'vid'=>$vid,'skey'=>$k));
        }

    }
}