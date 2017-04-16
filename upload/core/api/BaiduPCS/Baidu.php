<?php
/***************************************************************************
 *
 * Copyright (c) 2011 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

require_once( 'BaiduStore.php');
require_once( 'BaiduOAuth2.php');
require_once( 'BaiduApiClient.php');
require_once( 'BaiduUtils.php');

/**
 * 
 * @package Baidu
 * @author zhujianting(zhujianting@baidu.com)
 * @version v2.0.0
 */
class Baidu
{
	/**
	 * The client_id of the app or access_key of the developer.
	 * @var string
	 */
    protected $clientId;
    
    /**
     * The client_secret of the app or secret_key of the developer.
     * @var string
     */
    protected $clientSecret;
    
    /**
     * Redirect uri of the app, where we will redirect to after user authorization. 
     * @var string
     */
    protected $redirectUri;
    
    /**
     * Storage for the user session related datas, like state, authorization code,
     * access token and so on.
     * 
     * @var BaiduStore
     */
    protected $store = null;
    
    /**
     * @var string
     */
    protected $state = null;
    
    /**
     * User session info.
     * @var array
     */
    protected $session = null;
    
    /**
     * @var BaiduOAuth2
     */
    protected $oauth2 = null;
    
    /**
     * Constructor
     * 
     * @param string $clientId The client_id of the app or access_key of the developer.
     * @param string $clientSecret The client_secret of the app or secret_key of the developer.
     * @param string $redirectUri Redirect uri of the app.
     * @param BaiduStore $store Storage for the user session related datas.
     */
    public function __construct($clientId, $clientSecret, $redirectUri, $store = null)
    {
    	$this->clientId = $clientId;
    	$this->clientSecret = $clientSecret;
    	$this->redirectUri = $redirectUri;
    	$this->setStore($store ? $store : new BaiduCookieStore($clientId));
    }

	/**
	 * Get an instance of BaiduOAuth2 class.
	 * 
	 * @return BaiduOAuth2
	 */
	public function getBaiduOAuth2Service()
	{
		if (!$this->oauth2) {
			$this->oauth2 = new BaiduOAuth2($this->clientId, $this->clientSecret);
			$this->oauth2->setRedirectUri($this->redirectUri);
		}
		return $this->oauth2;
	}
	
	/**
	 * Get an instance of BaiduApiClient class.
	 * 
	 * @param string $accessToken Access token for api calls.
	 * @return BaiduApiClient
	 */
	public function getBaiduApiClientService()
	{
		return new BaiduApiClient($this->clientId, $this->getAccessToken());
	}
	
	/**
	 * Get access token for openapi calls.
	 * 
	 * @return string|false Returns access token if user has authorized the app, or false if not.
	 */
	public function getAccessToken()
	{
		$session = $this->getSession();
		if (isset($session['access_token'])) {
			return $session['access_token'];
		} else {
			return false;
		}
	}
	
	/**
	 * Get refresh token.
	 * 
	 * @return string|false Returns refresh token if app has, or false if not.
	 */
	public function getRefreshToken()
	{
		$session = $this->getSession();
		if (isset($session['refresh_token'])) {
			return $session['refresh_token'];
		} else {
			return false;
		}
	}
	
	/**
	 * Get currently logged in user's uid.
	 * 
	 * @return uint|false Return uid of the loggedin user, or return
	 * false if user isn't loggedin.
	 */
	public function getLoggedInUser()
	{
		// Get user from cached data or from access token
		$user = $this->getUser();
		
		// If there's bd_sig & bd_user parameter in query parameters,
		// it must be an inside web app(app on baidu) loading request,
		// then we must check whether the uid passed from baidu is the
		// same as we get from persistent data or from access token, 
		// if it's not, we should clear all the persistent data and to 
		// get an access token again.
		if (isset($_REQUEST['bd_sig']) && isset($_REQUEST['bd_user'])) {
			$params = array('bd_user' => $_REQUEST['bd_user']);
			$sig = BaiduUtils::generateSign($params, $this->clientSecret, 'bd_sig');
			if ($sig != $_REQUEST['bd_sig'] || $user['uid'] != $_REQUEST['bd_user']) {
				$this->store->remove('session');
				return false;
			}
		}
		
		return $user;
	}
	
	/**
	 * Get a Login URL for use with redirects. By default, full page redirect is
	 * assumed. If you are using the generated URL with a window.open() call in
	 * JavaScript, you can pass in display=popup as part of the $params.
	 *
	 * @param string $scope		blank space separated list of requested extended perms
	 * @param string $display	Authorization page style, 'page', 'popup', 'touch' or 'mobile'
	 * @return string the URL for the login flow
	 */
	public function getLoginUrl($scope = '', $display = 'page')
	{
		$oauth2 = $this->getBaiduOAuth2Service();
		return $oauth2->getAuthorizeUrl('code', $scope, $this->state, $display);
	}
	
	/**
	 * Get the Logout URL suitable for use with redirects.
	 * 
	 * @param string $next Url to go to after a successful logout.
	 * 
	 * @return string
	 */
	public function getLogoutUrl($next)
	{
		$oauth2 = $this->getBaiduOAuth2Service();
		return $oauth2->getLogoutUrl($this->getAccessToken(), $next);
	}
	
	/**
     * Get user session info.
     * 
     * @return array 
     */
	public function getSession()
	{
		if ($this->session === null) {
			$this->session = $this->doGetSession();
		}
		
		return $this->session;
	}
	
	/**
	 * Set user session.
	 * 
	 * @param array $session	User session info.
	 * @return Baidu
	 */
	public function setSession($session)
	{
		$this->session = $session;
		if ($session) {
			$this->store->set('session', $session);
		} else {
			$this->store->remove('session');
		}
		return $this;
	}
	
	/**
	 * Get current user's uid and uname.
	 * 
	 * @return array|false array('uid' => xx, 'uname' => xx)
	 */
	protected function getUser()
	{
		$session = $this->getSession();
		if (is_array($session) && isset($session['uid']) && isset($session['uname'])) {
			return array('uid' => $session['uid'], 'uname' => $session['uname']);
		} else {
			return false;
		}
	}
	
	/**
     * Set the session data storage instance.
     * 
     * @param BaiduStore $store
     * @return Baidu
     */
    protected function setStore($store)
    {
    	$this->store = $store;
    	if ($this->store) {
    		$state = $this->store->get('state');
    		if (!empty($state)) {
    			$this->state = $state;
    		}
    		//as the storage engine is changed, we need to get the session again.
    		$this->session = null;
    		$this->getSession();
    		$this->establishCSRFTokenState();
    	}
    	
    	return $this;
    }
	
	/**
	 * Get session info from Baidu server or from the store in app server side.
	 * 
	 * @return array|false
	 */
	protected function doGetSession()
	{
		// get authorization code from query parameters
		$code = $this->getCode();
		// check whether it is a CSRF attack request
		if ($code && $code != $this->store->get('code')) {
			$oauth2 = $this->getBaiduOAuth2Service();
			$session = $oauth2->getAccessTokenByAuthorizationCode($code);
			if ($session) {
				$this->store->set('code', $code);
				$this->setSession($session);
				$apiClient = new BaiduApiClient($this->clientId, $session['access_token']);
				$user = $apiClient->api('passport/users/getLoggedInUser');
				if ($user) {
					$session = array_merge($session, $user);
					$this->setSession($session);
				}
				return $session;
			}
			
			// code was bogus, so everything based on it should be invalidated.
			$this->store->removeAll();
			return false;
		}
		
		// as a fallback, just return whatever is in the storage
		$session = $this->store->get('session');
		$this->setSession($session);
		if ($session && !isset($session['uid'])) {
			$apiClient = new BaiduApiClient($this->clientId, $session['access_token']);
			$user = $apiClient->api('passport/users/getLoggedInUser');
			if ($user) {
				$session = array_merge($session, $user);
				$this->setSession($session);
			}
		}
		
		return $session;
	}

	/**
	 * Get the authorization code from the query parameters, if it exists,
	 * otherwise return false to signal no authorization code was discoverable.
	 *
	 * @return mixed Returns the authorization code, or false if the authorization
	 * code could not be determined.
	 */
	protected function getCode()
	{
		if (isset($_GET['code'])) {
			if ($this->state && $this->state === $_GET['state']) {
				// CSRF state has done its job, so clear it
				$this->state = null;
				$this->store->remove('state');
				return $_GET['code'];
			} else {
				BaiduUtils::errorLog('CSRF state token does not match one provided.');
				return false;
			}
		}
		
		return false;
	}

	/**
	 * Lays down a CSRF state token for this process.
	 *
	 * @return void
	 */
	protected function establishCSRFTokenState()
	{
		if ($this->state === null) {
			$this->state = md5(uniqid(mt_rand(), true));
			$this->store->set('state', $this->state);
		}
	}
}