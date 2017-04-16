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

class table_vote_item extends dzz_table
{
	public function __construct() {

		$this->_table = 'vote_item';
		$this->_pk    = 'itemid';

		parent::__construct();
	}
	
	public function fetch_by_voteid($voteid,$type=0){
		$data=array();
		$sql='voteid=%d';
		$param=array($this->_table,$voteid);
		if($type){
			$sql.=" and type=%d";
		}
		foreach(DB::fetch_all("select * from %t where $sql order by disp",$param) as $value){
			if($value['type'] && $value['aid']){
				 $value['img']=(DZZSCRIPT?DZZSCRIPT:'index.php').'?mod=io&op=thumbnail&width=240&height=160&path='.dzzencode('attach::'.$value['aid']);
				  $value['url']=(DZZSCRIPT?DZZSCRIPT:'index.php').'?mod=io&op=thumbnail&width=240&height=160&original=1&path='.dzzencode('attach::'.$value['aid']);
			}
			$data['type_'.$value['type']][]=$value;
		}
		return $data;
	}
	public function delete_by_itemid($itemid){
		$data=parent::fetch($itemid);
		if($data['aid']) C::t('attachment')->delete_by_aid($data['aid']);
		return parent::delete($itemid);
	}
    public function delete_by_voteid($voteids){
		$voteids=(array)$voteids;
		$itemids=array();
		$aids=array();
		foreach(DB::fetch_all("select itemid,aid from %t where voteid IN(%n)",array($this->_table,$voteids)) as $value){
			if($value['aid']) $aids[]=$value['aid']; 
			$itemids[]=$value['itemid'];
		}
		if($ret=parent::delete($itemids)){
			foreach($aids as $aid){
				C::t('attachment')->delete_by_aid($aid);
			}
			C::t('vote_item_count')->delete_by_itemid($itemids);
		}
	   return $ret;
	}
	
	public function update_by_voteid($voteid,$item,$itemnew){
		if(!$vote=C::t('vote')->fetch($voteid)) return false;
		//删除已有的项目
		$sql='voteid=%d';
		$param=array($this->_table,$voteid);
		if($item && ($ids=array_keys($item))){
				$sql.=" and itemid NOT IN(%n)";
				$param[]=$ids;
		}
		$dels=array();
		foreach(DB::fetch_all("select itemid,aid from %t where $sql",$param) as $value){
			if($value['aid']) C::t('attachment')->delete_by_aid($value['aid']);
			$dels[]=$value['itemid'];
		}
		if(parent::delete($dels)){
			C::t('vote_item_count')->delete_by_itemid($dels);
		}
		
		//更新已有项目
		$addcopyaids=array();
		foreach($item as $key => $value){
			if(empty($value['content']) && !$value['aid']) self::delete_by_itemid($key);
			$value['content']=getstr($value['content']);
			parent::update($key,$value);
		}
		
		//添加新项目
		$disp=DB::result_first("select max(disp) from %t where voteid=%d",array($this->_table,$voteid));
		
		foreach($itemnew as $key =>$value){
			if(empty($value['content']) && !$value['aid']) continue;
			$disp++;
			$setarr=array('voteid'=>$voteid,
						  'content'=>getstr($value['content']),
						  'type'=>$value['aid']?2:1,
						  'aid'=>intval($value['aid']),
						  'disp'=>$disp,
						  'number'=>0
						  );
			if(parent::insert($setarr,1) && $setarr['aid']){
				C::t('attachment')->addcopy_by_aid($setarr['aid']);
			}
		}
		return true;
	}
	
	
	
	public function update_number_by_itemid($itemids,$uid){
		$itemids=(array)$itemids;
		
		if($ret=DB::query(" update %t SET number=number+1 where itemid IN (%n) ",array($this->_table,$itemids))){
			C::t('vote_item_count')->insert_by_itemid($itemids,$uid);
		}
		return $ret;
	}
}
?>
