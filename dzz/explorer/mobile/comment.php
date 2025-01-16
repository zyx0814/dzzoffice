<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;
$uid = $_G['uid'];
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
$fid = isset($_GET['fid']) ? intval($_GET['fid']) : 0;
$rid = isset($_GET['rid']) ? trim($_GET['rid']) : '';
function emoji_encode($str)
{
    if (!is_string($str)) return $str;
    if (!$str || $str == 'undefined') return '';
    $text = json_encode($str); //暴露出unicode
    $text = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i", function ($str) {
        return addslashes($str[0]);
    }, $text);
    return json_decode($text);
}

if ($operation == 'filelist') {
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    //文件信息或者动态请求
    $noselectnum = false;
    //动态数据请求
    $limit = 10;
    $start = ($page - 1) * $limit;
    $next = false;
    $nextstart = $start + $limit;
    if ($rid) {
        if (C::t('resources_event')->fetch_comment_by_rid($rid, true) >= $nextstart) {
            $next = $nextstart;
        }
        $events = C::t('resources_event')->fetch_comment_by_rid($rid, false, $start, $limit);
    } else if ($fid) {
        //动态信息
        if (C::t('resources_event')->fetch_comment_by_fid($fid, true) > $nextstart) {
            $next = $nextstart;
        }
        $events = C::t('resources_event')->fetch_comment_by_fid($fid, false, $start, $limit);
    }

    require template('mobile/commentlist');
    exit();
} elseif ($operation == 'addcomment') {
    $msg = isset($_GET['msg']) ? censor($_GET['msg']) : '';
    $msg = emoji_encode($msg);
    $appid = C::t('app_market')->fetch_appid_by_mod('{dzzscript}?mod=' . MOD_NAME, 2);
    if ($rid) {
        if (!$file = C::t('resources')->fetch_info_by_rid($rid)) {
            exit(json_encode(array('error' => true)));
        } else {
            $eventdata = array('msg' => $msg);
            if ($insert = C::t('resources_event')->addevent_by_pfid($file['pfid'], 'add_comment', 'addcomment', $eventdata, $file['gid'], $rid, $file['name'], 1)) {
                $headerColor = C::t('user_setting')->fetch_by_skey('headerColor');
                $return = array(
                    'username' => getglobal('username'),
                    'uid' => getglobal('uid'),
                    'dateline' => dgmdate(TIMESTAMP, 'H:i'),
                    'msg' => dzzcode($message),
                    'commentid' => $insert,
                    'avatarstatus' => getglobal('avatarstatus', 'member')
                );
                if (!$return['avatarstatus'] && $headerColor) {
                    $return['headerColor'] = $headerColor;
                    $return['userfirst'] = new_strsubstr(ucfirst($return['username']), 1, '');
                }
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

                showTips(array('success' => true, 'return' => $insert, 'json'));
            }

        }
    } else {
        if (!$folder = C::t('folder')->fetch($fid)) {
            exit(json_encode(array('error' => true)));
        } else {
            $rid = C::t('resources')->fetch_rid_by_fid($fid);
            $eventdata = array('msg' => $msg);
            if ($insert = C::t('resources_event')->addevent_by_pfid($fid, 'add_comment', 'addcomment', $eventdata, $folder['gid'], ($rid) ? $rid : '', $folder['fname'], 1)) {
                $headerColor = C::t('user_setting')->fetch_by_skey('headerColor');
                $return = array(
                    'username' => getglobal('username'),
                    'uid' => getglobal('uid'),
                    'dateline' => dgmdate(TIMESTAMP, 'H:i'),
                    'msg' => dzzcode($message),
                    'commentid' => $insert,
                    'avatarstatus' => getglobal('avatarstatus', 'member')
                );

                if (!$return['avatarstatus'] && $headerColor) {
                    $return['headerColor'] = $headerColor;
                    $return['userfirst'] = new_strsubstr(ucfirst($return['username']), 1, '');
                }
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
                showTips(array('success' => true, 'return' => $return, 'json'));
            }
        }
    }
} elseif ($operation == 'delcomment') {
    $id = $_GET['id'];
    $return = C::t('resources_event')->delete_comment_by_id($id);
    if ($return['error']) {
        exit(json_encode(array('error' => $return['error'])));
    } else {
        exit(json_encode(array('success' => true)));
    }
} elseif ($operation == 'commentadd') {
    require template('mobile/comment_edit');
} else {
    require template('mobile/comment');
}