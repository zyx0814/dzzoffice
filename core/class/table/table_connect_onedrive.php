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

class table_connect_onedrive extends dzz_table
{ 
	public function __construct() {

		$this->_table = 'connect_onedrive';
		$this->_pk    = 'id';
		$this->_pre_cache_key = 'connect_onedrive_';
		$this->_cache_ttl = 300;
		parent::__construct();
	}
	public function fetch_by_id($id){
		
		$value=self::fetch($id);
		$cloud=DB::fetch_first("select * from ".DB::table('connect')." where bz='{$value['bz']}'");
		if(!$value['cloudname']) $value['cloudname']=$cloud['name'].':'.($value['cusername']?$value['cusername']:$cloud['cuid']);
		$data=array(
				'id'=>$value['id'],
				'fid'=>md5($cloud['bz'].':'.$value['id'].':'.$cloud['root']),
				'pfid'=>0,
				'fname'=>$value['cloudname'],
				'ficon'=>'dzz/images/default/system/'.$cloud['bz'].'.png',
				'bz'=>$cloud['bz'].':'.$value['id'].':',
				'path'=>$cloud['bz'].':'.$value['id'].':'.$cloud['root'],
				'type'=>'pan',
				'fsperm'=>perm_FolderSPerm::flagPower($cloud['bz']),
				'perm'=>perm_binPerm::getGroupPower('all'),
				'flag'=>$cloud['bz'],
				'iconview'=>1,
				'disp'=>'0',
				);
		
		return $data;
	}
	public function fetch_all_by_id($ids){
		$data=array();
		foreach($ids as $id){
			$value=self::fetch_by_id($id);
			$data[$value['fid']]=$value;
		}
		return $data;
	}	
	public function delete_by_id($id){	
		$return=array();
		$data=parent::fetch($id);
		if(parent::delete($id)){
			$return['msg']='success';
			//C::t('source_shortcut')->delete_by_bz($data['bz'].':'.$id.':',true);//删除快捷方式；
			//删除图片缓存文件
			$imgcache=getglobal('setting/attachdir').'./imgcache/'.$data['bz'].'/'.$id.'/';
			removedirectory($imgcache);
		}
		return $return;
	}
	public function delete_by_uid($uid){
		if(!$uid) return 0;
		foreach(DB::fetch_all("select id from %t where uid=%d",array($this->_table,$uid)) as $value){
			self::delete_by_id($value['id']);
		}
		return true;
	}
	public function delete_by_bz($bz){	
		foreach(DB::fetch_all("select id from %t where bz=%s",array($this->_table,$bz)) as $value){
			self::delete_by_id($value['id']);
		}
	}
}

?>
