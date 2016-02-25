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

class table_document extends dzz_table
{
	public function __construct() {

		$this->_table = 'document';
		$this->_pk    = 'did';
	   /* $this->_pre_cache_key = 'document_';
		$this->_cache_ttl = 0;*/

		parent::__construct();
	}
	
	public function copy_by_did($did,$area='',$areaid=0,$fid=0){ //复制文档
	   global $_G;
		if(!$data=self::fetch($did)) return false;
		unset($data['did']);
		$data['version']=1;
		$data['uid']=$_G['uid'];
		$data['username']=$_G['username'];
		$data['dateline']=TIMESTAMP;
		if($area) $data['area']=$area;
		if($areaid) $data['areaid']=$areaid;
		if($fid) $data['fid']=$fid;
		
		if($data['did']=parent::insert($data,1)){
			$newest=array();
			$i=0;
			foreach(DB::fetch_all("select * from %t where did=%d order by version",array('document_reversion',$did)) as $value){
				$attachs=array();
				
				unset($value['revid']);
				$value['did']=$data['did'];
				$value['dateline']=TIMESTAMP;
				$value['uid']=$_G['uid'];
				$value['username']=$_G['username'];
				$value['version']-=$i;
				if($value['attachs']) $attachs=explode(',',$value['attachs']);
				$attachs[]=$value['aid'];
				if(C::t('document_reversion')->insert_by_parent($value)){
					C::t('attachment')->addcopy_by_aid($attachs);
					$newest=$value;
				}else{
					$i++;
				}
			}
			if($newest){
				parent::update($data['did'],array('version'=>$newest['version'],'aid'=>$newest['aid']));
				return $data['did'];
			}else{
				parent::delete($data['did']);
				return false;
			}
		}
		return false;
	}
	public function insert($arr,$attachs=array(),$area='',$areaid=0,$new=0){ //插入
		if(!$arr['did']){//首次插入
		    $setarr=$arr;
			$setarr['version']=1;
			$setarr['area']=$area;
			$setarr['areaid']=$areaid;
			$setarr['dateline']=TIMESTAMP;
			$arr['did']=parent::insert($setarr,1);
		}
		
		$arr['attachs']=$attachs;
		
		//插入版本库
		$verarr=array('did'=>$arr['did'],
					  'aid'=>$arr['aid'],
					  'uid'=>$arr['uid'],
					  'username'=>$arr['username'],
					  'attachs'=>$attachs
					  );
		if($re=C::t('document_reversion')->insert($verarr,$new)){
			$setarr=array('version'=>$re['version'],
						  'uid'=>$arr['uid'],
						  'username'=>$arr['username'],
						  'aid'=>$arr['aid']
						  );
			parent::update($re['did'],$setarr);
		}
		return $arr['did'];
	}
	public function fetch_by_did($did){
		if(!$data=self::fetch($did)) return false;
		$attach=C::t('attachment')->fetch($data['aid']);
		return array_merge($attach,$data);
	}
    public function delete_by_did($did,$force=false){
		if(!$data=self::fetch($did)) return false;
		if($force || $data['isdelete']){
			//删除版本
			  C::t('document_reversion')->delete_by_did($did);
			//删除评论
			  C::t('comment')->delete_by_id_idtype($did,'document');
			 return parent::delete($did);
		}else{
			return parent::update($did,array('isdelete'=>TIMESTAMP));
		}
	}
	//获取最新的10条附件。
	public function fetch_all_by_areaid($areaid,$area='project',$limit='10'){
		global $_G;
		$data=array();
		foreach(DB::fetch_all("select * from %t where areaid=%d and area=%s limit $limit",array($this->_table,$areaid,$area)) as $value){
			$attach=C::t('attachment')->fetch($value['aid']);
			$data[$value['qid']]=array_merge($value,$attach);
		}
		return $data;
	}
}

?>
