<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
class table_folder_attr extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'folder_attr';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'folder_attr_';
        $this->_cache_ttl = 60*60;
        //$this->noperm = (getglobal('appGreenChannel'))?getglobal('appGreenChannel'):false;
        parent::__construct();
    }
	public function delete_by_id($id){
		if(!$data=parent::fetch($id)) return false;
		if($ret=parent::delete($id)){
			if($data['skey']=='icon' && $data['svalue']>0){
				C::t('attachment')->delete_by_aid($data['svalue']);
			}
		}
		return $ret;
	}
	public function update($id,$setarr){
		if(!$data=parent::fetch($id)) return false;
		if($ret=parent::update($id,$setarr)){
			if($setarr['skey']=='icon'){
				if($data['svalue']) C::t('attachment')->delete_by_aid($data['svalue']);
				if($setarr['svalue'])  C::t('attachment')->addcopy_by_aid($setarr['svalue']);
			}
		}
		return $ret;
	}
	public function insert($setarr){
		if($id=DB::result_first("select id from %t where fid=%d and skey=%s",array($this->_table,$setarr['fid'],$setarr['skey']))){
			if($setarr['skey']=='icon'){
				$o=parent::fetch($id);
			}
			$ret=parent::update($id,$setarr);
		}else{
			$id=parent::insert($setarr,1);
		}
		//处理图标
		if($id && $setarr['skey']=='icon'){
			if($o['svalue']) C::t('attachment')->delete_by_aid($o['svalue']);
			if($setarr['svalue']) C::t('attachment')->addcopy_by_aid($setarr['svalue']);
		}
		return $id;
	}
    public function insert_data_by_fid($fid,$skeyarr){//插入文件夹设置值
        foreach($skeyarr as $key=>$value){
            $setarr=array(
                'fid'=>$fid,
                'skey'=>$key,
                'svalue'=>$value
            );
			self::insert($setarr);
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
			self::insert($setarr);
        }
        return true;
    }
    public function update_by_skey_fid($fid,$skey,$val){
		$setarr=array(
			'fid'=>$fid,
			'skey'=>$skey,
			'svalue'=>$val,
		);
		return self::insert($searr);
		
    }
    public function insert_by_skey_fid($fid,$skey,$val){
        $setarr = array(
            'fid'=>$fid,
            'skey'=>$skey,
            'svalue'=>$val
        );
       return self::insert($setarr);
    }
    public function fetch_by_skey_fid($fid,$skey){ //获取文件夹某项设置值
        return DB::result_first("select svalue from %t where fid=%d and skey=%s",array($this->_table,$fid,$skey));
    }
    public function delete_by_field_fid($fid,$skeys){ //删除文件夹某项设置值
        $skeys=(array)$skeys;
		$i=0;
		foreach(DB::fetch_all("select id from %t where fid=%d and skeys IN (%n)",array($this->_table,$fid,$skeys)) as $value){
			if(self::delete_by_id($value['id'])) $i++;
		}
        return $i;
    }
    public function delete_by_fid($fid){ //删除设置
        $fid=(array)$fid;
		$i=0;
        foreach(DB::fetch_all("select id from %t where fid=%d",array($this->_table,$fid)) as $value){
			if(self::delete_by_id($value['id'])) $i++;
		}
        return $i;
    }
    //获取当前文件夹所有附属信息
    public function fetch_all_folder_setting_by_fid($fid){
        $settings = array();
        foreach(DB::fetch_all("select * from %t where fid = %d",array($this->_table,$fid)) as $v){
            $settings[$v['skey']] = $v['svalue'];
        }
        return $settings;
    }
	 //获取当前文件夹所有附属信息
    public function fetch_all_by_fid($fid){
        $settings = array();
        foreach(DB::fetch_all("select * from %t where fid = %d",array($this->_table,$fid)) as $v){
			if($v['skey']=='icon'){
				$v['svalue']=C::t('attachment')->getThumbByAid($value['svalue']);
			}
            $settings[$v['skey']] = $v['svalue'];
        }
        return $settings;
    }
}