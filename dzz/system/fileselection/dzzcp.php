<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面

$operation = empty($_GET['operation'])?'':trim($_GET['operation']);
$uid =$_G['uid'];
$space = dzzgetspace($uid);
$space['self']=intval($space['self']);
$refer=dreferer();

if($operation == 'deleteIco'){//删除文件到回收站
    $arr=array();
    $names=array();
    $i=0;
    $icoids=$_GET['rids'];
    $ridarr = array();
    $bz= isset($_GET['bz']) ? trim($_GET['bz']):'';
    foreach($icoids as $icoid){
        $icoid=dzzdecode($icoid);
        if(empty($icoid)){
            continue;
        }
        if(strpos($icoid,'../')!==false){
            $arr['msg'][$return['rid']]=lang('illegal_calls');
        }else{
            $return=IO::Delete($icoid);
            if(!$return['error']){
                //处理数据
                $arr['sucessicoids'][$return['rid']]=$return['rid'];
                $arr['msg'][$return['rid']]='success';
                $ridarr[]= $return['rid'];
                $i++;
            }else{
                $arr['msg'][$return['rid']]=$return['error'];
            }
        }
    }
    //更新剪切板数据
    if(!empty($ridarr)){
        C::t('resources_clipboard')->update_data_by_delrid($ridarr);
    }
    echo json_encode($arr);
    exit();
}elseif($operation == 'copyfile' ){//复制或者剪切文件到云粘贴板
    $rids = isset($_GET['rids']) ? $_GET['rids']:'';
    $bz= isset($_GET['bz']) ? trim($_GET['bz']):'';
    $paths = array();
    foreach($rids as $rid){
        $paths[] = dzzdecode($rid);
    }
    $copytype = isset($_GET['copytype']) ? intval($_GET['copytype']):1;
    $return =   C::t('resources_clipboard')->insert_data($paths,$copytype);
    if(!$return['error']){
       $rids = explode(',',$return['rid']);
       $arr = array('msg'=>'success','rid'=>$rids,'copyid'=>$return['copyid']);
    }else{
        $arr = array('msg'=>$return['error']);
    }
    exit(json_encode($arr));
}elseif($operation == 'rename'){
    $path=dzzdecode($_GET['path']);
    $text=str_replace('...','',getstr(io_dzz::name_filter($_GET['text']),80));
    $ret=IO::rename($path,$text);
    exit(json_encode($ret));

}elseif($operation == 'paste'){//粘贴复制或者剪切的文件

    $copyinfo = C::t('resources_clipboard')->fetch_by_uid();
    //复制文件rid
    $icoids = explode(',',$copyinfo['files']);
    //复制文件的bz
    $obz = !empty($copyinfo['bz']) ? $copyinfo['bz']:'';
    //目标位置的bz
    $tbz=trim($_GET['tbz']);
    $tpath = trim($_GET['tpath']);

    $icoarr=array();
    $folderarr=array();

    //判断是否有粘贴文件
    if(!$icoids){
        $data=array('error'=>lang('data_error'));
        echo json_encode($data);
        exit();
    }
    //判断是否是剪切
    $iscopy =($copyinfo['copytype'] == 1)?1:0;

    $data=array();
    $totalsize=0;
    $icos=$folderids=array();
    //分4种情况：a：本地到api；b：api到api；c：api到本地；d：本地到本地；
    foreach($icoids as $icoid){
        if(empty($icoid)){
            $data['error'][]=$icoid.'：'.lang('forbid_operation');
            continue;
        }
        $rid=rawurldecode($icoid);
        $path=rawurldecode($tpath);
        $return=IO::CopyTo($rid,$path,$iscopy);
        if($return['success']===true){
            if(!$iscopy && $return['moved']!==true){
                IO::DeleteByData($return);
            }
            $data['icoarr'][]=$return['newdata'];
            if(!$tbz){
                addtoconfig($return['newdata'],$ticoid);
            }

            if($return['newdata']['type']=='folder') $data['folderarr'][]=IO::getFolderByIcosdata($return['newdata']);
            $data['successicos'][$return['rid']]=$return['newdata']['rid'];

        }else{
            $data['error'][]=$return['name'].':'.$return['success'];
        }
    }

    if($data['successicos']){
        $data['msg']='success';
        C::t('resources_clipboard')->delete_by_uid();
        if(isset($data['error'])) $data['error']=implode(';',$data['error']);
        echo json_encode($data);
        exit();
    }else{
        $data['error']=implode(';',$data['error']);
        echo json_encode($data);
        exit();
    }
}elseif($operation == 'recoverFile'){//恢复文件
    $arr=array();
    $i=0;
    $icoids=$_GET['rids'];
    $ridarr = array();
    $bz= isset($_GET['bz']) ? trim($_GET['bz']):'';
    foreach($icoids as $icoid){
        $icoid=dzzdecode($icoid);
        if(empty($icoid)){
            continue;
        }
        //判断文件是否在回收站
        if (!$recycleinfo = C::t('resources_recyle')->get_data_by_rid($icoid)) {
            $arr['msg'][$icoid]=lang('file_longer_exists');
        }else{
            $return=IO::Recover($icoid);
        }

        if(!$return['error']){
            //处理数据
            $arr['sucessicoids'][$return['rid']]=$return['rid'];
            $arr['msg'][$return['rid']]='success';
            $arr['name'][$return['rid']] = $return['name'];
            $ridarr[]= $return['rid'];
            $i++;
        }else{
            $arr['msg'][$return['rid']]=$return['error'];
        }
    }
    echo json_encode($arr);
    exit();
}elseif($operation == 'recoverAll'){//恢复所有文件
    $rids = C::t('resources_recyle')->fetch_all_rid();
    foreach($rids as $icoid){
        //$icoid=dzzdecode($icoid);
        if(empty($icoid)){
            continue;
        }
        //判断文件是否在回收站
        if (!$recycleinfo = C::t('resources_recyle')->get_data_by_rid($icoid)) {
            $arr['msg'][$icoid]=lang('file_longer_exists');
        }else{
            $return=IO::Recover($icoid);
        }

        if(!$return['error']){
            //处理数据
            $arr['sucessicoids'][$return['rid']]=$return['rid'];
            $arr['msg'][$return['rid']]='success';
            $arr['name'][$return['rid']] = $return['name'];
            $ridarr[]= $return['rid'];
            $i++;
        }else{
            $arr['msg'][$return['rid']]=$return['error'];
        }
    }
    echo json_encode($arr);
    exit();
}elseif($operation == 'finallydelete'){//彻底删除文件
    $arr=array();
    $i=0;
    $icoids=$_GET['rids'];
    $ridarr = array();
    $bz= isset($_GET['bz']) ? trim($_GET['bz']):'';
    foreach($icoids as $icoid){
        $icoid=dzzdecode($icoid);
        if(empty($icoid)){
            continue;
        }
        $return=IO::Delete($icoid);
        if(!$return['error']){
            //处理数据
            $arr['sucessicoids'][$return['rid']]=$return['rid'];
            $arr['msg'][$return['rid']]='success';
            $arr['name'][$return['rid']] = $return['name'];
            $ridarr[]= $return['rid'];
            $i++;
        }else{
            $arr['msg'][$return['rid']]=$return['error'];
        }
    }
    echo json_encode($arr);
    exit();
}elseif($operation == 'emptyallrecycle'){//清空回收站
    $rids = C::t('resources_recyle')->fetch_all_rid();
    foreach($rids as $icoid){
        //$icoid=dzzdecode($icoid);
        $return=IO::Delete($icoid);
        if(!isset($return['error'])){
            //处理数据
            $arr['sucessicoids'][$return['rid']]=$return['rid'];
            $arr['msg'][$return['rid']]='success';
            $arr['name'][$return['rid']] = $return['name'];
            $ridarr[]= $return['rid'];
            $i++;
        }else{
            $arr['msg'][$return['rid']]=$return['error'];
        }
    }
    echo json_encode($arr);
    exit();
}elseif($operation == 'download'){//暂无请求到此的下载
    define('NOROBOT', TRUE);
    $path = empty($_GET['icoid'])?trim($_GET['path']):$_GET['icoid'];
    $patharr=explode(',',$path);
    $paths=array();
    foreach($patharr as $path){
        if($path=dzzdecode($path)){
            $paths[]=$path;
        }
    }
    if($paths){
        IO::download($paths,$_GET['filename']);
        exit();
    }else{
        exit('path error!');
    }
}