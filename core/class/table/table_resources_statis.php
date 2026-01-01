<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class table_resources_statis extends dzz_table {
    public function __construct() {

        $this->_table = 'resources_statis';
        $this->_pk = 'rid';
        parent::__construct();
    }

    //根据fid增加统计数据
    public function add_statis_by_fid($fid, $setarr) {
        global $_G;
        $uid = $_G['uid'];
        $fid = intval($fid);
        if (!$fid) return false;
        if($setarr['editdateline']) $setarr['edituid'] = $uid;
        if (!DB::result_first("select count(*) from %t where fid = %d", [$this->_table, $fid])) {
            $fileinfo = C::t('folder')->fetch($fid);
            $insertarr = ['editdateline' => $fileinfo['dateline'], 'pfid' => $fileinfo['pfid'], 'uid' => $uid, 'opendateline' => $fileinfo['dateline'], 'fid' => $fid,'edituid' => $uid];
            if (!$insert = parent::insert($insertarr, 1)) {
                return false;
            }
        }
        $params = [$this->_table];
        $editsql = '';
        $editarr = [];
        //对有数据的进行修改
        foreach ($setarr as $k => $v) {
            $increasearr = ['edits', 'views', 'downs'];
            $editarr = ['uid', 'editdateline', 'opendateline', 'fid','edituid'];
            if (in_array($k, $increasearr)) {
                $editsql .= $k . '=' . $k . '+' . $v . ',';
            } elseif (in_array($k, $editarr)) {
                $editsql .= $k . '=%d' . ',';
                $params[] = $v;
            }
        }
        $editsql = substr($editsql, 0, -1);
        $params[] = $fid;
        return true;
    }

    //根据rid增加统计数据
    public function add_statis_by_rid($rids, $setarr) {//增加统计数据
        global $_G;
        $uid = $_G['uid'];
        if (!is_array($rids)) $rids = (array)$rids;
        if(!$rids) return false;
        if($setarr['editdateline']) $setarr['edituid'] = $uid;
        $statis = [];
        $statisrid = [];
        //查询rid数组,判断当前$rids数组是否在数据库已经有数据
        $statisrids = DB::fetch_all("select rid from %t where rid in(%n)", [$this->_table, $rids]);
        foreach ($statisrids as $v) {
            $statisrid[] = $v['rid'];
        }
        foreach ($rids as $v) {
            if (!in_array($v, $statisrid)) {
                $statis[] = $v;
            }
        }
        //无数据的进行创建
        if ($statis) {
            foreach ($statis as $v) {
                $fileinfo = C::t('resources')->fetch_info_by_rid($v);
                $insertarr = ['rid' => $v, 'editdateline' => $fileinfo['dateline'], 'pfid' => $fileinfo['pfid'], 'uid' => $uid, 'opendateline' => $fileinfo['dateline'], 'edituid' => $uid];
                if ($fileinfo['oid'] && $fileinfo['type'] == 'folder') {
                    $insertarr['fid'] = $fileinfo['oid'];
                }
                if (!parent::insert($insertarr, 1)) {
                    $index = array_search($v, $rids);
                    unset($rids[$index]);
                }
            }
        }

        $params = [$this->_table];
        $editsql = '';
        //对有数据的进行修改
        foreach ($setarr as $k => $v) {
            $increasearr = ['edits', 'views', 'downs'];
            $editarr = ['uid', 'editdateline', 'opendateline', 'fid', 'edituid'];
            if (in_array($k, $increasearr)) {
                $editsql .= $k . '=' . $k . '+' . $v . ',';
            } elseif (in_array($k, $editarr)) {
                $editsql .= $k . '=%d' . ',';
                $params[] = $v;
            }
        }
        $editsql = substr($editsql, 0, -1);
        $wheresql = ' where  rid in (%n)';
        $params[] = $rids;
        if (DB::query("update %t set $editsql $wheresql", $params)) {
            return true;
        }
        return false;
    }

    public function delete_by_rid($rid) {
        if (!is_array($rid)) $rid = (array)$rid;
        return DB::delete($this->_table, 'rid in(' . dimplode($rid) . ')');
    }

    public function fetch_by_fid($fid) {
        $fid = intval($fid);
        return DB::fetch_first("select * from %t where fid = %d", [$this->_table, $fid]);
    }

    public function fetch_by_rid($rid) {
        $rid = trim($rid);
        return DB::fetch_first("select * from %t where rid = %s", [$this->_table, $rid]);
    }

    //最近使用文件夹
    public function fetch_folder_by_uid($limit = 5) {
        global $_G;
        $folderdata = [];
        $orderby = ' order by edits desc,views desc,editdateline desc,opendateline desc';
        $limitsql = ' limit ' . $limit;
        return DB::fetch_all("select * from %t where uid = %d  and fid != 0 and rid != '' $orderby $limitsql", [$this->_table, $_G['uid']]);
    }

    //最近使用的文件
    public function fetch_files_by_uid($limit = 20) {
        global $_G;
        $data = [];
        $param = [$this->_table, $_G['uid']];
        $wheresql = " where uid = %d and fid = 0 and rid != '' ";
        $orderby = ' order by edits desc,views desc,editdateline desc,opendateline desc';
        $limitsql = ' limit ' . $limit;
        return DB::fetch_all("select * from %t $wheresql $orderby $limitsql", $param);
    }

    public function fetch_recent_files_by_uid($limit = 100) {
        $files = self::fetch_files_by_uid();
        $folders = self::fetch_folder_by_uid();
        $results = [];
        foreach ($folders as $v) {
            $results[] = $v;
        }
        foreach ($files as $v) {
            $results[] = $v;
        }
        return $results;
    }
}