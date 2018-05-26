<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2018/4/8
 * Time: 17:22
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
$operation = isset($_GET['operation']) ? trim($_GET['operation']):'';
if($operation == 'chkperm'){
    $fid = isset($_GET['path']) ? intval($_GET['path']):0;
    if(perm_check::checkperm_Container($fid,'upload')){
            exit(json_encode(array('success'=>true)));
    }else{
        exit(json_encode(array('error'=>lang('no_privilege'))));
    }
}