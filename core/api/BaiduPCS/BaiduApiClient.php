<?php

/***************************************************************************
 *
 * Copyright (c) 2012 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

require_once(dirname(__FILE__) . '/BaiduUtils.php');
require_once(dirname(__FILE__) . '/BaiduException.php');

/**
 * Client for Baidu OpenAPI2.0 service.
 * 
 * @package Baidu
 * @author zhujianting(zhujianting@baidu.com)
 * @version v2.0.0
 */
class BaiduApiClient
{
	/**
	 * Scheme & domain for Baidu OpenAPI interfaces.
	 */
	public static $BD_OPENAPI_DEFAULT_DOMAINS = array(
		'public'	=> 'http://openapi.baidu.com',
		'rest'		=> 'https://openapi.baidu.com',
		'file'		=> 'https://openapi.baidu.com',
	);
	
    /**
     * URL prefixs for Baidu OpenAPI interfaces.
     */
    public static $BD_OPENAPI_DEFAULT_PREFIXS = array(
    	'public'	=> 'http://openapi.baidu.com/public/2.0/',
    	'rest'		=> 'https://openapi.baidu.com/rest/2.0/',
    	'file'		=> 'https://openapi.baidu.com/file/2.0/',
    );
    
	protected $clientId;
	protected $accessToken;
	
	/**
     * Charset of the app pages, default is UTF-8
     */
    protected $finalEncode = 'UTF-8';
    
    /**
     * Mode of batch/run api
     * @var int
     */
    protected $batchMode;
    
    /**
     * Array of api calls to be batch run.
     * @var array
     */
	protected $batchQueue = null;
	
	const BATCH_MODE_SERVER_PARALLEL = 0;
	const BATCH_MODE_SERIAL_ONLY = 1;
	
	/**
	 * Constructor
	 * 
	 * @param string $clientId Client_id of the baidu thirdparty app or access_key of the developer.
	 * @param string $accessToken Access token for api call.
	 */
	public function __construct($clientId, $accessToken)
	{
		$this->clientId = $clientId;
		$this->accessToken = $accessToken;
	}
	
	/**
	 * Get the client_id.
	 * 
	 * @return string
	 */
	public function getClientId()
	{
		return $this->clientId;
	}
	
	/**
	 * Set the client_id.
	 * 
	 * @param string $clientId Client_id of the baidu thirdparty app or access_key of the developer.
	 * @return BaiduApiClient
	 */
	public function setClientId($clientId)
	{
		$this->clientId = $clientId;
		return $this;
	}
	
	/**
	 * Get the access token for the following api calls.
	 * 
	 * @return string
	 */
	public function getAccessToken()
	{
		return $this->accessToken;
	}
	
	/**
	 * Set access token for the following api calls.
	 * 
	 * @param string $accessToken
	 * @return BaiduApiClient
	 */
	public function setAccessToken($accessToken)
	{
		$this->accessToken = $accessToken;
		return $this;
	}
	
	/**
     * Get the charset of the app.
     * 
     * @return string
     */
    public function getFinalEncode()
    {
    	return $this->finalEncode;
    }
    
    /**
     * Set the charset for the app.
     * 
     * @param string $finalEncode 'UTF-8' or 'GBK'
     * @return BaiduApiClient
     */
    public function setFinalEncode($finalEncode)
    {
    	$this->finalEncode = strtoupper($finalEncode);
    	return $this;
    }
    
    /**
     * Set the mode of batch/run api.
     * 
     * @param int $batchMode Use BaiduApiClient::BATCH_MODE_SERVER_PARALLEL
     * or BaiduApiClient::BATCH_MODE_SERIAL_ONLY
     * @return BaiduApiClient
     */
    public function setBatchMode($batchMode)
    {
    	$this->batchMode = $batchMode;
    	return $this;
    }
    
    /**
	 * Start a batch operation.
	**/
	public function beginBatch()
	{
		if ($this->batchQueue === null) {
			$this->batchQueue = array();
		}
	}

	/**
	 * End current batch operation
	**/
	public function end_batch()
	{
		if ($this->batchQueue !== null) {
			$this->batchRun();
			$this->batchQueue = null;
		}
	}
	
	/**
     * Call an api which is opened by Baidu, file upload apis should not 
     * be called by this interface.
     * 
     * @param string $uri Uri for the api, it could be the whole url,
     * like 'https://openapi.baidu.com/rest/2.0/passport/user/info/get',
     * or url path only, like '/rest/2.0/passport/user/info/get',
     * or just api method only, like 'passport/user/info/get'.
     * 
     * @param array $params	Api specific parameters.
     * @param string $httpMethod Http method, could be 'GET' or 'POST'.
     * @param string $type Type name of the openapi, could be 'rest', or 'public'.
     * @return array|false Returns an array if success, or false if failed.
     */
    public function & api($uri, $params = array(), $httpMethod = 'GET', $type = 'rest')
    {
    	if (substr($uri, 0, 8) === 'https://') {
    		//apis using https + access_token
    		$params = array_merge(array('access_token' => $this->getAccessToken()), $params);
    	} elseif (substr($uri, 0, 7) === 'http://') {
    		//apis using http + client_id
    		$params = array_merge(array('client_id' => $this->getClientId()), $params);
    	} else {
    		if (substr($uri, 0, 6) === '/rest/') {
    			//apis using https + access_token and default domain
    			$uri = self::$BD_OPENAPI_DEFAULT_DOMAINS['rest'] . $uri;
    			$params = array_merge(array('access_token' => $this->getAccessToken()), $params);
    		} elseif (substr($uri, 0, 8) === '/public/') {
    			//apis using http + client and default domain
    			$uri = self::$BD_OPENAPI_DEFAULT_DOMAINS['public'] . $uri;
    			$params = array_merge(array('client_id' => $this->getClientId()), $params);
    		} elseif ($type === 'rest') {
    			$uri = self::$BD_OPENAPI_DEFAULT_PREFIXS['rest'] . $uri;
    			$params = array_merge(array('access_token' => $this->getAccessToken()), $params);
    		} elseif ($type === 'public') {
    			$uri = self::$BD_OPENAPI_DEFAULT_PREFIXS['public'] . $uri;
    			$params = array_merge(array('client_id' => $this->getClientId()), $params);
    		} else {
    			BaiduUtils::setError(-1, 'Invalid params for ' . __METHOD__ . ": uri[$uri] type[$type]");
    			return false;
    		}
    	}
    	
    	if ($this->batchQueue === null) {
	    	$result = BaiduUtils::request($uri, $params, $httpMethod);
	    	if ($result !== false) {
	    		$result = $this->converJson2Array($result);
				if (is_array($result) && isset($result['error_code'])) {
					BaiduUtils::setError(-1, 'failed to call baidu openapi: error_code[' .
						$result['error_code'] . '] error_msg[' . $result['error_msg'] . ']');
					return false;
				}
	    	}
    	} else {
    		// batch run
    		$result = null;
    		unset($params['access_token']);
    		unset($params['client_id']);
    		$query = http_build_query($params, '', '&');
    		
    		$parts = parse_url($uri);
    		$item = array('domain' => $parts['host'],
    			'path' => $parts['path'],
    			'params' => $parts['query'] ? $parts['query'] . '&' . $query : $query,
    			'http_method' => $httpMethod);
    		if ($parts['scheme'] === 'https') {
    			$this->batchQueue[0][] = array('i' => $item, 'r' => & $result);
    		} else {
    			$this->batchQueue[1][] = array('i' => $item, 'r' => & $result);
    		}
    	}
    	return $result;
    }
    
    /**
     * Call a file upload api.
     * 
     * @param string $uri Uri for the api, it could be the whole url,
     * like 'https://openapi.baidu.com/file/2.0/cloudalbum/picture/upload',
     * or just api method only, like 'cloudalbum/picture/upload', if the api
     * is provided under the domain of openapi.baidu.com.
     * @param $params Api specific parameters.
     * @return Returns an array if success, or false if failed.
     */
    public function upload($uri, $params = array())
    {
    	$params = array_merge(array('access_token' => $this->getAccessToken()), $params);
    	
    	if (substr($uri, 0, 8) === 'https://' || substr($uri, 0, 7) === 'http://') {
    		//do nothing
    	} elseif (substr($uri, 0, 6) === '/file/') {
    		$uri = self::$BD_OPENAPI_DEFAULT_DOMAINS['file'] . $uri;
    	} else {
    		$uri = self::$BD_OPENAPI_DEFAULT_PREFIXS['file'] . $uri;
    	}
    	
    	$result = BaiduUtils::request($uri, $params, 'POST', true);
    	if ($result !== false) {
    		$result = $this->converJson2Array($result);
			if (is_array($result) && isset($result['error_code'])) {
				BaiduUtils::setError(-1, 'failed to call baidu openapi: error_code[' .
					$result['error_code'] . '] error_msg[' . $result['error_msg'] . ']');
				return false;
			}
    	}
    	return $result;
    }
    
	public static function iconv($var, $inCharset = 'UTF-8', $outCharset = 'GBK')
	{
		if (is_array($var)) {
			$rvar = array();
			foreach ($var as $key => $val) {
				$rvar[$key] = self::iconv($val, $inCharset, $outCharset);
			}
			return $rvar;
		} elseif (is_object($var)) {
			$rvar = null;
			foreach ($var as $key => $val) {
				$rvar->{$key} = self::iconv($val, $inCharset, $outCharset);
			}
			return $rvar;
		} elseif (is_string($var)) {
			return iconv($inCharset, $outCharset, $var);
		} else {
			return $var;
		}
	}
	
	private function batchRun()
	{
		$this->doBatchRun(true);
		$this->doBatchRun(false);
	}
	
	private function doBatchRun($useHttps = true)
	{
		$batchQueue = $this->batchQueue[$useHttps ? 0 : 1];
		
		if (empty($batchQueue)) {
			return;
		}
		
		$num = count($batchQueue);
		$params = array();
		foreach ($batchQueue as $item) {
			$params[] = $item['i'];
		}
		
		$json = json_encode($params);
		$serialOnly = ($this->batchMode === self::BATCH_MODE_SERIAL_ONLY);
		$params = array('method' => $json, 'serial_only' => $serialOnly);
		
		if ($useHttps) {
			$params['access_token'] = $this->getAccessToken();
			$domain = self::$BD_OPENAPI_DEFAULT_DOMAINS['rest'];
		} else {
			$params['client_id'] = $this->getClientId();
			$domain = self::$BD_OPENAPI_DEFAULT_DOMAINS['public'];
		}
		
		$result = BaiduUtils::request($domain . '/batch/run', $params, 'POST');
		if ($result === false) {
			throw new BaiduException('failed to call batch/run api: ' .
				BaiduUtils::errmsg(), BaiduUtils::errno());
		}
		
    	$result = $this->converJson2Array($result);
		if (is_array($result) && isset($result['error_code'])) {
			throw new BaiduException('failed to call batch/run api: ' .
				$result['error_msg'], $result['error_code']);
		}
		
		for ($i = 0; $i < $num; $i++) {
			$item = $batchQueue[$i];
			$itemResult = $result[$i];
			if (is_array($itemResult) && isset($itemResult['error_code'])) {
				throw new BaiduException('failed to call ' . $item['i']['path'] . ' api: ' .
					$itemResult['error_msg'], $itemResult['error_code']);
			}
			$item['r'] = $itemResult;
		}
	}
	
	private function converJson2Array($json)
	{
		$result = json_decode($json, true);
		if (strcasecmp($this->finalEncode, 'UTF-8') !== 0) {
			$result = self::iconv($result, 'UTF-8', $this->finalEncode);
		}

		return $result;
	}
}
 
/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */