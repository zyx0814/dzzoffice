<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

if (!$path = dzzdecode(rawurldecode($_GET['path']))) {
    @header('HTTP/1.1 404 Not Found');
    @header('Status: 404 Not Found');
    exit('Access Denied');
}
if (!$url = (IO::getStream($path))) {
    @header('HTTP/1.1 403 Not Found');
    @header('Status: 403 Not Found');
    exit(lang('attachment_nonexistence'));
}
if (is_array($url) && isset($url['error'])) {
    @header('HTTP/1.1 403 Not Found');
    @header('Status: 403 Not Found');
    exit($url['error']);
}
$filename = trim($_GET['n'], '.dzz') ?: $_GET['filename'];
// 检查是否包含 Unicode 编码的字符
if (preg_match('/\\\\u[0-9a-fA-F]{4}/', $filename)) {
    // 将 Unicode 编码转换为 UTF-8
    $filename = json_decode('"' . $filename . '"');
}
$ext = strtolower(substr(strrchr($filename, '.'), 1, 10));
if (!$ext) $ext = strtolower(substr(strrchr(preg_replace("/\.dzz$/i", '', preg_replace("/\?.*/i", '', $url)), '.'), 1, 10));
if ($ext == 'dzz' || ($ext && in_array($ext, $_G['setting']['unRunExts']))) {//如果是本地文件,并且是阻止运行的后缀名时;
    $mime = 'text/plain';
} else {
    $mime = dzz_mime::get_type($ext);
}
@header('Content-Disposition: inline; filename="' . $filename . '"');
@header('cache-control:public');
@header('Content-Type: ' . $mime);

// 获取文件大小
$filesize = filesize($url);
$start = 0;
$end = $filesize - 1;
$length = $filesize;

// 检查是否收到 Range 请求
$isRangeRequest = false;
if (isset($_SERVER['HTTP_RANGE'])) {
    // 解析 Range 头，例如 "bytes=0-999"
    if (preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches)) {
        $start = intval($matches[1]);
        $end = isset($matches[2]) ? intval($matches[2]) : $filesize - 1;
        $length = $end - $start + 1;

        // 返回 206 Partial Content 状态码
        header('HTTP/1.1 206 Partial Content');
        header("Content-Range: bytes $start-$end/$filesize");
        $isRangeRequest = true;
    }
}

@header('Accept-Ranges: bytes');
@header('Content-Length: ' . $length);

$fp = @fopen($url, 'rb');
if ($fp) {
    @ob_end_clean();
    if (getglobal('gzipcompress')) @ob_start('ob_gzhandler');

    fseek($fp, $start);

    $bufferSize = 8192; // 8KB缓冲区
    $remaining = $length;

    while (!feof($fp) && $remaining > 0) {
        $readSize = min($bufferSize, $remaining);
        echo fread($fp, $readSize);
        $remaining -= $readSize;
        @flush();
        @ob_flush();
    }

    fclose($fp);
}
exit();
