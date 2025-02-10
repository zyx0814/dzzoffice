<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;
$uid = $_G['uid'];

if($_G['adminid'] != 1){
   showmessage('no_privilege',dreferer());
}
$do = isset($_GET['do']) ? trim($_GET['do']):'';
if($do == 'filelist'){
    $limit=isset($_GET['perpage'])?intval($_GET['perpage']):100;//默认每页条数
    $page = empty($_GET['page'])?0:intval($_GET['page']);//页码数
    $start = $page;//开始条数
    $total=0;//总条数
    $disp= isset($_GET['disp']) ? intval($_GET['disp']):1;
    $limitsql = "limit $start,$limit";

    $asc = isset($_GET['asc'])?intval($_GET['asc']):1;

    $order = $asc > 0 ? 'ASC' : "DESC";

    switch ($disp) {
        case 0:
            $orderby = 'orgname';
            break;
        case 1:
            $orderby = 'dateline';
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
    $params=array('organization',1);
    $wheresql = " where `type` = %d";
    //日期筛选
    if(isset($_GET['after']) && $_GET['after']){
        $afterdate = strtotime($_GET['after']);
        $wheresql .= " and dateline > %d";
        $params[] = $afterdate;
    }
    if(isset($_GET['before']) && $_GET['before']){
        $beforedate = strtotime($_GET['before']);
        $wheresql .= " and dateline <= %d";
        $params[] = $beforedate;
    }
    //状态筛选
    if(isset($_GET['groupon']) && $_GET['groupon']){
        $on = (intval($_GET['groupon']) == 1) ? 0:1;
        $wheresql .= " and syatemon = %d";
        $params[] = $on;
    }
    //共享目录状态筛选
    if(isset($_GET['diron']) && $_GET['diron']){
        $on = (intval($_GET['diron']) == 1) ? 0:1;
        $wheresql .= " and available = %d";
        $params[] = $on;
    }

    $next = false;
    $nextstart = $start+$limit;
    if(DB::result_first("select count(*) from %t $wheresql $ordersql ",$params) > $nextstart){
        $next = $nextstart;
    }
    $groups = array();
    $explorer_setting = get_resources_some_setting();
    if($explorer_setting['grouponperm']) {
        $groupdata = DB::fetch_all("select * from %t $wheresql $ordersql $limitsql", $params);
        foreach ($groupdata as $v) {
            $v['usernum'] = C::t('organization_user')->fetch_usernums_by_orgid($v['orgid']);
            $v['creater'] = C::t('organization_admin')->fetch_group_creater($v['orgid']);

            if ($v['aid'] > 0) {
                //群组图
                $v['imgs'] = "{$_G['siteurl']}index.php?mod=io&op=thumbnail&size=small&path=" . dzzencode('attach::' . $v['aid']);
            }
           /* $contaions = C::t('resources')->get_contains_by_fid($v['fid'],true);
            $v['ffsize'] = lang('property_info_size', array('fsize' => formatsize($contaions['size']), 'size' => $contaions['size']));
            $v['contain'] = lang('property_info_contain', array('filenum' => $contaions['contain'][0], 'foldernum' => $contaions['contain'][1]));*/
            $groups[] = $v;
        }
    }
    require template('group_list');
}elseif($do=='folder_maxspacesize'){
	$orgid=intval($_GET['orgid']);
	$setspacesize = intval($_GET['maxspacesize']);
	if(!$org=C::t('organization')->fetch($orgid)){
		exit(json_encode(array('error'=>'该机构或群组不存在或被删除')));
	}
	//暂时只允许系统管理员进行空间相关设置
	if($_G['adminid'] != 1){
		exit(json_encode(array('error'=>'没有权限')));
	}
	if($setspacesize != 0){

		//获取允许设置的空间值
		$allowallotspace = C::t('organization')->get_allowallotspacesize_by_orgid($orgid);

		if($allowallotspace < 0) {
			exit(json_encode(array('error' => '可分配空间不足')));
		}

		//获取当前已占用空间大小
		$currentallotspace = C::t('organization')->get_orgallotspace_by_orgid($orgid,0,false);
		//设置值小于当前下级分配总空间值即：当前设置值 < 下级分配总空间
		if($setspacesize > 0 && $setspacesize*1024*1024 < $currentallotspace){

			exit(json_encode(array('error'=>'设置空间值不足,小于已分配空间值！','val'=>$org['maxspacesize'])));

		}
		//上级包含空间限制时，无限制不处理，直接更改设置值
		if($allowallotspace > 0 && ($setspacesize*1024*1024 > $allowallotspace)){

			exit(json_encode(array('error'=>'总空间不足！','val'=>$org['maxspacesize'])));

		}
	}

	//设置新的空间值
	if(C::t('organization')->update($orgid,array('maxspacesize'=>$setspacesize))){

		exit(json_encode(array('msg'=>'success')));

	}else{
		exit(json_encode(array('error'=>'设置不成功或未更改','val'=>$org['maxspacesize'])));
	}

}elseif($do == 'groupmanage'){
    $gids = isset($_GET['gid']) ? $_GET['gid']:'';
    if(!$orgs = DB::fetch_all("select * from %t where orgid in(%n)",array('organization',$gids))){
        exit(json_encode(array('error'=>lang('explorer_do_failed'))));
    }
    //暂无对应事件记录，此处数据方便之后加
   /* $orgarr = array();
    $gidarr = array();
    foreach($orgs as $v){
        $orgarr['orgid'] = $v;
        $gidarr[] = $v['orgid'];
    }*/
    $setarr = array();
    if(isset($_GET['groupon'])){
        $setarr['manageon']= intval($_GET['groupon']);
    }
    if(isset($_GET['diron'])){
        $setarr['diron']= intval($_GET['diron']);
    }
    if(!empty($setarr)){
        $gidstr =  "'".implode("','", is_array($gids) ? $gids : array($gids))."'";
        if(DB::update('organization',$setarr,"orgid in(".$gidstr.")")){
            exit(json_encode(array('success'=>true)));
        }else{
            exit(json_encode(array('error'=>lang('explorer_do_failed'))));
        }
    }else{
        exit(json_encode(array('error'=>lang('explorer_do_failed'))));
    }

}elseif($do == 'delgroup'){
    $gids = isset($_GET['gid']) ? $_GET['gid']:'';
    if(!is_array($gids)) $gids = array($gids);
    if(!$orgs = DB::fetch_all("select orgid,orgname from %t where orgid in(%n)",array('organization',$gids))){
        exit(json_encode(array('error'=>lang('explorer_do_failed'))));
    }
    $orgarr = array();
    foreach($orgs as $v){
        $orgarr[$v['orgid']]['name']= $v['orgname'];
    }
    $forgid = intval($_GET['forgid']);
    $arr = array();
    foreach($gids as $orgid){
        $return = C::t('organization') -> delete_by_orgid($orgid);
            if ($return['error']) {
                $arr['sucessicoids'][$orgid]=$orgid;
                $arr['msg'][$orgid]=$return['error'];
                $arr['name'][$orgid] = $orgarr[$v['orgid']]['name'];
            }else{
                $arr['sucessicoids'][$orgid]=$orgid;
                $arr['msg'][$orgid]='success';
                $arr['name'][$orgid] = $orgarr[$v['orgid']]['name'];
            }
    }
    exit(json_encode($arr));

}else{
    $limit=isset($_GET['perpage'])?intval($_GET['perpage']):100;//默认每页条数
    $page = empty($_GET['page'])?0:intval($_GET['page']);//页码数
    $start = $page;//开始条数
    $total=0;//总条数
    $disp= isset($_GET['disp']) ? intval($_GET['disp']):1;
    $limitsql = "limit $start,$limit";

    $asc = isset($_GET['asc'])?intval($_GET['asc']):1;

    $order = $asc > 0 ? 'ASC' : "DESC";

    switch ($disp) {
        case 0:
            $orderby = 'orgname';
            break;
        case 1:
            $orderby = 'dateline';
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
    $next = false;
    $nextstart = $start+$limit;
    if(DB::result_first("select count(*) from %t where `type` = %d $ordersql $limitsql",array('organization',1)) > $nextstart){
        $next = $nextstart;
    }
    $groups = array();
    $explorer_setting = get_resources_some_setting();
    if($explorer_setting['grouponperm']) {
        $groupdata = DB::fetch_all("select * from %t where `type` = %d $ordersql $limitsql", array('organization', 1));
        foreach ($groupdata as $v) {
            $v['usernum'] = C::t('organization_user')->fetch_usernums_by_orgid($v['orgid']);
            $v['creater'] = C::t('organization_admin')->fetch_group_creater($v['orgid']);

            if ($v['aid'] > 0) {
                //群组图
                $v['imgs'] = "{$_G['siteurl']}index.php?mod=io&op=thumbnail&size=small&path=" . dzzencode('attach::' . $v['aid']);
            }
           /* $contaions = C::t('resources')->get_contains_by_fid($v['fid']);
            $v['ffsize'] = lang('property_info_size', array('fsize' => formatsize($contaions['size']), 'size' => $contaions['size']));
            $v['contain'] = lang('property_info_contain', array('filenum' => $contaions['contain'][0], 'foldernum' => $contaions['contain'][1]));*/
            $groups[] = $v;
        }
    }
    $groupsnumber = count($groups);
    require template('groupmanage');
}

