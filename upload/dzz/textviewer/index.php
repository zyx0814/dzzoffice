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
$path = dzzdecode($_GET['path']);
$str = IO::getFileContent($path);

require_once DZZ_ROOT . './dzz/class/class_encode.php';
$p = new Encode_Core();
$code = $p -> get_encoding($str);
if ($code)$str = diconv($str, $code, CHARSET);
$str = htmlspecialchars($str);
$str = nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $str));
include  template('textviewer');
?>
