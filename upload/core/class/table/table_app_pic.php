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

class table_app_pic extends dzz_table
{
	public function __construct() {

		$this->_table = 'app_pic';
		$this->_pk    = 'picid';
		/*$this->_pre_cache_key = 'app_pic_';
		$this->_cache_ttl = 0;*/

		parent::__construct();
	}
	public function delete_by_appid($appids){ //通过应用id删除应用图片
		if(!$appids) return;
		if(!is_array($appids)){
			$appids=array($appids);
		}
		$data=DB::fetch_all("SELECT * FROM %t WHERE appid IN(%n)",array($this->_table,$appids));
		
		foreach($data as $value){
			if($value['picid']) $this->delete_by_picid($value['picid']);
		}
		return true;
	}
	public function delete_by_picid($picid){ //删除应用图片
	  global $_G;
		if(!$data=$this->fetch($picid)){
			return false;	
		}
		if($data['aid']){
			C::t('attachment')->delete_by_aid($data['aid']);
			$this->delete($picid);
		}
		return true;
	}
	
	public function fetch($picid,$force=false){ //返回一条数据同时加载attachment表数据库数据
		$picid = intval($picid);
		$data = array();
		if($force || ($picid && $data = $this->fetch_cache($picid) === false)) {
			$data=DB::fetch_first("SELECT * FROM %t WHERE picid= %d ", array($this->_table,$picid));
			$attachment= array();
			if($data['aid']) $attachment=C::t('attachment')->fetch($data['aid']);
			$data=array_merge($attachment,$data);
			if(!empty($data)) $this->store_cache($picid, $data, $this->_cache_ttl);
		}
		return $data;
	}
	public function fetch_all_by_appid($appid,$iscount=false,$force=false){ //返回某个应用的全部图片
		$appid=intval($appid);
		$data=array();
		if($force || ($appid && ($data = $this->fetch_cache($appid,'app_pic_by_appid_')) === false)) {
			foreach(DB::fetch_all("select picid from %t where appid= %d",array($this->_table,$appid)) as $value){
				$data[$value['picid']]=$this->fetch($value['picid'],$force);
			}
			if(!empty($data)) $this->store_cache($appid, $data, 3600,'app_pic_by_appid_');
		}
		
		return $iscount?count($data):$data;
	}
}
?>
