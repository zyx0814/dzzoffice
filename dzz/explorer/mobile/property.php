<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
//文件信息数据请求
$fid = isset($_GET['fid']) ? trim($_GET['fid']) : '';
$rids = isset($_GET['rid']) ? $_GET['rid'] : '';
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
$cid = isset($_GET['cid']) ? intval($_GET['cid']):'';
if($operation == 'getfileProperty'){
    if($cid){
        $cat = C::t('resources_cat')->fetch_by_id($cid);
        $exts =  explode(',',str_replace('.','',$cat['ext']));
        $cat['ext'] = implode('/',$exts);
        $cattidarr = explode(',',$cat['tag']);
        if(count($cattidarr)){
            $tagarr = C::t('tag')->fetch_tag_by_tid($cattidarr,'explorer');
            $cat['tag'] = implode('/',$tagarr);
        }else{
            $cat['tag'] = '暂无标签';
        }
        include template('mobile/attr_type');
        exit();
    }
    if ($fid) {//如果获取到文件夹id
        if($rid = DB::result_first("select rid from %t where oid = %d and `flag` = %s",array('resources',$fid,'folder'))){
                //文件夹属性信息
                $fileinfo = C::t('resources')->get_property_by_rid($rid);
                //权限信息
                $userperm = perm_check::getPerm($fileinfo['pfid']);//获取用户权限
                $perm = C::t('folder')->fetch_perm_by_fid($fileinfo['pfid']);//获取文件夹权限
                //动态信息
                $gid = $fileinfo['gid'];

        }else{
            //文件夹信息
            $fileinfo = C::t('resources')->get_folderinfo_by_fid($fid);
            $gid = $fileinfo['gid'];
            if($fileinfo['isgroup']){
                $org = C::t('organization')->fetch($gid);
                //获取已使用空间
                $usesize = C::t('organization')->get_orgallotspace_by_orgid($gid, 0, false);
                //获取总空间
                if ($org['maxspacesize'] == 0) {
                    $maxspace = 0;
                } else {
                    if ($org['maxspacesize'] == -1) {
                        $maxspace = -1;
                    } else {
                        $maxspace = $org['maxspacesize'] * 1024 * 1024;
                    }
                }
            }elseif($fileinfo['pfid'] == 0){
                $spaceinfo = dzzgetspace($uid);
                $maxspace = $spaceinfo['maxspacesize'];
                $usesize = $spaceinfo['usesize'];
            }
        }

        $fileinfo['type'] ='文件夹';
        $progress = set_space_progress($usesize, $maxspace);
        //统计表数据
        $statis = C::t('resources_statis')->fetch_by_fid($fid);
        $fileinfo['opendateline'] = ($statis['opendateline']) ? dgmdate($statis['opendateline'], 'Y-m-d H:i:s') : '';
        $fileinfo['editdateline'] = ($statis['editdateline']) ? dgmdate($statis['editdateline'], 'Y-m-d H:i:s') : '';
        $fileinfo['fdateline'] = ($foldeinfo['dateline']) ? dgmdate($foldeinfo['dateline'], 'Y-m-d H:i:s') : '';
        $fileinfo['fid'] = $fid;
        $perms = get_permsarray();//获取所有权限
        //权限数据
        $perm = C::t('folder')->fetch_perm_by_fid($fid);//获取文件夹权限
        include template('mobile/attr_alltype');
        exit();
    }else if($rids){
        if (!is_array($rids)) $rids = explode(',', $rids);
        $ridnum = count($rids);
        if ($ridnum == 1) {//如果只有一个选中项，判断是否是文件夹
            $rid = $rids[0];
            $file = C::t('resources')->fetch_info_by_rid($rid);
            if ($file['type'] == 'folder')
            {
                $perms = get_permsarray();//获取所有权限
                $gid = $file['gid'];
                $fileinfo = C::t('resources')->get_property_by_rid($rid);
                if($fileinfo['isgroup']){
                    $org = C::t('organization')->fetch($gid);
                    //获取已使用空间
                    $usesize = C::t('organization')->get_orgallotspace_by_orgid($gid, 0, false);
                    //获取总空间
                    if ($org['maxspacesize'] == 0) {
                        $maxspace = 0;
                    } else {
                        if ($org['maxspacesize'] == -1) {
                            $maxspace = -1;
                        } else {
                            $maxspace = $org['maxspacesize'] * 1024 * 1024;
                        }
                    }
                }

                $progress = set_space_progress($usesize, $maxspace);
                $perm = C::t('folder')->fetch_perm_by_fid($file['oid']);//获取文件夹权限
                $fileinfo['fid'] = $file['oid'];
                include template('mobile/attr_alltype');
                exit();
            } else {
                $fileinfo = C::t('resources')->get_property_by_rid($rid);
                if($fileinfo['isdelete'] && $fileinfo['pfid'] == -1){
                    $pathrecord = DB::result_first("select pathinfo from %t where rid = %s",array('resources_recyle',$rid));
                    $fileinfo['realpath'] = preg_replace('/dzz:(.+?):/', '', $pathrecord);
                }
                $fileinfo['dpath'] = dzzencode($rid);
                $pfid = $fileinfo['pfid'];
                $gid = $fileinfo['gid'];
                $tags = C::t('resources_tag')->fetch_tag_by_rid($rid);
                include template('mobile/attr_alltype');
                exit();
            }
        } elseif ($ridnum > 1) {//如果是多项选中，则调对应综合文件信息
            $fileinfo = C::t('resources')->get_property_by_rid($rids);
            include template('mobile/attr_alltype');
            exit();
        }
    }
}else{
    include template('mobile/property');
    exit();
}


