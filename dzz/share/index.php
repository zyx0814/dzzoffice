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
Hook::listen('adminlogin');
$navtitle = $global_appinfo['appname'] ?: lang('appname');
$op = isset($_GET['op']) ? trim($_GET['op']) : '';
$do = isset($_GET['do']) ? $_GET['do'] : '';
$typeinfo = [
    'folder' => lang('catalogue'),
    'image' => lang('photo'),
    'document' => lang('type_attach'),
    'link' => lang('type_link'),
    'video' => lang('video'),
    'dzzdoc' => 'DZZ' . lang('type_attach'),
    'attach' => lang('attachment'),
    'url' => lang('other')
];
if ($do == 'getinfo') {
    $field = in_array($_GET['field'], ['title', 'dateline', 'type', 'count','downs', 'username','endtime','times']) ? trim($_GET['field']) : 'dateline';
    $order = in_array($_GET['order'], ['asc', 'desc']) ? trim($_GET['order']) : 'DESC';
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $type = isset($_GET['type']) ? trim($_GET['type']) : '';
    $page = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
    $limit = empty($_GET['limit']) ? 20 : $_GET['limit'];
    $start = ($page - 1) * $limit;
    $username = trim($_GET['username']);
    $uid = intval($_GET['uid']);
    $orderby = " order by $field " . $order;
    $sql = "1";
    $param = ['shares'];
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
    $data = [];
    if ($count = DB::result_first("SELECT COUNT(*) FROM %t WHERE $sql", $param)) {
        $list = DB::fetch_all("SELECT * FROM %t WHERE $sql $orderby limit $start,$limit", $param);
        $sharestatus = [
            '-5' => lang('sharefile_isdeleted_or_positionchange'),
            '-4' => '<span class="layui-badge">' . lang('been_blocked') . '</span>',
            '-3' => '<span class="layui-badge">' . lang('file_been_deleted') . '</span>',
            '-2' => '<span class="layui-badge layui-bg-gray">' . lang('degree_exhaust') . '</span>',
            '-1' => '<span class="layui-badge layui-bg-gray">' . lang('logs_invite_status_4') . '</span>',
            '0' => '<span class="layui-badge layui-bg-blue">' . lang('founder_upgrade_normal') . '</span>'
        ];
        foreach ($list as $value) {
            $sharelink = C::t('shorturl')->getShortUrl('index.php?mod=shares&sid=' . dzzencode($value['id']));
            $value['expireday'] = getexpiretext($value['endtime']);
            $rids = explode(',', $value['filepath']);
            if (count($rids) > 1) {
                $img = '/dzz/explorer/images/ic-files.png';
            } elseif ($value['type'] == 'folder') {
                $img = '/dzz/images/extimg/folder.png';
            }else {
                $img = $_G['siteurl'] . DZZSCRIPT . '?mod=io&op=thumbnail&size=small&path=' . dzzencode($value['filepath']);
            }
            $data[] = [
                "sid" => $value['id'],
                "username" => '<a href="user.php?uid=' . $value['uid'] . '" target="_blank">' . $value['username'] . '</a>',
                "name" => $value['title'],
                "title" => '<a href="' . $sharelink . '" target="_blank" class="text-break"><img class="w-32 pe-2" src="'.$img.'">' . $value['title'] . '</a>',
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
    }
    header('Content-Type: application/json');
    $return = [
        "code" => 0,
        "msg" => "",
        "count" => $count ?: 0,
        "data" => $data ?: []
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

