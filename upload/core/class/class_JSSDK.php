<?php
class JSSDK {
  private $appId;
  private $appSecret;
 private $qy;
  public function __construct($appId, $appSecret,$qy=1) {
    $this->appId = $appId;
    $this->appSecret = $appSecret;
	$this->qy=$qy;
  }

  public function getSignPackage() {
	 
    $jsapiTicket = $this->getJsApiTicket();
    // 注意 URL 一定要动态获取，不能 hardcode.
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    $timestamp = time();
    $nonceStr = $this->createNonceStr();

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

    $signature = sha1($string);

    $signPackage = array(
      "appId"     => $this->appId,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );
    return $signPackage; 
  }

  private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }

  private function getJsApiTicket() {
    // jsapi_ticket 应该全局存储与更新
	  if(loadcache('qy_jsapi_ticket') && ($ticket=authcode(getglobal('cache/qy_jsapi_ticket'),'DECODE',getglobal('setting/authkey')))){
			return $ticket;
	  }
	
      $accessToken = $this->getAccessToken();
      // 如果是企业号用以下 URL 获取 ticket
     if($this->qy) $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
     else $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
      $res = json_decode($this->httpGet($url));
      $ticket = $res->ticket;
      if ($ticket) {
		$expire = $res->expires_in ? intval($res->expires_in)-200 : 7000;
		savecache('qy_jsapi_ticket',authcode($ticket,'ENCODE',getglobal('setting/authkey'),$expire));
		return $ticket;
      }
     return false;
  }

  private function getAccessToken() {
    // access_token 应该全局存储与更新
	  if(loadcache('qy_access_token') && ($token=authcode(getglobal('cache/qy_access_token'),'DECODE',getglobal('setting/authkey')))){
			return $token;
	  }
      // 如果是企业号用以下URL获取access_token
	  if($this->qy) $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
      else $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
      $res = json_decode($this->httpGet($url));
	$access_token = $res->access_token;
      if ($access_token) {
		$expire = $res->expires_in ? intval($res->expires_in)-200 : 7000;
		//TODO: cache access_token
		savecache('qy_access_token',authcode($access_token,'ENCODE',getglobal('setting/authkey'),$expire)); 
      }
      return $access_token;
  }

  private function httpGet($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);

    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
  }
}

