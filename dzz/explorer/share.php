<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;
$uid = $_G['uid'];
$do = isset($_GET['do']) ? trim($_GET['do']):'';
if($do == 'delshare'){
    $shareid = explode(',',trim($_GET['shareid']));
    $return = array();
    foreach($shareid as $v){
        $result = C::t('shares')->delete_by_id($v);
        if($result['success']){
            $return['msg'][$v]=$result;
        }elseif ($result['error']){
            $return['msg'][$v] = $result['error'];
        }
    }
    exit(json_encode($return));
}elseif($do == 'filelist'){
    //分页
    $sid = $_GET['sid'];
    $perpage=isset($_GET['perpage'])?intval($_GET['perpage']):100;//默认每页条数
    $page = empty($_GET['page'])?1:intval($_GET['page']);//页码数
    $start = ($page - 1)*$perpage;//开始条数
    $limitsql = "limit $start,$perpage";
    $disp = isset($_GET['disp']) ? intval($_GET['disp']):3;

    $keyword = isset($_GET['keyword']) ? urldecode($_GET['keyword']) : '';
    $asc = intval($_GET['asc']);

    $order = $asc > 0 ? 'ASC' : "DESC";
    switch ($disp) {
        case 0:
            $orderby = 'title';
            break;
        case 1:
            $orderby = 'downs';
            break;
        case 2:
            $orderby = 'views';
            break;
        case 3:
             $orderby = 'dateline';
             break;
        case 4:
            $orderby = 'endtime';
            break;

    }
    $ordersql='';
    if(is_array($orderby)){
        foreach($orderby as $key=>$value){
            $orderby[$key]=$value.' '.$order;
        }
        $ordersql=' ORDER BY '.implode(',',$orderby);
    }elseif($orderby){
        $ordersql=' ORDER BY '.$orderby.' '.$order;
    }
    $data = C::t('shares')->fetch_all_share_file($limitsql,$ordersql);
    $disp = isset($_GET['disp']) ? intval($_GET['disp']) : 3;//文件排序
    $iconview=4;//排列方式
    if(count($data) >= $perpage){
        $total = $start + $perpage*2 -1;
    }else{
        $total = $start + count($data);
    }
    if(!$json_data=json_encode($data)) $data=array();
    if(!$json_data=json_encode($folderdata)) $folderdata=array();
    //返回数据
    $return=array(
        'sid'=>$sid,
        'total'=>$total,
        'data'=>$data?$data:array(),
        'folderdata'=>$folderdata?$folderdata:array(),
        'param'=>array(
            'disp'=>$disp,
            'view'=>$iconview,
            'page'=>$page,
            'perpage'=>$perpage,
            'bz'=>$bz,
            'total'=>$total,
            'asc'=>$asc,
            'keyword'=>$keyword,
            'tags'=>$tags,
            'exts'=>$exts,
            'localsearch'=>$bz?1:0,
            'fid'=>'',
        )
    );
    exit(json_encode($return));
}else{
    require template('share_content');
}
