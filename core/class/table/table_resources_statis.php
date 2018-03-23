<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
class table_resources_statis extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'resources_statis';
        $this->_pk = 'rid';
        parent::__construct();
    }
    //根据fid增加统计数据
    public function add_statis_by_fid($fid,$setarr){
        global $_G;
        $uid = $_G['uid'];
        $fid = intval($fid);
        if(!DB::result_first("select count(*) from %t where fid = %d",array($this->_table,$fid))){
            $fileinfo = C::t('folder')->fetch($fid);
            $insertarr = array('editdateline'=>$fileinfo['dateline'],'pfid'=>$fileinfo['pfid'],'uid'=>$uid,'opendateline'=>$fileinfo['dateline'],'fid'=>$fid);
            if(!$insert = parent::insert($insertarr,1)){
                return false;
            }
        }
        $params = array($this->_table);
        $editsql = '';
        $editarr = array();
        //对有数据的进行修改
        foreach($setarr as $k=>$v){
            $increasearr = array('edits','views','downs');
            $editarr = array('uid','editdateline','opendateline','fid');
            if(in_array($k,$increasearr)){
                $editsql .= $k.'='.$k.'+'.$v.',';
            }elseif(in_array($k,$editarr)){
                $editsql .= $k.'=%d'.',';
                $params[] = $v;
            }
        }
        $editsql = substr($editsql,0,-1);
        $params[] = $fid;
        if($ret = DB::query("update %t set $editsql where fid = %d",$params)){
            return true;
        }
        return true;
    }
    //根据rid增加统计数据
    public function add_statis_by_rid($rids,$setarr){//增加统计数据
        global $_G;
        $uid = $_G['uid'];
        if(!is_array($rids)) $rids = (array)$rids;

        $statis = array();
        $statisrid = array();
        //查询rid数组,判断当前$rids数组是否在数据库已经有数据
        $statisrids = DB::fetch_all("select rid from %t where rid in(%n)",array($this->_table,$rids));
        foreach($statisrids as $v){
            $statisrid[] = $v['rid'];
        }
        foreach($rids as $v){
            if(!in_array($v,$statisrid)){
                $statis[] = $v;
            }
        }
        //无数据的进行创建
        if($statis){
            foreach($statis as $v){
                $fileinfo = C::t('resources')->fetch_info_by_rid($v);
                $insertarr = array('rid'=>$v,'editdateline'=>$fileinfo['dateline'],'pfid'=>$fileinfo['pfid'],'uid'=>$uid,'opendateline'=>$fileinfo['dateline']);
                if($fileinfo['oid'] && $fileinfo['type'] == 'folder') $insertarr['oid'];
                if(!parent::insert($insertarr,1)){
                    $index = array_search($v,$rids);
                    unset($rids[$index]);
                }
            }
        }

        $params = array($this->_table);
        $editsql = '';
        //对有数据的进行修改
        foreach($setarr as $k=>$v){
            $increasearr = array('edits','views','downs');
            $editarr = array('uid','editdateline','opendateline','fid');
            if(in_array($k,$increasearr)){
                $editsql .= $k.'='.$k.'+'.$v.',';
            }elseif(in_array($k,$editarr)){
                $editsql .= $k.'=%d'.',';
                $params[] = $v;
            }
        }
        $editsql = substr($editsql,0,-1);
        $wheresql = ' where  rid in (%n)';
        $params[] = $rids;
       if(DB::query("update %t set $editsql $wheresql",$params)){
           return true;
       }
	   return true;
    }
    public function delete_by_rid($rid){
        if(!is_array($rid)) $rid = (array)$rid;
        return DB::delete($this->_table,'rid in('.dimplode($rid).')');
    }

    public function fetch_by_fid($fid){
        $fid = intval($fid);
        return DB::fetch_first("select * from %t where fid = %d",array($this->_table,$fid));
    }
    public function fetch_by_rid($rid){
        $rid = trim($rid);
        return DB::fetch_first("select * from %t where rid = %s",array($this->_table,$rid));
    }
    //最近使用文件夹
    public function fetch_folder_by_uid($limit = 5){
        global $_G;
        $uid = getglobal('uid');;
        $folderdata = array();
        $orderby = ' order by edits desc,views desc,editdateline desc,opendateline desc';
        $limitsql = ' limit '.$limit;
        $folders = DB::fetch_all("select * from %t where uid = %d  and fid != 0 and rid != '' $orderby $limitsql",array($this->_table,$uid));
        return $folders;
    }

    //最近使用的文件
    public function fetch_files_by_uid($limit = 20){
        global $_G;
        $uid = getglobal('uid');
        $data = array();
        $param = array($this->_table,$uid);
        $wheresql = " where uid = %d and fid = 0 and rid != '' ";

        $orderby = ' order by edits desc,views desc,editdateline desc,opendateline desc';
        $limitsql = ' limit '.$limit;
        $files = DB::fetch_all("select * from %t $wheresql $orderby $limitsql",$param);
        return $files;

    }
    public function fetch_recent_files_by_uid($limit =100){
        $files = self::fetch_files_by_uid();
        $folders = self::fetch_folder_by_uid();
        $results = array();
        foreach($folders as $v){
            $results[] = $v;
        }
        foreach($files as $v){
            $results[] = $v;
        }
        return $results;
    }
}