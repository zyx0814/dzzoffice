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
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if ($do == 'upload') {//上传图片文件
    include libfile('class/uploadhandler');
    $options = array('accept_file_types' => '/\.(gif|jpe?g|png)$/i',
        'upload_dir' => $_G['setting']['attachdir'] . 'cache/',
        'upload_url' => $_G['setting']['attachurl'] . 'cache/',
        'thumbnail' => array('max-width' => 40, 'max-height' => 40));
    $upload_handler = new uploadhandler($options);
    exit();
} elseif ($do == 'uploads') {//上传新文件(指新建)
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
} elseif ($do == 'uploadfiles') {//上传文件(单纯的上传)
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
} elseif ($do == 'selectperm') {
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    $fid = isset($_GET['fid']) ? intval($_GET['fid']) : '';
    if ($rid) {
        $fileinfo = C::t('resources')->get_property_by_rid($rid,false);
    } else {
        $fileinfo = C::t('resources')->get_property_by_fid($fid,false);
    }
    if($fileinfo['error']) showmessage($fileinfo['error']);
    $inherit = true;//是否允许继承上级权限
    if ($fileinfo['gid']) {
        $usergroupperm = perm_check::checkgroupPerm($fileinfo['gid'], 'admin');//判断管理员权限
        if(!$usergroupperm) showmessage('no_privilege');
        //如果是顶级群组的文件夹权限不允许继承上级权限
        if ($orginfo = C::t('organization')->fetch($fileinfo['gid'])) {
            if ($fid == $orginfo['fid']) {
                $inherit = false;
            } else {
                $inheritperm = DB::result_first("select perm from %t where fid = %d", array('folder', $fileinfo['pfid']));
            }
        }
    }
    //是否是新建权限
    $new = (isset($_GET['new']) && $_GET['new']) ? 1 : 0;
    $setting = (isset($_GET['setting']) && $_GET['setting']) ? 1 : 0;
    //获取权限
    if ($fileinfo['isfolder']) {
        $fperm = C::t('folder')->fetch_perm_by_fid($fileinfo['fid']);
    } else {
        $fperm = $fileinfo['sperm'];
    }
    
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
        if ($perm == $fperm) exit(json_encode(array('success' => true)));
        if ($fileinfo['isfolder']) {
            if(!$perm) exit(json_encode(array('msg' => '目录权限不允许为空')));
            if (C::t('folder')->update($fileinfo['fid'], array('perm' => $perm))) {
                //如果是编辑权限，增加相关事件
                if (!$new) {
                    //增加群组事件
                    $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($fileinfo['fid'], $fileinfo['gid']);
                    if ($orginfo && !$inherit) {
                        $eventdata = array('username' => $_G['username'], 'uid' => $_G['uid'], 'folder' => $orginfo['orgname'], 'hash' => $hash);
                        C::t('resources_event')->addevent_by_pfid($fileinfo['fid'], 'set_group_perm', 'setperm', $eventdata, $fileinfo['gid'], '', $orginfo['orgname']);
                    } else {//增加文件夹事件
                        $eventdata = array('username' => $_G['username'], 'uid' => $_G['uid'], 'position' => $fileinfo['realpath'] . $fileinfo['name'], 'hash' => $hash);
                        C::t('resources_event')->addevent_by_pfid($fileinfo['fid'], 'set_folder_perm', 'setperm', $eventdata, $fileinfo['gid'], $fileinfo['rid'], $fileinfo['name']);
                    }
                }
                exit(json_encode(array('success' => true, 'perm' => $perm)));
            } else {
                exit(json_encode(array('msg' => lang('save_unsuccess'))));
            }
        } else {
            if (C::t('resources')->update_by_rid($fileinfo['rid'], array('sperm' => $perm))) {
                $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($fileinfo['fid'], $fileinfo['gid']);
                $eventdata = array('username' => $_G['username'], 'uid' => $_G['uid'], 'position' => $fileinfo['realpath'] . $fileinfo['name'], 'hash' => $hash);
                C::t('resources_event')->addevent_by_pfid($fileinfo['fid'], 'set_folder_perm', 'setperm', $eventdata, $fileinfo['gid'], $fileinfo['rid'], $fileinfo['name']);
                exit(json_encode(array('success' => true, 'perm' => $perm)));
            } else {
                exit(json_encode(array('msg' => lang('save_unsuccess'))));
            }
        }
    } else {
        //获取权限组
        $permgroups = C::t('resources_permgroup')->fetch_all();
        //获取所有权限
        if ($fileinfo['isfolder']) {
            $perms = get_permsarray();
        } else {
            $perms = get_permsarray('document');
        }
    }
} elseif ($do == 'addgroup') {//添加群组
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

} elseif ($do == 'newFolder') {//新建文件夹
    $fid = isset($_GET['fid']) ? trim($_GET['fid']) : '';
    $bz = isset($_GET['bz']) ? trim($_GET['bz']) : '';
    $noperm = 1;
    if($fid) {
        $folderinfo = C::t('folder')->fetch($fid);
        if ($folderinfo['gid'] && C::t('organization_admin')->chk_memberperm($folderinfo['gid'])) {
            $noperm = 0;
            $inheritperm = DB::result_first("select perm from %t where fid = %d", array('folder', $fid));
        }
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
} elseif ($do == 'newLink') {//新建连接
    $fid = isset($_GET['fid']) ? intval($_GET['fid']) : '';
    if (!perm_check::checkperm_Container($fid, 'upload', $bz)) {
        $arr = array('error' => lang('folder_upload_no_privilege'));
    }
} elseif ($do == 'linkadd') {
    if (isset($_GET['createlink']) && $_GET['createlink']) {
        $link = isset($_GET['link']) ? trim($_GET['link']) : '';
        $name = isset($_GET['name']) ? trim($_GET['name']) : '';
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
    }
    exit(json_encode($arr));
} elseif ($do == 'showtips') {
    $msgtext = isset($_GET['msg']) ? trim($_GET['msg']) : lang('system_unknow_error');
} elseif ($do == 'txt') {//新建文档
    $filename = lang('new_txt') . '.txt';
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
} elseif ($do == 'newIco') {//新建文件
    $type = trim($_GET['type']);
    $bzpath = isset($_GET['bz']) ? trim($_GET['bz']) : '';
    $fid = intval($_GET['fid']);
    $filename = '';
    if ($bzpath) {
        $bz = getBzByPath($fid);
    } else {
        $bz = '';
    }
    switch ($type) {
        case 'newTxt':
            $filename = lang('new_txt') . '.txt';
            if (!perm_check::checkperm_Container($fid, 'upload', $bz)) {
                exit(json_encode(array('error' => lang('folder_upload_no_privilege'))));
            }
            $content = ' ';
            break;
        case 'newDzzDoc':
            $filename = lang('new_dzzdoc') . '.dzzdoc';
            if (!perm_check::checkperm_Container($fid, 'upload', $bz)) {
                exit(json_encode(array('error' => lang('folder_upload_no_privilege'))));
            }
            $content = ' ';
            break;
        case 'newDoc':
            $filename = lang('new_word') . '.docx';
            if (!perm_check::checkperm_Container($fid, 'upload', $bz)) {
                exit(json_encode(array('error' => lang('folder_upload_no_privilege'))));
            }
            $content = file_get_contents(DZZ_ROOT . './dzz/images/newfile/word.docx');
            break;
        case 'newExcel':
            $filename = lang('new_excel') . '.xlsx';
            if (!perm_check::checkperm_Container($fid, 'upload', $bz)) {
                exit(json_encode(array('error' => lang('folder_upload_no_privilege'))));
            }
            $content = file_get_contents(DZZ_ROOT . './dzz/images/newfile/excel.xlsx');
            break;
        case 'newPowerPoint':
            $filename = lang('new_PowerPoint') . '.pptx';
            if (!perm_check::checkperm_Container($fid, 'upload', $bz)) {
                exit(json_encode(array('error' => lang('folder_upload_no_privilege'))));
            }
            $content = file_get_contents(DZZ_ROOT . './dzz/images/newfile/ppt.pptx');
            break;
        case 'newpdf':
            $filename = lang('new_pdf') . '.pdf';
            if (!perm_check::checkperm_Container($fid, 'upload', $bz)) {
                exit(json_encode(array('error' => lang('folder_upload_no_privilege'))));
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
} elseif ($do == 'getfid') {//获取路径对应目录
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
} elseif ($do == 'uploadfile') {//上传文件获取相关文件信息
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    if ($rid) {
        $arr = C::t('resources')->fetch_by_rid($rid);
    } else {
        $arr = array('error' => lang('system_busy'));
    }
} elseif ($do == 'getfolder') {//获取文件夹信息
    $fid = isset($_GET['fid']) ? intval($_GET['fid']) : '';
    if ($fid) {
        $arr = C::t('resources')->fetch_by_oid($fid);
    } else {
        $arr = array('error' => lang('system_busy'));
    }

} elseif ($do == 'collect') {//收藏与取消收藏
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
} elseif ($do == 'tag') {
    $rid = isset($_GET['rid']) ? $_GET['rid'] : '';
    $fileinfo = C::t('resources')->get_property_by_rid($rid,false);
    if($fileinfo['error']) showmessage($fileinfo['error']);
    if(!$fileinfo['editperm']) showmessage(lang('no_privilege'));
    $tags = C::t('resources_tag')->fetch_tag_by_rid($rid);
    if (isset($_GET['addtag']) && $_GET['addtag']) {
        $tags = isset($_GET['tags']) ? $_GET['tags'] : '';
        $tagsarr = array_filter(explode(',', $tags));
        $tagsubmit = array();
        if (!empty($tagsarr)) {
            foreach ($tagsarr as $v) {
                $tagsubmit[] = getstr($v);
            }
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
            showmessage('add_unsuccess');
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
} elseif ($do == 'commentajax') {
    $fid = intval($_GET['fid']);
    $rid = trim($_GET['rid']);
    if ($rid) {
        $fileinfo = C::t('resources')->get_property_by_rid($rid,false);
    } elseif($fid) {
        $fileinfo = C::t('resources')->get_property_by_fid($fid,false);
    }
    if($fileinfo['error']) exit(json_encode(array('error' => $fileinfo['error'])));
    if (!perm_check::checkperm('comment', $fileinfo)) {
        exit(json_encode(array('error' => lang('file_comment_no_privilege'))));
    }
    include_once libfile('function/code');
    include_once libfile('function/use');
    $msg = isset($_GET['msg']) ? censor($_GET['msg']) : '';
    //获得提醒用户
    $at_users = array();
    $message = preg_replace_callback("/@\[(.+?):(.+?)\]/i", "atreplacement", $msg);
    $appid = C::t('app_market')->fetch_appid_by_mod('{dzzscript}?mod=' . MOD_NAME, 1);
    $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($fileinfo['fid'], $fileinfo['gid']);
    $eventdata = array('position' => $fileinfo['realpath'], 'hash' => $hash, 'msg' => $msg,'title' => $fileinfo['rid'] ? $fileinfo['name'] : '');
    if ($insert = C::t('resources_event')->addevent_by_pfid($fileinfo['fid'], 'add_comment', 'addcomment', $eventdata, $fileinfo['gid'], $fileinfo['rid'], $fileinfo['name'], 1)) {
        if ($fileinfo['uid'] != $_G['uid']) {
            $notevars = array(
                'from_id' => $appid,
                'from_idtype' => 'app',
                'url' => ($fileinfo['gid'] > 0) ? $_G['siteurl'] . MOD_URL . '#group&do=file&gid=' . $fileinfo['gid'] . '&fid=' . $fileinfo['fid'] : $_G['siteurl'] . MOD_URL . '#home&do=file&fid=' . $fileinfo['fid'],
                'author' => $_G['username'],
                'authorid' => $_G['uid'],
                'dataline' => dgmdate(TIMESTAMP),
                'fname' => getstr($fileinfo['name'], 31),
                'comment' => ($message) ? getstr(dzzcode($message)) : '',
            );
            $action = 'explorer_comment_mydoc';
            $type = 'explorer_comment_mydoc_' . $fileinfo['fid'];
            dzz_notification::notification_add($fileinfo['uid'], $type, $action, $notevars, 1, 'dzz/explorer');
        }
        if ($at_users) {//提醒相关人员
            foreach ($at_users as $uid) {
                if ($uid != $_G['uid']) {
                    //发送通知
                    $notevars = array(
                        'from_id' => $appid,
                        'from_idtype' => 'app',
                        'url' => ($fileinfo['gid'] > 0) ? $_G['siteurl'] . MOD_URL . '#group&do=file&gid=' . $fileinfo['gid'] . '&fid=' . $fileinfo['fid'] : $_G['siteurl'] . MOD_URL . '#home&do=file&fid=' . $fileinfo['fid'],
                        'author' => $_G['username'],
                        'authorid' => $_G['uid'],
                        'dataline' => dgmdate(TIMESTAMP),
                        'fname' => getstr($fileinfo['name'], 31),
                        'comment' => ($message) ? getstr($message) : '',

                    );
                    $action = 'explorer_comment_at';
                    $type = 'explorer_comment_at' . $fileinfo['fid'];
                    dzz_notification::notification_add($uid, $type, $action, $notevars, 0, MOD_PATH);
                }
            }
        }
        showTips(array('success' => true, 'json'));
    }
} elseif ($do == 'addsearchcat') {//增加类型筛选
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
        $arr = dhtmlspecialchars($_GET['arr']);
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

} elseif ($do == 'delsearchcat') {//删除筛选类型
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

} elseif ($do == 'share') {//分享
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
            if ($share['endtime']) $share['endtime'] = strtotime($share['endtime']);
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
                if ($share['endtime']) {
                    $share['endtime'] = dgmdate($share['endtime'], 'Y-m-d H:i');
                } else {
                    $share['endtime'] = '';
                }
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
                if ($share['endtime']) {
                    $share['endtime'] = dgmdate($share['endtime'], 'Y-m-d H:i');
                } else {
                    $share['endtime'] = '';
                }
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
                    foreach (DB::fetch_all("select * from %t where rid in(%n)", array('resources', $rids)) as $v) {
                        if (!perm_check::checkperm('share', $v)) {
                            $arr = array('error' => lang('file_share_no_privilege'));
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
} elseif ($do == 'property') {//属性
    $paths = isset($_GET['paths']) ? trim($_GET['paths']) : '';
    $bz = isset($_GET['bz']) ? trim($_GET['bz']) : '';
    $fid = isset($_GET['fid']) ? trim($_GET['fid']) : '';
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    $fileinfo = array();
    if ($bz) {
        if ($fid) {
            $fileinfo = IO::getMeta($fid);
            if ($fileinfo['error']) showmessage($fileinfo['error']);
            if (!$_G['adminid'] &&  $fileinfo['uid'] != $_G['uid']) {
                showmessage('no_privilege');
            }
            $contains = IO::getContains($fileinfo['path']);
            $fileinfo['ftype'] = lang('type_folder');
            $fileinfo['ffsize'] = lang('property_info_size', array('fsize' => formatsize($contains['size']), 'size' => $contains['size']));
            $fileinfo['contain'] = lang('property_info_contain', array('filenum' => $contains['contain'][0], 'foldernum' => $contains['contain'][1]));
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
            $fileinfo['ffsize'] = lang('property_info_size', array('fsize' => formatsize($size), 'size' => $size));
            $fileinfo['contain'] = lang('property_info_contain', array('filenum' => $contents[0], 'foldernum' => $contents[1]));
        } else {
            $paths = dzzdecode($paths);
            $fileinfo = IO::getMeta($paths);
            if ($fileinfo['error']) showmessage($fileinfo['error']);
            if (!$_G['adminid'] &&  $fileinfo['uid'] != $_G['uid']) {
                showmessage('no_privilege');
            }
            if ($fileinfo['type'] == 'folder') {
                $contains = IO::getContains($fileinfo['path']);
                $fileinfo['ftype'] = lang('type_folder');
                $fileinfo['ffsize'] = lang('property_info_size', array('fsize' => formatsize($contains['size']), 'size' => $contains['size']));
                $fileinfo['contain'] = lang('property_info_contain', array('filenum' => $contains['contain'][0], 'foldernum' => $contains['contain'][1]));
            }
        }
    } else {
        if ($rid) {
            $fileinfo = C::t('resources')->get_property_by_rid($rid);
        } elseif ($fid) {
            $fileinfo = C::t('resources')->get_property_by_fid($fid);
        } else {
            $patharr = explode(',', $paths);
            $rids = array();
            foreach ($patharr as $v) {
                $rids[] = dzzdecode($v);
            }
            $fileinfo = C::t('resources')->get_property_by_rid($rids);
        }
        if ($fileinfo['error']) showmessage($fileinfo['error']);
        if ($fileinfo['isgroup']) {
            $org = C::t('organization')->fetch($fileinfo['gid']);
            if ($org) {
                //获取已使用空间
                $usesize = C::t('organization')->get_orgallotspace_by_orgid($fileinfo['gid'], 0, false);
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
        } elseif ($fileinfo['pfid'] == 0) {
            $spaceinfo = dzzgetspace($uid);
            $maxspace = $spaceinfo['maxspacesize'];
            $usesize = $spaceinfo['usesize'];
            $progress = set_space_progress($usesize, $maxspace);
        }
        if (!$fileinfo['ismulti'] && $fileinfo['rid']) {
            $tags = C::t('resources_tag')->fetch_tag_by_rid($fileinfo['rid']);
            $filemeta = C::t('resources_meta')->fetch_by_key($fileinfo['rid'],'desc', true);
            if($filemeta) $fileinfo['desc'] = htmlspecialchars($filemeta);
            $attrdata = C::t('resources_attr')->fetch_by_rid($fileinfo['rid'], $fileinfo['vid']);
            if ($_G['adminid'] && $attrdata['aid']) {
                $attachment = IO::getStream('attach::' . $attrdata['aid']);
            }
        }
    }
} elseif ($do == 'editFileVersionInfo') {
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    $vid = isset($_GET['vid']) ? intval($_GET['vid']) : 0;
    $versioninfo = C::t('resources_version')->get_versioninfo_by_rid_vid($rid, $vid);
} elseif ($do == 'infoversion') {
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    $vid = isset($_GET['vid']) ? intval($_GET['vid']) : 0;

    $versioninfo = C::t('resources_version')->get_versioninfo_by_rid_vid($rid, $vid);
    if ($versioninfo['rid']) {
        $fileinfo = C::t('resources')->get_property_by_rid($versioninfo['rid']);
        if ($fileinfo['error']) showmessage($fileinfo['error']);
    }
    if ($_G['adminid'] && $versioninfo['aid']) {
        $attachment = IO::getStream('attach::' . $versioninfo['aid']);
    }
} elseif ($do == 'deletethisversion') {
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    $vid = isset($_GET['vid']) ? intval($_GET['vid']) : 0;
    if (!$rid || !$vid) {
        exit(json_encode(array('error' => 'access denied')));
    }
    $fileinfo = C::t('resources')->get_property_by_rid($rid,false);
    if ($fileinfo['editperm']) {
        if (C::t('resources_version')->delete_by_vid($vid, $rid, true)) {
            exit(json_encode(array('msg' => 'success')));
        } else {
            exit(json_encode(array('error' => '该版本不存在或最后一个不能删除')));
        }
    } else {
        exit(json_encode(array('error' => lang('no_privilege'))));
    }

} elseif ($do == 'addIndex') {//索引文件
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
} elseif ($do == 'updateIndex') {
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
} elseif ($do == 'deleteIndex') {
    $rids = $_GET['rids'];
    $ids = array();
    foreach ($rids as $v) {
        $ids[] = $v . '_' . '0';
    }
    Hook::listen('solrdel', $ids);
    exit(json_encode(array('success' => true)));
} elseif ($do == 'setExtopenDefault') {
    $extid=$_GET['extid'];
    if(!$extid) exit(json_encode(array('error' => '缺少参数')));
	if($extdata=C::t('app_open')->fetch($extid)){
		C::t('app_open_default')->insert_default_by_uid($_G['uid'],$extid,$extdata['ext']);
	} else {
		exit(json_encode(array('error' => '参数错误')));
	}
    exit(json_encode(array('success' => true)));
} elseif ($do == 'version') {
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    $property = isset($_GET['property']) ? intval($_GET['property']) : 0;
    $fileinfo = C::t('resources')->get_property_by_rid($rid,false);
    if ($fileinfo['error']) showmessage($fileinfo['error']);
    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
    $vlimit = 20;
    $limit = ($page) ? $page . '-' . $vlimit : $vlimit;
    $gets = array('op' => 'ajax', 'do' => 'version', 'rid' => $rid,'page' => $page,'property' => $property);
    $theurl = MOD_URL . "&" . url_implode($gets);
    $total = C::t('resources_version')->fetch_all_by_rid($rid, '', true);
    if($total) {
        $multi = multi($total, $vlimit, $page, $theurl, 'pull-right');
        $versions = C::t('resources_version')->fetch_all_by_rid($rid, $limit, false);
    }
    if(!$property) {
        include template('historyversion_content');
        exit();
    }
} elseif ($do == 'dynamic') {
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    $fid = isset($_GET['fid']) ? intval($_GET['fid']) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
    $property = isset($_GET['property']) ? intval($_GET['property']) : 0;
    $limit = 20;
    if ($rid) {
        $fileinfo = C::t('resources')->get_property_by_rid($rid,false);
        if($fileinfo['error']) showmessage($fileinfo['error']);
        $total = C::t('resources_event')->fetch_by_rid($rid, $page, $limit, true, 1);
        if ($total) {
            $events = C::t('resources_event')->fetch_by_rid($rid, $page, $limit, false, 1);
        }
    } else {
        $fileinfo = C::t('resources')->get_property_by_fid($fid,false);
        if($fileinfo['error']) showmessage($fileinfo['error']);
        $total = C::t('resources_event')->fetch_by_pfid_rid($fid, true, $page, $limit, '', 1);
        if ($total) {
            $events = C::t('resources_event')->fetch_by_pfid_rid($fid, false, $page, $limit, '', 1);
        }
    }
    if ($total) {
        $gets = array('op' => 'ajax', 'do' => 'dynamic', 'rid' => $rid, 'fid' =>$fid,'page' => $page,'property' => $property);
        $theurl = MOD_URL . "&" . url_implode($gets);
        $multi = multi($total, $limit, $page, $theurl);
    } else {
        $info = lang('no_dynamisc');
    }
    if(!$property) {
        include template('template_more_dynamic');
        exit();
    }
} elseif ($do == 'comment') {
    $property = isset($_GET['property']) ? intval($_GET['property']) : 0;
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    $fid = isset($_GET['fid']) ? intval($_GET['fid']) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
    $limit = 20;
    if ($rid) {
        $fileinfo = C::t('resources')->get_property_by_rid($rid,false);
        if($fileinfo['error']) showmessage($fileinfo['error']);
        $total = C::t('resources_event')->fetch_by_rid($rid, $page, $limit, true,2);
        if ($total) {
            $events = C::t('resources_event')->fetch_by_rid($rid, $page, $limit,false, 2);
        }
    } else {
        $fileinfo = C::t('resources')->get_property_by_fid($fid,false);
        if($fileinfo['error']) showmessage($fileinfo['error']);
        $total = C::t('resources_event')->fetch_by_pfid_rid($fid, true, $page, $limit, '', 2);
        if ($total) {
            $events = C::t('resources_event')->fetch_by_pfid_rid($fid, false, $page, $limit, '', 2);
        }
    }
    if ($total) {
        $gets = array('op' => 'ajax', 'do' => 'comment', 'rid' => $rid, 'fid' =>$fid,'page' => $page,'property' => $property);
        $theurl = MOD_URL . "&" . url_implode($gets);
        $multi = multi($total, $limit, $page, $theurl);
    } else {
        $info = lang('no_cmment');
    }
    $commentperm = false;
    if (perm_check::checkperm('comment', $fileinfo)) {
        $commentperm = true;
    }
    $commentid = 2;
    if(!$property) {
        $commentid = 1;
        include template('template_more_dynamic');
        exit();
    }
} elseif ($do == 'perm') {
    $property = isset($_GET['property']) ? intval($_GET['property']) : 0;
    $rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
    $fid = isset($_GET['fid']) ? intval($_GET['fid']) : '';
    if ($rid) {
        $fileinfo = C::t('resources')->get_property_by_rid($rid,false);
    } else {
        $fileinfo = C::t('resources')->get_property_by_fid($fid,false);
    }
    if($fileinfo['error']) showmessage($fileinfo['error']);
    if($fileinfo['gid']) {
        if ($fileinfo['isgroup']) {
            $org = C::t('organization')->fetch($fileinfo['gid']);
            if($org) {
                $members = C::t('organization_user')->fetch_user_byorgid($fileinfo['gid'],'', false);
                //处理成员头像函数
                $userids = array();
                foreach ($members as $k => $v) {
                    $userids[] = $v['uid'];
                }
                $userstr = implode(',', $userids);
            }
        }
        $usergroupperm = perm_check::checkgroupPerm($fileinfo['gid'], 'admin');//判断管理员权限
    }
    $myperm = perm_check::getridPerm($fileinfo);
    //获取所有权限
    if ($fileinfo['isfolder']) {
        $perms = get_permsarray();
        $fperm = C::t('folder')->fetch_perm_by_fid($fileinfo['fid']);
    } else {
        $perms = get_permsarray('document');
        $fperm = $fileinfo['sperm'];
    }
    if(!$property) {
        include template('template_perm');
        exit();
    }
}
include template('ajax');