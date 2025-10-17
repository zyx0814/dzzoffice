<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {
    exit('Access Denied');
}
include_once libfile('function/admin');
$op = isset($_GET['op']) ? $_GET['op'] : '';
$navtitle = lang('fileperms') . ' - ' . lang('appname');
$step = max(1, intval($_GET['step']));
if ($step == 1) {
} elseif ($step == 2) {
    $type = implode('_', (array)$_GET['type']);
} elseif ($step == 3) {
    $type = explode('_', $_GET['type']);
    try {
        $entryarray = array(
            'data',
            'data/attachment',
            'data/attachment/appico',
            'data/attachment/appimg',
            'data/attachment/cache',
            'data/attachment/dzz',
            'data/attachment/temp',
            'data/cache',
            'data/log',
            'data/template'
        );
        $result = '';
        $allWritable = true; // 标记是否所有目录都可写
        
        foreach ($entryarray as $entry) {
            $fullentry = DZZ_ROOT . './' . $entry;
            $exists = is_dir($fullentry) || file_exists($fullentry);
            
            if (!$exists) {
                $result .= '<li class="list-group-item text-warning d-flex justify-content-between align-items-start">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold">' . (is_dir($fullentry) ? lang('dir') : lang('file')) . './' . $entry . '</div>
                    </div>
                    <span class="badge bg-warning rounded-pill">不存在</span>
                </li>';
                $allWritable = false;
                continue;
            }
            
            // 检查是否可写
            if (!dir_writeable($fullentry)) {
                $result .= '<li class="list-group-item text-danger d-flex justify-content-between align-items-start">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold">' . (is_dir($fullentry) ? lang('dir') : lang('file')) . './' . $entry . '</div>
                    </div>
                    <span class="badge bg-danger rounded-pill">无法写入</span>
                </li>';
                $allWritable = false;
            } else {
                $result .= '<li class="list-group-item text-success d-flex justify-content-between align-items-start">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold">' . (is_dir($fullentry) ? lang('dir') : lang('file')) . './' . $entry . '</div>
                    </div>
                    <span class="badge bg-success rounded-pill">可写入</span>
                </li>';
            }
        }
        
        // 处理附件目录
        $attachdir = $_G['setting']['attachdir'];
        if($attachdir) {
            if(!dir_writeable($attachdir)) {
                $result .= '<li class="list-group-item text-danger d-flex justify-content-between align-items-start">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold">' . lang('dir') . $attachdir . '</div>
                    </div>
                    <span class="badge bg-danger rounded-pill">无法写入</span>
                </li>';
                $allWritable = false;
            } else {
                $result .= '<li class="list-group-item text-success d-flex justify-content-between align-items-start">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold">' . lang('dir') . $attachdir . '</div>
                    </div>
                    <span class="badge bg-success rounded-pill">可写入</span>
                </li>';
            }
        }
        
        if ($allWritable) {
            $alert = 'primary';
            $msg = '文件及目录属性全部正确，都能写入';
        } else {
            $alert = 'warning';
            $msg .= '部分目录存在问题，需要修复';
        }
    } catch (Exception $e) {
        $alert = 'warning';
        $msg = "<li class=\"list-group-item text-danger\">发生错误：" . $e->getMessage() . "</li>";
    }
}
include template('fileperms');
?>
