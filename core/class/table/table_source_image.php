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

class table_source_image extends dzz_table
{
	public function __construct() {

		$this->_table = 'source_image';
		$this->_pk    = 'picid';
		$this->_pre_cache_key = 'source_image_';
		$this->_cache_ttl = 0;
		parent::__construct();
	}
	public function delete_by_picid($picid){ 
	 	$picid=intval($picid);
		$image=self::fetch($picid);
		//删除附件
		if($image['aid']) C::t('attachment')->delete_by_aid($image['aid']);
		
		//删除统计
		C::t('count')->delete_by_type($picid,'image');
		
		if($image['cid']){
			C::t('cai_image')->delete_by_cid($image['cid']);
		}
		return self::delete($picid);
	}
	public function fetch_by_picid($picid,$havecount=false){ //返回一条数据同时加载附件表数据
		global $_G;
		$picid = intval($picid);
		$image = $attachment = array();
		$image=self::fetch($picid);
		if($image['aid']) $attachment=C::t('attachment')->fetch($image['aid']);
		$data=array_merge($attachment,$image);
		$data['title']=$data['filename'];
		//$data['url']=getAttachUrl($data,true);
		//if($data['thumb']) $data['icon']=getimgthumbname($_G['setting']['attachurl'].$data['attachment']);
		//else $data['icon']=$data['url'];
		/*if($data['thumb']>1){
			$data['url']=getimgthumbname($data['url'].'.1440x900');
		}*/
		$data['ext']=$data['filetype'];
		$data['size']=$data['filesize'];
		if($havecount){
			$count=C::t('count')->fetch_by_type($picid,'image');
			$data['viewnum']=intval($count['viewnum']);
			$data['replynum']=intval($count['replynum']);
			$data['downnum']=intval($count['downnum']);
			$data['star']=intval($count['star']);
			$data['starnum']=intval($count['starnum']);
		}
		return $data;
	}
	
	public function fetch_all_by_uid($uid,$limit = 0,$orderby='dateline',$order='DESC',$start=0){ //返回用户最新的图片,按时间倒序排列
	
		$ordersql = $orderby ? ' ORDER BY '.$orderby.' '.$order : '';
		$limitsql = $limit ? DB::limit($start, $limit) : '';
		$image=array();
		foreach(DB::fetch_all("SELECT picid FROM %t  WHERE uid= %d $ordersql $limitsql", array($this->_table, $uid)) as $value){
			$image[$value['picid']]=self::fetch_by_picid($value['picid']);
		}
		return $image;
	}
}

?>
