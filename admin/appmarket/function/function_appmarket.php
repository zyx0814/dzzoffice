<?php
/* @authorcode  codestrings
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      qchlian(3580164@qq.com)
 */
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

function  dzzunzip($filename,$path,$md5file){
	if( file_exists( $filename )){//压缩包存在 
		//打开压缩包
		$resource = zip_open($filename);
		$i = 1;
		//遍历读取压缩包里面的一个个文件 
		while ($dir_resource = zip_read($resource)) {
		  //如果能打开则继续
		  if (zip_entry_open($resource,$dir_resource)) {
			//获取当前项目的名称,即压缩包里面当前对应的文件名
			$file_name = $path.zip_entry_name($dir_resource);
			//以最后一个“/”分割,再用字符串截取出路径部分
			$file_path = substr($file_name,0,strrpos($file_name, "/"));
			//如果路径不存在，则创建一个目录，true表示可以创建多级目录
			if(!is_dir($file_path)){
			  mkdir($file_path,0777,true);
			}
			//如果不是目录，则写入文件
			if(!is_dir($file_name)){
			  //读取这个文件
			  if( $file_name==$md5file){
				  continue; //排查md5文件，dzzmdfile会重新生成
			  } 
			  $file_size = zip_entry_filesize($dir_resource);
			  //最大读取6M，如果文件过大，跳过解压，继续下一个
			  //if($file_size<(1024*1024*300)){
				$file_content = zip_entry_read($dir_resource,$file_size);
				if(file_exists( $file_name)){
					@unlink($file_name);
				}
				file_put_contents($file_name,$file_content); 
			}
			//关闭当前
			zip_entry_close($dir_resource);
		  }
		} 
		//关闭压缩包
		zip_close($resource); 
	} 
}
?>
