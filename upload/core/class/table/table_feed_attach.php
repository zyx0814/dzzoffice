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

class table_feed_attach extends dzz_table
{
	public function __construct() {

		$this->_table = 'feed_attach';
		$this->_pk    = 'qid';
		$this->_pre_cache_key = 'feed_attach_';
		$this->_cache_ttl = 0;
		parent::__construct();
	}
	public function fetch_by_qid($qid){
		global $_G;
		if(!$data=self::fetch($qid)) return false;
		if($data['aid']>0){
			$attach=C::t('attachment')->fetch($data['aid']);
			if(in_array(strtolower($attach['filetype']),array('png','jpeg','jpg','gif'))){
				$attach['img']=C::t('attachment')->getThumbByAid($attach,120,80);
				$attach['isimage']=1;
				$attach['type']='image';
			}else{
				$attach['img']=geticonfromext($attach['filetype'],'');
				$attach['isimage']=0;
			}
			$attach['url']=C::t('attachment')->getThumbByAid($attach,120,80,1);
			$attach['filename']=$data['title'];
			$data = array_merge($attach,$data);
		}
		return $data;
	}
    public function delete_by_pid($pids){
		$ret=0;
		foreach(DB::fetch_all("select qid,aid,type,img from %t where pid IN (%n) ",array('feed_attach',$pids)) as $value){
		   if(parent::delete($value['qid'])){
			   $ret++;
				if($value['aid']>0)  C::t('attachment')->delete_by_aid($value['aid']);
				if($value['type']=='link'){
					  $imgarr=$value['img']?explode('icon',$value['img']):array();
					  if(isset($imgarr[1]) && ($did=DB::result_first("select did from %t where pic=%s",array('icon','icon'.$imgarr[1])))) C::t('icon')->update_copys_by_did($did,-1);
				 }
		   }
	   }
	  return $ret;
	}
	public function fetch_all_by_pid($pid){
		global $_G;
		$data=array();
		//$openext=C::t('app_open')->fetch_all_orderby_ext($_G['uid']);
		foreach(DB::fetch_all("select * from %t where pid= %d",array($this->_table,$pid)) as $value){
			if($value['aid']){
				$attach=C::t('attachment')->fetch($value['aid']);
				if(in_array(strtolower($attach['filetype']),array('png','jpeg','jpg','gif','bmp'))){
					$attach['img']=C::t('attachment')->getThumbByAid($attach);
					$attach['isimage']=1;
				}else{
					$attach['img']=geticonfromext($attach['filetype'],'');
					$attach['isimage']=0;
				}
				$attach['url']=C::t('attachment')->getThumbByAid($attach,120,80,1);
				$attach['preview']=1;
				$attach['filesize']=formatsize($attach['filesize']);
				$data[$value['qid']]=array_merge($value,$attach);
			}else{
				$value['preview']=1;
				$data[$value['qid']]=$value;
			}
		}
		return $data;
	}
	public function update_by_pid($pid,$tid,$attach){
		$qids=array();
		$ret=0;
		foreach(DB::fetch_all("select qid from %t where pid=%d",array($this->_table,$pid)) as $value){
			$qids[$value['qid']]=$value['qid'];
		}
		
		foreach($attach['title'] as $key=> $value){
			$qid=intval($attach['qid'][$key]);
			if($qid>0){
				unset($qids[$qid]);
			}else{
				
				$setarr=array('pid'=>$pid,
							  'tid'=>$tid,
							  'dateline'=>TIMESTAMP,
							  'aid'=>intval($attach['aid'][$key]),
							  'title'=>trim($value),
							  'type'=>trim($attach['type'][$key]),
							  'img'=>trim($attach['img'][$key]),
							  'url'=>trim($attach['url'][$key])
							  );
				if($ret+=parent::insert($setarr)){
					if($setarr['aid']) C::t('attachment')->addcopy_by_aid($setarr['aid']);
					if($setarr['type']=='link'){
						 $imgarr=$setarr['img']?explode('icon',$setarr['img']):array();
						 if(isset($imgarr[1]) && ($did=DB::result_first("select did from %t where pic=%s",array('icon','icon'.$imgarr[1])))) C::t('icon')->update_copys_by_did($did);
					}
				}
			}
		}
		if($qids) $ret+=self::delete_by_qid($qids);
		return $ret;
	}
	public function delete_by_qid($qids){
		$qids=(array)$qids;
		$ret=0;
		foreach(DB::fetch_all("select qid,aid,type,img from %t where qid IN(%n)",array($this->_table,$qids)) as $value){
		  if(parent::delete($value['qid'])){
			  $ret+=1;
			  if($value['aid']>0)  C::t('attachment')->delete_by_aid($value['aid']);
			   if($value['type']=='link'){
				   $imgarr=$value['img']?explode('icon',$value['img']):array();
				   if(isset($imgarr[1]) && ($did=DB::result_first("select did from %t where pic=%s",array('icon','icon'.$imgarr[1])))) C::t('icon')->update_copys_by_did($did,-1);
			  }
		  }
	   }
	   return $ret;
	}
}
?>
