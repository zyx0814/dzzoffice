<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class table_folder extends dzz_table
{
    public $noperm = false;

    public function __construct()
    {

        $this->_table = 'folder';
        $this->_pk = 'fid';
        $this->_pre_cache_key = 'folder_';
        $this->_cache_ttl = 60 * 60;
        parent::__construct();
    }

    public function update($fid, $data,$applytosub=false)
    {
        if (isset($data['perm'])) {
            $perm = intval($data['perm']);
            $data['perm_inherit'] = perm_check::getPerm1($fid, '', 0, $perm);
        }
        if ($ret = parent::update($fid, $data)) {
            if (!empty($data['perm_inherit'])) {//如果更新权限的话，需要单独处理子目录的继承权限
                $this->set_newperm_by_pfid($fid,$data['perm_inherit'],$applytosub);
                $indexdata = array('fid'=>$fid);
                Hook::listen('changepermafter_updateindex',$indexdata);
                /*$power = new perm_binPerm($perm);
                if ($power->isPower('flag')) {//不继承，更新下级继承权限
                    $subfids = array();
                    foreach (DB::fetch_all("select p.fid from %t p  LEFT JOIN %t f ON p.fid=f.fid where p.pathkey regexp %s and f.perm='0'", array('resources_path', 'folder', '^_'.$fid.'_.+')) as $value) {
                        $subfids[] = $value['fid'];
                    }
                    if ($subfids) parent::update($subfids, array('perm_inherit' => $perm));
                }*/
            }
        }
        return $ret;
    }
    //更改权限修改下级,$force==true，强制修改所有下级目录权限为继承（perm值设置为0）
    public function set_newperm_by_pfid($pfid,$perm,$force=false){
        $pfid = intval($pfid);
        foreach(DB::fetch_all("select fid,perm from %t where pfid = %d",array($this->_table,$pfid)) as $v){
            if(!$force && $v['perm']>0){
                continue;
            }else{
                parent::update($v['fid'],array('perm'=>0,'perm_inherit'=>$perm));
                $indexdata = array('fid'=>$v['fid']);
                Hook::listen('changepermafter_updateindex',$indexdata);
                $this->set_newperm_by_pfid($v['fid'],$perm,$force);
            }
        }
		return true;
    }
    public function update_by_pfids($pfids,$setarr){
        if(!is_array($pfids)) $pfids = (array)$pfids;
        $fids = array();
        foreach(DB::fetch_all("select fid from %t where pfid in(%n)",array($this->_table,$pfids)) as $v){
            $fids[] = $v['fid'];
        }
        foreach($fids as $v){
            self::update($v,$setarr);
        }
        return true;
    }

    //更改继承权限
    public function update_perm_inherit_by_fid($fids)
    {
        if (!is_array($fids)) $fids = (array)$fids;
        foreach ($fids as $value) {
            $perm_inherit = perm_check::getPerm1($value);
            parent::update($value,array('perm_inherit' => $perm_inherit));
        }
    }

    public function insert($data, $appid = 0)
    {
        if (empty($data)) {
            return false;
        }
        if ($path['fid'] = parent::insert($data, 1)) {
            $perm_inherit = perm_check::getPerm1($path['fid']);
            parent::update($path['fid'], array('perm_inherit' => $perm_inherit));
            if ($data['pfid']) {
                if (!$pdata = C::t('resources_path')->fetch_pathby_pfid($data['pfid'], true)) {
                    //根据fid生成path和pathkey
                    $pdata = self::create_pathinfo_by_fid($data['pfid'], $appid);
                }
                if (!$pdata) {
                    return array('error' => lang('failure_newfolder'));
                }
                $path['path'] = $pdata['path'] . $data['fname'] . "/";
                $path['pathkey'] = ($pdata['pathkey']) ? $pdata['pathkey'] . '-_' . $path['fid'] . '_' : '_' . $path['fid'] . '_';
            } else {
                if ($appid) {
                    $path['path'] = "dzz:app_" . $appid . ":" . $data['fname'] . "/";
                } else {
                    $path['path'] = ($data['gid']) ? "dzz:gid_" . $data['gid'] . ":" . $data['fname'] . "/" : "dzz:uid_" . $data['uid'] . ":" . $data['fname'] . "/";
                }
                $path['pathkey'] = '_' . $path['fid'] . '_';
            }
            C::t('resources_path')->insert($path);
            self::check_sub_by_flag($path['fid'],$data['flag']);
			return $path['fid'];
        }
        return false;
    }
	public function  check_sub_by_flag($pfid,$flag){//检查这个目录是否含有子目录,有就创建
		$subids=C::t('folder_sub')->fetch_all_by_flag($flag);
		foreach($subids as $value){
			$params=array(
				'flag'=>$value['flag'],
				'fsperm'=>$value['fsperm'],
				'allow_exts'=>$value['allow_exts'],
				'iconview'=>$value['iconview'],
				'disp'=>$value['disp']
			);
			IO::CreateFolder($pfid, $value['fname'], $value['perm'], $params,'newcopy',true);
		}
		return true;
	}
    public function get_folder_pathinfo_by_fid($fid, $folderarr = array(), $i = 0)
    {
        if (!$folderinfo = parent::fetch($fid)) return;

        array_unshift($folderarr, array('fname' => $folderinfo['fname'], 'fid' => $folderinfo['fid'], 'gid' => $folderinfo['gid'], 'uid' => $folderinfo['uid']));

        if ($folderinfo['pfid'] > 0 && $i < 100) {
            $i++;
            $folderarr = self::get_folder_pathinfo_by_fid($folderinfo['pfid'], $folderarr, $i);
        }
        return $folderarr;
    }

    //根据fid生成path和pathkey
    public function create_pathinfo_by_fid($fid, $appid = 0)
    {
        $patharr = array();
        if (!$pathdata = self::get_folder_pathinfo_by_fid($fid)) return $patharr;
        $pathprefix = ($appid) ? "dzz:app_" . $appid . ":" : '';
        $path = '';
        $pathkey = '';
        foreach ($pathdata as $v) {
            $path .= $v['fname'] . '/';
            $pathkey .= '_' . $v['fid'] . '_-';
        }
        if (!$pathprefix) {
            $pathprefix = ($v['gid']) ? "dzz:gid_" . $v['gid'] . ":" : "dzz:uid_" . $v['uid'] . ":";
        }
        $patharr['path'] = $pathprefix . $path;
        $patharr['pathkey'] = substr($pathkey, 0, -1);
        return $patharr;
    }

    public function check_home_by_uid($uid = '')
    {
        global $_G;
        if (!$uid) $uid = $_G['uid'];
        else $uid = intval($uid);
        return DB::fetch_first("select f.*,p.path from %t f left join %t p on f.fid = p.fid where f.uid = %d and f.flag = %s", array($this->_table, 'resources_path', $uid, 'home'));

    }

    //查询个人文档目录以及路径
    public function fetch_home_by_uid($uid = '')
    {
        global $_G;
        if (!$uid) $uid = $_G['uid'];
        else $uid = intval($uid);
        if ($return = DB::fetch_first("select f.*,p.path from %t f left join %t p on f.fid = p.fid where f.uid = %d and f.flag = %s", array($this->_table, 'resources_path', $uid, 'home'))) {
            return $return;
        } else {
            $root = array(
                'pfid' => 0,
                'uid' => $uid,
                'username' => $_G['username'],
                'perm' => 0,
                'fname' => lang('explorer_user_root_dirname'),
                'flag' => 'home',
                'innav' => 1,
                'fsperm' => perm_FolderSPerm::flagPower('home')
            );
            if ($rootfid = DB::result_first("select fid from " . DB::table('folder') . " where uid='{$uid}' and flag='home' ")) {
                C::t('folder')->update($rootfid, array('fname' => $root['fname'], 'isdelete' => 0, 'pfid' => 0, 'fsperm' => $root['fsperm'], 'perm' => $root['perm']));
            } else {
                $rootfid = C::t('folder')->insert($root);
                C::t('folder')->update_perm_inherit_by_fid($rootfid);
            }
            $root['fid'] = $rootfid;
            $root['path'] = C::t('resources_path')->fetch_pathby_pfid($rootfid);
            return $root;
        }
    }

    //依文件名查询顶级目录
    public function fetch_topby_fname($fname)
    {
        $fid = DB::result_first("select fid from %t where fname = %s and pfid = 0", array($this->_table, $fname));
        return self::fetch_by_fid($fid);
    }

    //返回一条数据及附件表数据
    public function fetch_by_fid($fid, $gid = '')
    {
        global $_G;
        $fid = intval($fid);
        if (!$data = self::fetch($fid)) array('error' => lang('file_not_exist'));
		//获取目录的附加信息
		if($attr=C::t('folder_attr')->fetch_all_by_fid($fid)){
			$data=array_merge($data,$attr);
		}
        $data['title'] = $data['fname'];
        //统计文件数
        if ($data['gid'] > 0) {//如果是群组
            //文件数
            $data['iconum'] = DB::result_first("select COUNT(*) from " . DB::table('resources') . " where pfid='{$fid}' and gid='{$gid}' and isdelete<1");
            //文件夹数
            $data['foldernum'] = DB::result_first("select COUNT(*) from " . DB::table('resources') . " where pfid='{$fid}' and gid='{$gid}' and type='folder' and isdelete<1");
        } else {
            //文件数
            $data['iconum'] = DB::result_first("select COUNT(*) from " . DB::table('resources') . " where pfid='{$fid}' and isdelete < 1");
            //文件夹数
            $data['foldernum'] = DB::result_first("select COUNT(*) from " . DB::table('resources') . " where pfid='{$fid}' and type='folder' and isdelete < 1");
        }
        $data['perm'] = perm_check::getPerm($fid);
        $data['perm1'] = $data['perm_inherit'];
        if ($data['gid'] > 0) {
            $data['isadmin'] = $data['ismoderator'] = C::t('organization_admin')->is_admin_by_orgid($data['gid'], $_G['uid']);
            $permtitle = perm_binPerm::getGroupTitleByPower($data['perm1']);
            if (file_exists('dzz/images/default/system/folder-' . $permtitle['flag'] . '.png')) {
                $data['icon'] = './dzz/images/default/system/folder-' . $permtitle['flag'] . '.png';
            } else {
                $data['icon'] = './dzz/images/default/system/folder-read.png';
            }
        }
        $data['realpath'] = C::t('resources_path')->fetch_pathby_pfid($fid);
        $data['relativepath'] = preg_replace('/dzz:(.+?):/', '', $data['realpath']);
        $data['path'] = $data['fid'];
        $data['oid'] = $data['fid'];
        $data['bz'] = '';
		Hook::listen('filter_folder_fid', $data);//数据过滤挂载点
        return $data;
    }

    //过滤文件名称
    public function name_filter($name)
    {
        return str_replace(array('/', '\\', ':', '*', '?', '<', '>', '|', '"', "\n"), '', $name);
    }

    public function getFolderName($name, $pfid, $fid)
    {
        static $i = 0;
        $name = self::name_filter($name);
        //echo("select COUNT(*) from ".DB::table('folder')." where fname='{$name}' and  pfid='{$pfid}'");
        if (DB::result_first("select COUNT(*) from %t where fname=%s and  pfid=%d and fid != %d and isdelete<1", array('folder', $name, $pfid, $fid))) {
            $name = preg_replace("/\(\d+\)/i", '', $name) . '(' . ($i + 1) . ')';
            $i += 1;
            return self::getFolderName($name, $pfid, $fid);
        } else {
            return $name;
        }
    }

    //更改文件夹名称
    public function rename_by_fid($fid, $name)
    {
        if (!$folder = parent::fetch($fid)) return false;
        //如果文件夹有对应的rid
        if ($rid = C::t('resources')->fetch_rid_by_fid($fid)) {
            $return = C::t('resources')->rename_by_rid($rid);
            if ($return['error']) {
                return false;
            } else {
                return true;
            }
        } else {
            $name = self::getFolderName($name, $folder['pfid'], $fid);
            //更改路径表数据
            if (C::t('resources_path')->update_path_by_fid($fid, $name)) {
                //增加统计数
                $statisdata = array(
                    'uid' => getglobal('uid'),
                    'edits' => 1,
                    'editdateline' => TIMESTAMP
                );
                C::t('resources_statis')->add_statis_by_fid($fid, $statisdata);
                //更改folder数据
                return parent::update($fid, array('fname' => $name));
            } else {
                return false;
            }
        }

    }

    //查询组织id
    public function fetch_gid_by_fid($fid, $i = 0)
    {

        if (!$folder = parent::fetch($fid)) return 0;
        if ($folder['flag'] == 'organization') return $folder['gid'];
        elseif ($folder['pfid'] > 0) {
            $i++;
            if ($i > 100) {
                return $folder['gid'];
            }
            return self::fetch_gid_by_fid($folder['pfid'], $i);
        }
    }

    //查询路径
    public function fetch_path_by_fid($fid, $fids = array())
    {
        if (!$folder = parent::fetch($fid)) return;
        $fids[] = $folder['fid'];
        if ($folder['pfid'] > 0) {
            $fids = self::fetch_path_by_fid($folder['pfid'], $fids);
        }
        return $fids;
    }

    /*
            此函数不会删除 $fid对应的 rid数据，正常调用是作为C::t('resources')->deletesourcedata()的内部调用；
         如果单独调用，请注意这个fid应该没有对应的rid；
         我的网盘 应用主目录 群组和部门主目录 都是没有对应的rid的特殊目录；
     */
    public function delete_by_fid($fid, $force = false)
    { //清空目录
        $folder = self::fetch($fid);
        //默认只允许删除文件夹和群组根目录，暂时不允许删除应用根目录
        if (!$force && $folder['flag'] =='app') {
            return false;
        }
        //判断删除权限
        /* if (!perm_check::checkperm_container($fid, 'delete')) {
             return array('error' => lang('no_privilege'));
         }*/
        $rids = array();
        $isdelrids = array();
        $nodelrids = array();
        //获取当前文件夹包含的删除状态数据，及非删除状态数据
        foreach (DB::fetch_all("select rid,oid,isdelete from %t where pfid = %d", array('resources', $fid, $fid)) as $v) {
            if ($v['isdelete'] > 0) {
                $isdelrids[] = $v['rid'];
            } else {
                $nodelrids[] = $v['rid'];
            }
            $rids[] = $v['rid'];
        }
        $delrids = array();
        if (!$force) {//如果非强制彻底删除，只删除删除状态项
            $delrids = $isdelrids;
        } else {//如果是强制删除，删除所有文件，包括非删除状态和删除状态文件
            $delrids = $rids;
            //获取当前目录中在回收站中的数据
            foreach (DB::fetch_all("select rid from %t where pfid = %d", array('resources_recyle', $fid)) as $v) {
                $delrids[] = $v['rid'];
            }
        }
        //执行删除
        foreach ($delrids as $value) {
            C::t('resources')->delete_by_rid($value, $force);
        }
        //如果当前目录是非删除状态或者下级有不能彻底删除文件(未删除文件)，则跳过当前目录删除，只清空回收站表数据
        if (!$force && count($nodelrids) > 0) {
            return 2;
        }
        return self::delete($fid);
    }

    //删除目录
    public function delete($fid)
    {
        //删除路径表数据
        C::t('resources_path')->delete_by_fid($fid);
        //删除文件夹属性表数据
        C::t('folder_attr')->delete_by_fid($fid);
        //删除文件夹动态(只限于文件夹)
        C::t('resources_event')->delete_by_pfid_and_notrid($fid);
        return parent::delete($fid);
    }

    public function fetch_all_default_by_uid($uid)
    {
        return DB::fetch_all("SELECT * FROM %t WHERE `default`!= '' and uid=%d  ", array($this->_table, $uid), 'fid');
    }

    public function fetch_typefid_by_uid($uid)
    {
        $data = array();
        foreach (DB::fetch_all("SELECT * FROM %t WHERE `flag`!= 'folder' and  uid='{$uid}' and gid<1  ", array($this->_table), 'fid') as $value) {
            $data[$value['flag']] = $value['fid'];
        }
        return $data;
    }

    public function fetch_all_by_pfid($pfid, $count)
    {
        global $_G;
        $wheresql = 'pfid = %d  and isdelete<1';
        if ($folder = C::t('folder')->fetch_by_fid($pfid)) {
            $where1 = array();
            if (!$this->noperm && $folder['gid'] > 0) {
                $folder['perm'] = perm_check::getPerm($folder['fid']);

                if ($folder['perm'] > 0) {
                    if (perm_binPerm::havePower('read1', $folder['perm'])) {
                        $where1[] = "uid ='{$_G[uid]}'";
                    }
                    if (perm_binPerm::havePower('read2', $folder['perm'])) {
                        $where1[] = "uid!='{$_G[uid]}'";
                    }
                }
                if ($where1) $wheresql .= " and (" . implode(' OR ', $where1) . ")";
                else $wheresql .= " and 0";
            }
        }
        if ($count) return DB::result_first("SELECT COUNT(*) FROM %t WHERE $wheresql", array($this->_table, $pfid));
        else return DB::fetch_all("SELECT * FROM %t WHERE $wheresql", array($this->_table, $pfid), 'fid');
    }

    public function fetch_folderinfo_by_gid($gid)
    {//查询群组目录及文件基本信息
        $gid = intval($gid);
        if ($info = DB::fetch_first("select f.*,p.path from %t f left join %t p on f.fid = p.fid  where gid = %d and flag = %s", array($this->_table, 'resources_path', $gid, 'organization'))) {
            return $info;
        }
        return false;
    }

    public function fetch_fid_by_flag($flag)
    {
        $uid = getglobal('uid');
        return DB::result_first("select fid from %t where uid = %d and  flag = %s", array($this->_table, $uid, $flag));
    }

    public function fetch_fid_by_flags($flags)
    {
        if (!is_array($flags)) $flags = (array)$flags;
        $fids = array();
        foreach (DB::fetch_all("select fid from %t where flag in(%n)", array($this->_table, $flags)) as $v) {
            $fids[] = $v['fid'];
        }
        return $fids;
    }

    public function fetch_folderinfo_by_fid($fid)
    {//查询群组目录及文件基本信息
        $fid = intval($fid);
        if (!$folderinfo = self::fetch($fid)) {
            return false;
        }
        $pathinfo = C::t('resources_path')->fetch_pathby_pfid($fid, true);
        $info = array_merge($folderinfo, $pathinfo);
        return $info;

    }

    //获取文件夹权限
    public function fetch_perm_by_fid($fid)
    {
        $perms = DB::fetch_first("select perm,perm_inherit from %t where fid = %d", array($this->_table, $fid));
        if ($perms['perm']) {
            return $perms['perm'];
        } else {
            return $perms['perm_inherit'];
        }
    }

    public function fetch_folder_by_pfid($pfid, $field = array())
    {//查询群组目录及文件夹基本信息
        global $_G;
        $fielddata = '*';
        if (!empty($field)) {
            $fielddata = implode(',', $field);
        }
        $pfid = intval($pfid);
        $infoarr = array();
        if ($folder = C::t('folder')->fetch($pfid)) {
            $where1 = array();
            if (!$this->noperm && $folder['gid'] > 0) {
                    if (perm_check::checkperm_Container($folder['fid'], 'read2')) {
                        $where1[] = "1";
                    } elseif (perm_check::checkperm_Container($folder['fid'], 'read1')) {
                        $where1[] = "uid='{$_G[uid]}'";
                    }
                $where1 = array_filter($where1);
                if (!empty($where1)) $temp[] = "(" . implode(' OR ', $where1) . ")";
                else $temp[] = "0";
            } else {
                $temp[] = " uid='{$_G[uid]}'";
            }
            $where[] = '(' . implode(' and ', $temp) . ')';
            unset($temp);
        }
        $wheresql = "where  pfid = %d and flag != %s and ";
        if ($where) $wheresql .= implode(' AND ', $where);
        else return false;
        $infoarr = DB::fetch_all("select $fielddata from %t $wheresql and isdelete < 1 order by 
		convert(fname,UNSIGNED)" .
		",SUBSTRING_INDEX(fname,'-',1)" .
		",convert(replace(replace(SUBSTRING_INDEX(fname,'-',2),SUBSTRING_INDEX(fname,'-',1),''),'-','') , UNSIGNED) " .
		",convert(replace(replace(SUBSTRING_INDEX(fname,'-',3),SUBSTRING_INDEX(fname,'-',2),''),'-','') , UNSIGNED) ", array($this->_table, $pfid, 'organization'));
        return $infoarr;
    }

    //查询子文件夹fid
    public function fetch_fid_by_pfid($pfid)
    {
        global $_G;
        $pfid = intval($pfid);
        $infoarr = array();
        if ($folder = C::t('folder')->fetch($pfid)) {
            $where1 = array();
            if (!$this->noperm && $folder['gid'] > 0) {
                $folder['perm'] = perm_check::getPerm($folder['fid']);
                if ($folder['perm'] > 0) {
                    if (perm_binPerm::havePower('read2', $folder['perm'])) {
                        $where1[] = "1";
                    } elseif (perm_binPerm::havePower('read1', $folder['perm'])) {
                        $where1[] = "uid='{$_G[uid]}'";
                    }
                }
                $where1 = array_filter($where1);
                if (!empty($where1)) $temp[] = "(" . implode(' OR ', $where1) . ")";
                else $temp[] = "0";
            } else {
                $temp[] = " uid='{$_G[uid]}'";
            }
            $where[] = '(' . implode(' and ', $temp) . ')';
            unset($temp);
        }
        $wheresql = "where  pfid = %d and ";
        if ($where) $wheresql .= implode(' AND ', $where);
        else return false;
        $infoarr = DB::fetch_all("select fid from %t $wheresql", array($this->_table, $pfid));

        return $infoarr;
    }

    /*    //查询所有有权限文件夹
        public function fetch_all_fid()
        {
            global $_G;
            $uid = $_G['uid'];
            $fids = array();
            //个人根目录
            $personfid = DB::result_first("select fid from %t where uid = %d and flag = %s", array($this->_table, $uid, 'home'));
            $fids[] = $personfid;
            foreach ($this->fetch_all_folderfid_by_pfid($personfid) as $v) {
                $fids[] = $v;
            }
            //群组部门顶级目录
            $orgs = C::t('organization')->fetch_all_orgid();
            $orgids = $orgs['orgids'];
            $fidarr = DB::fetch_all("select fid from %t where orgid in(%n)", array('organization', $orgids));

            //群组目录及下级所有目录fid
            foreach ($fidarr as $v) {
                $fids[] = $v['fid'];
                foreach ($this->fetch_all_folderfid_by_pfid($v['fid']) as $val) {
                    $fids[] = $val;
                }
            }
            return $fids;

        }*/


    //查询目录下所有文件夹的fid
    public function fetch_all_folderfid_by_pfid($pfid)
    {
        static $fids = array();
        foreach ($this->fetch_fid_by_pfid($pfid) as $v) {
            $fids[] = $v['fid'];
            $this->fetch_fid_by_pfid($v['fid']);
        }
        return $fids;
    }

    //获取目录的所有上级目录
    public function fetch_all_parent_by_fid($fid, $ret = array())
    {
        if (!$folder = parent::fetch($fid)) {
            return $ret;
        }
        $ret[] = $folder;
        if ($folder['pfid'] > 0) {
            $ret = self::fetch_all_parent_by_fid($folder['pfid'], $ret);
        }
        return $ret;
    }

}