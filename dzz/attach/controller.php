<?php
//header('Access-Control-Allow-Origin: http://www.baidu.com'); //设置http://www.baidu.com允许跨域访问
//header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
header("Content-Type: text/html; charset=utf-8");

$CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(DZZ_ROOT."./dzz/attach/config.json")), true);
$action = $_GET['action'];
$markdown=intval($_GET['markdown']);
switch ($action) {
    case 'config':
        $result =  ($CONFIG);
        break;

    /* 上传图片 */
    case 'uploadimage':
    /* 上传涂鸦 */
    case 'uploadscrawl':
    /* 上传视频 */
    case 'uploadvideo':
    /* 上传文件 */
    case 'uploadfile':
        $result = include("./dzz/attach/action_upload.php");
        break;

    /* 抓取远程文件 */
    case 'catchimage':
        $result = include("./dzz/attach/action_crawler.php");
        break;

    default:
        $result = array(
            'state'=> lang('request_address_wrong')
        );
        break;
}

/* 输出结果 */
if (isset($_GET["callback"])) {
    if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
        echo htmlspecialchars($_GET["callback"]) . '(' . json_encode($result) . ')';
    } else {
        echo json_encode(array(
            'state'=> lang('callback_parameter_valid')
        ));
    }
} else {
	
	if($markdown){
		$result=array('url'=>$result['url'],
					  'success'=>$result['state']=='SUCCESS'?1:0,
					  'message'=>$result['state']);
	  
	}
	 echo json_encode($result);
   
    
}