<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
class table_resources_tag extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'resources_tag';
        parent::__construct();
    }
    public function delete_by_rid($rid,$tid = ''){
        if(!is_array($rid)) $rid = (array)$rid;
        if($tid && DB::delete($this->_table,'rid in('.dimplode($rid).') and tid = '.$tid)){
            return true;
        }elseif(DB::delete($this->_table,'rid in('.dimplode($rid).')')){
            return true;
        }
        return false;
    }
    public function insert_data($rid,$tagnames){//添加标签

        if(empty($tagnames)) return false;
        //查询文件信息
        if(!$fileinfo = DB::fetch_first("select * from %t where rid = %s",array('resources',$rid))){
            return false;
        }
        $path = C::t('resources_path')->fetch_pathby_pfid($fileinfo['pfid']);
        $path = preg_replace('/dzz:(.+?):/','',$path.$fileinfo['name']);
        //添加标签，如果标签在标签库不存在
        $tags = C::t('tag')->insert_data($tagnames,'explorer');
        $uid = getglobal('uid');
        $username = getglobal('username');
        //得到标签tid数组
        $tids = array();
        foreach($tags as $v){
            $tids[] = $v['tid'];
        }
        //清除没有的标签
        if($return = DB::fetch_all("select rt.tid,t.tagname from %t rt left join %t t on rt.tid = t.tid where rt.rid = %s",array($this->_table,'tag',$rid))){
            $dels = array();
            $deleted = array();
            foreach($return as $value){
                if(!in_array($value['tid'],$tids)){
                    $this->delete_by_rid($rid,$value['tid']);
                    $dels[] = $value['tagname'];
                    $deleted[] = array('tid'=>$value['tid'],'tagname'=>$value['tagname']);
                }
            }
            if(!empty($dels)){
                $dels  = implode(',',$dels);
                $eventdata = array('username'=>$username,'filename'=>$fileinfo['name'],'tagname'=>$dels,'position'=>$path);
                C::t('resources_event')->addevent_by_pfid($fileinfo['pfid'],'del_tags','deltag',$eventdata,$fileinfo['gid'],$rid,$fileinfo['name']);
            }
        }

        //添加新标签
        $addtagnames = array();
        foreach($tags as $k=>$val){
            if(DB::result_first("select count(*) from %t where rid = %s and tid = %d",array($this->_table,$rid,$val['tid']))){
                unset($tags[$k]);
                continue;
            }else{
                $setarr = array(
                    'rid'=>$rid,
                    'tid'=>$val['tid'],
                    'uid'=>$uid,
                    'username'=>$username
                );
                if(parent::insert($setarr,1)){
                    $addtagnames[] = $val['tagname'];
                }
            }
        }
        if(!empty($addtagnames)){
            $addtagnames  = implode(',',$addtagnames);
            $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($fileinfo['pfid'],$fileinfo['gid']);
            $eventdata = array('username'=>$username,'filename'=>$fileinfo['name'],'tagname'=>$addtagnames,'position'=>$path,'hash'=>$hash);
           C::t('resources_event')->addevent_by_pfid($fileinfo['pfid'],'add_tags','addtag',$eventdata,$fileinfo['gid'],$rid,$fileinfo['name']);
        }
        return array('add'=>$tags,'del'=>$deleted);
    }

    public function fetch_tag_by_rid($rid){
        $rid = trim($rid);
        $result = array();
        if($result = DB::fetch_all("select rt.tid,t.tagname from %t rt left join %t t on rt.tid = t.tid where rt.rid = %s and t.idtype = %s",array($this->_table,'tag',$rid,'explorer'))){
            return $result;
        }
        return $result;
    }
    public function fetch_rid_in_tid($tids){
        if (!is_array($tids)) $tids = (array)$tids;
        $rids = array();
        foreach(DB::fetch_all("select rid from %t where tid in(%n)", array($this->_table, $tids)) as $v){
            $rids[] = $v['rid'];
        }
        return $rids;
    }

    public function fetch_rid_by_tid($tids)
    {
        if (!is_array($tids)) $tids = (array)$tids;
        $arr = array();
        //获取标签对应的所有rid
        if ($rids = DB::fetch_all("select rid,tid from %t where tid in(%n)", array($this->_table, $tids))) {
            //遍历rid数组组成tid为键的数组
            $ridarr = array();
            foreach ($rids as $v) {
                $ridarr[$v['tid']][] = $v['rid'];
            }
            //如果rid数组和tid数组数量一致,即有符合条件rid(含有所有标签的rid)
            if (count($ridarr) == count($tids)) {
                $i = 0;
                //遍历rid数组取交集，得到最后结果
                foreach ($ridarr as $k => $val) {
                    if ($i == 0) {
                        $arr = $val;
                    } else {
                        $arr = array_intersect($arr, $val);
                    }
                    $i++;
                }
                return $arr;
            } else {
                return $arr;
            }
        } else {
            return $arr;

        }
    }

}