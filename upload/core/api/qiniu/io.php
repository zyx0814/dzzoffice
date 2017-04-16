<?php

require_once("http.php");
require_once("auth_digest.php");

// ----------------------------------------------------------
// class Qiniu_PutExtra

class Qiniu_PutExtra
{
	public $Params = null;
	public $MimeType = null;
	public $Crc32 = 0;
	public $CheckCrc = 0;
}

function Qiniu_Put($upToken, $key, $body, $putExtra) // => ($putRet, $err)
{
	global $QINIU_UP_HOST;

	if ($putExtra === null) {
		$putExtra = new Qiniu_PutExtra;
	}

	$fields = array('token' => $upToken);
	if ($key === null) {
		$fname = '?';
	} else {
		$fname = $key;
		$fields['key'] = $key;
	}
	if ($putExtra->CheckCrc) {
		$fields['crc32'] = $putExtra->Crc32;
	}
	if ($putExtra->Params) {
		foreach ($putExtra->Params as $k=>$v) {
			$fields[$k] = $v;
		}
	}

	$files = array(array('file', $fname, $body, $putExtra->MimeType));

	$client = new Qiniu_HttpClient;
	return Qiniu_Client_CallWithMultipartForm($client, $QINIU_UP_HOST, $fields, $files);
}

function createFile($filename, $mime)
{
    // PHP 5.5 introduced a CurlFile object that deprecates the old @filename syntax
    // See: https://wiki.php.net/rfc/curl-file-upload
    if (function_exists('curl_file_create')) {
        return curl_file_create($filename, $mime);
    }

    // Use the old style if using an older version of PHP
    $value = "@{$filename}";
    if (!empty($mime)) {
        $value .= ';type=' . $mime;
    }

    return $value;
}

function Qiniu_PutFile($upToken, $key, $localFile, $putExtra) // => ($putRet, $err)
{
	global $QINIU_UP_HOST;

	if ($putExtra === null) {
		$putExtra = new Qiniu_PutExtra;
	}

	$fields = array('token' => $upToken, 'file' => createFile($localFile, $putExtra->MimeType));
	if ($key === null) {
		$fname = '?';
	} else {
		$fname = $key;
		$fields['key'] = $key;
	}
	if ($putExtra->CheckCrc) {
		if ($putExtra->CheckCrc === 1) {
			$hash = hash_file('crc32b', $localFile);
			$array = unpack('N', pack('H*', $hash));
			$putExtra->Crc32 = $array[1];
		}
		$fields['crc32'] = sprintf('%u', $putExtra->Crc32);
	}
	if ($putExtra->Params) {
		foreach ($putExtra->Params as $k=>$v) {
			$fields[$k] = $v;
		}
	}

	$client = new Qiniu_HttpClient;
	return Qiniu_Client_CallWithForm($client, $QINIU_UP_HOST, $fields, 'multipart/form-data');
}

// ----------------------------------------------------------

