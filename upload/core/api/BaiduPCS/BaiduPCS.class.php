<?php

/**
 * @desc PCS文件数据接口SDK, 要求PHP运行环境为5.2.0及以上
 * @package  baidu.pcs
 * @author   duanzhenxing(duanzhenxing@baidu.com)
 * @version  2.1.0
 */
require_once dirname ( __FILE__ ) . '/' . 'baiduRequestCore.class.php';

/**
 * @desc BaiduPCS类
 */
class BaiduPCS {

	/**
	 * 百度PCS RESTFUL API SERVER调用地址前缀
	 * @var array
	 */
	private $_pcs_uri_prefixs = array ('https' => 'https://pcs.baidu.com/rest/2.0/pcs/' );

	private $_accessToken = '';

	/**
	 * 初始化accessToken
	 * @param string $accessToken
	 */
	public function __construct($accessToken) {
		$this->_accessToken = $accessToken;
	}

	/**
	 * 设置accessToken
	 * @param string $_accessToken
	 * @return BaiduPCS
	 */
	public function setAccessToken($accessToken) {
		$this->_accessToken = $accessToken;
		return $this;
	}

	/**
	 * 获取accessToken
	 * @return string
	 */
	public function getAccessToken() {
		return $this->_accessToken;
	}

	/**
	 * 调用API
	 * @param string $apiMethod api方法名
	 * @param array || string  $params 请求参数
	 * @param string $method HTTP请求类型
	 * @param string $headers 附加的HTTP HEADER信息
	 * @return string
	 */
	private function _baseControl($apiMethod, $params, $method = 'GET', $headers = array()) {

		$method = strtoupper ( $method );

		if (is_array ( $params )) {
			$params = http_build_query ( $params, '', '&' );
		}

		$url = $this->_pcs_uri_prefixs ['https'] . $apiMethod . ($method == 'GET' ? '&' . $params : '');
		if($headers['returnurl']) return $url;
		$baiduRequestCore = new baiduRequestCore ();
		$baiduRequestCore->set_request_url ( $url );

		$baiduRequestCore->set_method ( $method );
		if ($method == 'POST') {
			$baiduRequestCore->set_body ( $params );
		}

		foreach ( $headers as $key => $value ) {
			$baiduRequestCore->add_header ( $key, $value );
		}

		$baiduRequestCore->send_request ();
		$result = $baiduRequestCore->get_response_body ();

		return $result;
	}

	/**
	 * 获取当前用户空间配额信息
	 * @return string
	 */
	public function getQuota() {
		$result = $this->_baseControl ( 'quota?method=info' . '&access_token=' . $this->_accessToken, array () );
		return $result;
	}

	/**
	 * 上传文件
	 * 注意：此方法适用于上传不大于2G的单个文件。
	 * @param string $fileContent 文件内容字符串
	 * @param string $targetPath 上传文件的目标保存路径
	 * @param string $fileName 文件名
	 * @param string $newFileName 新文件名
	 * @param boolean $isCreateSuperFile 是否分片上传
	 * @return string
	 */
	public function upload($fileContent, $targetPath, $fileName, $newFileName = null, $isCreateSuperFile = FALSE,$ondup = 'newcopy') {
		$boundary = md5 ( time () );
		$postContent .= "--" . $boundary . "\r\n";
		$postContent .= "Content-Disposition: form-data; name=\"file\"; filename=\"{$fileName}\"\r\n";
		$postContent .= "Content-Type: application/octet-stream\r\n\r\n";
		$postContent .= $fileContent . "\r\n";
		$postContent .= "--" . $boundary . "\r\n";

		$requestStr = 'file?method=upload&path=' . urlencode ( $targetPath . (empty ( $newFileName ) ? $fileName : $newFileName) ) . '&ondup='.$ondup.'&access_token=' . $this->_accessToken;
		
		if ($isCreateSuperFile === TRUE) {
			$requestStr .= '&type=tmpfile';
		}

		$result = $this->_baseControl ( $requestStr, $postContent, 'POST', array ('Content-Type' => 'multipart/form-data; boundary=' . $boundary ) );
		return $result;
	}

	/**
	 * 合并分片上传的文件块
	 * 注意：如果本地已有分片的文件块，可以调用upload接口按顺序上传之后，
	 * 再调用createSuperFile接口将各文件块合并成文件。（此方法一般适用于超大文件，>2G）
	 * @param string $targetPath 上传文件的目标保存路径
	 * @param string $fileName 文件名
	 * @param array $params 分片文件md5值数组
	 * @param string $newFileName 新文件名
	 * @return string
	 */
	public function createSuperFile($targetPath, $fileName, array $params, $newFileName = null,$ondup = 'newcopy') {
		$result = $this->_baseControl ( 'file?method=createsuperfile&path=' . urlencode ( $targetPath . (empty ( $newFileName ) ? $fileName : $newFileName) ) . '&ondup='.$ondup.'&access_token=' . $this->_accessToken, array ('param' => json_encode ( array ('block_list' => $params ) ) ), 'POST' );
		return $result;
	}

	/**
	 * 下载文件
	 * @param string $path 文件路径
	 * @return 文件内容
	 */
	public function download($path) {
		$result = $this->_baseControl ( 'file?method=download' . '&access_token=' . $this->_accessToken, array ('path' => $path ), 'GET' );
		return $result;
	}

	/**
	 * 创建文件夹
	 * @param string $path 文件路径
	 * @return string
	 */
	public function makeDirectory($path) {
		$result = $this->_baseControl ( 'file?method=mkdir' . '&access_token=' . $this->_accessToken, array ('path' => $path ), 'POST' );
		return $result;
	}

	/**
	 * 获取单个文件/目录meta信息
	 * @param string $path 文件路径
	 * @return string
	 */
	public function getMeta($path) {
		$result = $this->_baseControl ( 'file?method=meta' . '&access_token=' . $this->_accessToken, array ('path' => $path ) );
		return $result;
	}

	/**
	 * 批量获取文件/目录meta信息
	 * @param array $paths 文件路径数组
	 * @return string
	 */
	public function getBatchMeta(array $paths) {
		$list = array ();
		foreach ( $paths as $value ) {
			array_push ( $list, array ('path' => $value ) );
		}
		$list = array ('list' => $list );
		$list = json_encode ( $list );
		$result = $this->_baseControl ( 'file?method=meta' . '&access_token=' . $this->_accessToken, array ('param' => $list ), 'POST' );
		return $result;
	}

	/**
	 * 获取指定文件夹下的文件列表
	 * @param string $path 文件路径
	 * @param string $by 排序字段，缺省根据文件类型排序，time（修改时间），name（文件名），size（大小，注意目录无大小）
	 * @param string $order asc或desc，缺省采用降序排序
	 * @param string $limit 返回条目控制，参数格式为：n1-n2。返回结果集的[n1, n2)之间的条目，缺省返回所有条目。n1从0开始。
	 * @return string
	 */
	public function listFiles($path, $by = 'name', $order = 'asc', $limit = '0-9') {
		$result = $this->_baseControl ( 'file?method=list' . '&access_token=' . $this->_accessToken, array ('path' => $path, 'by' => $by, 'order' => $order, 'limit' => $limit ) );
		return $result;
	}

	/**
	 * 移动单个文件/目录
	 * @param string $from 源路径
	 * @param string $to 目标路径
	 * @return string
	 */
	public function moveSingle($from, $to) {
		$result = $this->_baseControl ( 'file?method=move' . '&access_token=' . $this->_accessToken, array ('from' => $from, 'to' => $to ), 'POST' );
		return $result;
	}

	/**
	 * 批量移动文件/目录
	 * @param array $from 源路径数组
	 * @param array $to 目标路径数组
	 * @return string
	 */
	public function moveBatch(array $from, array $to) {
		$list = array ();
		for($i = 0; $i < count ( $from ); $i ++) {
			array_push ( $list, array ('from' => $from [$i], 'to' => $to [$i] ) );
		}
		$list = array ('list' => $list );
		$list = json_encode ( $list );
		$result = $this->_baseControl ( 'file?method=move' . '&access_token=' . $this->_accessToken, array ('param' => $list ), 'POST' );
		return $result;
	}

	/**
	 * 拷贝单个文件/目录
	 * @param string $from 源路径
	 * @param string $to 目标路径
	 * @return string
	 */
	public function copySingle($from, $to) {
		$result = $this->_baseControl ( 'file?method=copy' . '&access_token=' . $this->_accessToken, array ('from' => $from, 'to' => $to ), 'POST' );
		return $result;
	}

	/**
	 * 批量拷贝文件/目录
	 * @param array $from 源路径数组
	 * @param array $to 目标路径数组
	 * @return string
	 */
	public function copyBatch(array $from, array $to) {
		$list = array ();
		for($i = 0; $i < count ( $from ); $i ++) {
			array_push ( $list, array ('from' => $from [$i], 'to' => $to [$i] ) );
		}
		$list = array ('list' => $list );
		$list = json_encode ( $list );

		$result = $this->_baseControl ( 'file?method=copy' . '&access_token=' . $this->_accessToken, array ('param' => $list ), 'POST' );
		return $result;
	}

	/**
	 * 删除单个文件/目录
	 * @param string $path 文件路径
	 * @return string
	 */
	public function deleteSingle($path) {
		$result = $this->_baseControl ( 'file?method=delete' . '&access_token=' . $this->_accessToken, array ('path' => $path ), 'POST' );
		return $result;
	}

	/**
	 * 批量删除文件/目录
	 * @param array $paths 文件路径数组
	 * @return string
	 */
	public function deleteBatch(array $paths) {
		$list = array ();
		foreach ( $paths as $value ) {
			array_push ( $list, array ('path' => $value ) );
		}
		$list = array ('list' => $list );
		$list = json_encode ( $list );

		$result = $this->_baseControl ( 'file?method=delete' . '&access_token=' . $this->_accessToken, array ('param' => $list ), 'POST' );
		return $result;
	}

	/**
	 * 按文件名搜索文件
	 * @param string $path 文件路径
	 * @param string $wd 搜索关键字
	 * @param int $re 是否递归
	 * @return string
	 */
	public function search($path, $wd, $re = 1) {
		$result = $this->_baseControl ( 'file?method=search' . '&access_token=' . $this->_accessToken, array ('path' => $path, 'wd' => $wd, 're' => $re ) );
		return $result;
	}

	/**
	 * 生成缩略图
	 * @param string $path 图片路径
	 * @param int $width
	 * @param int $height
	 * @param int32 $quality
	 * @return 文件内容
	 */
	public function thumbnail($path, $width, $height, $quality = 100) {
		$result = $this->_baseControl ( 'thumbnail?method=generate' . '&access_token=' . $this->_accessToken, array ('path' => $path, 'width' => $width, 'height' => $height, 'quality' => $quality ), 'GET' );
		return $result;
	}

	/**
	 * 文件增量更新操作查询
	 * @param string $cursor 用于标记更新断点。首次调用cursor=null；非首次调用，使用最后一次调用diff接口的返回结果中的cursor
	 * @return string
	 */
	public function diff($cursor) {
		$result = $this->_baseControl ( 'file?method=diff' . '&access_token=' . $this->_accessToken, array ('cursor' => $cursor ) );
		return $result;
	}

	/**
	 * 为当前用户下载一个流式文件
	 * @param string $path
	 * @return 文件内容
	 */
	public function downloadStream($path) {
		$result = $this->_baseControl ( 'stream?method=download' . '&access_token=' . $this->_accessToken, array ('path' => $path ) );
		return $result;
	}
/**
	 * 为当前用户获取流式文件地址
	 * @param string $path
	 * @return 文件内容
	 */
	public function getStreamUri($path) {
		$result = $this->_baseControl ( 'stream?method=download' . '&access_token=' . $this->_accessToken, array ('path' => $path ),'GET',array('returnurl'=>true) );
		return $result;
	}
	/* 为当前用户进行视频转码并实现在线实时观看
	 * @param string $path
	 * @return 文件内容
	 */
	public function getStreamingUri($path,$type) {
		$result = $this->_baseControl ( 'file?method=streaming' . '&access_token=' . $this->_accessToken, array ('path' => $path, 'type' => $type ),'GET',array('returnurl'=>true) );
		return $result;
	}
	/**
	 * 获取应用目录下所有流式文件列表
	 * @param string $type 取值为video，audio，image，doc四种
	 * @param string $start
	 * @param string $limit
	 * @param string $filterPath
	 * @return string
	 */
	public function listStream($type, $start = 0, $limit = '1000', $filterPath = '') {
		$result = $this->_baseControl ( 'stream?method=list' . '&access_token=' . $this->_accessToken, array ('type' => $type, 'start' => $start, 'limit' => $limit, 'filter_path' => $filterPath ) );
		return $result;
	}

	/**
	 * 为当前用户进行视频转码并实现在线实时观看
	 * @param string $path 格式必须为m3u8,m3u,asf,avi,flv,gif,mkv,mov,mp4,m4a,3gp,3g2,mj2,mpeg,ts,rm,rmvb,webm
	 * @param string $type M3U8_320_240、M3U8_480_224、M3U8_480_360、M3U8_640_480和M3U8_854_480
	 * @return 文件播放列表URL
	 */
	public function streaming($path, $type) {
		$result = $this->_baseControl ( 'file?method=streaming' . '&access_token=' . $this->_accessToken, array ('path' => $path, 'type' => $type ) );
		return $result;
	}

	/**
	 * 秒传一个文件
	 * 注意事项：
	 * 1. 被秒传文件必须大于256KB（即 256*1024 B）
	 * 2. 校验段为文件的前256KB，秒传接口需要提供待秒传文件CRC32，校验段的MD5
	 * @param string $path
	 * @param int $contentLength
	 * @param string $contentMd5
	 * @param string $sliceMd5
	 * @param string $contentCrc32
	 * @return string
	 */
	public function cloudMatch($path, $contentLength, $contentMd5, $sliceMd5, $contentCrc32) {
		$result = $this->_baseControl ( 'file?method=rapidupload' . '&access_token=' . $this->_accessToken, array ('path' => $path, 'content-length' => $contentLength, 'content-md5' => $contentMd5, 'slice-md5' => $sliceMd5, 'content-crc32' => $contentCrc32 ) );
		return $result;
	}

	/**
	 * 添加离线下载任务
	 * @param string $savePath 离线下载数据在PCS中存放的路径
	 * @param string $sourceUrl 要下载数据的URL
	 * @param int $rateLimit 下载速度， byte/s
	 * @param int $timeout 下载的超时时间
	 * @param string $callback 回调URL，回调过程不处理302跳转
	 * @param int $expires 请求失效时间
	 * @return string
	 */
	public function addOfflineDownloadTask($savePath, $sourceUrl, $rateLimit = '', $timeout = 3600, $callback='', $expires = ''){
		$result = $this->_baseControl ( 'services/cloud_dl?method=add_task' . '&access_token=' . $this->_accessToken, array ('save_path' => $savePath, 'source_url' => $sourceUrl, 'rate_limit' => $rateLimit, 'timeout' => $timeout, 'callback' =>$callback ), 'POST' );
		return $result;
	}

	/**
	 * 精确查询离线下载任务
	 * @param string $taskIds 要查询的task_id列表，如：'1,2,3,4'
	 * @param int $expires 请求失效时间
	 * @param int $opType 0：查任务信息，1：查进度信息
	 * @return string
	 */
	public function queryOfflineDownloadTask($taskIds, $opType = 1, $expires = ''){
		$result = $this->_baseControl ( 'services/cloud_dl?method=query_task' . '&access_token=' . $this->_accessToken, array ('task_ids' => $taskIds, 'op_type' => $opType) );
		return $result;
	}

	/**
	 * 查询离线下载任务列表
	 * @param int $start 起始位置
	 * @param int $limit 返回多少个
	 * @param int $asc 按开始时间升序 or 降序
	 * @param string $sourceURL 目标地址URL
	 * @param string $savePath 存放路径
	 * @param string $createTime STARTTIMESTMAP, ENDTIMESTAMP, 如果不限制下限可写成"NULL, 1235", 不限制上线，可写成'1234,NULL'
	 * @param int $status 任务状态过滤
	 * @param int $needTaskInfo 是否需要返回任务信息
	 * @param int $expires 请求失效时间
	 * @return string
	 */
	public function listOfflineDownloadTask($start = 0, $limit = 10, $asc = 0, $sourceURL = '', $savePath = '', $createTime = '', $status = 1, $needTaskInfo = 1, $expires = ''){
		$result = $this->_baseControl ( 'services/cloud_dl?method=list_task' . '&access_token=' . $this->_accessToken,
				array ('start' => $start, 'limit' => $limit, 'asc' => $asc, 'source_url' => $sourceURL,
						'save_path' => $savePath, 'create_time' => $createTime, 'status' => $status, 'need_task_info' =>$needTaskInfo), 'POST' );
		return $result;
	}

	/**
	 * 取消离线下载任务
	 * @param int $taskId 要取消的任务Id
	 * @param int $expires 请求失效时间
	 * @return string
	 */
	public function cancelOfflineDownloadTask($taskId, $expires = ''){
		$result = $this->_baseControl ( 'services/cloud_dl?method=cancel_task' . '&access_token=' . $this->_accessToken, array ('task_id' => $taskId) );
		return $result;
	}
}
?>