<?php

/*
 * A Client instance allows communication with the OneDrive API and perform
 * operations programmatically.
 *
 * For an overview of the OneDrive protocol flow, see here:
 * http://msdn.microsoft.com/en-us/library/live/hh243647.aspx
 *
 * To manage your Live Connect applications, see here:
 * https://account.live.com/developers/applications/index
 * Or here:
 * https://manage.dev.live.com/ (not working?)
 *
 * For an example implementation, see here:
 * https://github.com/drumaddict/skydrive-api-yii/blob/master/SkyDriveAPI.php
 */
// TODO: support refresh tokens: http://msdn.microsoft.com/en-us/library/live/hh243647.aspx
// TODO: pass parameters in POST request body when obtaining the access token
define('CLIENT_ID','');
define('CLIENT_SECRET','');
define('CALLBACK_URI','http://localhost/core/api/onedrive/example/auth.php');
class Client {
	// The base URL for API requests.
	//const API_URL_OneDrive   = 'https://apis.live.net/v5.0/';
	
	
	const API_URL   = 'https://api.onedrive.com/v1.0';

	// The base URL for authorization requests.
	const AUTH_URL  = 'https://login.live.com/oauth20_authorize.srf';

	// The base URL for token requests.
	const TOKEN_URL = 'https://login.live.com/oauth20_token.srf';

	// Client information.
	private $_clientId;

	// OAuth state (token, etc...).
	private $_state;

	// The last HTTP status received.
	private $_httpStatus;

	// The last Content-Type received.
	private $_contentType;

	/**
	 * Creates a base cURL object which is compatible with the OneDrive API.
	 *
	 * @param  (string) $path - The path of the API call (eg. me/skydrive).
	 * @param  (array) $options - Further curl options to set.
	 * @return (resource) A compatible cURL object.
	 */
	

	/**
	 * Constructor.
	 *
	 * @param  (array) $options. The options to use while creating this object.
	 *         The only supported key is 'state'. When defined, it should contain
	 *         a valid OneDrive client state, as returned by getState(). Default:
	 *         array().
	 */
	public function __construct(array $options = array()) {
		$this->_clientId = array_key_exists('client_id', $options)
			? (string) $options['client_id'] : CLIENT_ID;

		$this->_state = array_key_exists('state', $options)
			? $options['state'] :array(
				'redirect_uri' => '',
				'token'        => '',
				'expire_in'    => 0,
				'refreshtime'=>0
			);
	}

	/**
	 * Gets the current state of this Client instance. Typically saved in the
	 * session and passed back to the Client constructor for further requests.
	 *
	 * @return (object) The state of this Client instance.
	 */
	public function getState(){
		return $this->_state;
	}
	public function setState($arr){
		
		if(is_array($arr)) $this->_state=array_merge($this->_state,$arr);
	
		return $this->_state;
	}
	/**
	 * Gets the URL of the log in form. After login, the browser is redirected to
	 * the redirect URL, and a code is passed as a GET parameter to this URL.
	 *
	 * The browser is also redirected to this URL if the user is already logged
	 * in.
	 *
	 * @param  (array) $scopes - The OneDrive scopes requested by the application.
	 *         Supported values: 'wl.signin', 'wl.basic', 'wl.contacts_skydrive',
	 *         'wl.skydrive_update'.
	 * @param  (string) $redirectUri - The URI to which to redirect to upon
	 *         successful log in.
	 * @param  (array) $options. Reserved for future use. Default: array(). TODO:
	 *         support it.
	 * @return (string) The login URL.
	 */
	public function getLogInUrl(array $scopes, $redirectUri, array $options = array()) {
		if (null === $this->_clientId) {
			return array('error'=>'The client ID must be set to call getLoginUrl()');
		}

		$imploded    = implode(' ', $scopes);
		$redirectUri = $redirectUri?((string) $redirectUri):CALLBACK_URI;
		$this->_state['redirect_uri'] = $redirectUri;

		// When using this URL, the browser will eventually be redirected to the
		// callback URL with a code passed in the URL query string (the name of the
		// variable is "code"). This is suitable for PHP.
		$url = self::AUTH_URL
			. '?client_id=' . urlencode($this->_clientId)
			. '&scope=' . ($imploded)
			. '&response_type=code'
			. '&redirect_uri=' . urlencode($redirectUri)
			. '&display=popup'
			. '&locale=en';

		return $url;
	}

	/**
	 * Gets the access token expiration delay.
	 *
	 * @return (int) The token expiration delay, in seconds.
	 */
	public function getTokenExpire() {
		return $this->_state['refreshtime']
			+ $this->_state['expires_in'] - time();
	}

	/**
	 * Gets the status of the current access token.
	 *
	 * @return (int) The status of the current access token:
	 *          0 => no access token
	 *         -1 => access token will expire soon (1 minute or less)
	 *         -2 => access token is expired
	 *          1 => access token is valid
	 */
	public function getAccessTokenStatus() {
		if (null === $this->_state['access_token']) {
			return 0;
		}

		$remaining = $this->getTokenExpire();

		if (0 >= $remaining) {
			return -2;
		}

		if (60 >= $remaining) {
			return -1;
		}

		return 1;
	}

	/**
	 * Obtains a new access token from OAuth. This token is valid for one hour.
	 *
	 * @param  (string) $clientSecret - The OneDrive client secret.
	 * @param  (string) $code - The code returned by OneDrive after successful log
	 *         in.
	 * @param  (string) $redirectUri. Must be the same as the redirect URI passed
	 *         to getLoginUrl().
	 */
	public function obtainAccessToken($clientSecret, $code) {
		if (null === $this->_clientId) {
			return array('error'=>'The client ID must be set to call obtainAccessToken()');
		}

		if (null === $this->_state['redirect_uri']) {
			return array('error'=>'The state\'s redirect URI must be set to call obtainAccessToken()');
		}

		$url = self::TOKEN_URL
			. '?client_id=' . urlencode($this->_clientId)
			. '&redirect_uri=' . urlencode($this->_state['redirect_uri'])
			. '&client_secret=' . urlencode($clientSecret)
			. '&grant_type=authorization_code'
			. '&code=' . urlencode($code);

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL            => $url,
			// General options.
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_AUTOREFERER    => true,

			// SSL options.
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_TIMEOUT        => 15
			
		));
		
		$result = curl_exec($curl);
		curl_close($curl);
		$decoded = json_decode($result,true);

		if (null === $decoded) {
			return array('error'=>'json_decode() failed');
		}
		return  $this->_state =$decoded;
	}
	public function refreshAccessToken($clientSecret, $refresh_token) {
		if (null === $this->_clientId) {
			return array('error'=>'没有获取到 client ID');
		}
		if (!$refresh_token) {
			return array('error'=>'没有获取到refresh_token');
		}
		if(!$this->_state['redirect_uri']) $this->_state['redirect_uri'] = CALLBACK_URI;
		

		$url = self::TOKEN_URL
			. '?client_id=' . urlencode($this->_clientId)
			. '&redirect_uri=' . urlencode($this->_state['redirect_uri'])
			. '&client_secret=' . urlencode($clientSecret)
			. '&grant_type=refresh_token'
			. '&refresh_token=' . urlencode($refresh_token);
	
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL            => $url,
			// General options.
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_AUTOREFERER    => true,

			// SSL options.
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_TIMEOUT        => 15
			
		));
		
		$result = curl_exec($curl);
		curl_close($curl);
		$decoded = json_decode($result,true);

		if (null === $decoded) {
			return array('error'=>'json_decode() failed');
		}
		return $this->_state = $decoded;
		
	}
	/**
	 * Fetches the account info of the current OneDrive account.
	 *
	 * @return (object) An object with the following properties:
	 *           (string) id - OneDrive account ID.
	 *           (string) first_name - account owner's first name.
	 *           (string) last_name - account owner's last name.
	 *           (string) name - account owner's full name.
	 *           (string) gender - account owner's gender.
	 *           (string) locale - account owner's locale.
	 */
	public function fetchAccountInfo() {
		return $this->apiGet('/drive');
	}
	public function fetchChildren($path,$parameter) {
	
		return $this->apiGet('/drive/root:'.($path?$path:'/').':/children',array(),$parameter);
	}
	/**
	 * Fetches an object from the current OneDrive account.
	 *
	 * @param  (null|string) The unique ID of the OneDrive object to fetch, or
	 *         null to fetch the OneDrive root folder. Default: null.
	 * @return (Object) The object fetched, as an Object instance referencing to
	 *         the OneDrive object fetched.
	 */
	public function fetchObject($path='') {
		return $this->apiGet('/drive/root:'.($path?$path:'/'));
	}
	/**
	 * Updates the properties of an object in the current OneDrive account.
	 *
	 * @param  (string) $objectId - The unique ID of the object to update.
	 * @param  (array|object) $properties - The properties to update. Default:
	 *         array().
	 * @throw  (Exception) Thrown on I/O errors.
	 */
	public function updateObject($path, $properties = array()) {
		$path   = '/drive/root:'.($path?$path:'/');
		return $this->apiPatch($path, $properties);
	}
	//$type=='Crop' 剪裁图像;
	public function thumbnails($path,$width,$height,$type='') {
		$parameter=array('select'=>'c'.$width.'x'.$height.($type=='Crop'?'_Crop':''));
		return $this->apiGet('/drive/root:'.($path?$path:'/').':/thumbnails',array(),$parameter);
	}

	
	/**
	 * Creates a folder in the current OneDrive account.
	 *
	 * @param  (string) $name - The name of the OneDrive folder to be created.
	 * @param  (null|string) $parentId - The ID of the OneDrive folder into which
	 *         to create the OneDrive folder, or null to create it in the OneDrive
	 *         root folder. Default: null.
	 * @param  (null|string) $description - The description of the OneDrive folder to be
	 *         created, or null to create it without a description. Default: null.
	 * @return (Folder) The folder created, as a Folder instance referencing to
	 *         the OneDrive folder created.
	 */
	public function createFolder($path,$name,$ondup='fail') {
		if(!in_array((string)$ondup,array('rename','fail'))) $ondup='rename';
		
		if ('' === $path) {
			$path = '/drive/root/children';
		}else{
			$path = '/drive/root:'.$path.':/children';
		}

		$properties = array(
			'name' => (string)($name),
			'@name.conflictBehavior'=>(string)$ondup,
			'folder'=>(object)array()
			
		);
		return $this->apiPost($path, (object) $properties);
	}
		

	/**
	 * Creates a file in the current OneDrive account.
	 *
	 * @param  (string) $name - The name of the OneDrive file to be created.
	 * @param  (null|string) $parentId - The ID of the OneDrive folder into which
	 *         to create the OneDrive file, or null to create it in the OneDrive
	 *         root folder. Default: null.
	 * @param  (string) $content - The content of the OneDrive file to be created.
	 * @param  (string) $ondup - rename(the default), replace, and fail .
	 * @return (File) The file created, as File instance referencing to the
	 *         OneDrive file created.
	 * @throw  (Exception) Thrown on I/O errors.
	 */
	public function createFile( $path = null,$name, $content = '') {
		if (null === $path) {
			$path = '';
		}

		$stream = fopen('php://temp', 'w+b');

		if (false === $stream) {
			return array('error'=>'fopen() failed');
		}

		if (false === fwrite($stream, $content)) {
			fclose($stream);
			return array('error'=>'fwrite() failed');
		}

		if (!rewind($stream)) {
			fclose($stream);
			return array('error'=>'rewind() failed');
		}

		// TODO: some versions of cURL cannot PUT memory streams? See here for a
		// workaround: https://bugs.php.net/bug.php?id=43468
		$file = $this->apiPut('/drive/root:' .$path. '/' . urlencode(urldecode($name)).':/content', $stream);
		fclose($stream);
		return $file;
		//return new File($this, $file->id, $file);
	}
	public function uploadFile( $path = null,$name, $file) {
		if (null === $path) {
			$path = '';
		}

		if(!$stream = fopen($file, 'r+b')){
			return array('error'=>'打开文件失败');
		}
		// TODO: some versions of cURL cannot PUT memory streams? See here for a
		// workaround: https://bugs.php.net/bug.php?id=43468
		$ret = $this->apiPut('/drive/root:' .$path. '/' . urlencode(urldecode($name)).':/content', $stream);
		fclose($stream);
		return $ret;
		//return new File($this, $file->id, $file);
	}
	
	/**
	 * Fetches the root folder from the current OneDrive account.
	 *
	 * @return (Folder) The root folder, as a Folder instance referencing to the
	 *         OneDrive root folder.
	 */
	public function fetchRoot() {
		return $this->fetchObject();
	}

	
	
	public function createSession($path,$name,$ondup='rename') {
		if(!in_array((string)$ondup,array('rename','replace','fail'))) $ondup='rename';
		
		if ('' === $path) {
			$path = '/drive/root:/'.urlencode(urldecode($name)).':/upload.createSession';
		}else{
			$path = '/drive/root:'.$path.'/'.urlencode(urldecode($name)).':/upload.createSession';
		}

		$properties = array(
			'item'=>array(
					'@name.conflictBehavior'=>(string)$ondup,
				)
		);
		return $this->apiPost($path, (object) $properties);
	}
	
	public function cancelSession($uploadUrl) {
		$this->apiDelete($uploadUrl);
	}

	public function uploadFragment($uploadUrl,$file ,$contentRange) {
		
		$stream = @fopen($file, 'r+b');
		$ret = $this->apiPut($uploadUrl, $stream,array('Content-Range'=>$contentRange));
		@fclose($stream);
		return $ret;
		//return new File($this, $file->id, $file);
	}
	
	public function createLink($path,$type='view') {
		if(!in_array((string)$type,array('view','edit'))) $ondup='view';
		
		if ('' === $path) {
			$path = '/drive/root:/'.$path.':/action.createLink';
		}else{
			$path = '/drive/root:'.$path.':/action.createLink';
		}

		$properties = array(
			'type'=>$type
		);
		return $this->apiPost($path, (object) $properties);
	}
	
	/**
	 * Moves an object into another folder.
	 *
	 * @param  (string) The unique ID of the object to move.
	 * @param  (null|string) The unique ID of the folder into which to move the
	 *         object, or null to move it to the OneDrive root folder. Default:
	 *         null.
	 */
	public function moveObject($path, $tpath = null) {
		if (empty($path)) {
			$path = '/drive/root';
		}else{
			$path = '/drive/root:'.($path);
		}
		if (empty($tpath)) {
			$tpath = '/drive/root';
		}else{
			$tpath = '/drive/root:'.urldecode($tpath);
		}
		$this->apiPatch($path, (object) array(
			'parentReference' => array('path'=>$tpath),
		));
	}

	/**
	 * Copies a file into another folder. OneDrive does not support copying
	 * folders.
	 *
	 * @param  (string) The unique ID of the file to copy.
	 * @param  (null|string) The unique ID of the folder into which to copy the
	 *         file, or null to copy it to the OneDrive root folder. Default:
	 *         null.
	 */
	public function copyObject($path, $tpath = null) {
		if (empty($path)) {
			$path = '/drive/root/action.copy';
		}else{
			$path = '/drive/root:'.$path.':/action.copy';
		}
		if (empty($tpath)) {
			$tpath = '/drive/root';
		}else{
			$tpath = '/drive/root:'.$tpath;
		}
		
		$properties = array(
			'parentReference' => array('path'=>$tpath),
		);
		return $this->apiPost($path, (object) $properties);
	}

	/**
	 * Deletes an object in the current OneDrive account.
	 *
	 * @param  (string) $objectId - The unique ID of the object to delete.
	 */
	public function deleteObject($path) {
		if (empty($path)) {
			$path = '/drive/root';
		}else{
			$path = '/drive/root:'.$path;
		}
		$this->apiDelete($path);
	}

	
   

	/**
	 * Fetches the objects shared with the current OneDrive account.
	 *
	 * @return (object) An object with the following properties:
	 *           (array) data - The list of the shared objects.
	 */
	public function fetchShared() {
		return $this->apiGet('me/skydrive/shared');
	}
	private static function _createCurl($path, $options = array()) {
		$curl = curl_init();

		$default_options = array(
			// General options.
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_AUTOREFERER    => true,

			// SSL options.
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_TIMEOUT        => 1500
			
		);

		// See http://php.net/manual/en/function.array-merge.php for a description of the + operator (and why array_merge() would be wrong)
		$final_options = $options + $default_options;

		curl_setopt_array($curl, $final_options);

		return $curl;
	}
	/**
	 * Performs a call to the OneDrive API using the GET method.
	 *
	 * @param  (string) $path - The path of the API call (eg. me/skydrive).
	 * @param  (array) $options - Further curl options to set.
	 */
	public function apiGet($path, $options = array(),$data=array()) {
		$url = self::API_URL . $path
			. ($data?('?'.http_build_query($data).'&'):'?')
			. 'access_token=' . urlencode($this->_state['access_token']);
		$curl = self::_createCurl($path, $options);
		
		curl_setopt($curl, CURLOPT_URL, $url);
		//exit($url);
		return $this->_processResult($curl);
	}

	/**
	 * Performs a call to the OneDrive API using the POST method.
	 *
	 * @param  (string) $path - The path of the API call (eg. me/skydrive).
	 * @param  (array|object) $data - The data to pass in the body of the request.
	 */
	public function apiPost($path, $data) {
		$url  = self::API_URL . $path;
		$data = (object) $data;
		$curl = self::_createCurl($path);

		curl_setopt_array($curl, array(
			CURLOPT_URL        => $url,
			CURLOPT_POST       => true,

			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json', // The data is sent as JSON as per OneDrive documentation
				'Authorization: Bearer ' . $this->_state['access_token']
			),

			CURLOPT_POSTFIELDS => json_encode($data)
		));

		return $this->_processResult($curl);
	}

	/**
	 * Performs a call to the OneDrive API using the PUT method.
	 *
	 * @param  (string) $path - The path of the API call (eg. me/skydrive).
	 * @param  (resource) $stream - The data stream to upload.
	 * @param  (string) $contentType - The MIME type of the data stream, or null
	 *         if unknown. Default: null.
	 */
	public function apiPut($path, $stream, $extraheader = null) {
		$url   = (strpos($path,'https://')===false)?self::API_URL . $path:$path;
		$curl  = self::_createCurl($path);
		$stats = fstat($stream);
		$headers = array(
			'Authorization: Bearer ' . $this->_state['access_token']
		);
		foreach($extraheader as $key => $val){
			$headers[] = "$key: $val";
		}
		

		$options = array(
			CURLOPT_URL        => $url,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_PUT        => true,
			CURLOPT_INFILE     => $stream,
			CURLOPT_INFILESIZE => $stats[7] // Size
		);
		curl_setopt_array($curl, $options);
		return $this->_processResult($curl);
	}
	/**
	 * Performs a call to the OneDrive API using the PUT method.
	 *
	 * @param  (string) $path - The path of the API call (eg. me/skydrive).
	 * @param  (resource) $stream - The data stream to upload.
	 * @param  (string) $contentType - The MIME type of the data stream, or null
	 *         if unknown. Default: null.
	 */
	public function apiPatch($path, $data) {
		$url  = self::API_URL . $path;
		$data = (object) $data;
		$curl = self::_createCurl($path);

		curl_setopt_array($curl, array(
			CURLOPT_URL           => $url,
			CURLOPT_CUSTOMREQUEST => 'PATCH',

			CURLOPT_HTTPHEADER    => array(
				'Content-Type: application/json', // The data is sent as JSON as per OneDrive documentation
				'Authorization: Bearer ' . $this->_state['access_token']
			),

			CURLOPT_POSTFIELDS    => json_encode($data)
		));
		return $this->_processResult($curl);
	}
	/**
	 * Performs a call to the OneDrive API using the DELETE method.
	 *
	 * @param  (string) $path - The path of the API call (eg. me/skydrive).
	 */
	public function apiDelete($path) {
		$url = self::API_URL . $path
			. '?access_token=' . urlencode($this->_state['access_token']);

		$curl = self::_createCurl($path);

		curl_setopt_array($curl, array(
			CURLOPT_URL           => $url,
			CURLOPT_CUSTOMREQUEST => 'DELETE'
		));

		return $this->_processResult($curl);
	}

	/**
	 * Performs a call to the OneDrive API using the MOVE method.
	 *
	 * @param  (string) $path - The path of the API call (eg. me/skydrive).
	 * @param  (array|object) $data - The data to pass in the body of the request.
	 */
	public function apiMove($path, $data) {
		$url  = self::API_URL . $path;
		$data = (object) $data;
		$curl = self::_createCurl($path);

		curl_setopt_array($curl, array(
			CURLOPT_URL           => $url,
			CURLOPT_CUSTOMREQUEST => 'MOVE',

			CURLOPT_HTTPHEADER    => array(
				'Content-Type: application/json', // The data is sent as JSON as per OneDrive documentation
				'Authorization: Bearer ' . $this->_state['access_token']
			),

			CURLOPT_POSTFIELDS    => json_encode($data)
		));
		return $this->_processResult($curl);
	}

	/**
	 * Performs a call to the OneDrive API using the COPY method.
	 *
	 * @param  (string) $path - The path of the API call (eg. me/skydrive).
	 * @param  (array|object) $data - The data to pass in the body of the request.
	 */
	public function apiCopy($path, $data) {
		$url  = self::API_URL . $path;
		$data = (object) $data;
		$curl = self::_createCurl($path);

		curl_setopt_array($curl, array(
			CURLOPT_URL           => $url,
			CURLOPT_CUSTOMREQUEST => 'COPY',

			CURLOPT_HTTPHEADER    => array(
				'Content-Type: application/json', // The data is sent as JSON as per OneDrive documentation
				'Authorization: Bearer ' . $this->_state['access_token']
			),

			CURLOPT_POSTFIELDS    => json_encode($data)
		));

		return $this->_processResult($curl);
	}

	/**
	 * Processes a result returned by the OneDrive API call using a cURL object.
	 *
	 * @param  (resource) $curl - The cURL object used to perform the call.
	 * @return (object|string) The content returned, as an object instance if
	 *         served a JSON, or as a string if served as anything else.
	 */
	private function _processResult($curl) {
		$result = curl_exec($curl);

		if (false === $result) {
			return array('error'=>'curl_exec() failed: ' . curl_error($curl));
		}

		$info = curl_getinfo($curl);
		$this->_httpStatus = array_key_exists('http_code', $info) ?
			(int) $info['http_code'] : null;

		$this->_contentType = array_key_exists('content_type', $info) ?
			(string) $info['content_type'] : null;

		// Parse nothing but JSON.
		if (1 !== preg_match('|^application/json|', $this->_contentType)) {
			return $result;
		}
//print_r($info);exit($result);
		// Empty JSON string is returned as an empty object.
		if ('' == $result) {
			return  array();
		}
		
		$decoded = json_decode($result,true);
		if (isset($decoded['error'])) {
			return array('error'=> $decoded['error']['code'].':'.$decoded['error']['message'],'code'=>$decoded['error']['code']);
		}
		//print_r($info);exit($result);
		return $decoded;
	}
}
