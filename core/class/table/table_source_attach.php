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

class table_source_attach extends dzz_table
{
	public function __construct() {

		$this->_table = 'source_attach';
		$this->_pk    = 'qid';
		$this->_pre_cache_key = 'source_attach_';
		$this->_cache_ttl = 0;
		parent::__construct();
	}
	public function delete_by_qid($qid){
		$attach=self::fetch($qid);
		if($attach['aid'])	C::t('attachment')->delete_by_aid($attach['aid']);
		
		//删除统计
		C::t('count')->delete_by_type($qid,'attach');
		
		return self::delete($qid);
	}
	public function fetch_by_qid($qid,$havecount=true){ //返回一条数据同时加载附件表数据
		$qid = intval($qid);
		$attach = $attachment = array();
		$attach=self::fetch($qid);
		$attachment=C::t('attachment')->fetch($attach['aid']);
		$data=array_merge($attachment,$attach);
		//$data['icon']=geticonfromext($data['filetype'],'attach');
		$data['title']=$data['filename'];
		//$data['url']=getAttachUrl($data);
		$data['ext']=$data['filetype'];
		$data['size']=$data['filesize'];
		if($havecount){
			$count=C::t('count')->fetch_by_type($qid,'attach');
			$data['viewnum']=intval($count['viewnum']);
			$data['replynum']=intval($count['replynum']);
			$data['downnum']=intval($count['downnum']);
			$data['star']=intval($count['star']);
			$data['starnum']=intval($count['starnum']);
		}
		return $data;
	}
	
	public function fetch_all_by_uid($uid,$limit = 0,$orderby='dateline',$order='DESC',$start=0){ //返回用户最新的附件,按时间倒序排列
	
		$ordersql = $orderby ? ' ORDER BY '.$orderby.' '.$order : '';
		$limitsql = $limit ? DB::limit($start, $limit) : '';
		$sql=' uid= '.$uid;
		$attach=array();
		foreach(DB::fetch_all("SELECT qid FROM %t  WHERE uid= %d $ordersql $limitsql", array($this->_table, $uid)) as $value){
			$attach[$value['qid']]=self::fetch_by_qid($value['qid']);
		}
		return $attach;
	}
	
}

?>
