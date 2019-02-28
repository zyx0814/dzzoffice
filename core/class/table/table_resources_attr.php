<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
class table_resources_attr extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'resources_attr';
		$this->_pk = 'id';
        $this->_pre_cache_key = 'resources_attr_';
        $this->_cache_ttl = 60 * 60;
        parent::__construct();
    }
	public function delete_by_id($id){
		if(!$data=parent::fetch($id)) return false;
		if($ret=parent::delete($id)){
			if($data['skey']=='icon' && $data['sval']>0){
				C::t('attachment')->delete_by_aid($data['sval']);
			}
			$cachekey = 'resources_attr_data_'.$data['rid'].$data['vid'];
		    $this->clear_cache($cachekey);
		}
		return $ret;
	}
	public function update($id,$setarr){
		if(!$data=parent::fetch($id)) return false;
		if($ret=parent::update($id,$setarr)){
			if($setarr['skey']=='icon'){
				if($data['sval']) C::t('attachment')->delete_by_aid($data['sval']);
				if($setarr['sval'])  C::t('attachment')->addcopy_by_aid($setarr['sval']);
				 $cachekey = 'resources_attr_data_'.$data['rid'].$data['vid'];
				 $this->clear_cache($cachekey);
			}
		}
		return $ret;
	}
	public function insert($setarr){
		if($id=DB::result_first("select id from %t where rid=%s and skey=%s and vid=%d",array($this->_table,$setarr['rid'],$setarr['skey'],intval($setarr['vid'])))){
			if($setarr['skey']=='icon'){
				$o=parent::fetch($id);
			}
			$ret=self::update($id,$setarr);
		}else{
			if($id=parent::insert($setarr,1)){
				 $cachekey = 'resources_attr_data_'.$setarr['rid'].$setarr['vid'];
				 $this->clear_cache($cachekey);
			}
		}
		//处理图标
		if($id && $setarr['skey']=='icon'){
			if($o['sval']) C::t('attachment')->delete_by_aid($o['sval']);
			if($setarr['sval']) C::t('attachment')->addcopy_by_aid($setarr['sval']);
		}
		return $id;
	}
    public function fetch_by_rid($rid,$vid = 0){
        $cachekey = 'resources_attr_data_'.$rid.$vid;
        if($returndata = $this->fetch_cache($cachekey)){
            return $returndata;
        }
        $returndata = array();
        foreach(DB::fetch_all("select * from %t where rid = %s and vid = %d",array($this->_table,$rid,$vid)) as $val ){
			if($val['skey']=='icon'){
				$val['sval']=C::t('attachment')->getThumbByAid($val['sval'],0,0,1);
				$val['skey']='img';
			}
            $returndata[$val['skey']] = $val['sval'];
        }
        $this->store_cache($cachekey,$returndata);
        return $returndata;
    }
    public function insert_attr($rid,$vid=0,$attrs=array()){
        $i = 0;
        foreach($attrs as $k=>$v){
			$setarr=array('rid'=>$rid,'skey'=>$k,'vid'=>$vid,'sval'=>$v);
			if(self::insert($setarr)){
				$i++;
			}
        }
        return $i;
    }
    public function delete_by_rvid($rid,$vid){
       $i=0;
        foreach(DB::fetch_all("select id from %t where rid=%s and vid=%d",array($this->_table,$rid,$vid)) as $value){
            if(self::delete_by_id($value['id'])){
				$i++;
			}
        }
        return $i;
    }
    public function delete_by_rid($rid){
        if(!is_array($rid)) $rid = (array)$rid;
		$i=0;
        foreach(DB::fetch_all("select id from %t where rid IN(%n) ",array($this->_table,$rid)) as $value){
            if(self::delete_by_id($value['id'])){
				$i++;
			}
        }
        return $i;
    }

    public function update_by_skey($rid,$vid,$skeyarr){
		$i=0;
        foreach($skeyarr as $k=>$v){
           $setarr=array('rid'=>$rid,'skey'=>$k,'vid'=>$vid,'sval'=>$v);
			if(self::insert($setarr)){
				$i++;
			}
        }
        return $i;
    }
    public function update_vid_by_rvid($rid,$oldvid,$vid){
       $i=0;
	   foreach(DB::fetch_all("select id from %t where rid=%s and vid = %d",array($this->_table,$rid,$oldvid)) as $value){
			if(self::update($value['id'],array('vid'=>$vid))){
				$i++;
			}
	  }
		return $i;
    }
}