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
$navtitle = $global_appinfo['appname'] ? $global_appinfo['appname'] : lang('appname');
Hook::listen('adminlogin');
$uid = $_G['uid'];
$do = isset($_GET['do']) ? $_GET['do'] : '';
$orgid = isset($_GET['orgid']) ? intval($_GET['orgid']) : '';
$typeinfo = array(
    'recycle' => array('name' => lang('recycle'), 'icon' => 'mdi-delete'),
    'image' => array('name' => lang('photo'), 'icon' => 'mdi-file-image'),
    'document' => array('name' => lang('type_attach'), 'icon' => 'mdi-file-document'),
    'link' => array('name' => lang('type_link'), 'icon' => 'mdi-web'),
    'video' => array('name' => lang('video'), 'icon' => 'mdi-video'),
    'folder' => array('name' => lang('folder'), 'icon' => 'mdi-folder'),
    'dzzdoc' => array('name' => 'DZZ' . lang('type_attach'), 'icon' => 'mdi-file'),
    'attach' => array('name' => lang('rest_attachment'), 'icon' => 'mdi-file-chart')
);
if ($do == 'delete') {
    $icoid = isset($_GET['icoid']) ? trim($_GET['icoid']) : '';
    if (empty($icoid)) {
        exit(json_encode(['msg' => 'access denied']));
    }
    $icoids = explode(',', $icoid);
    $sucessicoids = [];
    $failedicoids = [];

    foreach ($icoids as $icoid) {
        try {
            $return = IO::Delete($icoid, true);
            if (!$return['error']) {
                $sucessicoids[$return['rid']] = [
                    'msg' => 'success',
                    'name' => $return['name']
                ];
                $dels[] = $icoid . '_0';
            } else {
                $failedicoids[$icoid] = $return['error'];
            }
        } catch (Exception $e) {
            $failedicoids[$icoid] = 'An unexpected error occurred: ' . $e->getMessage();
        }
    }
    // 执行成功的条目数检查
    if (!empty($dels)) {
        Hook::listen('solrdel', $dels);
    }

    $response = [
        'msg' => !empty($failedicoids) ? '部分文件删除失败' : 'success',
        'success' => $sucessicoids,
        'failed' => $failedicoids
    ];
    exit(json_encode($response));
} elseif ($do == 'getinfo') {
    $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
    $type = isset($_GET['type']) ? trim($_GET['type']) : '';
    $pfid = isset($_GET['pfid']) ? intval($_GET['pfid']) : '';
    $field = isset($_GET['field']) ? $_GET['field'] : 'dateline';
    $limit = empty($_GET['limit']) ? 20 : $_GET['limit'];
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $page = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
    $start = ($page - 1) * $limit;
    $validfields = ['name', 'size', 'type', 'username', 'dateline'];
    $validSortOrders = ['asc', 'desc'];
    if (in_array($field, $validfields) && in_array($order, $validSortOrders)) {
        $order = "ORDER BY $field $order";
    } else {
        $order = 'ORDER BY dateline DESC';
    }
    $sql = "type!='app' and type!='shortcut'";
    $foldername = array();
    $param = array();
    if ($keyword) {
        $sql .= ' and (name like %s OR username=%s)';
        $param[] = '%' . $keyword . '%';
        $param[] = $keyword;
    }
    if ($type) {
        if($type == 'recycle') {
            $pfid = -1;
        } else {
            $sql .= ' and type=%s';
            $param[] = $type;
        }
    }
    if ($pfid) {
        $sql .= ' and (pfid = %d)';
        $param[] = $pfid;
        $pathkey = DB::result_first("select pathkey from %t where fid = %d", array('resources_path', $pfid));
        $patharr = explode('-', str_replace('_', '', $pathkey));
        unset($patharr[0]);
        foreach (DB::fetch_all("select fname,fid from %t where fid in(%n)", array('folder', $patharr)) as $v) {
            $foldername[] = array('fid' => $v['fid'], 'fname' => $v['fname']);
        }
    } else {
        if ($orgid) {
            if ($org = C::t('organization')->fetch($orgid)) {
                $fids = array($org['fid']);
                foreach (DB::fetch_all("select fid from %t where pfid=%d", array('folder', $org['fid'])) as $value) {
                    $fids[] = $value['fid'];
                }
                $sql .= ' and  pfid IN(%n)';
                $param[] = $fids;
            }
        }
    }
    $limitsql = 'limit ' . $start . ',' . $limit;
    $list = array();
    $count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('resources') . " WHERE $sql", $param);
    if ($count) {
        $data = DB::fetch_all("SELECT rid FROM " . DB::table('resources') . " WHERE $sql $order $limitsql", $param);
        foreach ($data as $value) {
            if (!$data = C::t('resources')->fetch_by_rid($value['rid'])) {
                continue;
            }
            //文件统计信息
            $filestatis = C::t('resources_statis')->fetch_by_rid($value['rid']);
            if ($data['relpath'] == '/') {
                $data['relpath'] = '回收站';
            }
            if ($data['isdelete']) {
                $isdelete = '是';
            } else {
                $isdelete = '否';
            }
            $copys = $data['copys'];
            if ($data['attachment']) {
                if ($data['rbz']) {
                    $FileUri = IO::getStream($data['rbz'] . '/' . $data['attachment']);
                    if(is_array($FileUri) && $FileUri['error']) {
                        $FileUri = '<span class="text-danger">'.$FileUri['error'].'</span>';
                    } else {
                        $FileUri = '<a href="'.$FileUri.'" target="_blank">'.$FileUri.'</a>';
                    }
                } else {
                    $FileUri = $_G['setting']['attachdir'] . $data['attachment'];
                    $FileUri = '<a href="'.$FileUri.'" target="_blank">'.$FileUri.'</a>';
                }
            } else {
                $FileUri = '';
            }
            $list[] = [
                "username" => '<a href="user.php?uid=' . $data['uid'] . '" target="_blank">' . $data['username'] . '</a>',
                "rid" => $data['rid'],
                "name" => '<img class="icon" src="' . $data['img'] . '">' . $data['name'],
                "dpath" => $data['dpath'],
                "size" => $data['fsize'],
                "type" => $data['ftype'],
                "ftype" => $data['type'],
                "oid" => $data['oid'],
                "md5" => $data['md5'],
                "relpath" => $data['relpath'],
                "dateline" => $data['fdateline'],
                "isdelete" => $isdelete,
                "copys" => $copys,
                "FileUri" => $FileUri,
                "downs" => $filestatis['downs'],
                "views" => $filestatis['views'],
                "edits" => $filestatis['edits']
            ];
        }
    }
    
    $typearrname = $typeinfo[$type]['name'] ?? lang('all_typename_attach');
    $typeicon = $typeinfo[$type]['icon'] ?? 'mdi-file-document-outline';
    $breadcrumb = '<li class="breadcrumb-item"><a href="javascript:;" class="fid-btn" data-fid=""><i class="mdi '.$typeicon.' pe-2"></i>' . $typearrname . '</a></li>';
    if (!empty($foldername)) {
        $i = 0;
        foreach ($foldername as $v) {
            $i++;
            if ($i == count($foldername)) {
                $breadcrumb .= '<li class="breadcrumb-item active" aria-current="page">' . $v['fname'] . '</li>';
            } else {
                $breadcrumb .= '<li class="breadcrumb-item"><a href="javascript:;" class="fid-btn" data-fid="' . $v['fid'] . '">' . $v['fname'] . '</a></li>';
            }
        }
    }
    header('Content-Type: application/json');
    $return = [
        "code" => 0,
        "msg" => "",
        "count" => $count ? $count : 0,
        "data" => $list ? $list : [],
        "breadcrumb" => $breadcrumb,
    ];
    $jsonReturn = json_encode($return);
    if ($jsonReturn === false) {
        $errorMessage = json_last_error_msg();
        $errorResponse = [
            "code" => 1,
            "msg" => "JSON 编码失败，请刷新重试: " . $errorMessage,
            "count" => 0,
            "data" => [],
        ];
        exit(json_encode($errorResponse));
    }
    exit($jsonReturn);
} else {
    if ($orgid && $org = C::t('organization')->fetch($orgid)) {
        $orgpath = C::t('organization')->getPathByOrgid($org['orgid'], false);
        $org['depart'] = implode('-', ($orgpath));
    } else {
        $org = array();
        $org['depart'] = lang('select_a_organization_or_department');
        $org['orgid'] = $orgid;
    }
    include template('list');
}
?>