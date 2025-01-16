<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2018/2/6
 * Time: 18:40
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
if($group['type'] == 0 && $_G['adminid'] != 1){
    exit(json_encode(array('error'=>lang('no_privilage'))));
}
if($group['type'] == 1 && $perm < 2){
    exit(json_encode(array('error'=>lang('no_privilage'))));
}
$return = C::t('organization') -> delete_by_orgid($gid);
if(isset($return['error'])){
    exit(json_encode(array('error'=>$return['error'])));
}else{
    exit(json_encode(array('success'=>true)));
}
