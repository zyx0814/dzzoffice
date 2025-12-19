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
$do = empty($_GET['do']) ? '' : trim($_GET['do']);
$uid = $_G['uid'];
$refer = dreferer();
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if ($do == 'addopenrecord') {//增加打开记录
    $rid = $_GET['rid'];
    $setarr = array(
        'opendateline' => TIMESTAMP,
        'views' => 1,
        'uid' => $uid
    );
    if (C::t('resources_statis')->add_statis_by_rid($rid, $setarr)) {
        exit(json_encode(array('mgs' => 'success')));
    } else {
        exit(json_encode(array('mgs' => 'error')));
    }
}