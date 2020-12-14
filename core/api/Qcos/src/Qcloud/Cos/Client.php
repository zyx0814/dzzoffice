<?php

namespace Qcloud\Cos;

include("Common.php");

use Qcloud\Cos\Signature;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Guzzle\Deserializer;
use GuzzleHttp\Command\CommandInterface;
use GuzzleHttp\Command\Exception\CommandException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7;
use GuzzleHttp\Pool;


class Client extends GuzzleClient {
    const VERSION = '2.0.8';

    public $httpClient;
    
    private $api;
    private $desc;
    private $action;
    private $operation;
    private $cosConfig;
    private $signature;
    private $rawCosConfig;

    public function __construct($cosConfig) {
        $this->rawCosConfig = $cosConfig;
        $this->cosConfig['schema'] = isset($cosConfig['schema']) ? $cosConfig['schema'] : 'http';
        $this->cosConfig['region'] =  region_map($cosConfig['region']);
        $this->cosConfig['appId'] = isset($cosConfig['credentials']['appId']) ? $cosConfig['credentials']['appId'] : null;
        $this->cosConfig['secretId'] = isset($cosConfig['credentials']['secretId']) ? $cosConfig['credentials']['secretId'] : "";
        $this->cosConfig['secretKey'] = isset($cosConfig['credentials']['secretKey']) ? $cosConfig['credentials']['secretKey'] : "";
        $this->cosConfig['anonymous'] = isset($cosConfig['credentials']['anonymous']) ? $cosConfig['anonymous']['anonymous'] : false;
        $this->cosConfig['token'] = isset($cosConfig['credentials']['token']) ? $cosConfig['credentials']['token'] : null;
        $this->cosConfig['timeout'] = isset($cosConfig['timeout']) ? $cosConfig['timeout'] : 3600;
        $this->cosConfig['connect_timeout'] = isset($cosConfig['connect_timeout']) ? $cosConfig['connect_timeout'] : 3600;
        $this->cosConfig['ip'] = isset($cosConfig['ip']) ? $cosConfig['ip'] : null;
        $this->cosConfig['port'] = isset($cosConfig['port']) ? $cosConfig['port'] : null;
        $this->cosConfig['endpoint'] = isset($cosConfig['endpoint']) ? $cosConfig['endpoint'] : 'myqcloud.com';
        $this->cosConfig['domain'] = isset($cosConfig['domain']) ? $cosConfig['domain'] : null;
        $this->cosConfig['proxy'] = isset($cosConfig['proxy']) ? $cosConfig['proxy'] : null;
        $this->cosConfig['userAgent'] = isset($cosConfig['userAgent']) ? $cosConfig['userAgent'] : 'cos-php-sdk-v5.'. Client::VERSION;
        $this->cosConfig['pathStyle'] = isset($cosConfig['pathStyle']) ? $cosConfig['pathStyle'] : false;
        
        
        $service = Service::getService();
        $handler = HandlerStack::create();
		$handler->push(Middleware::mapRequest(function (RequestInterface $request) {
			return $request->withHeader('User-Agent', $this->cosConfig['userAgent']);
        }));
        if ($this->cosConfig['anonymous'] != true) {
            $handler->push($this::handleSignature($this->cosConfig['secretId'], $this->cosConfig['secretKey']));
        }
        if ($this->cosConfig['token'] != null) {
            $handler->push(Middleware::mapRequest(function (RequestInterface $request) {
                return $request->withHeader('x-cos-security-token', $this->cosConfig['token']);
            }));
        }
        $handler->push($this::handleErrors());
        $this->signature = new Signature($this->cosConfig['secretId'], $this->cosConfig['secretKey'], $this->cosConfig['token']);
        $this->httpClient = new HttpClient([
            'base_uri' => $this->cosConfig['schema'].'://cos.' . $this->cosConfig['region'] . '.myqcloud.com/',
            'timeout' => $this->cosConfig['timeout'],
            'handler' => $handler,
            'proxy' => $this->cosConfig['proxy'],
        ]);
        $this->desc = new Description($service);
        $this->api = (array)($this->desc->getOperations());
        parent::__construct($this->httpClient, $this->desc, [$this,
        'commandToRequestTransformer'], [$this, 'responseToResultTransformer'],
        null);
    }

    public function commandToRequestTransformer(CommandInterface $command)
    {
        $this->action = $command->GetName();
        $this->operation = $this->api[$this->action];
        $transformer = new CommandToRequestTransformer($this->cosConfig, $this->operation); 
        $seri = new Serializer($this->desc);
        $request = $seri($command);
        $request = $transformer->bucketStyleTransformer($command, $request);
        $request = $transformer->uploadBodyTransformer($command, $request);
        $request = $transformer->metadataTransformer($command, $request);
        $request = $transformer->md5Transformer($command, $request);
        $request = $transformer->specialParamTransformer($command, $request);
        return $request;
    }

    public function responseToResultTransformer(ResponseInterface $response, RequestInterface $request, CommandInterface $command)
    {
        $transformer = new ResultTransformer($this->cosConfig, $this->operation); 
        $transformer->writeDataToLocal($command, $request, $response);
        $deseri = new Deserializer($this->desc, true);
        $result = $deseri($response, $request, $command);

        $result = $transformer->metaDataTransformer($command, $response, $result);
        $result = $transformer->extraHeadersTransformer($command, $request, $result);
        $result = $transformer->selectContentTransformer($command, $result);
        return $result;
    }
    
    public function __destruct() {
    }

    public function __call($method, array $args) {
        try {
            return parent::__call(ucfirst($method), $args);
		} catch (CommandException $e) {
            $previous = $e->getPrevious();
			if ($previous !== null) {
				throw $previous;
			} else {
                throw $e;
            }
        }
    }

    public function getApi() {
        return $this->api;
    }

    private function getCosConfig() {
        return $this->cosConfig;
    }

    private function createPresignedUrl(RequestInterface $request, $expires) {
        return $this->signature->createPresignedUrl($request, $expires);
    }

    public function getPresignetUrl($method, $args, $expires = null) {
        $command = $this->getCommand($method, $args);
        $request = $this->commandToRequestTransformer($command);
        return $this->createPresignedUrl($request, $expires);
    }

    public function getObjectUrl($bucket, $key, $expires = null, array $args = array()) {
        $command = $this->getCommand('GetObject', $args + array('Bucket' => $bucket, 'Key' => $key));
        $request = $this->commandToRequestTransformer($command);
        return $this->createPresignedUrl($request, $expires)->__toString();
    }

    public function upload($bucket, $key, $body, $options = array()) {
        $body = Psr7\stream_for($body);
        $options['PartSize'] = isset($options['PartSize']) ? $options['PartSize'] : MultipartUpload::MIN_PART_SIZE;
        if ($body->getSize() < $options['PartSize']) {
            $rt = $this->putObject(array(
                    'Bucket' => $bucket,
                    'Key'    => $key,
                    'Body'   => $body,
                ) + $options);
        }
        else {
            $multipartUpload = new MultipartUpload($this, $body, array(
                    'Bucket' => $bucket,
                    'Key' => $key,
                ) + $options);

            $rt = $multipartUpload->performUploading();
        }
        return $rt;
    }

    public function resumeUpload($bucket, $key, $body, $uploadId, $options = array()) {
        $body = Psr7\stream_for($body);
        $options['PartSize'] = isset($options['PartSize']) ? $options['PartSize'] : MultipartUpload::DEFAULT_PART_SIZE;
        $multipartUpload = new MultipartUpload($this, $body, array(
                'Bucket' => $bucket,
                'Key' => $key,
                'UploadId' => $uploadId,
            ) + $options);

        $rt = $multipartUpload->resumeUploading();
        return $rt;
    }

    public function copy($bucket, $key, $copySource, $options = array()) {

        $options['PartSize'] = isset($options['PartSize']) ? $options['PartSize'] : Copy::DEFAULT_PART_SIZE;

        // set copysource client
        $sourceConfig = $this->rawCosConfig;
        $sourceConfig['region'] = $copySource['Region'];
        $cosSourceClient = new Client($sourceConfig);
        $copySource['VersionId'] = isset($copySource['VersionId']) ? $copySource['VersionId'] : "";
        try {
            $rt = $cosSourceClient->headObject(
                array('Bucket'=>$copySource['Bucket'],
                    'Key'=>$copySource['Key'],
                    'VersionId'=>$copySource['VersionId'],
                )
            );
        } catch (\Exception $e) {
            throw $e;
        }

        $contentLength =$rt['ContentLength'];
        // sample copy
        if ($contentLength < $options['PartSize']) {
            $rt = $this->copyObject(array(
                    'Bucket' => $bucket,
                    'Key'    => $key,
                    'CopySource'   => $copySource['Bucket']. '.cos.'. $copySource['Region'].
                                      ".myqcloud.com/". $copySource['Key']. "?versionId=". $copySource['VersionId'],
                ) + $options
            );
            return $rt;
        }
        // multi part copy
        $copySource['ContentLength'] = $contentLength;
        $copy = new Copy($this, $copySource, array(
                'Bucket' => $bucket,
                'Key'    => $key
            ) + $options
        );
        return $copy->copy();
    }

    public function doesBucketExist($bucket, array $options = array())
    {
        try {
            $this->HeadBucket(array(
                'Bucket' => $bucket));
            return True;
        } catch (\Exception $e){
            return False;
        }
    }

    public function doesObjectExist($bucket, $key, array $options = array())
    {
        try {
            $this->HeadObject(array(
                'Bucket' => $bucket,
                'Key' => $key));
            return True;
        } catch (\Exception $e){
            return False;
        }
    }
    
    public static function explodeKey($key) {
        // Remove a leading slash if one is found
        $split_key = explode('/', $key && $key[0] == '/' ? substr($key, 1) : $key);
        // Remove empty element
        $split_key = array_filter($split_key, function($var) {
            return !($var == '' || $var == null);
        });
        $final_key = implode("/", $split_key);
        if (substr($key, -1)  == '/') {
            $final_key = $final_key . '/';
        }
        return $final_key;
    }

    public static function handleSignature($secretId, $secretKey) {
            return function (callable $handler) use ($secretId, $secretKey) {
                    return new SignatureMiddleware($handler, $secretId, $secretKey);
            };
    }

    public static function handleErrors() {
            return function (callable $handler) {
                    return new ExceptionMiddleware($handler);
            };
    }
}
