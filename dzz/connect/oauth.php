<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

$bz = $_GET['bz'];
if (!$bz) {
    showmessage('Access Denied', dreferer());
}
IO::authorize($bz);
exit();
?>
