<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class table_resources_recyle extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'resources_recyle';
        $this->_pk = 'id';
        parent::__construct();
    }

    //插入回收站文件
    public function insert_data($setarr)
    {
        $arr = array(
            'rid' => $setarr['rid'],
            'uid' => getglobal('uid'),
            'username' => getglobal('username'),
            'gid' => $setarr['gid'],
            'filename' => $setarr['name'],
            'size' => $setarr['size'],
            'pfid' => $setarr['pfid'],
            'deldateline' => $setarr['deldateline']
        );
        $path = C::t('resources_path')->fetch_pathby_pfid($arr['pfid']);
        $arr['pathinfo'] = $path;
        if ($cid = parent::insert($arr)) {

            if ($path) $path = preg_replace('/dzz:(.+?):/', '', $path);
            $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($setarr['pfid'], $setarr['gid']);
            $eventdata = array('username' => $arr['username'], 'filename' => $arr['filename'], 'position' => ($path) ? $path : '', 'hash' => $hash);
            if ($setarr['type'] == 'folder') {

                if (C::t('resources_event')->addevent_by_pfid($setarr['pfid'], 'delete_folder', 'delfolder', $eventdata, $setarr['gid'], $setarr['rid'], $setarr['name'])) {
                    return true;
                } else {
                    parent::delete($cid);
                    return false;
                }
            } else {
                if (C::t('resources_event')->addevent_by_pfid($setarr['pfid'], 'delete_file', 'delfile', $eventdata, $setarr['gid'], $setarr['rid'], $setarr['name'])) {
                    return true;
                } else {
                    parent::delete($cid);
                    return false;
                }
            }

        }
    }

    //查询群组回收站文件
    public function fetch_by_gid($gid)
    {
        $gid = intval($gid);
        $result = array();
        foreach (DB::fetch_all("select * from %t where gid = %d", array($this->_table, $gid)) as $v) {
            $v['info'] = C::t('resources')->fetch_by_rid($v['rid']);
            $v['info']['deldateline'] = dgmdate($v['info']['deldateline']);
            $result[] = $v;
        }
        return $result;
    }

    //查询回收站文件
    public function fetch_by_ids($ids)
    {
        if (!is_array($ids)) $ids = (array)$ids;
        $results = DB::fetch_all("select * from %t where id in(%n)", array($this->_table, $ids));
        return $results;
    }

    //查询有权限回收站文件：我删除的和我管理的群组的(其他普通用户删除的文件不列出=>即我有权限删除的)
    public function fetch_all_recycle_data()
    {
        global $_G;
        $uid = $_G['uid'];
        $recycles = array();
        $orgids = C::t('organization')->fetch_all_orgid();
        $manageorgid = $orgids['orgids_admin'];
        if ($results = DB::fetch_all("select * from %t where uid = %d or gid in(%n)", array($this->_table, $uid, $manageorgid))) {
            $recycles = $results;
        }
        return $recycles;
    }

    //查询回收站数据
    public function fetch_all_rid($pfids=array())
    {
        $rids = array();
        $wheresql = ' where 1 ';
        $params = array($this->table);
		if($pfids){
			$wheresql.=" and pfid IN (%n)";
			$params[]=$pfids;
		}
        $uid = getglobal('uid');
        //查询有管理权限的群组id
        $manageorg = C::t('organization')->fetch_all_manage_orgid();
        $manageorgid = array();
        foreach ($manageorg as $v) {
            $manageorgid[] = $v['orgid'];
        }
        $wheresql .= 'and uid = %d or gid in(%n)';
        $params[] = $uid;
        $params[] = $manageorgid;
        foreach (DB::fetch_all("select rid from %t  $wheresql ", $params) as $v) {
            $rids[] = $v['rid'];
        }
        return $rids;
    }

    //查询回收站文件信息
    public function fetch_all_recycle($start = 0, $limit = 0, $condition = array(), $ordersql = '', $count = false)
    {
        global $_G;
        $limitsql = $limit ? DB::limit($start, $limit) : '';
        $wheresql = ' where 1 ';
        $params = array($this->table, 'resources', 'folder');
        //解析搜索条件
        if ($condition && is_string($condition)) {//字符串条件语句
            $wheresql .= $condition;
        } elseif (is_array($condition)) {
            foreach ($condition as $k => $v) {
                if (!is_array($v)) {
                    $connect = 'and';
                    $wheresql .= $connect . ' ' . $k . " = '" . $v . "' ";
                } else {
                    $relative = isset($v[1]) ? $v[1] : '=';
                    $connect = isset($v[2]) ? $v[2] : 'and';
                    if ($relative == 'in') {
                        $wheresql .= $connect . "  " . $k . " " . $relative . " (%n) ";
                        $params[] = $v[0];
                    } elseif ($relative == 'stringsql') {
                        $wheresql .= $connect . " " . $v[0] . " ";
                    } elseif ($relative == 'like') {
                        $wheresql .= $connect . " " . $k . " like %s ";
                        $params[] = '%' . $v[0] . '%';
                    } else {
                        $wheresql .= $connect . ' ' . $k . ' ' . $relative . ' ' . $v[0] . ' ';
                    }

                }
            }
        }
        $explorer_setting = get_resources_some_setting();
        $orgids = C::t('organization')->fetch_all_orgid();//获取所有有管理权限的部门
        $powerarr = perm_binPerm::getPowerArr();
        $or = array();
        //如果没有群组和网盘限制条件，默认查询我有权限管理的群组和我删除的文件
        if (!isset($condition['re.gid']) && !isset($condition['re.pfid'])) {
            $uid = $_G['uid'];
            if ($_G['adminid'] == 1) {
                if ($explorer_setting['useronperm']) {
                    $or[] = '(re.uid = %d and re.gid = 0)';
                    $params[] = $uid;
                }
                $gids = array_unique(array_merge($orgids['orgids_admin'], $orgids['orgids_member']));
                $or[] = ' (re.gid in (%n) )';
                $params[] = $gids;

            } else {
                if ($explorer_setting['useronperm']) {
                    $or[] = '(re.uid = %d and re.gid = 0)';
                    $params[] = $uid;
                }

                //我管理的群组或部门的文件
                if ($orgids['orgids_admin']) {
                    $or[] = "r.gid IN (%n)";
                    $params[] = $orgids['orgids_admin'];
                }
                //我参与的群组的文件
                if ($orgids['orgids_member']) {
                    $or[] = "(re.gid IN(%n) and ((f.perm_inherit & %d) OR (re.uid=%d and f.perm_inherit & %d)))";
                    $params[] = $orgids['orgids_member'];
                    $params[] = $powerarr['delete2'];
                    $params[] = $_G['uid'];
                    $params[] = $powerarr['delete1'];
                }

            }
        } else {
            if ($_G['adminid'] != 1 && !in_array($_G['uid'], $orgids['orgids_admin'])) {
                $wheresql .= " and ((f.perm_inherit & %d) OR (re.uid=%d and f.perm_inherit & %d))";
                $params[] = $powerarr['delete2'];
                $params[] = $_G['uid'];
                $params[] = $powerarr['delete1'];
            }
        }
        if ($or) $wheresql .= " and (" . implode(' OR ', $or) . ")";
        if ($count) {
            return DB::result_first("select count(*) from %t re left join %t r on re.rid=r.rid left join %t f on re.pfid=f.fid  $wheresql  $ordersql $limitsql ", $params);
        }
        $selectfileds = "re.id,re.deldateline,re.username,re.filename,re.pathinfo,r.name,r.size,r.rid,re.pfid,r.type,r.pfid,r.oid,r.gid";
        foreach (DB::fetch_all("select $selectfileds from %t re 
        left join %t r on re.rid=r.rid 
        left join %t f on re.pfid=f.fid
        $wheresql  $ordersql $limitsql ", $params) as $v) {
            if ($v['pathinfo']) {
                $path = preg_replace('/dzz:(.+?):/', '', $v['pathinfo']);
                $v['from'] = substr($path, 0, -1);
            }
            //计算最终删除时间
            $v['dpath'] = dzzencode($v['rid']);
            if ($explorer_setting['finallydelete'] > 0) {
                $endtime = intval($explorer_setting['finallydelete']);
                $dateend = strtotime("+" . $endtime . "day", $v['deldateline']);
                $v['finallydate'] = self::diffBetweenTwoDays($dateend);
            } else {
                $v['finallydate'] = '--';
            }
            $v['deldateline'] = dgmdate($v['deldateline'], 'Y-m-d');
            //获取文件图标
            $v['img'] = C::t('resources')->get_icosinfo_by_rid($v['rid']);
            //文件大小信息
            $v['fsize'] = ($v['size']) ? formatsize($v['size']) : 0;
            $v['isdelete'] = 1;
            $result[$v['rid']] = $v;
        }
        return $result;
    }

    //获取最终删除时间
    public function diffBetweenTwoDays($end)
    {
        $days = 0;
        $start = TIMESTAMP;
        if ($start < $end) {
            $days = floor(($start - $end) / 86400);
        }
        if ($days < 0) $days = 0;
        return $days;
    }

    //文件恢复
    public function recover_file_by_id($ids)
    {
        global $_G;
        $uid = $_G['uid'];
        if (!is_array($ids)) $ids = (array)$ids;
        $idarr = array();
        foreach ($ids as $id) {
            if (!$recyle = parent::fetch($id)) {
                continue;
            }
            $rid = $recyle['rid'];
            if (!$result = DB::fetch_first("select * from %t where rid = %s", array('resources', $rid))) {
                continue;
            }
            if ($result['gid'] > 0) {
                $pfid = $result['pfid'];
                $perm = perm_check::getPerm($result['pfid']);
                if ($perm > 0) {
                    if (!perm_binPerm::havePower('delete2', $perm) && !(perm_binPerm::havePower('delete1', $perm) && $result['uid'] == $uid)) {
                        continue;
                    }
                }
            }
            if (DB::update('resources', array('isdelete' => 0, 'deldateline' => 0), array('rid' => $rid)) && parent::delete($id)) {
                $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($result['pfid'], $result['gid']);
                $eventdata = array(
                    'username' => getglobal('username'),
                    'filename' => $result['name'],
                    'hash' => $hash
                );
                if (C::t('resources_event')->addevent_by_pfid($result['pfid'], 'recover_file', 'recover', $eventdata, $result['gid'], $rid, $result['name'])) {
                    $idarr[] = $id;
                } else {
                    DB::update($this->_table, array('isdelete' => 1, 'deldateline' => $result['deldateline']), array('rid' => $rid));
                    continue;
                }

            }

        }
        return $idarr;
    }

    public function fetch_by_rid($rid)
    {
        return DB::fetch_first("select * from %t where rid=%s", array($this->_table, $rid));
    }

    public function delete_by_rid($rid)
    {
        if (!is_array($rid)) $rid = (array)$rid;
        $rids = '';
        foreach ($rid as $v) {
            $rids .= "'" . $v . "',";
        }
        $rids = substr($rids, 0, -1);
        DB::delete($this->_table, "rid in (" . $rids . ")");
        return true;
    }

    //彻底删除
    public function delete_by_id($id)
    {
        if (!is_array($id)) $id = (array)$id;
        $ids = array();
        foreach ($id as $v) {
            if (!$recyle = parent::fetch($v)) {
                continue;
            }
            if (C::t('resources')->delete_by_rid($recyle['rid'])) {
                $ids[] = $v;
            }
        }
        return $ids;
    }

    //根据rid获取回收站数据
    public function get_data_by_rid($rid)
    {
        $rid = trim($rid);
        return DB::fetch_first("select * from %t where rid = %s", array($this->_table, $rid));
    }

    public function fetch_rid_bydate($date)
    {
        $rids = array();
        foreach (DB::fetch_all("select rid from %t where deldateline <= %s", array($this->_table, $date)) as $v) {
            $rids[] = $v['rid'];
        }
        return $rids;
    }
}