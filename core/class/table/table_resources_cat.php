<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
class table_resources_cat extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'resources_cat';
        $this->_pk = 'id';
        parent::__construct();
    }
    //添加搜索类型
    public function insert_cat($setarr){
        //搜索类型同一用户不能重名
        if(DB::result_first("select count(*) from %t where catname = %s and uid = %d",array($this->_table,$setarr['catname'],$setarr['uid'])) > 0){
            return array('error'=>true,'msg'=>lang('exploder_name_repeat'));
        }
        //处理标签数据
        if(isset($setarr['tag'])){
            $setarr['tag'] = explode(',',$setarr['tag']);
            //将标签放入标签表，如果有并且为该应用下，则自动增加使用数
            $tagdata = C::t('tag')->insert_data($setarr['tag'],'explorer');
            $tags  = '';
            foreach($tagdata as $v){
                $tags .= $v['tid'].',';
            }
            $setarr['tag'] = substr($tags,0,-1);
        }
        if($insertid = parent::insert($setarr,1)){
            return array('success'=>true,'insert'=>$insertid);
        }
        return array('error'=>true,'msg'=>lang('exploder_add_failed'));
    }

    public function update($catid,$setarr){
        if(!$catinfo = parent::fetch($catid)) return false;
        if(isset($setarr['tag'])){
            $oldtids = explode(',',$catinfo['tag']);
            $setarr['tag'] = array_filter(explode(',',$setarr['tag']));
            if(!empty($setarr['tag'])){
                //将标签放入标签表，如果有并且为该应用下，则自动增加使用数
                $tagdata = C::t('tag')->insert_data($setarr['tag'],'explorer');
                $tags  = '';
                foreach($tagdata as $v){
                    $tags .= $v['tid'].',';
                    $newtids[] = $v['tid'];
                }
                $setarr['tag'] = substr($tags,0,-1);
            }else{
              $setarr['tag'] = '';
            }
            C::t('tag')->addhot_by_tid($oldtids,-1);
        }
        return parent::update($catid,$setarr);
    }
    //查询搜索类型
    public function fetch_by_id($id){
        $id = intval($id);
        return parent::fetch($id);
    }
    //删除搜索类型
    public function del_by_id($id){
        $id = intval($id);
        if(!$cat = parent::fetch($id)){
            return false;
        }
        if($cat['dafault'] == 1){//系统默认不能删除
            return false;
        }
       return  parent::delete($id);
    }
    public function fetch_by_uid($uid){
        return DB::fetch_all("select * from %t where uid = %d order by `default` desc",array($this->_table,$uid));
    }
    //查询当前数据最近的一条
    public function fetch_rencent_id($id){
        global $_G;
        return DB::result_first("select id from %t where id < %d and uid = %d order by id desc",array($this->_table,$id,$_G['uid']));
    }

}