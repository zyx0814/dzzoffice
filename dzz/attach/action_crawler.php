<?php
/**
 * 抓取远程图片
 * User: Jinqn
 * Date: 14-04-14
 * Time: 下午19:18
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
set_time_limit(0);
include DZZ_ROOT . "./dzz/attach/Uploader.class.php";

/* 上传配置 */
$config = [
    "pathFormat" => $CONFIG['catcherPathFormat'],
    "maxSize" => $CONFIG['catcherMaxSize'],
    "allowFiles" => $CONFIG['catcherAllowFiles'],
    "oriName" => "remote.png"
];
$fieldName = $CONFIG['catcherFieldName'];

/* 抓取远程图片 */
$list = [];
$source = $_POST[$fieldName] ?? $_GET[$fieldName];
foreach ($source as $imgUrl) {
    $item = new Uploader($imgUrl, $config, "remote");
    $info = $item->getFileInfo();
    $list[] = [
        "state" => $info["state"],
        "url" => $info["url"],
        "size" => $info["size"],
        "title" => htmlspecialchars($info["title"]),
        "original" => htmlspecialchars($info["original"]),
        "source" => htmlspecialchars($imgUrl),
        'attach' => $info['attach']
    ];
}

/* 返回抓取数据 */
return json_encode([
    'state' => count($list) ? 'SUCCESS' : 'ERROR',
    'list' => $list
]);