<?php
/* @authorcode  codestrings
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
$path=trim($_GET['path']);
$path=dzzdecode($path);

$aid=intval(str_replace('attach::','',$path));
$name=trim($_GET['filename']);
if(!$attach=C::t('attachment')->fetch($aid)){
	topshowmessage(lang('attachment_not_exist'));
	if(!empty($_GET['filename'])) $attach['filename']=trim($_GET['filename']);
}
$filename = $_G['setting']['attachdir'].$attach['attachment'];

$filesize = $attach['remote']<2 ? filesize($filename) : $attach['filesize'];

$attachurl=getAttachUrl($attach,true);

$attach['filename'] = '"'.(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($attach['filename']) : $attach['filename']).'"';

	
	$db = DB::object();
	$db->close();
	$chunk = 10 * 1024 * 1024; 
	if(!$fp = @fopen($attachurl, 'rb')) {
		exit(lang('attachment_nonexistence'));
	}
	dheader('Date: '.gmdate('D, d M Y H:i:s', $attach['dateline']).' GMT');
	dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', $attach['dateline']).' GMT');
	dheader('Content-Encoding: none');
	dheader('Content-Disposition: attachment; filename='.$attach['filename']);
	dheader('Content-Type: application/octet-stream');
	dheader('Content-Length: '.$filesize);
	@ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
	while (!feof($fp)) { 
		echo fread($fp, $chunk);
		@ob_flush();  // flush output
		@flush();
	}
	exit();
?>