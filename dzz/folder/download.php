<?php
/* //分享地址支持下载（a=down)，预览(a=view)和流
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

@set_time_limit(0);
include_once  libfile('class/ZipStream');

$patharr = $_GET['paths'];
//print_r($_GET);
exit('dfdsfsf');
$meta = IO::getMeta(dzzdecode($patharr[0]));
if ($meta['error'])exit($meta['error']);
$filename = (strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($meta['name']) : $meta['name']);
$zip = new ZipStream($filename . ".zip");
foreach ($patharr as $dpath) {
	$path = dzzdecode($dpath);
	$meta = IO::getMeta($path);
	switch($meta['type']) {
		case 'app' :
		case 'video' :
		case 'dzzdoc' :
		case 'link' :
			continue;
			break;
		case 'folder' :
			IO::getFolderInfo($path, $meta['name'], $zip);
			break;

		default :
			$zip -> addLargeFile(fopen(IO::getStream($path), 'rb'), $meta['name'], $meta['dateline']);
			break;
	}
}
$zip -> finalize();
?>
