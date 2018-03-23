<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
class table_resources_clipboard extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'resources_clipboard';
        $this->_pk = 'id';
        parent::__construct();
    }
    //$copytype默认值为1，为1是复制，为2是剪切
    public function insert_data($paths,$copytype = 1){
        $uid = getglobal('uid');
        if(!is_array($paths)) $paths = (array)$paths;
        $rids = '';
        foreach(DB::fetch_all("select rid,uid,pfid from %t where rid in (%n) and isdelete < 1",array('resources',$paths)) as $v){
            $pfid = $v['pfid'];
            $perm = perm_check::getPerm($pfid);
            if(!perm_binPerm::havePower('copy2', $perm) && !(perm_binPerm::havePower('copy1', $perm) && $uid == $v['uid']) ){
                continue;
            }
            $rids .= $v['rid'].',';
        }
        if(!$rids) return array('error'=>lang('no_privilege'));

        $rids = substr($rids,0,-1);
        $setarr = array(
            'uid'=>getglobal('uid'),
            'username'=>getglobal('username'),
            'dateline'=>time(),
            'files'=>$rids,
            'copytype'=>$copytype
        );
       self::delete_by_uid();
        if($copyid = parent::insert($setarr,1)){
            return array('rid'=>$rids,'copyid'=>$copyid);
        }
        return array('error'=>lang('sysem_busy'));
    }

    public  function delete_by_uid(){
        $uid = getglobal('uid');
        if(DB::result_first("select count(*) from %t where uid = %d",array($this->_table,$uid)) > 0){
            return DB::delete($this->_table,array('uid'=>$uid));
        }
    }
    public function fetch_by_uid(){
        $uid = getglobal('uid');
        if($return = DB::fetch_first("select * from %t where uid = %d",array($this->_table,$uid))){
            return $return;
        }
        return false;
    }
    //去掉粘贴板已删除的rid
    public function update_data_by_delrid($rids)
    {
        if (!is_array($rids)) $rids = (array)$rids;
        if(empty($rids)) return ;
        $datas = array();
        foreach ($rids as $v) {
            foreach (DB::fetch_all("select id,files from %t where find_in_set(%s,files)", array($this->table, $v)) as $val) {
                if($val['files']){
                    $files = explode(',', $val['files']);
                    $key = array_search($v,$files);
                    unset($files[$key]);
                    if(empty($files)){
                        parent::delete($val['id']);
                    }else{
                        $files = implode(',', $files);
                        parent::update($val['id'],array('files' => $files));
                    }
                }

            }

        }
        return true;
    }
}