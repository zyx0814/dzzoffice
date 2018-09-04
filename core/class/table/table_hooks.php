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

class table_hooks extends dzz_table
{
	public function __construct() {
		$this->_table = 'hooks';
		$this->_pk    = 'id';
		$this->_pre_cache_key = 'hooks_';
		$this->_cache_ttl =0;
		parent::__construct();
	}
	
	public function update_by_appid($appid,$setarr){
		if(empty($appid)) return false;
		$appid=(array)$appid;
		$ids=array();
		foreach(DB::fetch_all("select id from %t where app_market_id IN(%n)",array('hooks',$appid)) as $value){
			$ids[]=$value['id'];
		}
		if($ret=parent::update($ids,$setarr)){
			self::clear_cache_tags();
		}
		return $ret;
	}
	public function delete_by_appid($appid){
		if(empty($appid)) return false;
		$appid=(array)$appid;
		$ids=array();
		foreach(DB::fetch_all("select id from %t where app_market_id IN(%n)",array('hooks',$appid)) as $value){
			$ids[]=$value['id'];
		}
		if($ret=parent::delete($ids)){
			self::clear_cache_tags();
		}
		return $ret;
	}
	public function insert_by_appid($appid,$hooks,$attributes=array()){
		if(!$appid) return false;
		$ret=0;
		foreach($hooks as $name =>$addons){
			$priority=0;
			$description="";
			if( $attributes ){//xml导入时附带其他属性，如优先级，描述等信息
				if(isset($attributes[$name]["_attributes"]) ) {
					$priority = isset($attributes[$name]["_attributes"]["priority"])?$attributes[$name]["_attributes"]["priority"]:$priority;
					$description = isset($attributes[$name]["_attributes"]["description"])?$attributes[$name]["_attributes"]["description"]:$description;
				}
			}
			
			if($hid=DB::result_first("select id from %t where name=%s and addons=%s",array($this->_table,$name,$addons))){
				if(parent::update($hid,array('app_market_id'=>$appid,'priority'=>$priority,'description'=>$description,'status'=>0))){
					$ret+=1;
				}
			}else{
				$data=array(
					'app_market_id'=>$appid,
					'name'=>$name,
					'priority'=>$priority,
					'description'=>$description,
					'type'=>'1',
					'addons'=>$addons,
					'status'=>0
				);
				if(parent::insert($data,1,1)){
					$ret+=1;
				}
			}
		}
		if($ret) self::clear_cache_tags();
		return $ret;
	}
	public function clear_cache_tags(){
		@unlink(DZZ_ROOT.'./data/cache/tags.php');
	}
}
?>
