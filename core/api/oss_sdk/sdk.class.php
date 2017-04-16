<?php
/**
 * OSS(Open Storage Services) PHP SDK v1.1.6
 */
//设置默认时区
date_default_timezone_set('Asia/Shanghai');

//检测API路径
if(!defined('OSS_API_PATH'))
define('OSS_API_PATH', dirname(__FILE__));


//加载conf.inc.php文件
require_once OSS_API_PATH.DIRECTORY_SEPARATOR.'conf.inc.php';

//加载RequestCore
require_once OSS_API_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'requestcore'.DIRECTORY_SEPARATOR.'requestcore.class.php';

//加载MimeTypes
require_once OSS_API_PATH.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'mimetypes.class.php';

//检测语言包
if(file_exists(OSS_API_PATH.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.ALI_LANG.'.inc.php')){
	require_once OSS_API_PATH.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.ALI_LANG.'.inc.php';
}else{
	throw new OSS_Exception(OSS_LANG_FILE_NOT_EXIST);
}

//定义软件名称，版本号等信息
define('OSS_NAME','oss-sdk-php');
define('OSS_VERSION','1.1.6');
define('OSS_BUILD','201210121010245');
define('OSS_AUTHOR', 'xiaobing.meng@alibaba-inc.com');

// EXCEPTIONS

/**
 * OSS异常类，继承自基类
 */
class OSS_Exception extends Exception {}


//检测get_loaded_extensions函数是否被禁用。由于有些版本把该函数禁用了，所以先检测该函数是否存在。
if(function_exists('get_loaded_extensions')){
	//检测curl扩展
	$extensions = get_loaded_extensions();
	if($extensions){
		if(!in_array('curl', $extensions)){
			throw new OSS_Exception(OSS_CURL_EXTENSION_MUST_BE_LOAD);
		}
	}else{
		throw new OSS_Exception(OSS_NO_ANY_EXTENSIONS_LOADED);
	}
}else{
	throw new OSS_Exception('Function get_loaded_extensions has been disabled,Pls check php config.');
}


//CLASS
/**
 * OSS基础类
 * @author xiaobing.meng@alibaba-inc.com
 * @since 2012-05-31
 */

class ALIOSS{
	/*%******************************************************************************************%*/
	// CONSTANTS

	/**
	 * OSS服务地址
	 */
	const DEFAULT_OSS_HOST = 'oss.aliyuncs.com';
	//const DEFAULT_OSS_HOST = '10.230.201.90';
	/**
	 * 软件名称
	 */
	const NAME = OSS_NAME;

	/**
	 * OSS软件Build ID
	 */
	const BUILD = OSS_BUILD;

	/**
	 * 版本号
	 */
	const VERSION = OSS_VERSION;

	/**
	 * 作者
	 */
	const AUTHOR = OSS_AUTHOR;

	/*%******************************************************************************************%*/
	//OSS 内部常量

	const OSS_BUCKET = 'bucket';
	const OSS_OBJECT = 'object';
	const OSS_HEADERS = 'headers';
	const OSS_METHOD = 'method';
	const OSS_QUERY = 'query';
	const OSS_BASENAME = 'basename';
	const OSS_MAX_KEYS = 'max-keys';
	const OSS_UPLOAD_ID = 'uploadId';
	const OSS_MAX_KEYS_VALUE = 100;
	const OSS_MAX_OBJECT_GROUP_VALUE = 1000;
	const OSS_FILE_SLICE_SIZE = 8192;
	const OSS_PREFIX = 'prefix';
	const OSS_DELIMITER = 'delimiter';
	const OSS_MARKER = 'marker';
	const OSS_CONTENT_MD5 = 'Content-Md5';
	const OSS_CONTENT_TYPE = 'Content-Type';
	const OSS_CONTENT_LENGTH = 'Content-Length';
	const OSS_IF_MODIFIED_SINCE = 'If-Modified-Since';
	const OSS_IF_UNMODIFIED_SINCE = 'If-Unmodified-Since';
	const OSS_IF_MATCH = 'If-Match';
	const OSS_IF_NONE_MATCH = 'If-None-Match';
	const OSS_CACHE_CONTROL = 'Cache-Control';
	const OSS_EXPIRES = 'Expires';
	const OSS_PREAUTH = 'preauth';
	const OSS_CONTENT_COING = 'Content-Coding';
	const OSS_CONTENT_DISPOSTION = 'Content-Disposition';
	const OSS_RANGE = 'Range';
	const OS_CONTENT_RANGE = 'Content-Range';
	const OSS_CONTENT = 'content';
	const OSS_BODY = 'body';
	const OSS_LENGTH = 'length';
	const OSS_HOST = 'Host';
	const OSS_DATE = 'Date';
	const OSS_AUTHORIZATION = 'Authorization';
	const OSS_FILE_DOWNLOAD = 'fileDownload';
	const OSS_FILE_UPLOAD = 'fileUpload';
	const OSS_PART_SIZE = 'partSize';
	const OSS_SEEK_TO = 'seekTo';
	const OSS_SIZE = 'size';
	const OSS_QUERY_STRING = 'query_string';
	const OSS_SUB_RESOURCE = 'sub_resource';
	const OSS_DEFAULT_PREFIX = 'x-oss-';

	/*%******************************************************************************************%*/
	//私有URL变量

	const OSS_URL_ACCESS_KEY_ID = 'OSSAccessKeyId';
	const OSS_URL_EXPIRES = 'Expires';
	const OSS_URL_SIGNATURE = 'Signature';

	/*%******************************************************************************************%*/
	//HTTP方法

	const OSS_HTTP_GET = 'GET';
	const OSS_HTTP_PUT = 'PUT';
	const OSS_HTTP_HEAD = 'HEAD';
	const OSS_HTTP_POST = 'POST';
	const OSS_HTTP_DELETE = 'DELETE';
    const OSS_HTTP_OPTIONS = 'OPTIONS';

	/*%******************************************************************************************%*/
	//其他常量

	//x-oss
	const OSS_ACL = 'x-oss-acl';

	//OBJECT GROUP
	const OSS_OBJECT_GROUP = 'x-oss-file-group';
	
	//Multi Part
	const OSS_MULTI_PART = 'uploads';
	
	//Multi Delete
	const OSS_MULTI_DELETE = 'delete';

	//OBJECT COPY SOURCE
	const OSS_OBJECT_COPY_SOURCE = 'x-oss-copy-source';

	//private,only owner
	const OSS_ACL_TYPE_PRIVATE = 'private';

	//public reand
	const OSS_ACL_TYPE_PUBLIC_READ = 'public-read';

	//public read write
	const OSS_ACL_TYPE_PUBLIC_READ_WRITE = 'public-read-write';

	//OSS ACL数组
	static $OSS_ACL_TYPES = array(
	self::OSS_ACL_TYPE_PRIVATE,
	self::OSS_ACL_TYPE_PUBLIC_READ,
	self::OSS_ACL_TYPE_PUBLIC_READ_WRITE
	);

    const OSS_CORS_ALLOWED_ORIGIN='AllowedOrigin';
    const OSS_CORS_ALLOWED_METHOD='AllowedMethod';
    const OSS_CORS_ALLOWED_HEADER='AllowedHeader';
    const OSS_CORS_EXPOSE_HEADER='ExposeHeader';
    const OSS_CORS_MAX_AGE_SECONDS='MaxAgeSeconds';
    
    
    const OSS_OPTIONS_ORIGIN = 'Origin';
    const OSS_OPTIONS_REQUEST_METHOD = 'Access-Control-Request-Method';
    const OSS_OPTIONS_REQUEST_HEADERS = 'Access-Control-Request-Headers';

	/*%******************************************************************************************%*/
	// PROPERTIES

	/**
	 * 是否使用SSL
	 */
	public $hostarray=array( 'qingdao_out'=>'oss-cn-qingdao.aliyuncs.com',
							 'qingdao_in'=>'oss-cn-qingdao-internal.aliyuncs.com',
							 
							 'beijing_out'=>'oss-cn-beijing.aliyuncs.com',
							 'beijing_in'=>'oss-cn-beijing-internal.aliyuncs.com',
							 
							 'hangzhou_out'=>'oss-cn-hangzhou.aliyuncs.com',
							 'hangzhou_in'=>'oss-cn-hangzhou-internal.aliyuncs.com',
							 
							 'hongkong_out'=>'oss-cn-hongkong.aliyuncs.com',
							 'hongkong_in'=>'oss-cn-hongkong-internal.aliyuncs.com',
							 
							  'shenzhen_out'=>'oss-cn-shenzhen.aliyuncs.com',
							 'shenzhen_in'=>'oss-cn-shenzhen-internal.aliyuncs.com'
							);
	public $use_ssl = false;

	/**
	 * 是否开启debug模式
	 */
	private $debug_mode = false;
	
	/**
	 * 最大重试次数
	 */
	private $max_retries = 3;
	
	/**
	 * 已经重试次数
	 */
	private   $redirects = 0;	
	
	/**
	 * 虚拟地址
	 */
	private $vhost;
	
	/**
	 * 路径表现方式
	 */
	private $enable_domain_style = true;
	
	/**
	 * 请求URL
	 */
	private  $request_url;
	
	/**
	 * OSS API ACCESS ID
	 */
	private $access_id;

	/**
	 * OSS API ACCESS KEY
	 */
	private $access_key;

	/**
	 * hostname
	 */
	public $hostname;
	
	/**
	 * port number
	 */
	public $port;

	/*%******************************************************************************************************%*/
	//Constructor

	/**
	 * 默认构造函数
	 * @param string $_access_id (Optional)
	 * @param string $access_key (Optional)
	 * @param string $hostname (Optional)
	 * @throws OSS_Exception
	 * @author	xiaobing.meng@alibaba-inc.com
	 * @since	2011-11-08
	 */
	public function __construct($access_id = NULL, $access_key = NULL, $hostname = NULL  ){
		//验证access_id,access_key
		if(!$access_id && !defined('OSS_ACCESS_ID')){
				throw new OSS_Exception(NOT_SET_OSS_ACCESS_ID);
		}

		if(!$access_key && !defined('OSS_ACCESS_KEY')){
			throw new OSS_Exception(NOT_SET_OSS_ACCESS_KEY);
		}

		if($access_id && $access_key){
			$this->access_id = $access_id;
			$this->access_key = $access_key;
		}elseif (defined('OSS_ACCESS_ID') && defined('OSS_ACCESS_KEY')){
			$this->access_id = OSS_ACCESS_ID;
			$this->access_key = OSS_ACCESS_KEY;
		}else{
			throw new OSS_Exception(NOT_SET_OSS_ACCESS_ID_AND_ACCESS_KEY);
		}

		//校验access_id&access_key 
		if(empty($this->access_id) || empty($this->access_key)){
			throw new OSS_Exception(OSS_ACCESS_ID_OR_ACCESS_KEY_EMPTY);
		}

		//校验hostname
		if(NULL === $hostname){
			$this->hostname = self::DEFAULT_OSS_HOST;
		}else{
			$this->hostname = $hostname;
			
		}
	}


	/*%******************************************************************************************************%*/
	//属性
	
	/**
	 * 设置debug模式
	 * @param boolean $debug_mode (Optional)
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2012-05-29
	 * @return void
	 */
	public function set_debug_mode($debug_mode = true){
		$this->debug_mode = $debug_mode;
	}
	
	/**
	 * 设置最大尝试次数
	 * @param int $max_retries
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2012-05-29
	 * @return void
	 */
	public function set_max_retries($max_retries = 3){
		$this->max_retries = $max_retries;
	}
	
	/**
	 * 获取最大尝试次数
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2012-05-29
	 * @return int
	 */
	public function get_max_retries(){
		return $this->max_retries;
	}

	/**
	 * 设置host地址
	 * @author xiaobing.meng@alibaba-inc.com
	 * @param string $hostname host name
	 * @param int	$port int
	 * @since 2012-06-11
	 * @return void
	 */
	public function set_host_name($hostname,$port = null){
		$this->hostname = $hostname;
		
		if($port){
			$this->port = $port;
			$this->hostname .= ':'.$port;
		}
	}
	
	/**
	 * 设置vhost地址
	 * @author xiaobing.meng@alibaba-inc.com
	 * @param string $vhost vhost
	 * @since 2012-06-11
	 * @return void
	 */
	public function set_vhost($vhost){
		$this->vhost = $vhost;
	}
	
	/**
	 * 设置路径形式，如果为true,则启用三级域名，如bucket.oss.aliyuncs.com
	 * @author xiaobing.meng@alibaba-inc.com
	 * @param boolean $enable_domain_style 
	 * @since 2012-06-11
	 * @return void
	 */
	public function set_enable_domain_style($enable_domain_style = true){
		$this->enable_domain_style = $enable_domain_style;
	}

	
	/*%******************************************************************************************************%*/
	//请求

	/**
	 * Authorization
	 * @param array $options (Required)
	 * @throws OSS_Exception
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2012-05-31
	 */
	public function auth($options){
		//开始记录LOG
		$msg = "---LOG START---------------------------------------------------------------------------\n";
		
		//验证Bucket,list_bucket时不需要验证
		if(!( ('/' == $options[self::OSS_OBJECT]) && ('' == $options[self::OSS_BUCKET]) && ('GET' == $options[self::OSS_METHOD])) && !$this->validate_bucket($options[self::OSS_BUCKET])){
			throw new OSS_Exception('"'.$options[self::OSS_BUCKET].'"'.OSS_BUCKET_NAME_INVALID);
		}
	
		//验证Object
		if(isset($options[self::OSS_OBJECT]) && !$this->validate_object($options[self::OSS_OBJECT])){
			throw  new OSS_Exception($options[self::OSS_OBJECT].OSS_OBJECT_NAME_INVALID);
		}
		
		//Object编码为UTF-8
		if($this->is_gb2312($options[self::OSS_OBJECT])){
			$options[self::OSS_OBJECT] = iconv('GB2312', "UTF-8",$options[self::OSS_OBJECT]);
		}elseif($this->check_char($options[self::OSS_OBJECT],true)){
			$options[self::OSS_OBJECT] = iconv('GBK', "UTF-8",$options[self::OSS_OBJECT]);
		}	

		
		//验证ACL
		if(isset($options[self::OSS_HEADERS][self::OSS_ACL]) && !empty($options[self::OSS_HEADERS][self::OSS_ACL])){
			if(!in_array(strtolower($options[self::OSS_HEADERS][self::OSS_ACL]), self::$OSS_ACL_TYPES)){
				throw new OSS_Exception($options[self::OSS_HEADERS][self::OSS_ACL].':'.OSS_ACL_INVALID);
			}
		}

				
		//定义scheme
		$scheme = $this->use_ssl ? 'https://' : 'http://';
		
		if($this->enable_domain_style){
			$hostname = $this->vhost ? $this->vhost : (($options[self::OSS_BUCKET] =='')?$this->hostname:($options[self::OSS_BUCKET].'.').$this->hostname);
		}else{
			$hostname = (isset($options[self::OSS_BUCKET]) && ''!==$options[self::OSS_BUCKET])?$this->hostname.'/'.$options[self::OSS_BUCKET]:$this->hostname;
		}

		
		//请求参数
		$resource = '';
		$sub_resource = '';
		$signable_resource = '';
		$query_string_params = array();
		$signable_query_string_params = array();
		$string_to_sign = '';		
		
		$headers = array (
			self::OSS_CONTENT_MD5 => '',
			self::OSS_CONTENT_TYPE => isset($options[self::OSS_CONTENT_TYPE])?$options[self::OSS_CONTENT_TYPE]:'application/x-www-form-urlencoded',
			self::OSS_DATE => isset($options[self::OSS_DATE])? $options[self::OSS_DATE]: gmdate('D, d M Y H:i:s \G\M\T'),
			self::OSS_HOST => $this->enable_domain_style?$hostname:$this->hostname,
		);

		if(isset ( $options [self::OSS_OBJECT] ) && '/' !== $options [self::OSS_OBJECT]){
			//$options[self::OSS_OBJECT] = $this->replace_invalid_xml_char($options[self::OSS_OBJECT]);
			$signable_resource = '/'.str_replace(array('%2F','%25'),array('/','%'), rawurlencode($options[self::OSS_OBJECT]));
		}

		if(isset($options[self::OSS_QUERY_STRING])){
			$query_string_params = array_merge($query_string_params,$options[self::OSS_QUERY_STRING]);
		}
		$query_string = $this->to_query_string($query_string_params);
	
		$signable_list = array(
			'partNumber',
			'uploadId',			
		);
		
		foreach ($signable_list as $item){
			if(isset($options[$item])){
				$signable_query_string_params[$item] = $options[$item]; 
			}
		}
		$signable_query_string = $this->to_query_string($signable_query_string_params);
		
		//合并 HTTP headers
		if (isset ( $options [self::OSS_HEADERS] )) {
			$headers = array_merge ( $headers, $options [self::OSS_HEADERS] );
		}
		
		//生成请求URL
		$conjunction = '?';
		
		$non_signable_resource = '';

		if (isset($options[self::OSS_SUB_RESOURCE])){
			$signable_resource .= $conjunction . $options[self::OSS_SUB_RESOURCE];
			$conjunction = '&';
		}	

		if($signable_query_string !== ''){
			$signable_query_string = $conjunction.$signable_query_string;
			$conjunction = '&';
		}
		
		if($query_string !== ''){
			$non_signable_resource .= $conjunction . $query_string;
			$conjunction = '&';			
		}
		
		$this->request_url = 	 $scheme . $hostname . $signable_resource . $signable_query_string . $non_signable_resource;

		$msg .= "--REQUEST URL:----------------------------------------------\n".$this->request_url."\n";
		
		//创建请求
		$request = new RequestCore($this->request_url);
		 
		// Streaming uploads
		if (isset($options[self::OSS_FILE_UPLOAD])){
			if (is_resource($options[self::OSS_FILE_UPLOAD])){
				$length = null; 

				if (isset($options[self::OSS_CONTENT_LENGTH])){
					$length = $options[self::OSS_CONTENT_LENGTH];
				}elseif (isset($options[self::OSS_SEEK_TO])){
					
					$stats = fstat($options[self::OSS_FILE_UPLOAD]);

					if ($stats && $stats[self::OSS_SIZE] >= 0){
						$length = $stats[self::OSS_SIZE] - (integer) $options[self::OSS_SEEK_TO];
					}
				}

				$request->set_read_stream($options[self::OSS_FILE_UPLOAD], $length);

				if ($headers[self::OSS_CONTENT_TYPE] === 'application/x-www-form-urlencoded'){
					$headers[self::OSS_CONTENT_TYPE] = 'application/octet-stream';
				}
			}else{
				$request->set_read_file($options[self::OSS_FILE_UPLOAD]);

				$length = $request->read_stream_size; 

				if (isset($options[self::OSS_CONTENT_LENGTH])){
					$length = $options[self::OSS_CONTENT_LENGTH];
				}elseif (isset($options[self::OSS_SEEK_TO]) && isset($length)){
					$length -= (integer) $options[self::OSS_SEEK_TO];
				}

				$request->set_read_stream_size($length);

				if (isset($headers[self::OSS_CONTENT_TYPE]) && ($headers[self::OSS_CONTENT_TYPE] === 'application/x-www-form-urlencoded')){
					$extension = explode('.', $options[self::OSS_FILE_UPLOAD]);
					$extension = array_pop($extension);
					$mime_type = MimeTypes::get_mimetype($extension);
					$headers[self::OSS_CONTENT_TYPE] = $mime_type;
				}
			}

			$options[self::OSS_CONTENT_MD5] = '';
		}

		if (isset($options[self::OSS_SEEK_TO])){
			$request->set_seek_position((integer) $options[self::OSS_SEEK_TO]);
		}	

		if (isset($options[self::OSS_FILE_DOWNLOAD])){
			if (is_resource($options[self::OSS_FILE_DOWNLOAD])){
				$request->set_write_stream($options[self::OSS_FILE_DOWNLOAD]);
			}else{
				$request->set_write_file($options[self::OSS_FILE_DOWNLOAD]);
			}
		}		
		
		
		if(isset($options[self::OSS_METHOD])){
			$request->set_method($options[self::OSS_METHOD]);
			$string_to_sign .= $options[self::OSS_METHOD] . "\n";			
		}
		
		if (isset ( $options [self::OSS_CONTENT] )) {
			$request->set_body ( $options [self::OSS_CONTENT] );
			if ($headers[self::OSS_CONTENT_TYPE] === 'application/x-www-form-urlencoded'){
				$headers[self::OSS_CONTENT_TYPE] = 'application/octet-stream';
			}			
			
			$headers[self::OSS_CONTENT_LENGTH] = strlen($options [self::OSS_CONTENT]);
			$headers[self::OSS_CONTENT_MD5] = $this->hex_to_base64(md5($options[self::OSS_CONTENT]));
		}

		uksort($headers, 'strnatcasecmp');
		
		foreach ( $headers as $header_key => $header_value ) {
			$header_value = str_replace ( array ("\r", "\n" ), '', $header_value );
			if ($header_value !== '') {
				$request->add_header ( $header_key, $header_value );
			}

			if (
				strtolower($header_key) === 'content-md5' ||
				strtolower($header_key) === 'content-type' ||
				strtolower($header_key) === 'date' ||
				(isset($options['self::OSS_PREAUTH']) && (integer) $options['self::OSS_PREAUTH'] > 0)
			){
				$string_to_sign .= $header_value . "\n";
			}elseif (substr(strtolower($header_key), 0, 6) === self::OSS_DEFAULT_PREFIX){
				$string_to_sign .= strtolower($header_key) . ':' . $header_value . "\n";
			}			
		}
	
		$string_to_sign .= '/' . $options[self::OSS_BUCKET];
		$string_to_sign .=  $this->enable_domain_style ? ($options[self::OSS_BUCKET]!=''? ($options[self::OSS_OBJECT]=='/'?'/':'') :'' ) : '';
		$string_to_sign .= rawurldecode($signable_resource) . urldecode($signable_query_string);

		$msg .= "STRING TO SIGN:----------------------------------------------\n".$string_to_sign."\n";
		
		$signature = base64_encode(hash_hmac('sha1', $string_to_sign, $this->access_key, true));
		$request->add_header('Authorization', 'OSS ' . $this->access_id . ':' . $signature);

		if (isset($options[self::OSS_PREAUTH]) && (integer) $options[self::OSS_PREAUTH] > 0){
			return $this->request_url . $conjunction . self::OSS_URL_ACCESS_KEY_ID.'=' . $this->access_id . '&'.self::OSS_URL_EXPIRES.'=' . $options[self::OSS_PREAUTH] . '&'.self::OSS_URL_SIGNATURE.'=' . rawurlencode($signature);
		}elseif (isset($options[self::OSS_PREAUTH])){
			return $this->request_url;
		}		
		
		if ($this->debug_mode){
			$request->debug_mode = $this->debug_mode;
		}

		$msg .= "REQUEST HEADERS:----------------------------------------------\n".serialize($request->request_headers)."\n";
		
		$request->send_request();

		$response_header = $request->get_response_header();
		$response_header['x-oss-request-url'] = $this->request_url;
		$response_header['x-oss-redirects'] = $this->redirects;
		$response_header['x-oss-stringtosign'] = $string_to_sign;
		$response_header['x-oss-requestheaders'] = $request->request_headers;				
		
		$msg .= "RESPONSE HEADERS:----------------------------------------------\n".serialize($response_header)."\n";
		
		$data =  new ResponseCore ( $response_header , $request->get_response_body (), $request->get_response_code () );
	
		if((integer)$request->get_response_code() === 400 /*Bad Request*/ || (integer)$request->get_response_code() === 500 /*Internal Error*/ || (integer)$request->get_response_code() === 503 /*Service Unavailable*/){	
		   if($this->redirects <= $this->max_retries ){
		   		//设置休眠
		   		$delay = (integer) (pow(4, $this->redirects) * 100000);
		   		usleep($delay);
		   		$this->redirects++;
		   		$data = $this->auth($options);
		   }
		}
		
		$msg .= "RESPONSE DATA:----------------------------------------------\n".serialize($data)."\n";
		$msg .= date('Y-m-d H:i:s').":---LOG END---------------------------------------------------------------------------\n";
		//add log
		$this->log($msg);
		
		$this->redirects = 0;	
		return $data;
	}


	/*%******************************************************************************************************%*/
	//Service Operation

	/**
	 * Get Bucket list
	 * @param array $options (Optional)
	 * @throws OSS_Exception
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-11-14
	 * @return ResponseCore
	 */
	public function list_bucket($options = NULL) {
		//$options
		$this->validate_options($options);

		if (! $options) {
			$options = array ();
		}

		$options[self::OSS_BUCKET] = '';
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = '/';
		$response = $this->auth ( $options );

		return $response;
	}


	/*%******************************************************************************************************%*/
	//Bucket Operation

	/**
	 * Create Bucket
	 * @param string $bucket (Required)
	 * @param string $acl (Optional)
	 * @param array $options (Optional)
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-11-14
	 * @return ResponseCore
	 */
	public function create_bucket($bucket, $acl = self::OSS_ACL_TYPE_PRIVATE, $options = NULL){
		//$options
		$this->validate_options($options);

		if (! $options) {
			$options = array ();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_HEADERS] = array(self::OSS_ACL => $acl);
		$response = $this->auth ( $options );

		return $response;
	}

	/**
	 * Delete Bucket
	 * @param string $bucket (Required)
	 * @param array $options (Optional)
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-11-14
	 * @return ResponseCore
	 */
	public function delete_bucket($bucket, $options = NULL){
		//$options
		$this->validate_options($options);

		if (! $options) {
			$options = array ();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
		$options[self::OSS_OBJECT] = '/';
		$response = $this->auth ( $options );

		return $response;
	}

	/**
	 * Get Bucket's ACL
	 * @param string $bucket (Required)
	 * @param array $options (Optional)
	 * @throws OSS_Exception
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-11-14
	 * @return ResponseCore
	 */
	public function get_bucket_acl($bucket, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'acl';
		$response = $this->auth ( $options );

		return $response;
	}

	/**
	 * Set Bucket'ACL
	 * @param string $bucket (Required)
	 * @param string $acl  (Required)
	 * @param array $options (Optional)
	 * @throws OSS_Exception
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-11-14
	 * @return ResponseCore
	 */
	public function set_bucket_acl($bucket, $acl, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_HEADERS] = array(self::OSS_ACL => $acl);
		$response = $this->auth ( $options );

		return $response;
	}
	
     /**
	 * Get Bucket's Logging
	 * @param string $bucket (Required)
	 * @param array $options (Optional)
	 * @throws OSS_Exception
	 * @author lijie.ma@alibaba-inc.com
	 * @since 2014-05-04
	 * @return ResponseCore
	 */
	public function get_bucket_logging($bucket, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'logging';
		$response = $this->auth ($options);

		return $response;
	}

     /**
	 * Set Bucket's Logging
	 * @param string $bucket (Required)
	 * @param string $target_bucket  (Required)
	 * @param string $target_prefix  (Optional)
	 * @param array $options (Optional)
	 * @throws OSS_Exception
	 * @author lijie.ma@alibaba-inc.com
	 * @since 2014-05-04
	 * @return ResponseCore
	 */
	public function set_bucket_logging($bucket, $target_bucket, $target_prefix, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);
		$this->is_empty($target_bucket,OSS_TARGET_BUCKET_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'logging';
		$options[self::OSS_CONTENT_TYPE] = 'application/xml';

		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><BucketLoggingStatus></BucketLoggingStatus>');
		$logging_enabled_part=$xml->addChild('LoggingEnabled');
		$logging_enabled_part->addChild('TargetBucket', $target_bucket);
		$logging_enabled_part->addChild('TargetPrefix', $target_prefix);

		$options[self::OSS_CONTENT] = $xml->asXML();		
		echo	$xml->asXML();
		return $this->auth($options);
	}
	
     /**
	 * Delete Bucket's Logging
	 * @param string $bucket (Required)
	 * @param array $options (Optional)
	 * @throws OSS_Exception
	 * @author lijie.ma@alibaba-inc.com
	 * @since 2014-05-04
	 * @return ResponseCore
	 */
	public function delete_bucket_logging($bucket, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'logging';
		$response = $this->auth ($options);

		return $response;
	}
	
     /**
	 * Set Bucket's Website
	 * @param string $bucket (Required)
	 * @param string $index_document  (Required)
	 * @param string $error_document  (Optional)
	 * @param array $options (Optional)
	 * @throws OSS_Exception
	 * @author lijie.ma@alibaba-inc.com
	 * @since 2014-05-04
	 * @return ResponseCore
	 */
	public function set_bucket_website($bucket, $index_document, $error_document, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);
		$this->is_empty($index_document,OSS_INDEX_DOCUMENT_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'website';
		$options[self::OSS_CONTENT_TYPE] = 'application/xml';

		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><WebsiteConfiguration></WebsiteConfiguration>');
		$index_document_part=$xml->addChild('IndexDocument');
		$error_document_part=$xml->addChild('ErrorDocument');
		
		$index_document_part->addChild('Suffix', $index_document);
		$error_document_part->addChild('Key', $error_document);

		$options[self::OSS_CONTENT] = $xml->asXML();		
		echo	$xml->asXML();
		return $this->auth($options);
	}
	
     /**
	 * Get Bucket's Website
	 * @param string $bucket (Required)
	 * @param array $options (Optional)
	 * @throws OSS_Exception
	 * @author lijie.ma@alibaba-inc.com
	 * @since 2014-05-04
	 * @return ResponseCore
	 */
	public function get_bucket_website($bucket, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'website';
		$response = $this->auth ($options);

		return $response;
	}
	
     /**
	 * Delete Bucket's Website
	 * @param string $bucket (Required)
	 * @param array $options (Optional)
	 * @throws OSS_Exception
	 * @author lijie.ma@alibaba-inc.com
	 * @since 2014-05-04
	 * @return ResponseCore
	 */
	public function delete_bucket_website($bucket, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'website';
		$response = $this->auth ($options);

		return $response;
	}
	
     /**
	 * Set Bucket's Cors
	 * @param string $bucket (Required)
	 * @param array $cors_rules  (Required)
	 * @param array $options (Optional)
	 * @throws OSS_Exception
	 * @author lijie.ma@alibaba-inc.com
	 * @since 2014-05-04
	 * @return ResponseCore
	 */
	public function set_bucket_cors($bucket, $cors_rules, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'cors';
		$options[self::OSS_CONTENT_TYPE] = 'application/xml';

		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><CORSConfiguration></CORSConfiguration>');
		
		foreach ($cors_rules as $cors_rule){
			$cors_rule_part = $xml->addChild('CORSRule');
			
			foreach ($cors_rule[self::OSS_CORS_ALLOWED_ORIGIN] as $value){	
				$cors_rule_part->addChild(self::OSS_CORS_ALLOWED_ORIGIN, $value);
			}
			
			foreach ($cors_rule[self::OSS_CORS_ALLOWED_HEADER] as $value){	
				$cors_rule_part->addChild(self::OSS_CORS_ALLOWED_HEADER, $value);
			}
			
			foreach ($cors_rule[self::OSS_CORS_ALLOWED_METHOD] as $value){	
				$cors_rule_part->addChild(self::OSS_CORS_ALLOWED_METHOD, $value);
			}
			
			foreach ($cors_rule[self::OSS_CORS_EXPOSE_HEADER] as $value){	
				$cors_rule_part->addChild(self::OSS_CORS_EXPOSE_HEADER, $value);
			}
			
			$cors_rule_part->addChild(self::OSS_CORS_MAX_AGE_SECONDS, $cors_rule[self::OSS_CORS_MAX_AGE_SECONDS]);
		}	

		$options[self::OSS_CONTENT] = $xml->asXML();		
		return $this->auth($options);
	}
     /**
	 * Get Bucket's Cors
	 * @param string $bucket (Required)
	 * @param array $options (Optional)
	 * @throws OSS_Exception
	 * @author lijie.ma@alibaba-inc.com
	 * @since 2014-05-04
	 * @return ResponseCore
	 */
	public function get_bucket_cors($bucket, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'cors';
		$response = $this->auth ($options);

		return $response;
	}
	
     /**
	 * Delete Bucket's Cors
	 * @param string $bucket (Required)
	 * @param array $options (Optional)
	 * @throws OSS_Exception
	 * @author lijie.ma@alibaba-inc.com
	 * @since 2014-05-04
	 * @return ResponseCore
	 */
	public function delete_bucket_cors($bucket, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'cors';
		$response = $this->auth ($options);

		return $response;
	}
	
     /**
	 * Options Object
	 * @param string $bucket (Required)
	 * @param array $options_request  (Required)
	 * @param array $options (Optional)
	 * @throws OSS_Exception
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-11-14
	 * @return ResponseCore
	 */
	public function options_object($bucket, $object, $origin, $request_method, $request_headers, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_OPTIONS;
		$options[self::OSS_OBJECT] = $object;
		$options[self::OSS_HEADERS] = array(self::OSS_OPTIONS_ORIGIN => $origin, self::OSS_OPTIONS_REQUEST_HEADERS => $request_headers, self::OSS_OPTIONS_REQUEST_METHOD => $request_method);
		$response = $this->auth ( $options );

		return $response;
	}
	/*%******************************************************************************************************%*/
	//Object Operation

	/**
	 * List Object
	 * @param string $bucket (Required)
	 * @param array $options (Optional)
	 * 其中options中的参数如下
	 * $options = array(
	 * 		'max-keys' 	=> max-keys用于限定此次返回object的最大数，如果不设定，默认为100，max-keys取值不能大于100。
	 * 		'prefix'	=> 限定返回的object key必须以prefix作为前缀。注意使用prefix查询时，返回的key中仍会包含prefix。
	 * 		'delimiter' => 是一个用于对Object名字进行分组的字符。所有名字包含指定的前缀且第一次出现delimiter字符之间的object作为一组元素
	 * 		'marker'	=> 用户设定结果从marker之后按字母排序的第一个开始返回。
	 * )
	 * 其中 prefix，marker用来实现分页显示效果，参数的长度必须小于256字节。
	 * @throws OSS_Exception
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-11-14
	 * @return ResponseCore
	 */
	public function list_object($bucket, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_HEADERS] = array(
		self::OSS_DELIMITER => isset($options[self::OSS_DELIMITER])?$options[self::OSS_DELIMITER]:'/',
		self::OSS_PREFIX => isset($options[self::OSS_PREFIX])?$options[self::OSS_PREFIX]:'',
		self::OSS_MAX_KEYS => isset($options[self::OSS_MAX_KEYS])?$options[self::OSS_MAX_KEYS]:self::OSS_MAX_KEYS_VALUE,
		self::OSS_MARKER => isset($options[self::OSS_MARKER])?$options[self::OSS_MARKER]:'',
		);
				
		$response = $this->auth ( $options );

		return $response;

	}

	/**
	 * 创建目录(目录和文件的区别在于，目录最后增加'/')
	 * @param string $bucket
	 * @param string $object
	 * @param array $options
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-11-14
	 * @return ResponseCore
	 */
	public function create_object_dir($bucket, $object, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//object
		$this->is_empty($object,OSS_OBJECT_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_OBJECT] = $object.'/';   //虚拟目录需要以'/结尾'
		$options[self::OSS_CONTENT_LENGTH] = array(self::OSS_CONTENT_LENGTH => 0);

		$response = $this->auth ( $options );

		return $response;
	}

	/**
	 * 通过在http body中添加内容来上传文件，适合比较小的文件
	 * 根据api约定，需要在http header中增加content-length字段
	 * @param string $bucket (Required)
	 * @param string $object (Required)
	 * @param string $content (Required)
	 * @param array $options (Optional)
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-11-14
	 * @return ResponseCore
	 */
	public function upload_file_by_content($bucket, $object, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//object
		$this->is_empty($object,OSS_OBJECT_IS_NOT_ALLOWED_EMPTY);

		//内容校验
		$this->validate_content($options);

			
		$objArr = explode('/', $object);
		$basename = array_pop($objArr);
		$extension = explode ( '.', $basename );
		$extension = array_pop ( $extension );
		$content_type = MimeTypes::get_mimetype(strtolower($extension));

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_OBJECT] = $object;
		
		if(!isset($options[self::OSS_LENGTH])){
			$options[self::OSS_CONTENT_LENGTH] = strlen($options[self::OSS_CONTENT]);
		}else{
			$options[self::OSS_CONTENT_LENGTH] = $options[self::OSS_LENGTH];
		}
		
		if(!isset($options[self::OSS_CONTENT_TYPE]) && isset($content_type) && !empty($content_type) ){
			$options[self::OSS_CONTENT_TYPE] = $content_type;
		}

		$response = $this->auth ( $options );

		return $response;
	}

	/**
	 * 上传文件，适合比较大的文件
	 * @param string $bucket (Required)
	 * @param string $object (Required)
	 * @param string $file (Required)
	 * @param array $options (Optional)
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2012-02-28
	 * @return ResponseCore
	 */
	public function upload_file_by_file($bucket, $object, $file, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//object
		$this->is_empty($object,OSS_OBJECT_IS_NOT_ALLOWED_EMPTY);

		//file
		$this->is_empty($file, OSS_FILE_PATH_IS_NOT_ALLOWED_EMPTY);
		
		if($this->chk_chinese($file)){
			$file = iconv('utf-8','gbk',$file);
		}
		
		$options[self::OSS_FILE_UPLOAD] = $file;
		
		if(!file_exists($options[self::OSS_FILE_UPLOAD])){
			throw new OSS_Exception($options[self::OSS_FILE_UPLOAD].OSS_FILE_NOT_EXIST);
		}
		
		$filesize = filesize($options[self::OSS_FILE_UPLOAD]);
		$partsize = 1024 * 1024 ; //默认为 1M
		
		
		$extension = explode ( '.', $object );
		$extension = array_pop ( $extension );
		$content_type = MimeTypes::get_mimetype(strtolower($extension));
				
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_OBJECT] = $object;
		$options[self::OSS_CONTENT_TYPE] = $content_type;
		$options[self::OSS_CONTENT_LENGTH] = $filesize;
		$response = $this->auth($options);
		return $response;
	}
	
	
	/**
	 * 拷贝Object
	 * @param string $bucket (Required)
	 * @param string $from_object (Required)
	 * @param string $to_object (Required)
	 * @param string $options (Optional)
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-12-21
	 * @return ResponseCore
	 */
	public function copy_object($from_bucket, $from_object, $to_bucket, $to_object, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//from bucket
		$this->is_empty($from_bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//to bucket
		$this->is_empty($to_bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//from object
		$this->is_empty($from_object,OSS_OBJECT_IS_NOT_ALLOWED_EMPTY);

		//to object
		$this->is_empty($to_object,OSS_OBJECT_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $to_bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_OBJECT] = $to_object;
		$options[self::OSS_HEADERS] = array(self::OSS_OBJECT_COPY_SOURCE => '/'.$from_bucket.'/'.$from_object);

		$response = $this->auth ( $options );

		return $response;
	}

	/**
	 * 获得object的meta信息
	 * @param string $bucket (Required)
	 * @param string $object (Required)
	 * @param string $options (Optional)
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-11-14
	 * @return ResponseCore
	 */
	public function get_object_meta($bucket, $object, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//object
		$this->is_empty($object,OSS_OBJECT_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_HEAD;
		$options[self::OSS_OBJECT] = $object;

		$response = $this->auth ( $options );

		return $response;
	}

	/**
	 * 删除object
	 * @param string $bucket(Required)
	 * @param string $object (Required)
	 * @param array $options (Optional)
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-11-14
	 * @return ResponseCore
	 */
	public function delete_object($bucket, $object, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//object
		$this->is_empty($object,OSS_OBJECT_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
		$options[self::OSS_OBJECT] = $object;

		$response = $this->auth ( $options );

		return $response;
	}
	
	/**
	 * 批量删除objects
	 * @param string $bucket(Required)
	 * @param array $objects (Required)
	 * @param array $options (Optional)
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2012-03-09
	 * @return ResponseCore
	 */
	public function delete_objects($bucket, $objects, $options = null){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//objects
		if(!is_array($objects) || !$objects){
			throw new OSS_Exception('The ' . __FUNCTION__ . ' method requires the "objects" option to be set as an array.');
		}
		
		$options[self::OSS_METHOD] = self::OSS_HTTP_POST;
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'delete';
		$options[self::OSS_CONTENT_TYPE] = 'application/xml';
		
		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><Delete></Delete>');
		
		// Quiet mode?
		if (isset($options['quiet'])){
			$quiet = 'false';
			if (is_bool($options['quiet'])) { //Boolean
				$quiet = $options['quiet'] ? 'true' : 'false';
			}elseif (is_string($options['quiet'])){ // String
				$quiet = ($options['quiet'] === 'true') ? 'true' : 'false';
			}

			$xml->addChild('Quiet', $quiet);
		}
				
		// Add the objects
		foreach ($objects as $object){
			$xobject = $xml->addChild('Object');
			$object = $this->s_replace($object);		
			$xobject->addChild('Key', $object);
		}		

		$options[self::OSS_CONTENT] = $xml->asXML();		
			
		return $this->auth($options);
	}

	/**
	 * 获得Object内容
	 * @param string $bucket(Required)
	 * @param string $object (Required)
	 * @param array $options (Optional)
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-11-14
	 * @return ResponseCore
	 */
	public function get_object($bucket, $object, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//object
		$this->is_empty($object,OSS_OBJECT_IS_NOT_ALLOWED_EMPTY);
		
		if(isset($options[self::OSS_FILE_DOWNLOAD]) && $this->chk_chinese($options[self::OSS_FILE_DOWNLOAD])){
			$options[self::OSS_FILE_DOWNLOAD] = iconv('utf-8','gbk',$options[self::OSS_FILE_DOWNLOAD]);
		}

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = $object;
		
		if(isset($options['lastmodified'])){
			$options[self::OSS_HEADERS][self::OSS_IF_MODIFIED_SINCE] = $options['lastmodified'];
            unset($options['lastmodified']);
		}
		
		if(isset($options['etag'])){
			$options[self::OSS_HEADERS][self::OSS_IF_NONE_MATCH] = $options['etag'];
            unset($options['etag']);
		}
		
		if(isset($options['range'])){
			$options[self::OSS_HEADERS][self::OSS_RANGE] = 'bytes=' . $options['range'];
            unset($options['range']);
		}
		
		return $this->auth ( $options );
	}

	/**
	 * 检测Object是否存在
	 * @param string $bucket(Required)
	 * @param string $object (Required)
	 * @param array $options (Optional)
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-11-14
	 * @return boolean
	 */
	public function is_object_exist($bucket, $object, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//object
		$this->is_empty($object,OSS_OBJECT_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = $object;

		$response = $this->get_object_meta($bucket, $object,$options);

		return $response;
	}


	/*%******************************************************************************************************%*/
	//Multi Part相关操作	
	
	/**
	 * 计算文件可以分成多少个part，以及每个part的长度以及起始位置
	 * 方法必须在 <upload_part()>中调用
	 *
	 * @param integer $filesize (Required) 文件大小
	 * @param integer $part_size (Required) part大小,默认5M
	 * @return array An array 包含 key-value 键值对. Key 为 `seekTo` 和 `length`.
	 */	
	public function get_multipart_counts($filesize, $part_size = 5242880 ){
		$i = 0;
		$sizecount = $filesize;
		$values = array();

		if((integer)$part_size <= 5242880){ 
			$part_size = 5242880;	//5M
		}elseif ((integer)$part_size > 524288000){
			$part_size = 524288000; //500M
		}else{
			$part_size = 52428800; //50M
		}		
		
		while ($sizecount > 0)
		{
			$sizecount -= $part_size;
			$values[] = array(
				self::OSS_SEEK_TO => ($part_size * $i),
				self::OSS_LENGTH => (($sizecount > 0) ? $part_size : ($sizecount + $part_size)),
			);
			$i++;
		}

		return $values;		
	}
	
	/**
	 * 初始化multi-part upload，并且返回uploadId
	 * @param string $bucket (Required) Bucket名称
	 * @param string $object (Required) Object名称
	 * @param array $options (Optional) Key-Value数组，其中可以包括以下的key
	 * @return ResponseCore
	 */
	public function initiate_multipart_upload($bucket, $object, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//object
		$this->is_empty($object,OSS_OBJECT_IS_NOT_ALLOWED_EMPTY);

		// 发送请求
		$options[self::OSS_METHOD] = self::OSS_HTTP_POST;
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_OBJECT] = $object;
		$options[self::OSS_SUB_RESOURCE] = 'uploads';
		$options[self::OSS_CONTENT] = '';
		//$options[self::OSS_CONTENT_LENGTH] = 0;
		$options[self::OSS_HEADERS] = array(self::OSS_CONTENT_TYPE => 'application/octet-stream');

		$response = $this->auth ( $options );

		return $response;		
	}
	
	/**
	 * 上传part
	 * @param string $bucket (Required) Bucket名称
	 * @param string $object (Required) Object名称
	 * @param string $upload_id (Required) uploadId
	 * @param array $options (Optional) Key-Value数组，其中可以包括以下的key
	 * @return ResponseCore
	 */
	public function upload_part($bucket, $object, $upload_id, $options = null){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//object
		$this->is_empty($object,OSS_OBJECT_IS_NOT_ALLOWED_EMPTY);

		if (!isset($options[self::OSS_FILE_UPLOAD]) || !isset($options['partNumber'])){
			throw new OSS_Exception('The `fileUpload` and `partNumber` options are both required in ' . __FUNCTION__ . '().');
		}
						
		$options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_OBJECT] = $object;
		$options[self::OSS_UPLOAD_ID] = $upload_id;	
		
		if(isset($options[self::OSS_LENGTH])){
			$options[self::OSS_CONTENT_LENGTH] =  $options[self::OSS_LENGTH];
		}

		return $this->auth($options);
	}
	
	/**
	 * list part
	 * @param string $bucket (Required) Bucket名称
	 * @param string $object (Required) Object名称
	 * @param string $upload_id (Required) uploadId
	 * @param array $options (Optional) Key-Value数组，其中可以包括以下的key
	 * @return ResponseCore
	 */	
	public function list_parts($bucket, $object, $upload_id, $options = null){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//object
		$this->is_empty($object,OSS_OBJECT_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_OBJECT] = $object;
		$options[self::OSS_UPLOAD_ID] = $upload_id;
		$options[self::OSS_QUERY_STRING] = array();

		foreach (array('max-parts', 'part-number-marker') as $param){
			if (isset($options[$param])){
				$options[self::OSS_QUERY_STRING][$param] = $options[$param];
				unset($options[$param]);
			}
		}	

		return $this->auth($options);
	}
	
	/**
	 * 中止上传mulit-part upload
	 * @param string $bucket (Required) Bucket名称
	 * @param string $object (Required) Object名称
	 * @param string $upload_id (Required) uploadId
	 * @param array $options (Optional) Key-Value数组，其中可以包括以下的key
	 * @return ResponseCore
	 */	
	public function abort_multipart_upload($bucket, $object, $upload_id, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//object
		$this->is_empty($object,OSS_OBJECT_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_OBJECT] = $object;
		$options[self::OSS_UPLOAD_ID] = $upload_id;
		
		return $this->auth($options);
	}
	
	/**
	 * 完成multi-part上传
	 * @param string $bucket (Required) Bucket名称
	 * @param string $object (Required) Object名称
	 * @param string $upload_id (Required) uploadId
	 * @param string $parts xml格式文件
	 * @param array $options (Optional) Key-Value数组，其中可以包括以下的key
	 * @return ResponseCore
	 */	
	public function complete_multipart_upload($bucket, $object, $upload_id, $parts, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//object
		$this->is_empty($object,OSS_OBJECT_IS_NOT_ALLOWED_EMPTY);
		
		$options[self::OSS_METHOD] = self::OSS_HTTP_POST;
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_OBJECT] = $object;
		$options[self::OSS_UPLOAD_ID] = $upload_id;
		$options[self::OSS_CONTENT_TYPE] = 'application/xml';

		
		if(is_string($parts)){
			$options[self::OSS_CONTENT] = $parts;
		}else if($parts instanceof SimpleXMLElement){
			$options[self::OSS_CONTENT] = $parts->asXML();
		}else if((is_array($parts) || $parts instanceof ResponseCore)){
			$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><CompleteMultipartUpload></CompleteMultipartUpload>');

			if (is_array($parts)){
				//生成关联的xml
				foreach ($parts as $node){
					$part = $xml->addChild('Part');
					$part->addChild('PartNumber', $node['PartNumber']);
					$part->addChild('ETag', $node['ETag']);
				}
			}elseif ($parts instanceof ResponseCore){
				foreach ($parts->body->Part as $node){
					$part = $xml->addChild('Part');
					$part->addChild('PartNumber', (string) $node->PartNumber);
					$part->addChild('ETag', (string) $node->ETag);
				}
			}

			$options[self::OSS_CONTENT] = $xml->asXML();			
		}
	
		return $this->auth($options);		
	}
	
	/**
	 * 列出multipart上传
	 * @param string $bucket (Requeired) bucket 
	 * @param array $options (Optional) 关联数组
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2012-03-05
	 * @return ResponseCore
	 */
	public function list_multipart_uploads($bucket, $options = null){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_OBJECT] = '/';
		$options[self::OSS_SUB_RESOURCE] = 'uploads';

		foreach (array('key-marker', 'max-uploads', 'upload-id-marker') as $param){
			if (isset($options[$param])){
				$options[self::OSS_QUERY_STRING][$param] = $options[$param];
				unset($options[$param]);
			}
		}
				
		return $this->auth($options);
	}
	
	/**
	 * multipart上传统一封装，从初始化到完成multipart，以及出错后中止动作
	 * @param unknown_type $bucket
	 * @param unknown_type $object
	 * @param unknown_type $options
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2012-03-05
	 * @return ResponseCore 
	 */
	public function create_mpu_object($bucket, $object, $options = null){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//object
		$this->is_empty($object,OSS_OBJECT_IS_NOT_ALLOWED_EMPTY);

		if(isset($options[self::OSS_LENGTH])){
			$options[self::OSS_CONTENT_LENGTH] = $options[self::OSS_LENGTH];
			unset($options[self::OSS_LENGTH]);
		}
		
		if(isset($options[self::OSS_FILE_UPLOAD])){
			if($this->chk_chinese($options[self::OSS_FILE_UPLOAD])){
				$options[self::OSS_FILE_UPLOAD] = mb_convert_encoding($options[self::OSS_FILE_UPLOAD],'UTF-8');
			}			
		}
		
		if(!isset($options[self::OSS_FILE_UPLOAD])){
			throw new OSS_Exception('The `fileUpload` option is required in ' . __FUNCTION__ . '().');
		}elseif (is_resource($options[self::OSS_FILE_UPLOAD])){					
			$upload_position = isset($options[self::OSS_SEEK_TO]) ? (integer) $options[self::OSS_SEEK_TO] : ftell($options[self::OSS_FILE_UPLOAD]);
			$upload_filesize = isset($options[self::OSS_CONTENT_LENGTH]) ? (integer) $options[self::OSS_CONTENT_LENGTH] : null;

			if (!isset($upload_filesize) && $upload_position !== false){
				$stats = fstat($options[self::OSS_FILE_UPLOAD]);

				if ($stats && $stats[self::OSS_SIZE] >= 0){
					$upload_filesize = $stats[self::OSS_SIZE] - $upload_position;
				}
			}			
		}else{
			$upload_position = isset($options[self::OSS_SEEK_TO]) ? (integer) $options[self::OSS_SEEK_TO] : 0;

			if (isset($options[self::OSS_CONTENT_TYPE])){
				$upload_filesize = (integer) $options[self::OSS_CONTENT_TYPE];
			}
			else{
				$upload_filesize = filesize($options[self::OSS_FILE_UPLOAD]);

				if ($upload_filesize !== false){
					$upload_filesize -= $upload_position;
				}
			}			
		}
		
		if ($upload_position === false || !isset($upload_filesize) || $upload_filesize === false || $upload_filesize < 0){
			throw new OSS_Exception('The size of `fileUpload` cannot be determined in ' . __FUNCTION__ . '().');
		}
		
		// 处理partSize
		if (isset($options[self::OSS_PART_SIZE])){
			// 小于5M
			if ((integer) $options[self::OSS_PART_SIZE] <= 5242880){
				$options[self::OSS_PART_SIZE] = 5242880; // 5 MB
			}
			// 大于500M
			elseif ((integer) $options[self::OSS_PART_SIZE] > 524288000){
				$options[self::OSS_PART_SIZE] = 524288000; // 500 MB
			}
		}
		else{
			$options[self::OSS_PART_SIZE] = 52428800; // 50 MB
		}

		// 如果上传的文件小于partSize,则直接使用普通方式上传
		if ($upload_filesize < $options[self::OSS_PART_SIZE] && !isset($options['uploadId'])){
			return $this->upload_file_by_file($bucket, $object, $options[self::OSS_FILE_UPLOAD]);
		}	

		// 初始化multipart
		if (isset($options['uploadId'])){
			$upload_id = $options['uploadId'];
		}else{
			//初始化
			$upload = $this->initiate_multipart_upload($bucket, $object);
			
			if (!$upload->isOK()){
				throw new OSS_Exception('Init multi-part upload failed...');
			}
			$xml = new SimpleXmlIterator($upload->body);
			$uploadId = (string)$xml->UploadId;
		}		

		// 或的分片
		$pieces = $this->get_multipart_counts($upload_filesize, (integer) $options[self::OSS_PART_SIZE]);

		$response_upload_part = array();
		foreach ($pieces as $i => $piece){
			$response_upload_part[] = $this->upload_part($bucket, $object, $uploadId, array(
				//'expect' => '100-continue',
				self::OSS_FILE_UPLOAD => $options[self::OSS_FILE_UPLOAD],
				'partNumber' => ($i + 1),
				self::OSS_SEEK_TO => $upload_position + (integer) $piece[self::OSS_SEEK_TO],
				self::OSS_LENGTH => (integer) $piece[self::OSS_LENGTH],
			));
		}
		
		$upload_parts = array();
		$upload_part_result = true;
		
		foreach ($response_upload_part as $i=>$response){
			$upload_part_result = $upload_part_result && $response->isOk();
		}
		
		if(!$upload_part_result){
			throw new OSS_Exception('any part upload failed...,pls try again');
		}
		
		foreach ($response_upload_part as $i=>$response){
			$upload_parts[] = array(
				'PartNumber' => ($i + 1),
			    'ETag' => (string) $response->header['etag']
			);		
		}
				
		return $this->complete_multipart_upload($bucket, $object, $uploadId, $upload_parts);
	}
	
	
	/**
	 * 通过Multi-Part方式上传整个目录，其中的object默认为文件名
	 * @param string $bucket (Required) 
	 * @param string $dir  (Required) 必选
	 * @param boolean $recursive (Optional) 是否递归，如果为true，则递归读取所有目录，默认为不递归读取
	 * @param string $exclude 需要过滤的文件
	 * @param array $options (Optional) 关联数组
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2012-03-05
	 * @return ResponseCore 
	 */
	public function create_mtu_object_by_dir($bucket, $dir, $recursive = false, $exclude = ".|..|.svn", $options = null){
		//options
		$this->validate_options($options);

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		if($this->chk_chinese($dir)){
			$dir = iconv('utf-8','gbk',$dir);
		}
		
		//判断是否目录
		if(!is_dir($dir)){
			throw new OSS_Exception($dir.' is not a directory...,pls check it');
		}
		
		$file_list_array = $this->read_dir($dir,$exclude,$recursive);
				
		if(!$file_list_array){
			throw new OSS_Exception($dir.' is empty...');
		}
		
		$index = 1;
		
		foreach ($file_list_array as $item){
			$options = array(
				self::OSS_FILE_UPLOAD => $item['path'],
				self::OSS_PART_SIZE => 5242880,
			);			
			
			echo $index++.". ";
			$response = $this->create_mpu_object($bucket, $item['file'],$options);
			if($response->isOK()){
				echo "Upload file {".$item['path']." } successful..\n";
			}else{
				echo "Upload file {".$item['path']." } failed..\n";
				continue;
			}
		}
	}
	
	/**
	 * 通过multi-part方式上传目录(优化版)
	 * $options = array(
	 * 		'bucket' 	=>  (Required)
	 * 		'object' 	=>  (Optional)
	 * 		'directory' =>  (Required)
	 * 		'exclude'	=>  (Optional)
	 * 		'recursive' =>  (Optional)
	 * )
	 */
	public function batch_upload_file($options = NULL){
		if((NULL == $options) || !isset($options['bucket']) || empty($options['bucket']) || !isset($options['directory']) ||empty($options['directory']) ) {
			throw new OSS_Exception('Bad Request',400);
		}
		
		$bucket = $options['bucket']; unset($options['bucket']);
		
		$directory = $options['directory']; unset($options['directory']);
		if($this->chk_chinese($directory)){
			$directory = iconv('utf-8','gbk',$directory);
		}		
		
		//判断是否目录
		if(!is_dir($directory)){
			throw new OSS_Exception($dir.' is not a directory...,pls check it');
		}
				
		$object = '';
		if(isset($options['object'])){
			$object = $options['object'];
			unset($options['object']);	
		}
		
		$exclude = '.|..|.svn';
		if (isset($options['exclude']) && !empty($options['exclude'])){
			$exclude = $options['exclude'];
			unset($options['exclude']);
		}
		
		$recursive = false;
		if(isset($options['recursive']) && !empty($options['recursive'])){
			if(in_array($options['recursive'],array(true,false))){
				$recursive = $options['recursive'];
			}
			unset($options['recursive']);
		}
		
		//read directory
		$file_list_array = $this->read_dir($directory,$exclude,$recursive);		
		
		if(!$file_list_array){
			throw new OSS_Exception($directory.' is empty...');
		}
		
		$index = 1;
		
		foreach ($file_list_array as $item){
			$options = array(
				self::OSS_FILE_UPLOAD => $item['path'],
				self::OSS_PART_SIZE => 5242880,
			);			
			
			echo $index++.". ";
			$response = $this->create_mpu_object($bucket, (!empty($object)?$object.'/':'').$item['file'],$options);
			if($response->isOK()){
				echo "Upload file {".$item['path']." } successful..\n";
			}else{
				echo "Upload file {".$item['path']." } failed..\n";
				continue;
			}
		}		
	}
	
		
	/*%******************************************************************************************************%*/
	//Object Group相关操作

	/**
	 * 创建Object Group
	 * @param string $object_group (Required)  Object Group名称
	 * @param string $bucket (Required) Bucket名称
	 * @param array $object_arry (Required) object数组，所有的object必须在同一个bucket下
	 * 其中$object 数组的格式如下:
	 * $object = array(
	 * 		$object1,
	 * 		$object2,
	 * 		...
	 * )
	 * @param array $options (Optional)
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-11-14
	 * @return ResponseCore
	 */
	public function create_object_group($bucket, $object_group, $object_arry, $options = NULL){
		//options
		$this->validate_options($options);

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//object group
		$this->is_empty($object_group,OSS_OBJECT_GROUP_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_POST;
		$options[self::OSS_OBJECT] = $object_group;
		$options[self::OSS_CONTENT_TYPE] = 'txt/xml';  //重设Content-Type
		$options[self::OSS_SUB_RESOURCE] = 'group';	   //设置?group
		$options[self::OSS_CONTENT] = $this->make_object_group_xml($bucket,$object_arry);   //格式化xml

		$response = $this->auth ( $options );

		return $response;
	}

	/**
	 * 获取Object Group
	 * @param string $object_group (Required)
	 * @param string $bucket	(Required)
	 * @param array $options	(Optional)
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-11-14
	 * @return ResponseCore
	 */
	public function get_object_group($bucket, $object_group, $options = NULL){
		//options
		$this->validate_options($options);

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//object group
		$this->is_empty($object_group,OSS_OBJECT_GROUP_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = $object_group;
		//$options[self::OSS_OBJECT_GROUP] = true;	   //设置?group
		//$options[self::OSS_CONTENT_TYPE] = 'txt/xml';  //重设Content-Type
		$options[self::OSS_HEADERS] = array(self::OSS_OBJECT_GROUP => self::OSS_OBJECT_GROUP);  //header中的x-oss-file-group不能为空，否则返回值错误

		$response = $this->auth ( $options );

		return $response;
	}

	/**
	 * 获取Object Group 的Object List信息
	 * @param string $object_group (Required)
	 * @param string $bucket	(Required)
	 * @param array $options	(Optional)
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-11-14
	 * @return ResponseCore
	 */
	public function get_object_group_index($bucket, $object_group, $options = NULL){
		//options
		$this->validate_options($options);

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//object group
		$this->is_empty($object_group,OSS_OBJECT_GROUP_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_OBJECT] = $object_group;
		$options[self::OSS_CONTENT_TYPE] = 'application/xml';  //重设Content-Type
		//$options[self::OSS_OBJECT_GROUP] = true;	   //设置?group
		$options[self::OSS_HEADERS] = array(self::OSS_OBJECT_GROUP => self::OSS_OBJECT_GROUP);

		$response = $this->auth ( $options );

		return $response;
	}

	/**
	 * 获得object group的meta信息
	 * @param string $bucket (Required)
	 * @param string $object_group (Required)
	 * @param string $options (Optional)
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-11-14
	 * @return ResponseCore
	 */
	public function get_object_group_meta($bucket, $object_group, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//object group
		$this->is_empty($object_group,OSS_OBJECT_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_HEAD;
		$options[self::OSS_OBJECT] = $object_group;
		$options[self::OSS_CONTENT_TYPE] = 'application/xml';  //重设Content-Type
		//$options[self::OSS_SUB_RESOURCE] = 'group';	   //设置?group
		$options[self::OSS_HEADERS] = array(self::OSS_OBJECT_GROUP => self::OSS_OBJECT_GROUP);

		$response = $this->auth ( $options );

		return $response;
	}

	/**
	 * 删除Object Group
	 * @param string $bucket(Required)
	 * @param string $object_group (Required)
	 * @param array $options (Optional)
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-11-14
	 * @return ResponseCore
	 */
	public function delete_object_group($bucket, $object_group, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//object group
		$this->is_empty($object_group,OSS_OBJECT_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
		$options[self::OSS_OBJECT] = $object_group;

		$response = $this->auth ( $options );

		return $response;
	}


	/*%******************************************************************************************************%*/
	//带签名的url相关

	/**
	 * 获取带签名的url
	 * @param string $bucket (Required)
	 * @param string $object (Required)
	 * @param int	 $timeout (Optional)
	 * @param array $options (Optional)
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-12-21
	 * @return string
	 */
	public function get_sign_url($bucket, $object, $timeout = 60, $options = NULL){
		//options
		$this->validate_options($options);

		if(!$options){
			$options = array();
		}

		//bucket
		$this->is_empty($bucket,OSS_BUCKET_IS_NOT_ALLOWED_EMPTY);

		//object
		$this->is_empty($object,OSS_OBJECT_IS_NOT_ALLOWED_EMPTY);

		$options[self::OSS_BUCKET] = $bucket;
		$options[self::OSS_OBJECT] = $object;
		$options[self::OSS_METHOD] = self::OSS_HTTP_GET;
		$options[self::OSS_CONTENT_TYPE] = '';

		$timeout = time() + $timeout;
		$options[self::OSS_PREAUTH] = $timeout;
		$options[self::OSS_DATE] = $timeout;

		return $this->auth($options);
	}

	/*%******************************************************************************************************%*/
	//日志相关

	/**
	 * 记录日志
	 * @param string $msg (Required)
	 * @throws OSS_Exception
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-12-27
	 * @return void
	 */
	private function log($msg){
		if(defined('ALI_LOG_PATH') ){
			$log_path = ALI_LOG_PATH;
			if(empty($log_path) || !file_exists($log_path)){
				throw new OSS_Exception($log_path.OSS_LOG_PATH_NOT_EXIST);
			}
		}else{
			$log_path = dirname(__FILE__).DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR;
		}
		
		//检测日志目录是否存在
		if(!file_exists($log_path)){
			throw new OSS_Exception(OSS_LOG_PATH_NOT_EXIST);
		}

		$log_name = $log_path.'oss_sdk_php_'.date('Y-m-d').'.log';

		if(ALI_DISPLAY_LOG){
			echo $msg."\n<br/>";
		}
		
		if(ALI_LOG){
			if(!error_log(date('Y-m-d H:i:s')." : ".$msg."\n", 3,$log_name)){
				throw new OSS_Exception(OSS_WRITE_LOG_TO_FILE_FAILED);
			}
		}
	}


	/*%******************************************************************************************************%*/
	//工具类相关

	/**
	 * 生成query params
	 * @param array $array 关联数组
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2012-03-04
	 * @return string 返回诸如 key1=value1&key2=value2
	 */
	public function to_query_string($options = array()){
		$temp = array();
		
		foreach ($options as $key => $value){
			if (is_string($key) && !is_array($value)){
				$temp[] = rawurlencode($key) . '=' . rawurlencode($value);
			}
		}

		return implode('&', $temp);
	}
	
	/**
	 * 转化十六进制的数据为base64
	 *
	 * @param string $str (Required) 要转化的字符串
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2012-03-20
	 * @return string Base64-encoded string.
	 */
	private function hex_to_base64($str){
		$result = '';

		for ($i = 0; $i < strlen($str); $i += 2){
			$result .= chr(hexdec(substr($str, $i, 2)));
		}

		return base64_encode($result);
	}

	private function s_replace($subject){
		$search = array('<','>','&','\'','"');
		$replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
		return str_replace($search, $replace, $subject);
	}
	
	/**
	 * 替换控制字符，诸如&#26; 替换为%1A
	 * @param unknown $invalid_xml_chars
	 */
	public function replace_invalid_xml_char($subject){
		$search = array(
			'&#01;','&#02;','&#03;','&#04;','&#05;','&#06;','&#07;','&#08;','&#09;','&#10;','&#11;','&#12;','&#13;',
			'&#14;','&#15;','&#16;','&#17;','&#18;','&#19;','&#20;','&#21;','&#22;','&#23;','&#24;','&#25;','&#26;',
			'&#27;','&#28;','&#29;','&#30;','&#31;','&#127;'
		);
		$replace = array(
			'%01','%02','%03','%04','%05','%06','%07','%08','%09','%0A','%0B','%0C','%0D',
			'%0E','%0F','%10','%11','%12','%13','%14','%15','%16','%17','%18','%19','%1A',
			'%1B','%1C','%1D','%1E','%1F','%7F'
		);
		
		return str_replace($search, $replace, $subject);
	}
	
	/**
	 * 检测是否含有中文
	 * @param string $subject
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2012-06-06
	 * @return boolean
	 */
	private function chk_chinese($str){
		return preg_match('/[\x80-\xff]./', $str);
	}
	
	/**
	 * 检测是否GB2312编码
	 * @param string $str 
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2012-03-20
	 * @return boolean false UTF-8编码  TRUE GB2312编码
	 */
	function is_gb2312($str)  {  
	    for($i=0; $i<strlen($str); $i++) {  
	        $v = ord( $str[$i] );  
	        if( $v > 127) {  
	            if( ($v >= 228) && ($v <= 233) ){  
	                if( ($i+2) >= (strlen($str) - 1)) return true;  // not enough characters  
	                $v1 = ord( $str[$i+1] );  
	                $v2 = ord( $str[$i+2] );  
	                if( ($v1 >= 128) && ($v1 <=191) && ($v2 >=128) && ($v2 <= 191) )  
	                    return false;   //UTF-8编码  
	                else  
	                    return true;    //GB编码  
	            }  
	        }  
	    }  
	} 


	/**
	 * 检测是否GBK编码
	 * @param string $str 
	 * @param boolean $gbk
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2012-06-04
	 * @return boolean 
	 */	
	private function check_char($str, $gbk = true){ 
	    for($i=0; $i<strlen($str); $i++) {
	        $v = ord( $str[$i] );
	        if( $v > 127){
	            if( ($v >= 228) && ($v <= 233) ){
	                 if(($i+2)>= (strlen($str)-1)) return $gbk?true:FALSE;  // not enough characters
	                 $v1 = ord( $str[$i+1] ); $v2 = ord( $str[$i+2] );
	                 if($gbk){
	                      return (($v1 >= 128) && ($v1 <=191) && ($v2 >=128) && ($v2 <= 191))?FALSE:TRUE;//GBK
	                 }else{
	                      return (($v1 >= 128) && ($v1 <=191) && ($v2 >=128) && ($v2 <= 191))?TRUE:FALSE;
	                 }
	            }
	        }
	    }
	   return $gbk?TRUE:FALSE;
	}


	/**
	 * 读取目录
	 * @param string $dir (Required) 目录名
	 * @param boolean $recursive (Optional) 是否递归，默认为false
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2012-03-05
	 * @return array
	 */
	private  function read_dir($dir, $exclude = ".|..|.svn", $recursive = false){
		static $file_list_array = array();
		
		$exclude_array = explode("|", $exclude);
		//读取目录
		if($handle = opendir($dir)){
			while ( false !== ($file = readdir($handle))){						
				if(!in_array(strtolower($file),$exclude_array)){
					$new_file = $dir.'/'.$file;					
					if(is_dir($new_file) && $recursive){
						$this->read_dir($new_file,$exclude,$recursive);
					}else{
						$file_list_array[] = array(
							'path' => $new_file,
							'file' => $file,
						);
					}
				}
			}
			
			closedir($handle);			
		}			
		
		return $file_list_array;
	}
	
	
	/**
	 * 转化object数组为固定个xml格式
	 * @param string $bucket (Required)
	 * @param array $object_array (Required)
	 * @throws OSS_Exception
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-12-27
	 * @return string
	 */
	private function make_object_group_xml($bucket, $object_array){
		$xml = '';
		$xml .= '<CreateFileGroup>';

		if($object_array){
			if(count($object_array) > self::OSS_MAX_OBJECT_GROUP_VALUE){
				throw new OSS_Exception(OSS_OBJECT_GROUP_TOO_MANY_OBJECT, '-401');
			}
			$index = 1;
			foreach ($object_array as $key=>$value){
				$object_meta = (array)$this->get_object_meta($bucket, $value);
				if(isset($object_meta) && isset($object_meta['status']) && isset($object_meta['header']) && isset($object_meta['header']['etag']) && $object_meta['status'] == 200){
					$xml .= '<Part>';
					$xml .= '<PartNumber>'.intval($index).'</PartNumber>';
					$xml .= '<PartName>'.$value.'</PartName>';
					$xml .= '<ETag>'.$object_meta['header']['etag'].'</ETag>';
					$xml .= '</Part>';

					$index++;
				}
			}
		}else{
			throw new OSS_Exception(OSS_OBJECT_ARRAY_IS_EMPTY, '-400');
		}

		$xml .= '</CreateFileGroup>';

		return $xml;
	}

	/**
	 * 检验bucket名称是否合法
	 * bucket的命名规范：
	 * 1. 只能包括小写字母，数字
	 * 2. 必须以小写字母或者数字开头
	 * 3. 长度必须在3-63字节之间
	 * @param string $bucket (Required)
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-12-27
	 * @return boolean
	 */
	private function validate_bucket($bucket){
		$pattern = '/^[a-z0-9][a-z0-9-]{2,62}$/';
		if (! preg_match ( $pattern, $bucket )) {
			return false;
		}
		return true;
	}

	/**
	 * 检验object名称是否合法
	 * object命名规范:
	 * 1. 规则长度必须在1-1023字节之间
	 * 2. 使用UTF-8编码
	 * @param string $object (Required)
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-12-27
	 * @return boolean
	 */
	private function validate_object($object){
		$pattern = '/^.{1,1023}$/';
		if (empty ( $object ) || ! preg_match ( $pattern, $object )) {
			return false;
		}
		return true;
	}

	/**
	 * 检验$options
	 * @param array $options (Optional)
	 * @throws OSS_Exception
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-12-27
	 * @return boolean 
	 */
	private function validate_options($options){
		//$options
		if ($options != NULL && ! is_array ( $options )) {
			throw new OSS_Exception ($options.':'.OSS_OPTIONS_MUST_BE_ARRAY);
		}
	}

	/**
	 * 检测上传文件的内容
	 * @param array $options (Optional)
	 * @throws OSS_Exception
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since  2011-12-27
	 * @return string
	 */
	private function validate_content($options){
		if(isset($options[self::OSS_CONTENT])){
			if($options[self::OSS_CONTENT] == '' || !is_string($options[self::OSS_CONTENT])){
				throw new OSS_Exception(OSS_INVALID_HTTP_BODY_CONTENT,'-600');
			}
		}else{
			throw new OSS_Exception(OSS_NOT_SET_HTTP_CONTENT, '-601');
		}
	}

	/**
	 * 验证文件长度
	 * @param array $options (Optional)
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-12-27
	 * @return void
	 */
	private function validate_content_length($options){
		if(isset($options[self::OSS_LENGTH]) && is_numeric($options[self::OSS_LENGTH])){
			if( ! $options[self::OSS_LENGTH] > 0){
				throw new OSS_Exception(OSS_CONTENT_LENGTH_MUST_MORE_THAN_ZERO, '-602');
			}
		}else{
			throw new OSS_Exception(OSS_INVALID_CONTENT_LENGTH, '-602');
		}
	}

	/**
	 * 校验BUCKET/OBJECT/OBJECT GROUP是否为空
	 * @param  string $name (Required)
	 * @param  string $errMsg (Required)
	 * @throws OSS_Exception
	 * @author xiaobing.meng@alibaba-inc.com
	 * @since 2011-12-27
	 * @return void
	 */
	private function is_empty($name,$errMsg){
		if(empty($name)){
			throw new OSS_Exception($errMsg);
		}
	}

	/**
	 * 设置http header
	 * @param string $key (Required)
	 * @param string $value (Required)
	 * @param array $options (Required)
	 * @throws OSS_Exception
	 * @author xiaobing.meng@alibaba-inc.com
	 * @return void
	 */
	private static function set_options_header($key, $value, &$options) {
		if (isset ( $options [self::OSS_HEADERS] )) {
			if (! is_array ( $options [self::OSS_HEADERS] )) {
				throw new OSS_Exception(OSS_INVALID_OPTION_HEADERS, '-600');
			}
		} else {
			$options [self::OSS_HEADERS] = array ();
		}

		$options [self::OSS_HEADERS] [$key] = $value;
	} 	
}
