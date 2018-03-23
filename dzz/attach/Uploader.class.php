<?php

/**
 * Created by JetBrains PhpStorm.
 * User: taoqili
 * Date: 12-7-18
 * Time: 上午11: 32
 * UEditor编辑器通用上传类
 */
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
class Uploader
{
    private $fileField; //文件域名
    private $file; //文件上传对象
    private $base64; //文件上传对象
    private $config; //配置信息
    private $oriName; //原始文件名
    private $fileName; //新文件名
    private $fullName; //完整文件名,即从当前配置目录开始的URL
    private $filePath; //完整文件名,即从当前配置目录开始的URL
    private $fileSize; //文件大小
    private $fileType; //文件类型
    private $stateInfo; //上传状态信息,
    private $stateMap = array( //上传状态映射表，国际化用户需考虑此处数据的国际化
        "SUCCESS", //上传成功标记，在UEditor中内不可改变，否则flash判断会出错
        "文件大小超出 upload_max_filesize ",
        "文件大小超出 MAX_FILE_SIZE 限制",
        "文件未被完整上传",
        "没有文件被上传",
        "上传文件为空",
        "ERROR_TMP_FILE" => "临时文件错误",
        "ERROR_TMP_FILE_NOT_FOUND" => "找不到临时文件",
        "ERROR_SIZE_EXCEED" => "文件大小超出网站限制",
        "ERROR_TYPE_NOT_ALLOWED" => "文件类型不允许",
        "ERROR_CREATE_DIR" => "目录创建失败",
        "ERROR_DIR_NOT_WRITEABLE" => "目录没有写权限",
        "ERROR_FILE_MOVE" => "文件保存时出错",
        "ERROR_FILE_NOT_FOUND" => "找不到上传文件",
        "ERROR_WRITE_CONTENT" => "写入文件内容错误",
        "ERROR_UNKNOWN" => "未知错误",
        "ERROR_DEAD_LINK" => "链接不可用",
        "ERROR_HTTP_LINK" => "链接不是http链接",
        "ERROR_HTTP_CONTENTTYPE" => "链接contentType不正确"
    );

    /**
     * 构造函数
     * @param string $fileField 表单名称
     * @param array $config 配置项
     * @param bool $base64 是否解析base64编码，可省略。若开启，则$fileField代表的是base64编码的字符串表单名
     */
    public function __construct($fileField, $config, $type = "upload")
    {
        $this->fileField = $fileField;
        $this->config = $config;
        $this->type = $type;
        if ($type == "remote") {
            $this->saveRemote();
        } else if($type == "base64") {
            $this->upBase64();
        } else {
            $this->upFile();
        }

        $this->stateMap['ERROR_TYPE_NOT_ALLOWED'] = iconv('unicode', 'utf-8', $this->stateMap['ERROR_TYPE_NOT_ALLOWED']);
    }

    /**
     * 上传文件的主处理方法
     * @return mixed
     */
    private function upFile()
    {
        $file = $this->file = $_FILES[$this->fileField];
        if (!$file) {
            $this->stateInfo = $this->getStateInfo("ERROR_FILE_NOT_FOUND");
            return;
        }
        if ($this->file['error']) {
            $this->stateInfo = $this->getStateInfo($file['error']);
            return;
        } else if (!file_exists($file['tmp_name'])) {
            $this->stateInfo = $this->getStateInfo("ERROR_TMP_FILE_NOT_FOUND");
            return;
        } else if (!is_uploaded_file($file['tmp_name'])) {
            $this->stateInfo = $this->getStateInfo("ERROR_TMPFILE");
            return;
        }

        $this->oriName = $file['name'];
        $this->fileSize = $file['size'];
        $this->fileType = $this->getFileExt();
        $this->fileName = $this->getFileName();

        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
            return;
        }

        //检查是否不允许的文件格式
        if (!$this->checkType()) {
            $this->stateInfo = $this->getStateInfo("ERROR_TYPE_NOT_ALLOWED");
            return;
        }

        /*//创建目录失败
        if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
            $this->stateInfo = $this->getStateInfo("ERROR_CREATE_DIR");
            return;
        } else if (!is_writeable($dirname)) {
            $this->stateInfo = $this->getStateInfo("ERROR_DIR_NOT_WRITEABLE");
            return;
        }*/

        //移动文件
       
		$this->attach=$this->save($file['tmp_name'],$this->fileName);
		if($this->attach) $this->stateInfo = $this->stateMap[0];
		else $this->stateInfo=$this->stateMap["ERROR_FILE_MOVE"];
        
    }

    /**
     * 处理base64编码的图片上传
     * @return mixed
     */
    private function upBase64()
    {
        $base64Data = $_POST[$this->fileField];
        $img = base64_decode($base64Data);

        $this->oriName = $this->config['oriName'];
        $this->fileSize = strlen($img);
        $this->fileType = $this->getFileExt();
        $this->fileName = $this->getFileName();

        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
            return;
        }

       $temp=getglobal('setting/attachdir').'cache/'.random(5);

      //移动文件
        if (!(file_put_contents($temp, $img))) { //移动失败
            $this->stateInfo = $this->getStateInfo("ERROR_WRITE_CONTENT");
        } else { //移动成功
            $this->attach=$this->save($temp,$this->fileName);
			if($this->attach) $this->stateInfo = $this->stateMap[0];
			else $this->stateInfo=$this->stateMap["ERROR_FILE_MOVE"];
        }


    }

    /**
     * 拉取远程图片
     * @return mixed
     */
    private function saveRemote()
    {
        $imgUrl = htmlspecialchars($this->fileField);
        $imgUrl = str_replace("&amp;", "&", $imgUrl);

        //http开头验证
        if (strpos($imgUrl, "http") !== 0) {
            $this->stateInfo = $this->getStateInfo("ERROR_HTTP_LINK");
            return;
        }
        //获取请求头并检测死链
        $heads = get_headers($imgUrl,1);
        if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
            $this->stateInfo = $this->getStateInfo("ERROR_DEAD_LINK");
            return;
        }
		
       //格式验证(扩展名验证和Content-Type验证)
        $fileType = strtolower(preg_replace("/image\//i",'',$heads['Content-Type']));
		
        if (!in_array('.'.$fileType, $this->config['allowFiles'])) {
            $this->stateInfo = $this->getStateInfo("ERROR_HTTP_CONTENTTYPE");
            return;
        }

        //打开输出缓冲区并获取远程图片
        ob_start();
        $context = stream_context_create(
            array('http' => array(
                'follow_location' => false // don't follow redirects
            ))
        );
        readfile($imgUrl, false, $context);
        $img = ob_get_contents();
        ob_end_clean();
        preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $imgUrl, $m);

        $this->oriName = $m ? $m[1]:"remoteImage". $fileType;
        $this->fileSize = strlen($img);
        $this->fileType = $fileType;
      
        $this->fileName = $this->getFileName();
       

        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
            return;
        }

        $temp=getglobal('setting/attachdir').'cache/'.random(5);
        //移动文件
        if (!(file_put_contents($temp, $img))) { //移动失败
            $this->stateInfo = $this->getStateInfo("ERROR_WRITE_CONTENT");
        } else { //移动成功
            $this->attach=$this->save($temp,$this->fileName);
			if($this->attach) $this->stateInfo = $this->stateMap[0];
			else $this->stateInfo=$this->stateMap["ERROR_FILE_MOVE"];
        }

    }

    /**
     * 上传错误检查
     * @param $errCode
     * @return string
     */
    private function getStateInfo($errCode)
    {
        return !$this->stateMap[$errCode] ? $this->stateMap["ERROR_UNKNOWN"] : $this->stateMap[$errCode];
    }

    /**
     * 获取文件扩展名
     * @return string
     */
    private function getFileExt()
    {
        return strtolower(strrchr($this->oriName, '.'));
    }

    /**
     * 重命名文件
     * @return string
     */
   /* private function getFullName()
    {
        //替换日期事件
        $t = time();
        $d = explode('-', date("Y-y-m-d-H-i-s"));
        $format = $this->config["pathFormat"];
        $format = str_replace("{yyyy}", $d[0], $format);
        $format = str_replace("{yy}", $d[1], $format);
        $format = str_replace("{mm}", $d[2], $format);
        $format = str_replace("{dd}", $d[3], $format);
        $format = str_replace("{hh}", $d[4], $format);
        $format = str_replace("{ii}", $d[5], $format);
        $format = str_replace("{ss}", $d[6], $format);
        $format = str_replace("{time}", $t, $format);

        //过滤文件名的非法自负,并替换文件名
        $oriName = substr($this->oriName, 0, strrpos($this->oriName, '.'));
        $oriName = preg_replace("/[\|\?\"\<\>\/\*\\\\]+/", '', $oriName);
        $format = str_replace("{filename}", $oriName, $format);

        //替换随机字符串
        $randNum = rand(1, 10000000000) . rand(1, 10000000000);
        if (preg_match("/\{rand\:([\d]*)\}/i", $format, $matches)) {
            $format = preg_replace("/\{rand\:[\d]*\}/i", substr($randNum, 0, $matches[1]), $format);
        }

        $ext = $this->getFileExt();
        return $format . $ext;
    }*/

    /**
     * 获取文件名
     * @return string
     */
    private function getFileName () {
        return $this->oriName;
    }

   

    /**
     * 文件类型检测
     * @return bool
     */
    private function checkType()
    {
        return in_array($this->getFileExt(), $this->config["allowFiles"]);
    }

    /**
     * 文件大小检测
     * @return bool
     */
    private function  checkSize()
    {
        return $this->fileSize <= ($this->config["maxSize"]);
    }

    /**
     * 获取当前上传成功文件的各项信息
     * @return array
     */
    public function getFileInfo()
    {
        return array(
            "state" => $this->stateInfo,
            "url" => $this->attach['url'],
            "title" => $this->fileName,
            "original" => $this->oriName,
            "type" => $this->fileType,
            "size" => $this->fileSize,
			"attach"=>$this->attach
        );
    }
	public function getPath($filename,$dir='dzz'){
		global $_G;
			$ext = strtolower(trim($this->fileType,'.'));
			if($ext && in_array($ext ,getglobal('setting/unRunExts'))){
				$ext='dzz';
			}
		    $subdir = $subdir1 = $subdir2 = '';
			$subdir1 = date('Ym');
			$subdir2 = date('d');
			$subdir = $subdir1.'/'.$subdir2.'/';
			$target1=$dir.'/'.$subdir.'index.html';
			$target=$dir.'/'.$subdir;
			$target_attach=getglobal('setting/attachdir').$target1;
			$targetpath = dirname($target_attach);
			dmkdir($targetpath);
			return $target.date('His').''.strtolower(random(16)).'.'.$ext;
	 }
	public function save($file_path,$filename) {
	 global $_G;
	 	
        $md5=md5_file($file_path);
		$filesize=filesize($file_path);
		if($md5 && $attach=DB::fetch_first("select * from %t where md5=%s and filesize=%d",array('attachment',$md5,$filesize))){
			$attach['filename']=$filename;
			$attach['filetype']=trim($this->fileType,'.');
			if(in_array(strtolower($attach['filetype']),array('png','jpeg','jpg','gif','bmp'))){
				$attach['url']=C::t('attachment')->getThumbByAid($attach,0,0,1);
				$attach['img']=C::t('attachment')->getThumbByAid($attach,256,256);
				$attach['isimage']=1;
			}else{
				$attach['img']=geticonfromext($attach['filetype']);
				$attach['url']=(DZZSCRIPT?DZZSCRIPT:'index.php').'?mod=io&op=getStream&path='.dzzencode('attach::'.$attach['aid']);
				$attach['isimage']=0;
			}
			$attach['dpath']=$attach['apath']=dzzencode('attach::'.$attach['aid']);
			$attach['filesize']=formatsize($attach['filesize']);
			@unlink($file_path);
			return $attach;
		}else{
			$target=self::getPath($filename);
			
			$ext = strtolower(trim($this->fileType,'.'));
			if($ext && in_array($ext ,getglobal('setting/unRunExts'))){
				$unrun=1;
			}else{
				$unrun=0;
			}
			$filepath=$_G['setting']['attachdir'].$target;
			$handle=fopen($file_path, 'r');
			$handle1=fopen($filepath,'w');
			while (!feof($handle)) {
			   fwrite($handle1,fread($handle, 8192));
			}
			fclose($handle);
			fclose($handle1);
			@unlink($file_path);
			
			$filesize=filesize($filepath);
			$remote=0;
			
        	$attach=array(
			
				'filesize'=>$filesize,
				'attachment'=>$target,
				'filetype'=>strtolower($ext),
				'filename' =>$filename,
				'remote'=>$remote,
				'copys' => 0,
				'md5'=>$md5,
				'unrun'=>$unrun,
				'dateline' => $_G['timestamp'],
			);
			
			if($attach['aid']=C::t('attachment')->insert($attach,1)){
				C::t('local_storage')->update_usesize_by_remoteid($attach['remote'],$attach['filesize']);
				dfsockopen(getglobal('siteurl').'misc.php?mod=movetospace&aid='.$attach['aid'].'&remoteid=0',0, '', '', FALSE, '',1);
				if(in_array(strtolower($attach['filetype']),array('png','jpeg','jpg','gif','bmp'))){
					$attach['url']=C::t('attachment')->getThumbByAid($attach,0,0,1);
					$attach['img']=C::t('attachment')->getThumbByAid($attach,256,256);
					$attach['isimage']=1;
				}else{
					$attach['img']=geticonfromext($attach['filetype']);
					$attach['url']=(DZZSCRIPT?DZZSCRIPT:'index.php').'?mod=io&op=getStream&path='.dzzencode('attach::'.$attach['aid']);
					$attach['isimage']=0;
				}
				$attach['dpath']=$attach['apath']=dzzencode('attach::'.$attach['aid']);
				$attach['filesize']=formatsize($attach['filesize']);
				return $attach;
			}else{
				return false;
			}
		}
    }

}