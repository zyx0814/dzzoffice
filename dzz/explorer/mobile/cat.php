<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;
$uid = $_G['uid'];
$operation = isset($_GET['operation']) ? trim($_GET['operation']):'';
if($operation == 'filelist'){
    $perpage=isset($_GET['perpage'])?intval($_GET['perpage']):10;//默认每页条数
    $page = empty($_GET['page'])?1:intval($_GET['page']);//页码数
    $start = ($page-1)*$perpage;//开始条数
    $total=0;//总条数
    $disp=intval($_GET['disp']);
    $catid=empty($_GET['cid'])?0:$_GET['cid'];//id
    $marker=empty($_GET['marker'])?'':trim($_GET['marker']);
    $data=array();
    $limitsql = "limit $start,$perpage";

    $keyword = isset($_GET['keyword']) ? urldecode($_GET['keyword']) : '';

    $asc = isset($_GET['asc'])?intval($_GET['asc']):1;

    $order = $asc > 0 ? 'ASC' : "DESC";

    switch ($disp) {
        case 0:
            $orderby = 'r.name';
            break;
        case 1:
            $orderby = 'r.size';
            break;
        case 2:
            $orderby = array('r.type', 'r.ext');
            break;
        case 3:
            $orderby = 'r.dateline';
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
    $wheresql=' where r.isdelete < 1';
    $param=array('resources','folder');
    $folderdata=array();
    $folderids=array();
    $cats = C::t('resources_cat')->fetch_by_id($catid);
    //如果接收到后缀名条件，则按指定后缀名搜索
    $exts = isset($_GET['exts'])?getstr($_GET['exts']):'';
    $tags = isset($_GET['tags'])?getstr($_GET['tags']):'';
    if($exts){
        $extarr = explode(',',str_replace('.','',$exts));
        $wheresql .= " and r.ext IN (%n)";
        $param[]=$extarr;
    }else{
        if($cats['ext']){
            $extarr = explode(',',str_replace('.','',$cats['ext']));
            $wheresql .= " and r.ext IN (%n)";
            $param[]=$extarr;
        }else{
            $wheresql .= " and 0 ";
        }
    }
    //如果接收到标签条件
    if($tags){
        $tagsarr = explode(',',$tags);
        $rids = C::t('resources_tag')->fetch_rid_by_tid($tagsarr);
        if(count($rids) < 1){
            $wheresql .= " and 0";
        }else{
            $wheresql .= " and r.rid IN (%n)";
            $param[]=$rids;
        }

    }elseif($cats['tag']){
        //查询标签表中有对应rid
        if(!empty($tagsarr)){
            $rids = C::t('resources_tag')->fetch_rid_in_tid($tagsarr);
            if(count($rids) < 1){
                $wheresql .= " and 0";
            }else{
                $wheresql .= " and r.rid IN (%n)";
                $param[]=$rids;
            }
        }
    }
    $explorer_setting = get_resources_some_setting();
    $orgids = C::t('organization')->fetch_all_orgid();//获取所有有管理权限的部门
    $powerarr=perm_binPerm::getPowerArr();

    $or=array();
    //用户自己的文件
    if($explorer_setting['useronperm']){
        $or[]="(r.gid=0 and r.uid=%d)";
        $param[]=$_G['uid'];
    }
    //我管理的群组或部门的文件
    if($orgids['orgids_admin']){
        $or[]="r.gid IN (%n)";
        $param[]=$orgids['orgids_admin'];
    }
    //我参与的群组的文件
    if($orgids['orgids_member']){
        $or[]="(r.gid IN(%n) and ((f.perm_inherit & %d) OR (r.uid=%d and f.perm_inherit & %d)))";
        $param[]=$orgids['orgids_member'];
        $param[]=$powerarr['read2'];
        $param[]=$_G['uid'];
        $param[]=$powerarr['read1'];
    }
    if($or) $wheresql .=" and (".implode(' OR ',$or).")";
    $data=array();
    $folderids=$folderdata=array();
    if($total=DB::result_first("SELECT COUNT(*) FROM %t r LEFT JOIN %t f ON r.pfid=f.fid $wheresql", $param)){
        foreach(DB::fetch_all("SELECT rid FROM %t r LEFT JOIN %t f ON r.pfid=f.fid $wheresql $ordersql $limitsql", $param) as $value){
            if($arr=C::t('resources')->fetch_by_rid($value['rid'])){
                $folderids[$arr['pfid']]=$arr['pfid'];
                if($arr['type']=='folder'){
                    $folderids[$arr['oid']]=$arr['oid'];
					if(empty($arr['contaions'])){
						$arr['contaions']=C::t('resources')->get_contains_by_fid($arr['oid']);
					}
                    $arr['filenum'] = $arr['contaions']['contain'][0];
                    $arr['foldernum'] = $arr['contaions']['contain'][1];
                }else{
                    $arr['monthdate'] = dgmdate($arr['dateline'],'m-d');
                    $arr['hourdate'] = dgmdate($arr['dateline'],'H:i');
                }
                if($arr['type'] == 'image'){
                    $arr['img'] = DZZSCRIPT.'?mod=io&op=thumbnail&width=45&height=45&path=' . dzzencode('attach::' . $arr['aid']);
                    $arr['imgpath'] =  DZZSCRIPT.'?mod=io&op=thumbnail&path=' .dzzencode('attach::' . $arr['aid']);
                }
                $data[$arr['rid']]=$arr;

            }
        }
        //获取目录信息
        foreach($folderids as $fid){
            if($folder = C::t('folder')->fetch_by_fid($fid)) $folderdata[$fid] =$folder;
        }
    }

    $disp = isset($_GET['disp']) ? intval($_GET['disp']) : intval($cats['disp']);//文件排序
    $iconview=(isset($_GET['iconview']) ? intval($_GET['iconview']) : intval($cats['iconview']));//排列方式
    $next = false;
    if(count($data) >= $perpage){
        $next = $page + 1;
    }
    $folderjson = json_encode($folderdata);
    //返回数据
    $return=array(
        'cid'=>$cid,
        'data'=>($data) ? $data:array(),
        'param'=>array(
            'disp'=>$disp,
            'view'=>$iconview,
            'bz'=>$bz,
            'datatotal'=>count($data)+$start,
            'asc'=>$asc,
            'page' => $next,
            'keyword'=>$keyword,
            'localsearch'=>$bz?1:0
        )
    );
    $return = json_encode($return);
    $return = str_replace("'","\'",$return);
    require template('mobile/filelist');
    exit();
}elseif($operation == 'catcontent'){
    $cid = isset($_GET['cid']) ? intval($_GET['cid']):'';
    $cats = C::t('resources_cat')->fetch_by_id($cid);
    $navtitle = $cats['catname'];
    require template('mobile/catcontent');
    exit();
}else{
    //搜索类型
    $catsearch = array();
    foreach(C::t('resources_cat')->fetch_by_uid($uid) as $v){
        $exts = str_replace('.','',$v['ext']);
        $catcontain = explode(',',$exts);
        $v['catcontain'] = implode('/',$catcontain);
        $catsearch[] = $v;
    }
    require template('mobile/type');
}
