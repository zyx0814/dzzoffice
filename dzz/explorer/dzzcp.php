<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面

$do = empty($_GET['do']) ? '' : trim($_GET['do']);
$uid = $_G['uid'];
$space = dzzgetspace($uid);
$space['self'] = intval($space['self']);
$refer = dreferer();

if ($do == 'deleteIco') {//删除文件到回收站
    $arr = array();
    $names = array();
    $i = 0;
    $icoids = $_GET['rids'];
    $ridarr = array();
    $bz = isset($_GET['bz']) ? trim($_GET['bz']) : '';
    $deletefinally = 0;
    if (isset($_G['setting']['explorer_finallydelete'])) {
        if (intval($_G['setting']['explorer_finallydelete']) === 0) {
            $deletefinally = 1;
        }
    }
    foreach ($icoids as $icoid) {
        $icoid = dzzdecode($icoid);
        if (empty($icoid)) {
            continue;
        }
        if (strpos($icoid, '../') !== false) {
            $arr['msg'][$return['rid']] = lang('illegal_calls');
        } else {
            $return = IO::Delete($icoid,$deletefinally);
            if (!$return['error']) {
                //处理数据
                $arr['sucessicoids'][$return['rid']] = $return['rid'];
                $arr['msg'][$return['rid']] = 'success';
                $ridarr[] = $return['rid'];
                $i++;
            } else {
                $arr['msg'][$return['rid']] = $return['error'];
            }
        }
    }
    //更新剪切板数据
    if (!empty($ridarr)) {
        C::t('resources_clipboard')->update_data_by_delrid($ridarr);
    }
    echo json_encode($arr);
    exit();
} elseif ($do == 'copyfile') {//复制或者剪切文件到云粘贴板
    $rids = isset($_GET['rids']) ? $_GET['rids'] : '';
    $bzrid = isset($_GET['rid']) ? $_GET['rid'] : '';
    $bz = isset($_GET['bz']) ? trim($_GET['bz']) : '';
    $paths = array();
    foreach ($rids as $rid) {
        $paths[] = dzzdecode($rid);
    }
    $copytype = isset($_GET['copytype']) ? intval($_GET['copytype']) : 1;
    $return = C::t('resources_clipboard')->insert_data($paths, $copytype, $bz);
    if ($return['error']) {
        $arr = array('msg' => $return['error']);
    } else {
        if ($bz && $bz !== 'dzz') {
            $rids = $bzrid;
        } else {
            $rids = explode(',', $return['rid']);
        }
        $arr = array('msg' => 'success', 'rid' => $rids, 'copyid' => $return['copyid'], 'type' => $return['type']);
    }
    exit(json_encode($arr));
} elseif ($do == 'deletecopy') {
    $return = C::t('resources_clipboard')->delete_by_uid();
    if ($return) {
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode(array('error' => true)));
    }
} elseif ($do == 'rename') {
    $path = dzzdecode($_GET['path']);
    $text = str_replace('...', '', getstr(IO::name_filter($_GET['text']), 80));
    $ret = IO::rename($path, $text);
    exit(json_encode($ret));

} elseif ($do == 'paste') {//粘贴复制或者剪切的文件
    $copyinfo = C::t('resources_clipboard')->fetch_by_uid();
    //复制文件rid
    $icoids = explode(',', $copyinfo['files']);
    //复制文件的bz
    $obz = !empty($copyinfo['bz']) ? $copyinfo['bz'] : '';
    //目标位置的bz
    $tbz = trim($_GET['tbz']);
    $tpath = trim($_GET['tpath']);

    $icoarr = array();
    $folderarr = array();

    //判断是否有粘贴文件
    if (!$icoids) {
        $data = array('error' => lang('data_error'));
        echo json_encode($data);
        exit();
    }
    //判断是否是剪切
    $iscopy = ($copyinfo['copytype'] == 1) ? 1 : 0;

    $data = array();
    $totalsize = 0;
    $icos = $folderids = array();
    //分4种情况：a：本地到api；b：api到api；c：api到本地；d：本地到本地；
    foreach ($icoids as $icoid) {
        if (empty($icoid)) {
            $data['error'][] = $icoid . '：' . lang('forbid_operation');
            continue;
        }
        $rid = rawurldecode($icoid);
        $path = rawurldecode($tpath);
        $return = IO::CopyTo($rid, $path, $iscopy);
        if ($return['success'] === true) {
            if (!$iscopy && $return['moved'] !== true) {
                IO::DeleteByData($return);
            }
            $data['icoarr'][] = $return['newdata'];
            if (!$tbz) {
                addtoconfig($return['newdata'], $ticoid);
            }

            if ($return['newdata']['type'] == 'folder') $data['folderarr'][] = IO::getFolderByIcosdata($return['newdata']);
            $data['successicos'][$return['rid']] = $return['newdata']['rid'];

        } else {
            $data['error'][] = $return['name'] . ':' . $return['success'];
        }
    }

    if ($data['successicos']) {
        $data['msg'] = 'success';
        C::t('resources_clipboard')->delete_by_uid();
        if (isset($data['error'])) $data['error'] = implode(';', $data['error']);
        $data['copytype'] = $iscopy;
        echo json_encode($data);
        exit();
    } else {
        $data['error'] = implode(';', $data['error']);
        echo json_encode($data);
        exit();
    }
} elseif ($do == 'recoverFile') {//恢复文件
    $arr = array();
    $i = 0;
    $icoids = $_GET['rids'];
    $ridarr = array();
    $bz = isset($_GET['bz']) ? trim($_GET['bz']) : '';
    foreach ($icoids as $icoid) {
        $icoid = dzzdecode($icoid);
        if (empty($icoid)) {
            continue;
        }
        //判断文件是否在回收站
        if (!$recycleinfo = C::t('resources_recyle')->get_data_by_rid($icoid)) {
            $arr['msg'][$icoid] = lang('file_longer_exists');
        } else {
            $return = IO::Recover($icoid);
        }
        if (!$return['error']) {
            //处理数据
            $arr['sucessicoids'][$return['rid']] = $return['rid'];
            $arr['msg'][$return['rid']] = 'success';
            $arr['name'][$return['rid']] = $return['name'];
            $ridarr[] = $return['rid'];
            $i++;
        } else {
            $arr['msg'][$return['rid']] = $return['error'];
        }
    }
    echo json_encode($arr);
    exit();
} elseif ($do == 'recoverAll') {//恢复所有文件
    $rids = C::t('resources_recyle')->fetch_all_rid();
    if (count($rids) < 1) exit(json_encode(array('error' => lang('recycle_not_data'))));
    foreach ($rids as $icoid) {
        //$icoid=dzzdecode($icoid);
        if (empty($icoid)) {
            continue;
        }
        //判断文件是否在回收站
        if (!$recycleinfo = C::t('resources_recyle')->get_data_by_rid($icoid)) {
            $arr['msg'][$icoid] = lang('file_longer_exists');
        } else {
            $return = IO::Recover($icoid);
        }

        if (!$return['error']) {
            //处理数据
            $arr['sucessicoids'][$return['rid']] = $return['rid'];
            $arr['msg'][$return['rid']] = 'success';
            $arr['name'][$return['rid']] = $return['name'];
            $ridarr[] = $return['rid'];
            $i++;
        } else {
            $arr['msg'][$return['rid']] = $return['error'];
        }
    }
    echo json_encode($arr);
    exit();
} elseif ($do == 'finallydelete') {//彻底删除文件
    $arr = array();
    $i = 0;
    $icoids = $_GET['rids'];
    $ridarr = array();
    $bz = isset($_GET['bz']) ? trim($_GET['bz']) : '';
    foreach ($icoids as $icoid) {
        $icoid = dzzdecode($icoid);
        if (empty($icoid)) {
            continue;
        }
        $return = IO::Delete($icoid, true);
        if (!$return['error']) {
            //处理数据
            $arr['sucessicoids'][$return['rid']] = $return['rid'];
            $arr['msg'][$return['rid']] = 'success';
            $arr['name'][$return['rid']] = $return['name'];
            $ridarr[] = $return['rid'];
            $i++;
        } else {
            $arr['msg'][$return['rid']] = $return['error'];
        }
    }
    echo json_encode($arr);
    exit();
} elseif ($do == 'emptyallrecycle') {//清空回收站
    $rids = C::t('resources_recyle')->fetch_all_rid();
    if (count($rids) < 1) exit(json_encode(array('error' => lang('recycle_not_data'))));
    foreach ($rids as $icoid) {
        //$icoid=dzzdecode($icoid);
        $return = IO::Delete($icoid, true);
        if (!isset($return['error'])) {
            //处理数据
            $arr['sucessicoids'][$return['rid']] = $return['rid'];
            $arr['msg'][$return['rid']] = 'success';
            $arr['name'][$return['rid']] = $return['name'];
            $ridarr[] = $return['rid'];
            $i++;
        } else {
            $arr['msg'][$return['rid']] = $return['error'];
        }
    }
    echo json_encode($arr);
    exit();
} elseif ($do == 'download') {//暂无请求到此的下载
    define('NOROBOT', TRUE);
    $path = empty($_GET['icoid']) ? trim($_GET['path']) : $_GET['icoid'];
    $patharr = explode(',', $path);
    $paths = array();
    foreach ($patharr as $path) {
        if ($path = dzzdecode($path)) {
            $paths[] = $path;
        }
    }
    if ($paths) {
        IO::download($paths, $_GET['filename']);
        exit();
    } else {
        exit('path error!');
    }
} elseif ($do == 'uploadnewVersion') {//更新文件版本
    $rid = isset($_GET['rid']) ? $_GET['rid'] : '';
    $setarr = array(
        'uid' => $uid,
        'username' => $_G['username'],
        'name' => getstr($_GET['name']),
        'aid' => intval($_GET['aid']),
        'size' => intval($_GET['size']),
        'ext' => $_GET['ext'],
        'dateline' => TIMESTAMP
    );
    $return = C::t('resources_version')->add_new_version_by_rid($rid, $setarr);
    if ($return['error']) {
        exit(json_encode(array('error' => $return['error'])));
    } else {
        $statisdata = array(
            'uid' => $_G['uid'],
            'edits' => 1,
            'editdateline' => TIMESTAMP
        );
        C::t('resources_statis')->add_statis_by_rid($rid, $statisdata);
        $resources = C::t('resources')->fetch_by_rid($rid);
        exit(json_encode(array('success' => true, 'data' => $return, 'filedata' => $resources)));
    }
} elseif ($do == 'setpramiryversion') {//设置主版本
    $vid = isset($_GET['vid']) ? intval($_GET['vid']) : '';
    $return = C::t('resources_version')->set_primary_version_by_vid($vid);
    if ($return['rid']) {
        $resourcesdata = C::t('resources')->fetch_by_rid($return['rid']);
        exit(json_encode(array('success' => true, 'data' => $resourcesdata)));
    } else {
        exit(json_encode(array('error' => true, 'msg' => $return['error'])));
    }

} elseif ($do == 'setversionname') {//修改版本名称
    $vid = isset($_GET['vid']) ? intval($_GET['vid']) : '';
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    $vname = isset($_GET['vname']) ? getstr($_GET['vname']) : '';
    if (!$vname) exit(json_encode(array('error' => lang('explorer_do_failed'))));
    $vdesc = isset($_GET['vdesc']) ? dhtmlspecialchars(substr(trim($_GET['vdesc']), 0, 120)) : '';
    $return = array();
    if ($vid) {
        $return = C::t('resources_version')->update_versionname_by_vid($vid, $vname, $vdesc);
    } else {
        $return = C::t('resources_version')->update_versionname_by_rid($rid, $vname, $vdesc);
    }
    if ($return['vid']) {
        exit(json_encode($return));
    } else {
        $msg = (!isset($return['error'])) ? lang('explorer_do_failed') : $return['error'];
        exit(json_encode(array('error' => $msg)));
    }
} elseif ($do == 'riddesc') {//修改文件描述
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    if (!$rid) exit(json_encode(array('error' => lang('explorer_do_failed'))));
    $fileinfo = C::t('resources')->fetch_by_rid($rid);
    if(!$fileinfo) exit(json_encode(array('error' => lang('explorer_do_failed'))));
    if (!perm_check::checkperm('edit', $fileinfo)) {
        exit(json_encode(array('error' => lang('file_edit_no_privilege'))));
    }
    $desc = isset($_GET['desc']) ? htmlspecialchars(trim($_GET['desc'])) : '';

    C::t('resources_meta')->update_by_key($fileinfo['rid'], array('desc' => $desc));
    exit(json_encode(array('success' => true)));
}