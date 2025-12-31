<?php

require dirname(__FILE__) . '/../vendor/autoload.php';

$secretId = "COS_SECRETID"; //"云 API 密钥 SecretId";
$secretKey = "COS_SECRETKEY"; //"云 API 密钥 SecretKey";
$region = "ap-beijing"; //设置一个默认的存储桶地域
$cosClient = new Qcloud\Cos\Client([
    'region' => $region,
    'schema' => 'https', //协议头部，默认为http
    'credentials'=> [
        'secretId'  => $secretId ,
        'secretKey' => $secretKey
    ]
]);
try { 
    $result = $cosClient->selectObjectContent([
        'Bucket' => $bucket, //格式：BucketName-APPID
        'Key' => $key, 
        'Expression' => 'Select * from COSObject s', 
        'ExpressionType' => 'SQL', 
        'InputSerialization' => [
            'CompressionType' => 'None', 
            'CSV' => [
                'FileHeaderInfo' => 'NONE', 
                'RecordDelimiter' => '\n', 
                'FieldDelimiter' => ',', 
                'QuoteEscapeCharacter' => '"', 
                'Comments' => '#', 
                'AllowQuotedRecordDelimiter' => 'FALSE'
            ]
        ],
        'OutputSerialization' => [
            'CSV' => [
                'QuoteField' => 'ASNEEDED', 
                'RecordDelimiter' => '\n', 
                'FieldDelimiter' => ',', 
                'QuoteCharacter' => '"', 
                'QuoteEscapeCharacter' => '"'
            ]
        ],
        'RequestProgress' => [
                'Enabled' => 'FALSE'
        ]
    ]);
    // 请求成功
    foreach ($result['Data'] as $data) { 
        // 迭代遍历select结果
        print_r($data); 
    }
} catch (\Exception $e) {
    // 请求失败
    echo($e); 
}
