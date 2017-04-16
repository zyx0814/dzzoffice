<?php
/***************************************************************************
 *
 * Copyright (c) 2011 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

require_once(dirname(__FILE__) . '/BaiduUtils.php');

/**
 * Abstract class of storage engine for user session related data,
 * like state & authorization code for oauth2.0, access token &
 * refresh token for current user.
 * 
 * @package Baidu
 * @author zhujianting(zhujianting@baidu.com)
 * @version v2.0.0
 */
abstract class BaiduStore
{
	/**
	 * Supported variable key name.
	 * @var array
	 */
	protected static $supportedKeys = array(
		'state', 'code', 'session',
	);
	
	protected $clientId;
	
	public function __construct($clientId)
	{
		$this->clientId = $clientId;
	}
	
	/**
	 * Get the variable value specified by the variable key name for
	 * current session user from the storage system.
	 * 
	 * @param string $key Variable key name
	 * @param mix $default Default value if the key couldn't be found
	 * @return mix Returns the value for the specified key if it exists, 
	 * otherwise return $default value
	 */
	abstract public function get($key, $default = false);
	
	/**
	 * Save the variable item specified by the variable key name into
	 * the storage system for current session user.
	 * 
	 * @param string $key	Variable key name
	 * @param mix $value	Variable value
	 * @return bool Returns true if the saving operation is success,
	 * otherwise returns false
	 */
	abstract public function set($key, $value);
	
	/**
	 * Remove the stored variable item specified by the variable key name
	 * from the storage system for current session user.
	 * 
	 * @param string $key	Variable key name
	 * @return bool Returns true if remove success, otherwise returns false
	 */
	abstract public function remove($key);
	
	/**
	 * Remove all the stored variable items for current session user from
	 * the storage system.
	 * 
	 * @return bool Returns true if remove success, otherwise returns false
	 */
	abstract public function removeAll();
	
	/**
	 * Get the actual key name for current storage engine.
	 * 
	 * @param string $key The original key name
	 * @return string
	 */
	protected function getKeyForStore($key)
	{
		return implode('_', array('bds', $this->clientId, $key));
	}
}

/**
 * Storage engine using Browser Cookie.
 */
class BaiduCookieStore extends BaiduStore
{
	/**
	 * The domain where to save the session cookie.
	 * @var string
	 */
	protected $domain;
	
	/**
	 * Consturctor
	 * @param string $clientId App's client id.
	 * @param string $domain The domain where to save the session cookie.
	 */
	public function __construct($clientId, $domain = '')
	{
		parent::__construct($clientId);
		$this->domain = $domain;
		header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTR STP IND DEM"');
	}
	
	public function get($key, $default = false)
	{
		if (!in_array($key, self::$supportedKeys)) {
			return $default;
		}
		
		$name = $this->getKeyForStore($key);
		$value = $_COOKIE[$name];
		if ($value && $key == 'session') {
			parse_str($value, $value);
		}
		if (empty($value)) {
			$value = $default;
		}
		
		return $value;
	}
	
	public function set($key, $value)
	{
		if (!in_array($key, self::$supportedKeys)) {
			return false;
		}
		
		$name = $this->getKeyForStore($key);
		if ($key == 'session') {
			$expires = isset($value['expires_in']) ? $value['expires_in'] * 2 : 3600*24;
			$value = http_build_query($value, '', '&');
		} else {
			$expires = 3600*24;
		}
		
		setcookie($name, $value, time() + $expires, '/');
		$_COOKIE[$name] = $value;
		
		return true;
	}
	
	public function remove($key)
	{
		if (!in_array($key, self::$supportedKeys)) {
			return false;
		}
		
		$name = $this->getKeyForStore($key);
		setcookie($name, 'delete', time() - 3600*24, '/');
		unset($_COOKIE[$name]);
		
		return true;
	}
	
	public function removeAll()
	{
		foreach (self::$supportedKeys as $key) {
			$this->remove($key);
		}
		return true;
	}
}

/**
 * Storage engine using Session.
 */
class BaiduSessionStore extends BaiduStore
{
	public function __construct($clientId)
	{
		if (!session_id()) {
			session_start();
		}
		parent::__construct($clientId);
	}
	
	public function get($key, $default = false)
	{
		if (!in_array($key, self::$supportedKeys)) {
			return $default;
		}
		
		$name = $this->getKeyForStore($key);
		return isset($_SESSION[$name]) ? $_SESSION[$name] : $default;
	}
	
	public function set($key, $value)
	{
		if (!in_array($key, self::$supportedKeys)) {
			return false;
		}
		
		$name = $this->getKeyForStore($key);
		$_SESSION[$name] = $value;
		return true;
	}
	
	public function remove($key)
	{
		if (!in_array($key, self::$supportedKeys)) {
			return false;
		}
		
		$name = $this->getKeyForStore($key);
		unset($_SESSION[$name]);
		
		return true;
	}
	
	public function removeAll()
	{
		foreach (self::$supportedKeys as $key) {
			$this->remove($key);
		}
		return true;
	}
}

/**
 * Storage engine using memcached.
 */
class BaiduMemcachedStore extends BaiduStore
{
	/**
	 * Memcache instance
	 * @var Memcache
	 */
	protected $memcache;
	
	/**
	 * Session ID for current user to distinguish with other users.
	 * @var string
	 */
	protected $sessionId;
	
	/** 
	 * @param string $clientId
	 * @param Memcache $memcache
	 */
	public function __construct($clientId, $memcache, $sessionId)
	{
		$this->memcache = $memcache;
		$this->sessionId = $sessionId;
		
		parent::__construct($clientId);
	}
	
	public function get($key, $default = false)
	{
		if (!in_array($key, self::$supportedKeys)) {
			return $default;
		}
		
		$name = $this->getKeyForStore($key);
		$value = $this->memcache->get($name);
		return ($value === false) ? $default : $value;
	}
	
	public function set($key, $value)
	{
		if (!in_array($key, self::$supportedKeys)) {
			return false;
		}
		
		$name = $this->getKeyForStore($key);
		return $this->memcache->set($name, $value, 0, 0);
	}
	
	public function remove($key)
	{
		if (!in_array($key, self::$supportedKeys)) {
			return false;
		}
		
		$name = $this->getKeyForStore($key);
		return $this->memcache->delete($name);
	}
	
	public function removeAll()
	{
		foreach (self::$supportedKeys as $key) {
			$this->remove($key);
		}
		return true;
	}
	
	protected function getKeyForStore($key)
	{
		return implode('_', array('bds', $this->clientId, $this->sessionId, $key));
	}
}

/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */