<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class table_organization extends dzz_table
{
    private $_uids = array();

    public function __construct()
    {

        $this->_table = 'organization';
        $this->_pk = 'orgid';
        $this->_pre_cache_key = 'organization_';
       // $this->_cache_ttl = 60*60;

        parent::__construct();
    }
	/*获取所有下级部门
	  $count: >0 仅返回数量
	  $type: -1:全部部门或群组；1：仅群组；0;仅机构或部门
	  $forgid: 上级orgid，当$forgid=0时，获取所有一级
	*/
    public function fetch_all_by_forgid($forgid, $count = 0, $type = 0)
    {
        if ($count) $sql = 'SELECT COUNT(*) FROM %t WHERE forgid= %d';
        else $sql = 'SELECT * FROM %t WHERE forgid= %d';
        $param = array($this->_table, $forgid);
        if ($type > -1) {
            $sql .= ' and `type`=%d';
            $param[] = $type;
        }
        if ($count) return DB::result_first($sql, $param);
        return DB::fetch_all($sql . '  ORDER BY disp', $param, 'orgid');
    }
	
	/*
	  获取用户参与的部门或群组信息
	  $uid:   参与人，为空时为当前用户
	  $type: -1:全部部门或群组；1：仅群组；0;仅机构或部门
	  return  array();
	*/
    public function fetch_all_by_uid($uid,$type=-1){
		if(empty($uid)) $uid=getglobal('uid');
		if(!$uid) return array();
		if($orgids=C::t('organization_user')->fetch_orgids_by_uid($uid,$type)){
			parent::fetch_all($orgids);
		}
		return array();
	}
	//插入数据
	public function insert($arr){
		if($orgid=parent::insert($arr)){
			if(intval($arr['aid'])){//如果有头像图片，增加copys
				C::t('attachment')->add_by_aid(intval($arr['aid']));
			}
		}
		return $orgid;
	}
	
    //查询机构群组信息
    public function fetch_all_orggroup($uid,$getmember = true)
    {
        global $_G;
        $groups = array();
        if($_G['adminid'] == 1){
            $orgids = DB::fetch_all("select orgid from %t where `type`=%d and forgid = %d",array($this->_table,0,0));
            foreach($orgids as $v){
                $groups['org'][]= parent::fetch($v['orgid']);
            }
        }else{
            if ($uid) {
                $orgids = C::t('organization_user')->fetch_org_by_uid($uid);
                $orgids = array_unique($orgids);
                $toporgids = array();
                foreach (parent::fetch_all($orgids) as $v) {
                    if ($v['type'] == 0) {
                        $patharr = explode('-', $v['pathkey']);
                        $toporgid = intval(str_replace('_', '', $patharr[0]));
                        if (in_array($toporgid, $toporgids)) {
                            continue;
                        }
                        $orginfo=parent::fetch($toporgid);
                        if (C::t('organization_admin')->chk_memberperm($toporgid, $uid) > 0) {
                            if($orginfo['syatemon'] == 1){
                                if($getmember){
                                    $orginfo['usernum'] = C::t('organization_user')->fetch_num_by_toporgid($toporgid);
                                    $orginfo['adminer'] = C::t('organization_admin')->fetch_adminer_by_orgid($toporgid);
                                }
                                $groups['org'][] = $orginfo;
                            }
                            //$orginfo = DB::fetch_first("select * from %t where `orgid` = %d and syatemon = %d ORDER BY disp", array($this->_table, $toporgid, 1));

                        } else {
                            if ($orginfo['syatemon'] == 1  && $orginfo['manageon'] == 1  && $orginfo['diron'] == 1) {
                                if($getmember){
                                    $orginfo['usernum'] = C::t('organization_user')->fetch_num_by_toporgid($toporgid);
                                    $orginfo['adminer'] = C::t('organization_admin')->fetch_adminer_by_orgid($toporgid);
                                }
                                $groups['org'][] = $orginfo;
                            }
                        }
                        $toporgids[] = $toporgid;
                    }
                }

            }
        }
        return $groups;
    }

    public function fetch_group_by_uid($uid, $foreces = false)
    {//查询自定义群组，$foreces=true为jstree加载内容，进行群组开启判断
        global $_G;
        if (!$uid) return false;
        $groups = array();
        $orgids = C::t('organization_user')->fetch_orgids_by_uid($uid,1);
        foreach (DB::fetch_all("select * from %t where `orgid` IN(%n) order by disp", array($this->_table, $orgids)) as $orginfo) {

                if ($foreces) {
                    if ($orginfo['syatemon'] == 0) {//系统管理员关闭群组
                        continue;
                        //如果是普通成员，判断群组是否关闭，暂时用diron来进行判断
                    } elseif ($orginfo['diron'] == 0 && C::t('organization_admin')->chk_memberperm($orginfo['orgid'], $uid) == 0) {//管理员关闭群组，当前用户不具备管理员权限
                        continue;
                    }
                }
                //jstree加载不需获取成员数和创建者
                if(!$foreces){
                    $orginfo['usernum'] = C::t('organization_user')->fetch_usernums_by_orgid($orginfo['orgid']);
                    $orginfo['creater'] = C::t('organization_admin')->fetch_group_creater($orginfo['orgid']);
                }

                if ($orginfo['aid'] > 0) {
                    //群组图
                    $orginfo['imgs'] = 'index.php?mod=io&op=thumbnail&width=24&height=24&path=' . dzzencode('attach::' . $orginfo['aid']);
                }
               /* $contaions = C::t('resources')->get_contains_by_fid($orginfo['fid'],true);
                $orginfo['ffsize'] = lang('property_info_size', array('fsize' => formatsize($contaions['size']), 'size' => $contaions['size']));
                $orginfo['contain'] = lang('property_info_contain', array('filenum' => $contaions['contain'][0], 'foldernum' => $contaions['contain'][1]));*/
                $groups[] = $orginfo;
        }
        return $groups;
    }

    public function delete_by_orgid($orgid)
    {
        if (!$org = parent::fetch($orgid)) {
            return array('error' => lang('remove_error_object_inexistence'));
        }
        if (self::fetch_all_by_forgid($org['orgid'], true) || ($org['fid'] && DB::result_first("select count(*) from %t where pfid = %d and isdelete < 1", array('resources', $org['fid'])))) {
            return array('error' => lang('remove_error_check_the_content'));
        }
        //删除对应目录
        if ($org['fid']) {
            C::t('folder')->delete_by_fid($org['fid'],true);
        }
        //删除对应事件
        C::t('resources_event')->delete_by_gid($orgid);
        //删除对应用户
        C::t('organization_user')->delete_by_orgid($orgid);
        //删除对应管理员
        C::t('organization_admin')->delete_by_orgid($orgid);
        if (parent::delete($orgid)) {
            if (intval($org['aid']) != 0) {
                C::t('attachment')->addcopy_by_aid($org['aid'], -1);
            }
            if( $org["type"]==0){//非群主才同步
                self::syn_organization($org['orgid'],'delete');
            }
            return $org;
        } else {
            return array('error' => lang('delete_error'));
        }
    }
  

    //判断用户是否是该部门或机构下级成员
    public function ismember($orgid,$uid=0,$onlychild = false)
    {
		if(!$uid) $uid=getglobal('uid');
        $pathkey = '_' . $orgid . '_';
        $orgids = array();
        if($onlychild){
            $pathkey = $pathkey . '.+';
        }else{
            $pathkey = $pathkey . '.*';
        }
        foreach (DB::fetch_all("select orgid from %t where pathkey regexp %s", array($this->_table, $pathkey)) as $v) {
            $orgids[] = $v['orgid'];
        }
        if (DB::result_first("select count(*) from %t where uid = %d and orgid in (%n) ", array('organization_user', $uid, $orgids)) > 0) {
            return true;
        }
        return false;
    }
	
    //获取机构群组下级
    public function fetch_org_by_uidorgid($uid, $orgid)
    {
		
        $resultarr = array();
        //如果该用户是当前部门普通成员则不获取下级机构信息,如果是下级机构成员或当前机构管理员则获取下级部门信息
        if (C::t('organization_admin')->chk_memberperm($orgid, $uid)) {//如果是管理员
            $resultarr = self::fetch_all_by_forgid($orgid,0,0);

        } elseif ($this->ismember($orgid,$uid,true)) {//如果是当前机构或部门下级的成员
            $orgids = C::t('organization_user')->fetch_orgids_by_uid($uid,0);
            $pathkeyarr = DB::fetch_all("select pathkey from %t where orgid in (%n) ", array($this->_table, $orgids));
            $porgids = array();
            foreach ($pathkeyarr as $v) {
                $vs = str_replace('_', '', $v['pathkey']);
                $varr = explode('-', $vs);
                $porgids = array_merge($porgids,$varr);
            }
			
            $orgsarr = self::fetch_all_by_forgid($orgid,0,0);
            $orgidarr = array();
            foreach ($orgsarr as $v) {
                
                    if (!in_array($v['orgid'], $orgidarr) && in_array($v['orgid'], $porgids)) {
						
                        if (C::t('organization_admin')->chk_memberperm($v['orgid'], $uid) > 0 && $v['syatemon'] == 1) {
                            $resultarr[] = $v;
                        } elseif ($v['syatemon'] && $v['manageon'] && $v['diron']) {
                            $resultarr[] = $v;
                        }
                        $orgidarr[] = $v['orgid'];
                    }
            }

		}
		//print_r($resultarr);
        return $resultarr;
    }

    //获取包含当前机构或部门包含下级的orgid
    public function get_all_contaionchild_orgid($orgid, $uid)
    {
        $orgids = array();
        if (!$org = self::fetch($orgid)) {
            return $orgids;
        }
        if ($org['type'] > 0) {
            $orgids[] = $orgid;
            return $orgids;
        }
        if (C::t('organization_admin')->chk_memberperm($orgid, $uid)) {//如果是管理员
            $orgids = self::get_childorg_by_orgid($orgid);
        } elseif ($this->ismember($uid, $orgid,true)) {//如果不是管理员，判断是否是下级成员
            $orgidarrs = array();
            //获取当前用户所在的所有群组id
            foreach (DB::fetch_all("select o.orgid,o.pathkey,o.manageon,o.diron from %t u left join %t o on o.orgid = u.orgid where u.uid = %d and o.type = 0", array('organization_user', 'organization', $uid)) as $v) {
                $pathkey = $v['pathkey'];
                $orgidarr = explode('-', str_replace('_', '', $pathkey));
                if (in_array($orgid, $orgidarr)) {
                    foreach ($orgidarr as $v) {
                        $orgidarrs[] = $v;
                    }
                }
            }
            $orgids = array_unique($orgidarrs);
        }
        return $orgids;
    }

    //获取当前部门或机构的下级
    public function get_childorg_by_orgid($orgid)
    {
        $orgidarr = array();
        if (!$orgid) return $orgidarr;
        $pathkey = DB::result_first("select pathkey from %t where orgid = %d", array($this->_table, $orgid));
        $orgids = DB::fetch_all("select orgid from %t where pathkey regexp %s ", array($this->_table, '^' . $pathkey . '.*'));
        foreach ($orgids as $v) {
            $orgidarr[] = $v['orgid'];
        }
        return $orgidarr;
    }

    //获取机构群组的上级id
    public function fetch_parent_by_orgid($orgid,$onlyid=true)
    {
        $pathkey = DB::result_first("select pathkey from %t where orgid = %d", array($this->_table, $orgid));
        $path = str_replace('_', '', $pathkey);
        $patharr = explode('-', $path);
		if($onlyid){
			return $patharr;
		}else{
			return parent::fetch_all($patharr);
		}
    }
    
    //将available修改为diron,后期将调整
    public function setFolderAvailableByOrgid($orgid, $available=0)
    {
        if (!$org = parent::fetch($orgid)) return false;
        if ($available > 0 && $org['forgid'] > 0) {//上级没有开启目录共享，下级无法开启
            $parent = parent::fetch($org['forgid']);
            if ($parent['diron'] < 1) return false;
        }
        if (parent::update($orgid, array('diron' => $available))) {
            //self::setFolderByOrgid($orgid);
            //include_once libfile('function/cache');
            //updatecache('organization');
            return true;
        }
        return false;
    }

    //暂时将syatemon调整为manageon
    public function setgroupByOrgid($orgid, $groupon=0)
    {
        if (!$org = parent::fetch($orgid)) return false;
        /*if ($groupon > 0 && $org['forgid'] > 0) {
            $toporgid = self::getTopOrgid($orgid);
            $top = parent::fetch($toporgid);
            if ($top['manageon'] < 1) return false;
        }*/
        if (parent::update($orgid, array('manageon' => $groupon))) {
            return true;
        }
        return false;
    }

    public function setIndeskByOrgid($orgid, $indesk)
    {
        if (!$org = parent::fetch($orgid)) return false;
        if ($indesk > 0) {
            if ($org['available'] < 1) return false;
        }
        if (parent::update($orgid, array('indesk' => $indesk))) {
            /*include_once libfile('function/cache');
            updatecache('organization');*/
            return true;
        }
        return false;
    }

    public function setFolderByOrgid($orgid)
    {
        if (!$org = parent::fetch($orgid)) return false;
        if ($org['forgid'] == 0) {
            $pfid = 0;
        } else {
            $pfid = DB::result_first("select fid from " . DB::table($this->_table) . " where orgid='{$org['forgid']}'");
        }

        if ($fid = DB::result_first("select fid from " . DB::table('folder') . " where gid='{$orgid}' and flag='organization'")) {
            if(C::t('folder')->rename_by_fid($fid,$org['orgname'])){
                self::update($orgid, array('fid' => $fid));
            }
        } else {
            $folder = array('fname' => C::t('folder')->getFolderName($org['orgname'],$pfid,$org['fid']),
                'pfid' => $pfid,
                'display' => $org['disp'],
                'flag' => 'organization',
                'gid' => $org['orgid'],
                'innav' => $org['available'],
                'uid' => getglobal('uid'),
                'username' => getglobal('username'),
                'perm' => perm_binPerm::getGroupPower('read')
            );
            $fid = C::t('folder')->insert($folder, 0);
        }
        if ($fid) {
            self::update($org['orgid'], array('fid' => $fid));
            return $fid;
        }
        return false;
    }

    public function setDispByOrgid($orgid, $disp, $forgid = 0)
    {
        if (!$org = parent::fetch($orgid)) return false;

        if ($torg = DB::fetch_first("select disp,orgid from %t where forgid=%d and orgid!=%d order by disp limit %d,1", array($this->_table, $forgid, $orgid, $disp))) {
            $disp = $torg['disp'];
         
			foreach (DB::fetch_all("select orgid,disp from %t where disp>%d and forgid=%d", array($this->_table, $disp, $forgid)) as $value) {
				parent::update($value['orgid'],array('disp'=>$value['disp']+1));
				//self::wx_update($value['orgid']);
			}
            
        } else {
            $disp = DB::result_first("select max(disp) from %t where forgid=%d", array($this->_table, $forgid)) + 1;
        }
        if ($return = parent::update($orgid, array('disp' => $disp, 'forgid' => $forgid))) {

            if ($org['forgid'] != $forgid) {
                //检查重名
                $orgname = self::get_uniqueName_by_forgid($forgid, $org['orgname'], $org['orgid']);
                if ($orgname != $org['orgname']) {//有重名
                    self::update_by_orgid($org['orgid'], array('orgname' => $orgname));
                }
                //重新设置所有下级机构的共享目录
                if ($pathkey = self::setPathkeyByOrgid($orgid)) {
                    $like = '^' . $pathkey;
                    foreach (DB::fetch_all("select orgid from %t where pathkey REGEXP %s", array($this->_table, $like)) as $value) {
                        self::setFolderByOrgid($value['orgid']);
                    }
                }
            }
            if ($disp > 10000) {
				foreach (DB::fetch_all("select orgid ,disp from %t where forgid=%d", array($this->_table, $forgid)) as $value) {
					parent::update($value['orgid'],array('disp'=>$value['disp']-9000));
				}
            } 
            return $return;
        } else {
            return false;
        }
    }

    public function getDispByOrgid($borgid)
    {
        $data = parent::fetch($borgid);
        $disp = $data['disp'] + 1;
       // DB::query("update %t SET disp=disp+1 where disp>=%d and forgid=%d", array($this->_table, $disp, $data['forgid']));
        return $disp;
    }

    public function chk_by_orgname($orgname,$type = 0,$forgid=0)
    {
        if (DB::result_first("select count(*) from %t where orgname = %s and `type` = %d and forgid = %d", array($this->_table, $orgname,$type,$forgid)) > 0) {
            return false;
        }
        return true;
    }

    public function insert_by_orgid($setarr, $synwx = 1)
    {
        $setarr['orgname'] = self::get_uniqueName_by_forgid($setarr['forgid'], $setarr['orgname']);
        if ($setarr['orgid'] = parent::insert($setarr, true)) {
            //self::setFolderByOrgid($org['orgid']);
            //include_once libfile('function/cache');
            //updatecache('organization'); 
            $uid = getglobal('uid');
            $username = getglobal('username');
            $fid = self::setFolderByOrgid($setarr['orgid']);//添加对应群组目录，默认未启用
            //添加自定义群组时，添加对应创始人
            if ($setarr['type'] !=0) {
                C::t('organization_user')->insert_by_orgid($setarr['orgid'],$uid);
                C::t('organization_admin')->insert($uid, $setarr['orgid'], 2);
            }
            //添加对应动态
            $eventdata = array('groupname' => $setarr['orgname'], 'uid' => getglobal('uid'), 'username' => getglobal('username'));
            C::t('resources_event')->addevent_by_pfid($fid, 'create_group', 'create', $eventdata, $setarr['orgid']);
            self::setPathkeyByOrgid($setarr['orgid']);
            return $setarr['orgid'];
        }
        return false;
    }

    public function insert_by_forgid($setarr, $borgid)
    {
        if ($borgid) {
            $setarr['disp'] = self::getDispByOrgid($borgid);
        }
        $setarr['orgname'] = self::get_uniqueName_by_forgid($setarr['forgid'], $setarr['orgname']);
        if ($setarr['orgid'] = parent::insert($setarr, true)) {
            self::setFolderByOrgid($setarr['orgid']);
            //include_once libfile('function/cache');
            //updatecache('organization');
            if (isset($setarr['type']) && $setarr['type'] != 0) {
                $uid = getglobal('uid');
                C::t('organization_admin')->insert($uid, $setarr['orgid'], 1);
            }
            
            self::setPathkeyByOrgid($setarr['orgid']); 
            if(isset($setarr['type']) && $setarr['type'] == 0 ) self::syn_organization($setarr['orgid']);
            return $setarr;
        }

        return false;
    }
    
    public function syn_organization( $data=array(),$type="update" ){
        if( $type=="update"){
            Hook::listen('syntoline_department',$data);//注册绑定到三方部门表 
        }else if( $type=="delete"){
            Hook::listen('syntoline_department',$data,"del");//删除对应到三方部门表
        }
    }

    public function update_by_orgid($orgid, $setarr, $synwx = 1)
    {
        if (!$org = self::fetch($orgid)) return false;
        if (isset($setarr['orgname'])) {
            $fid = $org['fid'];
            $name = self::get_uniqueName_by_forgid($org['forgid'], getstr($setarr['orgname']), $orgid);
            if (C::t('folder')->rename_by_fid($fid, $name)) {
                if (parent::update($orgid, array('orgname' => $name))) {
                    $body_data = array('username' => getglobal('username'), 'oldname' => $org['orgname'], 'newname' => $name);
                    $event_body = 'update_group_name';
                    C::t('resources_event')->addevent_by_pfid($org['fid'], $event_body, 'update_groupname', $body_data, $orgid, '', $org['orgname']);//记录事件 
                    if( $synwx && $org['type']==0) self::syn_organization($orgid);
                }
                unset($setarr['orgname']);
            }

        }
		
        if (isset($setarr['perm']) && $setarr['perm']) {
            $fid = $org['fid'];
            C::t('folder')->update($fid, array('perm' => $setarr['perm']));
            unset($setarr['perm']);
        }
        if (isset($setarr['desc'])) {
            $setarr['desc'] = htmlspecialchars($setarr['desc']);
        }
        if (empty($setarr)) return true;
        if (parent::update($orgid, $setarr)) {
		//处理图标copys数
			if(isset($setarr['aid'])){
				$oaid=intval($org['aid']);
				$aid=intval($setarr['aid']);
				if($oaid){
					C::t('attachment')->addcopy_by_aid($oaid,-1);
				}
				if($aid){
					C::t('attachment')->addcopy_by_aid($aid);
				}
			}
            $org = array_merge($org, $setarr);
            self::setFolderByOrgid($org['orgid']);
            $body_data = array('username' => getglobal('username'));
            $event_body = 'update_group_setting';
            C::t('resources_event')->addevent_by_pfid($org['fid'], $event_body, 'update_setting', $body_data, $orgid, '', $org['orgname']);//记录事件
            self::setPathkeyByOrgid($orgid);  
            if( $synwx &&  $org['type']==0 ) self::syn_organization($orgid);
            return true;
        }
        return true;
    }

    public function getTopOrgid($orgid)
    {
        include_once libfile('function/organization');
        $ids = self::fetch_parent_by_orgid($orgid);
        return $ids[0];
    }
	public function getUpOrgidTree($orgid,$pids=array()){
		global $_G;
		if($org=C::t('organization')->fetch($orgid)){
			//$pids[]=$orgid;
			array_unshift($pids,$orgid);
			$pids=self::getUpOrgidTree($org['forgid'],$pids);
		}
		return ($pids);
	}

    public function setPathkeyByOrgid($orgid, $force = 0)
    { //设置此机构的pathkey的值，$force>0 重设此部门的pathkey
        @set_time_limit(0);
        if (!$org = parent::fetch($orgid)) return false;
        if($org['type'] > 0){
            $pathkey = '_'.$orgid.'_';
            if (parent::update($org['orgid'], array('pathkey' => $pathkey))) return $pathkey;
            return false;
        }else{
            if ($force || empty($org['pathkey'])) {//没有pathkey,
              // include_once libfile('function/organization');
				if($ids=self::getUpOrgidTree($org['orgid'])){
					$pathkey='_'.implode('_-_',$ids).'_';
					if( parent::update($org['orgid'],array('pathkey'=>$pathkey))) return $pathkey;
				}
				return false;
            }
            //设置所有子部门的pathkey；
            if ($org['forgid'] && ($porg = parent::fetch($org['forgid']))) {
                $npathkey = $porg['pathkey'] . '-' . '_' . $orgid . '_';
            } else {
                $npathkey = '_' . $orgid . '_';
            }
            if ($org['pathkey'] == $npathkey) return $npathkey; //没有改变；
            $like = '^' . $org['pathkey'];
			foreach(DB::fetch_all("select orgid,pathkey from %t where pathkey REGEXP %s", array($this->_table, $like)) as $value){
				parent::update($value['orgid'],array('pathkey'=>str_replace($org['pathkey'],$npathkey,$value['pathkey'])));
			}
            /*if (DB::query("update %t set pathkey=REPLACE(pathkey,%s,%s) where pathkey REGEXP %s", array($this->_table, $org['pathkey'], $npathkey, $like))) {
                return $npathkey;
            }*/
        }
    }


    public function wx_update($orgid)
    {
        global $_G;
        if (!$this->_wxbind) return;
        if (!$org = parent::fetch($orgid)) return false;
        if ($org['type'] > 0) {//群主类型不同步至微信
            return false;
        }
        $wx = new qyWechat(array('appid' => $_G['setting']['CorpID'], 'appsecret' => $_G['setting']['CorpSecret'], 'agentid' => 0));
        $wd = array();
        if ($wxdepart = $wx->getDepartment()) {
            foreach ($wxdepart['department'] as $value) {
                $wd[$value['id']] = $value;
            }
        } else {
            return false;
        }
        if ($org['forgid']) {
            if (($forg = parent::fetch($org['forgid'])) && !$forg['worgid']) {
                if ($worgid = self::wx_update($forg['orgid'])) {
                    $forg['worgid'] = $worgid;
                } else {
                    return;
                }
            }
        }
        $parentid = ($org['forgid'] == 0 ? 1 : $forg['worgid']);
        if ($org['worgid'] && $wd[$org['worgid']] && $parentid == $wd[$org['worgid']]['parentid']) {//更新机构信息
            $data = array("id" => $org['worgid']);

            if ($wd[$org['worgid']]['name'] != $org['orgname']) $data['name'] = $org['orgname'];
            if ($wd[$org['worgid']]['parentid'] != $parentid) $data['parentid'] = $parentid;
            if ($wd[$org['worgid']]['order'] != $org['order']) $data['order'] = $org['order'];
            if ($data) $data['id'] = $org['worgid'];
            if ($data) {
                if (!$wx->updateDepartment($data)) {
                    $message = 'updateDepartment：errCode:' . $wx->errCode . ';errMsg:' . $wx->errMsg;
                    runlog('wxlog', $message);
                    return false;
                }
            }
            return $org['worgid'];

        } else {
            $data = array(
                "name" => $org['orgname'],   //部门名称
                "parentid" => $org['forgid'] == 0 ? 1 : $forg['worgid'],         //父部门id
                "order" => $org['disp'] + 1,            //(非必须)在父部门中的次序。从1开始，数字越大排序越靠后
            );
            if ($ret = $wx->createDepartment($data)) {
                parent::update($orgid, array('worgid' => $ret['id']));
                return $ret['id'];
            } else {
                if ($wx->errCode == '60008') {//部门的worgid不正确导致的问题
                    foreach ($wd as $value) {
                        if ($value['name'] == $data['name'] && $value['parentid'] = $data['parentid']) {
                            C::t('organization')->update($org['orgid'], array('worgid' => $value['id']));
                            return $value['id'];
                        }
                    }
                }
                $message = 'createDepartment：errCode:' . $wx->errCode . ';errMsg:' . $wx->errMsg;
                runlog('wxlog', $message);
                return false;
            }
        }
        return false;
    } 

    public function getPathByOrgid($orgid,$space='-')
    {
        $ret = array();
        if ($org = parent::fetch($orgid)) {
            $ids = explode('-', str_replace('_', '', $org['pathkey']));
            $arr = parent::fetch_all($ids);
            foreach ($ids as $id) {
                if ($arr[$id]) $ret[] = $arr[$id]['orgname'];
            }
        }
		if($space) $ret=implode($space,$ret);
        return $ret;
    }

    //获取用户有权限的机构orgid
    public function fetch_all_orgid()
    {
        global $_G;
        $uid = $_G['uid'];
        //获取当前用户参与的机构和群组
        $orgids = array();
        $orgids_admin = array();
        $orgids_member = array();
        $explorer_setting = get_resources_some_setting();
        if($_G['adminid'] == 1){
            $orgdatas = DB::fetch_all("select orgid,`type` from %t where 1", array('organization'));
        }else{
            $orgdatas = DB::fetch_all("select u.orgid,o.`type` from %t u left join %t o on u.orgid=o.orgid where uid = %d", array('organization_user', 'organization', $uid));
        }
        foreach ($orgdatas as $v) {
            if (!$explorer_setting['grouponperm'] && $v['type'] == 1) {
                continue;
            }
            if (!$explorer_setting['orgonperm'] && $v['type'] == 0) {
                continue;
            }
            $orgids[] = $v['orgid'];
        }
        //获取对应权限的机构orgid
        foreach ($orgids as $val) {
            //当前机构或部门管理员，查询所有下级和上级
            if (C::t('organization_admin')->chk_memberperm($val, $uid)) {
                $path = DB::result_first("select pathkey from %t where orgid = %d", array($this->_table, $val));
                $patharr = DB::fetch_all("select pathkey from %t where pathkey regexp %s and available = %d", array($this->_table, '^' . $path . '.*', 1));
                foreach ($patharr as $v) {
                    $pathstr = str_replace('_', '', $v['pathkey']);
                    if ($orgidarr = explode('-', $pathstr)) $orgids_admin = array_merge($orgids_admin, $orgidarr);
                }

            } else {//当前部门成员查询所有上级机构
                $path = DB::result_first("select pathkey from %t where orgid = %d and available = %d and diron = %d", array($this->_table, $val, 1, 1));
                $pathstr = str_replace('_', '', $path);
                if ($orgidarr = explode('-', $pathstr)) $orgids_member = array_merge($orgids_member, $orgidarr);

            }
        }
        $member_orgids = array();
        //判断参与群组的群组开启和文件开启
        foreach (DB::fetch_all('select manageon,diron,orgid from %t where orgid in(%n)', array($this->_table, $orgids_member)) as $v) {
            if ($v['manageon'] && $v['diron']) {
                $member_orgids[] = $v['orgid'];
            }
        }
        return array('orgids' => array_unique(array_merge($orgids_admin, $member_orgids)), 'orgids_admin' => array_unique($orgids_admin), 'orgids_member' => array_unique(array_diff($member_orgids, $orgids_admin)));
    }

    //获取用户管理的所有群组orgid
    public function fetch_all_manage_orgid()
    {
        global $_G;
        $uid = $_G['uid'];
        $explorer_setting = get_resources_some_setting();
        $manageorgid = DB::fetch_all("select orgid from %t where uid = %d", array('organization_admin', $uid));
        $orgids = array();
        $orgarr = array();
        foreach ($manageorgid as $v) {
            if (!in_array($v['orgid'], $orgarr)) {
                $info = DB::fetch_first("select pathkey,type from %t where orgid = %d", array($this->_table, $v['orgid']));
                if (!$explorer_setting['grouponperm'] && $info['type'] == 1) {
                    continue;
                }
                if (!$explorer_setting['orgonperm'] && $info['type'] == 0) {
                    continue;
                }
                $pathkey = $info['pathkey'];
                $orgidarr = DB::fetch_all("select orgid,orgname from %t where pathkey regexp %s ", array($this->_table, '^' . $pathkey . '.*'));
                foreach ($orgidarr as $val) {
                    $orgids[$val['orgid']] = array('orgid' => $val['orgid'], 'orgname' => $val['orgname']);
                    $orgarr[] = $val['orgid'];
                }
            }
        }
        return $orgids;
    }

    //我参与的所有群组和机构
    public function fetch_all_part_org()
    {
        $uid = getglobal('uid');
        $orgid = array();
        foreach (DB::fetch_all("select orgid from %t where uid = %d", array('organization_user', $uid)) as $v) {
            $orgid[] = $v['orgid'];
        }
        $orgid = array_unique($orgid);
        $explorer_setting = get_resources_some_setting();
        $orgarr = array();
        foreach (DB::fetch_all("select * from %t where orgid in(%n)", array($this->_table, $orgid)) as $v) {
            if (!$explorer_setting['grouponperm'] && $v['type'] == 1) {
                continue;
            }
            if (!$explorer_setting['orgonperm'] && $v['type'] == 0) {
                continue;
            }
            $org = array('orgid' => $v['orgid'], 'orgname' => $v['orgname']);
            $orgarr[] = $org;
        }
        return $orgarr;
    }

    //获取群组类型
    public function get_grouptype_by_orgid($orgid)
    {
        if (!is_array($orgid)) {
            return DB::result_first("select `type` from %t where orgid = %d", array($this->_table, $orgid));
        } else {
            $orgtypes = array();
            foreach (DB::fetch_all("select orgid,`type` from %t where orgid in(%n)", array($this->_table, $orgid)) as $v) {
                if ($v['type'] == 0) {
                    $orgtypes['org'][] = $v['orgid'];
                } else {
                    $orgtypes['group'][] = $v['orgid'];
                }

            }
            return $orgtypes;
        }

    }

    public function get_uniqueName_by_forgid($forgid, $orgname = '', $orgid = 0)
    {
        static $i = 0;
        if (empty($orgname)) $orgname = lang('new_department');

        if (DB::result_first("select COUNT(*) from %t where orgname=%s and  forgid=%d and orgid!=%d", array($this->_table, $orgname, $forgid, $orgid))) {
            $orgname = preg_replace("/\(\d+\)/i", '', $orgname) . '(' . ($i + 1) . ')';
            $i += 1;
            return self::get_uniqueName_by_forgid($forgid, $orgname);
        } else {
            return $orgname;
        }
    }

    /*空间相关*/

    /* 获取当前部门机构空间含有的空间限制值(从上向下)
     * 包含未分配空间的机构或部门已使用空间(单位为B)
     * $owner参数，默认为true即包含自身空间限制占用，设为false,不包含自身空间限制占用
     * */
    public function get_orgallotspace_by_orgid($orgid, $allotspace = 0, $owner = true)
    {
        $org = self::fetch($orgid);

        //如果当前部门有空间限制值，则返回该值
        if ($org['maxspacesize'] > 0 && $owner) {

            $allotspace += $org['maxspacesize'] * 1024 * 1024;

        } else {//如果当前部门没有分配空间，寻找下级分配空间之和

            //当前机构或部门已使用空间大小
            $allotspace += intval($org['usesize']);
            //下级部门分配空间大小
            foreach (DB::fetch_all("select orgid from %t where forgid = %d", array($this->_table, $orgid)) as $val) {
                $allotspace += self::get_orgallotspace_by_orgid($val['orgid']);
            }
        }
        return $allotspace;
    }


    /* *
     * 获取系统可分配空间大小
     * 如果系统无空间限制，返回0
     * 如果系统空间设置为-1，返回-1
     * 如果系统空间有设置，且空间使用量超出或等于分配和，返回-2，否则返回剩余可分配值(单位为B)
     * */
    public function get_system_allowallot_space()
    {
        global $_G;

        //获取系统空间设置值
        $systemspace = isset($_G['setting']['systemSpace']) ? intval($_G['setting']['systemSpace']) : 0;

        //系统空间无限制时
        if ($systemspace == 0) {

            $allowallotspace = 0;

        } elseif ($systemspace < 0) {//系统空间关闭时

            $allowallotspace = -1;

        } elseif ($systemspace > 0) {//设置系统空间限制时

            //获取所有顶级机构和群组空间限制值
            $fpathkey = DB::fetch_all("select maxspacesize,orgid from %t where forgid = 0", array($this->_table));

            $allotspace = 0;
            foreach ($fpathkey as $v) {

                //如果顶级机构有限制值，计算入限制值当中
                if ($v['maxspacesize'] > 0) {

                    $allotspace += intval($v['maxspacesize']) * 1024 * 1024;
                } else {//如果当前顶级机构没有限制值，获取当前机构已使用空间值+下层机构限制值之和(包含下层机构无限制的已使用空间)
                    $allotspace += self::get_orgallotspace_by_orgid($v['orgid']);
                }
            }
            //用户分配空间值
            $allotspace += C::t('user')->get_allotspace();
            $allowallotspace = $systemspace * 1024 * 1024 - $allotspace;


            if ($allowallotspace <= 0) {
                $allowallotspace = -2;
            }
        }
        return $allowallotspace;

    }

    /*
     * 获取当前部门空间限制值
     *从下到上依次查找，如果未找到空间限制，则获取系统空间限制，返回值单位为B
     * */
    public function get_parent_maxspacesize_by_pathkey($pathkey, $currentorgid)
    {
        $arr = array('orgid' => '', 'maxspacesize' => '');
        $pathkeys = explode('-', $pathkey);
        $pathkeys = array_reverse($pathkeys);
        foreach ($pathkeys as $v) {
            $orgid = intval(str_replace('_', '', $v));

            //排除当前部门
            if ($orgid == $currentorgid) {

                continue;

            } else {

                /*//判断是否有该层的管理权限
                if(!C::t('organization_admin')->chk_memberperm($orgid)){
                    exit(json_encode(array('error'=>'没有权限')));
                }*/
                //获取当前层分配空间大小
                $result = DB::result_first("select maxspacesize from %t where orgid = %d", array($this->_table, $orgid));

                if ($result > 0 || $result == -1) {

                    $arr['maxspacesize'] = $result * 1024 * 1024;
                    $arr['orgid'] = $orgid;
                    break;
                }
            }

        }
        //如果没有获取到上层限制,获取系统空间限制
        if ($arr['maxspacesize'] == '') {
            $arr['maxspacesize'] = self::get_system_allowallot_space();
        }
        return $arr;
    }


    /*
     * 获取当前机构或部门已分配空间大小
     * 包含已使用空间大小
     * 单位为B
     * */
    /*   public function get_current_occupysize_byorgid($orgid, $return = array())
       {
           $org = self::fetch($orgid);
           if($org['maxspacesize'] > 0){
               return
           }
           $return['usesize'] += $org['usesize'];
           //获取当前机构子级空间信息
           foreach (DB::fetch_all("select maxspacesize,usesize,orgid from %t where forgid = %d ", array('organization', $orgid)) as $v) {
               if ($v['maxspacesize'] > 0) {
                   $return['maxsize'] += $v['maxspacesize'] * 1024 * 1024;
               } elseif ($v['maxspacesize'] == 0) {
                   $return['maxsize'] += $v['usesize'];
                   $return = $this->get_current_occupysize_byorgid($v['orgid']);
               }
           }
           return $return;
       }*/

    /* *
     * 获取当前机构可分配空间大小
     * 上级限制-上级已占用+当前原值
     * */
    public function get_allowallotspacesize_by_orgid($orgid)
    {
        $currentallowsetsize = 0;
        $org = C::t('organization')->fetch($orgid);
        if (!$org) return;
        //获取父级可分配空间大小
        $topmaxspacesizeinfo = $this->get_parent_maxspacesize_by_pathkey($org['pathkey'], $orgid);

        //如果当前部门之前有分配空间设置
        if ($org['maxspacesize'] > 0) {

            $oldmaxspacesize = $org['maxspacesize'] * 1024 * 1024;

        } else {//如果当前部门之前无分配空间设置,获取其下级部门分配空间设置与当前部门未分配已使用之和

            $oldmaxspacesize = $this->get_orgallotspace_by_orgid($orgid);
        }
        if ($topmaxspacesizeinfo['maxspacesize'] > 0) {

            if ($topmaxspacesizeinfo['orgid']) {//有上级限制

                //含限制上级空间占用
                $topallotapce = $this->get_orgallotspace_by_orgid($topmaxspacesizeinfo['orgid'], 0, false);

                //计算当前部门可设置空间大小：有限制上级限制空间-有限制上级已占用空间+当前部门原空间
                $currentallowsetsize = $topmaxspacesizeinfo['maxspacesize'] - $topallotapce + $oldmaxspacesize;

            } else {//无上级限制(即使用系统空间限制)

                $currentallowsetsize = $topmaxspacesizeinfo['maxspacesize'] + $oldmaxspacesize;
            }

        } else {//返回值为0，-1，-2的特殊情形

            $currentallowsetsize = $topmaxspacesizeinfo['maxspacesize'];
        }
        return $currentallowsetsize;
    }

    //获取可使用空间大小
    public function get_usespace_size_by_orgid($orgid)
    {
        $allowusespace = 0;
        if (!$org = C::t('organization')->fetch($orgid)) {
            return -1;
        }
        //如果当前机构或部门已设置分配空间
        if ($org['maxspacesize'] > 0) {

            //获取当前机构或部门已占用空间大小
            $currentallotspace = $this->get_orgallotspace_by_orgid($orgid, 0, false);

            //获取当前机构或部门可使用空间大小
            $allowusespace = $org['maxspacesize'] * 1024 * 1024 - $currentallotspace;

            //如果当前机构或部门可使用空间大小不足
            if ($allowusespace <= 0) {

                $allowusespace = -2;

            }

        } elseif ($org['maxspacesize'] < 0) {//如果当前机构或部门已分配空间为-1

            $allowusespace = -1;

        } elseif ($org['maxspacesize'] == 0) {//如果当前机构或部门未分配空间

            //获取当前机构或部门可分配空间大小即其可用空间
            $allowusespace = self::get_allowallotspacesize_by_orgid($orgid);

        }
        return $allowusespace;
    }
	
	
	//获取我有管理权限的机构和部门(包括下级部门）orgids
	public function fetch_all_manage_orgids_by_uid($uids,$sub=true){
		if(!is_array($uids)) $uids=(array)$uids;
		if(!$orgids=C::t('organization_admin')->fetch_orgids_by_uid($uids)) return array();
		$sql="1";
		$param=array($this->_table);
		$sqlarr=array();
		if($sub){
			foreach(parent::fetch_all($orgids) as $value){
				$sqlarr[]='pathkey regexp %s';
				$param[]='^'.$value['pathkey'].'.*';
			}
			if($sqlarr){
				$sql.=' and (' . implode(' OR ',$sqlarr).')';
				foreach(DB::fetch_all("select orgid from %t where $sql",$param) as $value){
					$orgids[]=$value['orgid'];
				}
			}
		}
		return array_unique($orgids);
	}

}
