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

define('CURRENT_PATH', 'dzz/system');
$operation = empty($_GET['operation']) ? '' : $_GET['operation'];
$uid = isset($_GET['uid']) ? intval($_GET['uid']) : $_G['uid'];

if ($operation == 'filelist') {
    $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 100;//默认每页条数
    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);//页码数
    $start = ($page - 1) * $perpage;//开始条数
    $total = 0;//总条数
    $disp = intval($_GET['disp']);
    $sid = empty($_GET['sid']) ? 0 : $_GET['sid'];//id
    $bz = empty($_GET['bz']) ? '' : urldecode($_GET['bz']);
    $marker = empty($_GET['marker']) ? '' : trim($_GET['marker']);
    $data = array();
    $permfilter = isset($_GET['permfilter']) ? trim($_GET['permfilter']) : '';
    if ($bz) {//云盘查询
        $asc = intval($_GET['asc']);
        list($prex, $id) = explode('-', $sid);
        $disp = intval($_GET['disp']) ? intval($_GET['disp']) : 0;//文件排序
        $order = $asc > 0 ? 'asc' : "desc";
        switch ($_GET['disp']) {
            case 0:
                $by = 'name';
                break;
            case 1:
                $by = 'size';
                break;
            case 2 :
                $by = array('type', 'ext');
                break;
            case 3:
                $by = 'dateline';
                break;

        }
        $limit = $start . '-' . ($start + $perpage);
        if (strpos($bz, 'ALIOSS') === 0 || strpos($bz, 'JSS') === 0 || strpos($bz, 'qiniu') === 0) {
            $order = $marker;
            $limit = $perpage;
        } elseif (strpos($bz, 'OneDrive') === 0) {
            $limit = $perpage;
            $force = $marker;
        }
        $icosdata = IO::listFiles($path, $by, $order, $limit, $force);

        if ($icosdata['error']) {
            exit(json_encode($icosdata));
        }
        $folderdata = array();
        $ignore = 0;
        foreach ($icosdata as $key => $value) {
            if ($value['error']) {
                $ignore++;
                continue;
            }
            if ($value['type'] == 'folder') {
                $folder = IO::getFolderByIcosdata($value);
                $folderdata[$folder['fid']] = $folder;
            }
            if (strpos($bz, 'ftp') === false) {
                if (trim($value['path'], '/') == trim($path, '/')) {
                    $ignore++;
                    continue;
                }
            }

            $userdata[$value['uid']] = $value['username'];
            $data[$key] = $value;
        }
        $bz = ($bz);
    } else {
        list($prex, $id) = explode('-', $sid);
        if ($prex == 'f') {
            $arr = array();
            //查询当前文件夹信息
            if ($folder = C::t('folder')->fetch_by_fid($id)) {

                $folder['disp'] = $disp = intval($_GET['disp']) ? intval($_GET['disp']) : intval($folder['disp']);//文件排序

                $folder['iconview'] = (isset($_GET['iconview']) ? intval($_GET['iconview']) : intval($folder['iconview']));//排列方式
                $conditions = array();
                $keyword = isset($_GET['keyword']) ? urldecode($_GET['keyword']) : '';
                $exts = isset($_GET['exts']) ? trim($_GET['exts']) : '';
                if ($exts) {
                    $extsarr = explode(',', $exts);
                    $conditions['ext'] = array($extsarr, 'in', 'and');
                }

                if ($keyword) {
                    $conditions['name'] = array($keyword, 'like', 'and');
                }
                $conditions['mustdition'] = "or (flag = 'folder')";
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
                $folder['perm'] = perm_check::getPerm($folder['fid']);//获取文件权限
                foreach (C::t('resources')->fetch_all_by_pfid($folder['fid'], $conditions, $perpage, $orderby, $order, $start) as $v) {
                    if ($v['type'] != 'folder' && $permfilter && $v['gid']) {
                        if (filter_permdata($permfilter, $folder['perm'], $v, $uid)) {
                            continue;
                        }
                    }
                    if ($v['type'] == 'image') {
                        $v['img'] = DZZSCRIPT . '?mod=io&op=thumbnail&width=100&height=90&path=' . dzzencode('attach::' . $v['aid']);
                    }
                    $data[$v['rid']] = $v;
                }
                $folderdata[$folder['fid']] = $folder;//文件夹信息

            }
        }
    }
    if (count($data) >= $perpage) {
        $total = $start + $perpage * 2 - 1;
    } else {
        $total = $start + count($data);
    }
    //$total=$count;//总条数
    if (!$json_data = json_encode($data)) $data = array();
    if (!$json_data = json_encode($folderdata)) $folderdata = array();
    //返回数据
    $return = array(
        'sid' => $sid,
        'total' => $total,
        'data' => $data ? $data : array(),

        'folderdata' => $folderdata ? $folderdata : array(),
        'param' => array(
            'disp' => $folder['disp'],
            'view' => $folder['iconview'],
            'page' => $page,
            'perpage' => $perpage,
            'bz' => $bz,
            'total' => $total,
            'asc' => $asc,
            'keyword' => $keyword,
            'localsearch' => $bz ? 1 : 0
        )
    );
    exit(json_encode($return));
}
function filter_permdata($permfilter, $perm, $data, $uid){
    $powerarr = perm_binPerm::getPowerArr();
    $specialperm = array('read', 'edit', 'delete', 'download', 'copy');
    $noperm = false;
    if (!C::t('organization_admin')->chk_memberperm($data['gid'], $uid)) {
        $permfilterarr = explode(',', $permfilter);
        foreach ($permfilterarr as $val) {
            if ($val == 'write') $val = 'edit';
            if (in_array($val, $specialperm)) {
                if ($uid == $data['uid']) {
                    if (!($powerarr[$val . '1'] & $perm)) {
                        $noperm = true;
                        break;
                    }
                } else {
                    if (!($powerarr[$val . '2'] & $perm)) {
                        $noperm = true;
                        break;
                    }
                }
            } else {
                if (!($powerarr[$val] & $perm)) {
                    $noperm = true;
                    break;
                }
            }
        }
    }
    return $noperm;
}