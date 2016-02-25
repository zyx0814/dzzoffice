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

class table_source_shortcut extends dzz_table
{
	public function __construct() {

		$this->_table = 'source_shortcut';
		$this->_pk    = 'cutid';
		$this->_pre_cache_key = 'source_shortcut_';
		$this->_cache_ttl = 0;
		parent::__construct();
	}
	public function delete_by_cutid($cutid){ 
	 	$cutid=intval($cutid);
		return self::delete($cutid);
	}
	public function fetch_by_cutid($cutid){ //返回一条数据同时加载附件表数据
		$cutid = intval($cutid);
		if(!$cut=parent::fetch($cutid)){
			return array();
		}
		$data=array();
	//print_r($cut);
		if($cut['data']) $data=unserialize($cut['data']);
		else{
			 $data=self::getDataByPath($cut['path']);
			 self::update($cutid,array('data'=>serialize($data)));
		}
		//print_r($data);
		return array('tdata'=>$data);
	}
	public function getDataByPath($path){
		$data=array();
		$patharr=explode(':',$path);
		$bzarr=C::t('connect')->fetch_all_bz();
		if(in_array($patharr[0],$bzarr)){
			$bz=$patharr[0];
		}else{
			$bz='dzz';
		}
		if($bz=='dzz'){
			list($idtype,$id)=explode('_',str_replace('dzz:','',$path));
			if($idtype=='fid'){
				$data=C::t('folder')->fetch_by_fid($id);
				$data['name']=$data['title'];
				$data['oid']=$data['fid'];
				$data['bz']='';
				$data['path']=$data['fid'];
				$data['topfid']=array();
				$data['type']='folder';
				$data['folderarr']=IO::getFolderDatasByPath($id);
				foreach($data['folderarr'] as $value){
					$data['topfid'][]=$value['fid'];
				}
			}elseif($idtype=='icoid'){
				$data=C::t('icos')->fetch_by_icoid($id);
				if($data['type']=='folder'){
					$data['topfid']=array();
					$data['folderarr']=IO::getFolderDatasByPath($data['oid']);
					foreach($data['folderarr'] as $value){
						$data['topfid'][]=$value['fid'];
					}
				}
			}
		}else{
			$data=IO::getMeta($path);
			if($data['type']=='folder'){
				$data['topfid']=array();
				$data['folderarr']=IO::getFolderDatasByPath($data['path']);
				foreach($data['folderarr'] as $value){
					if(!empty($value['fid'])) $data['topfid'][]=$value['fid'];
				}
				$data['topfid']=array_reverse($data['topfid']);
			}
		}
		return $data;
	}
}

?>
