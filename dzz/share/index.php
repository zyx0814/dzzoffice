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
$type = trim($_GET['type']);
$do = isset($_GET['do']) ? $_GET['do'] : '';
$page = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
$limit = empty($_GET['limit']) ? 20 : $_GET['limit'];
$start = ($page - 1) * $limit;
$typeinfo = array(
    'folder' => array('name' => lang('catalogue'), 'icon' => 'mdi-folder'),
    'image' => array('name' => lang('photo'), 'icon' => 'mdi-file-image'),
    'document' => array('name' => lang('type_attach'), 'icon' => 'mdi-file-document'),
    'link' => array('name' => lang('type_link'), 'icon' => 'mdi-web'),
    'video' => array('name' => lang('video'), 'icon' => 'mdi-video'),
    'dzzdoc' => array('name' => 'DZZ' . lang('type_attach'), 'icon' => 'mdi-file'),
    'attach' => array('name' => lang('attachment'), 'icon' => 'mdi-file-chart'),
    'url' => array('name' => lang('other'), 'icon' => 'mdi-share-all-outline')
);
if ($do == 'getinfo') {
    $field = in_array($_GET['field'], array('title', 'dateline', 'type', 'count','downs', 'username','endtime','times')) ? trim($_GET['field']) : 'dateline';
    $order = in_array($_GET['order'], array('asc', 'desc')) ? trim($_GET['order']) : 'DESC';
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $username = trim($_GET['username']);
    $uid = intval($_GET['uid']);
    $uid1 = $_G['uid'];
    $orderby = " order by $field " . $order;
    $sql = "1";
    $param = array('shares');
    if ($type) {
        $sql .= " and type=%s";
        $param[] = $type;
    }
    if ($keyword) {
        $sql .= " and title LIKE %s";
        $param[] = '%' . $keyword . '%';
    }
    if ($username) {
        $sql .= " and username=%s";
        $param[] = $username;
    }
    if ($uid) {
        $sql .= " and uid=%d";
        $param[] = $uid;
    }
    if ($_G['adminid']) {
        if ($count = DB::result_first("SELECT COUNT(*) FROM %t WHERE $sql", $param)) {
            $list = DB::fetch_all("SELECT * FROM %t WHERE $sql $orderby limit $start,$limit", $param);
        }
    } else {
        if ($count = DB::result_first("SELECT COUNT(*) FROM %t WHERE uid =$uid1 and $sql", $param)) {
            $list = DB::fetch_all("SELECT * FROM %t WHERE uid =$uid1 and $sql $orderby limit $start,$limit", $param);
        }
    }
    $sharestatus = array('-5' => lang('sharefile_isdeleted_or_positionchange'), '-4' => '<span class="layui-badge">' . lang('been_blocked') . '</span>', '-3' => '<span class="layui-badge">' . lang('file_been_deleted') . '</span>', '-2' => '<span class="layui-badge layui-bg-gray">' . lang('degree_exhaust') . '</span>', '-1' => '<span class="layui-badge layui-bg-gray">' . lang('logs_invite_status_4') . '</span>', '0' => '<span class="layui-badge layui-bg-blue">' . lang('founder_upgrade_normal') . '</span>');
    $data = array();
    foreach ($list as $value) {
        $sharelink = C::t('shorturl')->getShortUrl('index.php?mod=shares&sid=' . dzzencode($value['id']));
        $value['expireday'] = getexpiretext($value['endtime']);
        $rids = explode(',', $value['filepath']);
        if($value['pfid'] == -1) {
            $img = $_G['siteurl'] . DZZSCRIPT . '?mod=io&op=thumbnail&size=small&path=' . dzzencode($value['filepath']);
        } elseif (count($rids) > 1) {
            $img = '/dzz/explorer/images/ic-files.png';
        } else {
            $img = C::t('resources')->get_icosinfo_by_rid($value['filepath']);
        }
        $data[] = [
            "sid" => $value['id'],
            "username" => '<a href="user.php?uid=' . $value['uid'] . '" target="_blank">' . $value['username'] . '</a>',
            "name" => $value['title'],
            "title" => '<a href="' . $sharelink . '" target="_blank"><img class="w-32 pe-2" src="'.$img.'" title="'.$value['title'].'">' . $value['title'] . '</a>',
            "status" => $sharestatus[$value['status']],
            "type" => getFileTypeName($value['type'], $value['ext']),
            "endtime" => $value['expireday']?? '',
            "password" => $value['password'] ? dzzdecode($value['password']) : lang('open_links'),
            "dateline" => $value['dateline'] ? dgmdate($value['dateline'],'Y-m-d H:i:s') : '',
            "count" => $value['count']?? 0,
            "downs" => $value['downs']?? 0,
            "sharelink" => $sharelink,
            "times" => $value['times'] ? $value['count'] . '/' . $value['times'] : lang('no_limit'),
        ];
    }
    $typearrname = $typeinfo[$type]['name'] ?? lang('all');
    $typeicon = $typeinfo[$type]['icon'] ?? 'mdi-share-variant-outline';
    $title = '<i class="mdi '.$typeicon.' pe-2"></i>' . $typearrname;
    header('Content-Type: application/json');
    $return = [
        "code" => 0,
        "msg" => "",
        "count" => $count ? $count : 0,
        "data" => $data ? $data : [],
        "title" => $title,
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
    include template('share');
}
?>
