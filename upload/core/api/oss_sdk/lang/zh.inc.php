<?php
/*%*************************************************************************************%*/
//access id & access key 相关
define('NOT_SET_OSS_ACCESS_ID', '未设置OSS服务的ACCESS ID');
define('NOT_SET_OSS_ACCESS_KEY', '未设置OSS服务的ACCESS KEY');
define('NOT_SET_OSS_ACCESS_ID_AND_ACCESS_KEY', '没有设置ACCESS ID & ACCESS KEY');
define('OSS_ACCESS_ID_OR_ACCESS_KEY_EMPTY', 'ACCESS ID或ACCESS KEY为空');

/*%*************************************************************************************%*/
//OSS语言包以及文件相关
define('OSS_LANG_FILE_NOT_EXIST', 'OSS语言包文件不存在');
define('OSS_CONFIG_FILE_NOT_EXIST',OSS_API_PATH.DIRECTORY_SEPARATOR.'conf.inc.php不存在');
define('OSS_UTILS_FILE_NOT_EXIST',OSS_API_PATH.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'utils.php不存在');
define('OSS_CURL_EXTENSION_MUST_BE_LOAD','系统没有安装CURL扩展');
define('OSS_NO_ANY_EXTENSIONS_LOADED','系统没有安装任何扩展,请检查系统配置');


/*%*************************************************************************************%*/
//日志文件相关
define('OSS_WRITE_LOG_TO_FILE_FAILED','日志写入失败,请检查日志文件是否存在或者日志文件的权限');
define('OSS_LOG_PATH_NOT_EXIST','日志路径不存在');

/*%**************************************************************************************%*/
//OSS bucket操作相关
define('OSS_OPTIONS_MUST_BE_ARRAY', '$option必须为数组');
define('OSS_GET_BUCKET_LIST_SUCCESS','获取Bucket列表成功!');
define('OSS_GET_BUCKET_LIST_FAILED', '获取Bucket列表失败!');
define('OSS_CREATE_BUCKET_SUCCESS', '创建Bucket成功');
define('OSS_CREATE_BUCKET_FAILED', '创建Bucket失败');
define('OSS_DELETE_BUCKET_SUCCESS', '删除Bucket成功');
define('OSS_DELETE_BUCKET_FAILED', '删除Bucket失败');
define('OSS_BUCKET_NAME_INVALID', '未通过Bucket名称规则校验');
define('OSS_GET_BUCKET_ACL_SUCCESS','获取Bucket ACL成功');
define('OSS_GET_BUCKET_ACL_FAILED','获取Bucket ACL失败');
define('OSS_SET_BUCKET_ACL_SUCCESS','设置Bucket ACL成功');
define('OSS_SET_BUCKET_ACL_FAILED','设置Bucket ACL失败');
define('OSS_ACL_INVALID','ACL不在允许范围,目前仅允许(private,public-read,public-read-write三种权限)');
define('OSS_BUCKET_IS_NOT_ALLOWED_EMPTY', 'Bucket不允许为空');
define('OSS_TARGET_BUCKET_IS_NOT_ALLOWED_EMPTY', 'TargetBucket不允许为空');
define('OSS_INDEX_DOCUMENT_IS_NOT_ALLOWED_EMPTY', 'IndexDocument不允许为空');

/*%****************************************************************************************%*/
//OSS object操作相关
define('OSS_GET_OBJECT_LIST_SUCCESS','获得OBJECT列表成功');
define('OSS_GET_OBJECT_LIST_FAILED','获得OBJECT列表失败');
define('OSS_CREATE_OBJECT_DIR_SUCCESS','创建OBJECT目录成功');
define('OSS_CREATE_OBJECT_DIR_FAILED','创建OBJECT目录失败');
define('OSS_DELETE_OBJECT_SUCCESS','删除OBJECT成功');
define('OSS_DELETE_OBJECT_FAILED','删除OBJECT失败');
define('OSS_UPLOAD_FILE_BY_CONTENT_SUCCESS','通过Http Body上传文件成功');
define('OSS_UPLOAD_FILE_BY_CONTENT_FAILED','通过Http Body上传文件失败');
define('OSS_GET_OBJECT_META_SUCCESS','获得OBJECT META成功');
define('OSS_GET_OBJECT_META_FAILED','获得OBJECT META失败');
define('OSS_OBJECT_NAME_INVALID','未通过Object名称规则校验');
define('OSS_OBJECT_IS_NOT_ALLOWED_EMPTY','Object不允许为空');
define('OSS_INVALID_HTTP_BODY_CONTENT','Http Body的内容非法');
define('OSS_GET_OBJECT_SUCCESS','获得Object成功');
define('OSS_GET_OBJECT_FAILED','获得Object失败');
define('OSS_OBJECT_EXIST','Object存在');
define('OSS_OBJECT_NOT_EXIST','Object不存在');
define('OSS_NOT_SET_HTTP_CONTENT','为设置Http Body');
define('OSS_INVALID_CONTENT_LENGTH','非法的Content-Length值');
define('OSS_CONTENT_LENGTH_MUST_MORE_THAN_ZERO','Content-Length必须大于0');
define('OSS_UPLOAD_FILE_NOT_EXIST','上传文件不存在');
define('OSS_COPY_OBJECT_SUCCESS','拷贝Object成功');
define('OSS_COPY_OBJECT_FAILED', '拷贝Object失败');
define('OSS_FILE_NOT_EXIST','文件不存在');
define('OSS_FILE_PATH_IS_NOT_ALLOWED_EMPTY', '上传文件路径为空');

/*%****************************************************************************************%*/
//OSS object Group操作相关
define('OSS_CREATE_OBJECT_GROUP_SUCCESS','创建Object Group成功');
define('OSS_CREATE_OBJECT_GROUP_FAILED','创建Object Group失败');
define('OSS_GET_OBJECT_GROUP_SUCCESS','获取Object Group成功');
define('OSS_GET_OBJECT_GROUP_FAILED','获取Object Group失败');
define('OSS_GET_OBJECT_GROUP_INDEX_SUCCESS','获取Object Group Index成功');
define('OSS_GET_OBJECT_GROUP_INDEX_FAILED','获取Object Group Index失败');
define('OSS_GET_OBJECT_GROUP_META_SUCCESS','获取Object Group Group Meta成功');
define('OSS_GET_OBJECT_GROUP_META_FAILED','获取Object Group Group Meta失败');
define('OSS_DELETE_OBJECT_GROUP_SUCCESS','删除Object Group Group成功');
define('OSS_DELETE_OBJECT_GROUP_FAILED','删除Object Group Group失败');
define('OSS_OBJECT_GROUP_IS_NOT_ALLOWED_EMPTY', 'Object Group不允许为空');
define('OSS_OBJECT_ARRAY_IS_EMPTY','创建Object Group的Object不允许为空');
define('OSS_OBJECT_GROUP_TOO_MANY_OBJECT','每个Object Group最多包含1000个Object');

/*%****************************************************************************************%*/
//OSS Multi-Part Upload相关
define('OSS_INITIATE_MULTI_PART_SUCCESS', '初始化Multi-Part Upload成功');
define('OSS_INITIATE_MULTI_PART_FAILED', '初始化Multi-Part Upload失败');

/*%*******************************************************************************************%*/
//其他
define('OSS_INVALID_OPTION_HEADERS', 'OPTIONS不是数组');





