<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
class table_folder_flag extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'folder_flag';
        $this->_pk = 'flag';
        $this->_pre_cache_key = 'folder_flag_';
        $this->_cache_ttl = 60*60;
        parent::__construct();
    }
   public function insert_by_flag($setarr){
	   if(!$flag=$setarr['flag']) return false;
	   
	   if(parent::fetch($flag)){
		   unset($setarr['flag']);
		   if($ret=parent::update($flag,$setarr)){
			   unset($setarr['system']);
			   foreach(DB::fetch_all("select fid from %t where flag=%s",array('folder',$flag)) as $value){
				    C::t('folder')->update($value['fid'],$setarr);
			   }
		   }
		   return $ret;
	   }else{
		   return parent::insert($setarr,1);
	   }
   }
}