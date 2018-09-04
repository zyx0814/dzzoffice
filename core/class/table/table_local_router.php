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
//所有用户应用
//uid=0 的表示为默认应用

class table_local_router extends dzz_table
{ 
	public function __construct() {

		$this->_table = 'local_router';
		$this->_pk    = 'routerid';
		//$this->_pre_cache_key = 'local_router_';
		//$this->_cache_ttl = 0;
		parent::__construct();
	}
	public function fetch_by_routerid($routerid){
		$data=self::fetch($routerid);
		$data['router']=unserialize($data['router']);
		$data['drouter']=self::getRouterDetail($data['router']);
		return $data;
	}
	public function fetch_all_orderby_priority($available=false){
		$data=array();
		$where='1';
		if($available){
			$where.=' and `available`>0';
		}
		foreach(DB::fetch_all("SELECT * FROM %t WHERE $where ORDER BY priority DESC ",array($this->_table)) as $value){
			$value['router']=unserialize($value['router']);
			$value['drouter']=self::getRouterDetail($value['router']);
			$data[$value['routerid']]=$value;
		}
		return $data;
	}	
	public function getRouterDetail($router){
		$html='';
		foreach($router as $type  =>$value){
		
			switch($type){
				case 'exts':
					if($value) $html.="文件后缀：".implode(',',$value);
					else $html.="文件后缀：不限制";
					break;
				case 'size':
				    $sizearr=array();
					if(is_numeric($value['lt'])) $sizearr[]="大于".formatsize($value['lt']*1024*1024);
					if(is_numeric($value['gt'])) $sizearr[]="小于".formatsize($value['gt']*1024*1024);
					if($sizearr) $html.='<br>文件大小：'.implode(' and ',$sizearr);
					else $html.='<br>文件大小：不限制';
					break;
			}
		}
		
		return $html;
	}
	public function insert($setarr){
		$setarr['router']=serialize($setarr['router']);
		return parent::insert($setarr,1);
	}
	public function update($routerid,$setarr){
		if($setarr['router']) $setarr['router']=serialize($setarr['router']);
		return parent::update($routerid,$setarr);
	}
	public function delete_by_remoteid($remoteid){
		return DB::delete($this->_table,"remoteid='{$remoteid}'");
	}
	//根据路由规则筛选出存储位置
	public function getRemoteId($data){
		$remoteid=0;
		$guize=self::fetch_all_orderby_priority(true);
		foreach($guize as  $value){
			//没有此存储位置
			if(!$ldata = C::t('local_storage')->fetch($value['remoteid'])){
				continue;
			}else{
				$available = DB::result_first("select available from %t where bz = %s", array('connect',$ldata['bz']));
				if($available <1) continue;
			}
			//云停用跳转
			if($available<1) continue;
			if($value['router']['exts']){
				if(!in_array(strtolower($data['filetype']),$value['router']['exts'])) continue;
			}
			if(is_numeric($value['router']['size']['lt']) && $data['filesize']<$value['router']['size']['lt']*1024*1024) continue;
			if(is_numeric($value['router']['size']['gt']) && $data['filesize']>$value['router']['size']['gt']*1024*1024) continue;
			return $value['remoteid'];
		}
		
		return $remoteid;
	}
}

?>
