<?php
/**
 * User: 小胡
 * Date: 2025/3/8
 * Time: 20:00
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
header('Content-Type: application/json; charset=utf-8');
if (!$path = dzzdecode($_GET['path'])) {
    exit(json_encode(array('error' => 'access denied')));
}
$content = getContent();
$arr = IO::setFileContent($path, $content);
if ($arr) {
    if ($arr['error']) {
        exit(json_encode(array('error' => $arr['error'])));
    } else {
        $arr['success'] = true;
        exit(json_encode($arr));
    }
} else {
    exit(json_encode(array('error' => lang('file_save_failure'))));
}
function getContent() {
    try {
        if ($_FILES['content']['tmp_name']) {
            $content = file_get_contents($_FILES['content']['tmp_name']);
            if ($content === FALSE) {
                exit(json_encode(array('error' => 'Bad Request')));
            }
        } else {
            $content = isset($_GET['content']) ? $_GET['content'] : '';
        }
    } catch (Exception $e) {
        exit(json_encode(array('error' => 'Bad Request')));
    }
    return $content;
}