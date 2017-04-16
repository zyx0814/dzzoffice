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
class table_organization extends dzz_table
{
	private $_uids = array();
	
	public function __construct() {

		$this->_table = 'organization';
		$this->_pk    = 'orgid';
		//$this->_pre_cache_key = 'organization_';
		//$this->_cache_ttl = 0;
		
		parent::__construct();
	}
	public function fetch_all_by_forgid($forgid,$count=0){
		if($count) return DB::result_first("SELECT COUNT(*) FROM %t WHERE forgid= %d",array($this->_table,$forgid));
		return DB::fetch_all("SELECT * FROM %t WHERE forgid= %d ORDER BY disp",array($this->_table,$forgid),'orgid');
	}
	public function delete_by_orgid($orgid){
		if(!$org=parent::fetch($orgid)){
			return array('error'=>'删除错误！要删除的对象已经不存在');
		}
		
		if(self::fetch_all_by_forgid($org['orgid'],true) || ($org['fid'] && C::t('icos')->fetch_all_by_pfid($org['fid'],'',0,'','',0,true))){
			return array('error'=>'删除错误！请检查以下内容：<br><ul><li>此部门没有下级部门</li><li>此部门共享目录下没有文件或子目录</li></ul>');
		}
		if($org['fid']){
			C::t('folder')->delete_by_fid($org['fid'],true);
		}
		C::t('organization_user')->delete_by_orgid($orgid);
		if(parent::delete($orgid)){
			/*include_once libfile('function/cache');
			updatecache('organization');*/
			if($this->_wxbind){
				self::deleteDepartment($org['worgid']);
			}
			return $org;
		}else{
			return array('error'=>'删除错误');
		}
	}
	
	public function setFolderAvailableByOrgid($orgid,$available){
		if(!$org=parent::fetch($orgid)) return false;
		if($available>0 && $org['forgid']>0){
			$toporgid=self::getTopOrgid($orgid);
			$top=parent::fetch($toporgid);
			if($top['available']<1) return false;
		}
		if(parent::update($orgid,array('available'=>$available))){
			self::setFolderByOrgid($orgid);
			//include_once libfile('function/cache');
			//updatecache('organization');
			return true;
		}
		return false;
	}
	
	public function setIndeskByOrgid($orgid,$indesk){
		if(!$org=parent::fetch($orgid)) return false;
		if($indesk>0){
			if($org['available']<1) return false;
		}
		if(parent::update($orgid,array('indesk'=>$indesk))){
			/*include_once libfile('function/cache');
			updatecache('organization');*/
			return true;
		}
		return false;
	}
	
	public function setFolderByOrgid($orgid){
		if(!$org=parent::fetch($orgid)) return false;
		if($org['forgid']==0){
			$pfid=0;
		}else{
			$toporgid=self::getTopOrgid($orgid);
			$pfid=DB::result_first("select fid from ".DB::table($this->_table)." where orgid='{$toporgid}'");
		}
		
		if($fid=DB::result_first("select fid from ".DB::table('folder')." where gid='{$orgid}' and uid='0'  and flag='organization'")){
			C::t('folder')->update($fid,array('fname'=>$org['orgname'],'display'=>$org['disp'],'pfid'=>$pfid,'perm'=>perm_binPerm::getGroupPower('read'),'innav'=>$org['available']));
			self::update($orgid,array('fid'=>$fid));
		/*}elseif($org['available']){*/
		}else{
			$folder=array('fname'=>$org['orgname'],
						  'pfid'=>$pfid,
						  'display'=>$org['disp'],
						  'flag'=>'organization',
						  'gid'=>$org['orgid'],
						  'innav'=>$org['available'],
						  'perm'=>perm_binPerm::getGroupPower('read')
					  );
			$fid=C::t('folder')->insert($folder,true);
		}
		if($fid){
			self::update($org['orgid'],array('fid'=>$fid));
			return $fid;
		}
		return false;
	}
	
	public function setDispByOrgid($orgid,$disp,$forgid){
		if(!$org=parent::fetch($orgid)) return false;
		if($torg=DB::fetch_first("select disp,orgid from %t where forgid=%d and orgid!=%d order by disp limit %d,1",array($this->_table,$forgid,$orgid,$disp))){
			$disp=$torg['disp'];
			if(DB::query("update %t SET disp=disp+1 where disp>=%d and forgid=%d",array($this->_table,$disp,$forgid)) && $this->_wxbind){
				foreach(DB::fetch_all("select orgid from %t where disp>%d and forgid=%d",array($this->_table,$disp,$forgid)) as $value){
					self::wx_update($value['orgid']);
				}
			}
		}else{
			$disp=DB::result_first("select max(disp) from %t where forgid=%d",array($this->_table,$forgid))+1;
		}
		if($return=parent::update($orgid,array('disp'=>$disp,'forgid'=>$forgid))){
			if($org['forgid']!=$forgid){
				//重新设置所有下降机构的共享目录
				if($pathkey=self::setPathkeyByOrgid($orgid)){
					$like='^'.$pathkey;
					foreach(DB::fetch_all("select orgid from %t where pathkey REGEXP %s",array($this->_table,$like)) as $value){
						self::setFolderByOrgid($value['orgid']);
					}
				}
			}
			//include_once libfile('function/cache');
			//updatecache('organization');
			if($disp>10000){
				if(DB::query("update %t SET disp=disp-9000 where forgid=%d",array($this->_table,$forgid))){
					foreach(DB::fetch_all("select orgid from %t where forgid=%d",array($this->_table,$forgid)) as $value){
						self::wx_update($value['orgid']);
					}
				}
			}else{
				if($this->_wxbind) self::wx_update($orgid);
			}
			return $return;
		}else{
			return false;
		}
	}
	
	public function getDispByOrgid($borgid){
		$data=parent::fetch($borgid);
		$disp=$data['disp']+1;
		DB::query("update %t SET disp=disp+1 where disp>=%d and forgid=%d",array($this->_table,$disp,$data['forgid']));
		return $disp;
	}
	
	public function insert_by_orgid($setarr){
		if($setarr['orgid']=parent::insert($setarr,true)){
			//self::setFolderByOrgid($org['orgid']);
			//include_once libfile('function/cache');
			//updatecache('organization');
			
			if($this->_wxbind){//同步到微信端
				self::wx_update($setarr['orgid']);
			}
			self::setPathkeyByOrgid($setarr['orgid']);
			return $setarr['orgid'];
		}
		return false;
	}
	
	public function insert_by_forgid($setarr,$borgid){
		if($borgid){
			$setarr['disp']=self::getDispByOrgid($borgid);
		}
		if($setarr['orgid']=parent::insert($setarr,true)){
			//self::setFolderByOrgid($org['orgid']);
			//include_once libfile('function/cache');
			//updatecache('organization');
			
			if($this->_wxbind){//同步到微信端
				self::wx_update($setarr['orgid']);
			}
			self::setPathkeyByOrgid($setarr['orgid']);
			return $setarr;
		}
		
		return false;
	}
	
	public function update_by_orgid($orgid,$setarr){
		if(!$org=self::fetch($orgid)) return false;
		if(parent::update($orgid,$setarr)){
			$org=array_merge($org,$setarr);
			self::setFolderByOrgid($org['orgid']);
			//include_once libfile('function/cache');
			//updatecache('organization');
			self::setPathkeyByOrgid($orgid);
			if($this->_wxbind ){//同步到微信端
				self::wx_update($orgid);
			}
			return true;
		}
		return false;
	}
	
	public function getTopOrgid($orgid){
		include_once libfile('function/organization');
		$ids=getUpOrgidTree($orgid);
		$ids=array_reverse($ids);
		return $ids[0];
	}
	
	public function setPathkeyByOrgid($orgid,$force=0){ //设置此机构的pathkey的值，$force>0 重设此部门的pathkey
		@set_time_limit(0);
		if(!$org=parent::fetch($orgid)) return false;
		if($force || empty($org['pathkey'])){//没有pathkey,
			include_once libfile('function/organization');
			if($ids=array_reverse(getUpOrgidTree($org['orgid']))){
				$pathkey='_'.implode('_-_',$ids).'_';
				
				if( parent::update($org['orgid'],array('pathkey'=>$pathkey))) return $pathkey;
			}
			return false;
		}
		//设置所有子部门的pathkey；
		if($org['forgid'] && ($porg=parent::fetch($org['forgid']))){
			$npathkey=$porg['pathkey'].'-'.'_'.$orgid.'_';
		}else{
			$npathkey='_'.$orgid.'_';
		}
		if($org['pathkey']==$npathkey) return $npathkey; //没有改变；
		$like='^'.$org['pathkey'];
		if(DB::query("update %t set pathkey=REPLACE(pathkey,%s,%s) where pathkey REGEXP %s",array($this->_table,$org['pathkey'],$npathkey,$like))){
			return $npathkey;
		}
	}
	
	
	public function wx_update($orgid){
		global $_G;
		if(!$this->_wxbind) return;
		if(!$org=parent::fetch($orgid)) return false;
		$wx=new qyWechat(array('appid'=>$_G['setting']['CorpID'],'appsecret'=>$_G['setting']['CorpSecret'],'agentid'=>0));
		$wd=array();
		if($wxdepart=$wx->getDepartment()){
			foreach($wxdepart['department'] as $value){
				$wd[$value['id']]=$value;
			}
		}else{
			return false;
		}
		if($org['forgid']){
			 if(($forg=parent::fetch($org['forgid'])) && !$forg['worgid']){
				 if($worgid=self::wx_update($forg['orgid'])){
					$forg['worgid']=$worgid;
				 }else{
					return;
				 }
			 }
		}
		$parentid=($org['forgid']==0?1:$forg['worgid']);
		if($org['worgid'] && $wd[$org['worgid']] && $parentid==$wd[$org['worgid']]['parentid']){//更新机构信息
			$data=array("id"=>$org['worgid']);
			
			if($wd[$org['worgid']]['name']!=$org['orgname']) $data['name']=$org['orgname'];
			if($wd[$org['worgid']]['parentid']!=$parentid) $data['parentid']=$parentid;
			if($wd[$org['worgid']]['order']!=$org['order']) $data['order']=$org['order'];
			if($data) $data['id']=$org['worgid'];
			if($data){
				 if(!$wx->updateDepartment($data)){
					 $message='updateDepartment：errCode:'.$wx->errCode.';errMsg:'.$wx->errMsg;
						runlog('wxlog', $message);
						return false;
				 }
		    }
			return $org['worgid'];
			
		}else{
			$data=array(
				  "name" => $org['orgname'],   //部门名称
				  "parentid" =>$org['forgid']==0?1:$forg['worgid'],         //父部门id
				  "order" => $org['disp']+1,            //(非必须)在父部门中的次序。从1开始，数字越大排序越靠后
			);
			if($ret=$wx->createDepartment($data)){
				parent::update($orgid,array('worgid'=>$ret['id']));
				return $ret['id'];
			}else{
				if($wx->errCode=='60008'){//部门的worgid不正确导致的问题
					foreach($wd as $value){
						if($value['name']==$data['name'] && $value['parentid']=$data['parentid']){
							C::t('organization')->update($org['orgid'],array('worgid'=>$value['id']));
							return $value['id'];
						}
					}
				}
				$message='createDepartment：errCode:'.$wx->errCode.';errMsg:'.$wx->errMsg;
				runlog('wxlog', $message);
				return false;
			}
		}
		return false;
	}
	public function deleteDepartment($id){
		$wx=new qyWechat(array('appid'=>getglobal('setting/CorpID'),'appsecret'=>getglobal('setting/CorpSecret'),'agentid'=>0));
		if($wx->deleteDepartment($id)) return true;
		else{
			$message='deleteDepartment：errCode:'.$wx->errCode.';errMsg:'.$wx->errMsg;
			runlog('wxlog', $message);
		}
		return false;
	}
	public function getPathByOrgid($orgid){
		$ret=array();
		if($org=parent::fetch($orgid)){
			$ids=explode('-',str_replace('_','',$org['pathkey']));
			$arr=parent::fetch_all($ids);
			foreach($ids as $id){
				if($arr[$id]) $ret[]=$arr[$id]['orgname'];
			}
		}
		return $ret?implode('-',$ret):'';
	}
}

?>
