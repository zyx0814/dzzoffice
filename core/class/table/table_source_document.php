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

class table_source_document extends dzz_table
{
	public function __construct() {

		$this->_table = 'source_document';
		$this->_pk    = 'did';
		$this->_pre_cache_key = 'source_document_';
		$this->_cache_ttl = 0;
		parent::__construct();
	}
	public function delete_by_did($did){
		$document=self::fetch($did);
		if($document['aid'])	C::t('attachment')->delete_by_aid($document['aid']);
		
		//删除统计
		C::t('count')->delete_by_type($did,'document');
		
		
		return self::delete($did);
	}
	public function fetch_by_did($did,$havecount=true){ //返回一条数据同时加载附件表数据
		$did = intval($did);
		$document = $attachment = array();
		$document=self::fetch($did);
		$attachment=C::t('attachment')->fetch($document['aid']);
		$data=array_merge($attachment,$document);
		//$data['icon']=geticonfromext($data['filetype'],'document');
		//$data['url']=getAttachUrl($data);
		$data['ext']=$data['filetype'];
		$data['size']=$data['filesize'];
		if($havecount){
			$count=C::t('count')->fetch_by_type($did,'document');
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
		$document=array();
		foreach(DB::fetch_all("SELECT did FROM %t  WHERE uid= %d $ordersql $limitsql", array($this->_table, $uid)) as $value){
			$document[$value['did']]=self::fetch_by_did($value['did']);
		}
		return $document;
	}
}

?>
