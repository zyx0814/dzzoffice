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

class table_document_reversion extends dzz_table
{
	public function __construct() {

		$this->_table = 'document_reversion';
		$this->_pk    = 'revid';

		parent::__construct();
	}
	public function fetch_by_revid($revid){
		global $_G;
		if(!$data=self::fetch($revid)) return false;
		$attach=C::t('attachment')->fetch($data['aid']);
		return array_merge($attach,$data);
	}
	public function reversion($did,$version,$uid,$username){ //使用此版本
		$newest=DB::fetch_first("select * from %t where did=%d order by version DESC limit 1",array($this->_table,$did));
		if($version==$newest['version']) return false;//已经是最新版了
		if($oversion=DB::fetch_first("select * from %t where did=%d and version=%d",array($this->_table,$did,$version))){
			unset($oversion['revid']);
			$oversion['version']=$newest['version']+1;
			$oversion['uid']=$uid;
			$oversion['did']=$did;
			$oversion['username']=$username;
			$oversion['dateline']=TIMESTAMP;
			if($oversion['revid']=parent::insert($oversion,1)){
				//插入事件
				$event=array('did'=>$did,
							 'action'=>'reversion',
							 'uid'=>$uid,
							 'username'=>$username,
							 'dateline'=>TIMESTAMP,
							 
							 );
				C::t('document_event')->insert($event);
				
				//更新附件的copys
				$addids=array();
				if($oversion['attachs']) $addids=explode(',',$oversion['attachs']);
				$addids[]=$oversion['aid'];
				C::t('attachment')->addcopy_by_aid($addids);
				//更新主文档表
				$setarr=array('version'=>$oversion['version'],
							  'uid'=>$oversion['uid'],
							  'username'=>$oversion['username'],
							  'aid'=>$oversion['aid']
							  );
				C::t('document')->update($did,$setarr);
			}
			return $oversion['version'];
		}
		return false;
	}
	public function insert_by_parent($arr){
		return parent::insert($arr,1);
	}
	public function insert($arr,$new){
		//先获取最新版本,没有的话新插入
		$newest=array();
		if($newest=DB::fetch_first("select * from %t where did=%d order by version DESC limit 1",array($this->_table,$arr['did']))){
			if($new){
				$arr['version']=$newest['version']+1;
				$arr['dateline']=TIMESTAMP;
				$attachs=array();
				if($arr['attachs']) {
					$attachs=$arr['attachs'];
					$arr['attachs']=implode(',',$attachs);
				}else{
					$arr['attachs']='';
				}
				if($arr['revid']=parent::insert($arr,1)){
					$attachs[]=$arr['aid'];
					C::t('attachment')->addcopy_by_aid($attachs);
					//插入事件
					$event=array('did'=>$arr['did'],
								 'action'=>'edit',
								 'uid'=>$arr['uid'],
								 'username'=>$arr['username'],
								 'dateline'=>TIMESTAMP
								 );
					C::t('document_event')->insert($event);
				}
			}else{
				$oldattachs=$newest['attachs']?explode(',',$newest['attachs']):array();
				$oldattachs[]=$newest['aid'];
				
				$attachs=array();
				if($arr['attachs']) {
					$attachs=$arr['attachs'];
					$arr['attachs']=implode(',',$arr['attachs']);
				}else{
					$arr['attachs']='';
				}
				$attachs[]=$arr['aid'];
				if(parent::update($newest['revid'],$arr)){
					$arr['version']=$newest['version'];
					$arr['revid']=$newest['revid'];
					$delaids=array_diff($oldattachs,$attachs);
					C::t('attachment')->addcopy_by_aid($delaids,-1);
					$insertaids=array_diff($attachs,$oldattachs);
					C::t('attachment')->addcopy_by_aid($insertaids);
				}
			}
		}else{
			$arr['version']=1;
			$arr['dateline']=TIMESTAMP;
			$attachs=array();
			if($arr['attachs']) {
				$attachs=$arr['attachs'];
				$arr['attachs']=implode(',',$arr['attachs']);
			}else{
					$arr['attachs']='';
				}
			if($arr['revid']=parent::insert($arr,1)){
				$attachs[]=$arr['aid'];
				C::t('attachment')->addcopy_by_aid($attachs);
				//插入事件
				$event=array('did'=>$arr['did'],
							 'action'=>'create',
							 'uid'=>$arr['uid'],
							 'username'=>$arr['username'],
							 'dateline'=>TIMESTAMP
							 );
				C::t('document_event')->insert($event);
			}
		}
		if($arr['revid']) return $arr;
		else return false;
	}
	public function delete_by_version($did,$version){
		$vers=self::fetch_all_by_did($did);
		self::delete($vers[$version]['revid']);
		unset($vers[$version]);
		$vers1=array();
		$vers=array_values(array_reverse($vers));	
		foreach($vers as $key=> $value){
			$value['version']=$key+1;
			parent::update($value['revid'],array('version'=>$value['version']));
		}
		//更新主文档表
		if($value['version']>0){
			$setarr=array('version'=>$value['version'],
						  'uid'=>$value['uid'],
						  'username'=>$value['username'],
						  'aid'=>$value['aid']
						  );
			C::t('document')->update($did,$setarr);
			return $value['version'];
		}else{
			return false;
		}
	}
    public function delete($revid){
		$data=parent::fetch($revid);
		$attachs=array();
		if($data['attachs']) $attachs=explode(',',$data['attachs']);
		$attachs[]=$data['aid'];
		foreach($attachs as $aid){
			C::t('attachment')->delete_by_aid($aid);
		}
	  	return  parent::delete($revid);
	}
	 public function delete_by_did($dids){
		if(!is_array($dids)) $dids=array($dids);
		$attachs=array();
		foreach(DB::fetch_all("select revid,aid,attachs from %t where did IN (%n) ",array($this->_table,$dids)) as $value){
		   if($value['attachs']) $attachs=array_merge($attachs,explode(',',$value['attachs']));
			$attachs[]=$value['aid'];
		    $revids[]=$value['revid'];
	   }
	   foreach($attachs as $aid){
			C::t('attachment')->delete_by_aid($aid);
		}
	   return parent::delete($revids);
	}
	public function fetch_all_by_did($did){
		$data=array();
		foreach(DB::fetch_all("select * from %t where did= %d order by version DESC",array($this->_table,$did)) as $value){
			$attach=C::t('attachment')->fetch($value['aid']);
			$data[$value['version']]=array_merge($value,$attach);
		}
		return $data;
	}
}

?>
