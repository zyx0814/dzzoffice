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
        $typearr = array();
        foreach(DB::fetch_all("select rid,uid,pfid,gid,oid,`type` from %t where rid in (%n) and isdelete < 1",array('resources',$paths)) as $v){
            $pfid = $v['pfid'];
            if($copytype == 1){
                if($v['type'] == 'folder'){
                    $return = C::t('resources')->check_folder_perm($v,'copy');
                    if($return['error']) return array('error'=>$return['error']);
                    $typearr[] = 1;
                }else{
                    if (!perm_check::checkperm_Container($pfid, 'copy2') && !($uid == $v['uid'] && perm_check::checkperm_Container($pfid, 'copy1'))) {
                        return array('error'=>lang('no_privilege'));
                    }
                    $typearr[] = 2;
                }
            }else{
                if($v['type'] == 'folder'){
                    $return = C::t('resources')->check_folder_perm($v,'cut');
                    if($return['error']) return array('error'=>$return['error']);
                    $typearr[] = 1;
                }else{
                    if (!perm_check::checkperm_Container($pfid, 'delete2') && !($uid == $v['uid'] && perm_check::checkperm_Container($pfid, 'delete1'))) {
                        return array('error'=>lang('no_privilege'));
                    }
                    $typearr[] = 2;
                }
            }
            $rids .= $v['rid'].',';
        }
        $typearr = array_unique($typearr);
        if(count($typearr) > 1){
            $type = 3;
        }else{
            $type = $typearr[0];
        }
        if(!$rids) return array('error'=>lang('no_privilege'));

        $rids = substr($rids,0,-1);
        $setarr = array(
            'uid'=>getglobal('uid'),
            'username'=>getglobal('username'),
            'dateline'=>time(),
            'type'=>$type,
            'files'=>$rids,
            'copytype'=>$copytype
        );
       self::delete_by_uid();
        if($copyid = parent::insert($setarr,1)){
            return array('rid'=>$rids,'copyid'=>$copyid,'type'=>$type);
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
    public function fetch_user_paste_type(){
        $uid = getglobal('uid');
        return DB::result_first("select `type` from %t where uid = %d",array($this->_table,$uid));
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