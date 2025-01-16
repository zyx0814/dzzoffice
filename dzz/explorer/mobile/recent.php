<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
$operation = isset($_GET['operation']) ? trim($_GET['operation']):'';
if($operation == 'filelist'){
    $sid=htmlspecialchars($_GET['sid']);
    $limit=isset($_GET['perpage'])?intval($_GET['perpage']):20;//默认每页条数
    $page = empty($_GET['page'])?1:intval($_GET['page']);//页码数
    $start = ($page-1)*$perpage;//开始条数
    $limitsql = "limit $start,$limit";
    $perpage=25;
    $disp = isset($_GET['disp']) ? intval($_GET['disp']):3;

    $keyword = isset($_GET['keyword']) ? urldecode($_GET['keyword']) : '';

    $asc = isset($_GET['asc'])?intval($_GET['asc']):1;

    $order = $asc > 0 ? 'ASC' : "DESC";

    //最近使用文件
    $explorer_setting = get_resources_some_setting();
    $recents = C::t('resources_statis')->fetch_recent_files_by_uid();
    $result = $data =$sortarr = array();
    $folderids=$folderdata=array();
    foreach($recents as $val){
        if($val = C::t('resources')->fetch_by_rid($val['rid'],false,true)){
            if(!$explorer_setting['useronperm'] && $val['gid'] == 0){
                continue;
            }
            if(!$explorer_setting['grouponperm'] && $val['gid'] > 0){
                if(DB::result_first("select `type` from %t where orgid = %d",array('organization',$val['gid'])) == 1){
                    continue;
                }
            }
            if(!$explorer_setting['orgonperm'] && $val['gid'] > 0){
                if(DB::result_first("select `type` from %t where orgid = %d",array('organization',$val['gid'])) == 0){
                    continue;
                }
            }
            $folderids[$val['pfid']]=$val['pfid'];
            if($val['type']=='folder') {
                $folderids[$val['oid']]=$val['oid'];
                $val['filenum'] = $val['contaions']['contain'][0];
                $val['foldernum'] = $val['contaions']['contain'][1];
            }else{
                $val['monthdate'] = dgmdate($val['dateline'],'m-d');
                $val['hourdate'] = dgmdate($val['dateline'],'H:i');
            }
            if($val['type'] == 'image'){
                $val['img'] = DZZSCRIPT.'?mod=io&op=thumbnail&width=45&height=45&path=' . dzzencode('attach::' . $val['aid']);
                $val['imgpath'] =  DZZSCRIPT.'?mod=io&op=thumbnail&size=large&path=' .dzzencode('attach::' . $val['aid']);
            }
            if($val['isdelete'] == 0){
                if($disp == 0) $sortarr[$val['rid']] = $val['name'];
                elseif ($disp ==  1) $sortarr[$val['rid']] = $val['size'];
                elseif ($disp ==  3) $sortarr[$val['rid']] = $val['dateline'];
                $result[$val['rid']]=$val;
            }
        }

    }
    //获取目录信息
    foreach($folderids as $fid){
        if($folder = C::t('folder')->fetch_by_fid($fid)) $folderdata[$fid] =$folder;
    }
    if($asc){
        asort($sortarr);
    }else{
       arsort($sortarr);
    }
    foreach($sortarr as $k=>$v){
        $data[$k] = $result[$k];
    }
    $folderjson = json_encode($folderdata);
    //返回数据
    $return=array(
        'data'=>($data) ? $data:array(),
        'param'=>array(
            'disp'=>$disp,
            'view'=>$iconview,
            'bz'=>$bz,
            'datatotal'=>count($data),
            'asc'=>$asc,
            'keyword'=>$keyword,
            'localsearch'=>$bz?1:0
        )
    );
    $return = json_encode($return);
    $return = str_replace("'","\'",$return);
    include template('mobile/filelist');

}else{
    include template('mobile/recent');
}