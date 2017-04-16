<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

//oss操作类 
define('OSSON',intval($_G['setting']['storage']['on'])); //OSS PORT  

define('OSS_ACCESS_ID', $_G['setting']['storage']['ACCESS_ID']); //OSS ACCESS_ID  ,

define('OSS_ACCESS_KEY', $_G['setting']['storage']['ACCESS_KEY']); //OSS ACCESS_KEY

define('OSS_BUCKET', $_G['setting']['storage']['BUCKET']);    //BUCKET

define('OSS_SIGN_TIMEOUT',3600*2); //签名链接超时时间

define('OSS_HOST_NAME',"oss.aliyuncs.com"); //OSS TIMEOUT

define('OSS_HOST_PORT',"80"); //OSS PORT  

define('OSSURL','http://'.OSS_BUCKET.'.'.OSS_HOST_NAME.'/'); //OSS PORT 

 
class OSS {
	/**
 * 函数定义
 */
/*%**************************************************************************************************************%*/
// Service 相关

//获取bucket列表
	function get_service($obj){
		$response = $obj->list_bucket();
		
		_format($response);
		return $response;
	}

/*%**************************************************************************************************************%*/
// Bucket 相关

	//创建bucket
	function create_bucket($obj,$bucket){
		$acl = ALIOSS::OSS_ACL_TYPE_PRIVATE;
		//$acl = ALIOSS::OSS_ACL_TYPE_PUBLIC_READ;
		//$acl = ALIOSS::OSS_ACL_TYPE_PUBLIC_READ_WRITE;
		
		$response = $obj->create_bucket($bucket,$acl);
		_format($response);
		return $response;
	}

//删除bucket
	function delete_bucket($obj,$bucket){
		
		
		$response = $obj->delete_bucket($bucket);
		
		_format($response);
		return $response;
	}

//设置bucket ACL
function set_bucket_acl($obj){
	
	//$acl = ALIOSS::OSS_ACL_TYPE_PRIVATE;
	$acl = ALIOSS::OSS_ACL_TYPE_PUBLIC_READ;
	//$acl = ALIOSS::OSS_ACL_TYPE_PUBLIC_READ_WRITE;
	
	$response = $obj->set_bucket_acl(OSS_BUCKET,$acl);
	_format($response);
	return $response;
}

//获取bucket ACL
function get_bucket_acl($obj){
	
	$options = array(
		ALIOSS::OSS_CONTENT_TYPE => 'text/xml',
	);
		
	$response = $obj->get_bucket_acl(OSS_BUCKET,$options);
	_format($response);	
	return $response;
}

/*%**************************************************************************************************************%*/
// Object 相关

//获取object列表
function list_object($obj){
	
	$options = array(
		'delimiter' => '',
		'prefix' => '',
		'max-keys' => 100,
		//'marker' => 'myobject-1330850469.pdf',
	);
	
	$response = $obj->list_object(OSS_BUCKET,$options);	
	_format($response);
}

//创建目录
function create_directory($obj,$folder){
	$response  = $obj->create_object_dir(OSS_BUCKET,$folder);
	_format($response);
	return $response->isOK();
}

//通过内容上传文件
function upload_by_content($obj,$content,$object){
	
	$upload_file_options = array(
		'content' => $content,
		'length' => strlen($content)
	);
	$response = $obj->upload_file_by_content(OSS_BUCKET,$object,$upload_file_options);
	
	//_format($response);
	return $response->isOK();
}

//通过路径上传文件
function upload_by_file($obj,$object,$filepath){
	$response = $obj->upload_file_by_file(OSS_BUCKET,$object,$filepath);
	return $response->isOK();
}

//拷贝object
function copy_object($obj,$from_object,$to_object){
		//copy object
		$response = $obj->copy_object(OSS_BUCKET,$from_object,OSS_BUCKET,$to_object);
		return $response->isOK();
}

//获取object meta
function get_object_meta($obj,$object){
	$response = $obj->get_object_meta(OSS_BUCKET,$object);
	_format($response);
	return $response;
}

//删除object
function delete_object($obj,$object){
	
	$response = $obj->delete_object(OSS_BUCKET,$object);
	//_format($response);
	return $response->isOK();
}

//删除objects
function delete_objects($obj,$objects){
	
	$options = array(
		'quiet' => false,
		//ALIOSS::OSS_CONTENT_TYPE => 'text/xml',
	);
	
	$response = $obj->delete_objects(OSS_BUCKET,$objects,$options);
	_format($response);
	return $response->isOK();
}

//获取object
function get_object($obj,$object,$objectdir){
	
	
	$options = array(
		ALIOSS::OSS_FILE_DOWNLOAD => $objectdir,
		//ALIOSS::OSS_CONTENT_TYPE => 'txt/html',
	);	
	$response = $obj->get_object(OSS_BUCKET,$object,$options);
	return $response->isOK();
}

//检测object是否存在
function is_object_exist($obj,$object){
	$response = $obj->is_object_exist(OSS_BUCKET,$object);
	return $respons->isOK();
}

//通过multipart上传文件
function upload_by_multi_part($obj,$object,$filepath,$partsize="5242880"){
	
	//$object = 'Mining.the.Social.Web-'.time().'.pdf';  //英文
	//$filepath = "D:\\Book\\Mining.the.Social.Web.pdf";  //英文
		
	$options = array(
		ALIOSS::OSS_FILE_UPLOAD => $filepath,
		'partSize' => 5242880,
	);

	$response = $obj->create_mpu_object(OSS_BUCKET, $object,$options);
	_format($response);
	return $response->isOK();
}

//通过multipart上传整个目录
function upload_by_dir($obj,$dir){
	
	//$dir = "D:\\alidata\\www\\logs\\aliyun.com\\oss\\";
	$recursive = false;
	
	$response = $obj->create_mtu_object_by_dir(OSS_BUCKET,$dir,$recursive);
	var_dump($response);
	return $response;	
}

//通过multi-part上传整个目录(新版)
function batch_upload_file($obj,$object,$dir){
	$options = array(
		'bucket' 	=> OSS_BUCKET,
		'object'	=> $object,
		'directory' =>$dir, // 'D:\alidata\www\logs\aliyun.com\oss',
	);
	$response = $obj->batch_upload_file($options);
}



/*%**************************************************************************************************************%*/
// 签名url 相关

//生成签名url,主要用户私有权限下的访问控制
function get_sign_url($obj,$object){
	
	//$object = 'netbeans-7.1.2-ml-cpp-linux.sh';
	$timeout = OSS_SIGN_TIMEOUT;

	$response = $obj->get_sign_url(OSS_BUCKET,$object,$timeout);
	return $response;
}

/*%**************************************************************************************************************%*/
// 结果 相关

//格式化返回结果
function _format($response) {
	echo '|-----------------------Start---------------------------------------------------------------------------------------------------'."\n";
	echo '|-Status:' . $response->status . "\n";
	echo '|-Body:' ."\n"; 
	echo $response->body . "\n";
	echo "|-Header:\n";
	print_r ( $response->header );
	echo '-----------------------End-----------------------------------------------------------------------------------------------------'."\n\n";
}
}  
?>
