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

class table_vote extends dzz_table
{
	public function __construct() {

		$this->_table = 'vote';
		$this->_pk    = 'voteid';

		parent::__construct();
	}
	
	public function fetch_by_voteid($voteid){
		if(!$data=self::fetch($voteid)) return false;
		$data['items']=C::t('vote_item')->fetch_by_voteid($voteid);
		return $data;
	}
	public function fetch_by_id_idtype($id,$idtype){
		$voteid=DB::result_first("select voteid from %t where id=%d and idtype=%s",array($this->_table,$id,$idtype));
		return self::fetch_by_voteid($voteid);
	}
	public function insert_by_voteid($arr,$itemnew){
		if($voteid=parent::insert($arr,1)){
		  C::t('vote_item')->update_by_voteid($voteid,array(),$itemnew);
		}
		return $voteid;
	}
	public function update_by_voteid($voteid,$arr,$item,$itemnew){
		C::t('vote_item')->update_by_voteid($voteid,$item,$itemnew);
		return parent::update($voteid,$arr);
	}
	
    public function delete_by_voteid($voteids){
		$ret=0;
		$voteids=(array)$voteids;
		if($ret=parent::delete($voteids)){
			C::t('vote_item')->delete_by_voteid($voteids);
		}
		
	   return $ret;
	}
	public function delete_by_id_idtype($ids,$idtype){
		$ids=(array)$ids;
		foreach(DB::fetch_all("select voteid from %t where id IN(%n) and idtype=%s",array('vote',$ids,$idtype)) as $value){
			$voteids[]=$value['voteid'];
		}
		return self::delete_by_voteid($voteids);
	}

}
?>
