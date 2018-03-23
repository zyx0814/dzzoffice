<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
class table_folder_attr extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'folder_attr';
        $this->_pk = 'fid';
        $this->_pre_cache_key = 'folder_attr_';
        $this->_cache_ttl = 60*60;
        //$this->noperm = (getglobal('appGreenChannel'))?getglobal('appGreenChannel'):false;
        parent::__construct();
    }
    public function insert_data_by_fid($fid,$skeyarr){//插入文件夹设置值
        foreach($skeyarr as $key=>$value){
            $setarr=array(
                'fid'=>$fid,
                'skey'=>$key,
                'svalue'=>$value
            );
            DB::insert($this->_table,$setarr,0,1);
        }
        return true;
    }
    public function update_by_fid($fid,$skeyarr){//更新文件设置
        foreach($skeyarr as $key=>$value){
            $setarr=array(
                'fid'=>$fid,
                'skey'=>$key,
                'svalue'=>$value,
            );
            DB::update($this->_table,$setarr,array('fid'=>$fid));
        }
        return true;
    }
    public function update_by_skey_fid($fid,$skey,$val){
        DB::update($this->_table,array('svalue'=>$val),array('fid'=>$fid,'skey'=>$skey));
        return true;
    }
    public function insert_by_skey_fid($fid,$skey,$val){
        $setarr = array(
            'fid'=>$fid,
            'skey'=>$skey,
            'svalue'=>$val
        );
        DB::insert($this->_table,$setarr);
        return true;
    }
    public function fetch_by_skey_fid($fid,$skey){ //获取文件夹某项设置值
        return DB::result_first("select svalue from %t where fid=%d and skey=%s",array($this->_table,$fid,$skey));
    }
    public function delete_by_field_fid($fid,$skeys){ //删除文件夹某项设置值
        $skeys=(array)$skeys;
        return DB::delete($this->_table,"skey IN (".dimplode($skeys).") and fid = ".$fid);
    }
    public function delete_by_fid($fid){ //删除设置
        $fids=(array)$fid;
        return DB::delete($this->_table,"fid IN (".dimplode($fids).")");
    }
    //获取当前文件夹所有附属信息
    public function fetch_all_folder_setting_by_fid($fid){
        $settings = array();
        foreach(DB::fetch_all("select * from %t where fid = %d",array($this->_table,$fid)) as $v){
            $settings[$v['skey']] = $v['svalue'];
        }
        return $settings;
    }
}