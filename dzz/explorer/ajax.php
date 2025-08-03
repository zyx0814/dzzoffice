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
    $bz = isset($_GET['bz']) ? trim($_GET['bz']) : '';
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
} elseif ($operation == 'app') {
    $applist = $_GET['data'];
    //获取已安装应用
    $app = C::t('app_market')->fetch_all_by_appid($applist);
    $applist_1 = array();
    foreach ($app as $key => $value) {
        if ($value['isshow'] < 1) continue;
        if ($value['available'] < 1) continue;
        if ($value['system'] == 2) continue;
        $applist_1[$key] = $value;

    }

    exit(json_encode($applist_1));

} elseif ($operation == 'selectperm') {

    $fid = isset($_GET['fid']) ? intval($_GET['fid']) : '';
    $gid = isset($_GET['gid']) ? intval($_GET['gid']) : '';
    $inherit = true;//是否允许继承上级权限

    //如果是顶级群组的文件夹权限不允许继承上级权限
    if ($gid && $orginfo = C::t('organization')->fetch($gid)) {
        if ($fid == $orginfo['fid']) {
            $inherit = false;
        } else {
            $folderinfo = C::t('folder')->fetch($fid);
            $inheritperm = DB::result_first("select perm from %t where fid = %d", array('folder', $folderinfo['pfid']));
        }
    } else {
        $folderinfo = C::t('folder')->fetch($fid);
    }

    //是否是新建权限
    $new = (isset($_GET['new']) && $_GET['new']) ? 1 : 0;

    $setting = (isset($_GET['setting']) && $_GET['setting']) ? 1 : 0;

    //获取权限
    $groupperm = intval(C::t('folder')->fetch_perm_by_fid($fid));

    //获取权限组
    $permgroups = C::t('resources_permgroup')->fetch_all();

    $perms = get_permsarray();//获取所有权限
    //设置权限
    if (isset($_GET['permsubmit']) && $_GET['permsubmit']) {
        $perms = isset($_GET['selectperm']) ? $_GET['selectperm'] : array();
        $perm = 0;
        if (!empty($perms)) {
            foreach ($perms as $v) {
                $perm += intval($v);
            }
            $perm += 1;
        }
        if ($perm == $groupperm) exit(json_encode(array('success' => true)));
        if (!$inherit && !$perm) exit(json_encode(array('error' => true)));
        $fid = intval($_GET['fid']);
        if (C::t('folder')->update($fid, array('perm' => $perm))) {
            //如果是编辑权限，增加相关事件
            if (!$new) {
                //增加群组事件
                if ($orginfo && !$inherit) {
                    $eventdata = array('username' => getglobal('username'), 'uid' => getglobal('uid'), 'folder' => $orginfo['orgname']);
                    C::t('resources_event')->addevent_by_pfid($fid, 'set_group_perm', 'setperm', $eventdata, $gid, '', $orginfo['orgname']);
                } else {//增加文件夹事件
                    $rid = C::t('resources')->fetch_rid_by_fid($fid);
                    $path = C::t('resources_path')->fetch_pathby_pfid($fid);
                    $realpath = preg_replace('/dzz:(.+?):/', '', $path);
                    $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($fid, $gid);
                    $eventdata = array('username' => getglobal('username'), 'uid' => getglobal('uid'), 'position' => $realpath, 'hash' => $hash);
                    C::t('resources_event')->addevent_by_pfid($fid, 'set_folder_perm', 'setperm', $eventdata, $gid, $rid, $folderinfo['fname']);
                }
            }
            exit(json_encode(array('success' => true, 'perm' => $perm)));
        } else {
            exit(json_encode(array('error' => true)));
        }

    }
} elseif ($operation == 'addgroup') {//添加群组
    if (isset($_GET['arr'])) {
        $arr = $_GET['arr'];
        $groupname = isset($arr['orgname']) ? getstr($arr['orgname']) : '';
        $img = isset($arr['aid']) ? trim($arr['aid']) : '';
        $groupmemorysetting = getglobal('groupmemorySpace', 'setting');
        //if (!$img) exit(json_encode(array('error' => true, 'msg' => '请选择或者上传一张图片，作为群组头像', 'pos' => 'img')));
        if (preg_match('/^\s*$/', $groupname)) exit(json_encode(array('error' => true, 'msg' => '群组名不能为空', 'pos' => 'name')));
        if (!C::t('organization')->chk_by_orgname($groupname, 1)) showTips(array('error' => true, 'msg' => '群组名已被占用', 'pos' => 'name'), 'json');
        $setarr = array(
            'orgname' => $groupname,
            'aid' => $img,
            'desc' => htmlspecialchars(trim($arr['desc'])),
            'type' => 1,
            'dateline' => TIMESTAMP,
            'maxspacesize' => $groupmemorysetting,
            'manageon' => 1,
            'diron' => 1
        );
        if ($return = C::t('organization')->insert_by_orgid($setarr)) {
            if ($return) exit(json_encode(array('success' => true, 'gid' => $return)));
            else exit(json_encode(array('error' => true, 'msg' => lang('create_group_failed'))));
        } else {
            exit(json_encode(array('error' => true)));
        }
    }

} elseif ($operation == 'newFolder') {//新建文件夹
    $fid = isset($_GET['fid']) ? trim($_GET['fid']) : '';
    $bz = isset($_GET['bz']) ? trim($_GET['bz']) : '';
    $folderinfo = C::t('folder')->fetch($fid);
    $noperm = 1;
    if ($folderinfo['gid'] && C::t('organization_admin')->chk_memberperm($folderinfo['gid'])) {
        $noperm = 0;
        $inheritperm = DB::result_first("select perm from %t where fid = %d", array('folder', $fid));
    }
    $name = !empty($_GET['foldername']) ? trim($_GET['foldername']) : lang('newfolder');
    if (isset($_GET['createfolder'])) {
        $perm = 0;
        $fname = IO::name_filter(getstr($name, 80));
        if ($bz) {
            $fid = $bz;
        } else {
            $fid = intval($fid);
            $perms = isset($_GET['selectperm']) ? $_GET['selectperm'] : array();
            if (!empty($perms) && $perms) {
                foreach ($perms as $v) {
                    $perm += intval($v);
                }
                $perm += 1;
            }
        }
        if(!$fid) exit(json_encode(array('error'=>lang('no_target_folderID'))));
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
    } else {
        $permgroups = C::t('resources_permgroup')->fetch_all(true);
        $perms = get_permsarray();//获取所有权限
        $permselect = true;
    }


} elseif ($operation == 'newLink') {//新建连接
    $fid = isset($_GET['fid']) ? intval($_GET['fid']) : '';
    if (!perm_check::checkperm_Container($fid, 'upload', $bz)) {
        $arr = array('error' => lang('no_privilege'));
    }
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

            $ext = strtolower(substr(strrchr($link, '.'), 1, 10));
            $isimage = in_array(strtoupper($ext), $imageexts) ? 1 : 0;
            $ismusic = 0;

            //是图片时处理
            if ($isimage) {
                if (!perm_check::checkperm_Container($fid, 'upload')) {
                    $arr['error'] = lang('target_not_accept_image');
                }
                if ($data = io_dzz::linktoimage($link, $fid)) {
                    if ($data['error']) $arr['error'] = $data['error'];
                    else {
                        $arr = $data;
                        $arr['msg'] = 'success';
                    }
                }

            } else {
                //试图作为视频处理
                if ($data = io_dzz::linktovideo($link, $fid)) {
                    if (!perm_check::checkperm_Container($fid, 'upload')) {
                        $arr['error'] = lang('target_not_accept_video');
                    } else {
                        if ($data['error']) $arr['error'] = $data['error'];
                        else {
                            $arr = $data;
                            $arr['msg'] = 'success';
                        }
                    }
                }
                //作为网址处理
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
    }
    exit(json_encode($arr));
} elseif ($operation == 'showtips') {
    $msgtext = isset($_GET['msg']) ? trim($_GET['msg']) : lang('system_unknow_error');
} elseif ($operation == 'dzzdocument' || $operation == 'txt') {//新建文档
    if ($operation == 'dzzdocument') {
        $ext = 'dzzdoc';
    } else {
        $ext = 'txt';
    }
    $name = lang('new_' . $ext);
    $filename = $name . '.' . $ext;
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
    $bzpath = isset($_GET['bz']) ? trim($_GET['bz']) : '';
    $fid = intval($_GET['fid']);
    $filename = '';
    $bz = getBzByPath($fid);
    switch ($type) {
        case 'newTxt':
            $filename = lang('new_txt') . '.txt';
            if (!perm_check::checkperm_Container($fid, 'upload', $bz)) {
                exit(json_encode(array('error' => lang('privilege'))));
            }
            $content = ' ';
            break;
        case 'newDzzDoc':
            $filename = lang('new_dzzdoc') . '.dzzdoc';
            if (!perm_check::checkperm_Container($fid, 'upload', $bz)) {
                exit(json_encode(array('error' => lang('privilege'))));
            }
            $content = ' ';
            break;
        case 'newDoc':
            $filename = lang('new_word') . '.docx';
            if (!perm_check::checkperm_Container($fid, 'upload', $bz)) {
                exit(json_encode(array('error' => lang('privilege'))));
            }
            $content = file_get_contents(DZZ_ROOT . './dzz/images/newfile/word.docx');
            break;
        case 'newExcel':
            $filename = lang('new_excel') . '.xlsx';
            if (!perm_check::checkperm_Container($fid, 'upload', $bz)) {
                exit(json_encode(array('error' => lang('privilege'))));
            }
            $content = file_get_contents(DZZ_ROOT . './dzz/images/newfile/excel.xlsx');
            break;
        case 'newPowerPoint':
            $filename = lang('new_PowerPoint') . '.pptx';
            if (!perm_check::checkperm_Container($fid, 'upload', $bz)) {
                exit(json_encode(array('error' => lang('privilege'))));
            }
            $content = file_get_contents(DZZ_ROOT . './dzz/images/newfile/ppt.pptx');
            break;
        case 'newpdf':
            $filename = lang('new_pdf') . '.pdf';
            if (!perm_check::checkperm_Container($fid, 'upload', $bz)) {
                exit(json_encode(array('error' => lang('privilege'))));
            }
            $content = file_get_contents(DZZ_ROOT . './dzz/images/newfile/pdf.pdf');
            break;
    }
    if ($bzpath) {
        $fid = $bzpath;
    }
    if ($arr = IO::upload_by_content($content, $fid, $filename)) {
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
    if ($fid = C::t('resources_path')->fetch_by_path($path, $prefix)) {
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
} elseif ($operation == 'uploadfile') {//上传文件获取相关文件信息
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    if ($rid) {
        $arr = C::t('resources')->fetch_by_rid($rid);
    } else {
        $arr = array('error' => lang('system_busy'));
    }
} elseif ($operation == 'getfolder') {//获取文件夹信息
    $fid = isset($_GET['fid']) ? intval($_GET['fid']) : '';
    if ($fid) {
        $arr = C::t('resources')->fetch_by_oid($fid);
    } else {
        $arr = array('error' => lang('system_busy'));
    }

} elseif ($operation == 'collect') {//收藏与取消收藏
    $paths = $_GET['paths'];
    //collect参数为1为收藏,否则为取消收藏,未接收到此参数，默认为收藏
    $collect = isset($_GET['collect']) ? $_GET['collect'] : 1;
    $rids = array();
    foreach ($paths as $v) {
        $rids[] = dzzdecode($v);
    }
    if ($collect) {//加入收藏
        $return = C::t('resources_collect')->add_collect_by_rid($rids);
        exit(json_encode($return));
    } else {//取消收藏
        $return = C::t('resources_collect')->delete_usercollect_by_rid($rids);
        exit(json_encode($return));
    }
} elseif ($operation == 'tag') {
    $rid = isset($_GET['rid']) ? $_GET['rid'] : '';
    if (!$fileinfo = C::t('resources')->fetch_info_by_rid($rid)) {
        showTips(array('error' => true), 'json');
    }
    $tags = C::t('resources_tag')->fetch_tag_by_rid($rid);
    if (isset($_GET['addtag']) && $_GET['addtag']) {
        $tags = isset($_GET['tags']) ? $_GET['tags'] : '';
        $tagsarr = array_filter(explode(',', $tags));
        if (empty($tagsarr)) {
            exit(json_encode(array('error' => lang('tag_name_ismust'))));
        }
        $tagsubmit = array();
        foreach ($tagsarr as $v) {
            $tagsubmit[] = getstr($v);
        }
        if ($insert = C::t('resources_tag')->insert_data($rid, $tagsubmit)) {
            $statisarr = array(
                'uid' => $uid,
                'edits' => 1,
                'editdateline' => TIMESTAMP
            );
            C::t('resources_statis')->add_statis_by_rid($rid, $statisarr);
            showTips(array('success' => true, 'tagsadd' => $insert['add'], 'tagsdel' => $insert['del']), 'json');
        } else {
            showTips(array('error' => true), 'json');
        }
    } else {
        $tagarr = array();
        $tagval = array();
        foreach ($tags as $v) {
            $tagarr[] = array('name' => $v['tagname']);
            $tagval[] = $v['tagname'];
        }
        $tagstr = htmlspecialchars(json_encode($tagarr));
        $tagval = implode(',', $tagval);
    }
} elseif ($operation == 'comment') {
    include_once libfile('function/code');
    include_once libfile('function/use');
    $fid = intval($_GET['fid']);
    $rid = trim($_GET['rid']);
    $msg = isset($_GET['msg']) ? censor($_GET['msg']) : '';
    //获得提醒用户
    $at_users = array();
    $message = preg_replace_callback("/@\[(.+?):(.+?)\]/i", "atreplacement", $msg);
    $appid = C::t('app_market')->fetch_appid_by_mod('{dzzscript}?mod=' . MOD_NAME, 1);
    if ($rid) {
        if (!$file = C::t('resources')->fetch_info_by_rid($rid)) {
            exit(json_encode(array('error' => '未查询到该文件信息')));
        } else {
            if (!perm_check::checkperm_Container($file['oid'], 'comment')) {
                exit(json_encode(array('error' => lang('file_comment_no_privilege'))));
            }
            $eventdata = array('msg' => $msg);
            if ($insert = C::t('resources_event')->addevent_by_pfid($file['pfid'], 'add_comment', 'addcomment', $eventdata, $file['gid'], $rid, $file['name'], 1)) {
                $return = array(
                    'username' => getglobal('username'),
                    'uid' => getglobal('uid'),
                    'dateline' => dgmdate(TIMESTAMP, 'u'),
                    'msg' => dzzcode($message),
                    'commentid' => $insert,
                    'avatar' => avatar_block($_G['uid'])
                );
                if ($file['uid'] != getglobal('uid')) {
                    $notevars = array(
                        'from_id' => $appid,
                        'from_idtype' => 'app',
                        'url' => ($file['gid'] > 0) ? $_G['siteurl'] . MOD_URL . '#group&do=file&gid=' . $file['gid'] . '&fid=' . $file['pfid'] : $_G['siteurl'] . MOD_URL . '#home&do=file&fid=' . $file['pfid'],
                        'author' => getglobal('username'),
                        'authorid' => getglobal('uid'),
                        'dataline' => dgmdate(TIMESTAMP),
                        'fname' => getstr($file['name'], 31),
                        'comment' => ($message) ? getstr(dzzcode($message)) : '',
                    );
                    $action = 'explorer_comment_mydoc';
                    $type = 'explorer_comment_mydoc_' . $file['pfid'];
                    dzz_notification::notification_add($file['uid'], $type, $action, $notevars, 1, 'dzz/explorer');
                }
                if ($at_users) {//提醒相关人员
                    foreach ($at_users as $uid) {
                        if ($uid != getglobal('uid')) {
                            //发送通知
                            $notevars = array(
                                'from_id' => $appid,
                                'from_idtype' => 'app',
                                'url' => ($file['gid'] > 0) ? $_G['siteurl'] . MOD_URL . '#group&do=file&gid=' . $file['gid'] . '&fid=' . $file['pfid'] : $_G['siteurl'] . MOD_URL . '#home&do=file&fid=' . $file['pfid'],
                                'author' => getglobal('username'),
                                'authorid' => getglobal('uid'),
                                'dataline' => dgmdate(TIMESTAMP),
                                'fname' => getstr($file['name'], 31),
                                'comment' => ($message) ? getstr($message) : '',

                            );
                            $action = 'explorer_comment_at';
                            $type = 'explorer_comment_at' . $file['pfid'];
                            dzz_notification::notification_add($uid, $type, $action, $notevars, 0, MOD_PATH);
                        }
                    }
                }

                showTips(array('success' => true, 'return' => $return, 'json'));
            }

        }
    } else {
        if (!$folder = C::t('folder')->fetch($fid)) {
            exit(json_encode(array('error' => '没有查询到该文件夹信息')));
        } else {
            if (!perm_check::checkperm_Container($fid, 'comment')) {
                exit(json_encode(array('error' => lang('folder_comment_no_privilege'))));
            }
            $rid = C::t('resources')->fetch_rid_by_fid($fid);
            $eventdata = array('msg' => $msg);
            if ($insert = C::t('resources_event')->addevent_by_pfid($fid, 'add_comment', 'addcomment', $eventdata, $folder['gid'], ($rid) ? $rid : '', $folder['fname'], 1)) {
                $return = array(
                    'username' => getglobal('username'),
                    'uid' => getglobal('uid'),
                    'dateline' => dgmdate(TIMESTAMP, 'u'),
                    'msg' => dzzcode($message),
                    'commentid' => $insert,
                    'avatar' => avatar_block($_G['uid'])
                );
                if ($folder['uid'] != getglobal('uid')) {
                    $notevars = array(
                        'from_id' => $appid,
                        'from_idtype' => 'app',
                        'url' => ($folder['gid'] > 0) ? $_G['siteurl'] . MOD_URL . '#group&do=file&gid=' . $folder['gid'] . '&fid=' . $folder['fid'] : $_G['siteurl'] . MOD_URL . '#home&do=file&fid=' . $folder['fid'],
                        'author' => getglobal('username'),
                        'authorid' => getglobal('uid'),
                        'dataline' => dgmdate(TIMESTAMP),
                        'fname' => getstr($folder['fname'], 31),
                        'comment' => ($message) ? getstr(dzzcode($message)) : '',
                    );
                    $action = 'explorer_comment_mydoc';
                    $type = 'explorer_comment_mydoc_' . $fid;

                    dzz_notification::notification_add($folder['uid'], $type, $action, $notevars, 0, 'dzz/explorer');
                }
                if ($at_users) {//提醒相关人员
                    foreach ($at_users as $uid) {
                        if ($uid != getglobal('uid')) {
                            //发送通知
                            $notevars = array(
                                'from_id' => $appid,
                                'from_idtype' => 'app',
                                'url' => ($folder['gid'] > 0) ? $_G['siteurl'] . MOD_URL . '#group&do=file&gid=' . $folder['gid'] . '&fid=' . $folder['fid'] : $_G['siteurl'] . MOD_URL . '#home&do=file&fid=' . $folder['fid'],
                                'author' => getglobal('username'),
                                'authorid' => getglobal('uid'),
                                'dataline' => dgmdate(TIMESTAMP),
                                'fname' => getstr($folder['fname'], 31),
                                'comment' => ($message) ? getstr($message) : '',

                            );
                            $action = 'explorer_comment_at';
                            $type = 'explorer_comment_at_' . $fid;

                            dzz_notification::notification_add($uid, $type, $action, $notevars, 0, MOD_PATH);
                        }
                    }
                }

                showTips(array('success' => true, 'return' => $return, 'json'));
            }
        }

    }

} elseif ($operation == 'addsearchcat') {//增加类型筛选
    $id = isset($_GET['id']) ? intval($_GET['id']) : '';
    if ($id) {
        $cat = C::t('resources_cat')->fetch_by_id($id);
        $cattidarr = explode(',', $cat['tag']);
        $tags = '';
        foreach (C::t('tag')->fetch_tag_by_tid($cattidarr, 'explorer') as $v) {
            $tags .= $v['tagname'] . ',';
        }

        $cat['tag'] = substr($tags, 0, -1);
    }
    if (isset($_GET['editcatsearch'])) {
        $id = $_GET['editcatsearch'];
        $arr = $_GET['arr'];
        if (!$arr['catname'] || preg_match('/^\s*$/', $arr['catname'])) {
            exit(json_encode(array('error' => true, 'msg' => lang('name_is_must'))));
        }

        $catoldid = DB::result_first("select id from %t where catname = %s and uid = %d", array('resources_cat', $arr['catname'], $uid));
        if ($catoldid && $catoldid != $id) {
            exit(json_encode(array('error' => true, 'msg' => lang('typename_must_only'))));
        }
        //处理后缀名
        if ($arr['ext']) {
            $qualifiedExt = array();
            $extarr = explode(',', $arr['ext']);
            foreach ($extarr as $v) {
                if (!preg_match('/^\.\w+$/', $v)) {
                    $v = '.' . strtolower($v);
                }
                if (preg_match('/^\.\w+$/', $v)) {
                    $qualifiedExt[] = strtolower($v);
                }

            }
            $qualifiedExt = array_unique($qualifiedExt);
            $arr['ext'] = implode(',', $qualifiedExt);
        }
        if (!$arr['ext']) {
            exit(json_encode(array('error' => true, 'msg' => lang('cat_is_must'))));
        }
        if (C::t('resources_cat')->update($id, $arr)) {
            exit(json_encode(array('success' => true)));
        } else {
            exit(json_encode(array('error' => true)));
        }
    }
    if (isset($_GET['addcatsearch'])) {
        $arr = $_GET['arr'];
        //处理名称
        if (!$arr['catname'] || preg_match('/^\s*$/', $arr['catname'])) {
            exit(json_encode(array('error' => true, 'msg' => lang('name_is_must'))));
        }
        if (DB::result_first("select count(*) from %t where catname = %s and uid = %d", array('resources_cat', $arr['catname'], $uid)) > 0) {
            exit(json_encode(array('error' => true, 'msg' => lang('typename_must_only'))));
        }
        //处理处理后缀名
        if ($arr['ext']) {
            $qualifiedExt = array();
            $extarr = explode(',', $arr['ext']);
            foreach ($extarr as $v) {
                if (!preg_match('/^\.\w+$/', $v)) {
                    $v = '.' . strtolower($v);
                }
                if (preg_match('/^\.\w+$/', $v)) {
                    $qualifiedExt[] = strtolower($v);
                }
            }
            $qualifiedExt = array_unique($qualifiedExt);
            $arr['ext'] = implode(',', $qualifiedExt);
        }
        if (!$arr['ext']) {
            exit(json_encode(array('error' => true, 'msg' => lang('cat_is_error'))));
        }
        $arr['uid'] = $uid;
        $insert = C::t('resources_cat')->insert_cat($arr);
        if ($insert['success']) {
            exit(json_encode(array('success' => true, 'insertid' => $insert['insert'])));
        } else {
            exit(json_encode(array('error' => true, 'msg' => $insert['msg'])));
        }
    }

} elseif ($operation == 'delsearchcat') {//删除筛选类型
    $catid = isset($_GET['id']) ? intval($_GET['id']) : '';
    if ($_GET['delcat']) {
        if (C::t('resources_cat')->del_by_id($catid)) {
            $previd = C::t('resources_cat')->fetch_rencent_id($catid);
            exit(json_encode(array('success' => true, 'catid' => $catid, 'previd' => $previd)));
        } else {
            exit(json_encode(array('error' => true)));
        }
    } else {
        exit(json_encode(array('error' => true)));
    }

} elseif ($operation == 'share') {//分享
    $bz = isset($_GET['bz']) ? trim($_GET['bz']) : '';
    $table = isset($_GET['table']) ? trim($_GET['table']) : '';
    if (isset($_GET['paths'])) {
        $patharr = explode(',', $_GET['paths']);
        //判断是否是分享id
        if (count($patharr) == 1 && preg_match('/^\d+$/', $patharr[0])) {
            $shareid = $patharr[0];
        } else {
            $rids = array();
            foreach ($patharr as $v) {
                $rids[] = dzzdecode($v);
            }
            $files = implode(',', $rids);
        }
    } else {
        $files = $_GET['rid'];
    }
    if (isset($_GET['share'])) {
        if (isset($_GET['delshare']) && $_GET['delshare'] == 1 && isset($_GET['do']) && $_GET['do'] == 'del') {
            $id = isset($_GET['id']) ? intval($_GET['id']) : '';
            $return = C::t('shares')->delete_by_id($id);
            if ($return['success']) {
                showTips(array('success' => true, 'shareid' => $id));
            } else {
                showTips(array('error' => $return['error']));
            }
        } else {
            $share = $_GET['share'];
            $share['filepath'] = trim($_GET['rid']);
            $share['title'] = getstr($share['title']);
            if ($share['endtime']) $share['endtime'] = strtotime($share['endtime']) + 24 * 60 * 60;
            if ($share['password']) $share['password'] = dzzencode($share['password']);
            $share['times'] = intval($share['times']);
            $perm = isset($_GET['perm']) ? $_GET['perm'] : [];
            if (is_array($perm)) {
                $share['perm'] = implode(',', $perm);
            }
            if (isset($_GET['id']) && $_GET['id']) $id = intval($_GET['id']);
            
            if ($id) {
                if ($ret = C::t('shares')->update_by_id($id, $share,$bz)) {
                    showTips(array('success' => true, 'shareurl' => C::t('shorturl')->getShortUrl('index.php?mod=shares&sid=' . dzzencode($ret)), 'shareid' => $ret));
                } elseif ($ret['error']) {
                    showTips(array('error' => $ret['error']), 'json');
                } else {
                    showTips(array('error' => lang('create_share_failer') . '！'), 'json');
                }
            } else {
                if($bz) {
                    $bzinfo = IO::getMeta($files);
                    if ($share['error']) showTips(array('error' => $share['error']), 'json');
                    $share['type'] = $bzinfo['type'];
                }
                $ret = C::t('shares')->insert($share,$bz);
                if ($ret['success']) {
                    showTips(array('success' => true, 'shareurl' => C::t('shorturl')->getShortUrl('index.php?mod=shares&sid=' . dzzencode($ret['success'])), 'shareid' => $ret['success']));
                } elseif ($ret['error']) {
                    showTips(array('error' => $ret['error']), 'json');
                } else {
                    showTips(array('error' => lang('create_share_failer') . '！'), 'json');
                }
            }
        }
    } else {
        if ($shareid) {
            if ($share = C::t('shares')->fetch($shareid)) {
                $share['shareurl'] = C::t('shorturl')->getShortUrl('index.php?mod=shares&sid=' . dzzencode($share['id']));
                if ($share['password']) $share['password'] = dzzdecode($share['password']);
                if ($share['status'] >= -2) {
                    if ($share['endtime'] && $share['endtime'] < TIMESTAMP) $share['status'] = -1;
                    elseif ($share['times'] && $share['times'] <= $share['count']) $share['status'] = -2;
                    else $share['status'] = 0;
                }
                if ($share['endtime']) $share['endtime'] = dgmdate($share['endtime'], 'Y-m-d');
                if (!$share['times']) {
                    $share['times'] = '';
                }
                $files = $share['filepath'];
                $share['perm'] = explode(',', $share['perm']);
                if($share['pfid']==-1) {
                    $bz = 1;
                }
            }
        } else {
            if ($share = C::t('shares')->fetch_by_path($files)) {
                $share['shareurl'] = C::t('shorturl')->getShortUrl('index.php?mod=shares&sid=' . dzzencode($share['id']));
                if ($share['password']) $share['password'] = dzzdecode($share['password']);
                if ($share['status'] >= -2) {
                    if ($share['endtime'] && $share['endtime'] < TIMESTAMP) $share['status'] = -1;
                    elseif ($share['times'] && $share['times'] <= $share['count']) $share['status'] = -2;
                    else $share['status'] = 0;
                }
                if ($share['endtime']) $share['endtime'] = dgmdate($share['endtime'], 'Y-m-d');
                if (!$share['times']) {
                    $share['times'] = '';
                }
                $share['perm'] = explode(',', $share['perm']);
                if($share['pfid']==-1) {
                    $bz = 1;
                }
            } else {
                if($bz) {
                    $share = IO::getMeta($files);
                    if ($share['error']) {
                        $arr = array('error' => $share['error']);
                    } else {
                        $share['title'] = $share['name'];
                    }
                } else {
                    $rids = explode(',', $files);
                    //默认单个文件分享
                    $more = false;
                    //多个文件分享
                    if (count($rids) > 1) $more = true;
                    $filenames = array();
                    $gidarr = array();
                    foreach (DB::fetch_all("select pfid,name,gid from %t where rid in(%n)", array('resources', $rids)) as $v) {
                        if (!perm_check::checkperm_Container($v['pfid'], 'share')) {
                            $arr = array('error' => lang('no_privilege'));
                        } else {
                            $gidarr[] = $v['gid'];
                            $filenames[] = $v['name'];
                        }
                    }
                    //判断文件来源
                    if (count(array_unique($gidarr)) > 1) {
                        $arr = array('error' => lang('share_notallow_from_different_zone'));
                    }
                    //自动生成分享标题
                    if ($more) {
                        $share['title'] = $filenames[0] . lang('more_file_or_folder');
                    } else {
                        $share['title'] = $filenames[0];
                    }
                }
            }
        }
    }
} elseif ($operation == 'property') {//属性
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
                showmessage(lang('no_privilege'));
            }
            $contains = IO::getContains($propertys['path']);
            $propertys['type'] = lang('type_folder');
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
                showmessage(lang('no_privilege'));
            }
            if ($propertys['type'] == 'folder') {
                $contains = IO::getContains($propertys['path']);
                $propertys['type'] = lang('type_folder');
                $propertys['ffsize'] = lang('property_info_size', array('fsize' => formatsize($contains['size']), 'size' => $contains['size']));
                $propertys['contain'] = lang('property_info_contain', array('filenum' => $contains['contain'][0], 'foldernum' => $contains['contain'][1]));
            }
        }
        $propertys['type'] = $propertys['ftype'];
    } else {
        if (intval($fid)) {
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
            if (!$propertys['ismulti']) {
                $attrdata = C::t('resources_attr')->fetch_by_rid($propertys['rid'], $propertys['vid']);
                if ($_G['adminid'] && $attrdata['aid']) {
                    $attachment = IO::getStream('attach::' . $attrdata['aid']);
                }
            }
        }
        if ($propertys['error']) {
            $error = $propertys['error'];
        }
    }

} elseif ($operation == 'editFileVersionInfo') {
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    $vid = isset($_GET['vid']) ? intval($_GET['vid']) : 0;
    $versioninfo = C::t('resources_version')->get_versioninfo_by_rid_vid($rid, $vid);
} elseif ($operation == 'infoversion') {
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    $vid = isset($_GET['vid']) ? intval($_GET['vid']) : 0;

    $versioninfo = C::t('resources_version')->get_versioninfo_by_rid_vid($rid, $vid);
    if ($versioninfo['rid']) {
        $propertys = C::t('resources')->get_property_by_rid($versioninfo['rid']);
    } else {
        $error = lang('file_not_exist');
    }
    if ($versioninfo['aid']) {
        $attachment = IO::getFileUri('attach::' . $versioninfo['aid']);
    }
} elseif ($operation == 'deletethisversion') {
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    $vid = isset($_GET['vid']) ? intval($_GET['vid']) : 0;
    if (!$rid || !$vid) {
        exit(json_encode(array('error' => 'access denied')));
    }
    $fileinfo = C::t('resources')->get_property_by_rid($rid);
    if ($fileinfo['editperm']) {
        if (C::t('resources_version')->delete_by_vid($vid, $rid, true)) {
            exit(json_encode(array('msg' => 'success')));
        } else {
            exit(json_encode(array('error' => '该版本不存在或最后一个不能删除')));
        }
    } else {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }

} elseif ($operation == 'addIndex') {//索引文件
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
} elseif ($operation == 'updateIndex') {
    $arr = isset($_GET['arr']) ? $_GET['arr'] : '';
    if (empty($arr)) {
        exit(json_encode(array('error' => '缺少数据')));
    }
    $rid = isset($arr['rid']) ? trim($arr['rid']) : '';
    if (!$rid) exit(json_encode(array('error' => '缺少数据')));
    $vid = isset($arr['vid']) ? intval($_GET['vid']) : 0;
    $result = Hook::listen('solredit', $setarr);
    if ($result[0]['error']) {
        exit(json_encode(array('error' => $result[0]['error'])));
    } else {
        exit(json_encode(array('success' => true)));
    }
} elseif ($operation == 'deleteIndex') {
    $rids = $_GET['rids'];
    $ids = array();
    foreach ($rids as $v) {
        $ids[] = $v . '_' . '0';
    }
    Hook::listen('solrdel', $ids);
    exit(json_encode(array('success' => true)));
}
include template('ajax');