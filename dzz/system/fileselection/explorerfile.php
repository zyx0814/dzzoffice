<?php
/* @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
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
    $disp = intval($_GET['disp']);
    $sid = empty($_GET['sid']) ? 0 : $_GET['sid'];//id
    $bz = empty($_GET['bz']) ? '' : urldecode($_GET['bz']);
    $marker = empty($_GET['marker']) ? '' : trim($_GET['marker']);
    $data = [];
    $permfilter = isset($_GET['permfilter']) ? trim($_GET['permfilter']) : '';
    if ($bz) {//云盘查询
        $asc = intval($_GET['asc']);
        list($prex, $id) = explode('-', $sid);
        $disp = intval($_GET['disp']) ?: 0;//文件排序
        $order = $asc > 0 ? 'asc' : "desc";
        switch ($_GET['disp']) {
            case 0:
                $by = 'name';
                break;
            case 1:
                $by = 'size';
                break;
            case 2 :
                $by = ['type', 'ext'];
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
        $folderdata = [];
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
            $data['data'][$key] = $value;
        }
        if (count($data) >= $perpage) {
            $data['total'] = $start + $perpage * 2 - 1;
        } else {
            $data['total'] = $start + count($data);
        }
    } else {
        list($prex, $id) = explode('-', $sid);
        if ($prex == 'f') {
            $arr = [];
            //查询当前文件夹信息
            if ($folder = C::t('folder')->fetch_by_fid($id)) {
                if ($folder['fid']) {
                    $folder['disp'] = $disp = intval($_GET['disp']) ?: intval($folder['disp']);//文件排序
                    $folder['iconview'] = (isset($_GET['iconview']) ? intval($_GET['iconview']) : intval($folder['iconview']));//排列方式
                    $conditions = [];
                    $keyword = isset($_GET['keyword']) ? urldecode($_GET['keyword']) : '';
                    $exts = isset($_GET['exts']) ? trim($_GET['exts']) : '';
                    if ($exts) {
                        $extsarr = explode(',', $exts);
                        $conditions['ext'] = [$extsarr, 'in', 'and'];
                    }

                    if ($keyword) {
                        $conditions['name'] = [$keyword, 'like', 'and'];
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
                            $orderby = ['type', 'ext'];
                            break;
                        case 3:
                            $orderby = 'dateline';
                            break;

                    }
                    $data = C::t('resources')->fetch_all_by_pfid($folder['fid'], $conditions, $perpage, $orderby, $order, $start, false, false, true, true);
                    foreach ($data['data'] as $v) {
                        if ($v['type'] != 'folder' && $permfilter && $v['gid']) {
                            if (filter_permdata($permfilter, $folder['perm'], $v, $uid)) {
                                continue;
                            }
                        }
                        $data[$v['rid']] = $v;
                    }
                    $folderdata[$folder['fid']] = $folder;//文件夹信息
                }
            }
        }
    }
    $total = $data['total'] ?? 0;
    //返回数据
    $return = [
        'sid' => $sid,
        'total' => $total,
        'data' => $data['data'] ?? [],
        'folderdata' => $folderdata ?? [],
        'param' => [
            'disp' => $folder['disp'],
            'view' => $folder['iconview'],
            'page' => $page,
            'perpage' => $perpage,
            'bz' => $bz,
            'total' => $total,
            'asc' => $asc,
            'keyword' => $keyword,
            'localsearch' => $bz ? 1 : 0
        ]
    ];
    exit(json_encode($return));
}
function filter_permdata($permfilter, $perm, $data, $uid) {
    $powerarr = perm_binPerm::getPowerArr();
    $specialperm = ['read', 'edit', 'delete', 'download', 'copy'];
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
                } elseif (!($powerarr[$val . '2'] & $perm)) {
                    $noperm = true;
                    break;
                }
            } elseif (!($powerarr[$val] & $perm)) {
                $noperm = true;
                break;
            }
        }
    }
    return $noperm;
}