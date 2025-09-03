<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/8/29
 * Time: 10:43
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
$sid = $_GET['sid'] ? $_GET['sid'] : '';
if (!$sid) {
    exit(json_encode(array('error' => 'Access Denied')));
}
$sid = dzzdecode($sid);
if (!$sid) {
    exit(json_encode(array('error' => 'Access Denied')));
}
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
if ($share['password'] && (dzzdecode($share['password']) != authcode($_G['cookie']['pass_' . $sid]))) {
    $avatar = avatar_block($share['uid']);
    $return = array(
        'password' => array(
            'avatar' => $avatar,
            'username' => $share['username'],
        )
    );
    if ($_GET['passwordsubmit']) {
        if ($_GET['password'] != dzzdecode($share['password'])) {
            exit(json_encode(array('error' => '密码不正确，请重试')));
        }
        dsetcookie('pass_' . $sid, authcode($_GET['password'], 'ENCODE'));
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode($return));
    }
}
$canview = 1;
$download = 1;
$create = 0;
if ($share['perm']) {
    $perms = array_flip(explode(',', $share['perm'])); // 将权限字符串转换为数组
    if (isset($perms[3]) && !$_G['uid']) { // 3 表示仅登录访问
        exit(json_encode(array('error' => 'no_login')));
    } elseif (isset($perms[2])) { // 2 表示禁用预览权限
        $canview = 0;
    }
    if (isset($perms[5])) {
        $create = 1;
    }
    if (isset($perms[1])) {
        $download = 0; // 下载权限被禁用
    }
}
$fdateline = dgmdate($share['dateline'], 'Y-m-d');
$expireday = getexpiretext($share['endtime']);
$perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 100;//默认每页条数
$page = empty($_GET['page']) ? 1 : intval($_GET['page']);//页码数
$start = ($page - 1) * $perpage;//开始条数
$total = 0;//总条数
$disp = isset($_GET['disp']) ? intval($_GET['disp']) : 3;
$fid = empty($_GET['fid']) ? 0 : $_GET['fid'];
$view = isset($_GET['view']) ? intval($_GET['view']) : 2;
$asc = isset($_GET['asc']) ? intval($_GET['asc']) : 1;
$order = $asc > 0 ? 'ASC' : "DESC";
switch ($disp) {
    case 0:
        $orderby = 'name';
        break;
    case 1:
        $orderby = 'size';
        break;
    case 2:
        $orderby = array('type', 'ext');
        break;
    case 3:
        $orderby = 'dateline';
        break;
}
$data = array();
$fids = array();
$filepaths = $share['filepath'];
$rids = explode(',', $filepaths);
if ($do == 'filelist') {
    if (!isset($fid)) {
        exit(json_encode(array('error' => 'fid 参数错误')));
    }
    $bz = empty($_GET['bz']) ? '' : trim($_GET['bz']);
    if ($bz) {
        if (!$bz = dzzdecode($bz)) {
            exit(json_encode(array('error' => 'bz 参数错误')));
        }
        if (preg_match('/^sid:([^\_]+)_/', $bz)) {
            $dbz = preg_replace('/^sid:[^\_]+_/', '', $bz);
        }
        if (strpos($dbz, $filepaths) === 0) {
            $folder = IO::getMeta($bz);
            if ($folder['error']) {
                exit(json_encode(array('error' => $folder['error'])));
            }
            $bzinfo=IO::getCloud($folder['bz']);
            if (!$bzinfo) {
                exit(json_encode(array('error' => lang('cloud_no_info'))));
            }
            if($bzinfo['available']<1) {
                exit(json_encode(array('error' => lang('cloud_no_available'))));
            }
            if ($folder['type'] == 'folder') {
                $limit = $start . '-' . ($start + $perpage);
                $bzid = explode(':', $filepaths);
                if (strpos($bzid[0], 'ALIOSS') === 0 || strpos($bzid[0], 'JSS') === 0 || strpos($bzid[0], 'qiniu') === 0) {
                    $order = $marker;
                    $limit = $perpage;
                } elseif (strpos($bzid[0], 'OneDrive') === 0) {
                    $limit = $perpage;
                    $force = $marker;
                }
                $icosdata = IO::listFiles($folder['path'], $orderby, $order, $limit,$force);
                if ($icosdata['error']) {
                    exit(json_encode(array('error' => $icosdata['error'])));
                }
                foreach ($icosdata as $key => $value) {
                    if ($value['error']) {
                        $ignore++;
                        continue;
                    }
                    $value['dpath'] = dzzencode('sid:' . $sid . '_' . $value['path']);
                    if (strpos($bzid[0], 'ftp') === false) {
                        if (trim($value['path'], '/') == trim($path, '/')) {
                            $ignore++;
                            continue;
                        }
                    }
                    $data[$key] = $value;
                }
            } else {
                $folder['dpath'] = dzzencode('sid:' . $sid . '_' . $folder['path']);
                $data[$folder['rid']] = $folder;
            }
            $foldername = $folder['name'];
            $folder['fid'] = $folder['rid'];
        } else {
            exit(json_encode(array('error' => '该目录不在该分享范围内')));
        }
    } else {
        list($prex, $id) = explode('-', $fid);
        if ($prex == 'f') {
            // 查询文件夹信息
            $folder = C::t('folder')->fetch_by_fid($id);
            if (!$folder) {
                exit(json_encode(array('error' => '文件夹不存在')));
            }
            if (!$folder['fid']) {
                exit(json_encode(array('error' => '文件夹不存在')));
            }
            $fiddata = C::t('resources_path')->fetch_folder_containfid_by_pfid($share['pfid']);
            if (!empty($fiddata)) {
                // 排除第一个元素
                array_shift($fiddata);
            }
            if (in_array($id, $fiddata)) {
                $path = C::t('resources_path')->fetch_pathby_pfid($id, true);
                if (!$path['path']) {
                    exit(json_encode(array('error' => '文件夹不存在')));
                }
                $foldername = $folder['title'];
                $folder['disp'] = $disp = intval($_GET['disp']) ? intval($_GET['disp']) : intval($folder['disp']); // 文件排序
                // 查询文件信息
                $conditions = array();
                $data = C::t('resources')->fetch_all_by_pfid($folder['fid'], $conditions, $perpage, $orderby, $order, $start, false, $sid, true);
            } else {
                exit(json_encode(array('error' => '该目录不在该分享范围内')));
            }
        } else {
            exit(json_encode(array('error' => 'fid参数错误')));
        }
    }
} else {
    $foldername = '全部文件';
    if($share['pfid'] == -1) {
        $ignore = 0;
        $folder = IO::getMeta('sid:' . $sid . '_' .$filepaths,0,$sid);
        if ($folder['error']) {
            if ($folder['delete']) {
                DB::update('shares', array('status' => '-3'), array('id' => $sid));
                exit(json_encode(array('error' => lang('share_file_deleted'))));
            }
            exit(json_encode(array('error' => $folder['error'])));
        }
        $bzinfo=IO::getCloud($folder['bz']);
        if (!$bzinfo) {
            exit(json_encode(array('error' => lang('cloud_no_info'))));
        }
        if($bzinfo['available']<1) {
            exit(json_encode(array('error' => lang('cloud_no_available'))));
        }
        if ($folder['type'] == 'folder') {
            $limit = $start . '-' . ($start + $perpage);
            $bzid = explode(':', $folder['bz']);
            if (strpos($bzid[0], 'ALIOSS') === 0 || strpos($bzid[0], 'JSS') === 0 || strpos($bzid[0], 'qiniu') === 0) {
                $order = $marker;
                $limit = $perpage;
            } elseif (strpos($bzid[0], 'OneDrive') === 0) {
                $limit = $perpage;
                $force = $marker;
            }
            $icosdata = IO::listFiles('sid:' . $sid . '_' .$filepaths, $orderby, $order, $limit,$force);
            if ($icosdata['error']) {
                exit(json_encode(array('error' => $icosdata['error'])));
            }
            foreach ($icosdata as $key => $value) {
                if ($value['error']) {
                    $ignore++;
                    continue;
                }
                if (strpos($bzid[0], 'ftp') === false) {
                    if (trim($value['path'], '/') == trim($path, '/')) {
                        $ignore++;
                        continue;
                    }
                }
                $data[$key] = $value;
            }
        } else {
            $data[$folder['rid']] = $folder;
        }
    } else {
        $ordersql = '';
        if (is_array($orderby)) {
            foreach ($orderby as $key => $value) {
                $orderby[$key] = $value . ' ' . $order;
            }
            $ordersql = ' ORDER BY ' . implode(',', $orderby);
        } elseif ($orderby) {
            $ordersql = ' ORDER BY ' . $orderby . ' ' . $order;
        }
        if (!empty($rids)) {
            $params = array('resources', $rids);
            $wheresql = " where rid in(%n) and isdelete < 1";
            $limitsql = 'limit ' . $start . ',' . ($start + $perpage);
            $count = DB::result_first("select count(*) from %t $wheresql $ordersql $limitsql", $params);
            //获取分享数据
            foreach (DB::fetch_all("select rid from %t $wheresql $ordersql $limitsql", $params) as $v) {
                $fileinfo = getfileinfo($v['rid'], $sid);
                if (!$fileinfo) {
                    continue;
                }
                if ($fileinfo['rid']) {
                    $fileinfo['dpath'] = dzzencode('sid:' . $sid . '_' . $fileinfo['rid']);
                }
                if ($fileinfo['attachment']) {
                    unset($fileinfo['attachment']);
                }
                if (isset($fileinfo['relativepath'])) {
                    unset($fileinfo['relativepath']);
                }
                if (isset($fileinfo['relpath'])) {
                    unset($fileinfo['relpath']);
                }
                if (isset($fileinfo['realpath'])) {
                    unset($fileinfo['realpath']);
                }
                if (isset($fileinfo['position'])) {
                    unset($fileinfo['position']);
                }
                $data[$fileinfo['path']] = $fileinfo;
            }
            if (count($data) < 1) {
                DB::update('shares', array('status' => '-3'), array('id' => $sid));
                exit(json_encode(array('error' => lang('share_file_deleted'))));
            }
        }
    }
    if($share['pfid'] == -1) {
        $share['img'] = $_G['siteurl'] . DZZSCRIPT . '?mod=io&op=thumbnail&size=small&path=' . dzzencode($share['filepath']);
    } elseif (count($rids) > 1) {
        $share['img'] = '/dzz/explorer/images/ic-files.png';
    } else {
        $share['img'] = C::t('resources')->get_icosinfo_by_rid($share['filepath']);
    }
    C::t('shares')->add_views_by_id($sid);
}
if (count($data) >= $perpage) {
    $total = $start + $perpage * 2 - 1;
} else {
    $total = $start + count($data);
}
if (!$json_data = json_encode($data)) $data = array();
if ($page > 1) {
    $foldername = '';
}
//返回数据
$return = array(
    'fid' => 'f-1',
    'total' => $total,
    'data' => $data ? $data : array(),
    'foldername' => $foldername ? $foldername : '',
    'folderid' => $folder['fid'] ? $folder['fid'] : '0',
    'folderdata' => array(
        1 => array(
            'fid' => $folder['fid'] ? $folder['fid'] : '0',
            'pfid' => '1',
            'path' => $folder['path'],
            'gid' => '1',
            'bz' => $folder['bz'],
        )
    ),
    'share' => array(
        'fdateline' => $fdateline,
        'expireday' => $expireday,
        'title' => $share['title'],
        'username' => $share['username'],
        'views' => $share['views'] ? $share['views'] : '1',
        'downs' => $share['downs'],
        'img' => $share['img'],
    ),
    'param' => array(
        'disp' => $disp,
        'download' => $download,
        'create' => $do ? $create : 0,
        'open' => $canview,
        'view' => $view,
        'page' => $page,
        'perpage' => $perpage,
        'total' => $total,
        'asc' => $asc,
    )
);
exit(json_encode($return));