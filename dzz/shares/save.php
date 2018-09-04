<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2018/3/9
 * Time: 16:11
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
$dzzrids = isset($_GET['dzzrids']) ? trim($_GET['dzzrids']) :'';
if(!$dzzrids){
    $dzzrids = $_GET['token']['paths'];
}
$icoids = explode(',', $dzzrids);
$data = array();
$ridarr = array();
$fid = isset($_GET['fid']) ? intval($_GET['fid']) : 0;
$folder = C::t('folder')->fetch($fid);
$explorer_setting = get_resources_some_setting();
$doing = true;
if($folder['gid'] > 0){
    $group = C::t('organization')->fetch($folder['gid']);
    if($group['type'] == 0 &&  !$explorer_setting['orgonperm']){
        $doing = false;
    }elseif($group['type'] == 0 &&  !$explorer_setting['grouponperm']){
        $doing = false;
    }elseif(!$group['manageon'] || !$group['diron']){
        $doing = false;
    }elseif(!perm_check::checkperm_Container($fid,'upload')){
        $doing = false;
    }
}else{
    if(!$explorer_setting['useronperm']) {
        $doing = false;
    }
}
if(!$doing){
    $data['error'][$fid] = lang('no_privilege');
    $data['msg'][$fid] = 'error';
    $data['name'][$fid] = '';
    if(isset($_GET['token'])){
        exit(json_encode(array('error'=>lang('no_privilege'))));
    }else{
        exit(json_encode($data));
    }

}

$totalsize = 0;
$icos = $folderids = array();
$i = 0;
$errorarr = array();
foreach ($icoids as $icoid) {
    $rid = dzzdecode($icoid);
    if (empty($rid)) {
        $data['error'][] = $rid . '：' . lang('forbid_operation');
        $data['msg'][] = 'error';
        continue;
    }
    $return = IO::CopyTo($rid, $fid, 1);
    if ($return['success'] === true) {
        $data['icoarr'][] = $return['newdata'];
        if (!$tbz) {
            addtoconfig($return['newdata'], $ticoid);
        }
        $data['sucessicoids'][$return['rid']] = $return['newdata']['rid'];
        $data['msg'][$return['newdata']['rid']] = 'success';
        $data['name'][$return['newdata']['rid']] = $return['newdata']['name'];
        $ridarr[] = $return['newdata']['rid'];
        $i++;
    } else {
        $data['name'][$return['newdata']['rid']] = $return['newdata']['name'];
        $data['error'][$return['newdata']['rid']] = $return['newdata']['name'] . ':' . $return['error'];
        $data['msg'][$return['newdata']['rid']] = 'error';
        $errorarr[] = $return['error'];
    }
}
if(isset($_GET['token'])){
    if(count($errorarr)){
        exit(json_encode(array('error'=>$errorarr[0])));
    }else{
        exit(json_encode(array('success'=>true)));
    }
}else{
    exit(json_encode($data));
}

