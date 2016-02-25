<?php

require_once("utils.php");
require_once("conf.php");

// ----------------------------------------------------------

class Qiniu_Mac {

	public $AccessKey;
	public $SecretKey;

	public function __construct($accessKey, $secretKey)
	{
		$this->AccessKey = $accessKey;
		$this->SecretKey = $secretKey;
	}

	public function Sign($data) // => $token
	{
		$sign = hash_hmac('sha1', $data, $this->SecretKey, true);
		return $this->AccessKey . ':' . Qiniu_Encode($sign);
	}

	public function SignWithData($data) // => $token
	{
		$data = Qiniu_Encode($data);
		return $this->Sign($data) . ':' . $data;
	}

	public function SignRequest($req, $incbody) // => ($token, $error)
	{
		$url = $req->URL;
		$url = parse_url($url['path']);
		$data = '';
		if (isset($url['path'])) {
			$data = $url['path'];
		}
		if (isset($url['query'])) {
			$data .= '?' . $url['query'];
		}
		$data .= "\n";

		if ($incbody) {
			$data .= $req->Body;
		}
		return $this->Sign($data);
	}

	public function VerifyCallback($auth, $url, $body) // ==> bool
	{
		$url = parse_url($url);
		$data = '';
		if (isset($url['path'])) {
			$data = $url['path'];
		}
		if (isset($url['query'])) {
			$data .= '?' . $url['query'];
		}
		$data .= "\n";

		$data .= $body;
		$token = 'QBox ' . $this->Sign($data);
		return $auth === $token;
	}
}

function Qiniu_SetKeys($accessKey, $secretKey)
{
	global $QINIU_ACCESS_KEY;
	global $QINIU_SECRET_KEY;

	$QINIU_ACCESS_KEY = $accessKey;
	$QINIU_SECRET_KEY = $secretKey;
}

function Qiniu_RequireMac($mac) // => $mac
{
	if (isset($mac)) {
		return $mac;
	}

	global $QINIU_ACCESS_KEY;
	global $QINIU_SECRET_KEY;

	return new Qiniu_Mac($QINIU_ACCESS_KEY, $QINIU_SECRET_KEY);
}

function Qiniu_Sign($mac, $data) // => $token
{
	return Qiniu_RequireMac($mac)->Sign($data);
}

function Qiniu_SignWithData($mac, $data) // => $token
{
	return Qiniu_RequireMac($mac)->SignWithData($data);
}

// ----------------------------------------------------------

