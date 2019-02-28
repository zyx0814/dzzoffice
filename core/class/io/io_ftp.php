<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
@set_time_limit(0);
@ini_set('max_execution_time',0);
class io_ftp extends io_api
{

    const T ='connect_ftp';
    const BZ='ftp';
    var $perm=0;
    var $icosdatas=array();
    var $error='';
    var $conn=null;
    var $encode='GBK';
    var $_root='';
    var $_rootname='';
    var $filecachetime = 300;
    public function __construct($path) {
        $bzarr=explode(':',$path);
        $ftpid=trim($bzarr[1]);
        if($config=DB::fetch_first("select * from ".DB::table(self::T)." where  id='{$ftpid}'")){
            $this->_root='ftp:'.$config['id'].':';
            $this->encode=$config['charset'];
            $this->_rootname=$config['cloudname'];
            if($config['port'] == 22 || $config['ssl'] == 2){
                $ftp=new dzz_sftp($config);
            }else{
                $ftp=new dzz_ftp($config);
            }
            if($ftp->error()){
                $this->error=$ftp->error();
                return $this;
            }
            if($ftp->connect()){
                $this->conn=$ftp;
            }else{
                $this->error='ftp not connect';
            }

        }else{
            $this->error='need authorize';
        }
        $this->perm=perm_binPerm::getMyPower();
        return $this;
    }

    public function MoveToSpace($path,$attach){
        global $_G;
        $filename=substr($path,strrpos($path,'/')+1);;
        $fpath=substr($path,0,strrpos($path,'/'));
        $obz=io_remote::getBzByRemoteid($attach['remote']);
        if($obz=='dzz'){
            $opath='attach::'.$attach['aid'];
        }else{
            $opath=$obz.'/'.$attach['attachment'];
        }
        //exit($opath.'==='.$fpath.'/'.$filename);
        if($re=self::multiUpload($opath,$fpath,$filename,$attach,'overwrite')){
            if($re['error']) return $re;
            else{
                return true;
            }
        }
        return false;
    }



    //根据路径获取目录树的数据；
    public function getFolderDatasByPath($path){

        $bzarr=self::parsePath($path);

        $spath=preg_replace("/\/+/",'/',$bzarr['path1']);
        if($spath){
            $patharr=explode('/',trim($spath,'/'));
        }else{
            $patharr=array();
        }
        //if(empty($patharr[0])) unset($patharr[0]);
        $folderarr=array();
        for($i=0;$i<=count($patharr);$i++){
            $path1=$bzarr['bz'];
            for($j=0;$j<$i;$j++){
                if($patharr[$j]) $path1.='/'.$patharr[$j];
            }
            if($arr=self::getMeta($path1)){
                if(isset($arr['error'])) continue;
                $folder=self::getFolderByIcosdata($arr);
                $folderarr[$folder['fid']]=$folder;
            }
        }
        return $folderarr;
    }
    public function authorize($refer){
        global $_G,$_GET,$clouds;;
        if(empty($_G['uid'])) {
            dsetcookie('_refer', rawurlencode(BASESCRIPT.'?mod=connect&op=oauth&bz=ftp'));
            showmessage('to_login', '', array(), array('showmsg' => true, 'login' => 1));
        }
        if(submitcheck('ftpsubmit')){
            $config=$_GET['config'];
            $config['password']=authcode($config['password'], 'ENCODE', md5(getglobal('config/security/authkey')));
            $config['bz']='ftp';
            $uid=defined('IN_ADMIN')?0:$_G['uid'];
            $config['on']=1;
            if($config['port'] == 22 || $config['ssl'] == 2){
                $ftp = new dzz_sftp($config);
            }else{
                $ftp = new dzz_ftp($config);
            }
            if($ftp->error()) showmessage(lang('ftp_Parameter_setting_error').$ftp->error(),dreferer());
            if($ftp->connect()){
                $config['uid']=$uid;
                if($id=DB::result_first("select id from %t where uid=%d and host=%s and port=%d and username=%s",array(self::T,$uid,$config['host'],$config['port'],$config['username']))){
                    DB::update(self::T,$config,"id ='{$id}'");
                }else{
                    $config['dateline']=TIMESTAMP;
                    $id=DB::insert(self::T,$config,1);
                }
                if(defined('IN_ADMIN')){
                    $setarr=array('name'=>$config['cloudname'],
                        'bz'=>'ftp',
                        'isdefault'=>0,
                        'dname'=>self::T,
                        'did'=>$id,
                        'dateline'=>TIMESTAMP
                    );
                    if(!DB::result_first("select COUNT(*) from %t where did=%d and dname=%s ",array('local_storage',$id,self::T))){
                        C::t('local_storage')->insert($setarr);
                    }
                    showmessage('do_success',BASESCRIPT.'?mod=cloud&op=space');
                }else{
                    showmessage('do_success',$refer?$refer:BASESCRIPT.'?mod=connect');
                }
            }else{
                showmessage('try_connect_FTP_failed',dreferer());
            }

        }else{
            include template('oauth_ftp');
        }
    }
    public function parsePath($path){
        $bzarr=explode(':',$path);
        return array('bz'=>$bzarr[0].':'.$bzarr[1].':','path'=>diconv($bzarr[2],CHARSET,$this->encode),'path1'=>$bzarr[2]);
    }
    //更改权限
    public function chmod($path,$chmod=0777,$son=0){
        if($this->error)  return array('error'=>$this->error);
        $bzarr=self::parsePath($path);
        $chmod=eval("return({$chmod});");
        if($son){
            return $this->conn->ftp_chmod_son($bzarr['path'],$chmod);
        }else{
            //showmessage($chmod.'======='.$this->conn->ftp_chmod($bzarr['path'],$chmod));
            return $this->conn->ftp_chmod($bzarr['path'],$chmod);
        }
    }
    //获取文件流；
    //$path: 路径
    function getStream($path){
        global  $_G;
        $arr=self::parsePath($path);
        $filename = basename($path);
        $cachetarget =  $_G['setting']['attachdir'].'cache/'.$filename;
        if(file_exists($cachetarget) && (filectime($cachetarget) > TIMESTAMP - $this->filecachetime)){
            return $cachetarget;
        }else{
            $config=$this->conn->config;
            if($config['ssl']) $scheme='ftps://';
            else $scheme='ftp://';
            if($config['port'] == 22 || $config['ssl'] == 2){
                $sftp = ssh2_sftp($this->conn->connectid);
                $url = 'ssh2.sftp://'.$sftp.$arr['path'];
            }else{
                $url = $scheme.urlencode($config['username']).':'.urlencode($config['password']).'@'.$config['host'].':'.$config['port'].$arr['path'];
            }
            if(file_put_contents($cachetarget,file_get_contents($url)) === false){
                $cachetarget = false;
            }
        }
        return $cachetarget;

    }
    //获取文件流地址；
    //$path: 路径
    function getFileUri($path){
        return getglobal('siteurl').'index.php?mod=io&op=getStream&path='.dzzencode($path);
    }
    public function deleteThumb($path){
        global $_G;
        $imgcachePath='./imgcache/';
        $cachepath=preg_replace("/\/+/",'/',str_replace(':','/',$path));
        foreach($_G['setting']['thumbsize'] as $value){
           	$target = $imgcachePath . ($cachepath) . '.' . $value['width'] . '_' . $value['height'] . '_1.jpeg';
			$target1 = $imgcachePath . ($cachepath) . '.' . $value['width'] . '_' . $value['height'] . '_2.jpeg';
			@unlink($_G['setting']['attachdir'].$target);
			@unlink($_G['setting']['attachdir'].$target1);
        }
    }
    public function createThumb($path,$size,$width=0,$height=0,$thumbtype = 1){
        global $_G;
        if(intval($width)<1) $width=$_G['setting']['thumbsize'][$size]['width'];
        if(intval($height)<1) $height=$_G['setting']['thumbsize'][$size]['height'];
        $imgcachePath='imgcache/';
        $cachepath=str_replace(':','/',$path);
        $cachepath=preg_replace("/\/+/",'/',str_replace(':','/',$path));
		$target = $imgcachePath . ($cachepath) . '.' . $width . '_' . $height. '_'.$thumbtype.'.jpeg';
        if(@getimagesize($_G['setting']['attachdir'].'./'.$target)){
            return 2;//已经存在缩略图
        }
        //调用挂载点程序生成缩略图绝对和相对地址；
        $fileurls=array();
        Hook::listen('thumbnail',$fileurls,$path);
        if(!$fileurls){
            $fileurls=array('fileurl'=>self::getFileUri($path),'filedir'=>self::getStream($path));
        }
        //非图片类文件的时候，直接获取文件后缀对应的图片
        if(!$imginfo = @getimagesize($fileurls['filedir'])){
            return -1; //非图片不能生成
        }
        if(($imginfo[0]<$width && $imginfo[1]<$height) ) {
            return 3;//小于要求尺寸，不需要生成
        }
        //生成缩略图
        include_once libfile('class/image');
        $target_attach=$_G['setting']['attachdir'].'./'.$target;
        $targetpath = dirname($target_attach);
        dmkdir($targetpath);
        $image=new image();
        if($thumb = $image->Thumb($fileurls['filedir'], $target, $width, $height,$thumbtype)){
            return 1;//生成缩略图成功
        }else{
            return 0;//生成缩略图失败
        }

    }
    public function getThumb($path,$width,$height,$original,$returnurl=false,$thumbtype = 1){
        global $_G;
        $imgcachePath='imgcache/';
        $cachepath=str_replace(':','/',$path);
        $cachepath=preg_replace("/\/+/",'/',str_replace(':','/',$path));
		$target = $imgcachePath . ($cachepath) . '.' . $width . '_' . $height. '_'.$thumbtype.'.jpeg';
        if(!$original && @getimagesize($_G['setting']['attachdir'].'./'.$target)){
            if($returnurl) return $_G['setting']['attachurl'].'/'.$target;
            IO::output_thumb($_G['setting']['attachdir'].'./'.$target);
        }
        //调用挂载点程序生成缩略图绝对和相对地址；
        $fileurls=array();
        Hook::listen('thumbnail',$fileurls,$path);
        if(!$fileurls){
            $fileurls=array('fileurl'=>self::getFileUri($path),'filedir'=>self::getStream($path));
        }
        //非图片类文件的时候，直接获取文件后缀对应的图片
        if(!$imginfo = @getimagesize($fileurls['filedir'])){
            $imgurl= geticonfromext($data['ext'],$data['type']);
            if ($returnurl) return $imgurl;
            IO::output_thumb($imgurl);
        }
        //返回原图的时候或图片小于缩略图宽高的不生成直接返回原图
        if ($original || ($imginfo[0] < $width && $imginfo[1] < $height)) {
            if ($returnurl) return $fileurls['fileurl'];
            IO::output_thumb($fileurls['filedir']);
        }
        //生成图片缩略图
        $imgurl = $fileurls['filedir'];
        $target_attach = $_G['setting']['attachdir'] .'./'. $target;
        $targetpath = dirname($target_attach);
        dmkdir($targetpath);
        require_once libfile('class/image');
        $image = new image();
        if($thumb = $image->Thumb($imgurl, $target, $width, $height,$thumbtype)){
            if($returnurl) return $_G['setting']['attachurl'].'/'.$target;
            IO::output_thumb($_G['setting']['attachdir'].'./'.$target);
        }else{
            if($returnurl) return $imgurl;
            IO::output_thumb($imgurl);
        }

    }
    //重写文件内容
    //@param number $path  文件的路径
    //@param string $data  文件的新内容
    public function setFileContent($path,$data){

        $bzarr=self::parsepath($path);
        $temp = tempnam(sys_get_temp_dir(), 'tmpimg_');
        if(!file_put_contents($temp,$data)){
            return array(lang('error_writing_temporary_file'));
        }
        if($this->conn->upload($temp,$bzarr['path'])){

        }else{
            return array('error'=>$this->conn->error());
        }
        $icoarr=self::getMeta($path);
        if($icoarr['type']=='image'){
            $icoarr['img'].='&t='.TIMESTAMP;
        }
        return $icoarr;
    }

    /**
     * 移动文件到目标位置
     * @param string $opath 被移动的文件路径
     * @param string $path 目标位置（可能是同一api内或跨api，这两种情况分开处理）
     * @return icosdatas
     */
    public function CopyTo($opath,$path,$iscopy){
        $oarr=self::parsePath($opath);
        $arr=IO::parsePath($path);
        $data=self::getMeta($opath);
        switch($data['type']){
            case 'folder'://创建目录
                if($re=IO::CreateFolder($path,$data['name'])){
                    $data['newdata']=$re['icoarr'];
                    $data['success']=true;
                    $contents=self::listFiles($opath);
                    foreach($contents as $key=>$value){
                        $data['contents'][$key]=self::CopyTo($value['path'],$re['folderarr']['path']);
                    }
                }
                break;
            default:

                if($re=IO::multiUpload($opath,$path,$data['name'])){
                    if($re['error']) $data['success']=$re['error'];
                    else{
                        $data['newdata']=$re;
                        $data['success']=true;
                    }
                }
        }


        return $data;
    }
    /*
     * 分块上传文件
     * param $file:文件路径（可以是url路径，需要服务器开启allow_url_fopen);
    */
    public function multiUpload($opath,$path,$filename,$attach=array(),$ondup="newcopy"){
        global $_G;
        $partsize=1024*1024*5; //分块大小2M
        if($attach){
            $data=$attach;
            $data['size']=$attach['filesize'];
        }else{
            $data=IO::getMeta($opath);
            if($data['error']) return $data;
        }
        $size=$data['size'];
        if(is_array($filepath=IO::getStream($opath))){
            return array('error'=>$filepath['error']);
        }

        //exit(($size<$partsize).'===='.$size.'==='.$filepath.'===='.$path);
        if($size<$partsize){
            //获取文件内容
            $fileContent='';
            if(!$handle=fopen($filepath, 'rb')){
                return array('error'=>lang('open_file_error'));
            }
            while (!feof($handle)) {
                $fileContent .= fread($handle, 8192);
                //if(strlen($fileContent)==0) return array('error'=>'文件不存在');
            }
            fclose($handle);
            //exit('upload');
            return self::upload_by_content($fileContent,$path,$filename);
        }else{ //分片上传

            $partinfo=array('ispart'=>true,'partnum'=>0,'iscomplete'=>false);
            if(!$handle=fopen($filepath, 'rb')){
                return array('error'=>lang('open_file_error'));
            }
            $cachefile=$_G['setting']['attachdir'].'./cache/'.md5($opath).'.dzz';
            $fileContent='';
            while (!feof($handle)) {
                $fileContent.=fread($handle, 8192);
                if(strlen($fileContent)==0) return array('error'=>lang('file_not_exist1'));
                if(strlen($fileContent)>=$partsize){
                    if($partinfo['partnum']*$partsize+strlen($fileContent)>=$size) $partinfo['iscomplete']=true;
                    $partinfo['partnum']+=1;
                    file_put_contents($cachefile,$fileContent);
                    $re=self::upload($cachefile,$path,$filename,$partinfo);
                    if($re['error']) return $re;
                    if($partinfo['iscomplete']){
                        @unlink($cachefile);
                        return $re;
                    }
                    $fileContent='';
                }
            }
            fclose($handle);
            if(!empty($fileContent)){
                $partinfo['partnum']+=1;
                $partinfo['iscomplete']=true;
                file_put_contents($cachefile,$fileContent);
                $re=self::upload($cachefile,$path,$filename,$partinfo);
                if($re['error']) return $re;
                if($partinfo['iscomplete']){
                    @unlink($cachefile);
                    return $re;
                }
            }
        }
    }
    /**
     * 获取指定文件夹下的文件列表
     * @param string $path 文件路径
     * @param string $by 排序字段，缺省根据文件类型排序，time（修改时间），name（文件名），size（大小，注意目录无大小）
     * @param string $order asc或desc，缺省采用降序排序
     * @param string $limit 返回条目控制，参数格式为：n1-n2。返回结果集的[n1, n2)之间的条目，缺省返回所有条目。n1从0开始。
     * @param string $force 读取缓存，大于0：忽略缓存，直接调用api数据，常用于强制刷新时。
     * @return icosdatas
     */
    function listFiles($path,$by='time',$order='desc',$limit='',$force=0){
        if($this->error)  return array('error'=>$this->error);
        $bzarr=self::parsePath($path);

        $data = $this->conn->ftp_list($bzarr['path']);
        //print_r($data);exit(diconv($bzarr['path'],'GBK',CHARSET));
        if($this->conn->error()) return array('error'=>$this->conn->error());
        $icosdata=array();
        foreach($data as $key => $value){
            $icoarr=self::_formatMeta($value,$bzarr['bz']);
            $icosdata[$icoarr['icoid']]=$icoarr;
        }
        return $icosdata;
    }
    /*
     *获取文件的meta数据
     *返回标准的icosdata
     *$force>0 强制刷新，不读取缓存数据；
    */
    function getMeta($path,$force=0){
        $bzarr=self::parsePath($path);
        $meta=array();
        if($path==$this->_root){
            $meta['path']='';
            $meta['name']=$this->_rootname;
            $meta['type']='folder';
            $meta['size']='-';
            $meta['flag']=self::BZ;
        }else{
            if(!$meta=$this->conn->ftp_meta($bzarr['path'])){
                $meta['path']=$bzarr['path1'];
                $meta['name']=substr(strrchr($bzarr['path1'], '/'), 1);
                if($this->conn->ftp_isdir($bzarr['path'])){
                    $meta['type']='folder';
                    $meta['size']='-';
                }else{
                    $meta['type']='file';
                    $meta['size']=$this->conn->ftp_size($bzarr['path']);
                    $meta['mtime']=$this->conn->ftp_mdtm($bzarr['path']);
                    if($meta['mtime']<0) $meta['mtime']=0;
                }

            }
        }
        $icosdata=self::_formatMeta($meta,$bzarr['bz']);
        return $icosdata;
    }
    //将api获取的meta数据转化为icodata
    function _formatMeta($meta,$bz){
        global $_G,$documentexts,$imageexts;
        //判断是否为根目录
        $icosdata=array();

        if($meta['type']=='folder'){
            $icoarr=array(
                'icoid'=>md5(($bz.$meta['path'])),
                'path'=>$bz.$meta['path'],
                'dpath'=>dzzencode($bz.$meta['path']),
                'bz'=>($bz),
                'gid'=>0,
                'name'=>$meta['name'],
                'username'=>$_G['username'],
                'uid'=>$_G['uid'],
                'oid'=>md5(($bz.$meta['path'])),
                'img'=>'dzz/images/default/system/folder.png',
                'type'=>'folder',
                'ext'=>'',
                'pfid'=>md5(str_replace(strrchr($meta['path'], '/'), '',$bz.$meta['path'])),
                'size'=>'-',
                'dateline'=>intval($meta['mtime']),
                'flag'=>$meta['flag']?$meta['flag']:'',
                'mod'=>$meta['mod']
            );

            $icoarr['fsize']='-';
            $icoarr['ftype']=getFileTypeName($icoarr['type'],$icoarr['ext']);
            if(!$icoarr['dateline']) $icoarr['fdateline']='-';
            else $icoarr['fdateline']=dgmdate($icoarr['dateline']);
            $icosdata=$icoarr;

        }else{
            $pathinfo = pathinfo($meta['path']);
            $ext = strtolower($pathinfo['extension']);
            if(in_array($ext,$imageexts)) $type='image';
            elseif(in_array($ext,$documentexts)) $type='document';
            else $type='attach';

            if($type=='image'){
                $img=$_G['siteurl'].DZZSCRIPT.'?mod=io&op=thumbnail&size=small&path='.dzzencode($bz.$meta['path']);
                $url=$_G['siteurl'].DZZSCRIPT.'?mod=io&op=thumbnail&size=large&path='.dzzencode($bz.$meta['path']);
            }else{
                $img=geticonfromext($ext,$type);
                $url=$_G['siteurl'].DZZSCRIPT.'?mod=io&op=getStream&path='.dzzencode($bz.$meta['path']);
            }
            $icoarr=array(
                'icoid'=>md5(($bz.$meta['path'])),
                'path'=>($bz.$meta['path']),
                'dpath'=>dzzencode($bz.$meta['path']),
                'bz'=>($bz),
                'gid'=>0,
                'name'=>$meta['name'],
                'username'=>$_G['username'],
                'uid'=>$_G['uid'],
                'oid'=>md5(($bz.$meta['path'])),
                'img'=>$img,
                'url'=>$url,
                'type'=>$type,
                'ext'=>strtolower($ext),
                'pfid'=>md5(str_replace(strrchr($meta['path'], '/'), '',$bz.$meta['path'])),
                'size'=>$meta['size'],
                'dateline'=>intval($meta['mtime']),
                'flag'=>'',
                'mod'=>$meta['mod']
            );

            $icoarr['fsize']=formatsize($icoarr['size']);
            $icoarr['ftype']=getFileTypeName($icoarr['type'],$icoarr['ext']);
            if(!$icoarr['dateline']) $icoarr['fdateline']='-';
            else $icoarr['fdateline']=dgmdate($icoarr['dateline']);
            $icosdata=$icoarr;
        }

        return $icosdata;
    }
    //通过icosdata获取folderdata数据
    function getFolderByIcosdata($icosdata){
        global $_GET;
        $folder=array();
        if($icosdata['type']=='folder'){
            $folder=array('fid'=>$icosdata['oid'],
                'path'=>$icosdata['path'],
                'fname'=>$icosdata['name'],
                'uid'=>$icosdata['uid'],
                'pfid'=>$icosdata['pfid'],
                'iconview'=>$_GET['iconview']?intval($_GET['iconview']):1,
                'disp'=>$_GET['disp']?intval($_GET['disp']):1,
                'perm'=>$this->perm,
                'hash'=>$icosdata['hash'],
                'bz'=>$icosdata['bz'],
                'gid'=>$icosdata['gid'],
                'fsperm'=>perm_FolderSPerm::flagPower('external')
            );

        }
        return $folder;
    }
    //获得文件内容；
    function getFileContent($path){
        /*$url=self::getStream($path);
        $config=$this->conn->config;
        if($config['port'] == 22 || $config['ssl'] == 2){
            $sftp = ssh2_sftp($this->conn->connectid);
            $url = str_replace('{stfp}',$sftp);
        }
        var_dump(file_get_contents($url));
        die;*/
        $arr=self::parsePath($path);

        $config=$this->conn->config;
        if($config['ssl']) $scheme='ftps://';
        else $scheme='ftp://';
        if($config['port'] == 22 || $config['ssl'] == 2){
            $sftp = ssh2_sftp($this->conn->connectid);
            $url =  'ssh2.sftp://'.$sftp.$arr['path'];
        }else{
            $url =  $scheme.urlencode($config['username']).':'.urlencode($config['password']).'@'.$config['host'].':'.$config['port'].$arr['path'];
        }
        return file_get_contents($url);
    }
    //打包下载文件
    public function zipdownload($paths,$filename){
        global $_G;
        $paths=(array)$paths;
        set_time_limit(0);

        if(empty($filename)){
            $meta=self::getMeta($paths[0]);
            $filename=$meta['name'].(count($paths)>1?lang('wait'):'');
        }
        $filename=(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($filename) : $filename);
        include_once libfile('class/ZipStream');

        $zip = new ZipStream($filename.".zip");
        //$zip->setComment("$meta[name] " . date('l jS \of F Y h:i:s A'));
        $data=self::getFolderInfo($paths,'',$zip);
        /*foreach($data as $value){
             $zip->addLargeFile(fopen($value['url'],'rb'), $value['position'], $value['dateline']);
        }*/
        $zip->finalize();
    }
    public function getFolderInfo($paths,$position='',&$zip){
        static $data=array();
        try{
            foreach($paths as $path){
                $arr=self::parsePath($path);

                $meta=self::getMeta($path);

                switch($meta['type']){
                    case 'folder':
                        $lposition=$position.$meta['name'].'/';
                        $contents=self::listFiles($path);
                        $arr=array();
                        foreach($contents as $key=>$value){
                            $arr[]=$value['path'];
                        }
                        if($arr) self::getFolderInfo($arr,$lposition,$zip);
                        break;
                    default:
                        $config=$this->conn->config;
                        if($config['ssl']) $scheme='ftps://';
                        else $scheme='ftp://';
                        if($config['port'] == 22 || $config['ssl'] == 2){
                            $sftp = ssh2_sftp($this->conn->connectid);
                            $url =  'ssh2.sftp://'.$sftp.$arr['path'];
                        }else{
                            $url =  $scheme.urlencode($config['username']).':'.urlencode($config['password']).'@'.$config['host'].':'.$config['port'].$arr['path'];
                        }
                        //$meta['url']=self::getStream($meta['path']);
                        $meta['url']=$url;
                        $meta['position']=$position.$meta['name'];
                        //$data[$meta['icoid']]=$meta;
                        $zip->addLargeFile(fopen($meta['url'],'rb'), $meta['position'], $meta['dateline']);
                }
            }

        }catch(Exception $e){
            //var_dump($e);
            $data['error']=$e->getMessage();
            return $data;
        }
        return $data;
    }

    //下载文件
    public function download($paths,$filename){
        global $_G;
        $paths=(array)$paths;
        if(count($paths)>1){
            self::zipdownload($paths,$filename);
            exit();
        }else{
            $path=$paths[0];
        }
        $path=rawurldecode($path);
        try {
            // Download the file
            $file=self::getMeta($path);
            if($file['type']=='folder'){
                self::zipdownload($path);
                exit();
            }
            $arr=self::parsePath($path);
            $config=$this->conn->config;
            if($config['ssl']) $scheme='ftps://';
            else $scheme='ftp://';
            if($config['port'] == 22 || $config['ssl'] == 2){
                $sftp = ssh2_sftp($this->conn->connectid);
                $url =  'ssh2.sftp://'.$sftp.$arr['path'];
            }else{
                $url =  $scheme.urlencode($config['username']).':'.urlencode($config['password']).'@'.$config['host'].':'.$config['port'].$arr['path'];
            }
            //$url=self::getStream($path);
            if(!$fp = @fopen($url, 'rb')) {
                topshowmessage(lang('file_not_exist1'));
            }

            $chunk = 10 * 1024 * 1024;
            //$file['data'] = self::getFileContent($path);
            //if($file['data']['error']) IO::topshowmessage($file['data']['error']);
            $file['name'] = '"'.(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($file['name']) : $file['name']).'"';
            $d=new FileDownload();
            $d->download($url,$file['name'],$file['size'],$file['dateline'],true);
            exit();
            dheader('Date: '.gmdate('D, d M Y H:i:s', $file['dateline']).' GMT');
            dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', $file['dateline']).' GMT');
            dheader('Content-Encoding: none');
            dheader('Content-Disposition: attachment; filename='.$file['name']);
            dheader('Content-Type: application/octet-stream');
            dheader('Content-Length: '.$file['size']);

            @ob_end_clean();
            if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
            while (!feof($fp)) {
                echo fread($fp, $chunk);
                @ob_flush();  // flush output
                @flush();
            }
            @fclose($fp);
            exit();
        } catch (Exception $e) {
            // The file wasn't found at the specified path/revision
            //echo 'The file was not found at the specified path/revision';
            topshowmessage($e->getMessage());
        }
    }


    public function rename($path,$name){
        $arr=self::parsePath($path);
        $patharr=explode('/',$arr['path1']);
        $arr['path2']='';
        $pathinfo = pathinfo($arr['path1']);
        $ext = strtolower($pathinfo['extension']);
        foreach($patharr as $key =>$value){
            if($key>=count($patharr)-1) break;
            $arr['path2'].=$value.'/';
        }
        $arr['path2'].=$ext?(preg_replace("/\.\w+$/i",'.'.$ext,$name)):$name;
        $arr['path3']=diconv($arr['path2'],CHARSET,$this->encode);
        if($arr['path1']!=$arr['path2']){
            if($this->conn->ftp_rename($arr['path'],$arr['path3'])){
            }else{
                return array('error'=>$this->conn->error());
            }
        }
        return self::getMeta($arr['bz'].$arr['path2']);
    }


    //删除原内容
    //$path: 删除的路径
    //$bz: 删除的api;
    //$data：可以删除的id数组（当剪切的时候，为了保证数据不丢失，目标位置添加成功后将此id添加到data数组，
    //删除时如果$data有数据，将会只删除id在$data中的数据；
    //如果删除的是目录或下级有目录，需要判断此目录内是否所有元素都在删除的id中，如果有未删除的元素，则此目录保留不会删除；
    //$force 真实删除，不放入回收站
    public function Delete($path,$force=false){
        //global $dropbox;
        $bzarr=self::parsePath($path);
        if($this->error){
            return array('error'=>$this->error);
        }

        if($this->conn->ftp_isdir($bzarr['path'])){
            if($this->conn->ftp_rmdir_force($bzarr['path'])){
                return array('icoid'=>md5(($path)),
                    'name'=>substr(strrchr($path, '/'), 1),
                );
            }else{
                return array('icoid'=>md5(($path)),'error'=>$this->conn->error());
            }
        }else{
            if($this->conn->ftp_delete($bzarr['path'])){
                return array('icoid'=>md5(($path)),
                    'name'=>substr(strrchr($path, '/'), 1),
                );
            }else{
                return array('icoid'=>md5(($path)),'error'=>$this->conn->error());
            }
        }
    }
    public function createFolderByPath($path, $pfid = '',$noperm = false)
    {
        $data = array();
        if($this->conn->ftp_mkdir($path)){
            $data = $this->conn->ftp_meta($path);
        }
        return $data;
    }
    //添加目录
    //$fname：目录路径;
    //$container：目标容器
    //$bz：api;
    public function CreateFolder($path,$fname){
        global $_G;
        $path=$path.'/'.$fname;
        $bzarr=self::parsePath($path);
        $return=array();
        if($this->conn->ftp_mkdir($bzarr['path'])){
            $meta['path']=$bzarr['path1'];
            $meta['name']=substr(strrchr($bzarr['path1'], '/'), 1);
            $meta['type']='folder';
            $meta['size']='-';
            $icoarr=self::_formatMeta($meta,$bzarr['bz']);
            $folderarr=self::getFolderByIcosdata($icoarr);
            return array('folderarr'=>$folderarr,'icoarr'=>$icoarr);
        }else{
            return array('error'=>$this->conn->error());
        }

    }
    //获取不重复的目录名称
    public function getFolderName($name,$path){
        static $i=0;
        if(!$this->icosdatas) $this->icosdatas=self::listFiles($path);
        $names=array();
        foreach($this->icosdatas as $value){
            $names[]=$value['name'];
        }
        if(in_array($name,$names)){
            $name=str_replace('('.$i.')','',$name).'('.($i+1).')';
            $i+=1;
            return self::getFolderName($name,$path);
        }else {
            return $name;
        }
    }
    private function getPartInfo($content_range){
        $arr=array();
        if(!$content_range){
            $arr['ispart']=false;
            $arr['iscomplete']=true;
        }elseif(is_array($content_range)){
            $arr['ispart']=true;
            $partsize=getglobal('setting/maxChunkSize');
            $arr['partnum']=ceil(($content_range[2]+1)/$partsize);
            if(($content_range[2]+1)>=$content_range[3]){
                $arr['iscomplete']=true;
            }else{
                $arr['iscomplete']=false;
            }
        }else{
            return false;
        }
        return $arr;
    }
    private function getCache($path){
        $cachekey='ftp_upload_'.md5($path);
        if($cache=C::t('cache')->fetch($cachekey)){
            return $cache['cachevalue'];
        }else{
            return false;
        }
    }
    private function saveCache($path,$str){
        global $_G;
        $cachekey='ftp_upload_'.md5($path);
        C::t('cache')->insert(array(
            'cachekey' => $cachekey,
            'cachevalue' => $str,
            'dateline' => $_G['timestamp'],
        ), false, true);
    }
    private function deleteCache($path){
        $cachekey='ftp_upload_'.md5($path);
        C::t('cache')->delete($cachekey);
    }
    public function uploadStream($file,$filename,$path,$relativePath,$content_range){
		 if($this->error){
            return array('error'=>$this->error);
        }
        $data=array();
        //exit($path.'===='.$filename);

        //处理目录(没有分片或者最后一个分片时创建目录
        $arr=self::getPartInfo($content_range);

        if($relativePath && ($arr['iscomplete'])){
            $path1=$path;
            $patharr=explode('/',$relativePath);
            foreach($patharr as $key=> $value){
                if(!$value){
                    unset($patharr[$key]);
                    continue;
                }
                if($patharr[$key-1]) $path1.='/'.$patharr[$key-1];

                $re=self::CreateFolder($path1,$value);
                if(isset($re['error'])){
                    return $re;
                }else{
                    if($key==0){
                        $data['icoarr'][]=$re['icoarr'];
                        $data['folderarr'][]=$re['folderarr'];
                    }
                }

            }
            //$path.='/'.implode('/',$patharr);
        }
        if($relativePath) $path=$path.'/'.trim($relativePath,'/');
        if($arr['ispart']){
            if($re1=self::upload($file,$path,$filename,$arr)){
                if($re1['error']){
                    return $re1;
                }
                if($arr['iscomplete']){
                    if(empty($re1['error'])){

                        $data['icoarr'][] = $re1;
                        return $data;
                    }else{
                        $data['error'] = $re1['error'];
                        return $data;
                    }
                }else{
                    return true;
                }
            }
        }else{
            $re1=self::upload($file,$path,$filename);
            if(empty($re1['error'])){
                /*if($relativePath){
                    $icoarr=self::getMeta($path);
                    $data['icoarr'][]=$icoarr;
                    $data['folderarr'][]=self::getFolderByIcosdata($icoarr);
                }*/
                $data['icoarr'][] = $re1;
                return $data;
            }else{
                $data['error'] = $re1['error'];
                return $data;
            }
        }
    }
    function upload($file,$path,$filename,$partinfo=array(),$ondup='newcopy'){
        global $_G;

        $bzarr=self::parsePath($path.'/'.$filename);
		 if($this->error){
            return array('error'=>$this->error);
        }
        //获取文件内容
        $fileContent='';
        if(!$handle=fopen($file, 'rb')){
            return array('error'=>lang('open_file_error'));
        }
        while (!feof($handle)) {
            $fileContent .= fread($handle, 8192);
        }
        fclose($handle);
        if($partinfo['ispart']){
            if($partinfo['partnum']==1){
                $target='./cache/'.md5($path.'/'.$filename);
                file_put_contents($_G['setting']['attachdir'].$target,'');
                self::saveCache(md5($path.'/'.$filename),$target);
            }else{
                $target=self::getCache(md5($path.'/'.$filename));
            }

            if(!file_put_contents($_G['setting']['attachdir'].$target,$fileContent,FILE_APPEND)){
                return array('error'=>lang('cache_file_error1'));
            }
            if(!$partinfo['iscomplete']) return true;
            else{
                if(!$this->conn->upload($_G['setting']['attachdir'].$target,$bzarr['path'])){
                    return array('error'=>$this->conn->error());
                }
                @unlink($_G['setting']['attachdir'].$target);
                self::deleteCache(md5($path.'/'.$filename));
            }
        }else{
            if(!$this->conn->upload($file,$bzarr['path'])){
                return array('error'=>$this->conn->error());
            }
        }
        return self::getMeta($path.'/'.$filename);
    }
    public function upload_by_content($content,$path,$filename){
        global $_G;
        $bzarr=self::parsePath($path.'/'.$filename);
		 if($this->error){
            return array('error'=>$this->error);
        }
        //获取文件内容
        //$fileinfo = pathinfo($filename);
        //$file=$_G['setting']['attachdir'].'./cache/'.md5($path.'/'.$filename).'.'. $fileinfo['extension'];
        $file=$_G['setting']['attachdir'].'./cache/'.md5($path.'/'.$filename);
        @file_put_contents($file,$content);
        if(!$this->conn->upload($file,$bzarr['path'])){
            return array('error'=>$this->conn->error());
        }
        @unlink($file);
        return self::getMeta($path.'/'.$filename);
    }
}
?>
