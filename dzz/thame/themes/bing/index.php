<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
	$str=file_get_contents('http://cn.bing.com/HPImageArchive.aspx?idx=1&n=1');
/*	$arr=new  SimpleXMLElement($str);
	print_r($arr);
	exit($arr->image->url);
*/	if(preg_match("/<url>(.+?)<\/url>/is",$str,$matches)){
		$imgurl='http://cn.bing.com'.$matches[1];
	}else{
		$imgurl='../../../../images/b.gif';
	}
	
	header('Content-Type: image/JPEG');
	@ob_end_clean();
	@readfile($imgurl);
	@flush(); @ob_flush();
	exit();
?>

