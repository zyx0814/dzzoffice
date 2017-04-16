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

class table_source_link extends dzz_table
{
	public function __construct() {

		$this->_table = 'source_link';
		$this->_pk    = 'lid';
		$this->_pre_cache_key = 'source_link_';
		$this->_cache_ttl = 0;
		parent::__construct();
	}
	public function delete_by_lid($lid){
		$lid=intval($lid);
		$link=self::fetch($lid);
		
		//删除统计
		C::t('count')->delete_by_type($lid,'link');
		
		if($link['cid']){
			$copys=DB::result_first("select copys from ".DB::table('cai_link')." where cid='{$link[cid]}'");
			if($copys<=1){
				DB::delete('cai_link',"cid='{$link[cid]}'");
			}else{
				DB::update('cai_link',array('copys'=>$copys-1),"cid='{$link[cid]}'");
			}
		}
		if($link['did']){
			C::t('icon')->update_copys_by_did($link['did'],-1);
		}
		return self::delete($lid);
	}
	public function fetch_by_lid($lid,$havecount=false){ //返回一条数据同时加载图标数据
		global $_G;
		$lid = intval($lid);
		$link =  array();
		$link=self::fetch($lid);
		$link['ext']='';
		$link['size']=0;
		if($havecount){
			$count=C::t('count')->fetch_by_type($lid,'link');
			$link['viewnum']=intval($count['viewnum']);
			$link['replynum']=intval($count['replynum']);
			$link['downnum']=intval($count['downnum']);
			$link['star']=intval($count['star']);
			$link['starnum']=intval($count['starnum']);
		}
		return $link;
	}	
	public function fetch_all_by_uid($uid,$limit = 0,$orderby='dateline',$order='DESC',$start=0){ //返回用户最新的网址,按时间倒序排列
	
		$ordersql = $orderby ? ' ORDER BY '.$orderby.' '.$order : '';
		$limitsql = $limit ? DB::limit($start, $limit) : '';
		$link=array();
		foreach(DB::fetch_all("SELECT * FROM %t  WHERE uid= %d $ordersql $limitsql", array($this->_table, $uid)) as $value){
			$link[$value['lid']]=$value;
		}
		return $link;
	}
}

?>
