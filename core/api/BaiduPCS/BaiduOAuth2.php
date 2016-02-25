<?php

/***************************************************************************
 *
 * Copyright (c) 2012 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

require_once(dirname(__FILE__) . '/BaiduUtils.php');

/**
 * Client for Baidu OAuth2.0 service.
 * 
 * @package Baidu
 * @author zhujianting(zhujianting@baidu.com)
 * @version v2.0.0
 */
class BaiduOAuth2
{	
    /**
     * Endpoints for Baidu OAuth2.0.
     */
    public static $BD_OAUTH2_ENDPOINTS = array(
    	'authorize'	=> 'https://openapi.baidu.com/oauth/2.0/authorize',
    	'token'		=> 'https://openapi.baidu.com/oauth/2.0/token',
    	'logout'	=> 'https://openapi.baidu.com/connect/2.0/logout',
		'getLoginUser'	=> 'https://openapi.baidu.com/rest/2.0/passport/users/getLoggedInUser',
    );
  
    protected $clientId='';
    protected $clientSecret='';
    protected $redirectUri;
    
    /**
     * Constructor
     * 
     * @param string $clientId Client_id of the baidu thirdparty app or access_key of the developer.
     * @param string $clientSecret Client_secret of the baidu thirdparty app or secret_key of the developer.
     */
    public function __construct($clientId='',$clientSecret='')
    {
    	if($clientId) $this->clientId = $clientId;
    	if($clientSecret) $this->clientSecret = $clientSecret;
    }
    
    /**
     * Set the redirect uri for the app.
     * 
     * @param $redirectUri Where to redirect after user authorization.
     * @return BaiduOAuth2
     */
    public function setRedirectUri($redirectUri)
    {
    	if (empty($redirectUri)) {
    		$redirectUri = BaiduUtils::getCurrentUrl();
    	}
    	$this->redirectUri = $redirectUri;
    	return $this;
    }
    
    /**
     * Get the redirect uri for the app.
     * 
     * @return string
     */
    public function getRedirectUri()
    {
    	return $this->redirectUri;
    }
	
	
	 public function getClientId()
    {
    	return $this->ClientId;
    }
	
	/**
	 * Get a Logout URL suitable for use with redirects.
	 * 
	 * @param string $accessToken Access token for current user
	 * @param string $next Url to go to after a successful logout
	 * @return String The URL for the logout flow
	 */
	public function getLogoutUrl($accessToken, $next = '')
	{
		$params = array('access_token' => $accessToken,
						'next' => $next ? $next : BaiduUtils::getCurrentUrl());
		return self::$BD_OAUTH2_ENDPOINTS['logout'] . '?' . http_build_query($params, '', '&');
	}
    
	/**
	 * Get baidu oauth2's authorization granting url.
	 * 
	 * @param string $responseType	Response type, 'code' or 'token'
	 * @param string $scope		blank space separated list of requested extended perms
	 * @param string $display	Authorization page style, 'page', 'popup', 'touch' or 'mobile'
	 * @param string $state		state parameter
	 * @return string Page url for authorization granting
	 */
	public function getAuthorizeUrl($responseType = 'code', $scope = '', $state = '', $display = 'popup')
	{		
		$params = array(
			'client_id'		=> $this->clientId,
			'response_type'	=> $responseType,
			'redirect_uri'	=> $this->redirectUri,
			'scope'			=> $scope,
			'state'			=> $state,
			'display'		=> $display,
		);
		return self::$BD_OAUTH2_ENDPOINTS['authorize'] . '?' . http_build_query($params, '', '&');
	}
	
	/**
	 * Get access token ifno by authorization code.
	 * 
	 * @param string $code	Authorization code
	 * @return array|false returns access token info if success, or false if failed
	 */
	public function getAccessTokenByAuthorizationCode($code)
	{
		$params = array(
			'grant_type'	=> 'authorization_code',
			'code'			=> $code,
			'client_id'		=> $this->clientId,
			'client_secret'	=> $this->clientSecret,
			'redirect_uri'	=> $this->redirectUri,
		);
		return $this->makeAccessTokenRequest($params);
	}
	
	/**
	 * Get access token info by client credentials.
	 * 
	 * @param string $scope		Extend permissions delimited by blank space
	 * @return array|false returns access token info if success, or false if failed.
	 */
	public function getAccessTokenByClientCredentials($scope = '')
	{
		$params = array(
			'grant_type'	=> 'client_credentials',
			'client_id'		=> $this->clientId,
			'client_secret'	=> $this->clientSecret,
			'scope'			=> $scope,
		);
		return $this->makeAccessTokenRequest($params);
	}
	
	/**
	 * Get access token info by developer credentials
	 * @param string $accessKey	Access key you got from baidu cloud platform
	 * @param string $secretKey	Secret key you got from baidu cloud platform
	 * @return array|false Returns access token info if success, or false if failed
	 */
	public function getAccessTokenByDeveloperCredentials($accessKey, $secretKey)
	{
		$params = array(
			'grant_type'	=> 'developer_credentials',
			'client_id'		=> $accessKey,
			'client_secret'	=> $secretKey,
		);
		return $this->makeAccessTokenRequest($params);
	}
	
	/**
	 * Refresh access token by refresh token.
	 * 
	 * @param string $refreshToken The refresh token
	 * @param string $scope	Extend permissions delimited by blank space
	 * @return array|false returns access token info if success, or false if failed.
	 */
	public function getAccessTokenByRefreshToken($refreshToken, $scope = '')
	{
		$params = array(
			'grant_type'	=> 'refresh_token',
			'refresh_token'	=> $refreshToken,
			'client_id'		=> $this->clientId,
			'client_secret'	=> $this->clientSecret,
			'scope'			=> $scope,
		);
		return $this->makeAccessTokenRequest($params);
	}
	
	/**
	 * Make an oauth access token request
	 * 
	 * The parameters:
	 * - client_id: The client identifier, just use api key
	 * - response_type: 'token' or 'code'
	 * - redirect_uri: the url to go to after a successful login
	 * - scope: The scope of the access request expressed as a list of space-delimited, case sensitive strings.
	 * - state: An opaque value used by the client to maintain state between the request and callback.
	 * - display: login page style, 'page', 'popup', 'touch' or 'mobile'
	 * 
	 * @param array $params	oauth request parameters
	 * @return mixed returns access token info if success, or false if failed
	 */
	
	public function makeAccessTokenRequest($params)
	{
		$result = BaiduUtils::request(self::$BD_OAUTH2_ENDPOINTS['token'], $params, 'POST');
		if ($result) {
			$result = json_decode($result, true);
			if (isset($result['error_description'])) {
				BaiduUtils::setError($result['error'], $result['error_description']);
				return false;
			}
			return $result;
		}
		
		return false;
	}
	public function getLoggedInUser($access_token)
	{
		$result = BaiduUtils::request(self::$BD_OAUTH2_ENDPOINTS['getLoginUser'], array('access_token'=>$access_token,'redirect_uri'	=> $this->redirectUri), 'GET');
		if ($result) {
			$result = json_decode($result, true);
			if (isset($result['error_msg'])) {
				BaiduUtils::setError($result['error_code'], $result['error_msg']);
				return false;
			}
			return $result;
		}
		
		return false;
	}
}

 
/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */