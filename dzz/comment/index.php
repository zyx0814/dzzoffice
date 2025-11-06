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
Hook::listen('check_login');
$uid = $_G['uid'];
include_once libfile('function/appperm');
$navtitle = $global_appinfo['appname'] ? $global_appinfo['appname'] : lang('appname');
$do = isset($_GET['do']) ? $_GET['do'] : '';
if ($do == 'getinfo') {
    $order = isset($_GET['order']) ? $_GET['order'] : 'desc';
    $type = isset($_GET['type']) ? trim($_GET['type']) : '';
    $field = isset($_GET['field']) ? $_GET['field'] : 'dateline';
    $limit = empty($_GET['limit']) ? 20 : $_GET['limit'];
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $page = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
    $start = ($page - 1) * $limit;
    $validfields = ['edittime', 'edituid', 'authorid', 'dateline', 'ip', 'xtllq', 'message'];
    $validSortOrders = ['asc', 'desc'];
    if (in_array($field, $validfields) && in_array($order, $validSortOrders)) {
        $order = "order by $field $order";
    } else {
        $order = 'order by dateline DESC';
    }
    $sql = "cid!='app'";
    $param = array();
    if ($keyword) {
        $sql .= 'and (message LIKE %s or author LIKE %s)';
        $param[] = '%' . $keyword . '%';
        $param[] = $keyword;
    }
    if ($type) {
        $sql .= ' and module=%s';
        $param[] = $type;
        if ($type) {
            $appidxu = C::t('app_market')->fetch_by_identifier($type);
            $navtitle = $appidxu['appname'] . ' - ' . $navtitle;
        }
    }
    $limitsql = 'limit ' . $start . ',' . $limit;
    if ($_G['adminid']) {
        $whereClause = $sql;
    } else {
        $whereClause = "authorid = $uid AND $sql";
    }
    $count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('comment') . " WHERE $whereClause", $param);
    if ($count) {
        $data = DB::fetch_all("SELECT * FROM " . DB::table('comment') . " WHERE $whereClause $order $limitsql", $param);
    }
    $list = array();
    foreach ($data as $value) {
        $user = getuserbyuid($value['edituid']);
        $list[] = [
            "authorid" => $value['author'],
            "ip" => $value['ip'],
            "xtllq" => $value['xtllq'],
            "dateline" => dgmdate($value['dateline'], 'Y-n-j H:i:s'),
            "cid" => $value['cid'],
            "edittime" => $value['edittime'] ? dgmdate($value['edittime'], 'Y-n-j H:i:s') : '',
            "edituid" => $user['username'],
            "message" => $value['message'],
            "fid" => $value['id'],
            "idtype" => $value['idtype']
        ];
    }
    header('Content-Type: application/json');
    $return = [
        "code" => 0,
        "msg" => "",
        "count" => $count ? $count : 0,
        "data" => $list ? $list : [],
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
} elseif ($do == 'delete') {
    $cid = isset($_GET['cid']) ? trim($_GET['cid']) : '';
    $cids = explode(',', $cid);
    foreach ($cids as $cid) {
        $data = C::t('comment')->fetch($cid);
        if ($_G['adminid'] != 1 && $_G['uid'] != $data['authorid']) {
            exit(json_encode(array('msg' => lang('no_privilege'))));
        }
        C::t('comment')->delete_by_cid($cid);
    }
    exit(json_encode(array('msg' => 'success')));
} else {
    //获取通知包含类型
    $searchappid = array();
    foreach (DB::fetch_all("select distinct(module) from %t where authorid = %d", array('comment', $_G['uid'])) as $v) {
        $searchappid[] = $v['module'];
    }
    $searchcats = array();
    if ($searchappid) {
        foreach (DB::fetch_all("select appname,identifier,appico from %t where identifier in(%n)", array('app_market', $searchappid)) as $v) {
            $searchcats[] = array('identifier' => $v['identifier'], 'appname' => $v['appname'], 'appico' => $_G['setting']['attachurl'] . $v['appico']);
        }
    }
    include template('list');
}
?>