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
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
if ($operation == 'chkperm') {
    $fid = isset($_GET['path']) ? intval($_GET['path']) : 0;
    if (perm_check::checkperm_Container($fid, 'upload')) {
        exit(json_encode(array('success' => true)));
    } else {
        exit(json_encode(array('error' => lang('folder_upload_no_privilege'))));
    }
}