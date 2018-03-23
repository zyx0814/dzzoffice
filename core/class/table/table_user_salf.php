<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}

class table_user_salf extends dzz_table
{
    public function __construct()
    {
        $this->_table = 'user_salf';
        $this->_pk = 'uid';

        parent::__construct();

    }

    public function fetch_by_tokenid($tokenid){

        return DB::fetch_first("select * from %t where tokenid = %s",array($this->_table,$tokenid));
    }

    public function fetch_by_keyid($kid){

        return DB::fetch_first("select * from %t where keyid = %d",array($this->_table,$kid));
    }

    public function update_by_tokenid($tokenid,$setarr){

        return DB::update($this->_table,$setarr,"tokenid = $tokenid");
    }

    public  function update_by_host($host,$arr){

        if(DB::update($this->_table,$arr,"host = $host")){
            return true;

        }else{

            return false;

        }
    }
    public function fetch_sdk_by_tokenid($tokenid){

       return  DB::fetch_first("select * from %t as a left join %t as b on a.keyid = b.key where a.tokendid = %s",array($this->_table,'user_sdk',$tokenid));
    }
}