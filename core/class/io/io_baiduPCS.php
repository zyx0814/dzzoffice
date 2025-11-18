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
include_once(DZZ_ROOT . './core/api/BaiduPCS/BaiduPCS.class.php');
@set_time_limit(0);
@ini_set('max_execution_time', 0);

class io_baiduPCS extends io_api {
    const T = 'connect_pan';
    const BZ = 'baiduPCS';
    private $icosdatas = array();
    private $uid = '';
    private $_root = '';
    private $_rootname = '';
    private $perm = 0;

    public function __construct($path) {
        global $_G;
        $arr = DB::fetch_first("SELECT root,name FROM %t WHERE bz=%s", array('connect', self::BZ));
        $this->_root = $arr['root'];
        $this->uid = $_G['adminid'] ? $_G['uid'] : 0;
        $this->_rootname = $arr['name'];
        $this->perm = perm_binPerm::getGroupPower('all');
        //$this->init($path);
        //print_r($arr);

    }

    public function MoveToSpace($path, $attach, $ondup = 'overwrite') {
        global $_G;
        /*
         *移动附件到百度网盘
         *
         */
        $filename = substr($path, strrpos($path, '/') + 1);
        $fpath = substr($path, 0, strrpos($path, '/'));
        //echo $path.'===='.$fpath.'===='.$filename;
        if (($re = $this->makeDir($fpath)) && $re['error']) { //创建目录
            return $re;
        }

        $obz = io_remote::getBzByRemoteid($attach['remote']);
        if ($obz == 'dzz') {
            $opath = 'dzz::' . $attach['attachment'];
        } else {
            $opath = $obz . '/' . $attach['attachment'];
        }
        //exit($opath.'==='.$fpath.'/'.$filename);
        if ($re = $this->multiUpload($opath, $fpath, $filename, $attach, $ondup)) {
            if ($re['error']) return $re;
            else {
                return $re;
            }
        }
        return false;
    }

    protected function makeDir($path) {
        $bzarr = $this->parsePath($path);

        $patharr = explode('/', trim(preg_replace("/^" . str_replace('/', '\/', $this->_root) . "/", '', $bzarr['path']), '/'));
        $folderarr = array();
        $p = $bzarr['bz'] . $this->_root;
        foreach ($patharr as $value) {
            $p .= '/' . $value;
            if ($re = $this->_makeDir($p) && isset($re['error'])) {
                return $re;
            } else {
                continue;
            }
        }
        return true;
    }

    protected function _makeDir($path) {
        global $_G;
        $bzarr = $this->parsePath($path);
        try {
            $pcs = $this->init($path);
            if (is_array($pcs) && $pcs['error']) return $pcs;
            $response = $pcs->makeDirectory($bzarr['path']);
            $result = json_decode($response, true);
            if (intval($result['error_code']) == 31061) {
                return true;
            } elseif ($result['error_code']) {
                return array('error' => $result['error_msg'], 'error_code' => $result['error_code']);
            }
            return true;
        } catch (Exception $e) {
            //var_dump($e);
            return array('error' => $e->getMessage());
        }

    }

    /*
    *初始化百度pcs 返回pcs 操作符
    */
    public function init($path, $isguest = 0) {
        global $_G;
        $bzarr = explode(':', $path);
        $bd_uid = trim($bzarr[1]);
        if ($baidu = DB::fetch_first("select access_token,cloudname,cusername,uid from " . DB::table(self::T) . " where  id='{$bd_uid}'")) {

            if (!$isguest && $baidu['uid'] > 0 && $baidu['uid'] != $_G['uid']) return array('error' => 'need authorize to baiduPCS');
            $access_token = $baidu['access_token'];
            if ($baidu['cloudname']) {
                $this->_rootname = $baidu['cloudname'];
            } else {
                $this->_rootname .= ':' . $baidu['cusername'];
            }
        } else {
            return array('error' => 'need authorize to baiduPCS');
        }
        return new BaiduPCS($access_token);
    }

    public function refresh_token($path) {
        require_once(DZZ_ROOT . './core/api/BaiduPCS/BaiduOAuth2.php');
        $bzarr = explode(':', $path);
        $bd_uid = trim($bzarr[1]);
        $cloud = DB::fetch_first("select `key` , `secret` from " . DB::table('connect') . " where bz='baiduPCS'");
        if ($baidu = DB::fetch_first("select id,access_token,refresh_token from " . DB::table('connect_pan') . " where  id='{$bd_uid}'")) {
            $auth = new BaiduOAuth2($cloud['key'], $cloud['secret']);
            if ($token = $auth->getAccessTokenByRefreshToken($baidu['refresh_token'], $baidu['scope'])) {
                $token['refreshtime'] = TIMESTAMP;
                if ($token['access_token']) C::t('connect_pan')->update($baidu['id'], $token);
                return true;
            }/*else{
				return (BaiduUtils::errmsg());
			}*/
        }
        return false;
    }

    //根据路径获取目录树的数据；
    public function getFolderDatasByPath($path) {

        $bzarr = $this->parsePath($path);
        $spath = $bzarr['path'];

        if ($this->_root) {
            $reg = str_replace('/', '\/', $this->_root);
            $spath = preg_replace("/^" . $reg . "/i", '', $spath);
        }
        //exit("/^".$reg."/i");
        $patharr = explode('/', $spath);
        if (empty($patharr[0])) unset($patharr[0]);

        //print_r($bzarr);exit($spath);
        $folderarr = array();
        for ($i = 0; $i <= count($patharr); $i++) {
            $path1 = $bzarr['bz'] . $this->_root;
            for ($j = 0; $j <= $i; $j++) {
                $path1 .= '/' . $patharr[$j];
            }
            if ($arr = $this->getMeta($path1)) {
                if (isset($arr['error'])) continue;
                $folder = $this->getFolderByIcosdata($arr);
                $folderarr[$folder['fid']] = $folder;
            }
        }
        //print_r($folderarr);exit($path);

        return $folderarr;
    }

    public function authorize($refer) {
        global $_G, $_GET;
        if (empty($_G['uid'])) {
            dsetcookie('_refer', rawurlencode(BASESCRIPT . '?mod=connect&op=oauth&bz=baiduPCS'));
            showmessage('to_login', '', array(), array('showmsg' => true, 'login' => 1));
        }
        require_once(DZZ_ROOT . './core/api/BaiduPCS/BaiduOAuth2.php');
        $cloud = DB::fetch_first("select `key` , `secret` from " . DB::table('connect') . " where bz='baiduPCS'");
        $auth = new BaiduOAuth2($cloud['key'], $cloud['secret']);
        $auth->setRedirectUri($_G['siteurl'] . 'index.php?mod=connect&op=oauth&bz=baiduPCS');
        if ($_GET['code'] && (($state = authcode($_GET['state'], 'DECODE')) == $cloud['key'] || $state == 'in_admin_' . $cloud['key']) && $token = $auth->getAccessTokenByAuthorizationCode($_GET['code'])) {
            $token['refreshtime'] = TIMESTAMP;
            $token['uid'] = strpos($state, 'in_admin_') === 0 ? 0 : $_G['uid'];
            if ($token['access_token'] && $userinfo = $auth->getLoggedInUser($token['access_token'])) {
                $token['cuid'] = $userinfo['uid'];
                $token['cusername'] = $userinfo['uname'];
                $token['portrait'] = $userinfo['portrait'];
            }
            if ($token['cuid']) {
                if ($id = DB::result_first("select id from " . DB::table(self::T) . " where uid='{$token[uid]}' and cuid='{$token[cuid]}' and bz='baiduPCS'")) {
                    DB::update(self::T, $token, "id ='{$id}'");
                } else {
                    $token['bz'] = 'baiduPCS';
                    $token['dateline'] = TIMESTAMP;
                    $id = DB::insert(self::T, $token, 1);
                }
                if (strpos($state, 'in_admin_') === 0) { //插入企业盘空间库(local_storage);
                    $setarr = array('name' => lang('baidu_network_disk') . '：' . $token['cusername'],
                        'bz' => 'baiduPCS',
                        'isdefault' => 0,
                        'dname' => self::T,
                        'did' => $id,
                        'dateline' => TIMESTAMP
                    );
                    if (!DB::result_first("select COUNT(*) from %t where did=%d and dname=%s", array('local_storage', $id, self::T))) {
                        C::t('local_storage')->insert($setarr);
                    }
                }
            }
            if (strpos($state, 'in_admin_') === 0) {
                $returnurl = 'admin.php?mod=cloud&op=space';
            } else {
                if (!$refer) $refer = DZZSCRIPT . '?mod=connect';
                $returnurl = $refer;
            }

            @header('Location: ' . $returnurl);
            //include template('oauth');
            exit();
        }
        $clientid = $cloud['key'];
        $state = authcode(defined('IN_ADMIN') ? 'in_admin_' . $clientid : $clientid, 'ENCODE');
        $authorizeurl = $auth->getAuthorizeUrl('code', 'basic netdisk', $state);
        //exit($authorizeurl);
        header('Location: ' . $authorizeurl);
    }

    public function parsePath($path) {
        $bzarr = explode(':', $path);
        return array('bz' => $bzarr[0] . ':' . $bzarr[1] . ':', 'path' => $bzarr[2]);
    }
    //获取转码文件；
    //$path: 路径
    public function getM3U8Uri($path, $type = 'M3U8_854_480') {
        $bzarr = $this->parsePath($path);
        $pcs = $this->init($path, 1);
        if (is_array($pcs) && $pcs['error']) return $pcs;
        return $pcs->streaming($bzarr['path'], $type);
    }
    //获取文件流；
    //$path: 路径
    public function getStream($path) {
        $bzarr = $this->parsePath($path);
        $pcs = $this->init($path, 1);
        if (is_array($pcs) && $pcs['error']) return $pcs;
        try {
            return $pcs->getStreamUri($bzarr['path']);
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }
    //获取文件流地址；
    //$path: 路径
    public function getFileUri($path) {
        $bzarr = $this->parsePath($path, 1);
        $pcs = $this->init($path, 1);
        if (is_array($pcs) && $pcs['error']) return $pcs;
        try {
            return $pcs->getStreamUri($bzarr['path']);
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }

    }

    public function deleteThumb($path) {
        global $_G;
        $imgcachePath = './imgcache/';

        $cachepath = str_replace(urlencode('/'), '/', urlencode(str_replace('//', '/', str_replace(':', '/', $path))));
        foreach ($_G['setting']['thumbsize'] as $value) {
            $target = $imgcachePath . ($cachepath) . '.' . $value['width'] . '_' . $value['height'] . '_1.jpeg';
            $target1 = $imgcachePath . ($cachepath) . '.' . $value['width'] . '_' . $value['height'] . '_2.jpeg';
            @unlink($_G['setting']['attachdir'] . $target);
            @unlink($_G['setting']['attachdir'] . $target1);
        }
    }

    public function createThumb($path, $size, $width = 0, $height = 0, $thumbtype = 1) {
        global $_G;
        if (intval($width) < 1) $width = $_G['setting']['thumbsize'][$size]['width'];
        if (intval($height) < 1) $height = $_G['setting']['thumbsize'][$size]['height'];
        $imgcachePath = 'imgcache/';
        $cachepath = str_replace(':', '/', $path);
        $cachepath = preg_replace("/\/+/", '/', str_replace(':', '/', $path));
        $target = $imgcachePath . ($cachepath) . '.' . $width . '_' . $height . '_' . $thumbtype . '.jpeg';
        if (@getimagesize($_G['setting']['attachdir'] . './' . $target)) {
            return 2;//已经存在缩略图
        }
        //调用挂载点程序生成缩略图绝对和相对地址；
        $fileurls = array();
        Hook::listen('thumbnail', $fileurls, $path);
        if ($fileurls) {
            //生成图片缩略图
            $imgurl = $fileurls['filedir'];
            $target_attach = $_G['setting']['attachdir'] . './' . $target;
            $targetpath = dirname($target_attach);
            dmkdir($targetpath);
            require_once libfile('class/image');
            $image = new image();
            if ($thumb = $image->Thumb($imgurl, $target, $width, $height, $thumbtype)) {
                return 1;
            } else {
                return 0;
            }
        } else {
            $fileurls = array('fileurl' => $this->getFileUri($path), 'filedir' => $this->getStream($path));
        }
        //非图片类文件的时候，直接获取文件后缀对应的图片
        if (!$imginfo = @getimagesize($fileurls['filedir'])) {
            return -1; //非图片不能生成
        }
        if (($imginfo[0] < $width && $imginfo[1] < $height)) {
            return 3;//小于要求尺寸，不需要生成
        }
        //获取缩略图
        $bzarr = $this->parsePath($path);
        $pcs = $this->init($path, 1);
        if (is_array($pcs) && $pcs['error']) return false;
        $quality = 80;
        $result = $pcs->thumbnail($bzarr['path'], $width, $height, $quality);
        $targetpath = dirname($_G['setting']['attachurl'] . './' . $target);
        dmkdir($targetpath);
        @file_put_contents($_G['setting']['attachdir'] . './' . $target, $result);
        return true;

    }

    public function getThumb($path, $width, $height, $original, $returnurl = false, $thumbtype = 1) {
        global $_G;
        $imgcachePath = 'imgcache/';
        $cachepath = str_replace(':', '/', $path);
        $cachepath = preg_replace("/\/+/", '/', str_replace(':', '/', $path));
        echo $path;
        die;
        $target = $imgcachePath . ($cachepath) . '.' . $width . '_' . $height . '_' . $thumbtype . '.jpeg';
        if (!$original && @getimagesize($_G['setting']['attachdir'] . './' . $target)) {
            if ($returnurl) return $_G['setting']['attachurl'] . '/' . $target;
            IO::output_thumb($_G['setting']['attachdir'] . './' . $target);
        }
        //调用挂载点程序生成缩略图绝对和相对地址；
        $fileurls = array();
        Hook::listen('thumbnail', $fileurls, $path);
        if ($fileurls) {
            //生成图片缩略图
            $imgurl = $fileurls['filedir'];
            $target_attach = $_G['setting']['attachdir'] . './' . $target;
            $targetpath = dirname($target_attach);
            dmkdir($targetpath);
            require_once libfile('class/image');
            $image = new image();
            if ($thumb = $image->Thumb($imgurl, $target, $width, $height, $thumbtype)) {
                if ($returnurl) return $_G['setting']['attachurl'] . '/' . $target;
                IO::output_thumb($_G['setting']['attachdir'] . './' . $target);
            } else {
                if ($returnurl) return $imgurl;
                IO::output_thumb($imgurl);
            }
        } else {
            $fileurls = array('fileurl' => $this->getFileUri($path), 'filedir' => $this->getStream($path));
        }
        if (!is_string($fileurls['filedir'])) {
            header("HTTP/1.1 304 Not Modified");
            exit;
        }
        //非图片类文件的时候，直接获取文件后缀对应的图片
        if (!$imginfo = @getimagesize($fileurls['filedir'])) {
            $imgurl = geticonfromext($data['ext'], $data['type']);
            if ($returnurl) return $imgurl;
            IO::output_thumb($imgurl);
        }
        //返回原图的时候或图片小于缩略图宽高的不生成直接返回原图
        if ($original || ($imginfo[0] < $width && $imginfo[1] < $height)) {
            if ($returnurl) return $fileurls['fileurl'];
            IO::output_thumb($fileurls['filedir']);
        }
        //获取缩略图
        $bzarr = $this->parsePath($path);
        $pcs = $this->init($path, 1);
        if (is_array($pcs) && $pcs['error']) return $pcs;
        $result = $pcs->thumbnail($bzarr['path'], $width, $height, 80);
        $targetpath = dirname($_G['setting']['attachurl'] . './' . $target);
        dmkdir($targetpath);
        file_put_contents($_G['setting']['attachdir'] . './' . $target, $result);
        if ($returnurl) return $_G['setting']['attachurl'] . '/' . $target;
        $file = $_G['setting']['attachdir'] . './' . $target;
        IO::output_thumb($imgurl);
    }


    //重写文件内容
    //@param number $path  文件的路径
    //@param string $data  文件的新内容
    public function setFileContent($path, $data) {
        $patharr = explode('/', $path);
        $filename = $patharr[count($patharr) - 1];
        unset($patharr[count($patharr) - 1]);
        $path1 = implode('/', $patharr);
        $icoarr = $this->upload($data, $path1, $filename, false, 'overwrite');
        if ($icoarr['type'] == 'image') {
            $this->deleteThumb($path);
            $icoarr['img'] .= '&t=' . TIMESTAMP;
        }
        return $icoarr;
    }

    /**
     * 获取当前用户空间配额信息
     * @return string
     * Array
     * (
     * [quota] => 2207613190144
     * [used] => 189239854410
     * [request_id] => 856227673
     * )
     */
    public function getQuota($bz) {
        $pcs = $this->init($bz, 1);
        if (is_array($pcs) && $pcs['error']) return $pcs;
        return json_decode($pcs->getQuota(), true);
    }

    public function rename($path, $name) {//重命名
        $arr = $this->parsePath($path);
        $patharr = explode('/', $arr['path']);
        $arr['path1'] = '';
        $ext = strtolower(substr(strrchr($arr['path'], '.'), 1));
        foreach ($patharr as $key => $value) {
            if ($key >= count($patharr) - 1) break;
            $arr['path1'] .= $value . '/';
        }
        $arr['path1'] .= $ext ? (preg_replace("/\.\w+$/i", '.' . $ext, $name)) : $name;

        if ($arr['path'] != $arr['path1']) {
            $pcs = $this->init($path);
            if (is_array($pcs) && $pcs['error']) return $pcs;
            $response = $pcs->moveSingle($arr['path'], $arr['path1']);

            $result = json_decode($response, true);
            if ($result['error_code']) {
                return array('error' => $result['error_msg']);
            }
        }
        return $this->getMeta($arr['bz'] . $arr['path1']);
    }


    /**
     * 移动文件到目标位置
     * @param string $opath 被移动的文件路径
     * @param string $path 目标位置（可能是同一api内或跨api，这两种情况分开处理）
     * @return icosdatas
     */
    public function CopyTo($opath, $path, $iscopy) {
        $oarr = $this->parsePath($opath);
        $arr = IO::parsePath($path);
        try {
            $pcs = $this->init($opath);
            if (is_array($pcs) && $pcs['error']) return $pcs;
            if ($arr['bz'] == $oarr['bz'] && !$iscopy) { //同一api内
                $data = $this->getMeta($opath);
                $response = $pcs->moveSingle($oarr['path'], $arr['path'] . '/' . $data['name']);

                $result = json_decode($response, true);
                if ($result['error_code']) {
                    $data['success'] = $result['error_msg'];
                    return $data;
                }
                $meta = $pcs->getMeta($arr['path'] . '/' . $data['name']);
                $meta = json_decode($meta, true);
                //if($meta['error_msg']) return array('error'=>$meta['error_msg']);
                $meta = $meta['list'][0];

                $data['newdata'] = $this->_formatMeta($meta, $arr['bz']);
                $data['success'] = true;
                return $data;
            } else {
                $data = $this->getMeta($opath);
                switch ($data['type']) {
                    case 'folder'://创建目录
                        if ($re = IO::CreateFolder($path, $data['name'])) {
                            if (isset($re['error']) && intval($re['error_code']) != 31061) {
                                $data['success'] = $re['error'];
                            } else {

                                $data['newdata'] = $re['icoarr'];
                                $data['success'] = true;
                                $contents = $this->listFiles($opath);
                                //	 print_r($contents);
                                foreach ($contents as $key => $value) {
                                    $data['contents'][$key] = $this->CopyTo($value['path'], $re['folderarr']['path'], $iscopy, $iscopy);
                                }
                            }
                        }
                        break;
                    default:

                        //$fileContent=IO::getFileContent($opath);
                        //exit($opath.'==='.$path.'==='.$data['name']);
                        if ($re = IO::multiUpload($opath, $path, $data['name'])) {
                            if ($re['error']) $data['success'] = $re['error'];
                            else {
                                $data['newdata'] = $re;
                                $data['success'] = true;
                            }
                        }
                }
            }
        } catch (Exception $e) {
            //var_dump($e);
            $data['success'] = $e->getMessage();
            return $data;
        }
        return $data;
    }

    public function multiUpload($opath, $path, $filename, $attach = array(), $ondup = "newcopy") {
        global $_G;
        /*
         * 分块上传文件
         * param $file:文件路径（可以是url路径，需要服务器开启allow_url_fopen);
        */

        $partsize = 1024 * 1024 * 5; //分块大小2M
        if ($attach) {
            $data = $attach;
            $data['size'] = $attach['filesize'];
        } else {
            $data = IO::getMeta($opath);
            if ($data['error']) return $data;
        }
        $size = $data['size'];
        if (is_array($filepath = IO::getStream($opath))) {
            return array('error' => $filepath['error']);
        }

        if ($size < $partsize) {
            //获取文件内容
            if (!$handle = fopen($filepath, 'rb')) {
                return array('error' => lang('open_file_error'));
            }
            while (!feof($handle)) {
                $fileContent .= fread($handle, 8192);
                //if(strlen($fileContent)==0) return array('error'=>'文件不存在');
            }

            return $this->upload($fileContent, $path, $filename, false, $ondup);
        } else { //分片上传
            $this->deleteCache($path . $filename);
            if (!$handle = fopen($filepath, 'rb')) {
                return array('error' => lang('open_file_error'));
            }
            $fileContent = '';
            while (!feof($handle)) {
                $fileContent .= fread($handle, 8192);
                //if(strlen($fileContent)==0) return array('error'=>'文件不存在');
                if (strlen($fileContent) >= $partsize) {
                    $re = $this->upload($fileContent, $path, $filename, true, $ondup);
                    if ($re['error']) {
                        return $re;
                    }
                    $fileContent = '';
                }
            }
            fclose($handle);
            if (!empty($fileContent)) {
                $re = $this->upload($fileContent, $path, $filename, true, $ondup);
                if ($re['error']) {
                    return $re;
                }
            }
            //分片上传结束，合并分片文件
            return $this->createSuperFile($path, $filename, $ondup);
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
    public function listFiles($path, $by = 'time', $order = 'desc', $limit = '', $force = 0) {
        global $_G, $_GET, $documentexts, $imageexts;

        try {
            $bzarr = $this->parsePath($path);
            $bz = $bzarr['bz'];
            $path1 = $bzarr['path'];
            $pcs = $this->init($path, 1);
            if (is_array($pcs) && $pcs['error']) return $pcs;

            $data = array();
            if ($result = $pcs->listFiles($path1, $by, $order, $limit)) {
                $result = json_decode($result, true);
                if ($result['error_code']) {
                    return array('error' => $result['error_msg']);
                } else $data = $result['list'];
            }
            $icosdata = array();
            foreach ($data as $key => $value) {
                $icoarr = $this->_formatMeta($value, $bz);
                $icosdata[$icoarr['icoid']] = $icoarr;
            }
            return $icosdata;
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

    /*获取目录信息*/
    public function getContains($path, $suborg = false, $contains = array('size' => 0, 'contain' => array(0, 0))) {
        foreach ($this->listFiles($path) as $value) {
            if ($value['type'] == 'folder') {
                $contains = $this->getContains($value['path'], false, $contains);
                $contains['contain'][1] += 1;
            } else {
                $contains['size'] += $value['size'];
                $contains['contain'][0] += 1;
            }
        }
        return $contains;
    }

    /*
     *获取文件的meta数据
     *返回标准的icosdata
     *$force>0 强制刷新，不读取缓存数据；
    */
    public function getMeta($path, $force = 0) {
        global $_G, $_GET, $documentexts, $imageexts;
        $icosdata = array();
        $bzarr = explode(':', $path);
        $bz = $bzarr[0] . ':' . $bzarr[1] . ':';
        $data = array();
        $path1 = $bzarr[2];
        // Get the metadata for the file/folder specified in $path
        $pcs = $this->init($bz, 1);
        if (is_array($pcs) && $pcs['error']) return $pcs;
        //exit($path1.'==='.$path.'==='.$bz);
        $meta = $pcs->getMeta($path1);
        $meta = json_decode($meta, true);
        //print_r($meta);
        //print_r($baidu);
        //exit($access_token);
        if ($meta['error_msg']) return array('error' => $meta['error_msg']);
        $meta = $meta['list'][0];

        $icosdata = $this->_formatMeta($meta, $bz);
        return $icosdata;
    }

    //将api获取的meta数据转化为icodata
    public function _formatMeta($meta, $bz) {
        global $_G, $documentexts, $imageexts;
        //判断是否为根目录
        $root = $bz . $this->_root;
        $icosdata = array();
        $bzarr = explode(':', $bz);
        $rid = md5($bz . $meta['path']);
        if ($this->uid) {
            $uid = $this->uid;
            $userinfo = getuserbyuid($uid);
            $username = $userinfo['username'];
        } else {
            $uid = 0;
            $username = lang('system');
        }
        $dpath = dzzencode($bz . $meta['path']);
        if ($meta['isdir']) {
            $icoarr = array(
                'icoid' => $rid,
                'path' => $bz . $meta['path'],
                'dpath' => $dpath,
                'bz' => ($bz),
                'gid' => 0,
                'name' => $meta['path'] ? substr(strrchr($meta['path'], '/'), 1) : '',
                'username' => $username,
                'uid' => $uid,
                'oid' => $rid,
                'img' => 'dzz/images/default/system/folder.png',
                'type' => 'folder',
                'ext' => '',
                'pfid' => md5(str_replace(strrchr($meta['path'], '/'), '', $bz . $meta['path'])),
                'size' => 0,
                'dateline' => intval($meta['mtime']),
                'flag' => '',
                'preview' => $this->preview,
                'sid' => $this->sharesid
            );
            if ($icoarr['path'] == $root) {
                $icoarr['name'] = $this->_rootname;
                $icoarr['flag'] = self::BZ;
                $icoarr['pfid'] = 0;
            }
            $icoarr['fsize'] = formatsize($icoarr['size']);
            $icoarr['ftype'] = getFileTypeName($icoarr['type'], $icoarr['ext']);
            if (!$icoarr['dateline']) $icoarr['fdateline'] = '-';
            else $icoarr['fdateline'] = dgmdate($icoarr['dateline']);
            $icosdata = $icoarr;

        } else {
            $ext = substr(strrchr($meta['path'], '.'), 1);
            if (in_array($ext, $imageexts)) $type = 'image';
            elseif (in_array($ext, $documentexts)) $type = 'document';
            else $type = 'attach';

            if ($type == 'image') {
                $img = $_G['siteurl'] . DZZSCRIPT . '?mod=io&op=thumbnail&size=small&path=' . $dpath;
                $url = $_G['siteurl'] . DZZSCRIPT . '?mod=io&op=thumbnail&size=large&path=' . $dpath;
            } else {
                $img = geticonfromext($ext, $type);
                $url = $_G['siteurl'] . DZZSCRIPT . '?mod=io&op=getStream&path=' . rawurlencode($bz . $meta['path']);
            }
            $rid = md5($bz . $meta['path']);
            $icoarr = array(
                'icoid' => $rid,
                'path' => ($bz . $meta['path']),
                'dpath' => $dpath,
                'bz' => ($bz),
                'gid' => 0,
                'name' => $meta['path'] ? substr(strrchr($meta['path'], '/'), 1) : '',
                'username' => $username,
                'uid' => $uid,
                'oid' => $rid,
                'img' => $img,
                'url' => $url,
                'type' => $type,
                'ext' => $ext,
                'pfid' => md5(str_replace(strrchr($meta['path'], '/'), '', $bz . $meta['path'])),
                'size' => $meta['size'],
                'dateline' => intval($meta['mtime']),
                'flag' => '',
                'preview' => $this->preview,
                'sid' => $this->sharesid
            );

            $icoarr['fsize'] = formatsize($icoarr['size']);
            $icoarr['ffsize'] = lang('property_info_size', array('fsize' => formatsize($icoarr['size']), 'size' => $icoarr['size']));
            $icoarr['ftype'] = getFileTypeName($icoarr['type'], $icoarr['ext']);
            if (!$icoarr['dateline']) $icoarr['fdateline'] = '-';
            else $icoarr['fdateline'] = dgmdate($icoarr['dateline']);
            $icosdata = $icoarr;
        }

        return $icosdata;
    }

    //通过icosdata获取folderdata数据
    public function getFolderByIcosdata($icosdata) {
        global $_GET;
        $folder = array();
        if ($icosdata['type'] == 'folder') {
            $folder = array('fid' => $icosdata['oid'],
                'path' => $icosdata['path'],
                'fname' => $icosdata['name'],
                'uid' => $icosdata['uid'],
                'pfid' => $icoadata['pfid'],
                'iconview' => $_GET['iconview'] ? intval($_GET['iconview']) : 1,
                'disp' => $_GET['disp'] ? intval($_GET['disp']) : 1,
                'perm' => $this->perm,
                'hash' => $icosdata['hash'],
                'bz' => $icosdata['bz'],
                'gid' => $icosdata['gid'],
                'fsperm' => perm_FolderSPerm::flagPower('baiduPCS')
            );

        }
        return $folder;
    }

    //获得文件内容；
    public function getFileContent($path) {
        $bzarr = explode(':', $path);
        $bz = $bzarr[0] . ':' . $bzarr[1] . ';';
        $path1 = $bzarr[2];
        try {
            $pcs = $this->init($bz, 1);
            if (is_array($pcs) && $pcs['error']) return $pcs;
            return $pcs->download($path1);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    //打包下载文件
    public function zipdownload($paths, $filename) {
        global $_G;
        $paths = (array)$paths;
        set_time_limit(0);

        if (empty($filename)) {
            $meta = $this->getMeta($paths[0]);
            $filename = $meta['name'] . (count($paths) > 1 ? lang('wait') : '');
        }
        $filename = (strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($filename) : $filename);
        include_once libfile('class/ZipStream');

        $zip = new ZipStream($filename . ".zip");
        $data = $this->getFolderInfo($paths, '', $zip);
        //$zip->setComment("$meta[name] " . date('l jS \of F Y h:i:s A'));
        /*foreach($data as $value){
             $zip->addLargeFile(fopen($value['url'],'rb'), $value['position'], $value['dateline']);
        }*/
        $zip->finalize();
    }

    public function getFolderInfo($paths, $position = '', &$zip) {
        static $data = array();
        try {
            foreach ($paths as $path) {
                $arr = IO::parsePath($path);
                $pcs = $this->init($path, 1);
                if (is_array($pcs) && $pcs['error']) return $pcs;
                $meta = $this->getMeta($path);

                switch ($meta['type']) {
                    case 'folder':
                        $lposition = $position . $meta['name'] . '/';
                        $contents = $this->listFiles($path);
                        $arr = array();
                        foreach ($contents as $key => $value) {
                            $arr[] = $value['path'];
                        }
                        if ($arr) $this->getFolderInfo($arr, $lposition, $zip);
                        break;
                    default:
                        $meta['url'] = $this->getStream($meta['path']);
                        $meta['position'] = $position . $meta['name'];
                        //$data[$meta['icoid']]=$meta;
                        $zip->addLargeFile(fopen($meta['url'], 'rb'), $meta['position'], $meta['dateline']);
                }
            }
        } catch (Exception $e) {
            //var_dump($e);
            $data['error'] = $e->getMessage();
            return $data;
        }
        //return $data;
    }

    //下载文件
    public function download($paths, $filename) {
        global $_G;
        $paths = (array)$paths;
        if (count($paths) > 1) {
            $this->zipdownload($paths, $filename);
            exit();
        } else {
            $path = $paths[0];
        }
        $path = rawurldecode($path);
        $url = $this->getStream($path);
        try {
            // Download the file
            $file = $this->getMeta($path);
            if ($file['type'] == 'folder') {//目录压缩下载
                $this->zipdownload($path);
                exit();
            } else {//文件直接跳转到文件源地址；不再通过服务器中转
                @header("Location: $url");
                exit();
            }
            $file['name'] = '"' . (strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($file['name']) : $file['name']) . '"';
            $d = new FileDownload();
            $d->download($url, $file['name'], $file['size'], $file['dateline'], true);
            exit();
            dheader('Date: ' . gmdate('D, d M Y H:i:s', $file['dateline']) . ' GMT');
            dheader('Last-Modified: ' . gmdate('D, d M Y H:i:s', $file['dateline']) . ' GMT');
            dheader('Content-Encoding: none');
            dheader('Content-Disposition: attachment; filename=' . $file['name']);
            dheader('Content-Type: application/octet-stream');
            dheader('Content-Length: ' . $file['size']);

            @ob_end_clean();
            if (getglobal('gzipcompress')) @ob_start('ob_gzhandler');
            @readfile($url);
            @flush();
            @ob_flush();
            exit();
        } catch (Exception $e) {
            // The file wasn't found at the specified path/revision
            //echo 'The file was not found at the specified path/revision';
            topshowmessage($e->getMessage());
        }
    }

    /**
     * 上传文件
     * 注意：此方法适用于上传不大于2G的单个文件。
     * @param string $fileContent 文件内容字符串
     * @param string $path 上传文件的目标保存路径
     * @param string $fileName 文件名
     * @param string $newFileName 新文件名
     * @param string $ondup overwrite：表示覆盖同名文件；newcopy：表示生成文件副本并进行重命名，命名规则为“文件名_日期.后缀”。
     * @param boolean $isCreateSuperFile 是否分片上传
     * @return string
     */
    public function upload_by_content($fileContent, $path, $filename, $isCreateSuperFile = false, $ondup = 'newcopy') {
        return $this->upload($fileContent, $path, $filename, $isCreateSuperFile, $ondup);
    }

    public function upload($fileContent, $path, $filename, $isCreateSuperFile = false, $ondup = 'newcopy') {
        global $_G;
        $bzarr = explode(':', ($path));
        $bz = $bzarr[0] . ':' . $bzarr[1] . ':';
        $path = $bzarr[2] . '/';
        try {
            $pcs = $this->init($bz);
            if (is_array($pcs) && $pcs['error']) return $pcs;
            $response = $pcs->upload($fileContent, $path, $filename, null, $isCreateSuperFile, $ondup);
            unset($fileContent);
            $response = json_decode($response, true);
            if ($response['error_msg']) {
                return array('error' => $response['error_msg']);
            }
            if ($isCreateSuperFile === true) {
                $path0 = $bz . $path . $filename;
                if ($response['md5']) {
                    $this->saveCache($path0, $response['md5']);
                    return true;
                } else {
                    return array('error' => ' part upload error');
                }
            } else {
                $icoarr = $this->_formatMeta($response, $bz);
                return $icoarr;
            }
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

    public function createSuperFile($path, $filename, $ondup = 'newcopy') {
        global $_G;
        $bzarr = explode(':', ($path));
        $bz = $bzarr[0] . ':' . $bzarr[1] . ':';
        $path = $bzarr[2] . '/';
        try {
            $pcs = $this->init($bz);
            if (is_array($pcs) && $pcs['error']) return $pcs;
            $path0 = $bz . $path . $filename;
            if (!($params = array_values($this->getCache($path0)))) {
                return array('error' => lang('file_merge_error'));
            }
            $response = $pcs->createSuperFile($path, $filename, $params, null, $ondup);
            $response = json_decode($response, true);
            if ($response['error_msg']) {
                return array('error' => $response['error_msg']);
            }

            $this->deleteCache($path0);
            $icoarr = $this->_formatMeta($response, $bz);
            return $icoarr;
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }


    //删除原内容
    //$path: 删除的路径
    //$bz: 删除的api;
    //$data：可以删除的id数组（当剪切的时候，为了保证数据不丢失，目标位置添加成功后将此id添加到data数组，
    //删除时如果$data有数据，将会只删除id在$data中的数据；
    //如果删除的是目录或下级有目录，需要判断此目录内是否所有元素都在删除的id中，如果有未删除的元素，则此目录保留不会删除；
    //$force 真实删除，不放入回收站
    public function Delete($path, $force = false) {
        //global $dropbox;
        $bzarr = explode(':', ($path));
        $rid = md5($path);
        $bz = $bzarr[0] . ':' . $bzarr[1] . ':';
        $path1 = $bzarr[2];
        try {
            $pcs = $this->init($bz, $force);
            if (is_array($pcs) && $pcs['error']) return $pcs;
            $response = $pcs->deleteSingle($path1);
            $response = json_decode($response, true);
            if ($response['error_msg']) {
                return array('icoid' => $rid, 'rid' => $rid, 'error' => $response['error_msg']);
            }
            return array('icoid' => $rid, 'rid' => $rid,
                'name' => substr(strrchr($path, '/'), 1),
            );
        } catch (Exception $e) {
            return array('icoid' => $rid, 'rid' => $rid, 'error' => $e->getMessage());
        }
    }

    public function CreateFolderByPath($path, $pfid = '', $noperm = false) {
        $data = array();
        if ($this->makeDir($path)) {
            $data = $this->getMeta($path);
        }
        return $data;
    }
    //添加目录
    //$fname：目录路径;
    //$container：目标容器
    //$bz：api;
    public function CreateFolder($path, $fname) {
        global $_G;
        $bzarr = explode(':', ($path));
        $bz = $bzarr[0] . ':' . $bzarr[1] . ':';
        $path1 = $bzarr[2] . '/' . $fname;
        /*echo('createrfolder==='.$fname.'===='.$path1.'===='.$bz);
        echo $path1.'===========';
        exit($path);*/
        $return = array();
        try {
            $pcs = $this->init($bz);
            if (is_array($pcs) && $pcs['error']) return $pcs;

            $response = $pcs->makeDirectory($path1);

            $result = json_decode($response, true);
            if ($result['error_code']) {
                $icoarr = $this->getMeta($path1);
                $folderarr = $this->getFolderByIcosdata($path1);
                return array('error' => $result['error_msg'], 'error_code' => $result['error_code'], 'icoarr' => $icoarr, 'folderarr' => $folderarr);
            }
            $result['isdir'] = 1;

            $icoarr = $this->_formatMeta($result, $bz);
            $folderarr = $this->getFolderByIcosdata($icoarr);
            $return = array('folderarr' => $folderarr, 'icoarr' => $icoarr);
        } catch (Exception $e) {
            //var_dump($e);
            $return = array('error' => $e->getMessage());
        }
        return $return;
    }

    //获取不重复的目录名称
    public function getFolderName($name, $path) {
        static $i = 0;
        if (!$this->icosdatas) $this->icosdatas = $this->listFiles($path);
        $names = array();
        foreach ($icosdatas as $value) {
            $names[] = $value['name'];
        }
        if (in_array($name, $names)) {
            $name = str_replace('(' . $i . ')', '', $name) . '(' . ($i + 1) . ')';
            $i += 1;
            return $this->getFolderName($name, $path);
        } else {
            return $name;
        }
    }

    private function getPartInfo($content_range) {
        $arr = array();
        if (!$content_range) {
            $arr['ispart'] = false;
            $arr['iscomplete'] = true;
        } elseif (is_array($content_range)) {
            $arr['ispart'] = true;
            $partsize = getglobal('setting/maxChunkSize');
            $arr['partnum'] = ceil(($content_range[2] + 1) / $partsize);
            if (($content_range[2] + 1) >= $content_range[3]) {
                $arr['iscomplete'] = true;
            } else {
                $arr['iscomplete'] = false;
            }
        } else {
            return false;
        }
        return $arr;
    }

    private function getCache($path) {
        $cachekey = 'baidu_upload_' . md5($path);
        $cache = C::t('cache')->fetch($cachekey);
        return (unserialize($cache['cachevalue']));
    }

    private function saveCache($path, $str) {
        global $_G;
        $cachekey = 'baidu_upload_' . md5($path);
        $cachevalue = $this->getCache($path);
        $cachevalue[$str] = $str;
        C::t('cache')->insert(array(
            'cachekey' => $cachekey,
            'cachevalue' => serialize($cachevalue),
            'dateline' => $_G['timestamp'],
        ), false, true);
    }

    private function deleteCache($path) {
        $cachekey = 'baidu_upload_' . md5($path);
        C::t('cache')->delete($cachekey);
    }

    public function uploadStream($file, $filename, $path, $relativePath, $content_range) {
        $data = array();
        //exit($path.'===='.$filename);

        //处理目录(没有分片或者最后一个分片时创建目录
        $arr = $this->getPartInfo($content_range);

        if ($relativePath && ($arr['iscomplete'])) {
            $path1 = $path;
            $patharr = explode('/', $relativePath);
            foreach ($patharr as $key => $value) {
                if (!$value) {
                    unset($patharr[$key]);
                    continue;
                }
                if ($patharr[$key - 1]) $path1 .= '/' . $patharr[$key - 1];

                $re = $this->CreateFolder($path1, $value);

                if (intval($re['error_code']) == 31061) {
                    continue;
                } else {
                    if (isset($re['error'])) {
                        return $re;
                    } else {
                        if ($key == 0) {
                            $data['icoarr'][] = $re['icoarr'];
                            $data['folderarr'][] = $re['folderarr'];
                        }
                    }
                }
            }
            //$path.='/'.implode('/',$patharr);
        }
        if ($relativePath) $path = $path . '/' . $relativePath;

        //获取文件内容
        $fileContent = '';
        if (!$handle = fopen($file, 'rb')) {
            return array('error' => lang('open_file_error'));
        }
        while (!feof($handle)) {
            $fileContent .= fread($handle, 8192);
        }
        fclose($handle);
        if ($arr['ispart']) {
            if ($re1 = $this->upload($fileContent, $path, $filename, true)) {
                if ($re1['error']) {
                    return $re1;
                }
                if ($arr['iscomplete']) {
                    $re1 = $this->createSuperFile($path, $filename);
                    if (empty($re1['error'])) {
                        $data['icoarr'][] = $re1;
                        return $data;
                    } else {
                        $data['error'] = $re1['error'];
                        return $data;
                    }
                } else {
                    return true;
                }
            }
        } else {

            $re1 = $this->upload($fileContent, $path, $filename);

            if (empty($re1['error'])) {
                $data['icoarr'][] = $re1;
                return $data;
            } else {
                $data['error'] = $re1['error'];
                return $data;
            }
        }
    }
}

?>
