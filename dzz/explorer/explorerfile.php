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
$do = empty($_GET['do']) ? '' : $_GET['do'];
$uid = isset($_GET['uid']) ? intval($_GET['uid']) : $_G['uid'];

if ($do == 'filelist') {
    $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 100;//默认每页条数
    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);//页码数
    $start = ($page - 1) * $perpage;//开始条数
    $total = 0;//总条数
    $disp = isset($_GET['disp']) ? intval($_GET['disp']) : 3;
    $sid = empty($_GET['sid']) ? 0 : $_GET['sid'];//id
    $bz = empty($_GET['bz']) ? '' : urldecode($_GET['bz']);
    $path = rawurldecode($_GET['path']);
    if (!$path) $path = $bz;
    $marker = empty($_GET['marker']) ? '' : trim($_GET['marker']);
    $data = array();

    if ($_G['uid'] && $bz && $bz !== 'dzz') {//云盘查询
        $bzinfo=IO::getCloud($bz);
        if (!$bzinfo) {
            exit(json_encode(array('error' => lang('cloud_no_info'))));
        }
        if($bzinfo['available']<1) {
            exit(json_encode(array('error' => lang('cloud_no_available'))));
        }
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
        $folder = IO::getMeta($path);
        if (!perm_check::checkperm('read', $folder)) {
            exit(json_encode(array('error' => lang('file_read_no_privilege'))));
        }
        if ($folder['error']) {
            exit(json_encode(array('error' => $folder['error'])));
        }
        $icosdata = IO::listFiles($path, $by, $order, $limit, $force);

        if ($icosdata['error']) {
            exit(json_encode(array('error' => $icosdata['error'])));
        }
        $folderdata = array();
        $ignore = 0;
        $folder['disp'] = $disp;
        $folder['iconview'] = $_GET['iconview'];
        $folderdata[$folder['rid']] = IO::getFolderByIcosdata($folder);
        foreach ($icosdata as $key => $value) {
            if ($value['error']) {
                $ignore++;
                continue;
            }
            if (strpos($bz, 'ftp') === false) {
                if (trim($value['path'], '/') == trim($path, '/')) {
                    $ignore++;
                    continue;
                }
            }
            $data[$key] = $value;
        }
    } else {
        list($prex, $id) = explode('-', $sid);
        if ($prex == 'f') {
            $arr = array();
            //查询当前文件夹信息
            if ($folder = C::t('folder')->fetch_by_fid($id)) {
                if ($folder['fid']) {
                    $folder['disp'] = $disp = intval($_GET['disp']) ? intval($_GET['disp']) : intval($folder['disp']);//文件排序
                    $folder['iconview'] = (isset($_GET['iconview']) ? intval($_GET['iconview']) : intval($folder['iconview']));//排列方式
                    $keyword = isset($_GET['keyword']) ? urldecode($_GET['keyword']) : '';
                    $conditions = array();
                    if ($keyword) {
                        $conditions['name'] = array($keyword, 'like', 'and');
                    }
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
                    $data = C::t('resources')->fetch_all_by_pfid($folder['fid'], $conditions, $perpage, $orderby, $order, $start, false, false, true, true);//查询文件信息
                    $folderdata[$folder['fid']] = $folder;//文件夹信息
                }
            }
        }
    }
    $total = $data['total'] ?? 0;
    //返回数据
    $return = array(
        'sid' => $sid,
        'total' => $total,
        'data' => $data['data'] ?? array(),
        'folderdata' => $folderdata ?? array(),
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