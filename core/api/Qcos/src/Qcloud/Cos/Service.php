<?php
namespace Qcloud\Cos;
// http://guzzle3.readthedocs.io/webservice-client/guzzle-service-descriptions.html
class Service {
    public static function getService() {
        return [
            'name' => 'Cos Service',
            'apiVersion' => 'V5',
            'description' => 'Cos V5 API Service',
            'operations' => [
                // 舍弃一个分块上传且删除已上传的分片块的方法.
                'AbortMultipartUpload' => [
                    'httpMethod' => 'DELETE',
                    'uri' => '/{Bucket}{/Key*}',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'AbortMultipartUploadOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri'],
                        'Key' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                            'minLength' => 1,
                            'filters' => [
                                'Qcloud\\Cos\\Client::explodeKey']],
                        'UploadId' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'uploadId'
                        ]
                    ]
                ],
                // 创建存储桶（Bucket）的方法.
                'CreateBucket' => [
                    'httpMethod' => 'PUT',
                    'uri' => '/{Bucket}',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'CreateBucketOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'CreateBucketConfiguration']],
                    'parameters' => [
                        'ACL' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-acl'],
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri'
                        ]
                    ]
                ],
                // 完成整个分块上传的方法.
                'CompleteMultipartUpload' => [
                    'httpMethod' => 'POST',
                    'uri' => '/{Bucket}{/Key*}',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'CompleteMultipartUploadOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'CompleteMultipartUpload'
                        ]
                    ],
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri'],
                        'Key' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                            'minLength' => 1,
                            'filters' => [
                                'Qcloud\\Cos\\Client::explodeKey'
                            ]
                        ],
                        'Parts' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'data' => [
                                'xmlFlattened' => true],
                            'items' => [
                                'name' => 'CompletedPart',
                                'type' => 'object',
                                'sentAs' => 'Part',
                                'properties' => [
                                    'ETag' => [
                                        'type' => 'string'
                                    ],
                                    'PartNumber' => [
                                        'type' => 'numeric'
                                    ]
                                ]
                            ]
                        ],
                        'UploadId' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'uploadId',
                        ],
                        'PicOperations' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Pic-Operations',
                        ]
                    ]
                ],
                // 初始化分块上传的方法.
                'CreateMultipartUpload' => [
                    'httpMethod' => 'POST',
                    'uri' => '/{Bucket}{/Key*}?uploads',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'CreateMultipartUploadOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'CreateMultipartUploadRequest'
                        ]
                    ],
                    'parameters' => [
                        'ACL' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-acl',
                        ],
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'CacheControl' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Cache-Control',
                        ],
                        'ContentDisposition' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Disposition',
                        ],
                        'ContentEncoding' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Encoding',
                        ],
                        'ContentLanguage' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Language',
                        ],
                        'ContentType' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Type',
                        ],
                        'Expires' => [
                            'type' => [
                                'object',
                                'string',
                                'integer',
                            ],
                            'format' => 'date-time-http',
                            'location' => 'header',
                        ],
                        'GrantFullControl' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-grant-full-control',
                        ],
                        'GrantRead' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-grant-read',
                        ],
                        'GrantReadACP' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-grant-read-acp',
                        ],
                        'GrantWriteACP' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-grant-write-acp',
                        ],
                        'Key' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                            'minLength' => 1,
                            'filters' => [
                                'Qcloud\\Cos\\Client::explodeKey'
                            ]
                        ],
                        'ServerSideEncryption' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption',
                        ],
                        'StorageClass' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-storage-class',
                        ],
                        'WebsiteRedirectLocation' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-website-redirect-location',
                        ],
                        'SSECustomerAlgorithm' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-algorithm',
                        ],
                        'SSECustomerKey' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-key',
                        ],
                        'SSECustomerKeyMD5' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-key-MD5',
                        ],
                        'SSEKMSKeyId' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-aws-kms-key-id',
                        ],
                        'RequestPayer' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-payer',
                        ],
                        'ACP' => [
                            'type' => 'object',
                            'additionalProperties' => true,
                        ],
                        'PicOperations' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Pic-Operations',
                        ]
                    ]
                ],
                // 复制对象的方法.
                'CopyObject' => [
                    'httpMethod' => 'PUT',
                    'uri' => '/{Bucket}{/Key*}',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'CopyObjectOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'CopyObjectRequest',
                        ],
                    ],
                    'parameters' => [
                        'ACL' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-acl',
                        ],
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'CacheControl' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Cache-Control',
                        ],
                        'ContentDisposition' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Disposition',
                        ],
                        'ContentEncoding' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Encoding',
                        ],
                        'ContentLanguage' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Language',
                        ],
                        'ContentType' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Type',
                        ],
                        'CopySource' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-copy-source',
                        ],
                        'CopySourceIfMatch' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-copy-source-if-match',
                        ],
                        'CopySourceIfModifiedSince' => [
                            'type' => [
                                'object',
                                'string',
                                'integer',
                            ],
                            'format' => 'date-time-http',
                            'location' => 'header',
                            'sentAs' => 'x-cos-copy-source-if-modified-since',
                        ],
                        'CopySourceIfNoneMatch' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-copy-source-if-none-match',
                        ],
                        'CopySourceIfUnmodifiedSince' => [
                            'type' => [
                                'object',
                                'string',
                                'integer',
                            ],
                            'format' => 'date-time-http',
                            'location' => 'header',
                            'sentAs' => 'x-cos-copy-source-if-unmodified-since',
                        ],
                        'Expires' => [
                            'type' => [
                                'object',
                                'string',
                                'integer',
                            ],
                            'format' => 'date-time-http',
                            'location' => 'header',
                        ],
                        'GrantFullControl' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-grant-full-control',
                        ],
                        'GrantRead' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-grant-read',
                        ],
                        'GrantReadACP' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-grant-read-acp',
                        ],
                        'GrantWriteACP' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-grant-write-acp',
                        ],
                        'Key' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                            'minLength' => 1,
                            'filters' => [
                                'Qcloud\\Cos\\Client::explodeKey']
                        ],
                        'MetadataDirective' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-metadata-directive',
                        ],
                        'ServerSideEncryption' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption',
                        ],
                        'StorageClass' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-storage-class',
                        ],
                        'WebsiteRedirectLocation' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-website-redirect-location',
                        ],
                        'SSECustomerAlgorithm' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-algorithm',
                        ],
                        'SSECustomerKey' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-key',
                        ],
                        'CopySourceSSECustomerAlgorithm' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-copy-source-server-side-encryption-customer-algorithm',
                        ],
                        'CopySourceSSECustomerKey' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-copy-source-server-side-encryption-customer-key',
                        ],
                        'CopySourceSSECustomerKeyMD5' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-copy-source-server-side-encryption-customer-key-MD5',
                        ],
                        'RequestPayer' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-payer',
                        ],
                        'ACP' => [
                            'type' => 'object',
                            'additionalProperties' => true,
                        ]
                    ],
                ],
                // 删除存储桶 (Bucket)的方法.
                'DeleteBucket' => [
                    'httpMethod' => 'DELETE',
                    'uri' => '/{Bucket}',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'DeleteBucketOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri'
                        ]
                    ]
                ],
                // 删除跨域访问配置信息的方法
                'DeleteBucketCors' => [
                    'httpMethod' => 'DELETE',
                    'uri' => '/{Bucket}?cors',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'DeleteBucketCorsOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                    ],
                ],
                // 删除存储桶标签信息的方法
                'DeleteBucketTagging' => [
                    'httpMethod' => 'DELETE',
                    'uri' => '/{Bucket}?tagging',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'DeleteBucketTaggingOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                    ],
                ],
                // 删除存储桶标清单任务的方法
                'DeleteBucketInventory' => [
                    'httpMethod' => 'Delete',
                    'uri' => '/{Bucket}?inventory',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'DeleteBucketInventoryOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'Id' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'id',
                        ]
                    ],
                ],
                // 删除 COS 上单个对象的方法.
                'DeleteObject' => [
                    'httpMethod' => 'DELETE',
                    'uri' => '/{Bucket}{/Key*}',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'DeleteObjectOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri'
                        ],
                        'Key' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                            'minLength' => 1,
                            'filters' => [
                                'Qcloud\\Cos\\Client::explodeKey'
                            ]
                        ],
                        'MFA' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-mfa',
                        ],
                        'VersionId' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'versionId',
                        ],
                        'RequestPayer' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-payer',
                        ]
                    ]
                ],
                // 批量删除 COS 对象的方法.
                'DeleteObjects' => [
                    'httpMethod' => 'POST',
                    'uri' => '/{Bucket}?delete',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'DeleteObjectsOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'Delete',
                        ],
                        'contentMd5' => true,
                    ],
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'Objects' => [
                            'required' => true,
                            'type' => 'array',
                            'location' => 'xml',
                            'data' => [
                                'xmlFlattened' => true,
                            ],
                            'items' => [
                                'type' => 'object',
                                'sentAs' => 'Object',
                                'properties' => [
                                    'Key' => [
                                        'required' => true,
                                        'type' => 'string',
                                        'minLength' => 1,
                                    ],
                                    'VersionId' => [
                                        'type' => 'string',
                                    ],
                                ],
                            ],
                        ],
                        'Quiet' => [
                            'type' => 'boolean',
                            'format' => 'boolean-string',
                            'location' => 'xml',
                        ],
                        'MFA' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-mfa',
                        ],
                        'RequestPayer' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-payer',
                        ]
                    ],
                ],
                // 删除存储桶（Bucket） 的website的方法.
                'DeleteBucketWebsite' => [
                    'httpMethod' => 'DELETE',
                    'uri' => '/{Bucket}?website',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'DeleteBucketWebsiteOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                    ],
                ],
                // 删除存储桶（Bucket） 的生命周期配置的方法.
                'DeleteBucketLifecycle' => [
                    'httpMethod' => 'DELETE',
                    'uri' => '/{Bucket}?lifecycle',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'DeleteBucketLifecycleOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                    ],
                ],
                // 删除跨区域复制配置的方法.
                'DeleteBucketReplication' => [
                    'httpMethod' => 'DELETE',
                    'uri' => '/{Bucket}?replication',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'DeleteBucketReplicationOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                    ],
                ],
                // 下载对象的方法.
                'GetObject' => [
                    'httpMethod' => 'GET',
                    'uri' => '/{Bucket}{/Key*}',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'GetObjectOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri'
                        ],
                        'IfMatch' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'If-Match'
                        ],
                        'IfModifiedSince' => [
                            'type' => [
                                'object',
                                'string',
                                'integer'
                            ],
                            'format' => 'date-time-http',
                            'location' => 'header',
                            'sentAs' => 'If-Modified-Since'
                        ],
                        'IfNoneMatch' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'If-None-Match'
                        ],
                        'IfUnmodifiedSince' => [
                            'type' => [
                                'object',
                                'string',
                                'integer'
                            ],
                            'format' => 'date-time-http',
                            'location' => 'header',
                            'sentAs' => 'If-Unmodified-Since'
                        ],
                        'Key' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                            'minLength' => 1,
                            'filters' => [
                                'Qcloud\\Cos\\Client::explodeKey'
                            ]
                        ],
                        'Range' => [
                            'type' => 'string',
                            'location' => 'header'],
                        'ResponseCacheControl' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'response-cache-control'
                        ],
                        'ResponseContentDisposition' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'response-content-disposition'
                        ],
                        'ResponseContentEncoding' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'response-content-encoding'
                        ],
                        'ResponseContentLanguage' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'response-content-language'
                        ],
                        'ResponseContentType' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'response-content-type'
                        ],
                        'ResponseExpires' => [
                            'type' => [
                                'object',
                                'string',
                                'integer'
                            ],
                            'format' => 'date-time-http',
                            'location' => 'query',
                            'sentAs' => 'response-expires'
                        ],
                        'VersionId' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'versionId',
                        ],
                        'SSECustomerAlgorithm' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-algorithm',
                        ],
                        'SSECustomerKey' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-key',
                        ],
                        'SSECustomerKeyMD5' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-key-MD5',
                        ],
                        'TrafficLimit' => [
                            'type' => 'integer',
                            'location' => 'header',
                            'sentAs' => 'x-cos-traffic-limit',
                        ]
                    ]
                ],
                // 获取 COS 对象的访问权限信息（Access Control List, ACL）的方法.
                'GetObjectAcl' => [
                    'httpMethod' => 'GET',
                    'uri' => '/{Bucket}{/Key*}?acl',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'GetObjectAclOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'Key' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                            'minLength' => 1,
                            'filters' => [
                                'Qcloud\\Cos\\Client::explodeKey']
                        ],
                        'VersionId' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'versionId',
                        ],
                        'RequestPayer' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-payer',
                        ]
                    ]
                ],
                // 获取存储桶（Bucket) 的访问权限信息（Access Control List, ACL）的方法.
                'GetBucketAcl' => [
                    'httpMethod' => 'GET',
                    'uri' => '/{Bucket}?acl',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'GetBucketAclOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri'
                        ]
                    ]
                ],
                // 查询存储桶（Bucket) 跨域访问配置信息的方法.
                'GetBucketCors' => [
                    'httpMethod' => 'GET',
                    'uri' => '/{Bucket}?cors',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'GetBucketCorsOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ]
                    ],
                ],
                // 查询存储桶（Bucket) Domain配置信息的方法.
                'GetBucketDomain' => [
                    'httpMethod' => 'GET',
                    'uri' => '/{Bucket}?domain',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'GetBucketDomainOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ]
                    ],
                ],
                // 查询存储桶（Bucket) Accelerate配置信息的方法.
                'GetBucketAccelerate' => [
                    'httpMethod' => 'GET',
                    'uri' => '/{Bucket}?accelerate',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'GetBucketAccelerateOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ]
                    ],
                ],
                // 查询存储桶（Bucket) Website配置信息的方法.
                'GetBucketWebsite' => [
                    'httpMethod' => 'GET',
                    'uri' => '/{Bucket}?website',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'GetBucketWebsiteOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ]
                    ],
                ],
                // 查询存储桶（Bucket) 的生命周期配置的方法.
                'GetBucketLifecycle' => [
                    'httpMethod' => 'GET',
                    'uri' => '/{Bucket}?lifecycle',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'GetBucketLifecycleOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ]
                    ],
                ],
                // 获取存储桶（Bucket）版本控制信息的方法.
                'GetBucketVersioning' => [
                    'httpMethod' => 'GET',
                    'uri' => '/{Bucket}?versioning',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'GetBucketVersioningOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ]
                    ],
                ],
                // 获取存储桶（Bucket) 跨区域复制配置信息的方法.
                'GetBucketReplication' => [
                    'httpMethod' => 'GET',
                    'uri' => '/{Bucket}?replication',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'GetBucketReplicationOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ]
                    ],
                ],
                // 获取存储桶（Bucket) 所在的地域信息的方法.
                'GetBucketLocation' => [
                    'httpMethod' => 'GET',
                    'uri' => '/{Bucket}?location',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'GetBucketLocationOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                    ],
                ],
                // 获取存储桶（Bucket) Notification信息的方法.
                'GetBucketNotification' => [
                    'httpMethod' => 'GET',
                    'uri' => '/{Bucket}?notification',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'GetBucketNotificationOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ]
                    ],
                ],
                // 获取存储桶（Bucket) 日志信息的方法.
                'GetBucketLogging' => [
                    'httpMethod' => 'GET',
                    'uri' => '/{Bucket}?logging',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'GetBucketLoggingOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ]
                    ],
                ],
                // 获取存储桶（Bucket) 清单信息的方法.
                'GetBucketInventory' => [
                    'httpMethod' => 'GET',
                    'uri' => '/{Bucket}?inventory',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'GetBucketInventoryOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'Id' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'id',
                        ]
                    ],
                ],
                // 获取存储桶（Bucket) 标签信息的方法.
                'GetBucketTagging' => [
                    'httpMethod' => 'GET',
                    'uri' => '/{Bucket}?tagging',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'GetBucketTaggingOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ]
                    ],
                ],
                // 分块上传的方法.
                'UploadPart' => [
                    'httpMethod' => 'PUT',
                    'uri' => '/{Bucket}{/Key*}',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'UploadPartOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'UploadPartRequest'
                        ]
                    ],
                    'parameters' => [
                        'Body' => [
                            'type' => [
                                'any'],
                            'location' => 'body'
                        ],
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri'
                        ],
                        'ContentLength' => [
                            'type' => 'numeric',
                            'minimum'=> 0,
                            'location' => 'header',
                            'sentAs' => 'Content-Length'
                        ],
                        'ContentMD5' => [
                            'type' => [
                                'string',
                                'boolean'
                            ],
                            'location' => 'header',
                            'sentAs' => 'Content-MD5'
                        ],
                        'Key' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                            'minLength' => 1,
                            'filters' => [
                                'Qcloud\\Cos\\Client::explodeKey'
                            ]
                        ],
                        'PartNumber' => [
                            'required' => true,
                            'type' => 'numeric',
                            'location' => 'query',
                            'sentAs' => 'partNumber'],
                        'UploadId' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'uploadId'],
                        'ServerSideEncryption' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption',
                        ],
                        'SSECustomerAlgorithm' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-algorithm',
                        ],
                        'SSECustomerKey' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-key',
                        ],
                        'SSECustomerKeyMD5' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-key-MD5',
                        ],
                        'RequestPayer' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-payer',
                        ],
                        'TrafficLimit' => [
                            'type' => 'integer',
                            'location' => 'header',
                            'sentAs' => 'x-cos-traffic-limit',
                        ]
                    ]
                ],
                // 上传对象的方法.
                'PutObject' => [
                    'httpMethod' => 'PUT',
                    'uri' => '/{Bucket}{/Key*}',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'PutObjectOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'PutObjectRequest'
                        ]
                    ],
                    'parameters' => [
                        'ACL' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-acl'
                        ],
                        'Body' => [
                            'required' => true,
                            'type' => [
                                'any'
                            ],
                            'location' => 'body'
                        ],
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri'
                        ],
                        'CacheControl' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Cache-Control'
                        ],
                        'ContentDisposition' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Disposition'
                        ],
                        'ContentEncoding' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Encoding'
                        ],
                        'ContentLanguage' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Language'
                        ],
                        'ContentLength' => [
                            'type' => 'numeric',
                            'minimum'=> 0,
                            'location' => 'header',
                            'sentAs' => 'Content-Length'
                        ],
                        'ContentMD5' => [
                            'type' => [
                                'string',
                                'boolean'
                            ],
                            'location' => 'header',
                            'sentAs' => 'Content-MD5'
                        ],
                        'ContentType' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Type'
                        ],
                        'Key' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                            'minLength' => 1,
                            'filters' => [
                                'Qcloud\\Cos\\Client::explodeKey'
                            ]
                        ],
                        'ServerSideEncryption' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption',
                        ],
                        'StorageClass' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-storage-class',
                        ],
                        'WebsiteRedirectLocation' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-website-redirect-location',
                        ],
                        'SSECustomerAlgorithm' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-algorithm',
                        ],
                        'SSECustomerKey' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-key',
                        ],
                        'SSECustomerKeyMD5' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-key-MD5',
                        ],
                        'SSEKMSKeyId' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-cos-kms-key-id',
                        ],
                        'RequestPayer' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-payer',
                        ],
                        'ACP' => [
                            'type' => 'object',
                            'additionalProperties' => true,
                        ],
                        'PicOperations' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Pic-Operations',
                        ],
                        'TrafficLimit' => [
                            'type' => 'integer',
                            'location' => 'header',
                            'sentAs' => 'x-cos-traffic-limit',
                        ]
                    ]
                ],
                // 设置 COS 对象的访问权限信息（Access Control List, ACL）的方法.
                'PutObjectAcl' => [
                    'httpMethod' => 'PUT',
                    'uri' => '/{Bucket}{/Key*}?acl',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'PutObjectAclOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'AccessControlPolicy',
                        ],
                    ],
                    'parameters' => [
                        'ACL' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-acl',
                        ],
                        'Grants' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'sentAs' => 'AccessControlList',
                            'items' => [
                                'name' => 'Grant',
                                'type' => 'object',
                                'properties' => [
                                    'Grantee' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'DisplayName' => [
                                                'type' => 'string'],
                                            'ID' => [
                                                'type' => 'string'],
                                            'Type' => [
                                                'type' => 'string',
                                                'sentAs' => 'xsi:type',
                                                'data' => [
                                                    'xmlAttribute' => true,
                                                    'xmlNamespace' => 'http://www.w3.org/2001/XMLSchema-instance']],
                                            'URI' => [
                                                'type' => 'string']]],
                                    'Permission' => [
                                        'type' => 'string',
                                    ],
                                ],
                            ],
                        ],
                        'Owner' => [
                            'type' => 'object',
                            'location' => 'xml',
                            'properties' => [
                                'DisplayName' => [
                                    'type' => 'string',
                                ],
                                'ID' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'GrantFullControl' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-grant-full-control',
                        ],
                        'GrantRead' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-grant-read',
                        ],
                        'GrantReadACP' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-grant-read-acp',
                        ],
                        'GrantWrite' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-grant-write',
                        ],
                        'GrantWriteACP' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-grant-write-acp',
                        ],
                        'Key' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                            'minLength' => 1,
                            'filters' => [
                                'Qcloud\\Cos\\Client::explodeKey']
                        ],
                        'RequestPayer' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-payer',
                        ],
                        'ACP' => [
                            'type' => 'object',
                            'additionalProperties' => true,
                        ],
                    ]
                ],
                // 设置存储桶（Bucket） 的访问权限（Access Control List, ACL)的方法.
                'PutBucketAcl' => [
                    'httpMethod' => 'PUT',
                    'uri' => '/{Bucket}?acl',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'PutBucketAclOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'AccessControlPolicy',
                        ],
                    ],
                    'parameters' => [
                        'ACL' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-acl',
                        ],
                        'Grants' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'sentAs' => 'AccessControlList',
                            'items' => [
                                'name' => 'Grant',
                                'type' => 'object',
                                'properties' => [
                                    'Grantee' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'DisplayName' => [
                                                'type' => 'string',
                                            ],
                                            'EmailAddress' => [
                                                'type' => 'string',
                                            ],
                                            'ID' => [
                                                'type' => 'string',
                                            ],
                                            'Type' => [
                                                'required' => true,
                                                'type' => 'string',
                                                'sentAs' => 'xsi:type',
                                                'data' => [
                                                    'xmlAttribute' => true,
                                                    'xmlNamespace' => 'http://www.w3.org/2001/XMLSchema-instance',
                                                ],
                                            ],
                                            'URI' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                    ],
                                    'Permission' => [
                                        'type' => 'string',
                                    ],
                                ],
                            ],
                        ],
                        'Owner' => [
                            'type' => 'object',
                            'location' => 'xml',
                            'properties' => [
                                'DisplayName' => [
                                    'type' => 'string',
                                ],
                                'ID' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'GrantFullControl' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-grant-full-control',
                        ],
                        'GrantRead' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-grant-read',
                        ],
                        'GrantReadACP' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-grant-read-acp',
                        ],
                        'GrantWrite' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-grant-write',
                        ],
                        'GrantWriteACP' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-grant-write-acp',
                        ],
                        'ACP' => [
                            'type' => 'object',
                            'additionalProperties' => true,
                        ],
                    ],
                ],
                // 设置存储桶（Bucket） 的跨域配置信息的方法.
                'PutBucketCors' => [
                    'httpMethod' => 'PUT',
                    'uri' => '/{Bucket}?cors',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'PutBucketCorsOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'CORSConfiguration',
                        ],
                        'contentMd5' => true,
                    ],
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'CORSRules' => [
                            'required' => true,
                            'type' => 'array',
                            'location' => 'xml',
                            'data' => [
                                'xmlFlattened' => true,
                            ],
                            'items' => [
                                'name' => 'CORSRule',
                                'type' => 'object',
                                'sentAs' => 'CORSRule',
                                'properties' => [
                                    'ID' => [
                                        'type' => 'string',
                                    ],
                                    'AllowedHeaders' => [
                                        'type' => 'array',
                                        'data' => [
                                            'xmlFlattened' => true,
                                        ],
                                        'items' => [
                                            'name' => 'AllowedHeader',
                                            'type' => 'string',
                                            'sentAs' => 'AllowedHeader',
                                        ],
                                    ],
                                    'AllowedMethods' => [
                                        'required' => true,
                                        'type' => 'array',
                                        'data' => [
                                            'xmlFlattened' => true,
                                        ],
                                        'items' => [
                                            'name' => 'AllowedMethod',
                                            'type' => 'string',
                                            'sentAs' => 'AllowedMethod',
                                        ],
                                    ],
                                    'AllowedOrigins' => [
                                        'required' => true,
                                        'type' => 'array',
                                        'data' => [
                                            'xmlFlattened' => true,
                                        ],
                                        'items' => [
                                            'name' => 'AllowedOrigin',
                                            'type' => 'string',
                                            'sentAs' => 'AllowedOrigin',
                                        ],
                                    ],
                                    'ExposeHeaders' => [
                                        'type' => 'array',
                                        'data' => [
                                            'xmlFlattened' => true,
                                        ],
                                        'items' => [
                                            'name' => 'ExposeHeader',
                                            'type' => 'string',
                                            'sentAs' => 'ExposeHeader',
                                        ],
                                    ],
                                    'MaxAgeSeconds' => [
                                        'type' => 'numeric',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // 设置存储桶（Bucket） 的Domain信息的方法.
                'PutBucketDomain' => [
                    'httpMethod' => 'PUT',
                    'uri' => '/{Bucket}?domain',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'PutBucketDomainOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'DomainConfiguration',
                        ],
                        'contentMd5' => true,
                    ],
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'DomainRules' => [
                            'required' => true,
                            'type' => 'array',
                            'location' => 'xml',
                            'data' => [
                                'xmlFlattened' => true,
                            ],
                            'items' => [
                                'name' => 'DomainRule',
                                'type' => 'object',
                                'sentAs' => 'DomainRule',
                                'properties' => [
                                    'Status' => [
                                        'required' => true,
                                        'type' => 'string',
                                    ],
                                    'Name' => [
                                        'required' => true,
                                        'type' => 'string',
                                    ],
                                    'Type' => [
                                        'required' => true,
                                        'type' => 'string',
                                    ],
                                    'ForcedReplacement' => [
                                        'type' => 'string',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // 设置存储桶（Bucket) 生命周期配置的方法.
                'PutBucketLifecycle' => [
                    'httpMethod' => 'PUT',
                    'uri' => '/{Bucket}?lifecycle',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'PutBucketLifecycleOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'LifecycleConfiguration',
                        ],
                        'contentMd5' => true,
                    ],
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'Rules' => [
                            'required' => true,
                            'type' => 'array',
                            'location' => 'xml',
                            'data' => [
                                'xmlFlattened' => true,
                            ],
                            'items' => [
                                'name' => 'Rule',
                                'type' => 'object',
                                'sentAs' => 'Rule',
                                'properties' => [
                                    'Expiration' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'Date' => [
                                                'type' => [
                                                    'object',
                                                    'string',
                                                    'integer',
                                                ],
                                                'format' => 'date-time',
                                            ],
                                            'Days' => [
                                                'type' => 'numeric',
                                            ],
                                        ],
                                    ],
                                    'ID' => [
                                        'type' => 'string',
                                    ],
                                    'Filter' => [
                                        'type' => 'object',
                                        'require' => true,
                                        'properties' => [
                                            'Prefix' => [
                                                'type' => 'string',
                                                'require' => true,
                                            ],
                                            'Tag' => [
                                                'type' => 'object',
                                                'require' => true,
                                                'properties' => [
                                                    'Key' => [
                                                        'type' => 'string'
                                                    ],
                                                    'filters' => [
                                                        'Qcloud\\Cos\\Client::explodeKey'],
                                                    'Value' => [
                                                        'type' => 'string'
                                                    ],
                                                ]
                                            ]
                                        ],
                                    ],
                                    'Status' => [
                                        'required' => true,
                                        'type' => 'string',
                                    ],
                                    'Transitions' => [
                                        'type' => 'array',
                                        'location' => 'xml',
                                        'data' => [
                                            'xmlFlattened' => true,
                                        ],
                                        'items' => [
                                            'name' => 'Transition',
                                            'type' => 'object',
                                            'sentAs' => 'Transition',
                                            'properties' => [
                                                'Date' => [
                                                    'type' => [
                                                        'object',
                                                        'string',
                                                        'integer',
                                                    ],
                                                    'format' => 'date-time',
                                                ],
                                                'Days' => [
                                                    'type' => 'numeric',
                                                ],
                                                'StorageClass' => [
                                                    'type' => 'string',
                                                ]]]],
                                    'NoncurrentVersionTransition' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'NoncurrentDays' => [
                                                'type' => 'numeric',
                                            ],
                                            'StorageClass' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                    ],
                                    'NoncurrentVersionExpiration' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'NoncurrentDays' => [
                                                'type' => 'numeric',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // 存储桶（Bucket）版本控制的方法.
                'PutBucketVersioning' => [
                    'httpMethod' => 'PUT',
                    'uri' => '/{Bucket}?versioning',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'PutBucketVersioningOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'VersioningConfiguration',
                        ],
                    ],
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'MFA' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-mfa',
                        ],
                        'MFADelete' => [
                            'type' => 'string',
                            'location' => 'xml',
                            'sentAs' => 'MfaDelete',
                        ],
                        'Status' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                    ],
                ],
                // 配置存储桶（Bucket) Accelerate的方法.
                'PutBucketAccelerate' => [
                    'httpMethod' => 'PUT',
                    'uri' => '/{Bucket}?accelerate',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'PutBucketAccelerateOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'AccelerateConfiguration',
                        ],
                        'xmlAllowEmpty' => true,
                    ],
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'Status' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'Type' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                    ],
                ],
                // 配置存储桶（Bucket) website的方法.
                'PutBucketWebsite' => [
                    'httpMethod' => 'PUT',
                    'uri' => '/{Bucket}?website',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'PutBucketWebsiteOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'WebsiteConfiguration',
                        ],
                        'xmlAllowEmpty' => true,
                    ],
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'ErrorDocument' => [
                            'type' => 'object',
                            'location' => 'xml',
                            'properties' => [
                                'Key' => [
                                    'type' => 'string',
                                    'minLength' => 1,
                                ],
                            ],
                        ],
                        'IndexDocument' => [
                            'required' => true,
                            'type' => 'object',
                            'location' => 'xml',
                            'properties' => [
                                'Suffix' => [
                                    'required' => true,
                                    'type' => 'string',
                                ],
                            ],
                        ],
                        'RedirectAllRequestsTo' => [
                            'type' => 'object',
                            'location' => 'xml',
                            'properties' => [
                                'HostName' => [
                                    'type' => 'string',
                                ],
                                'Protocol' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                        'RoutingRules' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'items' => [
                                'name' => 'RoutingRule',
                                'type' => 'object',
                                'properties' => [
                                    'Condition' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'HttpErrorCodeReturnedEquals' => [
                                                'type' => 'string',
                                            ],
                                            'KeyPrefixEquals' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                    ],
                                    'Redirect' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'HostName' => [
                                                'type' => 'string',
                                            ],
                                            'HttpRedirectCode' => [
                                                'type' => 'string',
                                            ],
                                            'Protocol' => [
                                                'type' => 'string',
                                            ],
                                            'ReplaceKeyPrefixWith' => [
                                                'type' => 'string',
                                            ],
                                            'ReplaceKeyWith' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // 配置存储桶（Bucket) 跨区域复制的方法.
                'PutBucketReplication' => [
                    'httpMethod' => 'PUT',
                    'uri' => '/{Bucket}?replication',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'PutBucketReplicationOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'ReplicationConfiguration',
                        ],
                        'contentMd5' => true,
                    ],
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'Role' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'Rules' => [
                            'required' => true,
                            'type' => 'array',
                            'location' => 'xml',
                            'data' => [
                                'xmlFlattened' => true,
                            ],
                            'items' => [
                                'name' => 'ReplicationRule',
                                'type' => 'object',
                                'sentAs' => 'Rule',
                                'properties' => [
                                    'ID' => [
                                        'type' => 'string',
                                    ],
                                    'Prefix' => [
                                        'required' => true,
                                        'type' => 'string',
                                    ],
                                    'Status' => [
                                        'required' => true,
                                        'type' => 'string',
                                    ],
                                    'Destination' => [
                                        'required' => true,
                                        'type' => 'object',
                                        'properties' => [
                                            'Bucket' => [
                                                'required' => true,
                                                'type' => 'string',
                                            ],
                                            'StorageClass' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // 设置存储桶（Bucket） 的回调设置的方法.
                'PutBucketNotification' => [
                    'httpMethod' => 'PUT',
                    'uri' => '/{Bucket}?notification',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'PutBucketNotificationOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'NotificationConfiguration',
                        ],
                    ],
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'CloudFunctionConfigurations' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'data' => [
                                'xmlFlattened' => true,
                            ],
                            'items' => [
                                'name' => 'CloudFunctionConfiguration',
                                'type' => 'object',
                                'sentAs' => 'CloudFunctionConfiguration',
                                'properties' => [
                                    'Id' => [
                                        'type' => 'string',
                                    ],
                                    'CloudFunction' => [
                                        'required' => true,
                                        'type' => 'string',
                                        'sentAs' => 'CloudFunction',
                                    ],
                                    'Events' => [
                                        'required' => true,
                                        'type' => 'array',
                                        'data' => [
                                            'xmlFlattened' => true,
                                        ],
                                        'items' => [
                                            'name' => 'Event',
                                            'type' => 'string',
                                            'sentAs' => 'Event',
                                        ],
                                    ],
                                    'Filter' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'Key' => [
                                                'type' => 'object',
                                                'sentAs' => 'Key',
                                                'properties' => [
                                                    'FilterRules' => [
                                                        'type' => 'array',
                                                        'data' => [
                                                            'xmlFlattened' => true,
                                                        ],
                                                        'items' => [
                                                            'name' => 'FilterRule',
                                                            'type' => 'object',
                                                            'sentAs' => 'FilterRule',
                                                            'properties' => [
                                                                'Name' => [
                                                                    'type' => 'string',
                                                                ],
                                                                'Value' => [
                                                                    'type' => 'string',
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                            'filters' => [
                                                'Qcloud\\Cos\\Client::explodeKey']
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // 配置存储桶（Bucket) 标签的方法.
                'PutBucketTagging' => [
                    'httpMethod' => 'PUT',
                    'uri' => '/{Bucket}?tagging',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'PutBucketTaggingOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'Tagging',
                        ],
                        'contentMd5' => true,
                    ],
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'TagSet' => [
                            'required' => true,
                            'type' => 'array',
                            'location' => 'xml',
                            'items' => [
                                'name' => 'TagRule',
                                'type' => 'object',
                                'sentAs' => 'Tag',
                                'properties' => [
                                    'Key' => [
                                        'required' => true,
                                        'type' => 'string',
                                    ],
                                    'Value' => [
                                        'required' => true,
                                        'type' => 'string',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                //开启存储桶（Bucket) 日志服务的方法.
                'PutBucketLogging' => [
                    'httpMethod' => 'PUT',
                    'uri' => '/{Bucket}?logging',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'PutBucketLoggingOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'BucketLoggingStatus',
                        ],
                        'contentMd5' => true,
                    ],
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'LoggingEnabled' => [
                            'location' => 'xml',
                            'type' => 'object',
                            'properties' => [
                                'TargetBucket' => [
                                    'type' => 'string',
                                    'location' => 'xml',
                                ],
                                'TargetPrefix' => [
                                    'type' => 'string',
                                    'location' => 'xml',
                                ],
                            ]
                        ],
                    ],
                ],
                // 配置存储桶（Bucket) 清单的方法.
                'PutBucketInventory' => [
                    'httpMethod' => 'PUT',
                    'uri' => '/{Bucket}?inventory',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'PutBucketInventoryOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'InventoryConfiguration',
                        ],
                        'contentMd5' => true,
                    ],
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'Id' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'IsEnabled' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'Destination' => [
                            'type' => 'object',
                            'location' => 'xml',
                            'properties' => [
                                'COSBucketDestination'=> [
                                    'type' => 'object',
                                    'properties' => [
                                        'Format' => [
                                            'type' => 'string',
                                            'require' => true,
                                        ],
                                        'AccountId' => [
                                            'type' => 'string',
                                            'require' => true,
                                        ],
                                        'Bucket' => [
                                            'type' => 'string',
                                            'require' => true,
                                        ],
                                        'Prefix' => [
                                            'type' => 'string',
                                        ],
                                        'Encryption' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'SSE-COS' => [
                                                    'type' => 'string',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'Schedule' => [
                            'required' => true,
                            'type' => 'object',
                            'location' => 'xml',
                            'properties' => [
                                'Frequency' => [
                                    'type' => 'string',
                                    'require' => true,
                                ],
                            ]
                        ],
                        'Filter' => [
                            'type' => 'object',
                            'location' => 'xml',
                            'properties' => [
                                'Prefix' => [
                                    'type' => 'string',
                                ],
                            ]
                        ],
                        'IncludedObjectVersions' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'OptionalFields' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'items' => [
                                'name' => 'Fields',
                                'type' => 'string',
                                'sentAs' => 'Field',
                            ],
                        ],
                    ],
                ],
                // 回热归档对象的方法.
                'RestoreObject' => [
                    'httpMethod' => 'POST',
                    'uri' => '/{Bucket}{/Key*}?restore',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'RestoreObjectOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'RestoreRequest',
                        ],
                    ],
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'Key' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                            'minLength' => 1,
                            'filters' => [
                                'Qcloud\\Cos\\Client::explodeKey']
                        ],
                        'VersionId' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'versionId',
                        ],
                        'Days' => [
                            'required' => true,
                            'type' => 'numeric',
                            'location' => 'xml',
                        ],
                        'CASJobParameters' => [
                            'type' => 'object',
                            'location' => 'xml',
                            'properties' => [
                                'Tier' => [
                                    'type' => 'string',
                                    'required' => true,
                                ],
                            ],
                        ],
                        'RequestPayer' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-payer',
                        ],
                    ],
                ],
                // 查询存储桶（Bucket）中正在进行中的分块上传对象的方法.
                'ListParts' => [
                    'httpMethod' => 'GET',
                    'uri' => '/{Bucket}{/Key*}',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'ListPartsOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri'
                        ],
                        'Key' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                            'minLength' => 1,
                            'filters' => [
                                'Qcloud\\Cos\\Client::explodeKey'
                            ]
                        ],
                        'MaxParts' => [
                            'type' => 'numeric',
                            'location' => 'query',
                            'sentAs' => 'max-parts'],
                        'PartNumberMarker' => [
                            'type' => 'numeric',
                            'location' => 'query',
                            'sentAs' => 'part-number-marker'
                        ],
                        'UploadId' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'uploadId'
                        ]
                    ]
                ],
                // 查询存储桶（Bucket) 下的部分或者全部对象的方法.
                'ListObjects' => [
                    'httpMethod' => 'GET',
                    'uri' => '/{Bucket}',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'ListObjectsOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri'
                        ],
                        'Delimiter' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'delimiter'
                        ],
                        'EncodingType' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'encoding-type'
                        ],
                        'Marker' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'marker'
                        ],
                        'MaxKeys' => [
                            'type' => 'numeric',
                            'location' => 'query',
                            'sentAs' => 'max-keys'
                        ],
                        'Prefix' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'prefix'
                        ]
                    ]
                ],
                // 获取所属账户的所有存储空间列表的方法.
                'ListBuckets' => [
                    'httpMethod' => 'GET',
                    'uri' => '/',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'ListBucketsOutput',
                    'responseType' => 'model',
                    'parameters' => [
                    ],
                ],
                // 获取多版本对象的方法.
                'ListObjectVersions' => [
                    'httpMethod' => 'GET',
                    'uri' => '/{Bucket}?versions',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'ListObjectVersionsOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'Delimiter' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'delimiter',
                        ],
                        'EncodingType' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'encoding-type',
                        ],
                        'KeyMarker' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'key-marker',
                        ],
                        'MaxKeys' => [
                            'type' => 'numeric',
                            'location' => 'query',
                            'sentAs' => 'max-keys',
                        ],
                        'Prefix' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'prefix',
                        ],
                        'VersionIdMarker' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'version-id-marker',
                        ]
                    ],
                ],
                // 获取已上传分块列表的方法
                'ListMultipartUploads' => [
                    'httpMethod' => 'GET',
                    'uri' => '/{Bucket}?uploads',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'ListMultipartUploadsOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'Delimiter' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'delimiter',
                        ],
                        'EncodingType' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'encoding-type',
                        ],
                        'KeyMarker' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'key-marker',
                        ],
                        'MaxUploads' => [
                            'type' => 'numeric',
                            'location' => 'query',
                            'sentAs' => 'max-uploads',
                        ],
                        'Prefix' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'prefix',
                        ],
                        'UploadIdMarker' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'upload-id-marker',
                        ]
                    ],
                ],
                // 获取清单列表的方法.
                'ListBucketInventoryConfigurations' => [
                    'httpMethod' => 'GET',
                    'uri' => '/{Bucket}?inventory',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'ListBucketInventoryConfigurationsOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri'
                        ],
                        'ContinuationToken' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'continuation-token',
                        ],
                    ],
                ],
                // 获取对象的meta信息的方法
                'HeadObject' => [
                    'httpMethod' => 'HEAD',
                    'uri' => '/{Bucket}{/Key*}',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'HeadObjectOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'IfMatch' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'If-Match',
                        ],
                        'IfModifiedSince' => [
                            'type' => [
                                'object',
                                'string',
                                'integer',
                            ],
                            'format' => 'date-time-http',
                            'location' => 'header',
                            'sentAs' => 'If-Modified-Since',
                        ],
                        'IfNoneMatch' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'If-None-Match',
                        ],
                        'IfUnmodifiedSince' => [
                            'type' => [
                                'object',
                                'string',
                                'integer',
                            ],
                            'format' => 'date-time-http',
                            'location' => 'header',
                            'sentAs' => 'If-Unmodified-Since',
                        ],
                        'Key' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                            'minLength' => 1,
                            'filters' => [
                                'Qcloud\\Cos\\Client::explodeKey']
                        ],
                        'Range' => [
                            'type' => 'string',
                            'location' => 'header',
                        ],
                        'VersionId' => [
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'versionId',
                        ],
                        'SSECustomerAlgorithm' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-algorithm',
                        ],
                        'SSECustomerKey' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-key',
                        ],
                        'SSECustomerKeyMD5' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-key-MD5',
                        ],
                        'RequestPayer' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-payer',
                        ],
                    ]
                ],
                // 存储桶（Bucket） 是否存在的方法.
                'HeadBucket' => [
                    'httpMethod' => 'HEAD',
                    'uri' => '/{Bucket}',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'HeadBucketOutput',
                    'responseType' => 'model',
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                    ]
                ],
                // 分块copy的方法.
                'UploadPartCopy' => [
                    'httpMethod' => 'PUT',
                    'uri' => '/{Bucket}{/Key*}',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'UploadPartCopyOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'UploadPartCopyRequest',
                        ],
                    ],
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'CopySource' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-copy-source',
                        ],
                        'CopySourceIfMatch' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-copy-source-if-match',
                        ],
                        'CopySourceIfModifiedSince' => [
                            'type' => [
                                'object',
                                'string',
                                'integer',
                            ],
                            'format' => 'date-time-http',
                            'location' => 'header',
                            'sentAs' => 'x-cos-copy-source-if-modified-since',
                        ],
                        'CopySourceIfNoneMatch' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-copy-source-if-none-match',
                        ],
                        'CopySourceIfUnmodifiedSince' => [
                            'type' => [
                                'object',
                                'string',
                                'integer',
                            ],
                            'format' => 'date-time-http',
                            'location' => 'header',
                            'sentAs' => 'x-cos-copy-source-if-unmodified-since',
                        ],
                        'CopySourceRange' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-copy-source-range',
                        ],
                        'Key' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                            'minLength' => 1,
                            'filters' => [
                                'Qcloud\\Cos\\Client::explodeKey']
                        ],
                        'PartNumber' => [
                            'required' => true,
                            'type' => 'numeric',
                            'location' => 'query',
                            'sentAs' => 'partNumber',
                        ],
                        'UploadId' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'query',
                            'sentAs' => 'uploadId',
                        ],
                        'SSECustomerAlgorithm' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-algorithm',
                        ],
                        'SSECustomerKey' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-key',
                        ],
                        'SSECustomerKeyMD5' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-key-MD5',
                        ],
                        'CopySourceSSECustomerAlgorithm' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-copy-source-server-side-encryption-customer-algorithm',
                        ],
                        'CopySourceSSECustomerKey' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-copy-source-server-side-encryption-customer-key',
                        ],
                        'CopySourceSSECustomerKeyMD5' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-copy-source-server-side-encryption-customer-key-MD5',
                        ],
                        'RequestPayer' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-payer',
                        ]
                    ],
                ],
                'SelectObjectContent' => [
                    'httpMethod' => 'Post',
                    'uri' => '/{/Key*}?select&select-type=2',
                    'class' => 'Qcloud\\Cos\\Command',
                    'responseClass' => 'SelectObjectContentOutput',
                    'responseType' => 'model',
                    'data' => [
                        'xmlRoot' => [
                            'name' => 'SelectRequest',
                        ],
                        'contentMd5' => true,
                    ],
                    'parameters' => [
                        'Bucket' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                        ],
                        'Key' => [
                            'required' => true,
                            'type' => 'string',
                            'location' => 'uri',
                            'minLength' => 1,
                            'filters' => [
                                'Qcloud\\Cos\\Client::explodeKey']
                        ],
                        'Expression' => [
                            'type' => 'string',
                            'location' => 'xml'
                        ],
                        'ExpressionType' => [
                            'type' => 'string',
                            'location' => 'xml'
                        ],
                        'InputSerialization' => [
                            'location' => 'xml',
                            'type' => 'object',
                            'properties' => [
                                'CompressionType' => [
                                    'type' => 'string',
                                    'location' => 'xml',
                                ],
                                'CSV' => [
                                    'type' => 'object',
                                    'location' => 'xml',
                                    'properties' => [
                                        'FileHeaderInfo' => [
                                            'type' => 'string',
                                            'location' => 'xml',
                                        ],
                                        'RecordDelimiter' => [
                                            'type' => 'string',
                                            'location' => 'xml',
                                        ],
                                        'FieldDelimiter' => [
                                            'type' => 'string',
                                            'location' => 'xml',
                                        ],
                                        'QuoteCharacter' => [
                                            'type' => 'string',
                                            'location' => 'xml',
                                        ],
                                        'QuoteEscapeCharacter' => [
                                            'type' => 'string',
                                            'location' => 'xml',
                                        ],
                                        'Comments' => [
                                            'type' => 'string',
                                            'location' => 'xml',
                                        ],
                                        'AllowQuotedRecordDelimiter' => [
                                            'type' => 'string',
                                            'location' => 'xml',
                                        ],
                                    ]
                                ],
                                'JSON' => [
                                    'type' => 'string',
                                    'location' => 'object',
                                    'properties' => [
                                        'Type' => [
                                            'type' => 'string',
                                            'location' => 'xml',
                                        ]
                                    ]
                                ],
                            ]
                        ],
                        'OutputSerialization' => [
                            'location' => 'xml',
                            'type' => 'object',
                            'properties' => [
                                'CompressionType' => [
                                    'type' => 'string',
                                    'location' => 'xml',
                                ],
                                'CSV' => [
                                    'type' => 'object',
                                    'location' => 'xml',
                                    'properties' => [
                                        'QuoteFields' => [
                                            'type' => 'string',
                                            'location' => 'xml',
                                        ],
                                        'RecordDelimiter' => [
                                            'type' => 'string',
                                            'location' => 'xml',
                                        ],
                                        'FieldDelimiter' => [
                                            'type' => 'string',
                                            'location' => 'xml',
                                        ],
                                        'QuoteCharacter' => [
                                            'type' => 'string',
                                            'location' => 'xml',
                                        ],
                                        'QuoteEscapeCharacter' => [
                                            'type' => 'string',
                                            'location' => 'xml',
                                        ],
                                    ]
                                ],
                                'JSON' => [
                                    'type' => 'string',
                                    'location' => 'object',
                                    'properties' => [
                                        'RecordDelimiter' => [
                                            'type' => 'string',
                                            'location' => 'xml',
                                        ]
                                    ]
                                ],
                            ]
                        ],
                        'RequestProgress' => [
                            'location' => 'xml',
                            'type' => 'object',
                            'properties' => [
                                'Enabled' => [
                                    'type' => 'string',
                                    'location' => 'xml',
                                ],
                            ]
                        ],
                    ],
                ],
            ],
            'models' => [
                'AbortMultipartUploadOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id'
                        ]
                    ]
                ],
                'CreateBucketOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'Location' => [
                            'type' => 'string',
                            'location' => 'header'
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id'
                        ]
                    ]
                ],
                'CompleteMultipartUploadOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'Location' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'Bucket' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'Key' => [
                            'type' => 'string',
                            'location' => 'xml'
                        ],
                        'Expiration' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-expiration',
                        ],
                        'ETag' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'ServerSideEncryption' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption',
                        ],
                        'VersionId' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-version-id',
                        ],
                        'SSEKMSKeyId' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-aws-kms-key-id',
                        ],
                        'RequestCharged' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-charged',
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'CreateMultipartUploadOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'Bucket' => [
                            'type' => 'string',
                            'location' => 'xml',
                            'sentAs' => 'Bucket'
                        ],
                        'Key' => [
                            'type' => 'string',
                            'location' => 'xml'
                        ],
                        'UploadId' => [
                            'type' => 'string',
                            'location' => 'xml'
                        ],
                        'ServerSideEncryption' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption',
                        ],
                        'SSECustomerAlgorithm' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-algorithm',
                        ],
                        'SSECustomerKeyMD5' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-key-MD5',
                        ],
                        'SSEKMSKeyId' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-aws-kms-key-id',
                        ],
                        'RequestCharged' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-charged',
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ]
                    ]
                ],
                'CopyObjectOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'ETag' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'LastModified' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'Expiration' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-expiration',
                        ],
                        'CopySourceVersionId' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-copy-source-version-id',
                        ],
                        'VersionId' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-version-id',
                        ],
                        'ServerSideEncryption' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption',
                        ],
                        'SSECustomerAlgorithm' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-algorithm',
                        ],
                        'RequestCharged' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-charged',
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'DeleteBucketOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id'
                        ]
                    ]
                ],
                'DeleteBucketCorsOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'DeleteBucketTaggingOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'DeleteBucketInventoryOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'DeleteObjectOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'DeleteMarker' => [
                            'type' => 'boolean',
                            'location' => 'header',
                            'sentAs' => 'x-cos-delete-marker',
                        ],
                        'VersionId' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-version-id',
                        ],
                        'RequestCharged' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-charged',
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'DeleteObjectsOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'Deleted' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'sentAs' => 'Deleted',
                            'data' => [
                                'xmlFlattened' => true,
                            ],
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'Key' => [
                                        'type' => 'string',
                                    ],
                                    'VersionId' => [
                                        'type' => 'string',
                                    ],
                                    'DeleteMarker' => [
                                        'type' => 'boolean',
                                    ],
                                    'DeleteMarkerVersionId' => [
                                        'type' => 'string',
                                    ],
                                ],
                            ],
                        ],
                        'RequestCharged' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-charged',
                        ],
                        'Errors' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'sentAs' => 'Error',
                            'data' => [
                                'xmlFlattened' => true,
                            ],
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'Key' => [
                                        'type' => 'string',
                                    ],
                                    'VersionId' => [
                                        'type' => 'string',
                                    ],
                                    'Code' => [
                                        'type' => 'string',
                                    ],
                                    'Message' => [
                                        'type' => 'string',
                                    ],
                                ],
                            ],
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'DeleteBucketLifecycleOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'DeleteBucketReplicationOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'DeleteBucketWebsiteOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'GetObjectOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'Body' => [
                            'type' => 'string',
                            'instanceOf' => 'GuzzleHttp\\Psr7\\Stream',
                            'location' => 'body',
                        ],
                        'DeleteMarker' => [
                            'type' => 'boolean',
                            'location' => 'header',
                            'sentAs' => 'x-cos-delete-marker',
                        ],
                        'AcceptRanges' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'accept-ranges',
                        ],
                        'Expiration' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-expiration',
                        ],
                        'Restore' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-restore',
                        ],
                        'LastModified' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Last-Modified',
                        ],
                        'ContentLength' => [
                            'type' => 'numeric',
                            'minimum'=> 0,
                            'location' => 'header',
                            'sentAs' => 'Content-Length',
                        ],
                        'ETag' => [
                            'type' => 'string',
                            'location' => 'header',
                        ],
                        'MissingMeta' => [
                            'type' => 'numeric',
                            'location' => 'header',
                            'sentAs' => 'x-cos-missing-meta',
                        ],
                        'VersionId' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-version-id',
                        ],
                        'CacheControl' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Cache-Control',
                        ],
                        'ContentDisposition' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Disposition',
                        ],
                        'ContentEncoding' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Encoding',
                        ],
                        'ContentLanguage' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Language',
                        ],
                        'ContentRange' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Range',
                        ],
                        'ContentType' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Type',
                        ],
                        'Expires' => [
                            'type' => 'string',
                            'location' => 'header',
                        ],
                        'WebsiteRedirectLocation' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-website-redirect-location',
                        ],
                        'ServerSideEncryption' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption',
                        ],
                        'SSECustomerAlgorithm' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-algorithm',
                        ],
                        'SSECustomerKeyMD5' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-key-MD5',
                        ],
                        'SSEKMSKeyId' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-aws-kms-key-id',
                        ],
                        'StorageClass' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-storage-class',
                        ],
                        'RequestCharged' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-charged',
                        ],
                        'ReplicationStatus' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-replication-status',
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'GetObjectAclOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'Owner' => [
                            'type' => 'object',
                            'location' => 'xml',
                            'properties' => [
                                'DisplayName' => [
                                    'type' => 'string',
                                ],
                                'ID' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                        'Grants' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'sentAs' => 'AccessControlList',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'Grantee' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'DisplayName' => [
                                                'type' => 'string'],
                                            'ID' => [
                                                'type' => 'string']]],
                                    'Permission' => [
                                        'type' => 'string',
                                    ],
                                ],
                            ],
                        ],
                        'RequestCharged' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-charged',
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'GetBucketAclOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'Owner' => [
                            'type' => 'object',
                            'location' => 'xml',
                            'properties' => [
                                'DisplayName' => [
                                    'type' => 'string'
                                ],
                                'ID' => [
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'Grants' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'sentAs' => 'AccessControlList',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'Grantee' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'DisplayName' => [
                                                'type' => 'string'
                                            ],
                                            'ID' => [
                                                'type' => 'string'
                                            ]
                                        ]
                                    ],
                                    'Permission' => [
                                        'type' => 'string'
                                    ]
                                ]
                            ]
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id'
                        ]
                    ]
                ],
                'GetBucketCorsOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'CORSRules' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'sentAs' => 'CORSRule',
                            'data' => [
                                'xmlFlattened' => true,
                            ],
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'ID' => [
                                        'type' => 'string'],
                                    'AllowedHeaders' => [
                                        'type' => 'array',
                                        'sentAs' => 'AllowedHeader',
                                        'data' => [
                                            'xmlFlattened' => true,
                                        ],
                                        'items' => [
                                            'type' => 'string',
                                        ]
                                    ],
                                    'AllowedMethods' => [
                                        'type' => 'array',
                                        'sentAs' => 'AllowedMethod',
                                        'data' => [
                                            'xmlFlattened' => true,
                                        ],
                                        'items' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                    'AllowedOrigins' => [
                                        'type' => 'array',
                                        'sentAs' => 'AllowedOrigin',
                                        'data' => [
                                            'xmlFlattened' => true,
                                        ],
                                        'items' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                    'ExposeHeaders' => [
                                        'type' => 'array',
                                        'sentAs' => 'ExposeHeader',
                                        'data' => [
                                            'xmlFlattened' => true,
                                        ],
                                        'items' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                    'MaxAgeSeconds' => [
                                        'type' => 'numeric',
                                    ],
                                ],
                            ],
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'GetBucketDomainOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'DomainRules' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'sentAs' => 'DomainRule',
                            'data' => [
                                'xmlFlattened' => true,
                            ],
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'Status' => [
                                        'type' => 'string'],
                                    'Name' => [
                                        'type' => 'string'],
                                    'Type' => [
                                        'type' => 'string'],
                                    'ForcedReplacement' => [
                                        'type' => 'string'],
                                ],
                            ],
                        ],
                        'DomainTxtVerification' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-domain-txt-verification',
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'GetBucketLifecycleOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'Rules' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'sentAs' => 'Rule',
                            'data' => [
                                'xmlFlattened' => true,
                            ],
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'Expiration' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'Date' => [
                                                'type' => 'string',
                                            ],
                                            'Days' => [
                                                'type' => 'numeric',
                                            ],
                                        ],
                                    ],
                                    'ID' => [
                                        'type' => 'string',
                                    ],
                                    'Filter' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'Prefix' => [
                                                'type' => 'string',
                                            ],
                                            'Tag' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'Key' => [
                                                        'type' => 'string'
                                                    ],
                                                    'Value' => [
                                                        'type' => 'string'
                                                    ],
                                                ]
                                            ]
                                        ],
                                    ],
                                    'Status' => [
                                        'type' => 'string',
                                    ],
                                    'Transition' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'Date' => [
                                                'type' => 'string',
                                            ],
                                            'Days' => [
                                                'type' => 'numeric',
                                            ],
                                            'StorageClass' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                    ],
                                    'NoncurrentVersionTransition' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'NoncurrentDays' => [
                                                'type' => 'numeric',
                                            ],
                                            'StorageClass' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                    ],
                                    'NoncurrentVersionExpiration' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'NoncurrentDays' => [
                                                'type' => 'numeric',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'GetBucketVersioningOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'Status' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'MFADelete' => [
                            'type' => 'string',
                            'location' => 'xml',
                            'sentAs' => 'MfaDelete',
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'GetBucketReplicationOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'Role' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'Rules' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'sentAs' => 'Rule',
                            'data' => [
                                'xmlFlattened' => true,
                            ],
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'ID' => [
                                        'type' => 'string',
                                    ],
                                    'Prefix' => [
                                        'type' => 'string',
                                    ],
                                    'Status' => [
                                        'type' => 'string',
                                    ],
                                    'Destination' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'Bucket' => [
                                                'type' => 'string',
                                            ],
                                            'StorageClass' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'GetBucketLocationOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'Location' => [
                            'type' => 'string',
                            'location' => 'body',
                            'filters' => [
                                'strval',
                                'strip_tags',
                                'trim',
                            ],
                        ],
                    ],
                ],
                'GetBucketAccelerateOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'Status' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'Type' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'GetBucketWebsiteOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RedirectAllRequestsTo' => [
                            'type' => 'object',
                            'location' => 'xml',
                            'properties' => [
                                'HostName' => [
                                    'type' => 'string',
                                ],
                                'Protocol' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                        'IndexDocument' => [
                            'type' => 'object',
                            'location' => 'xml',
                            'properties' => [
                                'Suffix' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                        'ErrorDocument' => [
                            'type' => 'object',
                            'location' => 'xml',
                            'properties' => [
                                'Key' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                        'RoutingRules' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'items' => [
                                'name' => 'RoutingRule',
                                'type' => 'object',
                                'sentAs' => 'RoutingRule',
                                'properties' => [
                                    'Condition' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'HttpErrorCodeReturnedEquals' => [
                                                'type' => 'string',
                                            ],
                                            'KeyPrefixEquals' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                    ],
                                    'Redirect' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'HostName' => [
                                                'type' => 'string',
                                            ],
                                            'HttpRedirectCode' => [
                                                'type' => 'string',
                                            ],
                                            'Protocol' => [
                                                'type' => 'string',
                                            ],
                                            'ReplaceKeyPrefixWith' => [
                                                'type' => 'string',
                                            ],
                                            'ReplaceKeyWith' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'GetBucketInventoryOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'Destination' => [
                            'type' => 'object',
                            'location' => 'xml',
                            'properties' => [
                                'COSBucketDestination' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'Format' => [
                                            'type' => 'string',
                                        ],
                                        'AccountId' => [
                                            'type' => 'string',
                                        ],
                                        'Bucket' => [
                                            'type' => 'string',
                                        ],
                                        'Prefix' => [
                                            'type' => 'string',
                                        ],
                                        'Encryption' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'SSE-COS' => [
                                                    'type' => 'string',
                                                ]
                                            ]
                                        ],

                                    ],
                                ],
                            ],
                        ],
                        'Schedule' => [
                            'type' => 'object',
                            'location' => 'xml',
                            'properties' => [
                                'Frequency' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                        'OptionalFields' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'properties' => [
                                'Key' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                        'OptionalFields' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'items' => [
                                'name' => 'Field',
                                'type' => 'string',
                                'sentAs' => 'Field',
                            ],
                        ],
                        'IsEnabled' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'Id' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'IncludedObjectVersions' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'GetBucketTaggingOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'TagSet' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'items' => [
                                'sentAs' => 'Tag',
                                'type' => 'object',
                                'properties' => [
                                    'Key' => [
                                        'type' => 'string',
                                    ],
                                    'Value' => [
                                        'type' => 'string',
                                    ],
                                ],
                            ],
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'GetBucketNotificationOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'CloudFunctionConfigurations' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'sentAs' => 'CloudFunctionConfiguration',
                            'data' => [
                                'xmlFlattened' => true,
                            ],
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'Id' => [
                                        'type' => 'string',
                                    ],
                                    'CloudFunction' => [
                                        'type' => 'string',
                                        'sentAs' => 'CloudFunction',
                                    ],
                                    'Events' => [
                                        'type' => 'array',
                                        'sentAs' => 'Event',
                                        'data' => [
                                            'xmlFlattened' => true,
                                        ],
                                        'items' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                    'Filter' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'Key' => [
                                                'type' => 'object',
                                                'sentAs' => 'Key',
                                                'properties' => [
                                                    'FilterRules' => [
                                                        'type' => 'array',
                                                        'sentAs' => 'FilterRule',
                                                        'data' => [
                                                            'xmlFlattened' => true,
                                                        ],
                                                        'items' => [
                                                            'type' => 'object',
                                                            'properties' => [
                                                                'Name' => [
                                                                    'type' => 'string',
                                                                ],
                                                                'Value' => [
                                                                    'type' => 'string',
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'GetBucketLoggingOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'LoggingEnabled' => [
                            'location' => 'xml',
                            'type' => 'object',
                            'properties' => [
                                'TargetBucket' => [
                                    'type' => 'string',
                                    'location' => 'xml',
                                ],
                                'TargetPrefix' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'UploadPartOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'ServerSideEncryption' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption',
                        ],
                        'ETag' => [
                            'type' => 'string',
                            'location' => 'header',
                        ],
                        'SSECustomerAlgorithm' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-algorithm',
                        ],
                        'SSECustomerKeyMD5' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-key-MD5',
                        ],
                        'SSEKMSKeyId' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-aws-kms-key-id',
                        ],
                        'RequestCharged' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-charged',
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'UploadPartCopyOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'CopySourceVersionId' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-copy-source-version-id',
                        ],
                        'ETag' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'LastModified' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'ServerSideEncryption' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption',
                        ],
                        'SSECustomerAlgorithm' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-algorithm',
                        ],
                        'SSECustomerKeyMD5' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-key-MD5',
                        ],
                        'SSEKMSKeyId' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-aws-kms-key-id',
                        ],
                        'RequestCharged' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-charged',
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'PutBucketAclOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id'
                        ]
                    ]
                ],
                'PutObjectOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'Expiration' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-expiration',
                        ],
                        'ETag' => [
                            'type' => 'string',
                            'location' => 'header',
                        ],
                        'ServerSideEncryption' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption',
                        ],
                        'VersionId' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-version-id',
                        ],
                        'SSECustomerAlgorithm' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-algorithm',
                        ],
                        'SSECustomerKeyMD5' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-key-MD5',
                        ],
                        'SSEKMSKeyId' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-aws-kms-key-id',
                        ],
                        'RequestCharged' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-charged',
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'PutObjectAclOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestCharged' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-charged',
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'PutBucketCorsOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'PutBucketDomainOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'PutBucketLifecycleOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'PutBucketVersioningOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'PutBucketReplicationOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'PutBucketNotificationOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'PutBucketWebsiteOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header', 
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'PutBucketAccelerateOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'PutBucketLoggingOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'PutBucketInventoryOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'PutBucketTaggingOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'RestoreObjectOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestCharged' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-charged',
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'ListPartsOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'Bucket' => [
                            'type' => 'string',
                            'location' => 'xml'
                        ],
                        'Key' => [
                            'type' => 'string',
                            'location' => 'xml'
                        ],
                        'UploadId' => [
                            'type' => 'string',
                            'location' => 'xml'
                        ],
                        'PartNumberMarker' => [
                            'type' => 'numeric',
                            'location' => 'xml'
                        ],
                        'NextPartNumberMarker' => [
                            'type' => 'numeric',
                            'location' => 'xml'
                        ],
                        'MaxParts' => [
                            'type' => 'numeric',
                            'location' => 'xml'
                        ],
                        'IsTruncated' => [
                            'type' => 'boolean',
                            'location' => 'xml'
                        ],
                        'Parts' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'sentAs' => 'Part',
                            'data' => [
                                'xmlFlattened' => true
                            ],
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'PartNumber' => [
                                        'type' => 'numeric'
                                    ],
                                    'LastModified' => [
                                        'type' => 'string'
                                    ],
                                    'ETag' => [
                                        'type' => 'string'
                                    ],
                                    'Size' => [
                                        'type' => 'numeric'
                                    ]
                                ]
                            ]
                        ],
                        'Initiator' => [
                            'type' => 'object',
                            'location' => 'xml',
                            'properties' => [
                                'ID' => [
                                    'type' => 'string'
                                ],
                                'DisplayName' => [
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'Owner' => [
                            'type' => 'object',
                            'location' => 'xml',
                            'properties' => [
                                'DisplayName' => [
                                    'type' => 'string'
                                ],
                                'ID' => [
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'StorageClass' => [
                            'type' => 'string',
                            'location' => 'xml'
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id'
                        ]
                    ]
                ],
                'ListObjectsOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'IsTruncated' => [
                            'type' => 'boolean',
                            'location' => 'xml'
                        ],
                        'Marker' => [
                            'type' => 'string',
                            'location' => 'xml'
                        ],
                        'NextMarker' => [
                            'type' => 'string',
                            'location' => 'xml'
                        ],
                        'Contents' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'data' => [
                                'xmlFlattened' => true
                            ],
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'Key' => [
                                        'type' => 'string'
                                    ],
                                    'LastModified' => [
                                        'type' => 'string'
                                    ],
                                    'ETag' => [
                                        'type' => 'string'
                                    ],
                                    'Size' => [
                                        'type' => 'numeric'
                                    ],
                                    'StorageClass' => [
                                        'type' => 'string'
                                    ],
                                    'Owner' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'DisplayName' => [
                                                'type' => 'string'
                                            ],
                                            'ID' => [
                                                'type' => 'string'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'Name' => [
                            'type' => 'string',
                            'location' => 'xml'
                        ],
                        'Prefix' => [
                            'type' => 'string',
                            'location' => 'xml'
                        ],
                        'Delimiter' => [
                            'type' => 'string',
                            'location' => 'xml'
                        ],
                        'MaxKeys' => [
                            'type' => 'numeric',
                            'location' => 'xml'
                        ],
                        'CommonPrefixes' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'data' => [
                                'xmlFlattened' => true
                            ],
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'Prefix' => [
                                        'type' => 'string'
                                    ]
                                ]
                            ]
                        ],
                        'EncodingType' => [
                            'type' => 'string',
                            'location' => 'xml'],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id'
                        ]
                    ]
                ],
                'ListBucketsOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'Buckets' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'Name' => [
                                        'type' => 'string',
                                    ],
                                    'CreationDate' => [
                                        'type' => 'string',
                                    ],
                                ],
                            ],
                        ],
                        'Owner' => [
                            'type' => 'object',
                            'location' => 'xml',
                            'properties' => [
                                'DisplayName' => [
                                    'type' => 'string',
                                ],
                                'ID' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'ListObjectVersionsOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'IsTruncated' => [
                            'type' => 'boolean',
                            'location' => 'xml',
                        ],
                        'KeyMarker' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'VersionIdMarker' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'NextKeyMarker' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'NextVersionIdMarker' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'Versions' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'sentAs' => 'Versions',
                            'data' => [
                                'xmlFlattened' => true,
                            ],
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'ETag' => [
                                        'type' => 'string',
                                    ],
                                    'Size' => [
                                        'type' => 'numeric',
                                    ],
                                    'StorageClass' => [
                                        'type' => 'string',
                                    ],
                                    'Key' => [
                                        'type' => 'string',
                                    ],
                                    'VersionId' => [
                                        'type' => 'string',
                                    ],
                                    'IsLatest' => [
                                        'type' => 'boolean',
                                    ],
                                    'LastModified' => [
                                        'type' => 'string',
                                    ],
                                    'Owner' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'DisplayName' => [
                                                'type' => 'string',
                                            ],
                                            'ID' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'DeleteMarkers' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'sentAs' => 'DeleteMarker',
                            'data' => [
                                'xmlFlattened' => true,
                            ],
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'Owner' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'DisplayName' => [
                                                'type' => 'string',
                                            ],
                                            'ID' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                    ],
                                    'Key' => [
                                        'type' => 'string',
                                    ],
                                    'VersionId' => [
                                        'type' => 'string',
                                    ],
                                    'IsLatest' => [
                                        'type' => 'boolean',
                                    ],
                                    'LastModified' => [
                                        'type' => 'string',
                                    ],
                                ],
                            ],
                        ],
                        'Name' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'Prefix' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'Delimiter' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'MaxKeys' => [
                            'type' => 'numeric',
                            'location' => 'xml',
                        ],
                        'CommonPrefixes' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'data' => [
                                'xmlFlattened' => true,
                            ],
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'Prefix' => [
                                        'type' => 'string',
                                    ],
                                ],
                            ],
                        ],
                        'EncodingType' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'ListMultipartUploadsOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'Bucket' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'KeyMarker' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'UploadIdMarker' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'NextKeyMarker' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'Prefix' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'Delimiter' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'NextUploadIdMarker' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'MaxUploads' => [
                            'type' => 'numeric',
                            'location' => 'xml',
                        ],
                        'IsTruncated' => [
                            'type' => 'boolean',
                            'location' => 'xml',
                        ],
                        'Uploads' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'sentAs' => 'Upload',
                            'data' => [
                                'xmlFlattened' => true,
                            ],
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'UploadId' => [
                                        'type' => 'string',
                                    ],
                                    'Key' => [
                                        'type' => 'string',
                                    ],
                                    'Initiated' => [
                                        'type' => 'string',
                                    ],
                                    'StorageClass' => [
                                        'type' => 'string',
                                    ],
                                    'Owner' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'DisplayName' => [
                                                'type' => 'string',
                                            ],
                                            'ID' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                    ],
                                    'Initiator' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'ID' => [
                                                'type' => 'string',
                                            ],
                                            'DisplayName' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'CommonPrefixes' => [
                            'type' => 'array',
                            'location' => 'xml',
                            'data' => [
                                'xmlFlattened' => true,
                            ],
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'Prefix' => [
                                        'type' => 'string',
                                    ],
                                ],
                            ],
                        ],
                        'EncodingType' => [
                            'type' => 'string',
                            'location' => 'xml',
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                'HeadObjectOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'DeleteMarker' => [
                            'type' => 'boolean',
                            'location' => 'header',
                            'sentAs' => 'x-cos-delete-marker',
                        ],
                        'AcceptRanges' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'accept-ranges',
                        ],
                        'Expiration' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-expiration',
                        ],
                        'Restore' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-restore',
                        ],
                        'LastModified' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Last-Modified',
                        ],
                        'ContentLength' => [
                            'type' => 'numeric',
                            'minimum'=> 0,
                            'location' => 'header',
                            'sentAs' => 'Content-Length',
                        ],
                        'ETag' => [
                            'type' => 'string',
                            'location' => 'header',
                        ],
                        'MissingMeta' => [
                            'type' => 'numeric',
                            'location' => 'header',
                            'sentAs' => 'x-cos-missing-meta',
                        ],
                        'VersionId' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-version-id',
                        ],
                        'CacheControl' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Cache-Control',
                        ],
                        'ContentDisposition' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Disposition',
                        ],
                        'ContentEncoding' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Encoding',
                        ],
                        'ContentLanguage' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Language',
                        ],
                        'ContentType' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'Content-Type',
                        ],
                        'Expires' => [
                            'type' => 'string',
                            'location' => 'header',
                        ],
                        'WebsiteRedirectLocation' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-website-redirect-location',
                        ],
                        'ServerSideEncryption' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption',
                        ],
                        'SSECustomerAlgorithm' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-algorithm',
                        ],
                        'SSECustomerKeyMD5' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-customer-key-MD5',
                        ],
                        'SSEKMSKeyId' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-server-side-encryption-aws-kms-key-id',
                        ],
                        'StorageClass' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-storage-class',
                        ],
                        'RequestCharged' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-charged',
                        ],
                        'ReplicationStatus' => [
                            'type' => 'string',
                            'location' => 'header',
                            'sentAs' => 'x-cos-replication-status',
                        ],
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ]
                    ]
                ],
                'HeadBucketOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RequestId' => [
                            'location' => 'header',
                            'sentAs' => 'x-cos-request-id',
                        ],
                    ],
                ],
                
                'SelectObjectContentOutput' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                    'properties' => [
                        'RawData' => [
                            'type' => 'string',
                            'instanceOf' => 'GuzzleHttp\\Psr7\\Stream',
                            'location' => 'body',
                        ],
                    ],
                ],
            ]
        ];
    }
}
