<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
class table_resources_collect extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'resources_collect';
        $this->_pk = 'id';
        parent::__construct();
    }
    public function add_collect_by_rid($rids){
        global $_G;
        if(!is_array($rids)) $rids = (array)$rids;
        $i = 0;
        $arr = array();
        foreach($rids as $v){
            //获取收藏文件基本信息
            if($data = C::t('resources')->fetch_info_by_rid($v,true)){
                $setarr = array(
                    'rid'=>$v,
                    'uid'=>$_G['uid'],
                    'username'=>$_G['username'],
                    'dateline'=>time(),
                    'pfid'=>$data['pfid']
                );
                if (!perm_check::checkperm_Container($data['pfid'], 'read2') && !($_G['uid'] == $data['uid'] && perm_check::checkperm_Container($data['pfid'], 'read1'))) {
                    continue;
                }
                if(self::add_collect($setarr)){
                    //处理数据
                        $arr['msg'][$v]='success';
                        $ridarr[]= $v;
                        $i++;
                }else{
                    $arr['msg'][$v]=array('error'=>lang('explorer_do_failed'));
                }
            }else{
                continue;
            }

        }
        return $arr;
    }
    public function add_collect($setarr){
        //如果已经加入收藏，不允许重复收藏
        if(DB::result_first("select id from %t where rid = %s and uid = %d",array($this->_table,$setarr['rid'],$setarr['uid']))){
            return false;
        }
        //加入收藏
        if($insert = parent::insert($setarr,1)){
            return $insert;
        }else{
            return false;
        }
    }
    //取消当前用户收藏的某文件
    public function cancle_clooect_by_rid_uid($rid,$uid){
        if(!$collectinfo = DB::fetch_first("select * from %t where rid = %s",array($this->_table,$rid))){
            return array('error'=>lang('collect_file_not_exists'));
        }
        if($collectinfo['uid'] != $uid){
            return array('error'=>lang('no_privilege'));
        }
        if(DB::delete($this->_table,array('rid'=>$rid,'uid'=>$uid))){
            $fileinfo = C::t('resources')->fetch_info_by_rid($rid);
            return array('success'=>true,'rid'=>$rid,'name'=>$fileinfo['name']);
        }
    }
    //删除当前用户对某些文件的收藏
    public function delete_usercollect_by_rid($rids){
        if(!is_array($rids)) $rids = (array)$rids;
        $uid = getglobal('uid');
        $i=0;
        $return = array();
        foreach($rids as $v){
            $return  = self::cancle_clooect_by_rid_uid($v,$uid);
            if(!isset($return['error'])){
                //处理数据
                $arr['msg'][$return['rid']]='success';
                $ridarr[]= $return['rid'];
                $i++;
            }else{
                $arr['msg'][$return['rid']]=$return['error'];
            }
        }

       return $arr;
    }
    //删除某文件的收藏
    public function delete_by_rid($rid){
        if(!is_array($rid)) $rid = (array)$rid;
        if(!$collectinfo = DB::fetch_first("select * from %t where rid in (%n)",array($this->_table,$rid))){
            return array('error'=>lang('collect_file_not_exists'));
        }
        if(DB::delete($this->_table,'rid in('.dimplode($rid).')')){
            return true;
        }
        return false;
    }
    //清空当前用户的所有收藏
    public function delete_by_uid(){
        $uid = getglobal('uid');
        if(DB::delete($this->_table,array('uid'=>$uid))){
            return true;
        }else{
            return false;
        }
    }
    //查询当前用户所有收藏
    public function fetch_by_uid($limitsql = '',$ordersql = ''){
        $uid = getglobal('uid');
        if($return = DB::fetch_all("select * from %t where uid = %d  $ordersql $limitsql",array($this->_table,$uid))){
            return $return;
        }else{
            return false;
        }
    }
    public function fetch_rid_by_uid(){
        $uid = getglobal('uid');
        if($return = DB::fetch_all("select rid from %t where uid = %d",array($this->_table,$uid))){
            return $return;
        }else{
            return false;
        }
    }
    public function fetch_by_rid($rid){
        $rid = trim($rid);
        $uid = getglobal('uid');
        if(DB::result_first("select count(*) from %t where uid = %d and rid = %s",array($this->_table,$uid,$rid))){
            return true;
        }else{
            return false;
        }
    }
}