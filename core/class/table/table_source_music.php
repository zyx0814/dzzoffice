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

class table_source_music extends dzz_table
{
	public function __construct() {

		$this->_table = 'source_music';
		$this->_pk    = 'mid';
		$this->_pre_cache_key = 'source_music_';
		$this->_cache_ttl = 0;
		parent::__construct();
	}
	public function fetch_by_mid($mid,$havecount=false){ //返回一条数据同时加载附件表数据
		$mid = intval($mid);
		$data=self::fetch($mid);
		$data['icon']=$data['icon']?$data['icon']:geticonfromext('','music');
		$data['ext']='';
		$data['size']=0;
		if($havecount){
			$count=C::t('count')->fetch_by_type($mid,'music');
			$data['viewnum']=intval($count['viewnum']);
			$data['replynum']=intval($count['replynum']);
			$data['downnum']=intval($count['downnum']);
			$data['star']=intval($count['star']);
			$data['starnum']=intval($count['starnum']);
		}
		return $data;
	}
	public function delete_by_mid($mid){ 
		$mid=intval($mid);
		$music=self::fetch($mid);
		
		//删除统计
		C::t('count')->delete_by_type($mid,'music');
		
		if($music['cid']){
			$copys=DB::result_first("select copys from ".DB::table('cai_music')." where cid='{$link[cid]}'");
			if($copys<=1){
				DB::delete('cai_music',"cid='{$link[cid]}'");
			}else{
				DB::update('cai_music',array('copys'=>$copys-1),"cid='{$link[cid]}'");
			}
		}
		return self::delete($mid);
	}
	public function fetch_all_by_uid($uid,$limit = 0,$orderby='dateline',$order='DESC',$start=0){ //返回用户最新的网址,按时间倒序排列
	
		$ordersql = $orderby ? ' ORDER BY '.$orderby.' '.$order : '';
		$limitsql = $limit ? DB::limit($start, $limit) : '';
		$data=array();
		foreach(DB::fetch_all("SELECT * FROM %t  WHERE uid= %d $ordersql $limitsql", array($this->_table, $uid)) as $value){
			$data[$value['mid']]=$value;
		}
		return $data;
	}
}

?>
