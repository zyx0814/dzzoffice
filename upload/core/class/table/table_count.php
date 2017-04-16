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

class table_count extends dzz_table
{
	public function __construct() {

		$this->_table = 'count';
		$this->_pk    = 'jid';
		//$this->_pre_cache_key = 'count_';
		//$this->_cache_ttl = 0;
		parent::__construct();
	}
	public function delete_by_type($id,$type){
		return DB::delete($this->_table," id='{$id}' AND type='{$type}'");
	}
	public function fetch_by_type($id,$type){
		return DB::fetch_first("SELECT * FROM %t where type = %s AND id = %d ",array($this->_table,$type,$id));
	}
	public function update_viewnum_by_type($id,$type){
		if($count=self::fetch_by_type($id,$type)){
			return self::update($count['jid'],array('viewnum'=>$count['viewnum']+1));
		}else{
			return self::insert(array('viewnum'=>1,'id'=>intval($id),'type'=>$type,'updatetime'=>TIMESTAMP));
		}
	}
	public function update_replynum_by_type($id,$type,$coef=1){
		$coef=intval($coef);
		if($count=self::fetch_by_type($id,$type)){
			return self::update($count['jid'],array('replynum'=>(($count['replynum']+$coef)<0?0:($count['replynum']+$coef))));
		}else{
			if($coef<0) return false;
			return self::insert(array('replynum'=>$coef,'id'=>intval($id),'type'=>$type,'updatetime'=>TIMESTAMP));
		}
	}
	public function update_downnum_by_type($id,$type){
		if($count=self::fetch_by_type($id,$type)){
			return self::update($count['jid'],array('downnum'=>$count['downnum']+$coef));
		}else{
			return self::insert(array('downnum'=>1,'id'=>intval($id),'type'=>$type,'updatetime'=>TIMESTAMP));
		}
	}
	public function update_star_by_type($id,$type){
		$stars=array('star1'=>0,'star2'=>0,'star3'=>0,'star4'=>0,'star5'=>0,'allstar'=>0);
		$query=DB::query("SELECT * FROM ".DB::table('score')." where id='{$id}' and idtype='{$idtype}'");
		while($value=DB::fetch($query)){
			if($value['star']==1) {$stars['star1']+=1;$stars['allstar']+=1;}
			if($value['star']==2) {$stars['star2']+=1;$stars['allstar']+=1;}
			if($value['star']==3) {$stars['star3']+=1;$stars['allstar']+=1;}
			if($value['star']==4) {$stars['star4']+=1;$stars['allstar']+=1;}
			if($value['star']==5) {$stars['star5']+=1;$stars['allstar']+=1;}
		}
		$score=0;
		for($i=1;$i<6;$i++){
			$score+=$stars['star'.$i]/$stars['allstar']*$i*2;
		}
		$score=round($score*100)/100;
	
		if($count=self::fetch_by_type($id,$type)){
			return self::update($count['jid'],array('star'=>$score,'starnum'=>$stars['allstar']));
		}else{
			return self::insert(array('star'=>$score,'starnum'=>$stars['allstar'],'id'=>intval($id),'type'=>$type,'updatetime'=>TIMESTAMP));
		}
	}

}

?>
