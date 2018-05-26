<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2018/3/30
 * Time: 16:44
 */

function filerouteParse($path){
    $prefix = '';
    if(preg_match('/\|/',$path)){
        $patharr = explode('|',$path);
        $prefix = trim($patharr[0]);
        $path = trim($patharr[1]);
    }
    if(substr($path,-1) !== '/'){
        $path = $path.'/';
    }
    if ($prefix) {
        switch ($prefix) {
            case '群组':
                $prefix = 'g';
                break;
            case '机构':
                $prefix = 'o';
                break;
            case '类型':
                $prefix = 'c';
                break;
        }
    }
    $arr = array();
    if ($fid = C::t('resources_path')->fetch_by_path($path, $prefix)) {
        if (preg_match('/c_\d+/', $fid)) {
            $arr['cid'] = str_replace('c_', '', $fid);
        } else {
            $folderarr = C::t('folder')->fetch($fid);
            if ($folderarr['gid']) {
                $arr['gid'] = $folderarr['gid'];
                if ($folderarr['flag'] != 'organization') {
                    $arr['fid'] = $fid;
                }
            } else {
                $arr['fid'] = $fid;
            }
        }
    }
    return $arr;

}
function get_default_select(){

}