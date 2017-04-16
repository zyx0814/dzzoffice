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

class table_icos extends dzz_table
{
	public function __construct() {

		$this->_table = 'icos';
		$this->_pk    = 'icoid';
		$this->_pre_cache_key = 'icos_';
		$this->_cache_ttl = 60*60;

		parent::__construct();
	}
	
	public function delete_by_appid($appid){ //通过应用appid删除图标
		$data=DB::fetch_all("SELECT icoid FROM %t WHERE oid = %d AND type='app'",array($this->table,$appid));
		foreach($data as $value){
			if($value['icoid']) self::delete_by_icoid($value['icoid'],true);
		}
		return true;
	}
	public function fetch_appids_by_uid($uid){
		$data=array();
		foreach(DB::fetch_all("select oid from %t where type='app' and uid=%d",array($this->_table,$uid)) as $value){
			$data[]=$value['oid'];
		}
		if($config=C::t('user_field')->fetch($uid)){
			if($config['applist']){
				foreach(explode(',',$config['applist']) as $appid){
					$data[]=$appid;
				}
			}
		}
		return $data;
	}
	public function fetch_by_idtype($id,$type){
		return DB::fetch_first("SELECT * FROM %t WHERE oid = %d AND type = %s ",array($this->_table,$id,$type));
	}
	public function fetch_by_icoid($icoid,$force_from_db=false){ //返回一条数据同时加载资源表数据
	 global $_G;
		$icoid = intval($icoid);
		$data = $icodata = $soucedata = array();
		//if($force_from_db || ($data = $this->fetch_cache('parse_'.$icoid)) === false) {
			if(!$icodata=self::fetch($icoid)) return array();
			
			if(!$sourcedata=self::getsourcedata($icodata['type'],$icodata['oid'])){
				return array();
			}
			if($icodata['type']=='pan' || $icodata['type']=='storage') $icodata['oid']=$sourcedata['fid'];
			
			$data=array_merge($sourcedata,$icodata);
			$data['size']=isset($sourcedata['filesize'])?$sourcedata['filesize']:0;
			if($data['type']=='image'){
					$data['img']=DZZSCRIPT.'?mod=io&op=thumbnail&size=small&path='.dzzencode($data['icoid']);
					$data['url']=DZZSCRIPT.'?mod=io&op=thumbnail&size=large&path='.dzzencode($data['icoid']);
			}elseif($data['type']=='attach' || $data['type']=='document'){
				$data['img']=geticonfromext($data['ext'],$data['type']);
				$data['url']=DZZSCRIPT.'?mod=io&op=getStream&path='.dzzencode($data['icoid']);
			}elseif($data['type']=='shortcut'){
				$data['img']=isset($data['tdata']['img'])?$data['tdata']['img']:geticonfromext($data['tdata']['ext'],$data['tdata']['type']);
				$data['ttype']=$data['tdata']['type'];
				$data['ext']=$data['tdata']['ext'];
			}elseif($data['type']=='dzzdoc'){	
				$data['url']=DZZSCRIPT.'?mod=document&icoid='.dzzencode($data['icoid']);
				$data['img']=isset($data['icon'])?$data['icon']:geticonfromext($data['ext'],$data['type']);
			}else{
				$data['img']=isset($data['icon'])?$data['icon']:geticonfromext($data['ext'],$data['type']);
			}
			if(empty($data['name'])) $data['name']=$data['title'];
			
			$data['url']=isset($data['url'])?replace_canshu($data['url']):'';
			$data['ftype']=getFileTypeName($data['type'],$data['ext']);
			$data['fdateline']=dgmdate($data['dateline']);
			$data['fsize']=formatsize($data['size']);
			$data['path']=$data['icoid'];
			$data['bz']='';
			if($data['remote']>1) $data['rbz']=io_remote::getBzByRemoteid($data['remote']);
			
			
			//增加安全相关的路径
			$data['dpath']=dzzencode($data['path']);
			$data['apath']=$data['aid']?dzzencode('attach::'.$data['aid']):$data['dpath'];
			
			//$data['like']=C::t('icos_like')->fetch_by_icoid_uid($icoid,$data['uid']);
			//获取sperm
			if(!$data['sperm']) $data['sperm']=perm_FileSPerm::typePower($data['type'],$data['ext']);
			//if(!empty($data)) $this->store_cache('parse_'.$icoid, $data);
		//}
		return $data;
	}
	public function fetch_parents_by_pfid($pfid,$ret=array()){
		$icoid=DB::result_first("select icoid from %t where type='folder' and oid=%d ",array($this->table,$pfid));
		if($data=self::fetch_by_icoid($icoid)){
			$ret[$pfid]=$data;
			if($data['pfid']>0){
				$ret=array_merge($ret,self::fetch_parents_by_pfid($data['pfid'],$ret));
			}
		}
		return $ret;
	}
	public function getsourcedata($type,$oid){
		global $_G;
		switch($type){
			case 'folder':
				return $sourcedata=C::t('folder')->fetch_by_fid($oid,false);
			case 'attach':
				return $sourcedata=C::t('source_attach')->fetch_by_qid($oid,false);
			case 'document':
				return $sourcedata=C::t('source_document')->fetch_by_did($oid,false);
			case 'image':
				return $sourcedata=C::t('source_image')->fetch_by_picid($oid,false);
			case 'link':
				return $sourcedata=C::t('source_link')->fetch_by_lid($oid,false);
			case 'video':
				return $sourcedata=C::t('source_video')->fetch_by_vid($oid,false);
			case 'music':
				return $sourcedata=C::t('source_music')->fetch_by_mid($oid,false);
			case 'topic':
				return $sourcedata=C::t('source_topic')->fetch_by_tid($oid,false);
			case 'app':
				return $sourcedata=C::t('app_market')->fetch_by_appid($oid,false);
			case 'shortcut':
				return $sourcedata=C::t('source_shortcut')->fetch_by_cutid($oid);
				
			case 'user':
			    $sourcedata = array();
				$user=C::t('user')->fetch($oid);
				$sourcedata['title']=$user['username'];
				$sourcedata['icon']=avatar($user['uid'],'middle',true);
				$sourcedata['ext']='';
				$sourcedata['size']=0;
				return $sourcedata;
			case 'pan':
				return $sourcedata=C::t('connect_pan')->fetch_by_id($oid);
			case 'storage':
				return $sourcedata=C::t('connect_storage')->fetch_by_id($oid);
			case 'dzzdoc':
				return $sourcedata=C::t('document')->fetch_by_did($oid);
			default:
				return array();
		}
		
	}
	public function delete_by_icoid($icoid,$force=false){ //删除图标
		global $_G;
		$icoid=intval($icoid);
		$data=self::fetch_by_icoid($icoid);
		if(!$force && !perm_check::checkperm('delete',$data)){ return array('error'=>lang('no_privilege'));}
		//删除sourcedata
		self::deletesourcedata($data,$force);
		
		//空间计算
		if($data['size']) SpaceSize(-$data['size'],$data['gid'],1,$data['uid']);
		
		if(self::delete($icoid) ){
			delete_icoid_from_container($icoid,$data['pfid']);
			//删除快捷方式
			C::t('source_shortcut')->delete_by_path('icoid_'.$icoid,true);
			return $data;
		}else{
			return false;
		}
	}
	public function deletesourcedata($ico,$force){
		$type=$ico['type'];
		$oid=$ico['oid'];
		switch($type){
			case 'folder':
				return C::t('folder')->delete_by_fid($oid,$force);
			case 'attach':
				return C::t('source_attach')->delete_by_qid($oid);
			case 'document':
				return C::t('source_document')->delete_by_did($oid);
			case 'image':
				return C::t('source_image')->delete_by_picid($oid);
			case 'link':
				return C::t('source_link')->delete_by_lid($oid);
			case 'video':
				return C::t('source_video')->delete_by_vid($oid);
			case 'music':
				return C::t('source_music')->delete_by_mid($oid);
			case 'shortcut':
				return C::t('source_shortcut')->delete_by_cutid($oid);
			case 'dzzdoc':
				return C::t('document')->delete_by_did($oid,true);
			case 'app':
				return true;
			case 'user':
				return true;
			case 'pan':
				return true;
			case 'storage':
				return true;
		}
	}
	public function fetch_all_isdelete($limit=0,$orderby='deldateline',$order='DESC',$start=0,$count=false){
		global $_G;
		if($count) return DB::result_first("SELECT COUNT(*) FROM %t WHERE uid='{$_G[uid]}' and isdelete>0 ",array($this->_table));
		$limitsql = $limit ? DB::limit($start, $limit) : '';
		$data=array();
		$ordersql='';
		if(is_array($orderby)){
			foreach($orderby as $key => $value){
				$orderby[$key]=$value.' '.$order;
			}
			$ordersql=' ORDER BY '.implode(',',$orderby);
		}elseif($orderby){
			 $ordersql=' ORDER BY '.$orderby.' '.$order;
		}
		foreach(DB::fetch_all("SELECT icoid FROM %t WHERE uid='{$_G[uid]}' and isdelete>0  $ordersql $limitsql", array($this->_table)) as $value){
			if($arr=self::fetch_by_icoid($value['icoid'])){
				$arr['dateline']=$arr['deldateline'];
				$data[$value['icoid']]=$arr;
			}
		}
		return $data;
	}
	public function fetch_all_by_pfid($pfid,$name='',$limit=0,$orderby='',$order='',$start=0,$count=false){
		global $_G;
		$limitsql = $limit ? DB::limit($start, $limit) : '';
		$data=array();
		$wheresql='';
		$where=array();
		$para=array($this->_table);
		$where[]=' isdelete<1 ';
		if($name){ 
			$where[]='name like %s';
			$para[]='%'.$name.'%';
		}
		if(is_array($pfid)){
			$arr=array();
			
			foreach($pfid as $fid){
				$temp=array('pfid = %d');
				$para[]=$fid;
				if($folder=C::t('folder')->fetch($fid)){
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
						if($where1) $temp[]="(".implode(' OR ' ,$where1).")";
						else $temp[]="0";
					}else{
						$temp[]=" uid='{$_G[uid]}'";
					}
				}
				$arr[]='('.implode(' and ',$temp).')';
				unset($temp);
			}
			if($arr)  $where[]='('.implode(' OR ',$arr).')';
		}elseif($pfid){
			 $temp=array('pfid= %d');
			 $para[]=$pfid;
			 if($folder=C::t('folder')->fetch($pfid)){
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
					if($where1) $temp[]="(".implode(' OR ' ,$where1).")";
					else $temp[]="0";
				}else{
					$temp[]=" uid='{$_G[uid]}'";
				}
			 }
			 $where[]='('.implode(' and ',$temp).')';
			 unset($temp);
		}
		if($where) $wheresql='WHERE '.implode(' AND ',$where);
		else return false;
		if($count) return DB::result_first("SELECT COUNT(*) FROM %t  $wheresql ", $para);
		$ordersql='';
		if(is_array($orderby)){
			foreach($orderby as $key=>$value){
				$orderby[$key]=$value.' '.$order;
			}
			$ordersql=' ORDER BY '.implode(',',$orderby);
		}elseif($orderby){
			 $ordersql=' ORDER BY '.$orderby.' '.$order;
		}
		
		foreach(DB::fetch_all("SELECT icoid FROM %t $wheresql $ordersql $limitsql", $para) as $value){
			if($arr=self::fetch_by_icoid($value['icoid']))	$data[$value['icoid']]=$arr;
		}
		return $data;
	}
	public function fetch_all_by_uid($uid,$limit=0,$start=0){
		$limitsql = $limit ? DB::limit($start, $limit) : '';
		$data=array();
		foreach(DB::fetch_all("SELECT icoid FROM %t  WHERE uid= %d  $limitsql", array($this->_table, $uid)) as $value){
			$data[$value['icoid']]=self::fetch_by_icoid($value['icoid']);
		}
		return $data;
	}
	public function fetch_all_by_condition($sql,$limit=0,$start=0){
		$limitsql = $limit ? DB::limit($start, $limit) : '';
		$data=array();
		foreach(DB::fetch_all("SELECT icoid FROM %t  WHERE $sql  $limitsql", array($this->_table)) as $value){
			$data[$value['icoid']]=self::fetch_by_icoid($value['icoid']);
		}
		return $data;
	}
	public function fetch_all_by_gid($gid,$limit=0,$start=0){
		$limitsql = $limit ? DB::limit($start, $limit) : '';
		$data=array();
		foreach(DB::fetch_all("SELECT icoid FROM %t WHERE gid= %d  $limitsql", array($this->_table, $gid)) as $value){
			$data[$value['icoid']]=self::fetch_by_icoid($value['icoid']);
		}
		return $data;
	}
	public function update_by_name($icoid,$text){ //重命名
		$arr=array();
		$arr['text']=$text;
		if(!$icoarr=self::fetch($icoid)) {
			$arr['error']=lang('icoid_not_exist');
			return $arr;
		}
		if(!perm_check::checkperm('rename',$icoarr)){ 
			$arr['error']=lang('no_privilege');
			return $arr; 
		}
		switch($icoarr['type']){
			case 'folder':
				if(C::t('folder')->update($icoarr['oid'],array('fname'=>$text))){
					$arr['dataname']='fname';
				}
				break;
			case 'link':case 'video':case 'music':case 'image':case 'attach':case 'document':
				C::t('source_'.$icoarr['type'])->update($icoarr['oid'],array('title'=>$text));
				break;
			case 'pan':
				C::t('connect_'.$icoarr['type'])->update($icoarr['oid'],array('cloudname'=>$text));
				break;
			case 'storage':
				C::t('connect_'.$icoarr['type'])->update($icoarr['oid'],array('cloudname'=>$text));
				break;
			case 'shortcut':
				break;
		}
		if(C::t('icos')->update($icoid,array('name'=>$text))){
			$arr['msg']='success';
		}
		return $arr;
	}
}

?>
