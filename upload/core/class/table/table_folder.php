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

class table_folder extends dzz_table
{
	public function __construct() {

		$this->_table = 'folder';
		$this->_pk    = 'fid';
		$this->_pre_cache_key = 'folder_';
		$this->_cache_ttl = 60*60;
		parent::__construct();
	}
	public function fetch_all_by_fid($fids){
		$data=array();
		foreach(self::fetch_all($fids) as $fid => $value){
			if($arr=self::fetch_by_fid($fid)) $data[$fid]=$arr;
		}
		return $data;
	}
	public function fetch_by_fid($fid){ //返回一条数据同时加载附件表数据
	 global $_G;
	 
		$fid = intval($fid);
		if(!$data=self::fetch($fid)) return false;
		
		//$data['icon']=($data['ficon']?$data['ficon']:geticonfromext('','folder'));
		$data['title']=$data['fname'];
		
		if($data['flag']=='recycle'){
			$data['iconum']= DB::result_first("select COUNT(*) from ".DB::table('icos') ." where isdelete>0 and uid='{$_G['uid']}'");
		}elseif($data['uid']<1){
			$data['iconum']= DB::result_first("select COUNT(*) from ".DB::table('icos') ." where pfid='{$fid}' and uid='{$_G['uid']}' and isdelete<1");
		}else{
			$data['iconum']= DB::result_first("select COUNT(*) from ".DB::table('icos') ." where pfid='{$fid}' and isdelete<1");
		}
		$data['perm']=perm_check::getPerm($fid);
		$data['perm1']=perm_check::getPerm1($fid);
		//print_r($data);
		if($data['gid']>0){
			$data['ismoderator']=C::t('organization_admin')->ismoderator_by_uid_orgid($data['gid'],$_G['uid']);
			$permtitle=perm_binPerm::getGroupTitleByPower($data['perm1']);
			if(file_exists('dzz/images/default/system/folder-'.$permtitle['flag'].'.png')){
				$data['icon']='dzz/images/default/system/folder-'.$permtitle['flag'].'.png';
			}else{
				$data['icon']='dzz/images/default/system/folder-read.png';
			}
		}
		$data['path']=$data['fid'];
		$data['oid']=$data['fid'];
		$data['bz']='';
		return $data;
	}
	public function fetch_gid_by_fid($fid){
		if(!$folder=parent::fetch($fid)) return 0;
		if($folder['flag']=='organization' || $folder['pfid']<1) return $folder['gid'];
		elseif($folder['pfid']){
			return self::fetch_gid_by_fid($folder['pfid']);
		}
	}
	public function fetch_path_by_fid($fid,$fids=array()){
		if(!$folder=parent::fetch($fid)) return ;
		$fids[]=$folder['fid'];
		if($folder['pfid']){
			$fids=self::fetch_path_by_fid($folder['pfid'],$fids);
		}
		return $fids;
	}
	public function getPathByPfid($pfid,$arr=array(),$count=0){
		if($count>100) return $arr; //防止死循环；
		else $count++;
		if($value=DB::fetch_first("select pfid,fid,fname from ".DB::table('folder')." where fid='{$pfid}'")){
			$arr[$value['fid']]=$value['fname'];
			if($value['pfid']>0 && $value['pfid']!=$pfid) $arr=self::getPathByPfid($value['pfid'],$arr,$count);
		}
		//$arr=array_reverse($arr);
	
		return $arr;
		
	}
	public function delete_by_fid($fid,$force){ //删除目录
		$folder=self::fetch($fid);
		if(!defined('IN_ADMIN') && $folder['flag']!='folder'){
			return;
		}
		if(!$force && !perm_check::checkperm_container($fid,'delete')){
			return array('error'=>lang('no_privilege'));
		}
		foreach(DB::fetch_all("select icoid from %t where pfid=%d",array('icos',$fid)) as $value){
			C::t('icos')->delete_by_icoid($value['icoid'],$force);
		}
		
		//删除快捷方式
		C::t('source_shortcut')->delete_by_path('fid_'.$fid,true);
		C::t('source_shortcut')->delete_by_bz('folder_'.$fid,true);
		
		//删除下级目录
		foreach(DB::fetch_all("SELECT * FROM %t WHERE pfid=%d and isdelete<1",array($this->_table,$fid)) as $value){
			self::delete_by_fid($value['fid'],$force);
		}
		unset($folder);
		return self::delete($fid);
	}
	public function empty_by_fid($fid){ //清空目录
		global $_G;
		if(!$folder=self::fetch($fid)){
			return array('error'=>lang('folder_not_exist'));
		}
		if(!perm_check::checkperm_container($fid,'delete')){
			return array('error'=>lang('no_privilege'));
		}
		if($folder['flag']=='recycle'){
			foreach(DB::fetch_all("SELECT icoid FROM %t WHERE uid=%d and isdelete>0", array('icos',$_G['uid'])) as $value){
				C::t('icos')->delete_by_icoid($value['icoid'],true);
			}
			
		}else{
			foreach(DB::fetch_all("select icoid from %t where pfid=%d",array('icos',$fid)) as $value){
				C::t('icos')->delete_by_icoid($value['icoid'],true);
			}
		}
		unset($folder);
		return true;
	}
	public function fetch_all_default_by_uid($uid){
		return DB::fetch_all("SELECT * FROM %t WHERE `default`!= '' and uid=%d  ",array($this->_table,$uid),'fid');
	}
	public function fetch_typefid_by_uid($uid){
		$data=array();
		foreach(DB::fetch_all("SELECT * FROM %t WHERE `flag`!= 'folder' and  uid='{$uid}' and gid<1  ",array($this->_table),'fid') as $value){
			$data[$value['flag']]=$value['fid'];
		}
		return $data;
	}
	public function fetch_all_by_uid(){
		return DB::fetch_all("SELECT * FROM %t WHERE  uid='0'  ",array($this->_table),'fid');
	}
	public function fetch_all_by_pfid($pfid,$count){
		$wheresql='pfid = %d  and isdelete<1';
		if($folder=C::t('folder')->fetch_by_fid($pfid)){
			$where1=array();
			if($folder['gid']>0 ){
				$folder['perm']=perm_check::getPerm($folder['fid']);
				
				if($folder['perm']>0){
					if(perm_binPerm::havePower('read1',$folder['perm'])){
						$where1[]="uid='{$_G[uid]}'";
					}
					if(perm_binPerm::havePower('read2',$folder['perm'])){
						 $where1[]="uid!='{$_G[uid]}'";
					}
				}
				if($where1) $wheresql.=" and (".implode(' OR ' ,$where1).")";
				else $wheresql.=" and 0";
			}
		}
		if($count) return DB::result_first("SELECT COUNT(*) FROM %t WHERE $wheresql",array($this->_table,$pfid));
		else return DB::fetch_all("SELECT * FROM %t WHERE $wheresql",array($this->_table,$pfid),'fid');
	}
	
	//获取目录的信息(总大小，文件数和目录数);
	public function getContainsByFid($fid,$suborg=false){
		static $contains=array('size'=>0,'contain'=>array(0,0));
		if(!$folder=parent::fetch($fid)) return $contains;
		$fids[]=$fid;
		if($suborg && ($folder['flag']=='organization')){
			foreach(DB::fetch_all("select fid from %t where flag='organization' and pfid=%d ",array($this->_table,$fid)) as $value){
				$fids[]=$value['fid'];
			}
		}
		if(empty($folder['default']) && $folder['flag']!='organization'){//没有生成icos表的 单独查出来
			foreach(DB::fetch_all("select fid from %t where `default`='' and pfid=%d ",array($this->_table,$fid)) as $value){
				$fids[]=$value['fid'];
			}
		}
		foreach($fids as $fid){
			foreach(C::t('icos')->fetch_all_by_pfid($fid) as $value){
				$contains['size']+=$value['size'];
				if($value['type']=='folder'){
					$contains['contain'][1]+=1;
					self::getContainsByFid($value['oid']);
				}else{
					$contains['contain'][0]+=1;
				}
			}
		}
		return $contains;
	}
	//返回自己和上级目录fid数组；
	public function getTopFid($fid,$i=0,$arr=array()){
		$arr[]=$fid;
		if($i>100) return $arr; //防止死循环；
		else $i++;
		if($pfid=DB::result_first("select pfid from ".DB::table('folder')." where fid='{$fid}'")){
			if($pfid!=$fid) $arr=getTopFid($pfid,$i,$arr);
		}
		return $arr;
	}
	
}

?>
