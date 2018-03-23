<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}

class table_user_sdk extends dzz_table
{
    public function __construct()
    {
        $this->_table = 'user_sdk';
        $this->_pk = 'key';

        parent::__construct();
    }

    public function fetch_by_host($host)
    {
        return DB::fetch_first("select * from %t where `host` = %s",array($this->_table,$host));
    }
    public function fetch_salf_by_key($key,$token){

        return DB::fetch_first("select * from %t as a left join %t as b on a.key = b.keyid where a.key = %s and b.tokenid = %s",array($this->_table,'user_salf',$key,$token));
    }

    public function update_by_host($host,$setarr){

        return  DB::update($this->_table,$setarr,array('host'=>$host));
    }
}