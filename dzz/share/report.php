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
$report = C::t('share_report');
$reporttypes = $report->get_report_types();
if ($do == 'getinfo') {
    $field = in_array($_GET['field'], ['title', 'type', 'status', 'dateline']) ? trim($_GET['field']) : 'dateline';
    $order = in_array($_GET['order'], ['asc', 'desc']) ? trim($_GET['order']) : 'DESC';
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $type = isset($_GET['type']) ? trim($_GET['type']) : '';
    $status = isset($_GET['status']) ? intval($_GET['status']) : null;
    $page = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
    $limit = empty($_GET['limit']) ? 20 : $_GET['limit'];
    $start = ($page - 1) * $limit;
    if ($field =='status') {
        $field = 's.status';
    }
    $orderby = " order by $field " . $order;
    $sql = "1";
    $param = ['share_report'];
    if ($type) {
        $sql .= " and type=%s";
        $param[] = $type;
    }
    if ($keyword) {
        $sql .= " and username LIKE %s";
        $param[] = '%' . $keyword . '%';
    }
    $data = [];
    if ($count = DB::result_first("SELECT COUNT(*) FROM %t WHERE $sql", $param)) {
        $list = DB::fetch_all("SELECT sr.*, s.title, s.status FROM %t sr LEFT JOIN " . DB::table('shares') . " s ON sr.sid = s.id WHERE $sql $orderby LIMIT $start,$limit", $param);
        $sharestatus = [
            '0' => '<span class="layui-badge layui-bg-green">分享正常</span>',
            '1' => '<span class="layui-badge layui-bg-orange">已不能访问</span>'
        ];
        foreach ($list as $value) {
            if ($value['status'] == 0) {
                $status = 0;
            } else {
                $status = 1;
            }
            $data[] = [
                "id" => $value['id'],
                "sid" => $value['sid'],
                "username" => '<a href="user.php?uid=' . $value['uid'] . '" target="_blank">' . $value['username'] . '</a>',
                "title" => $value['title'],
                "status" => $sharestatus[$status],
                "type" => $reporttypes[$value['type']] . ($value['desc'] ? '：' . $value['desc'] : ''),
                "dateline" => $value['dateline'] ? dgmdate($value['dateline'],'Y-m-d H:i:s') : '',
                "desc" => $value['desc'],
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
    include template('report');
}

