<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
$uid = $_G['uid'];
$do = empty($_GET['do']) ? '' : $_GET['do'];
$sid = $_GET['sid'] ? $_GET['sid'] : '';
if (!$sid) {
    exit(json_encode(array('error' => 'Access Denied')));
}
$sid = dzzdecode($sid);
$share = C::t('shares')->fetch($sid);
if (!$share || empty($share['filepath'])) exit(json_encode(array('error' => lang('share_file_iscancled'))));
if ($share['status'] == -4) exit(json_encode(array('error' => lang('shared_links_screened_administrator'))));
if ($share['status'] == -5) exit(json_encode(array('error' => lang('sharefile_isdeleted_or_positionchange'))));
//判断是否过期
if ($share['endtime'] && $share['endtime'] < TIMESTAMP) {
    exit(json_encode(array('error' => lang('share_link_expired'))));
}
if ($share['times'] && $share['times'] <= $share['count']) {
    exit(json_encode(array('error' => lang('link_already_reached_max_number'))));
}
if ($share['status'] == -3) {
    exit(json_encode(array('error' => lang('share_file_deleted'))));
}
$filepaths = $share['filepath'];
$rids = explode(',', $filepaths);
$create = 0;
$download = 1;
if ($share['perm']) {
    $perms = array_flip(explode(',', $share['perm'])); // 将权限字符串转换为数组
    if (isset($perms[3]) && !$_G['uid']) { // 3 表示仅登录访问
        exit(json_encode(array('error' => 'no_login')));
    }
    if (isset($perms[5])) {
        $create = 1;
    }
    if (isset($perms[1])) {
        $download = 0; // 下载权限被禁用
    }
}
if ($do == 'adddowns') {
    if (!$download) {
        exit(json_encode(array('error' => lang('file_download_no_privilege'))));
    }
    if (C::t('shares')->add_downs_by_id($sid)) {
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode(array('error' => 'error')));
    }
}
$fid = isset($_GET['fid']) ? intval($_GET['fid']) : '';
if ($do == 'uploads') {//上传新文件(指新建)
    if (!$create) {
        header('HTTP/1.1 400 Bad Request');
        exit('没有上传权限');
    }
    $container = trim($_GET['container']);
    $validatefid = validatefid($share, $container);
    if (!$validatefid) {
        header('HTTP/1.1 400 Bad Request');
        exit(lang('no_privilege'));
    }
    $space = dzzgetspace($uid);
    $space['self'] = intval($space['self']);
    $bz = trim($_GET['bz']);
    require_once dzz_libfile('class/UploadHandler');
    //上传类型
    $allowedExtensions = $space['attachextensions'] ? explode(',', $space['attachextensions']) : array();

    $sizeLimit = ($space['maxattachsize']);

    $options = array('accept_file_types' => $allowedExtensions ? ("/(\.|\/)(" . implode('|', $allowedExtensions) . ")$/i") : "/.+$/i",
        'max_file_size' => $sizeLimit ? $sizeLimit : null,
        'upload_dir' => $_G['setting']['attachdir'] . 'cache/',
        'upload_url' => $_G['setting']['attachurl'] . 'cache/',
        'force' => true,
    );
    $upload_handler = new UploadHandler($options);
    exit();
} elseif ($do == 'newFolder') {//新建文件夹
    if (!$create) {
        if ($_GET['createfolder']) exit(json_encode(array('error' => '分享者未开放新建权限')));
        showmessage('分享者未开放新建权限');
    }
    $validatefid = validatefid($share, $fid);
    if (!$validatefid) {
        if ($_GET['createfolder']) exit(json_encode(array('error' => '您没有该目录的新建权限')));
        showmessage('您没有该目录的新建权限');
    }
    $bz = isset($_GET['bz']) ? trim($_GET['bz']) : '';
    $name = !empty($_GET['foldername']) ? trim($_GET['foldername']) : lang('newfolder');
    if (isset($_GET['createfolder'])) {
        $fid = intval($_GET['fid']);
        if ($bz) {
            $fid = $bz;
        }
        if(!$fid) exit(json_encode(array('error'=>lang('no_target_folderID'))));
        $fname = IO::name_filter(getstr($name, 80));
        if ($arr = IO::CreateFolder($fid, $fname, 0, array(), 'newcopy', true)) {
            if ($arr['error']) {
            } else {
                $arr = array_merge($arr['icoarr'], $arr['folderarr']);
                $arr['msg'] = 'success';
            }
        } else {
            $arr = array();
            $arr['error'] = lang('failure_newfolder');
        }
        exit(json_encode($arr));
    } else {
        $permselect = true;
    }
} elseif ($do == 'newLink') {//新建连接
    if (!$create) {
        showmessage('no_privilege');
    }
    $validatefid = validatefid($share, $fid);
    if (!$validatefid) {
        showmessage('no_privilege');
    }
} elseif ($do == 'linkadd') {
    if (!$create) {
        showmessage('no_privilege');
    }
    $validatefid = validatefid($share, $fid);
    if (!$validatefid) {
        showmessage('no_privilege');
    }
    if (isset($_GET['createlink']) && $_GET['createlink']) {
        $name = isset($_GET['name']) ? trim($_GET['name']) : '';
        $link = isset($_GET['link']) ? trim($_GET['link']) : '';
        //检查网址合法性
        if (!preg_match("/^(http|ftp|https|mms)\:\/\/.{5,300}$/i", ($link))) {
            $link = 'http://' . preg_replace("/^(http|ftp|https|mms)\:\/\//i", '', $link);
        }
        if (!preg_match("/^(http|ftp|https|mms)\:\/\/.{4,300}$/i", ($link))) {
            $arr['error'] = lang('invalid_format_url');
        } else {

            $ext = strtolower(substr(strrchr($link, '.'), 1, 10));
            $isimage = in_array(strtoupper($ext), $imageexts) ? 1 : 0;
            $ismusic = 0;
            if ($data = io_dzz::linktourl($link, $fid,$name)) {
                if ($data['error']) {
                    $arr['error'] = $data['error'];
                } else {
                    $arr = $data;
                    $arr['msg'] = 'success';
                }
            } else {
                $arr['error'] = lang('network_error');
            }
        }
    }
    exit(json_encode($arr));
} elseif ($do == 'txt') {//新建文档
    $arr = array();
    if (!$create) {
        $arr['error'] = lang('no_privilege');
        exit(json_encode($arr));
    }
    $ext = 'txt';
    $name = lang('new_' . $ext);
    $filename = $name . '.' . $ext;
    $validatefid = validatefid($share, $fid);
    if (!$validatefid) {
        $arr['error'] = lang('no_privilege');
        exit(json_encode($arr));
    }
    if ($arr = IO::upload_by_content(' ', $fid, $filename)) {
        if ($arr['error']) {

        } else {
            $arr['msg'] = 'success';
        }
    } else {
        $arr['error'] = lang('failure_newfolder');
    }
} elseif ($do == 'newIco') {//新建文件
    if (!$create) {
        $arr['error'] = lang('no_privilege');
        exit(json_encode($arr));
    }
    $type = trim($_GET['type']);
    $validatefid = validatefid($share, $fid);
    if (!$validatefid) {
        $arr['error'] = lang('no_privilege');
        exit(json_encode($arr));
    }
    $filename = '';
    $bzpath = isset($_GET['bz']) ? trim($_GET['bz']) : '';
    switch ($type) {
        case 'newTxt':
            $filename = lang('new_txt') . '.txt';
            $content = ' ';
            break;
        case 'newDzzDoc':
            $filename = lang('new_dzzdoc') . '.dzzdoc';
            $content = ' ';
            break;
        case 'newDoc':
            $filename = lang('new_word') . '.docx';
            $content = file_get_contents(DZZ_ROOT . './dzz/images/newfile/word.docx');
            break;
        case 'newExcel':
            $filename = lang('new_excel') . '.xlsx';
            $content = file_get_contents(DZZ_ROOT . './dzz/images/newfile/excel.xlsx');
            break;
        case 'newPowerPoint':
            $filename = lang('new_PowerPoint') . '.pptx';
            $content = file_get_contents(DZZ_ROOT . './dzz/images/newfile/ppt.pptx');
            break;
        case 'newpdf':
            $filename = lang('new_pdf') . '.pdf';
            $content = file_get_contents(DZZ_ROOT . './dzz/images/newfile/pdf.pdf');
            break;
    }
    if ($bzpath) {
        $fid = $bzpath;
    }
    if ($arr = IO::upload_by_content($content, $fid, $filename, array(), true)) {
        if ($arr['error']) {
        } else {
            $arr['msg'] = 'success';
        }
    } else {
        $arr = array();
        $arr['error'] = lang('new_failure');
    }
    exit(json_encode($arr));
} elseif ($do == 'property') {//属性
    $paths = isset($_GET['paths']) ? trim($_GET['paths']) : '';
    $bz = isset($_GET['bz']) ? trim($_GET['bz']) : '';
    $fid = 0;
    if (preg_match('/fid_/', $paths)) {
        $fid = preg_replace('/fid_/', '', $paths);
    }
    if ($bz) {
        if ($fid) {
            $propertys = IO::getMeta($fid);
            if ($propertys['error']) {
                showmessage($propertys['error']);
            }
            if (!$_G['adminid'] &&  $propertys['uid'] != $_G['uid']) {
                showmessage('no_privilege');
            }
            $contains = IO::getContains($propertys['path']);
            $propertys['ftype'] = lang('type_folder');
            $propertys['ffsize'] = lang('property_info_size', array('fsize' => formatsize($contains['size']), 'size' => $contains['size']));
            $propertys['contain'] = lang('property_info_contain', array('filenum' => $contains['contain'][0], 'foldernum' => $contains['contain'][1]));
        } elseif (strpos($paths, ',') !== false) {
            $patharr = explode(',', $paths);
            $rids = array();
            foreach ($patharr as $v) {
                $rids[] = dzzdecode($v);
            }
            $size = 0;
            $contents = array(0, 0);
            foreach ($rids as $icoid) {
                if (!$icoarr = IO::getMeta($icoid)) continue;
                if ($icoarr['error']) {
                    showmessage($icoarr['error']);
                } else {
                    switch ($icoarr['type']) {
                        case 'folder':
                            $contains = IO::getContains($icoarr['path']);
                            $size += intval($contains['size']);
                            $contents[0] += $contains['contain'][0];
                            $contents[1] += $contains['contain'][1] + 1;
                            break;
                        default:
                            $size += $icoarr['size'];
                            $contents[0] += 1;
                            break;
                    }
                }
            }
            $propertys['ffsize'] = lang('property_info_size', array('fsize' => formatsize($size), 'size' => $size));
            $propertys['contain'] = lang('property_info_contain', array('filenum' => $contents[0], 'foldernum' => $contents[1]));
        } else {
            $paths = dzzdecode($paths);
            $propertys = IO::getMeta($paths);
            if ($propertys['error']) {
                showmessage($propertys['error']);
            }
            if (!$_G['adminid'] &&  $propertys['uid'] != $_G['uid']) {
                showmessage('no_privilege');
            }
            if ($propertys['type'] == 'folder') {
                $contains = IO::getContains($propertys['path']);
                $propertys['ftype'] = lang('type_folder');
                $propertys['ffsize'] = lang('property_info_size', array('fsize' => formatsize($contains['size']), 'size' => $contains['size']));
                $propertys['contain'] = lang('property_info_contain', array('filenum' => $contains['contain'][0], 'foldernum' => $contains['contain'][1]));
            }
        }
    }else {
        if ($fid) {
            $propertys['ftype'] = '分享文件';
            $propertys['username'] = $share['username'];
        } else {
            $patharr = explode(',', $paths);
            $rids = array();
            foreach ($patharr as $v) {
                $path = dzzdecode($v);
                if ($path && preg_match('/^sid:([^\_]+)_/', $path)) {
                    $v = preg_replace('/^sid:[^\_]+_/', '', $path);
                }
                $rids[] = $v;
            }
            $first_path = '';
            if (!$_G['adminid'] && $share['uid'] !== $_G['uid']) {
                $path = C::t('resources_path')->fetch_pathby_pfid($share['pfid'], true);
                if ($path['path']) {
                    $first_path = $path['path'];
                }
            }
            $propertys = C::t('resources')->get_property_by_rid($rids, true, $first_path,false);
        }
        if ($propertys['error']) {
            showmessage($propertys['error']);
        }
    }
} elseif ($do == 'addIndex') {//索引文件
    if (!$create) {
        exit(json_encode(array('error' => '没有上传权限')));
    }
    global $_G;
    $indexarr = array(
        'id' => $_GET['rid'] . '_' . intval($_GET['vid']),
        'name' => $_GET['filename'],
        'username' => $_GET['username'],
        'type' => $_GET['filetype'],
        'flag' => 'explorer',
        'vid' => intval($_GET['vid']),
        'gid' => intval($_GET['gid']),
        'uid' => intval($_GET['uid']),
        'aid' => isset($_GET['aid']) ? intval($_GET['aid']) : 0,
        'md5' => isset($_GET['md5']) ? trim($_GET['md5']) : '',
        'readperm' => 0
    );
    $fid = intval($_GET['pfid']);
    $folderdata = C::t('folder')->fetch($fid);
    $perm = $folderdata['perm_inherit'];
    if (perm_binPerm::havePower('read2', $perm)) {
        $indexarr['readperm'] = 2;
    } elseif (perm_binPerm::havePower('read1', $perm)) {
        $indexarr['readperm'] = 1;
    } else {
        $indexarr['readperm'] = 0;
    }
    $return = Hook::listen('solraddfile', $indexarr);
    if ($return[0]['error']) {
        exit(json_encode($return[0]));
    } else {
        exit(json_encode(array('success' => true)));
    }
}
include template('ajax');
function validatefid($share = array(), $fid = '') {
    if($share['pfid']==-1) {
        if ($_GET['bz'] && strpos($_GET['bz'], $share['filepath']) === 0) {
            return true;
        } elseif ($_GET['container'] && strpos($_GET['container'], $share['filepath']) === 0) {
            return true;
        } else {
           return false;
        }
    }
    $fiddata = C::t('resources_path')->fetch_folder_containfid_by_pfid($share['pfid']);
    if (!empty($fiddata)) {
        // 排除第一个元素
        array_shift($fiddata);
    }
    if (in_array($fid, $fiddata)) {
        return true;
    } else {
        return false;
    }
}