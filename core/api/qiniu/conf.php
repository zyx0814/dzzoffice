<?php
global $SDK_VER;

global $QINIU_UP_HOST;
global $QINIU_RS_HOST;
global $QINIU_RSF_HOST;
 
global $QINIU_ACCESS_KEY;
global $QINIU_SECRET_KEY;
global $HOSTS;
$SDK_VER = "6.1.9";
$HOSTS=array(
	'z0'=>array('title'=>'华东',
					 'up_http'=>'http://upload.qiniu.com',
					 'up_https'=>'https://up.qbox.me',
					 'io_http'=>'http://iovip.qbox.me',
					 'io_https'=>'https://iovip.qbox.me'
					 )
	,'z1'=>array('title'=>'华北',
					 'up_http'=>'http://upload-z1.qiniu.com',
					 'up_https'=>'https://up-z1.qbox.me',
					 'io_http'=>'http://iovip-z1.qbox.me',
					 'io_https'=>'https://iovip-z1.qbox.me'
					 )
	,'z2'=>array('title'=>'华南',
					 'up_http'=>'http://upload-z2.qiniu.com',
					 'up_https'=>'https://up-z2.qbox.me',
					 'io_http'=>'http://iovip-z2.qbox.me',
					 'io_https'=>'https://iovip-z2.qbox.me'
					 )
	,'na0'=>array('title'=>'北美',
					 'up_http'=>'http://upload-na0.qiniu.com',
					 'up_https'=>'https://up-na0.qbox.me',
					 'io_http'=>'http://iovip-na0.qbox.me',
					 'io_https'=>'https://iovip-na0.qbox.me'
					 )
	,'as0'=>array('title'=>'东南亚',
					 'up_http'=>'http://up-as0.qiniup.com',
					 'up_https'=>'https://up-as0.qbox.me',
					 'io_http'=>'http://iovip-as0.qbox.me',
					 'io_https'=>'https://iovip-as0.qbox.me'
					 )
					 
);
$QINIU_UP_HOST	= 'http://upload.qiniu.com';
$QINIU_RS_HOST	= 'http://rs.qbox.me';
$QINIU_RSF_HOST	= 'http://rsf.qbox.me';

$QINIU_ACCESS_KEY	= '<Please apply your access key>';
$QINIU_SECRET_KEY	= '<Dont send your secret key to anyone>';

