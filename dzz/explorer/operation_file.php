<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
$do = isset($_GET['do']) ? trim($_GET['do']):'';
if($do == 'removefile'){//移除文件到回收站
    $rid = isset($_GET['rid']) ? trim($_GET['rid']):'';
    $rid = explode(',',$rid);
    $return = C::t('resources')->recyle_by_rid($rid);
    if(!empty($return)){
        showTips(array('success'=>true,'dels'=>$return),'json');
    }else{
        showTips(array('error'=>true),'json');
    }
}elseif($do == 'recoverfile'){//恢复文件
    if(isset($_GET['gid'])){//恢复群组文件
        $gid = intval($_GET['gid']);
        $ids = array();
        foreach(C::t('resources_recyle')->fetch_by_gid($gid) as $v){
            $ids[] = $v['id'];
        }
    }elseif (isset($_GET['id'])){//多选恢复
        $id = $_GET['id'];
        $ids = explode(',',$id);
    }elseif (isset($_GET['recover']) && $_GET['recover']){//恢复回收站文件
        $ids = array();
        foreach(C::t('resources_recyle')->fetch_all_recycle_data() as $v){
            $ids[] = $v['id'];
        }
    }
    $return = C::t('resources_recyle')->recover_file_by_id($ids);
    if($return){
        showTips(array('success'=>true,'idarr'=>$return),'json');
    }else{
        showTips(array('error'=>true),'json');
    }
}elseif($do == 'deletefinally'){//彻底删除文件
    if(isset($_GET['gid'])){//清空群组回收站
        $gid = intval($_GET['gid']);
        $ids = array();
        foreach(C::t('resources_recyle')->fetch_by_gid($gid) as $v){
            $ids[] = $v['id'];

        }
    }elseif (isset($_GET['id'])){//多选删除
        $id = $_GET['id'];
        $ids = explode(',',$id);
    }elseif (isset($_GET['empty']) && $_GET['empty']){//清空回收站
        $ids = array();
        $rids[] = array();
        foreach(C::t('resources_recyle')->fetch_all_recycle_data() as $v){
            $ids[] = $v['id'];
        }
    }
    $return  = C::t('resources_recyle')->delete_by_id($ids);
    if($return){
        showTips(array('success'=>true,'idarr'=>$return),'json');
    }else{
        showTips(array('error'=>true,'msg'=>$return['error']),'json');
    }
}elseif($do == 'rename'){//更改名字
    $rid = isset($_GET['rid']) ? trim($_GET['rid']):'';
    $newname = isset($_GET['newname']) ? trim($_GET['newname']):'';
    $return = C::t('resources')->rename_by_rid($rid,$newname);
    if($return['newname']){
        $statisarr = array(
            'comments'=>1,
            'views'=>0,
            'downs'=>0,
        );
        C::t('resources_statis')->add_statis($rid,$setarr);
        showTips(array('newname'=>$return['newname']),'json');
    }else{
        showTips(array('error'=>true,'msg'=>$return['error']),'json');
    }
}elseif($do == 'emptycollect'){//清空收藏
    $empty = isset($_GET['empty']) ? $_GET['empty']:'';
    if(C::t('resources_collect')->delete_by_uid()){
        showTips(array('success'=>true),'json');
    }else{
        showTips(array('error'=>true),'json');
    }
}elseif($do == 'copyfile'){//复制或剪切文件
    $rid = isset($_GET['rid']) ? trim($_GET['rid']):'';
    $copytype = isset($_GET['copytype']) ? intval($_GET['copytype']):1;
    $return =   C::t('resources_clipboard')->insert_data($rid,$copytype);
    if(!$return['error']){
        showTips(array('success'=>true,$rid=>$return),'json');
    }else{
        showTips(array('error'=>true,'msg'=>$return['error']),'json');
    }
}elseif($do == 'pastefile'){//粘贴文件
    $rid = isset($_GET['rid']) ? trim($_GET['rid']):'';
    $rids = isset($_GET['rids']) ? trim($_GET['rids']):'';

    $ridarr = explode(',',$rid);
    $infoarr = C::t('resources')->fetch_by_rid($ridarr[0]);
    if(!perm_check::checkperm_Container($infoarr['pfid'],'upload')){
        return false;
    }else{
        if($copyinfo = DB::fetch_first("select * from %t where uid = %d",array('resources_clipboard',$uid))){
            $iscopy = ($copyinfo['copytype'] == 2) ? false:true;
        }else{
            return false;
        }
        $ridsarr = explode(',',$rids);
        $return = array();
        foreach($ridsarr as $v){
            $return[]=IO::copyTo($v,$infoarr['pfid'],$iscopy);
        }
        if($return){
            C::t('resources_clipboard')->delete_by_uid();
        }
        include template('clipboard');

        exit();
    }
}elseif($do =='downfile'){//下载文件
    $rid = isset($_GET['rid']) ? trim($_GET['rid']):'';
    $rids = explode(',',$rid);
    IO::download($rids,'');
}elseif($do == 'addstatis'){//统计操作
    $rid = isset($_GET['rid']) ? $_GET['rid']:'';
    $flag = isset($_GET['flag']) ? $_GET['flag']:'';
    $setarr = array(
        'views'=>0,
        'downs'=>0,
        'edits'=>0
    );
    if($flag) $setarr[$flag] = 1;
    if(C::t('resources_statis')->add_statis($rid,$setarr)){
        exit(json_encode(array('success'=>true)));
    }else{
        exit(json_encode(array('error'=>true)));
    }

}elseif($do == 'uploadnewVersion'){
    $rid = isset($_GET['rid']) ? $_GET['rid']:'';
    $setarr = array(
        'uid'=>$uid,
        'username'=>getglobal('username'),
        'vname'=>getstr($_GET['name']),
        'aid'=>intval($_GET['aid']),
        'size'=>intval($_GET['size']),
        'ext'=>$_GET['ext'],
        'dateline'=>TIMESTAMP
    );
    $return = C::t('resources_version')->add_new_version_by_rid($rid,$setarr);
    if($return['error']){
        exit(json_encode(array('error'=>$return['error'])));
    }else{
        $statisdata = array(
            'uid'=>getglobal('uid'),
            'edits'=>1,
            'editdateline'=>TIMESTAMP
        );
        C::t('resources_statis')->add_statis_by_rid($rid,$statisdata);
        $resources = C::t('resources')->fetch_by_rid($rid);
        $event = C::t('resources_event')->fetch_by_ridlast($rid);
        exit(json_encode(array('success'=>true,'data'=>$return,'filedata'=>$resources,'eventdata'=>$event)));
    }
}