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
require_once libfile('function/orguser');
$h1 = getProfileForImport();
$h0 = array_merge($h0, $h1);
$title = lang('bulk_import_user_template');
$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()->setCreator($_G['username'])
    ->setTitle($title . ' - DzzOffice')
    ->setSubject($title)
    ->setDescription($title . ' Export By DzzOffice  ' . date('Y-m-d H:i:s'))
    ->setKeywords($title)
    ->setCategory($title);
$list = array();
// Create a first sheet
$objPHPExcel->setActiveSheetIndex(0);
$j = 0;
foreach ($h0 as $key => $value) {
    $index = getColIndex($j) . '1';
    $objPHPExcel->getActiveSheet()->setCellValue($index, $value);
    if ($key == 'username' || $key == 'email') {
        $objPHPExcel->getActiveSheet()->getStyle($index)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
    }
    $list[1][$index] = $value;
    $j++;
}

$objPHPExcel->setActiveSheetIndex(0);
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$filename = $_G['setting']['attachdir'] . './cache/' . random(5) . '.xlsx';
$objWriter->save($filename);


$name = $title . '.xlsx';
$name = '"' . (strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($name) : $name) . '"';

$filesize = filesize($filename);
$chunk = 10 * 1024 * 1024;
if (!$fp = @fopen($filename, 'rb')) {
    exit(lang('export_failure'));
}
dheader('Date: ' . gmdate('D, d M Y H:i:s', TIMESTAMP) . ' GMT');
dheader('Last-Modified: ' . gmdate('D, d M Y H:i:s', TIMESTAMP) . ' GMT');
dheader('Content-Encoding: none');
dheader('Content-Disposition: attachment; filename=' . $name);
dheader('Content-Type: application/octet-stream');
dheader('Content-Length: ' . $filesize);
@ob_end_clean();
if (getglobal('gzipcompress')) @ob_start('ob_gzhandler');
while (!feof($fp)) {
    echo fread($fp, $chunk);
    @ob_flush();  // flush output
    @flush();
}
@unlink($filename);
exit();
?>