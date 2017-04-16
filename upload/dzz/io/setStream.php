<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
 if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

if(!$path=dzzdecode(rawurldecode($_GET['path']))){
	exit('Access Denied');
}

$tmp_filename = $_FILES['content']['tmp_name']; 
$msg='';
$cache='./cache/'.md5($path).'.tmp';
$upload_status = move_uploaded_file($tmp_filename, $_G['setting']['attachdir'].$cache); 
if(!$upload_status){
	$msg='save failure!';
}
$content=file_get_contents($_G['setting']['attachdir'].$cache);
if(!$msg && ($re=IO::setFileContent($path,$content,true))){
	if($re['error']) $msg=$re['error'];
	@unlink($_G['setting']['attachdir'].$cache);
}
if($msg){
	@header('HTTP/1.1 500 Not Found');
	@header('Status: 500 Not Found');
	exit();
}else{
	@header('HTTP/1.1 200 Not Found');
	exit();
}

?>
