cos-php-sdk-v5 Upgrade Guide
====================
2.0.6 to 2.0.7
----------
- Fix presigned url when using tmpSecretId/tmpSecretKey/Token

2.0.6 to 2.0.7
----------
- Fix response of `ListParts`

2.0.5 to 2.0.6
----------
- Support Domain
- Add Select Object Content Interface
- Add Traffic Limit
- Fix bug of object endswith /

2.0.4 to 2.0.5
----------
- Fix bug when upload object with metadata

2.0.3 to 2.0.4
----------
- Fix bug when using ip-port

2.0.2 to 2.0.3
----------
- Fix path parse bug with /0/

2.0.1 to 2.0.2
----------
- Fix bug of `putObject` with `fopen`
- Add ut


2.0.0 to 2.0.1
----------
- Add interface of inventory/tagging/logging
- Fix bug of some interface with query string


1.3 to 2.0
----------
cos-php-sdk-v5 now uses [GuzzleHttp] for HTTP message.
Due to fact, it depending on PHP >= 5.6.

- Use the `Qcloud\Cos\Client\getPresignetUrl()` method instead of the `Qcloud\Cos\Command\createPresignedUrl()`

v2:
```php
$signedUrl = $cosClient->getPresignetUrl($method='putObject',
                                         $args=['Bucket'=>'examplebucket-1250000000', 'Key'=>'exampleobject', 'Body'=>''],
                                         $expires='+30 minutes');
```

v1:
```php
$command = $cosClient->getCommand('putObject', array(
    'Bucket' => "examplebucket-1250000000",
    'Key' => "exampleobject",
    'Body' => '', 
));
$signedUrl = $command->createPresignedUrl('+30 minutes');
```

- `$copSource` parameters of the `Qcloud\Cos\Client\Copy` interface are no longer compatible with older versions.

v2:

```php
$result = $cosClient->copy( 
    $bucket = '<srcBucket>', 
    $Key = '<srcKey>', 
    $copySorce = array(
        'Region' => '<sourceRegion>', 
        'Bucket' => '<sourceBucket>', 
        'Key' => '<sourceKey>', 
    )
);
```

v1:
```php
$result = $cosClient->Copy(
    $bucket = '<srcBucket>',
    $key = '<srcKey>', 
    $copysource = '<sourceBucket>.cos.<sourceRegion>.myqcloud.com/<sourceKey>'
);
```
- Now when uploading files with using `open()` to upload stream, if the local file does not exist, a 0 byte file will be uploaded without throwing an exception, only a warning.

