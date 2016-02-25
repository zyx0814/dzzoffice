<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

$path=dzzdecode(urldecode($_GET['path']));
$width=intval($_GET['width']);
$height=intval($_GET['height']);
$original=intval($_GET['original']);
IO::getThumb($path,$width,$height,$original);
?>
