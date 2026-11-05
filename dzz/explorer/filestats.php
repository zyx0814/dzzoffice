<?php
/* @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 * @author      小胡(3203164629@qq.com)
 * @desc        网盘文件统计接口
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
if ($_G['adminid'] != 1) {
    showmessage('no_privilege', dreferer());
}
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if ($do == 'filelist') {
    $limit = empty($_GET['limit']) ? 50 : intval($_GET['limit']);
    $page = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
    $start = ($page - 1) * $limit;
    $limitsql = "limit $start,$limit";
    $params = ['resources', 'folder'];
    $wheresql = " where `type` != %s ";
    $list = [];
    $totalsize = $filenum = 0;
    $count = count(DB::fetch_all("SELECT ext, `type` FROM %t $wheresql GROUP BY ext, `type`", $params));
    if ($count) {
        //全部文件数量、全部总大小
        $allsum = DB::fetch_first("SELECT COUNT(rid) AS filenum, SUM(size) AS totalsize FROM %t $wheresql", $params);
        if ($allsum) {
            $filenum = $allsum['filenum'];
            $totalsize = $allsum['totalsize'];
        }
        foreach(DB::fetch_all("SELECT ext, `type`, COUNT(rid) AS extnum, SUM(size) AS size FROM %t $wheresql GROUP BY ext, `type` order by extnum desc $limitsql", $params) as $v){
            $v['ext'] = empty($v['ext']) ? $v['type'] : $v['ext'];
            $list[] = $v;
        }
    }
    
    header('Content-Type: application/json');
    $return = [
        "code" => 0,
        "msg" => "",
        "count" => $count ?: 0,
        "data" => $list ?: [],
        "totalRow" => [
            "filenum" => $filenum,
            "extnum" => $count,
            "totalsize" => formatsize($totalsize),
        ]
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
    require template('filestats');
}