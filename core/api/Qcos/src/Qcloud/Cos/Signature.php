<?php

namespace Qcloud\Cos;

use Psr\Http\Message\RequestInterface;

class Signature {
    private $accessKey;           // string: access key.
    private $secretKey;           // string: secret key.
    public function __construct($accessKey, $secretKey, $token=null) {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->token = $token;
        date_default_timezone_set("PRC");
    }
    public function __destruct() {
    }
    public function signRequest(RequestInterface $request) {
        $authorization = $this->createAuthorization($request);
        return $request->withHeader('Authorization', $authorization);
    }
    public function createAuthorization(RequestInterface $request, $expires = "+30 minutes") {
        $signTime = (string)(time() - 60) . ';' . (string)(strtotime($expires));
        $httpString = strtolower($request->getMethod()) . "\n" . urldecode($request->getUri()->getPath()) .
            "\n\nhost=" . $request->getHeader("Host")[0]. "\n";
        $sha1edHttpString = sha1($httpString);
        $stringToSign = "sha1\n$signTime\n$sha1edHttpString\n";
        $signKey = hash_hmac('sha1', $signTime, $this->secretKey);
        $signature = hash_hmac('sha1', $stringToSign, $signKey);
        $authorization = 'q-sign-algorithm=sha1&q-ak='. $this->accessKey .
            "&q-sign-time=$signTime&q-key-time=$signTime&q-header-list=host&q-url-param-list=&" .
            "q-signature=$signature";
        return $authorization;
    }
    public function createPresignedUrl(RequestInterface $request, $expires = "+30 minutes") {
        $authorization = $this->createAuthorization($request, $expires);
        $uri = $request->getUri();
        $query = "sign=".urlencode($authorization);
        if ($this->token != null) {
            $query = $query."&x-cos-security-token=".$this->token;
        }
        $uri = $uri->withQuery($query);
        return $uri;
    }
}
