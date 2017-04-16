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

class table_source_video extends dzz_table
{
	public function __construct() {

		$this->_table = 'source_video';
		$this->_pk    = 'vid';
		$this->_pre_cache_key = 'source_video_';
		$this->_cache_ttl = 0;
		parent::__construct();
	}
	public function fetch_by_vid($vid,$havecount=false){ //返回一条数据同时加载附件表数据
		$vid = intval($vid);
		$data=self::fetch($vid);
		$data['icon']=$data['icon']?$data['icon']:geticonfromext('','video');
		$data['ext']='';
		$data['size']=0;
		if($havecount){
			$count=C::t('count')->fetch_by_type($vid,'video');
			$data['viewnum']=intval($count['viewnum']);
			$data['replynum']=intval($count['replynum']);
			$data['downnum']=intval($count['downnum']);
			$data['star']=intval($count['star']);
			$data['starnum']=intval($count['starnum']);
		}
		return $data;
	}
	public function delete_by_vid($vid){ 
		$vid=intval($vid);
		$video=self::fetch($vid);
		
		//删除统计
		C::t('count')->delete_by_type($vid,'video');
		
		if($video['cid']){
			$copys=DB::result_first("select copys from ".DB::table('cai_video')." where cid='{$video[cid]}'");
			if($copys<=1){
				DB::delete('cai_video',"cid='{$video[cid]}'");
			}else{
				DB::update('cai_video',array('copys'=>$copys-1),"cid='{$link[cid]}'");
			}
		}
		return self::delete($vid);
	}
	public function fetch_all_by_uid($uid,$limit = 0,$orderby='dateline',$order='DESC',$start=0){ //返回用户最新的视频,按时间倒序排列
	
		$ordersql = $orderby ? ' ORDER BY '.$orderby.' '.$order : '';
		$limitsql = $limit ? DB::limit($start, $limit) : '';
		$data=array();
		foreach(DB::fetch_all("SELECT * FROM %t  WHERE uid= %d $ordersql $limitsql", array($this->_table, $uid)) as $value){
			$data[$value['vid']]=$value;
		}
		return $data;
	}
}

?>
