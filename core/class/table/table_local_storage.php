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

class table_local_storage extends dzz_table
{ 
	public function __construct() {

		$this->_table = 'local_storage';
		$this->_pk    = 'remoteid';
		/*$this->_pre_cache_key = 'local_storage_';
		$this->_cache_ttl = 300;*/
		parent::__construct();
	}
	
	public function fetch_by_remoteid($remoteid){
		$remoteid=intval($remoteid);
		if(!$data=self::fetch($remoteid)){
			return array();
		}
		if($connect=C::t('connect')->fetch($data['bz'])){
			$data=array_merge($connect,$data);
		}
		if($data['dname'] && $data['did']){
			if($pan=C::t($data['dname'])->fetch($data['did']))	$data=array_merge($pan,$data);
		}
		return $data;
	}
	public function getBzByRemoteid($remoteid){ //通过remoteid获取bz,默认返回dzz
		if(!($data=self::fetch_by_remoteid($remoteid))){
			return 'dzz';
		}
		if($data['type']=='pan') $bz=$data['bz'].':'.$data['id'].':'.$data['root'];
		elseif($data['type']=='storage') $bz=$data['bz'].':'.$data['id'].':'.$data['bucket'];
		elseif($data['type']=='ftp') $bz=$data['bz'].':'.$data['id'].':'.$data['root'];
		elseif($data['type']=='disk') $bz=$data['bz'].':'.$data['id'].':'.$data['root'];
		else $bz='dzz';
		return $bz;
	}
	public function fetch_all_orderby_disp(){
		$data=array();
		foreach(DB::fetch_all("SELECT s.*,c.available FROM %t s LEFT JOIN %t c ON c.bz=s.bz WHERE 1 ORDER BY s.disp ",array($this->_table,'connect')) as $value){
			$data[$value['remoteid']]=$value;
		}
		return $data;
	}	
	public function update_usesize_by_remoteid($remoteid,$ceof){
		if(!$remoteid) $remoteid=DB::result_first("select remoteid from %t where bz='dzz' limit 1",array($this->_table));
		$ceof=intval($ceof);
		try{
			if($ceof>0){
				 DB::query("update %t set usesize=usesize+%d where remoteid=%d",array($this->_table,$ceof,$remoteid));
			}else{
				 DB::query("update %t set usesize=usesize-%d where remoteid=%d",array($this->_table,abs($ceof),$remoteid));
			}
			$this->clear_cache($remoteid);
		}catch(Exception $e){}
		return true;
	}
	public function update_sizecount_by_remoteid($remoteid){
		if($arr=self::getQuota($remoteid)){
			 self::update($remoteid,$arr);
			 return $arr;
		}
		return false;
	}
	
	public function getQuota($remoteid){
		global $_G;
		$data=self::fetch_by_remoteid($remoteid);
		$return=array();
		if($data['type']=='local'){
			$return['usesize']=C::t('attachment')->getSizeByRemote($remoteid);
			$return['totalsize']=disk_free_space($_G['setting']['attachdir']);
		}elseif($data['type']=='pan'){
			$bz=$data['bz'].':'.$data['id'].':';
			$arr=IO::getQuota($bz);
			$return['usesize']=C::t('attachment')->getSizeByRemote($remoteid);
			if(is_numeric($arr['quota']) && is_numeric($arr['used'])) $return['totalsize']=($arr['quota'])-($arr['used']);
		}elseif($data['type']=='storage'){
			$return['usesize']=C::t('attachment')->getSizeByRemote($remoteid);
			$return['totalsize']=0;
		}elseif($data['type']=='ftp'){
			$bz=$data['bz'].':'.$data['id'].':';
			$return['usesize']=C::t('attachment')->getSizeByRemote($remoteid);
			$return['totalsize']=0;
		}elseif($data['type']=='disk'){
            $bz=$data['bz'].':'.$data['id'].':';
            $return['usesize']=C::t('attachment')->getSizeByRemote($remoteid);
            $return['totalsize']=disk_free_space($data['attachdir']);
        }else{
			$return['usesize']=C::t('attachment')->getSizeByRemote($remoteid);
			$return['totalsize']=0;
		}
		return $return;
	}
	public function delete_by_remoteid($remoteid){
		$data=self::fetch($remoteid);
		if($data['bz']=='dzz') return array('error'=>'内置，不能删除');
		if(C::t('attachment')->getSizeByRemote($remoteid)>0)  return array('error'=>'有文件未迁移，不能删除');
		C::t('local_router')->delete_by_remoteid($remoteid);
		if($data['dname'] && $data['did']) C::t($data['dname'])->delete_by_id($data['did']);//删除链接
		return self::delete($remoteid);
	}
	public function getRemoteId(){
		return DB::result_first("select s.remoteid from ".DB::table('local_storage')." s LEFT JOIN ".DB::table('connect')." c ON s.bz=c.bz where c.available>0 order by s.isdefault DESC, s.disp ASC"); 	
	}
	
}

?>
