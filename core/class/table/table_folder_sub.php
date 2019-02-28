<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
class table_folder_sub extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'folder_sub';
        $this->_pk = 'subid';
        $this->_pre_cache_key = 'folder_sub_';
        $this->_cache_ttl = 60*60;
        parent::__construct();
    }
    //获取默认子目录信息
	public function fetch_all_by_flag($flag){
		return DB::fetch_all("select * from %t  where pflag=%s",array($this->_table,$flag));
	}
	 public function insert_by_subid($setarr){
		if(!$flag=$setarr['flag']) return false;
		if(self::check_flag($setarr['flag'])) return false;
		if(empty($setarr['fname'])) return false;
		return parent::insert($setarr,1);
		
   }
	public function update($subid,$setarr){
		if($ret=parent::update($subid,$setarr)){
			$flag=$setarr['flag'];
			$allow_exts=isset($setarr['allow_exts'])?$setarr['allow_exts']:null;
			 unset($setarr['pflag']);
			 unset($setarr['flag']);
			 unset($setarr['allow_exts']);
			  foreach(DB::fetch_all("select fid from %t where flag=%s",array('folder',$flag)) as $value){
					C::t('folder')->update($value['fid'],$setarr);
					 if(isset($allow_exts)){
						  $arr=array(
								'fid'=>$value['fid'],
								'skey'=>'allow_exts',
								'svalue'=>$allow_exts
							);
						  C::t('folder_attr')->insert($arr);
					 }
			  }
		}
	}
	//检查flag不能和主flag同名
	public function check_flag($flag){
		return DB::result_first("select COUNT(*) from %t where flag=%s",array('folder_flag',$flag));
	}
	
}