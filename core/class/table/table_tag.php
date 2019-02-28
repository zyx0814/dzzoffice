<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}

class table_tag extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'tag';
        $this->_pk = 'tid';
        parent::__construct();
    }
    public  function insert_data($tags,$idtype=''){
        if(!is_array($tags)) $tags = (array)$tags;
        $tids = array();
        foreach($tags as $v){
            if(preg_match('/^\s*$/',$v)) continue;
            if($result = DB::fetch_first("select tid from %t where idtype = %s and tagname = %s",array($this->_table,$idtype,$v))){
                $tids[$result['tid']] = array('tid'=>$result['tid'],'tagname'=>$v);
            }else{
                $setarr = array(
                    'tagname'=>$v,
                    'uid'=>getglobal('uid'),
                    'username'=>getglobal('username'),
                    'idtype'=>$idtype,
                    'hot' =>0
                );
               $tid =  parent::insert($setarr,1);
               $tids[$tid] =  array('tid'=>$tid,'tagname'=>$v);
            }
        }
        return $tids;
    }
    public function addhot_by_tid($tid,$hot = 1){
        if(!is_array($tid)) $tid=array($tid);
        if($hot>0){
            DB::query("update %t set hot=hot+%d where tid IN(%n)",array($this->_table,$hot,$tid));
        }else{
            DB::query("update %t set hot=hot-%d where tid IN(%n)",array($this->_table,abs($hot),$tid));
        }
    }

    public function fetch_tag_by_tid($tids,$idtype){
        if(!is_array($tids)) $tids = (array)$tids;
        return DB::fetch_all("select tagname,tid from %t where tid in(%n) and idtype = %s ",array($this->_table,$tids,$idtype));
    }
    public function fetch_tid_by_tagname($tagnames,$idtype){
        if(!is_array($tagnames)) $tagnames = (array)$tagnames;
        $searchtag = array();
        foreach ($tagnames as $v){
            $searchtag[] = trim($v);
        }
        $tids = array();
       foreach(DB::fetch_all("select tid from %t where tagname in(%n) and idtype=%s",array($this->_table,$searchtag,$idtype)) as $v){
           $tids[] = $v['tid'];
       }
       if(count($tagnames) != count($tids)){
           $tids = array();
       }
       return $tids;
    }
    public function fetch_tag_byidtype($idtype,$limit=10){
        $tags = array();
        if(!$idtype) return $tags;
        $tags = DB::fetch_all("select tid,tagname from %t where idtype=%s order by hot limit 0,$limit",array($this->_table,$idtype));
        return $tags;
    }
}