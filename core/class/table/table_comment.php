<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class table_comment extends dzz_table
{
	public function __construct() {

		$this->_table = 'comment';
		$this->_pk    = 'cid';
		$this->_pre_cache_key = 'comment_';
		$this->_cache_ttl = 60*60;
		parent::__construct();
	}
	
    public function insert_by_cid($arr,$ats,$attach){
		if($arr['cid']=parent::insert($arr,1)){
			/*if($arr['rcid'] && $rdata=parent::fetch($arr['rcid']) && !in_array($rdata['authorid'],$ats)){
				$ats[]=$rdata['authorid'];
			}*/
			if($ats){
				C::t('comment_at')->insert_by_cid($arr['cid'],($ats));
			}
			if($attach) C::t('comment_attach')->insert_by_cid($arr['cid'],$attach);
			
			if($arr['module']){
				
				@include_once DZZ_ROOT.'./dzz/'.$arr['module'].'/class/table/table_'.$arr['idtype'].'.php';
			}else{
				@include_once DZZ_ROOT.'./core/class/table/table_'.$arr['idtype'].'.php';
			}
			if(class_exists('table_'.$arr['idtype']) && method_exists('table_'.$arr['idtype'],'callback_by_comment')){
				$arr['message']=dzzcode($arr['message']);
				C::t($arr['idtype'])->callback_by_comment($arr,'add',$ats);
			}
		}
		return $arr['cid'];
	}
	public function update_by_cid($cid,$message,$rcid,$attach){
		$ret=0;
		$ret+=parent::update($cid,array('message'=>$message,'rcid'=>$rcid,'edituid'=>getglobal('uid'),'edittime'=>TIMESTAMP));
		
		$ret+=C::t('comment_attach')->update_by_cid($cid,$attach);
		
		return $ret;
	}
	public function delete_by_cid($cid){
		if(!$data=parent::fetch($cid)) return false;
		$delcids=array($cid);
		foreach(DB::fetch_all("select cid from %t where pcid=%d ",array($this->_table,$cid)) as $value){
			$delcids[]=$value['cid'];
		}
		if($return=parent::delete($delcids)){
			 //删除@
	 	    C::t('comment_at')->delete_by_cid($delcids);
		    //删除附件
		    C::t('comment_attach')->delete_by_cid($delcids);
	   		if($data['module']){
				@include_once DZZ_ROOT.'dzz/'.$data['module'].'/class/table/table_'.$data['idtype'].'.php';
			}else{
				@include_once DZZ_ROOT.'core/class/table/table_'.$data['idtype'].'.php';
			}
			if(class_exists('table_'.$data['idtype']) && method_exists('table_'.$data['idtype'],'callback_by_comment')){
				C::t($data['idtype'])->callback_by_comment($data,'delete');
			}
			return $return;
		}else{
			return false;
		}
	}
	public function delete_by_id_idtype($ids,$idtype){
		$ids=(array)$ids;
		$dels=array();
		foreach(DB::fetch_all("select * from %t where id IN (%n) and idtype=%s",array($this->_table,$ids,$idtype)) as $value){
			$dels[]=$value['cid'];
		}
		if($return=parent::delete($dels)){
			 //删除@
	 	    C::t('comment_at')->delete_by_cid($dels);
		    //删除附件
		    C::t('comment_attach')->delete_by_cid($dels);
		}
		return parent::delete($dels);
	}
	public function fetch_all_by_idtype($id,$idtype,$limit,$iscount=false){
		
		$limitsql='';
		if($limit){
			$limit=explode('-',$limit);
			if(count($limit)>1){
				$limitsql.=" limit ".intval($limit[0]).",".intval($limit[1]);
			}else{
				$limitsql.=" limit ".intval($limit[0]);
			}
		}
		
		if($iscount) return DB::result_first("select COUNT(*) from %t where id=%s and idtype=%s and pcid=0",array($this->_table,$id,$idtype));
		$data=array();
		foreach(DB::fetch_all("select * from %t where id=%s and idtype=%s and pcid=0 order by dateline DESC $limitsql",array($this->_table,$id,$idtype)) as $value){
			$value['message']=dzzcode($value['message']);
			$value['dateline']=dgmdate($value['dateline'],'u');
			$value['replies']=DB::result_first("select COUNT(*) from  %t where pcid=%d",array($this->_table,$value['cid']));
			$value['replys']=self::fetch_all_by_pcid($value['cid'],5);
			$value['attachs']=C::t('comment_attach')->fetch_all_by_cid($value['cid']);
			$data[]=$value;
		}
		return $data;
	}
	public function fetch_all_by_pcid($pcid,$limit,$iscount=false){
		$limitsql='';
		if($limit){
			$limit=explode('-',$limit);
			if(count($limit)>1){
				$limitsql.=" limit ".intval($limit[0]).",".intval($limit[1]);
			}else{
				$limitsql.=" limit ".intval($limit[0]);
			}
		}
		if($iscount) return DB::result_first("select COUNT(*) from %t where pcid=%d ",array($this->_table,$pcid));
		$data=array();
		foreach(DB::fetch_all("select * from %t where pcid=%d order by dateline DESC $limitsql",array($this->_table,$pcid)) as $value){
			$value['message']=dzzcode($value['message']);
			$value['dateline']=dgmdate($value['dateline'],'u');
			$value['attachs']=C::t('comment_attach')->fetch_all_by_cid($value['cid']);
			if($value['rcid']){
				$value['rpost']=parent::fetch($value['rcid']);
			}
			
			$data[]=$value;
		}
		return $data;
	}
	
	public function copy_by_id_idtype($oid,$id,$idtype){
		$return=0;
		foreach(DB::fetch_all("select * from %t where id=%s and idtype=%s and pcid='0'",array($this->_table,$oid,$idtype)) as $value){
			$ocid=$value['cid'];
			unset($value['cid']);
			$value['id']=$id;
			if($value['cid']=parent::insert($value,1)){
				C::t('comment_at')->copy_by_cid($ocid,$value['cid']);
				C::t('comment_attach')->copy_by_cid($ocid,$value['cid']);
				$return+=1;
				//拷贝子评论
				foreach(DB::fetch_all("select * from %t where pcid=%d ",array($this->_table,$ocid)) as $value1){
					$ocid=$value1['cid'];
					unset($value1['cid']);
					$value1['pcid']=$value['cid'];
					$value1['id']=$id;
					$value1['rcid']=0;
					if(parent::insert($value1,1)){
						C::t('comment_at')->copy_by_cid($ocid,$value['cid']);
						C::t('comment_attach')->copy_by_cid($ocid,$value['cid']);
					}
				}
			}
		}
		return $return;
	}
	
}

?>
