<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
class table_resources_permgroup extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'resources_permgroup';
        $this->_pk = 'id';

        parent::__construct();
    }
    public function insert($setarr){
        if($setarr['default'] == 1){//删除原有默认值
            if($did = DB::result_first("select `id` from %t where `default` = %d",array($this->_table,1))){
                parent::update($did,array('default'=>0));
            }
        }
        if($insert = parent::insert($setarr,1)){
            return $insert;
        }
        return false;
    }
    public function update_by_id($id,$setarr){
        if($setarr['default'] == 1){//删除原有默认值
            if($did = DB::result_first("select id from %t where `default` = %d",array($this->_table,1))){
                parent::update($did,array('default'=>0));
            }
        }
        return parent::update($id,$setarr);
    }
    public function fetch_by_name($pername){
        return DB::result_first("select count(*) from %t where pername = %s",array($this->_table,$pername));
    }
    public function fetch_all($off = false){
        $params= array($this->_table);
        $wheresql = '';
        if($off){
            $wheresql = "where off != %d";
            $params[] = 1;
        }
        return DB::fetch_all("select * from %t $wheresql",$params);
    }
    public function update_off_status($id,$off){
        $id = intval($id);
        $off= intval($off);
        if(parent::fetch($id)){
            if(parent::update($id,array('off'=>$off))){
                return array('success'=>true);
            }
        }
        return array('error'=>true);
    }
    public  function setdefault_by_id($id){
        $id = intval($id);
        if(!$id) return false;
        if($did = DB::fetch_first("select id from %t where `default` = %d",array($this->_table,1))){
            parent::update($did,array('default'=>0));
        }
        return  parent::update($id,array('default'=>1));
    }
    public function delete_by_id($id){
        $id = intval($id);
        if(!$id) return false;
        if(parent::delete($id)){
            return true;
        }
        return false;
    }
}