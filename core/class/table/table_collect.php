<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
class table_collect extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'collect';
        $this->_pk = 'cid';
        parent::__construct();
    }
    public function delete_by_cid($cid){
        $lid=intval($cid);
        $link=self::fetch($cid);
        if($link['cid']){
            $copys=DB::result_first("select copys from ".DB::table('collect')." where cid='{$link[cid]}' and type = 'link'");
            if($copys<=1){
                return DB::delete('collect',"cid='{$link[cid]}'");
            }else{
               return  DB::update('collect',array('copys'=>$copys-1),"cid='{$link[cid]}'");
            }
        }
    }
    public function addcopy_by_cid($cid,$ceof=1){
        if(!is_array($cid)) $aids=array($cid);

        if($ceof>0){
            DB::query("update %t set copys=copys+%d where cid IN(%n)",array($this->_table,$ceof,$cid));
        }else{
            DB::query("update %t set copys=copys-%d where cid IN(%n)",array($this->_table,abs($ceof),$cid));
        }
    }
}