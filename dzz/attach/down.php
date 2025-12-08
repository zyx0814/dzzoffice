<?php
/* @authorcode  codestrings
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
if (!$path = dzzdecode($_GET['path'])) {
    topshowmessage(lang('parameter_error'));
}
$filename = $_GET['filename'] ?? '';
IO::download($path, $filename);
exit();
?>