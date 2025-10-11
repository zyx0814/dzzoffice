<?php 
/** php下载类,支持断点续传 
*  Date:  2013-06-30 
*  Author: test 
*  Ver:  1.0 
* 
*  Func: 
*  download: 下载文件 
*  setSpeed: 设置下载速度 
*  getRange: 获取header中Range 
*/
  
class FileDownload{ // class start 
  
  private $_speed = 2048;  // 下载速度（单位：KB）
  private $_limitDelay = 0;    // 限速延迟（单位：微秒，0=不限速，默认不限速）

  /** 下载 
  * @param String $file  要下载的文件路径 
  * @param String $name  文件名称,为空则与下载的文件名称一样 
  * @param boolean $reload 是否开启断点续传 
  */
  public function download($file, $name='',$file_size=0,$dateline=0, $reload=false){ 
    if (is_array($file) && isset($file['error'])) {
      topshowmessage(lang('file_not_exist1'));
    }
    if($name==''){ 
      $name = basename($file); 
    } 
    if(!$dateline){
      $dataline=TIMESTAMP;
    }
    if(!$fp = fopen($file, 'rb')){
      topshowmessage(lang('file_not_exist1'));
    }
    $db = DB::object();
    $db->close();
    @ob_end_clean();
    if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
    if(!$file_size) $file_size = filesize($file);
      $ranges = $this->getRange($file_size);
      $charset = CHARSET;
      header('cache-control:public');
      header('Date: '.gmdate('D, d M Y H:i:s', $dateline).' GMT');
      header('Last-Modified: '.gmdate('D, d M Y H:i:s', $dateline).' GMT');
      header('content-type:application/octet-stream');
      if (preg_match("/Firefox/", $_SERVER["HTTP_USER_AGENT"])) {
          $attachment = 'attachment; filename*='.$charset.'\'\'' . $name;
      } elseif (!preg_match("/Chrome/", $_SERVER["HTTP_USER_AGENT"]) && preg_match("/Safari/", $_SERVER["HTTP_USER_AGENT"])) {
          $name = trim($name,'"');
          $filename = rawurlencode($name); // 注意：rawurlencode与urlencode的区别
          $attachment = 'attachment; filename*='.$charset.'\'\'' . $filename;
      } else{
          $attachment = 'attachment; filename='.$name;
      }
      header('content-disposition:'.$attachment);
      if($reload && $ranges!=null){ // 使用续传
        header('HTTP/1.1 206 Partial Content'); 
        header('Accept-Ranges:bytes'); 
          
        // 剩余长度 
        header(sprintf('content-length:%u',$ranges['end']-$ranges['start']+1)); 
          
        // range信息 
        header(sprintf('content-range:bytes %s-%s/%s', $ranges['start'], $ranges['end'], $file_size)); 
          
        // fp指针跳到断点位置 
        fseek($fp, sprintf('%u', $ranges['start'])); 
      }else{
        header('HTTP/1.1 200 OK'); 
        header('content-length:'.$file_size); 
      } 
  
      while(!feof($fp)){
        echo fread($fp, round($this->_speed*1024,0));
        @flush(); @ob_flush();
        if($this->_limitDelay > 0){
          usleep($this->_limitDelay);
        }
       // usleep(500); // 用于测试,减慢下载速度 
      }
      ($fp!=null) && fclose($fp);
  }
  
  /** 设置下载速度 
  * @param int $speed 单位：KB，范围16KB ~ 20480KB（20MB）
  */
  public function setSpeed($speed){ 
    // 保留下限（16KB），新增上限20480KB（20MB），兼顾高速与服务器安全
    if(is_numeric($speed) && $speed>16 && $speed<=20480){ 
      $this->_speed = $speed; 
    } 
  }

  /**
   * 设置限速延迟（控制下载速度）
   * @param int $delay 微秒级延迟（1秒=1000000微秒）
   * 示例：$delay=500000 → 每次读取后暂停0.5秒，配合_speed=1024KB → 约2MB/s
   */
  public function setLimit($delay){
    if(is_numeric($delay) && $delay >= 0){
      $this->_limitDelay = (int)$delay;
    }
  }
  
  /** 获取header range信息 
  * @param int  $file_size 文件大小 
  * @return Array 
  */
  private function getRange($file_size){ 
    if(isset($_SERVER['HTTP_RANGE']) && !empty($_SERVER['HTTP_RANGE'])){
      list($a, $range) = explode("=",$_SERVER['HTTP_RANGE'],2);
      list($range) = explode(",",$range,2);
      list($start, $range_end) = explode("-", $range);
      if(!$range_end) {
        $range_end=$file_size-1;
      } else {
        $range_end=intval($range_end);
      }
      $range_end = min($range_end, $file_size - 1);
      $range = array('start'=>intval($start),'end'=>$range_end);
      return $range;  
    }
    return null;
  }
 
} // class end 
  
?> 