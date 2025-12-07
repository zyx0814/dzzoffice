<?php

if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
function _dfsockopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE, $encodetype  = 'URLENCODE', $allowcurl = TRUE, $position = 0, $files = array()) {
    $param = array(
        'url' => $url,
        'limit' => $limit,
        'post' => $post,
        'cookie' => $cookie,
        'ip' => $ip,
        'timeout' => $timeout,
        'block' => $block,
        'encodetype' => $encodetype,
        'allowcurl' => $allowcurl,
        'position' => $position,
        'files' => $files
    );
    $fs = filesock::open($param);
    return $fs->request();
}

?>