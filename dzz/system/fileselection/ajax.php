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
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
if ($operation == 'upload') {//上传图片文件
    include libfile('class/uploadhandler');
    $options = array('accept_file_types' => '/\.(gif|jpe?g|png)$/i',
        'upload_dir' => $_G['setting']['attachdir'] . 'cache/',
        'upload_url' => $_G['setting']['attachurl'] . 'cache/',
        'thumbnail' => array('max-width' => 40, 'max-height' => 40));
    $upload_handler = new uploadhandler($options);
    exit();
} elseif ($operation == 'uploads') {//上传新文件(指新建)
    $container = trim($_GET['container']);
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
    );
    $upload_handler = new UploadHandler($options);
    exit();
} elseif ($operation == 'uploadfiles') {//上传文件(单纯的上传)
    $space = dzzgetspace($uid);
    $space['self'] = intval($space['self']);
    require_once libfile('class/uploadhandler', '', 'core');
    //上传类型
    $allowedExtensions = $space['attachextensions'] ? explode(',', $space['attachextensions']) : array();
    $sizeLimit = ($space['maxattachsize']);

    $options = array('accept_file_types' => $allowedExtensions ? ("/(\.|\/)(" . implode('|', $allowedExtensions) . ")$/i") : "/.+$/i",
        'max_file_size' => $sizeLimit ? $sizeLimit : null,
        'upload_dir' => $_G['setting']['attachdir'] . 'cache/',
        'upload_url' => $_G['setting']['attachurl'] . 'cache/',
    );
    $upload_handler = new UploadHandler($options);
    exit();
} elseif ($operation == 'newFolder') {//新建文件夹
    $fid = isset($_GET['fid']) ? intval($_GET['fid']) : '';
    if(!$fid) exit(json_encode(array('error'=>lang('no_target_folderID'))));
    $perm = 0;
    $name = !empty($_GET['foldername']) ? trim($_GET['foldername']) : lang('newfolder');
    $fid = intval($_GET['fid']);
    $fname = IO::name_filter(getstr($name, 80));
    if ($arr = IO::CreateFolder($fid, $fname, $perm)) {
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
    // }


} elseif ($operation == 'linkadd') {
    if (isset($_GET['createlink']) && $_GET['createlink']) {
        $link = isset($_GET['link']) ? trim($_GET['link']) : '';
        $fid = isset($_GET['fid']) ? intval($_GET['fid']) : '';
        //检查网址合法性
        if (!preg_match("/^(http|ftp|https|mms)\:\/\/.{5,300}$/i", ($link))) {
            $link = 'http://' . preg_replace("/^(http|ftp|https|mms)\:\/\//i", '', $link);
        }
        if (!preg_match("/^(http|ftp|https|mms)\:\/\/.{4,300}$/i", ($link))) {
            $arr['error'] = lang('invalid_format_url');
        } else {
            if (!perm_check::checkperm_Container($fid, 'upload')) {
                $arr['error'] = lang('target_not_accept_link');
            } else {
                if ($data = io_dzz::linktourl($link, $fid)) {
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
    }
    exit(json_encode($arr));
} elseif ($operation == 'txt') {//新建文档
    $name = lang('new_txt');
    $filename = $name . '.txt';
    $fid = isset($_GET['fid']) ? intval($_GET['fid']) : '';
    if ($arr = IO::upload_by_content(' ', $fid, $filename)) {
        if ($arr['error']) {

        } else {
            $arr['msg'] = 'success';
        }
    } else {
        $arr = array();
        $arr['error'] = lang('failure_newfolder');
    }
} elseif ($operation == 'newIco') {//新建文件
    $type = trim($_GET['type']);
    $fid = trim($_GET['fid']);
    $filename = isset($_GET['filename']) ? trim($_GET['filename']) : '';
    switch ($type) {
        case 'newTxt':
            $filename = lang('new_txt') . '.txt';
            if (!perm_check::checkperm_Container($fid, 'upload')) {
                exit(json_encode(array('error' => lang('folder_upload_no_privilege'))));
            }
            $content = ' ';
            break;
        case 'newDzzDoc':
            $filename = lang('new_dzzdoc') . '.dzzdoc';
            if (!perm_check::checkperm_Container($fid, 'upload')) {
                exit(json_encode(array('error' => lang('folder_upload_no_privilege'))));
            }
            $content = ' ';
            break;
        case 'newDoc':
            $filename = lang('new_word') . '.docx';
            if (!perm_check::checkperm_Container($fid, 'upload')) {
                exit(json_encode(array('error' => lang('folder_upload_no_privilege'))));
            }
            $content = file_get_contents(DZZ_ROOT . './dzz/images/newfile/word.docx');
            break;
        case 'newExcel':
            $filename = lang('new_excel') . '.xlsx';
            if (!perm_check::checkperm_Container($fid, 'upload')) {
                exit(json_encode(array('error' => lang('folder_upload_no_privilege'))));
            }
            $content = file_get_contents(DZZ_ROOT . './dzz/images/newfile/excel.xlsx');
            break;
        case 'newPowerPoint':
            $filename = lang('new_PowerPoint') . '.pptx';
            if (!perm_check::checkperm_Container($fid, 'upload')) {
                exit(json_encode(array('error' => lang('folder_upload_no_privilege'))));
            }
            $content = file_get_contents(DZZ_ROOT . './dzz/images/newfile/ppt.pptx');
            break;
        case 'newpdf':
            $filename = lang('new_pdf') . '.pdf';
            if (!perm_check::checkperm_Container($fid, 'upload')) {
                exit(json_encode(array('error' => lang('no_privilege'))));
            }
            $content = file_get_contents(DZZ_ROOT . './dzz/images/newfile/pdf.pdf');
            break;
        default:
            if (!perm_check::checkperm_Container($fid, 'upload')) {
                exit(json_encode(array('error' => lang('folder_upload_no_privilege'))));
            }
            $content = ' ';
    }
    if ($filename) {
        $arr = IO::upload_by_content($content, $fid, $filename);
        if ($arr['error']) {
        } else {
            $arr['msg'] = 'success';
        }
    } else {
        $arr = array();
        $arr['error'] = lang('new_failure');
    }
    exit(json_encode($arr));
} elseif ($operation == 'getfid') {//获取路径对应目录
    $path = isset($_GET['name']) ? trim($_GET['name']) : '';
    $prefix = isset($_GET['prefix']) ? trim($_GET['prefix']) : '';
    $arr = array();
    if ($fid = C::t('resources_path')->fetch_by_path($path, $prefix, $uid)) {
        if (preg_match('/c_\d+/', $fid)) {
            $arr['cid'] = str_replace('c_', '', $fid);
        } else {
            $folderarr = C::t('folder')->fetch($fid);
            if ($folderarr['gid']) {
                $arr['gid'] = $folderarr['gid'];
                if ($folderarr['flag'] != 'organization') {
                    $arr['fid'] = $fid;
                }
            } else {
                $arr['fid'] = $fid;
            }
        }
        exit(json_encode(array('success' => $arr, 'json')));
    } else {
        exit(json_encode(array('error' => true, 'json')));
    }
} elseif ($operation == 'property') {//属性
    $paths = isset($_GET['paths']) ? trim($_GET['paths']) : '';
    $fid = 0;
    if (preg_match('/fid_/', $paths)) {
        $fid = intval(preg_replace('/fid_/', '', $paths));
    }
    if ($fid) {
        if ($rid = C::t('resources')->fetch_rid_by_fid($fid)) {
            $propertys = C::t('resources')->get_property_by_rid($rid);
        } else {
            $propertys = C::t('resources')->get_property_by_fid($fid);
        }
    } else {
        $patharr = explode(',', $paths);
        $rids = array();
        foreach ($patharr as $v) {
            $rids[] = dzzdecode($v);
        }
        $propertys = C::t('resources')->get_property_by_rid($rids);
    }
    if ($propertys['error']) {
        $error = $propertys['error'];
    }
}
include template('fileselection/ajax');