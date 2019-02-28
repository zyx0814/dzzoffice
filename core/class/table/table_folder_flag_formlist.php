<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
class table_folder_flag_formlist extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'folder_flag_formlist';
        $this->_pk = 'flag';
        $this->_pre_cache_key = 'folder_flag_formlist_';
        $this->_cache_ttl = 60*60;
        parent::__construct();
    }
   public function insert_by_flag($setarr){
	   if(!$flag=$setarr['flag']) return false;
	   if(parent::fetch($flag)){
		   unset($setarr['flag']);
		   parent::update($flag,$setarr);
	   }else{
		   return parent::insert($setarr,1);
	   }
   }
	public function fetch_by_flag($flag,$system=0){
		if(!$data=parent::fetch($flag)){
			$data['formlist']='name,fsize,ftype,fdateline';
		}
		$flags=explode(',',$data['formlist']);
		$temp=array();
		foreach(C::t('form_setting')->fetch_all($flags) as $value){
			if($system){
				if($system==1){
					if($value['system']) $temp[$value['flag']]=$value;
				}elseif($system==2){
					if(!$value['system']) $temp[$value['flag']]=$value;
				}
			}else{
				$temp[$value['flag']]=$value;
			}
		}
		//排序
		foreach($flags as $v){
			$ret[$v]=$temp[$v];
		}
		return $ret;
	}
}