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
function output_error($status_code, $message) {
    $status_map = [
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error'
    ];
    $status_text = $status_map[$status_code] ?? 'Error';

    @header("HTTP/1.1 {$status_code} {$status_text}");
    @header("Status: {$status_code} {$status_text}");

    @header("Content-Type: application/json; charset=utf-8");
    exit(json_encode([
        'code' => $status_code,
        'msg' => $message,
        'success' => false
    ], JSON_UNESCAPED_UNICODE));
}

if (!$_G['uid']) {
    output_error(403, lang('not_login'));
}

define('PERM_CACHE_DIR', DZZ_ROOT . '/data/cache/perm/');
// 缓存有效期（秒），默认5分钟
define('PERM_CACHE_EXPIRE', 300);

if (!is_dir(PERM_CACHE_DIR)) {
    @mkdir(PERM_CACHE_DIR, 0777, true);
    @file_put_contents(PERM_CACHE_DIR . '/index.htm', '');
}
/**
 * 获取权限缓存
 * @param int $uid 用户ID
 * @param string $path 资源路径
 * @return bool|false 缓存结果（true/false）或false（缓存无效）
 */
function get_perm_cache($uid, $path) {
    $cacheKey = md5("{$uid}_{$path}");
    $cacheFile = PERM_CACHE_DIR . "{$cacheKey}.php";
    
    if (!is_file($cacheFile)) {
        return false;
    }
    
    $data = @unserialize(@file_get_contents($cacheFile));
    if (!$data || !isset($data['time'], $data['result'])) {
        @unlink($cacheFile);// 缓存格式错误，清理无效文件
        return false;
    }
    
    if (TIMESTAMP - $data['time'] > PERM_CACHE_EXPIRE) {
        @unlink($cacheFile);
        return false;
    }
    
    return $data['result'];
}
/**
 * 写入权限缓存
 * @param int $uid 用户ID
 * @param string $path 资源路径
 * @param bool $result 权限检查结果（true/false）
 */
function set_perm_cache($uid, $path, $result) {
    $cacheKey = md5("{$uid}_{$path}");
    $cacheFile = PERM_CACHE_DIR . "{$cacheKey}.php";
    
    $data = serialize([
        'time' => TIMESTAMP,
        'result' => $result
    ]);
    
    $fp = @fopen($cacheFile, 'w');
    // 写入缓存（加锁避免并发写入问题）
    if ($fp) {
        @flock($fp, LOCK_EX);
        @fwrite($fp, $data);
        @flock($fp, LOCK_UN);
        @fclose($fp);
    }
}

if (!$path = dzzdecode(rawurldecode($_GET['path']))) {
    output_error(403,'Access Denied');
}
// 权限检查（优先使用缓存）
$permResult = get_perm_cache($_G['uid'], $path);
if ($permResult === false) {
    // 缓存未命中：获取元数据并检查权限
    $meta = IO::getMeta($path);
    if (!$meta) {
        output_error(404, lang('file_not_exist'));
    }
    if ($meta['error']) {
        output_error(403, $meta['error']);
    }
    $permResult = (isset($meta['rid']) && !empty($meta['rid'])) ? perm_check::checkperm('download', $meta) : false;
    // 写入缓存（即使权限为false也缓存，减少重复检查）
    set_perm_cache($_G['uid'], $path, $permResult);
}
if (!$permResult) {
    output_error(403, lang('file_download_no_privilege'));
}

if (!$url = (IO::getStream($path))) {
    output_error(403,lang('attachment_nonexistence'));
}
if (is_array($url) && isset($url['error'])) {
    output_error(403,$url['error']);
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
@header('Accept-Ranges: bytes');
if (is_file($url)) {
    $start = 0;
    $total = filesize($url);
    if (isset($_SERVER['HTTP_RANGE'])) {
        $range = str_replace('=', '-', $_SERVER['HTTP_RANGE']);
        $range = explode('-', $range);
        if (isset($range[2]) && intval($range[2]) > 0) {
            $end = trim($range[2]);
        } else {
            $end = $total - 1;
        }
        $start = trim($range[1]);
        $size = $end - $start + 1;

        header('HTTP/1.1 206 Partial Content');
        header('Content-Length:' . $size);
        header('Content-Range: bytes ' . $start . '-' . $end . '/' . $total);

    } else {
        $size = $end = $total;
        header('HTTP/1.1 200 OK');
        header('Content-Length:' . $size);
        header('Content-Range: bytes 0-' . ($total - 1) . '/' . $total);
    }
    $fp = @fopen($url, 'rb');
    if (!$fp) {
        @header('HTTP/1.1 404 Not Found');
        @header('Status: 404 Not Found');
        exit('Access Denied');
    } else {
        @ob_end_clean();
        if (getglobal('gzipcompress')) @ob_start('ob_gzhandler');
        fseek($fp, $start, 0);
        $cur = $start;

        while (!feof($fp) && $cur <= $end && (connection_status() == 0)) {
            print fread($fp, min(1024 * 16, ($end - $cur) + 1));
            $cur += 1024 * 16;
        }

        fclose($fp);
        exit();
    }
} else {
    @ob_end_clean();
    if (getglobal('gzipcompress')) @ob_start('ob_gzhandler');
    @readfile($url);
    @flush();
    @ob_flush();
    exit();
}