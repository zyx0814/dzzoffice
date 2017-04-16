<?php

require_once("http.php");

// ----------------------------------------------------------
// class Qiniu_RS_GetPolicy

class Qiniu_RS_GetPolicy
{
	public $Expires;

	public function MakeRequest($baseUrl, $mac) // => $privateUrl
	{
		$deadline = $this->Expires;
		if ($deadline == 0) {
			$deadline = 3600;
		}
		$deadline += time();

		$pos = strpos($baseUrl, '?');
		if ($pos !== false) {
			$baseUrl .= '&e=';
		} else {
			$baseUrl .= '?e=';
		}
		$baseUrl .= $deadline;

		$token = Qiniu_Sign($mac, $baseUrl);
		return "$baseUrl&token=$token";
	}
}

function Qiniu_RS_MakeBaseUrl($domain, $key) // => $baseUrl
{
	$keyEsc = str_replace("%2F", "/", rawurlencode($key));
	return "http://$domain/$keyEsc";
}

// --------------------------------------------------------------------------------
// class Qiniu_RS_PutPolicy

class Qiniu_RS_PutPolicy
{
	public $Scope;                  //必填
	public $Expires;                //默认为3600s
	public $CallbackUrl;
	public $CallbackBody;
	public $ReturnUrl;
	public $ReturnBody;
	public $AsyncOps;
	public $EndUser;
	public $InsertOnly;             //若非0，则任何情况下无法覆盖上传
	public $DetectMime;             //若非0，则服务端根据内容自动确定MimeType
	public $FsizeLimit;
	public $SaveKey;
	public $PersistentOps;
	public $PersistentPipeline;
	public $PersistentNotifyUrl;
	public $FopTimeout;
	public $MimeLimit;

	public function __construct($scope)
	{
		$this->Scope = $scope;
	}

	public function Token($mac) // => $token
	{
		$deadline = $this->Expires;
		if ($deadline == 0) {
			$deadline = 3600;
		}
		$deadline += time();

		$policy = array('scope' => $this->Scope, 'deadline' => $deadline);
		if (!empty($this->CallbackUrl)) {
			$policy['callbackUrl'] = $this->CallbackUrl;
		}
		if (!empty($this->CallbackBody)) {
			$policy['callbackBody'] = $this->CallbackBody;
		}
		if (!empty($this->ReturnUrl)) {
			$policy['returnUrl'] = $this->ReturnUrl;
		}
		if (!empty($this->ReturnBody)) {
			$policy['returnBody'] = $this->ReturnBody;
		}
		if (!empty($this->AsyncOps)) {
			$policy['asyncOps'] = $this->AsyncOps;
		}
		if (!empty($this->EndUser)) {
			$policy['endUser'] = $this->EndUser;
		}
		if (!empty($this->InsertOnly)) {
			$policy['exclusive'] = $this->InsertOnly;
		}
		if (!empty($this->DetectMime)) {
			$policy['detectMime'] = $this->DetectMime;
		}
		if (!empty($this->FsizeLimit)) {
			$policy['fsizeLimit'] = $this->FsizeLimit;
		}
		if (!empty($this->SaveKey)) {
			$policy['saveKey'] = $this->SaveKey;
		}
		if (!empty($this->PersistentOps)) {
			$policy['persistentOps'] = $this->PersistentOps;
		}
		if (!empty($this->PersistentPipeline)) {
			$policy['persistentPipeline'] = $this->PersistentPipeline;
		}
		if (!empty($this->PersistentNotifyUrl)) {
			$policy['persistentNotifyUrl'] = $this->PersistentNotifyUrl;
		}
		if (!empty($this->FopTimeout)) {
			$policy['fopTimeout'] = $this->FopTimeout;
		}
		if (!empty($this->MimeLimit)) {
			$policy['mimeLimit'] = $this->MimeLimit;
		}


		$b = json_encode($policy);
		return Qiniu_SignWithData($mac, $b);
	}
}

// ----------------------------------------------------------
// class Qiniu_RS_EntryPath

class Qiniu_RS_EntryPath
{
	public $bucket;
	public $key;

	public function __construct($bucket, $key)
	{
		$this->bucket = $bucket;
		$this->key = $key;
	}
}

// ----------------------------------------------------------
// class Qiniu_RS_EntryPathPair

class Qiniu_RS_EntryPathPair
{
	public $src;
	public $dest;

	public function __construct($src, $dest)
	{
		$this->src = $src;
		$this->dest = $dest;
	}
}

// ----------------------------------------------------------

function Qiniu_RS_URIStat($bucket, $key)
{
	return '/stat/' . Qiniu_Encode("$bucket:$key");
}

function Qiniu_RS_URIDelete($bucket, $key)
{
	return '/delete/' . Qiniu_Encode("$bucket:$key");
}

function Qiniu_RS_URICopy($bucketSrc, $keySrc, $bucketDest, $keyDest)
{
	return '/copy/' . Qiniu_Encode("$bucketSrc:$keySrc") . '/' . Qiniu_Encode("$bucketDest:$keyDest");
}

function Qiniu_RS_URIMove($bucketSrc, $keySrc, $bucketDest, $keyDest)
{
	return '/move/' . Qiniu_Encode("$bucketSrc:$keySrc") . '/' . Qiniu_Encode("$bucketDest:$keyDest");
}

// ----------------------------------------------------------

function Qiniu_RS_Stat($self, $bucket, $key) // => ($statRet, $error)
{
	global $QINIU_RS_HOST;
	$uri = Qiniu_RS_URIStat($bucket, $key);
	return Qiniu_Client_Call($self, $QINIU_RS_HOST . $uri);
}

function Qiniu_RS_Delete($self, $bucket, $key) // => $error
{
	global $QINIU_RS_HOST;
	$uri = Qiniu_RS_URIDelete($bucket, $key);
	return Qiniu_Client_CallNoRet($self, $QINIU_RS_HOST . $uri);
}

function Qiniu_RS_Move($self, $bucketSrc, $keySrc, $bucketDest, $keyDest) // => $error
{
	global $QINIU_RS_HOST;
	$uri = Qiniu_RS_URIMove($bucketSrc, $keySrc, $bucketDest, $keyDest);
	return Qiniu_Client_CallNoRet($self, $QINIU_RS_HOST . $uri);
}

function Qiniu_RS_Copy($self, $bucketSrc, $keySrc, $bucketDest, $keyDest) // => $error
{
	global $QINIU_RS_HOST;
	$uri = Qiniu_RS_URICopy($bucketSrc, $keySrc, $bucketDest, $keyDest);
	return Qiniu_Client_CallNoRet($self, $QINIU_RS_HOST . $uri);
}

// ----------------------------------------------------------
// batch

function Qiniu_RS_Batch($self, $ops) // => ($data, $error)
{
	global $QINIU_RS_HOST;
	$url = $QINIU_RS_HOST . '/batch';
	$params = 'op=' . implode('&op=', $ops);
	return Qiniu_Client_CallWithForm($self, $url, $params);
}

function Qiniu_RS_BatchStat($self, $entryPaths)
{
	$params = array();
	foreach ($entryPaths as $entryPath) {
		$params[] = Qiniu_RS_URIStat($entryPath->bucket, $entryPath->key);
	}
	return Qiniu_RS_Batch($self,$params);
}

function Qiniu_RS_BatchDelete($self, $entryPaths)
{
	$params = array();
	foreach ($entryPaths as $entryPath) {
		$params[] = Qiniu_RS_URIDelete($entryPath->bucket, $entryPath->key);
	}
	return Qiniu_RS_Batch($self, $params);
}

function Qiniu_RS_BatchMove($self, $entryPairs)
{
	$params = array();
	foreach ($entryPairs as $entryPair) {
		$src = $entryPair->src;
		$dest = $entryPair->dest;
		$params[] = Qiniu_RS_URIMove($src->bucket, $src->key, $dest->bucket, $dest->key);
	}
	return Qiniu_RS_Batch($self, $params);
}

function Qiniu_RS_BatchCopy($self, $entryPairs)
{
	$params = array();
	foreach ($entryPairs as $entryPair) {
		$src = $entryPair->src;
		$dest = $entryPair->dest;
		$params[] = Qiniu_RS_URICopy($src->bucket, $src->key, $dest->bucket, $dest->key);
	}
	return Qiniu_RS_Batch($self, $params);
}

// ----------------------------------------------------------

