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

class table_template extends dzz_table
{
	public function __construct() {

		$this->_table = 'template';
		$this->_pk    = 'tpid';

		parent::__construct();
	}

	public function fetch_all_by_type($type,$keyword='',$available=0,$limit=0,$iscount) {
		
		$limitsql='';
		if($limit){
			$limit=explode('-',$limit);
			if(count($limit)>1){
				$limitsql.=" limit ".intval($limit[0]).",".intval($limit[1]);
			}else{
				$limitsql.=" limit ".intval($limit[0]);
			}
		}
		$sql='';
		$param=array($this->_table, $type);
		if($available) $sql.=" and available>0";
		if($keyword){
			$sql.=' and tpname like %s';
			$param[]='%'.$keyword.'%';
		}
		if($iscount) return DB::result_first("SELECT COUNT(*) from %t WHERE type=%s $sql", $param);
		return DB::fetch_all("SELECT tpid,tpname,type,dateline,disp,available FROM %t WHERE type=%s $sql ORDER BY disp $limitsql", $param);
	}
	public function insert_by_tpid($setarr){
		$attachs=self::getAidsByMessage($setarr['body']);
		$setting['attachs']=$attachs?implode(',',$attachs):'';
		return parent::insert($setarr,1);
	}
	public function update_by_tpid($tpid,$setarr){
		if(!$data=parent::fetch($tpid)){
			return 0;
		}
		if(isset($setarr['body'])){ //含有body字段，处理附件
			$oaids=$data['attachs']?explode(',',$data['attachs']):array();
			$attachs=self::getAidsByMessage($setarr['body']);
			$aids=$attachs?implode(',',$attachs):array();
			if($oaids) C::t('attachment')->addcopy_by_aid($oaids,-1);
			if($aids)  C::t('attachment')->addcopy_by_aid($aids);
			$setting['attachs']=$attachs?implode(',',$attachs):'';
		}
		return parent::update($tpid,$setarr);
	}
	public function getAidsByMessage($message){
		$aids=array();
		if(preg_match_all("/path=\"attach::(\d+)\"/i",$message,$matches)){
			$aids=$matches[1];
		}
		if(preg_match_all("/path=\"".rawurlencode('attach::')."(\d+)\"/i",$message,$matches1)){
			$aids=array_merge($aids,$matches1[1]);
		}
		return array_unique($aids);
	}
	public function delete_by_tpid($tpids){
		$tpids=(array)$tpids;
		foreach(DB::fetch_all("select attachs from %t where tpid IN (%n)",array($this->_table,$tpids)) as $value){
			if($value['attachs']) C::t('attachment')->addcopy_by_aid(explode(',',$value['attachs']),-1);
		}
		return parent::delete($tpids);
	}
}

?>
