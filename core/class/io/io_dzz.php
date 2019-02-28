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
@set_time_limit(0);
@ini_set('max_execution_time', 0);

class io_dzz extends io_api
{
    public function listFiles($rid, $by = 'name', $asc = 'DESC', $limit = 0, $force = 0)
    {
        $data = array();
        $icoarr = C::t('resources_path')->fetch($rid);
        switch ($by) {
            case 'name':
                $orderby = 'name';
                break;
            case 'size':
                $orderby = 'size';
                break;
            case 'type':
                $orderby = array('type', 'ext');
                break;
            case 'time':
                $orderby = 'dateline';
                break;

        }
        if ($limit) list($start, $perpage) = explode('-', $limit);
        foreach (C::t('resources')->fetch_all_by_pfid($icoarr['oid'], '', $perpage, $by, $asc, $start) as $value) {
            $data[$value['rid']] = $value;
        }
        return $data;
    }

    /**
     * 获取空间配额信息
     * @return string
     */
    public function MoveToSpace($path, $attach = array())
    {
        global $_G;
        $obz = io_remote::getBzByRemoteid($attach['remote']);

        if ($obz == 'dzz') {
            return array('error' => lang('same_storage_area'));
        } else {
            $url = IO::getFileUri($obz . '/' . $attach['attachment']);
            if (is_array($url)) return array('error' => $url['error']);
            $target = $_G['setting']['attachdir'] . './' . $attach['attachment'];
            $targetpath = dirname($target);
            dmkdir($targetpath);
            try {
                if (file_put_contents($target, fopen($url, 'rb')) === false) {
                    return array('error' => lang('error_occurred_written_local'));
                }
            } catch (Exception $e) {
                return array('error' => $e->getMessage());
            }
            if (md5_file($target) != $attach['md5']) {
                return array('error' => lang('file_transfer_errors'));
            }
        }
        return true;

    }

    public function rename($rid, $text)
    {
        //查找当前目录下是否有同名文件
        $icoarr = C::t('resources')->fetch_info_by_rid($rid);
        $ext = '';
        $namearr = explode('.', $text);
        if (count($namearr) > 1) {
            $ext = $namearr[count($namearr) - 1];
            unset($namearr[count($namearr) - 1]);
            $ext = $ext ? ('.' . $ext) : '';
        }
        $tname = implode('.', $namearr);
        //如果有后缀名并且是文件
        if ($ext && $icoarr['ext']) {
            //如果后缀名和原后缀名不同,则加上原后缀名组成新的文件名
            if ($ext != '.' . $icoarr['ext']) {
                $text = $tname . $ext . '.' . $icoarr['ext'];
            } else {
                $text = $tname . $ext;
            }
        } elseif (!$ext && $icoarr['ext']) {
            $text = $tname . $ext . '.' . $icoarr['ext'];
        }
        /*$name=preg_replace("/\(\d+\)/i",'',$tname).'('.($i+1).')'.$ext;*/
        if ($icoarr['name'] != $text && ($ricoid = io_dzz::getRepeatIDByName($text, $icoarr['pfid'], ($icoarr['type'] == 'folder') ? true : false))) {//如果目录下有同名文件
            return array('error' => lang('filename_already_exists'));
        }
        if (!$arr = C::t('resources')->rename_by_rid($rid, $text)) {
            return array('error' => 'Not modified!');
        }
        $icoarr['name'] = $text;
        return $icoarr;
    }

    public function parsePath($path)
    {
        return $path;
    }

    //根据路径获取目录树的数据；
    function getFolderDatasByPath($fid)
    {
        $fidarr = getTopFid($fid);
        $folderarr = array();
        foreach ($fidarr as $fid) {
            $folderarr[$fid] = C::t('folder')->fetch_by_fid($fid);
        }
        return $folderarr;
    }

    //获取文件流地址
    public function getStream($path, $fop = '')
    {
        global $_G;//123
        if (strpos($path, 'attach::') === 0) {
            $attach = C::t('attachment')->fetch(intval(str_replace('attach::', '', $path)));
			Hook::listen('io_dzz_getstream_attach',$attach);//挂载点
            $bz = io_remote::getBzByRemoteid($attach['remote']);
            if ($bz == 'dzz') {
                return $_G['setting']['attachdir'] . $attach['attachment'];
            } else {
                return IO::getStream($bz . '/' . $attach['attachment'], $fop);
            }
        } elseif (strpos($path, 'dzz::') === 0) {
            if (strpos($path, '../') !== false) return '';
            return $_G['setting']['attachdir'] . preg_replace("/^dzz::/", '', $path);
        } elseif (strpos($path, 'TMP::') === 0) {
            $tmp = str_replace('\\', '/', sys_get_temp_dir());
            return str_replace('TMP::', $tmp . '/', $path);
        } elseif (preg_match('/\w{32}/i', $path)) {
            $icoid = trim($path);
            $icoarr = C::t('resources')->fetch_by_rid($path);
			Hook::listen('io_dzz_getstream_attach',$icoarr);//挂载点
            $bz = io_remote::getBzByRemoteid($icoarr['remote']);
            if ($bz == 'dzz') {
                if ($icoarr['type'] == 'video' || $icoarr['type'] == 'dzzdoc' || $icoarr['type'] == 'link') {
                    return $icoarr['url'];
                }
                return $_G['setting']['attachdir'] . $icoarr['attachment'];
            } else {
                return IO::getStream($bz . '/' . $icoarr['attachment'], $fop);
            }
        } elseif (preg_match('/^dzz:[gu]id_\d+:.+?/i', $path)) {
            $dir = dirname($path) . '/';
            if (!$pfid = C::t('resources_path')->fetch_fid_bypath($dir)) {
                return false;
            }
            $filename = preg_replace('/^.+[\\\\\\/]/', '', $path);
            //$filename = basename($path);
            if (!$rid = DB::result_first("select rid from %t where pfid = %d and name = %s", array('resources', $pfid, $filename))) {
                return false;
            }
            $icoarr = C::t('resources')->fetch_by_rid($rid);
			Hook::listen('io_dzz_getstream_attach',$icoarr);//挂载点
            $bz = io_remote::getBzByRemoteid($icoarr['remote']);
            if ($bz == 'dzz') {
                if ($icoarr['type'] == 'video' || $icoarr['type'] == 'dzzdoc' || $icoarr['type'] == 'link') {
                    return $icoarr['url'];
                }
				
                return $_G['setting']['attachdir'] . $icoarr['attachment'];
            } else {
                return IO::getStream($bz . '/' . $icoarr['attachment'], $fop);
            }
        } else {
            return $path;
        }
        return '';
    }

    //获取文件的真实地址
    public function getFileUri($path, $fop = '')
    {
        global $_G;
        if (strpos($path, 'attach::') === 0) {
            $attach = C::t('attachment')->fetch(intval(str_replace('attach::', '', $path)));
			Hook::listen('io_dzz_getstream_attach',$attach);//挂载点
			$bz = io_remote::getBzByRemoteid($attach['remote']);
            if ($bz == 'dzz') {
                return $_G['siteurl'] . $_G['setting']['attachurl'] . $attach['attachment'];
            } else {
                return IO::getFileUri($bz . '/' . $attach['attachment'], $fop);
            }
            
        } elseif (strpos($path, 'dzz::') === 0) {
            if (strpos($path, './') !== false) return '';
            return $_G['siteurl'] . $_G['setting']['attachurl'] . preg_replace("/^dzz::/", '', $path);
        } elseif (strpos($path, 'TMP::') === 0) {
            return $_G['siteurl'] . 'index.php?mod=io&op=getStream&path=' . dzzencode($path);
        } else {

            $icoarr = C::t('resources')->fetch_by_rid($path);
            if ($icoarr['aid']) {
                $attachment = C::t('attachment')->fetch($icoarr['aid']);
                $icoarr['remote'] = $attachment['remote'];
				Hook::listen('io_dzz_getstream_attach',$icoarr);//挂载点
                $bz = io_remote::getBzByRemoteid($icoarr['remote']);
                if ($bz == 'dzz') {
                    if ($icoarr['type'] == 'video' || $icoarr['type'] == 'dzzdoc' || $icoarr['type'] == 'link') {
                        return $icoarr['url'];
                    }
                    return $_G['siteurl'] . $_G['setting']['attachurl'] . $icoarr['attachment'];
                } else {
                    return IO::getFileUri($bz . '/' . $icoarr['attachment'], $fop);
                }
            } else {
                //待修改
                return $_G['siteurl'] . $icoarr['url'];
            }


        }
        return '';
    }

    //获取文件内容
    public function getFileContent($path)
    {
        $url = self::getStream($path);
        return file_get_contents($url);
    }

    public function deleteThumb($path, $width = 0, $height = 0)
    {
        global $_G;
        $data = IO::getMeta($path);
        $imgcachePath = './imgcache/';
        $cachepath = str_replace('//', '/', str_replace(':', '/', $data['attachment']));

        foreach ($_G['setting']['thumbsize'] as $value) {
			$target = $imgcachePath . ($cachepath) . '.' . $value['width'] . '_' . $value['height'] . '_1.jpeg';
			$target1 = $imgcachePath . ($cachepath) . '.' . $value['width'] . '_' . $value['height'] . '_2.jpeg';
			@unlink($_G['setting']['attachdir'].$target);
			@unlink($_G['setting']['attachdir'].$target1);
        }
    }

    public function createThumb($path, $size, $width = 0, $height = 0, $thumbtype = 1)
    {
        global $_G;
        if (!$data = IO::getMeta($path)) return false;
        $imgcachePath = 'imgcache/';
        $cachepath = str_replace('//', '/', str_replace(':', '/', $data['attachment'] ? $data['attachment'] : $data['path']));
       $target = $imgcachePath . ($cachepath) . '.' . $width . '_' . $height . '_'.$thumbtype.'.jpeg';
        if (@getimagesize($_G['setting']['attachdir'] . './' . $target)) {
            return 2;//已经存在缩略图
        }
        $fileurls = array();
        Hook::listen('thumbnail', $fileurls, $path);//生成缩略图绝对和相对地址；
        if (!$fileurls) {
            $fileurls = array('fileurl' => self::getFileUri($path), 'filedir' => self::getStream($path));
        }
        $filepath = $fileurls['filedir'];
      
        if (intval($width) < 1) $width = $_G['setting']['thumbsize'][$size]['width'];
        if (intval($height) < 1) $height = $_G['setting']['thumbsize'][$size]['height'];

        if (!$imginfo = @getimagesize($filepath)) {
            return -1; //非图片不能生成
        }

        if (($imginfo[0] < $width && $imginfo[1] < $height)) {
            return 3;//小于要求尺寸，不需要生成
        }

        //生成缩略图
        include_once libfile('class/image');
        $target_attach = $_G['setting']['attachdir'] . './' . $target;
        $targetpath = dirname($target_attach);
        dmkdir($targetpath);
        $image = new image();
        //Thumb($source, $target, $thumbwidth, $thumbheight, $thumbtype = 1, $nosuffix = 0)
        //Cropper($source, $target, $dstwidth, $dstheight, $srcx = 0, $srcy = 0, $srcwidth = 0, $srcheight = 0)
        if ($thumb = $image->Thumb($filepath, $target, $width, $height, $thumbtype) ) {
            return 1;//生成缩略图成功
        } else {
            return 0;//生成缩略图失败
        }
    }

    //获取缩略图
    public function getThumb($path, $width = 0, $height = 0, $original = false, $returnurl = false, $thumbtype = 1)
    {
        global $_G;
        //$path:可能的值 icoid,'dzz::dzz/201401/02/wrwsdfsdfasdsf.txt'等dzzPath格式；
        if (!$data = IO::getMeta($path)) return false;
        $enable_cache = true; //是否启用缓存
        $quality = 80;
        $imgcachePath = 'imgcache/';
        $cachepath = str_replace('//', '/', str_replace(':', '/', $data['attachment'] ? $data['attachment'] : $data['path']));
        $target = $imgcachePath . ($cachepath) . '.' . $width . '_' . $height . '_'.$thumbtype.'.jpeg';
        if (!$original && $enable_cache && @getimagesize($_G['setting']['attachdir'] . './' . $target)) {
            if ($returnurl) return $_G['setting']['attachurl'] . '/' . $target;
            $file = $_G['setting']['attachdir'] . './' . $target;
            IO::output_thumb($file);
        }


        $fileurls = array();
        Hook::listen('thumbnail', $fileurls, $path);//调用挂载点程序生成缩略图绝对和相对地址；
        if (!$fileurls) {
            $fileurls = array('fileurl' => self::getFileUri($path), 'filedir' => self::getStream($path));
        }
        //非图片类文件的时候，直接获取文件后缀对应的图片
        if (!$imginfo = @getimagesize($fileurls['filedir'])) {
            $imgurl = geticonfromext($data['ext'], $data['type']);
            if ($returnurl) return $imgurl;//$_G['setting']['attachurl'].'./'.$data['attachment'];
            $file = $imgurl;//$_G['setting']['attachdir'].'./'.$data['attachment'];
            IO::output_thumb($file);
        }
        //返回原图的时候
        if ($original) {
            if ($returnurl) return $fileurls['fileurl'];//$_G['setting']['attachurl'].'./'.$data['attachment'];
            $file = $fileurls['filedir'];//$_G['setting']['attachdir'].'./'.$data['attachment'];
            IO::output_thumb($file);
        }
        //图片小于缩略图宽高的不生成直接返回原图
        if (($imginfo[0] < $width && $imginfo[1] < $height)) {
            if ($returnurl) return $fileurls['fileurl'];
            $file = $fileurls['filedir'];//$_G['setting']['attachdir'].'./'.$data['attachment'];
            IO::output_thumb($file);
        }

        //生成缩略图
        include_once libfile('class/image');
        $target_attach = $_G['setting']['attachdir'] . './' . $target;
        $targetpath = dirname($target_attach);
        dmkdir($targetpath);
        $filepath = $fileurls['filedir'];
        $image = new image();
        if ($thumb = $image->Thumb($filepath, $target, $width, $height, $thumbtype)) {
            //if ($thumb = $image->Thumb($file, $target, $width, $height, 1)) {
            if ($returnurl) return $_G['setting']['attachurl'] . '/' . $target;
            $file = $target_attach;
            IO::output_thumb($file);
        } else {
            if ($returnurl) return $fileurls['fileurl'];
            $file = $fileurls['filedir'];
            IO::output_thumb($file);
        }
        exit();
    }

    /*//将文件内容保存成文件，放入附件表
    private function saveFilecontentToAttach($icoarr,$filecontent){
        global $_G;
        //保存的文件名
        $filename = $icoarr['name'];
        $pathinfo = pathinfo($filename);
        //获取后缀名
        $ext = strtolower($pathinfo['extension']);
        //生成路径
        $target = $this->getPath($ext ? ('.' . $ext) : '', 'dzz');

        if (!empty($fileContent) && !file_put_contents($_G['setting']['attachdir'] . $target, $fileContent)) {
            return array('error' => lang('cache_file_error'));
        }
        //判断空间大小
        $gid = DB::result_first("select gid from %t where fid=%d", array('folder', $icoarr['pfid']));
        if (!SpaceSize(filesize($_G['setting']['attachdir'] . $target), $gid)) {
            @unlink($_G['setting']['attachdir'] . $target);
            return array('error' => lang('inadequate_capacity_space'));
        }
        //保存到附件表
        if ($attach = $this->save($target, $filename)) {
            return $attach;
        } else {
            return array('error' => 'Could not save uploaded file. The upload was cancelled, or server error encountered');
        }
    }*/
  /*  //将文件内容作为新版本保存(用于保存时，选择文件覆盖的情形)
    private function coverFilebynewVersion($attach, $icoarr)
    {
        global $_G;
        $setting = $_G['setting'];
        //当前文件版本数量
        $versionnum = DB::result_first("select count(*) from %t where rid = %s", array('resources_version', $icoarr['rid']));
        //
        //版本开启
        $vperm = (!isset($setting['fileVersion']) || $setting['fileVersion']) ? true : false;
        //版本数量限制
        $vnumlimit = isset($setting['fileVersionNumber']) ? intval($setting['fileVersionNumber']) : 0;
        $covertype = 0;
        //当上传版本开启，上传版本数量不限制；或者上传版本开启，文件版本数量未达到上限：设置当前文件为最新版本
        if ($vperm && (!$vnumlimit || ($vnumlimit && ($versionnum < $vnumlimit)))) {
            $covertype = 1;
            //当上传版本关闭，并且文件包含版本；或者上传版本开启，并且版本数量达到上限：剔除最老版本，并设置新文件为主版本
        } elseif ((!$vperm && $versionnum > 0) || ($vperm && $vnumlimit && $versionnum > $vnumlimit)) {
            $covertype = 2;
            //当上传版本关闭，且当前文件不含有版本：替换当前文件
        } elseif (!$vperm && !$versionnum) {
            $covertype = 3;
        }
        if ($covertype == 1) {
            $setarr = array(
                'uid' => $_G['uid'],
                'username' => $_G['username'],
                'name' => $attach['filename'],
                'aid' => $attach['aid'],
                'size' => $attach['filesize'],
                'ext' => $attach['filetype'],
                'dateline' => TIMESTAMP
            );
            $return = C::t('resources_version')->add_new_version_by_rid($icoarr['rid'], $setarr);
        } elseif ($covertype == 2) {
            $setarr = array(
                'uid' => $_G['uid'],
                'username' => $_G['username'],
                'name' => $attach['filename'],
                'aid' => $attach['aid'],
                'size' => $attach['filesize'],
                'ext' => $attach['filetype'],
                'dateline' => TIMESTAMP
            );
            $return = C::t('resources_version')->add_new_version_by_rid($icoarr['rid'], $setarr);
            $vid = DB::result_first("select min(dateline),vid from %t where rid = %s ", array('resources_version', $icoarr['rid']));
            C::t('rescources_version')->delete_by_vid($vid, $icoarr['rid']);
        } elseif ($covertype == 3) {

        }
    }*/
    //@param number $rid  文件的rid
    //@param string $message  文件的新内容
    public function setFileContent($rid, $fileContent, $force = false, $nocover = true)
    {
        global $_G;
        if (!$icoarr = C::t('resources')->fetch_by_rid($rid)) {
            return array('error' => lang('file_not_exist'));
        }
        if ($icoarr['type'] != 'document' && $icoarr['type'] != 'attach' && $icoarr['type'] != 'image') {
            return array('error' => lang('no_privilege'));
        }

        $gid = DB::result_first("select gid from %t where fid=%d", array('folder', $icoarr['pfid']));
        if (!$force && !perm_check::checkperm('edit', $icoarr)) {
            return array('error' => lang('no_privilege'));
        }
        if (!$attach = getTxtAttachByMd5($fileContent, $icoarr['name'], $icoarr['ext'])) {
            return array('error' => lang('file_save_failure'));
        }
        $covertype = 0;
        if ($nocover) {//判断是否是覆盖
            $setting = $_G['setting'];
            //当前文件版本数量
            $versionnum = DB::result_first("select count(*) from %t where rid = %s", array('resources_version', $icoarr['rid']));
            //
            //版本开启
            $vperm = (!isset($setting['fileVersion']) || $setting['fileVersion']) ? true : false;
            //版本数量限制
            $vnumlimit = isset($setting['fileVersionNumber']) ? intval($setting['fileVersionNumber']) : 0;
            //当上传版本开启，上传版本数量不限制；或者上传版本开启，文件版本数量未达到上限：设置当前文件为最新版本
            if ($vperm && (!$vnumlimit || ($vnumlimit && ($versionnum < $vnumlimit)))) {
                $covertype = 1;
                //当上传版本关闭，并且文件包含版本；或者上传版本开启，并且版本数量达到上限：剔除最老版本，并设置新文件为主版本
            } elseif ((!$vperm && $versionnum > 0) || ($vperm && $vnumlimit && $versionnum >= $vnumlimit)) {
                $covertype = 2;
                //当上传版本关闭，且当前文件不含有版本：替换当前文件
            } elseif (!$vperm && !$versionnum) {
                $covertype = 0;
            }
        }
        if ($covertype) {
            if($covertype == 2){
                $vinfo = DB::fetch_first("select min(dateline),vid from %t where rid = %s ", array('resources_version', $icoarr['rid']));
                C::t('resources_version')->delete_by_vid($vinfo['vid'], $icoarr['rid']);
            }
            $setarr = array(
                'uid' => $_G['uid'],
                'username' => $_G['username'],
                'name' => $icoarr['name'],
                'aid' => $attach['aid'],
                'size' => $attach['filesize'],
                'ext' => $attach['filetype'],
                'dateline' => TIMESTAMP
            );
            $return = C::t('resources_version')->add_new_version_by_rid($icoarr['rid'], $setarr,$force);
            if($return['error']){
                return array('error'=>$return['error']);
            }
        } else {
            //计算用户新的空间大小
            $csize = $attach['filesize'] - $icoarr['size'];
            //重新计算用户空间
            if ($csize) {
                if (!SpaceSize($csize, $gid, 0, $icoarr['uid'])) {

                    return array('error' => lang('inadequate_capacity_space'));
                }
                SpaceSize($csize, $gid, 1, $icoarr['uid']);
            }
            $oldaid = $icoarr['aid'];
            //更新附件数量
            if ($oldaid != $attach['aid']) {
                C::t('resources')->update_by_rid($rid, array('size' => $attach['filesize']));
                C::t('resources_statis')->add_statis_by_rid($rid, array('editdateline' => TIMESTAMP));
                C::t('resources_attr')->update_by_skey($icoarr['rid'], $icoarr['vid'], array('aid' => $attach['aid']));
                C::t('attachment')->update($attach['aid'], array('copys' => $attach['copys'] + 1));
                C::t('attachment')->delete_by_aid($oldaid);
            }
            $path = C::t('resources_path')->fetch_pathby_pfid($icoarr['pfid']);
            $path = preg_replace('/dzz:(.+?):/', '', $path) ? preg_replace('/dzz:(.+?):/', '', $path) : '';
            $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($icoarr['pfid'], $icoarr['gid']);
            $eventdata = array(
                'title' => $icoarr['name'],
                'aid' => $icoarr['aid'],
                'username' => $_G['username'],
                'uid' => $_G['uid'],
                'path' => $icoarr['path'],
                'position' => $path,
                'hash' => $hash
            );
            $event = 'edit_file';
            C::t('resources_event')->addevent_by_pfid($icoarr['pfid'], $event, 'edit', $eventdata, $icoarr['gid'], $icoarr['rid'], $icoarr['name']);
        }
        return C::t('resources')->fetch_by_rid($rid);
    }
    //查找目录下的同名文件
    //@param string $filename  文件名称
    //@param number $fid  目录id
    //@param bool $isfolder  查找同名目录
    //return icoid  返回icoid
    public function getRepeatIDByName($filename, $fid, $isfolder = false)
    {

        $sql = "pfid=%d and name=%s and isdelete<1";
        if ($isfolder) $sql .= " and type='folder'";
        else $sql .= " and type!='folder'";
        if ($rid = DB::result_first("select rid from %t where $sql ", array('resources', $fid, $filename))) {
            return $rid;
        } else return false;
    }

    //获取icosdata
    public function getMeta($icoid)
    {
        if (strpos($icoid, 'dzz::') === 0) {
            $attachment = preg_replace('/^dzz::/i', '', $icoid);
            $name = array_pop(explode('/', $icoid));
            $ext = array_pop(explode('.', $name));
            return array('icoid' => $icoid,
                'name' => $name,
                'ext' => $ext,
                'size' => filesize(getglobal('setting/attachdir') . $attachment),
                'url' => getglobal('setting/attachurl') . $attachment,
                'path' => $icoid,
                'md5' => md5_file(getglobal('setting/attachdir') . $attachment),
                'attachment' => $attachment,
                'dpath' => dzzencode($icoid),
				'sperm' => perm_FileSPerm::typePower('attachment'),
				'bz'=>''
            );

        } elseif (strpos($icoid, 'attach::') === 0) {
            $attach = C::t('attachment')->fetch(intval(str_replace('attach::', '', $icoid)));
            $bz = io_remote::getBzByRemoteid($attach['remote']);
            if ($bz == 'dzz') {
                return array('icoid' => $icoid,
                    'name' => $attach['filename'],
                    'ext' => $attach['filetype'],
                    'apath' => dzzencode('attach::' . $attach['aid']),
                    'dpath' => dzzencode('attach::' . $attach['aid']),
                    'path' => 'attach::' . $attach['aid'],
                    'attachment' => $attach['attachment'],
                    'size' => $attach['filesize'],
                    'url' => getAttachUrl($attach),
                    'md5' => $attach['md5'],
                    'bz' => '',
					'sperm' => perm_FileSPerm::typePower('attachment')
                );
            } else {
                $path = $bz.'/'.$attach['attachment'];
                return  IO::getMeta($path);
            }

        } elseif (strpos($icoid, 'TMP::') === 0) {
            $file = self::getStream($icoid);
            $attachment = preg_replace('/^TMP::/i', '', $icoid);
            $pathinfo = pathinfo($file);
            return array('icoid' => md5($icoid),

                'name' => $pathinfo['basename'],
                'ext' => $pathinfo['extension'],
                'size' => filesize($file),
                'path' => $icoid,
                'dpath' => dzzencode($icoid),
                'url' => '',
                'bz' => ''

            );
        } elseif (preg_match('/^dzz:[gu]id_\d+:.+?/i', $icoid)) {
            $dir = dirname($icoid) . '/';
            if (!$pfid = C::t('resources_path')->fetch_fid_bypath($dir)) {
                return false;
            }
            $filename = basename($icoid);
            if (!$rid = DB::result_first("select rid from %t where pfid = %d and name = %s", array('resources', $pfid, $filename))) {
                return false;
            }
            return C::t('resources')->fetch_by_rid($rid);
        } elseif (preg_match('/\w{32}/i', $icoid)) {
            return C::t('resources')->fetch_by_rid($icoid);
        } else {
            return false;//C::t('resources')->fetch_by_icoid($icoid);
        }
    }

    public function getFolderByIcosdata($data)
    {
        if ($data['type'] == 'folder') {
            return C::t('folder')->fetch_by_fid($data['oid']);
        }
        return array();
    }

    //打包下载文件
    public function zipdownload($paths, $filename = '',$checkperm = true)
    {
        global $_G;
        $paths = (array)$paths;
        set_time_limit(0);
        if (empty($filename)) {
            $meta = self::getMeta($paths[0]);

            $filename = $meta['name'] . (count($paths) > 1 ? lang('wait') : '');
        }

        $eventdata = array('username' => getglobal('username'), 'dateline' => TIMESTAMP);

        $infos = C::t('resources')->fetch_info_by_rid($paths[0]);

        $path = C::t('resources_path')->fetch_pathby_pfid($infos['pfid']);
        $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($infos['pfid'], $infos['gid']);
        $eventdata['position'] = preg_replace('/dzz:(.+?):/', '', $path);
        $eventdata['hash'] = $hash;
        $statisdata = array(
            'downs' => 1,
        );
        C::t('resources_statis')->add_statis_by_rid($paths, $statisdata);
        if (count($paths) > 1) {
            $filenames = '';

            foreach (DB::fetch_all("select name from %t where rid in(%n)", array('resources', $paths)) as $v) {
                $filenames .= $v['name'] . ',';
            }
            $filenames = substr($filenames, 0, -1);

            $eventdata['files'] = $filenames;

            C::t('resources_event')->addevent_by_pfid($infos['pfid'], 'downfiles', 'down', $eventdata, $infos['gid'], '', $filenames);
        } else {
            $eventdata['files'] = $infos['name'];
            C::t('resources_event')->addevent_by_pfid($infos['pfid'], 'downfile', 'down', $eventdata, $infos['gid'], $infos['rid'], $infos['name']);
        }


        $filename = (strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($filename) : $filename);
        //$data=self::getFolderInfo($path);
        include_once libfile('class/ZipStream');
        $zip = new ZipStream($filename . ".zip");
        $data = self::getFolderInfo($paths, '', $zip,$checkperm);

        $zip->finalize();
    }

    public function getFolderInfo($paths, $position = '', &$zip,$checkperm = true)
    {
        static $data = array();
        try {
            foreach ($paths as $path) {
                $meta = self::getMeta($path);

                switch ($meta['type']) {
                    case 'folder':
                        $lposition = $position . $meta['name'] . '/';
                        $contents = C::t('resources')->fetch_by_pfid($meta['oid'],'',$checkperm);
                        foreach ($contents as $key => $value) {
                            self::getFolderInfo(array($value['rid']), $lposition, $zip);
                        }
                        break;
                    case 'discuss':
                    case 'dzzdoc':
                    case 'shortcut':
                    case 'user':
                    case 'link':
                    case 'music':
                    case 'video':
                    case 'topic':
                    case 'app'://这些内容不能移动到api网盘内；
                        break;
                    default:
                        $meta['url'] = IO::getStream($meta['path']);
                        $meta['position'] = $position . ($meta['ext']?(preg_replace("/\.".$meta['ext']."$/i",'',$meta['name']).'.'.$meta['ext']):$meta['name']);
                        /*$data[$meta['icoid']]=$meta;*/
                        $zip->addLargeFile(fopen($meta['url'], 'rb'), $meta['position'], $meta['dateline']);
                }
            }
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
            return $data;
        }
        return $data;
    }

    //下载
    public function download($paths, $filename = '',$checkperm = true)
    {
        global $_G;
        $paths = (array)$paths;
        if (count($paths) > 1) {
            self::zipdownload($paths, $filename,$checkperm);
            exit();
        } else {
            $path = $paths[0];
        }
        @set_time_limit(0);
        $attachexists = FALSE;
        if (strpos($path, 'attach::') === 0) {
            $attachment = C::t('attachment')->fetch(intval(str_replace('attach::', '', $path)));
            $attachment['name'] = $filename ? $filename : $attachment['filename'];
            $path = getDzzPath($attachment);
            $attachurl = IO::getStream($path);
        } elseif (strpos($path, 'dzz::') === 0) {
            $attachment = array('attachment' => preg_replace("/^dzz::/i", '', $path), 'name' => $filename ? $filename : substr(strrpos($path, '/')));
            $attachurl = $_G['setting']['attachdir'] . $attachment['attachment'];
        } elseif (strpos($path, 'TMP::') === 0) {
            $tmp = str_replace('\\', '/', sys_get_temp_dir());
            $attachurl = str_replace('TMP::', $tmp . '/', $path);
            $pathinfo = pathinfo($attachurl);
            $attachment = array('attachment' => $attachurl, 'name' => $filename ? $filename : $pathinfo['basename']);

        } elseif (preg_match('/\w{32}/i', $path)) {
            $icoid = trim($path);
            $icoarr = C::t('resources')->fetch_by_rid($path);
            if (!$icoarr['rid']) {
                topshowmessage(lang('attachment_nonexistence'));
            } elseif ($icoarr['type'] == 'folder') {
                self::zipdownload($path,$filename,$checkperm);
                exit();
            }
            if (!$icoarr['aid']) {
                topshowmessage(lang('attachment_nonexistence'));
            }
            $attachment = $icoarr;
            $attachurl = IO::getStream($path);
            //添加事件
            if ($attachurl) {
                $eventdata = array('username' => getglobal('username'), 'dateline' => TIMESTAMP);

                $infos = C::t('resources')->fetch_info_by_rid($path);

                $path = C::t('resources_path')->fetch_pathby_pfid($infos['pfid']);
                $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($infos['pfid'], $infos['gid']);
                $eventdata['position'] = $icoarr['relpath'];

                $eventdata['files'] = $icoarr['name'];
                $eventdata['hash'] = $hash;
                $statisdata = array(
                    'downs' => 1,
                );
                C::t('resources_statis')->add_statis_by_rid($icoarr['rid'], $statisdata);
                if (!C::t('resources_event')->addevent_by_pfid($icoarr['pfid'], 'downfile', 'down', $eventdata, $icoarr['gid'], $icoarr['rid'], $icoarr['name'])) {
                    return false;
                }
            }
        } elseif (preg_match('/^dzz:[gu]id_\d+:.+?/i', $path)) {
            $dir = dirname($path) . '/';
            if (!$pfid = C::t('resources_path')->fetch_fid_bypath($dir)) {
                return false;
            }
            $filename = preg_replace('/^.+[\\\\\\/]/', '', $path);
            //如果是文件夹
            if (!$filename) {
                $patharr = preg_split('/[\\\\\\/]/', $path);
                $patharr = array_filter($patharr);
                $filename = end($patharr);
            }

            if (!$rid = DB::result_first("select rid from %t where pfid = %d and name = %s", array('resources', $pfid, $filename))) {
                return false;
            }
            $icoarr = C::t('resources')->fetch_by_rid($rid);
            if (!$icoarr['rid']) {
                topshowmessage(lang('attachment_nonexistence'));
            } elseif ($icoarr['type'] == 'folder') {
                self::zipdownload($path, $filename,$checkperm);
                exit();
            }
            if (!$icoarr['aid']) {
                topshowmessage(lang('attachment_nonexistence'));
            }
            $attachment = $icoarr;
            $attachurl = IO::getStream($path);
            //添加事件
            if ($attachurl) {
                $eventdata = array('username' => getglobal('username'), 'dateline' => TIMESTAMP);

                $infos = C::t('resources')->fetch_info_by_rid($path);

                $path = C::t('resources_path')->fetch_pathby_pfid($infos['pfid']);
                $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($infos['pfid'], $infos['gid']);
                $eventdata['position'] = $icoarr['relpath'];

                $eventdata['files'] = $icoarr['name'];
                $eventdata['hash'] = $icoarr['hash'];
                $statisdata = array(
                    'downs' => 1,
                );
                C::t('resources_statis')->add_statis_by_rid($icoarr['rid'], $statisdata);
                if (!C::t('resources_event')->addevent_by_pfid($icoarr['pfid'], 'downfile', 'down', $eventdata, $icoarr['gid'], $icoarr['rid'], $icoarr['name'])) {
                    return false;
                }
            }
        }
        $filesize = !$attachment['remote'] ? filesize($attachurl) : $attachment['filesize'];
        if ($attachment['ext'] && strpos(strtolower($attachment['name']), $attachment['ext']) === false) {
            $attachment['name'] .= '.' . $attachment['ext'];
        }
        $attachment['name'] = '"' . (strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'Edge') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($attachment['name']) : ($attachment['name'])) . '"';
        $d = new FileDownload();
        $d->download($attachurl, $attachment['name'], $filesize, $attachment['dateline'], true);
        exit();
    }

    //删除
    //当文件在回收站时，彻底删除；
    //$force 真实删除，不放入回收站
    public function Delete($path, $force = false)
    {
        global $_G;
        if (strpos($path, 'dzz::') === 0) {
            if (strpos($path, './') !== false) return false;
            @unlink($_G['setting']['attachdir'] . preg_replace('/^dzz::/i', '', $path));
            return true;

        } elseif (strpos($path, 'attach::') === 0) {
            if (strpos($path, './') !== false) return false;
            return C::t('attachment')->delete_by_aid(intval(str_replace('attach::', '', $path)));
        } elseif (strpos($path, 'TMP::') === 0) {
            $tmp = str_replace('\\', '/', sys_get_temp_dir());
            return @unlink(str_replace('TMP::', $tmp . '/', $path));
        } elseif (preg_match('/^dzz:[gu]id_\d+:.+?/i', $path)) {
            $dir = dirname($path) . '/';
            if (!$pfid = C::t('resources_path')->fetch_fid_bypath($dir)) {
                return false;
            }
            $filename = preg_replace('/^.+[\\\\\\/]/', '', $path);
            //如果是文件夹
            if (!$filename) {
                $patharr = preg_split('/[\\\\\\/]/', $path);
                $patharr = array_filter($patharr);
                $filename = end($patharr);
            }

            if (!$icoarr = DB::fetch_first("select * from %t where pfid = %d and name = %s and isdelete < 1", array('resources', $pfid, $filename))) {
                return array('rid' => $icoarr['rid'], 'error' => lang('file_longer_exists'));
            }
            $size = 0;
            if ($icoarr['type'] == 'folder') {
                $contains = C::t('resources')->get_contains_by_fid($icoarr['oid'], true);
                $size = $contains['size'];
            } else {
                if ($icoarr['vid'] > 0) {
                    $size = DB::result_first("select sum(size) from %t where rid = %s", array('resources_version', $icoarr['rid']));
                } else {
                    $size = $icoarr['size'];
                }
            }
            if (perm_check::checkperm('delete', $icoarr)) {
                if ($force) {//强制彻底删除
                    C::t('resources')->delete_by_rid($path, true);
                } elseif ($icoarr['isdelete'] > 0) {//删除状态彻底删除
                    C::t('resources')->delete_by_rid($path, false);
                } else {//非删除状态删除到回收站
                    $return = C::t('resources')->recyle_by_rid($icoarr['rid']);
                    if ($return['error']) {
                        return $return;
                    }
                }
            } else {
                return array('rid' => $icoarr['rid'], 'error' => lang('no_privilege'));
            }
            if ($size > 0 && !$icoarr['isdelete']) {
                SpaceSize(-$size, $icoarr['gid'], true);
            }
            return array('rid' => $icoarr['rid'], 'name' => $icoarr['name']);
        } elseif (preg_match('/\w{32}/i', $path)) {//rid删除
            try {
                if (!$icoarr = C::t('resources')->fetch_info_by_rid($path)) {
                    return array('rid' => $path, 'error' => lang('file_longer_exists'));
                }
                $size = 0;
                if ($icoarr['type'] == 'folder') {
                    $contains = C::t('resources')->get_contains_by_fid($icoarr['oid'], true);
                    $size = $contains['size'];
                } else {
                    if ($icoarr['vid'] > 0) {
                        $size = DB::result_first("select sum(size) from %t where rid = %s", array('resources_version', $icoarr['rid']));
                    } else {
                        $size = $icoarr['size'];
                    }
                }
                if (perm_check::checkperm('delete', $icoarr)) {
                    if ($force) {//强制彻底删除
                        C::t('resources')->delete_by_rid($path, true);
                    } elseif ($icoarr['isdelete'] > 0) {//删除状态彻底删除
                        C::t('resources')->delete_by_rid($path, false);
                    } else {//非删除状态删除到回收站
                        $return = C::t('resources')->recyle_by_rid($icoarr['rid']);
                        if ($return['error']) {
                            return $return;
                        }
                    }
                } else {
                    return array('rid' => $icoarr['rid'], 'error' => lang('no_privilege'));
                }
                if ($size > 0 && !$icoarr['isdelete']) {
                    SpaceSize(-$size, $icoarr['gid'], true);
                }
                return array('rid' => $icoarr['rid'], 'name' => $icoarr['name']);
            } catch (Exception $e) {
                return array('error' => $e->getMessage());
            }
        }
    }

    //检查名称是否重复
    public function check_name_repeat($name, $pfid)
    {
        return DB::result_first("select rid from " . DB::table('resources') . " where name='{$name}' and  pfid='{$pfid}'");
    }

    //过滤文件名称
    public function name_filter($name)
    {
        return str_replace(array('/', '\\', ':', '*', '?', '<', '>', '|', '"', "\n"), '', $name);
    }

    //获取不重复的目录名称
    public function getFolderName($name, $pfid)
    {
        static $i = 0;
        $name = self::name_filter($name);
        //echo("select COUNT(*) from ".DB::table('folder')." where fname='{$name}' and  pfid='{$pfid}'");
        if (DB::result_first("select COUNT(*) from %t where fname=%s and  pfid=%d and isdelete<1", array('folder', $name, $pfid))) {
            $name = preg_replace("/\(\d+\)/i", '', $name) . '(' . ($i + 1) . ')';
            $i += 1;
            return self::getFolderName($name, $pfid);
        } else {
            return $name;
        }
    }

    //获取不重复的文件名称
    public function getFileName($name, $pfid)
    {
        static $i = 0;
        $name = self::name_filter($name);
        //echo("select COUNT(*) from ".DB::table('folder')." where fname='{$name}' and  pfid='{$pfid}'");
        if (DB::result_first("select COUNT(*) from %t where type!='folder' and name=%s and isdelete<1 and pfid=%d", array('resources', $name, $pfid))) {
            $ext = '';
            $namearr = explode('.', $name);
            if (count($namearr) > 1) {
                $ext = $namearr[count($namearr) - 1];
                unset($namearr[count($namearr) - 1]);
                $ext = $ext ? ('.' . $ext) : '';
            }
            $tname = implode('.', $namearr);
            $name = preg_replace("/\(\d+\)/i", '', $tname) . '(' . ($i + 1) . ')' . $ext;
            $i += 1;
            return self::getFileName($name, $pfid);
        } else {
            return $name;
        }
    }


    //根据文件名创建顶级目录
    public function createTopFolderByFname($fname, $perm = 0, $params = array(), $ondup = 'newcopy')
    {
        global $_G;
        $folderparams = array('innav', 'fsperm', 'disp', 'iconview', 'display', 'flag', 'default','perm');
        $data = array();
        if (($ondup == 'overwrite') && ($folder = C::t('folder')->fetch_topby_fname($fname))) {//如果目录下有同名目录
            $data['folderarr'] = $folder;
            return $data;
        } else $fname = self::getFolderName($fname, 0); //重命名
		$flag=$params['flag']?$params['flag']:'folder';
		if($flag!=='folder') $folder_set=C::t('folder_flag')->fetch($flag);
        $top = array(
            'pfid' => 0,
            'uid' => $_G['uid'],
            'username' => $_G['username'],
            'perm' => $perm?$perm:($folder_set['perm']?$folder_set['perm']:0),
			'fsperm' =>$folder_set['fsperm']?$folder_set['fsperm']:0,
            'fname' => $fname,
            'flag' => $flag,
			'disp' => $folder_set['disp']?$folder_set['disp']:0,
            'iconview' => $folder_set['iconview']?$folder_set['iconview']:4,
            'innav' => 0,
            'isdelete' => 0
        );
        foreach ($params as $k => $v) {
            if (in_array($k, $folderparams)) {
                $top[$k] = $v;
            }
        }
        if ($topfid = DB::result_first("select fid from " . DB::table('folder') . " where uid='{$_G[uid]}' and fname = '{$top[fname]}' and flag='{$top[flag]}' ")) {
            C::t('folder')->update($topfid, $top);
        } else {
            $appid = $params['appid'] ? $params['appid'] : 0;
            $folderattr = array();
            foreach ($params as $k => $v) {
                if (in_array($k, $folderparams)) {
                    $top[$k] = $v;
                } else {
                    $folderattr[$k] = $v;
                }
            }
            $topfid = C::t('folder')->insert($top, $appid);
            if ($folderattr) {
                C::t('folder_attr')->insert_data_by_fid($topfid, $folderattr);
            }
            
        }
        $data['folderarr'] = C::t('folder')->fetch_by_fid($topfid);
        return $data;
    }

    //创建目录
    public function CreateFolder($pfid, $fname, $perm = 0, $params = array(), $ondup = 'newcopy',$force=false)
    {
        global $_G, $_GET;
        $folderparams = array('innav', 'fsperm', 'disp', 'iconview', 'display', 'flag', 'default','perm');
        if ($pfid == 0) {
            return self::createTopFolderByFname($fname, $perm, $params, $ondup);
        }
        $fname = self::name_filter($fname);

        if (!$folder = C::t('folder')->fetch($pfid)){//DB::fetch_first("select fid,pfid,iconview,disp,gid,perm_inherit from %t where fid=%d", array('folder', $pfid))) {
            return array('error' => lang('parent_directory_not_exist'));
        }
        if (!$force && !perm_check::checkperm_Container($pfid, 'folder')) {
            return array('error' => lang('no_privilege'));
        }
        if (($ondup == 'overwrite') && ($rid = self::getRepeatIDByName($fname, $pfid, true))) {//如果目录下有同名目录
            $data = array();
            $data['icoarr'] = C::t('resources')->fetch_by_rid($rid);
            $data['folderarr'] = self::getFolderByIcosdata($data['icoarr']);
            return $data;
        } else $fname = self::getFolderName($fname, $pfid); //重命名
       
		$path = C::t('resources_path')->fetch_pathby_pfid($folder['fid']);
		//如果flag!=='folder'，使用此flag的默认设置
		$flag=$params['flag']?$params['flag']:'folder';
		if($flag!=='folder') $folder_set=C::t('folder_flag')->fetch($flag);
        $setarr = array('fname' => $fname,
            'uid' => $_G['uid'],
            'username' => $_G['username'],
            'pfid' => $folder['fid'],
            'disp' => $folder_set['disp']?$folder_set['disp']:$folder['disp'],
            'iconview' => $folder_set['iconview']?$folder_set['iconview']:$folder['iconview'],
            'perm' => $perm?$perm:($folder_set['perm']?$folder_set['perm']:0),
            'fsperm' =>$folder_set['fsperm']?$folder_set['fsperm']:0,
            'flag' =>$flag,
            'dateline' => $_G['timestamp'],
            'gid' => $folder['gid'],
          
        );
        $folderattr = array();
        foreach ($params as $k => $v) {
            if (in_array($k, $folderparams)) {
                $setarr[$k] = $v;
            } else {
                $folderattr[$k] = $v;
            }
        }
        //$appid = $params['appid'] ? $params['appid']:0;
        if ($setarr['fid'] = C::t('folder')->insert($setarr)) {
            $setarr['perm'] = perm_check::getPerm($setarr['fid']);
            $setarr['perm1'] = perm_check::getPerm1($setarr['fid']);

            if ($folderattr) {
                C::t('folder_attr')->insert_data_by_fid($setarr['fid'], $folderattr);
            }

            $setarr['title'] = $setarr['fname'];
            $setarr['ext'] = '';
            $setarr['size'] = 0;

            $setarr1 = array(
                'uid' => $_G['uid'],
                'username' => $_G['username'],
                'oid' => $setarr['fid'],
                'name' => $setarr['fname'],
                'type' => 'folder',
                'flag' => $setarr['flag'],
                'dateline' => $_G['timestamp'],
                'pfid' => intval($folder['fid']),
                'gid' => intval($folder['gid']),
                'ext' => '',
                'size' => 0,
            );
            if ($setarr1['rid'] = C::t('resources')->insert_data($setarr1)) {
                $setarr1['relativepath'] = $path . $setarr1['name'] . '/';
                $setarr1['path'] = $setarr1['rid'];
                $setarr1['dpath'] = dzzencode($setarr1['rid']);
                $setarr1['bz'] = '';
                //addtoconfig($setarr1);
              /*  $setarr1['fsize'] = formatsize($setarr1['size']);
                $setarr1['img'] = 'dzz/images/extimg/folder.png';
                $setarr1['ftype'] = getFileTypeName($setarr1['type'], $setarr1['ext']);
                $setarr1['fdateline'] = dgmdate($setarr1['dateline']);
                $setarr1['sperm'] = perm_FileSPerm::typePower($setarr1['type'], $setarr1['ext']);*/
                if ($fid = $setarr1['pfid']) {
                    $event = 'creat_folder';
                    $path = preg_replace('/dzz:(.+?):/', '', $path) ? preg_replace('/dzz:(.+?):/', '', $path) : '';
                    $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($setarr1['pfid'], $setarr1['gid']);
                    $eventdata = array(
                        'foldername' => $setarr1['name'],
                        'fid' => $setarr1['oid'],
                        'username' => $setarr1['username'],
                        'uid' => $setarr1['uid'],
                        'path' => $setarr1['path'],
                        'position' => $path,
                        'hash' => $hash
                    );
                    C::t('resources_event')->addevent_by_pfid($setarr1['pfid'], $event, 'create', $eventdata, $setarr1['gid'], $setarr1['rid'], $setarr1['name']);
                }
                return array('icoarr' => C::t('resources')->fetch_by_rid($setarr1['rid']), 'folderarr' => $setarr);
            } else {
                C::t('folder')->delete_by_fid($setarr['fid'],true);
                return array('error' => lang('data_error'));
            }
        }
        return false;
    }

    public function getPath($ext, $dir = 'dzz')
    {
        global $_G;
        if ($ext && in_array(trim($ext, '.'), $_G['setting']['unRunExts'])) {
            $ext = '.dzz';
        }
        $subdir = $subdir1 = $subdir2 = '';
        $subdir1 = date('Ym');
        $subdir2 = date('d');
        $subdir = $subdir1 . '/' . $subdir2 . '/';
        $target1 = $dir . '/' . $subdir . 'index.html';
        $target = $dir . '/' . $subdir;
        $target_attach = $_G['setting']['attachdir'] . $target1;
        $targetpath = dirname($target_attach);
        dmkdir($targetpath);
        return $target . date('His') . '' . strtolower(random(16)) . $ext;
    }

    public function save($target, $filename = '')
    {
        global $_G;
        $filepath = $_G['setting']['attachdir'] . $target;
        $md5 = md5_file($filepath);
        $filesize = fix_integer_overflow(filesize($filepath));
        if ($md5 && $attach = DB::fetch_first("select * from %t where md5=%s and filesize=%d", array('attachment', $md5, $filesize))) {
            $attach['filename'] = $filename;
            $pathinfo = pathinfo($filename);
            $ext = $pathinfo['extension'] ? $pathinfo['extension'] : '';
            $attach['filetype'] = strtolower($ext);
            @unlink($filepath);
            unset($attach['attachment']);
            return $attach;
        } else {
            $pathinfo = pathinfo($filename);
            $ext = $pathinfo['extension'] ? $pathinfo['extension'] : '';

            $pathinfo1 = pathinfo($target);
            $ext_dzz = strtolower($pathinfo1['extension']);
            if ($ext_dzz == 'dzz') {
                $unrun = 1;
            } else {
                $unrun = 0;
            }
            $filesize = filesize($filepath);
            $remote = 0;

            $attach = array(

                'filesize' => $filesize,
                'attachment' => $target,
                'filetype' => strtolower($ext),
                'filename' => $filename,
                'remote' => $remote,
                'copys' => 0,
                'md5' => $md5,
                'unrun' => $unrun,
                'dateline' => $_G['timestamp'],
            );

            if ($attach['aid'] = C::t('attachment')->insert($attach, 1)) {
                $remoteid = io_remote::getRemoteid($attach);
                if ($_G['setting']['thumb_active'] && $remoteid < 2 && in_array($attach['filetype'], array('jpg', 'jpeg', 'png'))) {//主动模式生成缩略图
                    try {
                        foreach ($_G['setting']['thumbsize'] as $key => $value) {
                            self::createThumb('dzz::' . $attach['attachment'], $key);
                        }
                        /*self::createThumb('dzz::'.$attach['attachment'],256,256);
						self::createThumb('dzz::'.$attach['attachment'],1440,900);*/
                    } catch (Exception $e) {
                    }
                }
                C::t('local_storage')->update_usesize_by_remoteid($attach['remote'], $attach['filesize']);
                if ($remoteid > 1) dfsockopen($_G['siteurl'] . 'misc.php?mod=movetospace&aid=' . $attach['aid'] . '&remoteid=0', 0, '', '', false, '', 1);
                unset($attach['attachment']);
                return $attach;
            } else {
                return false;
            }
        }
    }

    public function uploadToattachment($attach, $fid)
    {
        global $_G, $documentexts, $space, $docexts;
        if (!perm_check::checkperm_Container($fid, 'upload')) {

            return array('error' => lang('no_privilege'));
        }
        $gid = DB::result_first("select gid from %t where fid=%d", array('folder', $fid));

        $attach['filename'] = self::getFileName($attach['filename'], $fid);

        $path = C::t('resources_path')->fetch_pathby_pfid($fid);

        $imgexts = array('jpg', 'jpeg', 'gif', 'png', 'bmp');
        //图片文件时
        if (in_array(strtolower($attach['filetype']), $imgexts)) {
            $icoarr = array(
                'uid' => $_G['uid'],
                'username' => $_G['username'],
                'name' => $attach['filename'],
                'dateline' => $_G['timestamp'],
                'pfid' => intval($fid),
                'type' => 'image',
                'flag' => '',
                'vid' => 0,
                'gid' => intval($gid),
                'ext' => $attach['filetype'],
                'size' => $attach['filesize']
            );
            if ($icoarr['rid'] = C::t('resources')->insert_data($icoarr)) {//插入主表
                $sourceattrdata = array(
                    'postip' => $_G['clientip'],
                    'title' => $attach['filename'],
                    'aid' => $attach['aid']
                );
                if (C::t('resources_attr')->insert_attr($icoarr['rid'], $icoarr['vid'], $sourceattrdata)) {//插入属性表
                    C::t('attachment')->update($attach['aid'], array('copys' => $attach['copys'] + 1));//增加图片使用数
                    $icoarr = array_merge($attach, $icoarr, $sourceattrdata);
                    $icoarr['img'] = DZZSCRIPT . '?mod=io&op=thumbnail&size=small&path=' . dzzencode($icoarr['rid']);
                    $icoarr['url'] = DZZSCRIPT . '?mod=io&op=thumbnail&size=large&path=' . dzzencode($icoarr['rid']);
                    $icoarr['bz'] = '';
                    $icoarr['rbz'] = io_remote::getBzByRemoteid($attach['remote']);
                    $icoarr['relativepath'] = $path . $icoarr['name'];
                    $icoarr['path'] = $icoarr['rid'];
                    $icoarr['dpath'] = dzzencode($icoarr['rid']);
                    $icoarr['apath'] = dzzencode('attach::' . $attach['rid']);
                    $event = 'creat_file';
                    $path = preg_replace('/dzz:(.+?):/', '', $path) ? preg_replace('/dzz:(.+?):/', '', $path) : '';
                    $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($fid, $icoarr['gid']);
                    $eventdata = array(
                        'title' => $icoarr['name'],
                        'aid' => $icoarr['aid'],
                        'username' => $icoarr['username'],
                        'uid' => $icoarr['uid'],
                        'path' => $icoarr['path'],
                        'position' => $path,
                        'hash' => $hash
                    );

                    C::t('resources_event')->addevent_by_pfid($fid, $event, 'create', $eventdata, $icoarr['gid'], $icoarr['rid'], $icoarr['name']);
                } else {
                    C::t('resources')->delete_by_rid($icoarr['rid']);
                    return array('error' => lang('data_error'));
                }
            }

        } elseif (in_array(strtoupper($attach['filetype']), $documentexts)) {//文档文件时
            $icoarr = array(
                'uid' => $_G['uid'],
                'username' => $_G['username'],
                'name' => $attach['filename'],
                'type' => ($attach['filetype'] == 'dzzdoc') ? 'dzzdoc' : 'document',
                'dateline' => $_G['timestamp'],
                'pfid' => intval($fid),
                'flag' => '',
                'vid' => 0,
                'gid' => intval($gid),
                'ext' => $attach['filetype'],
                'size' => $attach['filesize']
            );
            if ($icoarr['rid'] = C::t('resources')->insert_data($icoarr)) {
                C::t('attachment')->update($attach['aid'], array('copys' => $attach['copys'] + 1));//增加文档使用数
                $sourcedata = array(
                    'title' => $attach['filename'],
                    'desc' => '',
                    'aid' => $attach['aid'],
                    'img' => geticonfromext($icoarr['ext'], $icoarr['type'])
                );

                if (C::t('resources_attr')->insert_attr($icoarr['rid'], $icoarr['vid'], $sourcedata)) {

                    $icoarr = array_merge($sourcedata, $attach, $icoarr);
                    $icoarr['img'] = geticonfromext($icoarr['ext'], $icoarr['type']);
                    $icoarr['url'] = DZZSCRIPT . '?mod=io&op=getStream&path=' . dzzencode($icoarr['rid']);
                    $icoarr['bz'] = '';
                    $icoarr['rbz'] = io_remote::getBzByRemoteid($attach['remote']);;
                    $icoarr['relativepath'] = $path . $icoarr['name'];
                    $icoarr['path'] = $icoarr['rid'];
                    $icoarr['dpath'] = dzzencode($icoarr['rid']);
                    $icoarr['apath'] = dzzencode('attach::' . $attach['aid']);
                    $event = 'creat_file';
                    $path = preg_replace('/dzz:(.+?):/', '', $path) ? preg_replace('/dzz:(.+?):/', '', $path) : '';
                    $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($fid, $icoarr['gid']);
                    $eventdata = array(
                        'title' => $icoarr['name'],
                        'aid' => $icoarr['aid'],
                        'username' => $icoarr['username'],
                        'uid' => $icoarr['uid'],
                        'path' => $icoarr['path'],
                        'position' => $path,
                        'hash' => $hash
                    );
                    C::t('resources_event')->addevent_by_pfid($fid, $event, 'create', $eventdata, $icoarr['gid'], $icoarr['rid'], $icoarr['name'], $icoarr['name']);
                } else {
                    C::t('resources')->delete_by_rid($icoarr['rid']);
                    return array('error' => lang('data_error'));
                }
            }

        } else {//附件
            $icoarr = array(
                'uid' => $_G['uid'],
                'username' => $_G['username'],
                'name' => $attach['filename'],
                'type' => 'attach',
                'flag' => '',
                'vid' => 0,
                'dateline' => $_G['timestamp'],
                'pfid' => intval($fid),
                'gid' => intval($gid),
                'ext' => $attach['filetype'],
                'size' => $attach['filesize']

            );

            if ($icoarr['rid'] = C::t('resources')->insert_data($icoarr)) {
                $sourcedata = array(
                    'title' => $attach['filename'],
                    'desc' => '',
                    'aid' => $attach['aid'],
                    'img' => geticonfromext($icoarr['ext'], $icoarr['type'])
                );
                C::t('attachment')->update($attach['aid'], array('copys' => $attach['copys'] + 1));
                if (C::t('resources_attr')->insert_attr($icoarr['rid'], $icoarr['vid'], $sourcedata)) {
                    $icoarr = array_merge($sourcedata, $attach, $icoarr);
                    $icoarr['img'] = geticonfromext($icoarr['ext'], $icoarr['type']);
                    $icoarr['url'] = DZZSCRIPT . '?mod=io&op=getStream&path=' . dzzencode($icoarr['rid']);
                    $icoarr['bz'] = '';
                    $icoarr['rbz'] = io_remote::getBzByRemoteid($attach['remote']);;
                    $icoarr['relativepath'] = $path . $icoarr['name'];
                    $icoarr['path'] = $icoarr['rid'];
                    $icoarr['dpath'] = dzzencode($icoarr['rid']);
                    $icoarr['apath'] = dzzencode('attach::' . $attach['aid']);
                    $event = 'creat_file';
                    $path = preg_replace('/dzz:(.+?):/', '', $path) ? preg_replace('/dzz:(.+?):/', '', $path) : '';
                    $eventdata = array(
                        'title' => $icoarr['name'],
                        'aid' => $icoarr['aid'],
                        'username' => $icoarr['username'],
                        'uid' => $icoarr['uid'],
                        'path' => $icoarr['path'],
                        'position' => $path
                    );
                    C::t('resources_event')->addevent_by_pfid($fid, $event, 'create', $eventdata, $icoarr['gid'], $icoarr['rid']);
                } else {
                    C::t('resources')->delete_by_rid($icoarr['rid']);
                    return array('error' => lang('data_error'));
                }
            }

        }

        if ($icoarr['rid']) {
            if ($icoarr['size']) SpaceSize($icoarr['size'], $gid, true);
            //addtoconfig($icoarr);
            $icoarr['fsize'] = formatsize($icoarr['size']);
            $icoarr['ftype'] = getFileTypeName($icoarr['type'], $icoarr['ext']);
            $icoarr['fdateline'] = dgmdate($icoarr['dateline']);
            $icoarr['sperm'] = perm_FileSPerm::typePower($icoarr['type'], $icoarr['ext']);
            return $icoarr;
        } else {
            return array('error' => lang('data_error'));
        }

    }

    public function createFolderByPath($path, $pfid, $params = array())
    {
        $data = array('pfid' => $pfid);
        if (!$path) {
            $data['pfid'] = $pfid;
        } else {
            $patharr = explode('/', $path);
            //生成目录
            foreach ($patharr as $fname) {
                if (!$fname) continue;
                //判断是否含有此目录
                if ($fid = DB::result_first("select fid from %t where pfid=%d and isdelete<1 and fname=%s", array('folder', $pfid, $fname))) {
                    $pfid = $data['pfid'] = $fid;
                } else {
                    if ($re = self::CreateFolder($data['pfid'], $fname, 0, $params, 'overwrite')) {
                        $data['icoarr'][] = $re['icoarr'];
                        $data['folderarr'][] = $re['folderarr'];
                        $pfid = $data['pfid'] = $re['folderarr']['fid'];
                    } else {
                        $data['error'] = 'create folder error!';
                        return $data;
                    }
                }
            }
        }
        return $data;
    }

    private function getCache($path)
    {
        $cachekey = 'dzz_upload_' . md5($path);
        if ($cache = C::t('cache')->fetch($cachekey)) {
            return $cache['cachevalue'];
        } else {
            return false;
        }
    }

    private function saveCache($path, $str)
    {
        global $_G;
        $cachekey = 'dzz_upload_' . md5($path);
        C::t('cache')->insert(array(
            'cachekey' => $cachekey,
            'cachevalue' => $str,
            'dateline' => $_G['timestamp'],
        ), false, true);
    }

    private function deleteCache($path)
    {
        $cachekey = 'dzz_upload_' . md5($path);
        C::t('cache')->delete($cachekey);
    }

    private function getPartInfo($content_range)
    {
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

    public function uploadStream($file, $filename, $pfid, $relativePath = '', $content_range = array(), $params = array())
    {
        $data = array();
        //处理目录(没有分片或者最后一个分片时创建目录
        $arr = self::getPartInfo($content_range);
        $data['pfid'] = intval($pfid);
        if ($relativePath && $arr['iscomplete']) {
            $data = self::createFolderByPath($relativePath, $pfid, $params);
            if (isset($data['error'])) {
                return array('error' => $data['error']);
            }
        }
		if(substr($filename,-7)=='.folder'){
			$data = self::createFolderByPath($relativePath?$relativePath:substr($filename,0,-7), $pfid, $params);
			if (isset($data['error'])) {
                return array('error' => $data['error']);
            }
			
			if(empty($data['folderarr'])){
				$data['folderarr']=array();
				$data['folderarr'][]=C::t('folder')->fetch_by_fid($data['pfid']);
			}
			if(empty($data['icoarr'])){
				$data['icoarr']=array();
				if($rid=DB::result_first("select rid from %t where type='folder' and oid=%d",array('resources',$data['pfid']))){
					$data['icoarr'][]=C::t('resources')->fetch_by_rid($rid);
				}
			}
			return $data;
		}
        $arr['flag'] = $pfid . '_' . $relativePath;

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
            $re = self::upload($fileContent, $data['pfid'], $filename, $arr);
            if ($arr['iscomplete']) {
                if (empty($re['error'])) {
                    $data['icoarr'][] = $re;
                    return $data;
                } else {
                    $data['error'] = $re['error'];
                    return $data;
                }
            } else {
                return true;
            }
        } else {
            $re = self::upload($fileContent, $data['pfid'], $filename);
            if (empty($re['error'])) {
                if($re['type'] == 'image' && $re['aid']){
                    $re['imgpath'] = DZZSCRIPT.'?mod=io&op=thumbnail&path='.dzzencode('attach::' . $re['aid']);
                }
                $re['monthdate'] = dgmdate($re['dateline'],'m-d');
                $re['hourdate'] = dgmdate($re['dateline'],'H:i');
                $re['pfid'] = $data['pfid'];
                $re['colect'] = 0;
                $data['icoarr'][] = $re;
                return $data;
            } else {
                $data['error'] = $re['error'];
                return $data;
            }
        }
    }

    public function upload_by_content($fileContent, $path, $filename,$partinfo=array())
    {
        return self::upload($fileContent, $path, $filename,$partinfo);
    }

    /**
     * 上传文件
     * 注意：此方法适用于上传不大于2G的单个文件。
     * @param string $fileContent 文件内容字符串
     * @param string $fid 上传文件的目标保存目录fid
     * @param string $fileName 文件名
     * @param string $ondup overwrite：表示覆盖同名文件；newcopy：表示生成文件副本并进行重命名，命名规则为“文件名_日期.后缀”。
     * @param boolean $isCreateSuperFile 是否分片上传
     * @return string
     */
    public function upload($fileContent, $fid, $filename, $partinfo = array(), $ondup = 'newcopy')
    {
        global $_G;
        $filename = self::name_filter($filename);
        if (($ondup == 'overwrite') && ($rid = self::getRepeatIDByName($filename, $fid))) {//如果目录下有同名文件
            return self::overwriteUpload($fileContent, $rid, $filename, $partinfo);//覆盖
        } else $nfilename = self::getFileName($filename, $fid); //重命名
        if ($partinfo['ispart']) {
            if ($partinfo['partnum'] == 1) {
                if ($target = self::getCache($partinfo['flag'] . '_' . md5($filename))) {
                    file_put_contents($_G['setting']['attachdir'] . $target, '');
                } else {
                    $pathinfo = pathinfo($filename);
                    $ext = strtolower($pathinfo['extension']);
                    $target = $this->getPath($ext ? ('.' . $ext) : '', 'dzz');
                    self::saveCache($partinfo['flag'] . '_' . md5($filename), $target);
                }
            } else {
                $target = self::getCache($partinfo['flag'] . '_' . md5($filename));
            }
            if (!file_put_contents(
                $_G['setting']['attachdir'] . $target,
                $fileContent,
                FILE_APPEND
            )
            ) {
                return array('error' => lang('cache_file_error'));
            }

            if (!$partinfo['iscomplete']) return true;
            else {
                self::deleteCache($partinfo['flag'] . '_' . md5($filename));

            }
        } else {
            $pathinfo = pathinfo($filename);
            $ext = strtolower($pathinfo['extension']);
            $target = $this->getPath($ext ? ('.' . $ext) : '', 'dzz');
            if (!empty($fileContent) && !file_put_contents($_G['setting']['attachdir'] . $target, $fileContent)) {
                return array('error' => lang('cache_file_error'));
            }
        }

        //判断空间大小
        $gid = DB::result_first("select gid from %t where fid=%d", array('folder', $fid));
        if (!SpaceSize(filesize($_G['setting']['attachdir'] . $target), $gid)) {
            @unlink($_G['setting']['attachdir'] . $target);
            return array('error' => lang('inadequate_capacity_space'));
        }

        if ($attach = $this->save($target, $nfilename)) {
            if ($attach['error']) {
                return array('error' => $attach['error']);
            } else {
                return $this->uploadToattachment($attach, $fid);
            }
        } else {
            return array('error' => 'Could not save uploaded file. The upload was cancelled, or server error encountered');
        }

    }

    public function overwriteUpload($fileContent, $rid, $filename, $partinfo = array())
    {
        global $_G, $space;

        if (!$fileContent) {
            return array('error' => lang('file_content_cannot_empty'));
        }
        if (!$icoarr = C::t('resources')->fetch_by_rid($rid)) {
            return array('error' => lang('file_not_exist1'));
        }
        $gid = DB::result_first("select gid from %t where fid=%d", array('folder', $icoarr['pfid']));
        if (in_array($icoarr['type'], array('folder', 'link', 'video', 'dzzdoc'))) {
            if (!perm_check::checkperm_Container($icoarr['pfid'], 'upload')) {
                return array('error' => lang('privilege'));
            }
        } elseif (!perm_check::checkperm_Container($icoarr['pfid'], 'upload')) {
            return array('error' => lang('privilege'));
        }
        $target = $icoarr['attachment'];
        if ($partinfo['ispart']) {
            if ($partinfo['partnum'] == 1) {
                file_put_contents($_G['setting']['attachdir'] . './' . $target, $fileContent);
            } else {
                file_put_contents(
                    $_G['setting']['attachdir'] . './' . $target,
                    $fileContent,
                    FILE_APPEND
                );
                if (!$partinfo['iscomplete']) return true;
            }
        } else {
            file_put_contents($_G['setting']['attachdir'] . './' . $target, $fileContent);
        }


        if (!$attach = self::save($target, $icoarr['name'])) {
            return array('error' => lang('file_save_exist'));
        }
        //计算用户新的空间大小
        $csize = $attach['filesize'] - $icoarr['size'];

        //重新计算用户空间
        if ($csize) {
            if (!SpaceSize($csize, $gid)) {
                return array('error' => lang('inadequate_capacity_space'));
            }
            SpaceSize($csize, $gid, 1);
        }
        $oldaid = $icoarr['aid'];
        //更新附件数量
        if ($oldaid != $attach['aid']) {
            /*  if ($icoarr['type'] == 'document') {
                  C::t('source_document')->update($icoarr['did'], array('aid' => $attach['aid']));
              } else {
                  C::t('source_attach')->update($icoarr['qid'], array('aid' => $attach['aid']));
              }*/
            C::t('resources_attr')->update_by_skey($icoarr['rid'], $icoarr['vid'], array('aid' => $attach['aid']));
            C::t('attachment')->update($attach['aid'], array('copys' => $attach['copys'] + 1));
            C::t('attachment')->delete_by_aid($oldaid);
        }
        $icoarr['size'] = $attach['filesize'];
        $icoarr['aid'] = $attach['aid'];
        return $icoarr;
    }

    //判断附件是否已经存在，返回附件数组
    public function dzz_imagetoattach($link, $gid)
    {
        global $_G;

        $md5 = md5_file($link);
        if ($md5 && $attach = C::t('attachment')->fetch_by_md5($md5)) {
            //判断空间大小
            if (!SpaceSize($attach['filesize'], $gid)) {
                return array('error' => lang('inadequate_capacity_space'));
            }
            return $attach;
        } else {
            if ($target = imagetolocal($link, 'dzz')) {
                //判断空间大小
                $size = @filesize($_G['setting']['attachdir'] . $target);
                //判断空间大小
                if (!SpaceSize($size, $gid)) {
                    @unlink($_G['setting']['attachdir'] . $target);
                    return array('error' => lang('inadequate_capacity_space'));
                }
                $object = str_replace('/', '-', $target);
                $remote = 0;

                $attach = array(
                    'filesize' => intval($size),
                    'attachment' => $target,
                    'filetype' => strtolower(substr(strrchr($link, '.'), 1, 10)),
                    'filename' => substr(strrchr($link, '/'), 1, 50),
                    'remote' => $remote,
                    'copys' => 1,
                    'md5' => $md5,
                    'dateline' => $_G['timestamp'],
                );
                if ($attach['aid'] = DB::insert('attachment', ($attach), 1)) {
                    C::t('local_storage')->update_usesize_by_remoteid($attach['remote'], $attach['filesize']);
                    dfsockopen($_G['siteurl'] . 'misc.php?mod=movetospace&aid=' . $attach['aid'] . '&remoteid=0', 0, '', '', FALSE, '', 1);

                    return $attach;
                }
            }
        }
        return false;
    }

    public function linktoimage($link, $pfid)
    {
        global $_G, $space;
        $fid = $pfid;
        $gid = DB::result_first("select gid from %t where fid =%d", array('folder', $pfid));
        if (!$cimage = DB::fetch_first("select * from " . DB::table('collect') . " where ourl='{$link}' and type = 'img'")) {
            if ($attach = self::dzz_imagetoattach($link, $gid)) {
                if ($attach['error']) return $attach;
                $data = array('type' => 'img', 'aid' => $attach['aid'], 'title' => '', 'desc' => '');
                $cimage = array(
                    'ourl' => $link,
                    'data' => serialize($data),
                    'copys' => 0,
                    'type' => 'img',
                    'dateline' => $_G['timestamp']
                );
                $cimage['cid'] = DB::insert('collect', ($cimage), 1);

            } else {
                return array('error' => lang('image_to_local_error'));
            }
        } else {
            $data = unserialize($cimage['data']);
            C::t('attachment')->addcopy_by_aid($data['aid']);
            $attach = C::t('attachment')->fetch($data['aid']);
        }
        //判断空间大小
        if (!SpaceSize($attach['filesize'], $gid)) {
            return array('error' => lang('inadequate_capacity_space'));
        }
        $path = C::t('resources_path')->fetch_pathby_pfid($fid);
        $path = $path . '/' . self::getFileName(strtolower(substr(strrchr($link, '/'), 1, 50)), $fid) . '.' . $attach['filetype'];
        $attachment = $_G['setting']['attachdir'] . './' . $attach['attachment'];
        $imginfo = @getimagesize($attachment);
        $icoarr = array(
            'uid' => $_G['uid'],
            'username' => $_G['username'],
            'name' => self::getFileName(strtolower(substr(strrchr($link, '/'), 1, 50)), $fid),
            'flag' => '',
            'type' => 'image',
            'vid' => 0,
            'dateline' => $_G['timestamp'],
            'pfid' => $fid,
            'oid' => $cimage['cid'],
            'gid' => $gid,
            'ext' => $attach['filetype'],
            'size' => $attach['filesize'],
        );
        if ($icoarr['rid'] = C::t('resources')->insert_data($icoarr)) {
            $sourceattr = array(
                'title' => $attach['filename'],
                'desc' => '',
                'postip' => $_G['clientip'],
                'desc' => $data['desc'],
                'aid' => $data['aid'],
                'width' => $imginfo[0],
                'height' => $imginfo[1],
            );
            if (C::t('resources_attr')->insert_attr($icoarr['rid'], $icoarr['vid'], $sourceattr)) {
                C::t('collect')->update($cimage['cid'], array('copys' => $cimage['copys'] + 1));
                $icoarr = array_merge($sourceattr, $icoarr);
                $icoarr['img'] = DZZSCRIPT . '?mod=io&op=thumbnail&&size=small&path=' . rawurlencode($icoarr['aid']);
                $icoarr['url'] = DZZSCRIPT . '?mod=io&op=thumbnail&&size=large&path=' . rawurlencode($icoarr['aid']);
                $icoarr['bz'] = '';
                $icoarr['aid'] = $sourceattr['aid'];
                $data['rbz'] = io_remote::getBzByRemoteid($icoarr['remote']);
                $icoarr['relativepath'] = $path;
                $icoarr['path'] = $icoarr['rid'];
                $icoarr['dpath'] = dzzencode($icoarr['rid']);
                $icoarr['apath'] = dzzencode('attach::' . $icoarr['aid']);
                if ($icoarr['size']) SpaceSize($icoarr['size'], $gid, true);
                //addtoconfig($icoarr);
                $icoarr['fsize'] = formatsize($icoarr['size']);
                $icoarr['ftype'] = getFileTypeName($icoarr['type'], $icoarr['ext']);
                $icoarr['fdateline'] = $icoarr['dateline'];
                $event = 'creat_file';
                $path = preg_replace('/dzz:(.+?):/', '', $path) ? preg_replace('/dzz:(.+?):/', '', $path) : '';
                $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($fid, $icoarr['gid']);
                $eventdata = array(
                    'name' => $icoarr['fname'],
                    'fid' => $icoarr['oid'],
                    'username' => $icoarr['username'],
                    'uid' => $icoarr['uid'],
                    'path' => $icoarr['path'],
                    'position' => $path,
                    'hash' => $hash
                );
                C::t('resources_event')->addevent_by_pfid($icoarr['pfid'], $event, 'create', $eventdata, $icoarr['gid'], $icoarr['rid'], $icoarr['name']);
            } else {
                C::t('resources')->delete_by_rid($icoarr['rid']);
                return array('error' => lang('linktoimage_error'));
            }

        }
        if ($icoarr['rid']) {
            return $icoarr;
        } else {
            return array('error' => lang('linktoimage_error'));
        }
    }

    public function linktomusic($link, $pfid)
    {
        global $_G;
        @set_time_limit(60);
        $fid = $pfid;
        $gid = DB::result_first("select gid from %t where fid =%d", array('folder', $pfid));
        if (!$cmusic = DB::fetch_first("select * from " . DB::table('collect') . " where ourl='{$link}' and type = 'music'")) {
            $data = array('type' => 'music', 'url' => $link, 'img' => '', 'desc' => '', 'title' => strtolower(substr(strrchr($link, '/'), 1, 50)));
            $cmusic = array(
                'ourl' => $link,
                'data' => serialize($data),
                'copys' => 0,
                'type' => 'music',
                'dateline' => $_G['timestamp']
            );
            $cmusic['cid'] = DB::insert('collect', ($cmusic), 1);
        } else {
            $data = unserialize($cmusic['data']);
            C::t('collect')->addcopy_by_cid($cmusic['id']);
        }
        $path = C::t('resources_path')->fetch_pathby_pfid($fid);
        $path = $path . '/' . self::getFileName($data['title'], $fid);
        $icoarr = array(
            'uid' => $_G['uid'],
            'username' => $_G['username'],
            'name' => self::getFileName($data['title'], $fid),
            'flag' => '',
            'type' => 'music',
            'pfid' => $fid,
            'oid' => $cmusic['cid'],
            'vid' => 0,
            'dateline' => $_G['timestamp'],
            'gid' => $gid,
            'ext' => '',
            'size' => 0
        );
        if ($icoarr['rid'] = C::t('resources')->insert_data($icoarr)) {
            $sourcedata = array(
                'img' => $data['img'],
                'desc' => $data['desc'],
                'title' => $data['title'],
            );
            $sourcedata['icon'] = $sourcedata['icon'] ? $sourcedata['icon'] : geticonfromext('', 'music');
            if (C::t('resources_attr')->insert_attr($icoarr['rid'], $icoarr['vid'], $sourcedata)) {
                C::t('collect')->update($cmusic['cid'], array('copys' => $cmusic['copys'] + 1));
                $icoarr['url'] = $sourcedata['url'];
                $icoarr['img'] = $sourcedata['icon'];
                $icoarr['bz'] = '';
                $icoarr['relativepath'] = $path;
                $icoarr['path'] = $icoarr['rid'];
                $icoarr['dpath'] = dzzencode($icoarr['rid']);
                //addtoconfig($icoarr);
                $icoarr['fsize'] = formatsize($icoarr['size']);
                $icoarr['ftype'] = getFileTypeName($icoarr['type'], $icoarr['ext']);
                $icoarr['fdateline'] = $icoarr['dateline'];
                $event = 'creat_file';
                $path = preg_replace('/dzz:(.+?):/', '', $path) ? preg_replace('/dzz:(.+?):/', '', $path) : '';
                $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($icoarr['pfid'], $icoarr['gid']);
                $eventdata = array(
                    'name' => $icoarr['fname'],
                    'fid' => $icoarr['oid'],
                    'username' => $icoarr['username'],
                    'uid' => $icoarr['uid'],
                    'path' => $icoarr['path'],
                    'position' => $path,
                    'hash' => $hash
                );
                C::t('resources_event')->addevent_by_pfid($icoarr['pfid'], $event, 'create', $eventdata, $icoarr['gid'], $icoarr['rid'], $icoarr['name']);
            } else {
                C::t('resources')->delete_by_rid($icoarr['rid']);
                return array('error' => lang('linktomusic_error'));
            }
        }

        if ($icoarr['rid']) {
            return $icoarr;
        } else {
            return array('error' => lang('linktomusic_error'));
        }
    }

    public function linktovideo($link, $pfid)
    {
        global $_G;
        @set_time_limit(60);
        $fid = $pfid;
        $gid = DB::result_first("select gid from %t where fid =%d", array('folder', $pfid));
        if (!$cvideo = DB::fetch_first("select * from " . DB::table('collect') . " where ourl='{$link}' and  type = 'video'")) {
            $arr = array();
            require_once libfile('function/code');
            if (!$arr = parseflv($link)) {
                return false;
            }
            //采集标题和描述
            if (!$arr['title'] || !$arr['description']) {
                require_once dzz_libfile('class/caiji');
                $caiji = new caiji($link);
                $arr['title'] = $caiji->getTitle();
                $arr['description'] = $caiji->getDescription();
            }

            $data = array(
                'type' => 'video',
                'url' => $arr['url'],
                'img' => $arr['img'],
                'desc' => $arr['description'],
                'title' => $arr['title'],
            );
            $cvideo = array(
                'ourl' => $link,
                'data' => serialize($data),
                'copys' => 0,
                'type' => 'video',
                'dateline' => $_G['timestamp']
            );
            $cvideo['cid'] = DB::insert('collect', ($cvideo), 1);
        } else {
            $data = unserialize($cvideo['data']);
            C::t('collect')->addcopy_by_cid($cvideo['id']);
        }
        //如果原先的标题和描述没采集到，重新采集
        if (!$data['title'] || !$data['desc']) {
            require_once dzz_libfile('class/caiji');
            $caiji = new caiji($link);
            $data['title'] = $caiji->getTitle();
            $data['desc'] = $caiji->getDescription();
            $usedata = $data;
            $data = serialize($data);
            C::t('collect')->update($cvideo['cid'], array('data' => $data));
        }
        $data = ($usedata) ? $usedata : $data;
        $path = C::t('resources_path')->fetch_pathby_pfid($fid);
        $path = $path . '/' . self::getFileName($data['title'], $fid) . '.swf';
        $icoarr = array(
            'uid' => $_G['uid'],
            'username' => $_G['username'],
            'name' => self::getFileName($data['title'], $fid),
            'type' => 'video',
            'oid' => $cvideo['cid'],
            'dateline' => $_G['timestamp'],
            'pfid' => $fid,
            'vid' => 0,
            'gid' => $gid,
            'ext' => 'swf',
            'flag' => '',
            'size' => 0
        );
        if ($icoarr['rid'] = C::t('resources')->insert_data($icoarr)) {
            $sourcedata = array(
                'url' => $data['url'],
                'img' => $data['img'],
                'desc' => $data['desc'],
                'title' => $data['title'],
            );
            if (C::t('resources_attr')->insert_attr($icoarr['rid'], $icoarr['vid'], $sourcedata)) {
                C::t('collect')->update($cvideo['cid'], array('copys' => $cvideo['copys'] + 1));
                $icoarr['url'] = $sourcedata['url'];
                $icoarr['img'] = $sourcedata['icon'];
                $icoarr['bz'] = '';
                $icoarr['relativepath'] = $path;
                $icoarr['path'] = $icoarr['rid'];
                $icoarr['dpath'] = dzzencode($icoarr['rid']);
                //addtoconfig($icoarr);
                $icoarr['fsize'] = formatsize($icoarr['size']);
                $icoarr['ftype'] = getFileTypeName($icoarr['type'], $icoarr['ext']);
                $icoarr['fdateline'] = $icoarr['dateline'];
                $event = 'creat_file';
                $path = preg_replace('/dzz:(.+?):/', '', $path) ? preg_replace('/dzz:(.+?):/', '', $path) : '';
                $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($icoarr['pfid'], $icoarr['gid']);
                $eventdata = array(
                    'name' => $icoarr['fname'],
                    'fid' => $icoarr['oid'],
                    'username' => $icoarr['username'],
                    'uid' => $icoarr['uid'],
                    'path' => $icoarr['path'],
                    'position' => $path,
                    'hash' => $hash
                );
                C::t('resources_event')->addevent_by_pfid($icoarr['pfid'], $event, 'create', $eventdata, $icoarr['gid'], $icoarr['rid'], $icoarr['name']);
            } else {
                C::t('resources')->delete_by_rid($icoarr['rid']);
                return array('error' => lang('linktovideo_error'));
            }
        }
        if ($icoarr['rid']) {
            return $icoarr;
        } else {
            return array('error' => lang('linktovideo_error'));
        }
    }

    public function linktourl($link, $pfid)
    {
        global $_G;
        $fid = $pfid;
        $gid = DB::result_first("select gid from %t where fid =%d", array('folder', $pfid));
        $clink = array();
        if (!$clink = DB::fetch_first("select * from " . DB::table("collect") . " where ourl='{$link}' and  type = 'link'")) {
            $arr = array();
            require_once dzz_libfile('class/caiji');
            $caiji = new caiji($link);
            $arr['title'] = $caiji->getTitle();
            $arr['desc'] = $caiji->getDescription();
            $arr['url'] = $link;
            $arr['type'] = 'url';
            $arr['img'] = '';
            $data = array(
                'type' => 'url',
                'url' => $arr['url'],
                'img' => $arr['img'],
                'desc' => $arr['desc'],
                'title' => $arr['title'],
            );
            $clink = array(
                'ourl' => $link,
                'data' => serialize($data),
                'copys' => 0,
                'type' => 'link',
                'dateline' => $_G['timestamp']
            );
            $clink['cid'] = DB::insert('collect', ($clink), 1);
        } else {
            $data = unserialize($clink['data']);
            C::t('collect')->addcopy_by_cid($clink['id']);
        }
        $parseurl = parse_url($link);
        $clink['title'] = self::getFileName($data['title'] ? $data['title'] : $parseurl['host'], $fid);
        $icondata = getUrlIcon($link);
        $path = C::t('resources_path')->fetch_pathby_pfid($fid);
        $path = $path . '/' . $data['title'] . '.' . $data['ext'];
        $icoarr = array(
            'uid' => $_G['uid'],
            'username' => $_G['username'],
            'name' => $clink['title'],
            'flag' => '',
            'oid' => $clink['cid'],
            'type' => 'link',
            'dateline' => $_G['timestamp'],
            'pfid' => $fid,
            'gid' => $gid,
            'vid' => 0,
            'ext' => $icondata['ext'],
            'size' => 0
        );
        if ($icoarr['rid'] = C::t('resources')->insert_data($icoarr)) {
            $sourcedata = array(
                'url' => $link,
                'desc' => $data['desc'],
                'title' => $data['title'],
                'imgid' => $icondata['did'],
                'img' => $icondata['img'],
            );

            if (C::t('resources_attr')->insert_attr($icoarr['rid'], $icoarr['vid'], $sourcedata)) {
                C::t('collect')->update($clink['cid'], array('copys' => $clink['copys'] + 1));
                $icoarr['url'] = $sourcedata['url'];
                $icoarr['img'] = $sourcedata['img'];
                $icoarr['bz'] = '';
                $icoarr['relativepath'] = $path;
                $icoarr['path'] = $icoarr['rid'];
                $icoarr['dpath'] = dzzencode($icoarr['rid']);

                //addtoconfig($icoarr);
                $icoarr['fsize'] = formatsize($icoarr['size']);
                $icoarr['ftype'] = getFileTypeName($icoarr['type'], $icoarr['ext']);
                $icoarr['fdateline'] = dgmdate($icoarr['dateline']);
                $event = 'creat_file';
                $path = preg_replace('/dzz:(.+?):/', '', $path) ? preg_replace('/dzz:(.+?):/', '', $path) : '';
                $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($icoarr['pfid'], $icoarr['gid']);
                $eventdata = array(
                    'title' => $icoarr['fname'],
                    'fid' => $icoarr['oid'],
                    'username' => $icoarr['username'],
                    'uid' => $icoarr['uid'],
                    'path' => $icoarr['path'],
                    'position' => $path,
                    'hash' => $hash
                );
                C::t('resources_event')->addevent_by_pfid($icoarr['pfid'], $event, 'create', $eventdata, $icoarr['gid'], $icoarr['rid'], $icoarr['name']);
            } else {
                C::t('resources')->delete_by_rid($icoarr['rid']);
                return array('error' => lang('linktourl_error'));
            }
        }

        if ($icoarr['rid']) {
            return $icoarr;
        } else {
            return array('error' => lang('linktourl_error'));
        }
    }

    /**
     * 移动文件到目标位置
     * @param string $opath 被移动的文件路径
     * @param string $path 目标位置（可能是同一api内或跨api，这两种情况分开处理）
     * @return icosdatas
     */
    public function CopyTo($rid, $pfid, $iscopy = 0,$force=0)
    {
		
        try {
            $data = C::t('resources')->fetch_by_rid($rid);
			
            if (is_numeric($pfid)) {//如果目标位置也是本地
				
				if($data['type']=='folder'){//判断上级目录不能移动到下级目录中
					$pfids=C::t('folder')->fetch_path_by_fid($pfid);
					if(in_array($data['oid'],$pfids)){
						$data['success']= lang('forbid_folder_to_sub_folder');
						return $data;
					}
				}
                if (!$iscopy) {
                    $re = self::FileMove($rid, $pfid, true,$force);
                    $data['newdata'] = $re['icoarr'];
                    $data['success'] = true;
                    $data['moved'] = true;
                } else {
                    $re = self::FileCopy($rid, $pfid, true,$force);
                    $data['newdata'] = $re['icoarr'];
                    $data['success'] = true;
                }
                if ($re['error']) $data['success'] = $re['error'];

            } else {//后期待处理
                switch ($data['type']) {
                    case 'folder'://创建目录
                        if ($re = IO::CreateFolder($pfid, $data['name'], 0)) {
                            if (isset($re['error']) && intval($re['error_code']) != 31061) {
                                $data['success'] = $re['error'];
                            } else {

                                $data['newdata'] = $re['icoarr'];
                                $data['success'] = true;
                                $contents = C::t('resources')->fetch_by_pfid($data['oid']);
                                foreach ($contents as $key => $value) {
                                    $data['contents'][$key] = self::CopyTo($value['rid'], $re['folderarr']['path'], $iscopy);
                                }
                            }
                        }
                        break;
                    case 'shortcut':
                    case 'discuss':
                    case 'dzzdoc':
                    case 'user':
                    case 'link':
                    case 'music':
                    case 'video':
                    case 'topic':
                    case 'app'://这些内容不能移动到api网盘内；
                        $data['success'] = lang('document_only_stored_enterprise');
                        break;
                    default:
                        $re = IO::multiUpload($rid, $pfid, $data['name']);
                        if ($re['error']) $data['success'] = $re['error'];
                        else {
                            $data['newdata'] = $re;
                            $data['success'] = true;
                        }
                        break;
                }
            }
        } catch (Exception $e) {
            $data['success'] = $e->getMessage();
        }
        $data['iscopy'] = $iscopy;
        return $data;
    }

    public function RecovercreateFolderByPath($path, $pfid, $params = array())
    {
        $data = array('pfid' => $pfid);
        if (!$path) {
            $data['pfid'] = $pfid;
        } else {
            $patharr = explode('/', $path);
            //生成目录
            foreach ($patharr as $fname) {
                if (!$fname) continue;
                $fpath = C::t('resources_path')->fetch_pathby_pfid($pfid, false);
                $path = $fpath . $fname . '/';
                $fid = DB::result_first("select fid from %t where path = %s", array('resources_path', $path));
                /* echo $fid;
                 die;*/
                //判断是否含有此目录
                if ($finfo = DB::fetch_first("select f.fid,f.isdelete,r.rid from %t f
                  left join %t r on f.fid=r.oid where f.fid=%d  and f.fname=%s", array('folder', 'resources', $fid, $fname))
                ) {
                    if ($finfo['isdelete'] > 0) {
                        if ($finfo['rid']) {
                            if ($ricoid = self::getRepeatIDByName($fname, $pfid, true)) {
                                $newname = self::getFolderName($fname, $pfid);
                                self::rename($finfo['rid'], $newname);
                            }
                            //DB::update('resources', array('isdelete' => 0, 'deldateline' => 0, 'pfid' => $pfid), array('rid' => $finfo['rid']));
                            C::t('resources')->update_by_rid($finfo['rid'],array('isdelete' => 0, 'deldateline' => 0, 'pfid' => $pfid));
                        }
                        //DB::update('folder', array('isdelete' => 0, 'deldateline' => 0, 'pfid' => $pfid), array('fid' => $finfo['fid']));
                        C::t('folder')->update($finfo['fid'],array('isdelete' => 0, 'deldateline' => 0, 'pfid' => $pfid));
                    }
                    $pfid = $data['pfid'] = $finfo['fid'];
                } else {
                    if ($re = self::CreateFolder($data['pfid'], $fname, 0, $params, 'overwrite')) {
                        $data['icoarr'][] = $re['icoarr'];
                        $data['folderarr'][] = $re['folderarr'];
                        $pfid = $data['pfid'] = $re['folderarr']['fid'];
                    } else {
                        $data['error'] = 'create folder error!';
                        return $data;
                    }
                }
            }
        }
        return $data;
    }

    //恢复文件
    public function Recover($rid, $combine = true,$force=false)
    {
        global $_G;
        //判断文件是否存在
        if (!$icoarr = C::t('resources')->fetch_info_by_rid($rid)) {
            return array('rid' => $rid, 'error' => lang('file_longer_exists'));
        }
        $newpfid = false;
        //获取回收站数据
        if ($recycleinfo = C::t('resources_recyle')->get_data_by_rid($rid)) {
            //获取文件目录信息
            if (!$dirinfo = C::t('resources_path')->parse_path_get_rootdirinfo($recycleinfo['pathinfo'])) {
                return array('rid' => $rid, 'error' => lang('file_longer_exists'));
            }
            if ($dirinfo['path']) {
                //若目录被删除恢复或创建目录
                if (!$folderinfo = self::createFolderByPath($dirinfo['path'], $dirinfo['pfid'])) {
                    return array('rid' => $rid, 'error' => lang('file_longer_exists'));
                }
            }
            if (isset($folderinfo['pfid'])) {
                $icoarr['pfid'] = $folderinfo['pfid'];
                if ($dirinfo['pfid'] != $folderinfo['pfid']) {
                    $newpfid = $folderinfo['pfid'];
                }
            } else {
                $icoarr['pfid'] = $dirinfo['pfid'];
            }

        }
        $gid = $icoarr['gid'];
        //判断是否具有恢复权限
        if (!$force && !perm_check::checkperm('delete', $icoarr)) {
            return array('rid' => $icoarr['rid'], 'error' => lang('no_privilege'));
        } else {
            $targetpath = C::t('resources_path')->fetch_pathby_pfid($icoarr['pfid']);//文件路径
            $patharr = getpath($targetpath);
            $path = implode('\\', $patharr);

            //如果是文件夹
            if ($icoarr['type'] == 'folder') {
                //验证空间大小
                $contains = C::t('resources')->get_contains_by_fid($icoarr['oid'], true);
                if (!SpaceSize($contains['size'], $gid)) {
                    return array('error' => lang('inadequate_capacity_space'));
                }
                if ($combine && $ricoid = self::getRepeatIDByName($icoarr['name'], $icoarr['pfid'], true)) {
                    $rinfo = C::t('resources')->fetch_info_by_rid($ricoid);
                    //目录下所有删除文件rid
                    $rids = array();
                    $fids = array();
                    foreach (DB::fetch_all("select rid,type,oid from %t where pfid = %d and isdelete > 0", array('resources', $icoarr['oid'])) as $v) {
                        if ($v['type'] == 'folder') {
                            $fids[] = $v['oid'];
                        } else {
                            $rids[] = $v['rid'];
                        }
                    }
                    // DB::update('resources', array('pfid' => $rinfo['oid']), 'rid in(' . dimplode($rids) . ')');
                    if (count($rids) > 0) C::t('resources')->update_by_rid($rids,array('pfid' => $rinfo['oid']));
                    //DB::update('folder', array('pfid' => $rinfo['oid']), 'fid in(' . dimplode($fids) . ')');
                    if (count($fids) > 0) C::t('folder')->update($fids, array('pfid' => $rinfo['oid']));
                    //更改当前目录下所有下级文件路径
                    C::t('resources_path')->update_pathdata_by_fid($icoarr['oid'], $rinfo['oid'], true);
                    //更改动态归属
                    C::t('resources_event')->update_position_by_rid($rids, $rinfo['oid'], $rinfo['gid']);
                    C::t('resources_event')->update_event_by_pfid($icoarr['oid'], $rinfo['oid']);
                    //改变分享表数据
                    DB::update('shares', array('pfid' => $rinfo['oid']), array('pfid' => $icoarr['oid']));

                    foreach ($rids as $v) {
                        self::Recover($v, $combine,$force);
                    }
                    self::delete($icoarr['rid']);
                    //删除回收站数据
                    C::t('resources_recyle')->delete_by_rid($icoarr['rid']);
                } else {
                    //判断目录中是否存在同名文件夹，如果有则将当前目录改名
                    if ($ricoid = self::getRepeatIDByName($icoarr['name'], $icoarr['pfid'], true)) {
                        $newname = self::getFolderName($icoarr['name'], $icoarr['pfid']);
                        self::rename($icoarr['rid'], $newname);
                    }
                    //如果当前文件夹是删除状态则恢复当前文件夹
                    if ($icoarr['isdelete'] > 0) {
                        $recoverarr = array('isdelete' => 0, 'deldateline' => 0, 'pfid' => $icoarr['pfid']);
                        //恢复文件夹表数据和resources表数据
                        //if (DB::update('folder', $recoverarr, 'fid =' . $icoarr['oid']) && DB::update('resources', $recoverarr, "rid ='{$rid}'")) {
                        if( C::t('resources')->update_by_rid($rid,$recoverarr)){
							C::t('folder')->update($icoarr['oid'],$recoverarr);
                            $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($icoarr['pfid'], $icoarr['gid']);
                            //添加事件
                            $eventdata1 = array('username' => $_G['username'], 'position' => $path, 'filename' => $icoarr['name'], 'hash' => $hash);
                            C::t('resources_event')->addevent_by_pfid($icoarr['pfid'], 'recover_file', 'recoverfile', $eventdata1, $icoarr['gid'], $rid, $icoarr['name']);
                        }
                    }
                    /*  //获取当前文件夹fid及所有下级目录fid
                      $fids = C::t('resources_path')->fetch_folder_containfid_by_pfid($icoarr['oid']);*/
                    //目录下所有删除文件rid
                    $rids = array();
                    foreach (DB::fetch_all("select rid from %t where pfid = %d and isdelete > 0", array('resources', $icoarr['oid'])) as $v) {
                        $rids[] = $v['rid'];
                    }
                    foreach ($rids as $v) {
                        self::Recover($v, $combine,$force);
                    }
						
                    if ($newpfid) {
                        C::t('resources_path')->update_pathdata_by_fid($icoarr['oid'], $newpfid);
                    }
                    //删除回收站数据
                    C::t('resources_recyle')->delete_by_rid($icoarr['rid']);
                }
            } else {
                if (!DB::result_first("select isdelete from %t where rid = %s", array('resources', $rid))) {
                    return;
                }
                $recoverarr = array('isdelete' => 0, 'deldateline' => 0, 'pfid' => $icoarr['pfid']);
                //如果目录下有同名文件,则恢复时生成新的文件名
                if ($ricoid = self::getRepeatIDByName($icoarr['name'], $icoarr['pfid'], false)) {
                    $recoverarr['name'] = self::getFileName($icoarr['name'], $icoarr['pfid']);
                }
                $totalsize = 0;
                if ($icoarr['vid'] > 0) {
                    $totalsize = DB::result_first("select sum(size) from %t where rid = %s", array('resources_version', $icoarr['rid']));
                } else {
                    $totalsize = $icoarr['size'];
                }
                //重新设定空间值
                if ($totalsize > 0) {
                    SpaceSize($totalsize, $gid, 1);
                }

                //恢复文件
                //if (DB::update('resources', $recoverarr, array('rid' => $rid))) {
                if(C::t('resources')->update_by_rid($rid,$recoverarr)){
                    //删除回收站收据
                    C::t('resources_recyle')->delete_by_rid($icoarr['rid']);
                    //添加事件
                    $path = preg_replace('/dzz:(.+?):/', '', $path) ? preg_replace('/dzz:(.+?):/', '', $path) : '';
                    $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($icoarr['pfid'], $icoarr['gid']);
                    $eventdata = array(
                        'username' => getglobal('username'),
                        'filename' => $icoarr['name'],
                        'position' => $path,
                        'hash' => $hash
                    );
                    C::t('resources_event')->addevent_by_pfid($icoarr['pfid'], 'recover_file', 'recoverfile', $eventdata, $icoarr['gid'], $rid, $icoarr['name']);
                    $statisdata = array(
                        'uid' => getglobal('uid'),
                        'edits' => 1,
                        'editdateline' => TIMESTAMP
                    );
                    C::t('resources_statis')->add_statis_by_rid($rid, $statisdata);
                }
            }
        }

        return array('rid' => $icoarr['rid'], 'name' => $icoarr['name']);
    }

    //本地文件移动到本地其它区域
    public function FileMove($rid, $pfid, $first = true,$force)
    {
        global $_G, $_GET;
        @set_time_limit(0);
        @ini_set("memory_limit", "512M");
        //判断目标目录是否存在
        if (!$tfolder = DB::fetch_first("select * from " . DB::table('folder') . " where fid='{$pfid}'")) {
            return array('error' => lang('target_location_not_exist'));
        }
        //获取目标路径
        $targetpdata = C::t('resources_path')->fetch_pathby_pfid($pfid, true);//目标路径
        $targetpath = $targetpdata['path'];
        $targetarr = getpath($targetpath);
        $targetstr = implode('\\', $targetarr);//路径字符串

        //判断文件数据是否存在
        if ($icoarr = C::t('resources')->fetch($rid)) {
            //判断移动文件是否和目标文件在同一目录

            if ($icoarr['pfid'] != $tfolder['fid']) {
                //判断有无删除权限
				if(!$force){
					if($icoarr['type'] == 'folder'){
						$return = C::t('resources')->check_folder_perm($icoarr,'delete');
						if($return['error']){
							return  array('error' => $return['error']);
						}
					}else{
						if (!perm_check::checkperm('delete', $icoarr)) {
							return array('error' => lang('privilege'));
						}
					}

					//判断有无新建权限,如果是文件夹判断是否有文件件新建权限
					if ($icoarr['type'] == 'folder' && !perm_check::checkperm_Container($pfid, 'folder')) {
						return array('error' => lang('privilege'));
					} elseif (!perm_check::checkperm_Container($pfid, 'upload')) {
						return array('error' => lang('privilege'));
					}
				}
            }else{
                $return['icoarr'] = $icoarr;
                $return['icoarr']['monthdate'] = dgmdate($return['icoarr']['dateline'], 'm-d');
                $return['icoarr']['hourdate'] = dgmdate($return['icoarr']['dateline'], 'H:i');
                unset($icoarr);
                return $return;
            }
            //源文件路径
            $oldpath = C::t('resources_path')->fetch_pathby_pfid($icoarr['pfid'], true);
            $oldarr = getpath($oldpath['path']);
            $oldpathstr = implode('\\', $oldarr);
            $oldpathstr = preg_replace('/dzz:(.+?):/', '', $oldpathstr);

            //判断空间大小
            $ogid = $icoarr['gid'];
            $gid = $tfolder['gid'];
            $oldpfid = $icoarr['pfid'];
            $oldgid = $icoarr['gid'];
            //如果是文件夹类型
            if ($icoarr['type'] == 'folder') {
                $contains = C::t('resources')->get_contains_by_fid($icoarr['oid'], true);
                if ($ogid != $gid && $contains['size'] && !SpaceSize($contains['size'], $gid)) {

                    return array('error' => lang('inadequate_capacity_space'));
                }

                //如果是文件夹，并且目标目录中有同名文件夹，则执行合并
                if ($currentfid = DB::result_first("select oid from %t where pfid = %d and `name` = %s and `type` = %s and isdelete < 1",
                    array('resources', $tfolder['fid'], $icoarr['name'], 'folder'))
                ) {
                    //移动源文件夹数据到目标目录同名文件夹
                    foreach (C::t('resources')->fetch_basicinfo_by_pfid($icoarr['oid']) as $value) {
                        try {
                            self::FileMove($value['rid'], $currentfid, false, false);
                            unset($value);
                            unset($folder);
                        } catch (Exception $e) {

                        }
                    }
                    //修改分享表状态
                    C::t('shares')->change_by_rid($icoarr['rid'], '-5');
                    //删除原文件夹数据
                    DB::delete('resources', array('rid' => $icoarr['rid']));
                    //删除路径表数据
                    DB::delete('resources_path', array('fid' => $icoarr['oid']));
                    //删除文件夹表数据
                    DB::delete('folder', array('fid' => $icoarr['oid']));
                    //删除事件表数据
                    C::t('resources_event')->delete_by_rid($icoarr['rid']);

                    //添加事件
                    $oldhash = C::t('resources_event')->get_showtpl_hash_by_gpfid($oldpfid, $oldgid);
                    $eventdata1 = array('username' => $_G['username'], 'olderposition' => $oldpathstr, 'newposition' => $targetstr, 'foldername' => $icoarr['name'], 'hash' => $oldhash);
                    C::t('resources_event')->addevent_by_pfid($pfid, 'moved_folder', 'movedfolder', $eventdata1, $gid, $rid, $icoarr['name']);
                    $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($icoarr['pfid'], $ogid);
                    $eventdata2 = array('username' => $_G['username'], 'newposition' => $targetstr, 'foldername' => $icoarr['name'], 'hash' => $hash);
                    C::t('resources_event')->addevent_by_pfid($oldpfid, 'move_folder', 'movefolder', $eventdata2, $ogid, $rid, $icoarr['name']);
                } else {
                    //查询源文件夹数据
                    if ($folder = C::t('folder')->fetch($icoarr['oid'])) {
                        if ($icoarr['type'] == 'folder') $icoarr['name'] = self::getFolderName($icoarr['name'], $tfolder['fid']);
                        $folder['uid'] = $_G['uid'];
                        $folder['username'] = $_G['username'];
                        $folder['gid'] = $gid;
                        $folder['pfid'] = $pfid;
                        $folder['fname'] = $icoarr['name'];
                        $updatefids = array();
                        $fids = C::t('resources_path')->fetch_folder_containfid_by_pfid($folder['fid']);
                        $folderinfo = array(
                            'uid' => $_G['uid'],
                            'username' => $_G['username'],
                            'gid' => $gid
                        );
                        $rids = array();
                        foreach (DB::fetch_all("select rid from %t where pfid in(%n) or oid in(%n)", array('resources', $fids, $fids)) as $v) {
                            $rids[] = $v['rid'];
                        }
                        //修改文件夹表数据和resources表数据
                        // DB::update('resources', array('oid' => $folder['fid'], 'pfid' => $pfid, 'gid' => $gid, 'uid' => $_G['uid'], 'username' => $_G['username']), array('rid' => $rid)
                        if (C::t('folder')->update($folder['fid'], $folder) &&
                           C::t('resources')->update_by_rid($rid,array('oid' => $folder['fid'], 'pfid' => $pfid, 'gid' => $gid, 'uid' => $_G['uid'], 'username' => $_G['username']))
                        ) {

                            //更改文件夹路径
                            C::t('resources_path')->update_pathdata_by_fid($folder['fid'], $pfid);
                            if ($fids) {
                                //修改资源表数据
                                //DB::update('resources', $folderinfo, "pfid IN(" . dimplode($fids) . ")");
                                C::t('resources')->update_by_pfids($fids,$folderinfo);
                                //更改动态表数据
                                DB::update('resources_event', $folderinfo, "pfid IN(" . dimplode($fids) . ")");
                                //更改folder表数据
                               // DB::update('folder', $folderinfo, "pfid IN(" . dimplode($fids) . ")");
                                C::t('folder')->update_by_pfids($fids,$folderinfo);
                            }
                            if ($contains['size'] > 0) {
                                SpaceSize(-$contains['size'], $ogid, 1);
                                SpaceSize($contains['size'], $gid, 1);
                            }
                            //修改分享表状态
                            C::t('shares')->change_by_rid($rids, '-5');
                            //更改文件夹动态归属位置
                            DB::update('resources_event', array(
                                'uid' => $_G['uid'],
                                'username' => $_G['username'],
                                'gid' => $gid,
                                'pfid' => $pfid
                            ), array('pfid' => $folder['fid']));

                            //添加事件
                            $oldhash = C::t('resources_event')->get_showtpl_hash_by_gpfid($oldpfid, $oldgid);
                            $eventdata1 = array('username' => $_G['username'], 'olderposition' => $oldpathstr, 'newposition' => $targetstr, 'foldername' => $icoarr['name'], 'hash' => $oldhash);
                            C::t('resources_event')->addevent_by_pfid($pfid, 'moved_folder', 'movedfolder', $eventdata1, $gid, $rid, $icoarr['name']);
                            $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($icoarr['pfid'], $ogid);
                            $eventdata2 = array('username' => $_G['username'], 'newposition' => $targetstr, 'foldername' => $icoarr['name'], 'hash' => $hash);
                            C::t('resources_event')->addevent_by_pfid($oldpfid, 'move_folder', 'movefolder', $eventdata2, $ogid, $rid, $icoarr['name']);
                        }
                    } else {
                        return array('error', lang('folder_not_exist'));
                    }
                }

            } else {
                $totalsize = 0;
                if ($icoarr['vid'] > 0) {
                    $totalsize = DB::result_first("select sum(size) from %t where rid = %s", array('resources_version', $icoarr['rid']));
                } else {
                    $totalsize = $icoarr['size'];
                }
                if ($ogid != $gid && $totalsize && !SpaceSize($totalsize, $gid)) {
                    return array('error' => lang('inadequate_capacity_space'));
                }
                //如果不是文件夹判断文件名重复
                if ($icoarr['pfid'] != $tfolder['fid'] || $icoarr['isdelete'] > 0) {

                    $icoarr['name'] = self::getFileName($icoarr['name'], $tfolder['fid']);
                }
                $icoarr['gid'] = $gid;
                $icoarr['uid'] = $_G['uid'];
                $icoarr['username'] = $_G['username'];
                $icoarr['pfid'] = $pfid;
                $icoarr['isdelete'] = 0;

                if (C::t('resources')->update_by_rid($icoarr['rid'], $icoarr)) {
                    Hook::listen('movefile_after',$icoarr['rid']);
                    //更改文件动态归属位置
                    C::t('resources_event')->update_position_by_rid($icoarr['rid'], $icoarr['pfid'], $icoarr['gid']);
                    //修改分享表状态
                    C::t('shares')->change_by_rid($icoarr['rid'], '-5');
                    //添加移动文件动态
                    $oldhash = C::t('resources_event')->get_showtpl_hash_by_gpfid($oldpfid, $oldgid);
                    $eventdata1 = array('username' => $_G['username'], 'olderposition' => $oldpathstr, 'newposition' => $targetstr, 'filename' => $icoarr['name'], 'hash' => $oldhash);
                    C::t('resources_event')->addevent_by_pfid($pfid, 'moved_file', 'movedfile', $eventdata1, $gid, $rid, $icoarr['name']);

                    $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($icoarr['pfid'], $ogid);
                    $eventdata2 = array('username' => $_G['username'], 'newposition' => $targetstr, 'filename' => $icoarr['name'], 'hash' => $hash);
                    C::t('resources_event')->addevent_by_pfid($oldpfid, 'move_file', 'movefile', $eventdata2, $ogid, $rid, $icoarr['name']);
                }
                if ($ogid != $gid) {
                    if ($totalsize > 0) {
                        SpaceSize(-$totalsize, $ogid, 1);
                        SpaceSize($totalsize, $gid, 1);
                    }
                }
                if (!$first) {
                    //addtoconfig($icoarr);
                }
            }

        } else {
            C::t('resources')->update_by_rid($icoarr['rid'], array('isdelete' => 0, 'deldateline' => 0));
            //addtoconfig($icoarr);
        }
        if ($icoarr['type'] == 'folder') C::t('folder')->update($icoarr['oid'], array('isdelete' => 0));
        $return['icoarr'] = C::t('resources')->fetch_by_rid($icoarr['rid']);
        $return['icoarr']['monthdate'] = dgmdate($return['icoarr']['dateline'], 'm-d');
        $return['icoarr']['hourdate'] = dgmdate($return['icoarr']['dateline'], 'H:i');
        unset($icoarr);
        return $return;
        return array('error' => lang('movement_error') . '！');
    }

    //本地文件复制到本地其它区域
    public function FileCopy($rid, $pfid, $first = true,$force)
    {
        global $_G, $_GET;
        if (!$tfolder = DB::fetch_first("select * from " . DB::table('folder') . " where fid='{$pfid}'")) {
            return array('error' => lang('target_location_not_exist'));
        }
        if ($icoarr = C::t('resources')->fetch_by_rid($rid)) {

            unset($icoarr['rid']);
            //判断当前文件有没有拷贝权限；
			if(!$force){
				if($icoarr['type'] == 'folder'){
					$permcheck = C::t('resources')->check_folder_perm($icoarr,'copy');
					if($permcheck['error']){
						return array('error'=>$permcheck['error']);
					}
				}else{
					if (!perm_check::checkperm('copy', $icoarr)) {
						return array('error' => lang('privilege'));
					}
				}

				//判断当前目录有无添加权限
				if (!perm_check::checkperm_Container($pfid, 'upload')) {
					return array('error' => lang('privilege'));
				}
			}
            $success = 0;
            $gid = DB::result_first("select gid from " . DB::table('folder') . " where fid='{$pfid}'");
            $targetpatharr = C::t('resources_path')->fetch_pathby_pfid($pfid,true);//目标路径
            $targetpath = $targetpatharr['path'];

            if ($icoarr['type'] == 'folder') {
                $foldercontains = C::t('resources')->get_contains_by_fid($icoarr['oid']);
                if (!SpaceSize($foldercontains['size'], $gid)) {
                    return array('error' => lang('inadequate_capacity_space'));
                }
                if ($icoarr['pfid'] == $pfid) {//判断源文件位置和目标位置是否相同,如果相同则生成副本

                    $icoarr['name'] = $icoarr['name'] . '-副本';

                    if ($ricoid = self::getRepeatIDByName($icoarr['name'], $pfid, ($icoarr['type'] == 'folder') ? true : false)) {//如果目录下有同名文件

                        $icoarr['name'] = self::getFolderName($icoarr['name'], $pfid);

                    }
                }
                //查询原文件夹是否存在
                if ($folder = C::t('folder')->fetch($icoarr['oid'])) {

                    //如果目标目录中有同名文件夹，并且源文件位置和目标位置不在同一目录，则将源文件夹中文件放入该目录下
                    if ($icoarr['pfid'] != $pfid && $currentinfo = DB::fetch_first("select oid,rid from %t where pfid = %d and `name` = %s and `type` = %s and isdelete < 1",
                            array('resources', $tfolder['fid'], $icoarr['name'], 'folder'))
                    ) {
                        $currentfid = $currentinfo['oid'];
                        //复制源文件夹数据到目标目录同名文件夹
                        foreach (C::t('resources')->fetch_by_pfid($icoarr['oid']) as $value) {
                            try {
                                self::FileCopy($value['rid'], $currentfid, false);
                            } catch (Exception $e) {
                            }
                        }
                        $data = C::t('resources')->fetch_by_rid($currentinfo['rid']);
                        $return['folderarr'] = $data;
                        $icoarr['rid'] = $data['rid'];

                    } else {//如果目标目录中不存在同名文件夹或者存在同名文件夹而源文件位置和目标位置在同一目录，执行创建
                        if ($data = self::createFolderByPath($icoarr['name'], $pfid)) {//根据文件夹名字和当前文件夹路径创建文件夹
                            foreach (C::t('resources')->fetch_by_pfid($folder['fid']) as $value) {//查询原文件夹中文件
                                try {
                                    self::FileCopy($value['rid'], $data['pfid'], false);//复制原文件夹中文件到新文件夹
                                } catch (Exception $e) {
                                }
                            }
                            $return['folderarr'] = $data['folderarr'][0];

                            $icoarr['rid'] = $data['icoarr'][0]['rid'];
                        }
                    }

                } else {
                    return array('error', lang('folder_not_exist'));
                }
            } else {
                //判断空间大小是否足够
                if (!SpaceSize($icoarr['size'], $gid)) {
                    return array('error' => lang('inadequate_capacity_space'));
                }
                //判断文件名重复
                if ($icoarr['pfid'] == $pfid) {
                    $namestr = $icoarr['name'];
                    $ext = '';
                    $namearr = explode('.', $namestr);
                    if (count($namearr) > 1) {
                        $ext = $namearr[count($namearr) - 1];
                        unset($namearr[count($namearr) - 1]);
                        $ext = $ext ? ('.' . $ext) : '';
                    }
                    $tname = implode('.', $namearr);
                    $icoarr['name'] = $tname . '-副本' . $ext;
                }

                if ($ricoid = self::getRepeatIDByName($icoarr['name'], $pfid, ($icoarr['type'] == 'folder') ? true : false)) {//如果目录下有同名文件

                    $icoarr['name'] = self::getFileName($icoarr['name'], $pfid);

                }
                $setarr = array(
                    'name' => $icoarr['name'],
                    'oid' => $icoarr['oid'],
                    'uid' => getglobal('uid'),
                    'username' => getglobal('username'),
                    'pfid' => $pfid,
                    'gid' => $tfolder['gid'],
                    'type' => $icoarr['type'],
                    'dateline' => TIMESTAMP,
                    'ext' => $icoarr['ext'],
                    'size' => $icoarr['size'],
                    'vid' => 0,
                );
                //新建文件
                if ($icoarr['rid'] = C::t('resources')->insert_data($setarr)) {
                    $sourceattrdata = array(
                        'postip' => $_G['clientip'],
                        'title' => $setarr['filename'],
                        'aid' => isset($icoarr['aid']) ? $icoarr['aid'] : '',
                        'img' => $icoarr['img'],
                    );
                    if (C::t('resources_attr')->insert_attr($icoarr['rid'], $setarr['vid'], $sourceattrdata)) {//插入属性表
                        if ($icoarr['aid']) {
                            $attach = C::t('attachment')->fetch($icoarr['aid']);
                            C::t('attachment')->update($icoarr['aid'], array('copys' => $attach['copys'] + 1));//增加使用数
                        }
                        if ($icoarr['oid']) {
                            $attach = C::t('collect')->fetch($icoarr['oid']);
                            C::t('collect')->update($icoarr['oid'], array('copys' => $attach['copys'] + 1));//增加使用数
                        }
                        $icoarr['path'] = $targetpath . $setarr['name'];
                        $event = 'creat_file';
                        $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($pfid, $setarr['gid']);
                        $eventdata = array(
                            'title' => $setarr['name'],
                            'aid' => $icoarr['aid'],
                            'username' => $setarr['username'],
                            'uid' => $setarr['uid'],
                            'position' => preg_replace('/dzz:(.+?):/', '', $targetpath),
                            'hash' => $hash
                        );
                        C::t('resources_event')->addevent_by_pfid($pfid, $event, 'create', $eventdata, $setarr['gid'], $icoarr['rid'], $icoarr['name']);
                    } else {
                        C::t('resources')->delete_by_rid($icoarr['rid']);
                        return array('error' => lang('data_error'));
                    }
                }
            }
            if ($icoarr['rid']) {
                if ($icoarr['size'] > 0) {
                    SpaceSize($icoarr['size'], $gid, 1, $icoarr['uid']);
                }
                if (!$first) {
                    //addtoconfig($icoarr);
                } else {
                    $return['icoarr'] = C::t('resources')->fetch_by_rid($icoarr['rid']);
                    $return['icoarr']['monthdate'] = dgmdate($return['icoarr']['dateline'], 'm-d');
                    $return['icoarr']['hourdate'] = dgmdate($return['icoarr']['dateline'], 'H:i');
                    Hook::listen('createafter_addindex',$return['icoarr']);
                    return $return;
                }

            } else {
                return array('error' => lang('files_allowed_copy'));
            }
            return array('error' => 'copy error');
        }
    }

    /*
		表单上传文件保存到attachment表，返回attach数组
	*/
    function UploadSave($FILE)
    {
        global $_G;
        $ext = strtolower(substr(strrchr($FILE['name'], '.'), 1));
        $target = self::getPath($ext ? ('.' . $ext) : '', 'dzz');
        if ($ext && in_array(strtolower($ext), $_G['setting']['unRunExts'])) {
            $unrun = 1;
        } else {
            $unrun = 0;
        }
        $filepath = $_G['setting']['attachdir'] . $target;
        if (!save_to_local($FILE['tmp_name'], $filepath)) {
            return false;
        }
        $md5 = md5_file($filepath);

        if ($md5 && $attach = DB::fetch_first("select * from " . DB::table('attachment') . " where md5='{$md5}'")) {
            $attach['filename'] = $FILE['name'];
            @unlink($filepath);
            unset($attach['attachment']);
            return $attach;
        } else {
            $remote = 0;

            $attach = array(
                'filesize' => $FILE['size'],
                'attachment' => $target,
                'filetype' => strtolower($ext),
                'filename' => $FILE['name'],
                'remote' => $remote,
                'copys' => 0,
                'md5' => $md5,
                'unrun' => $unrun,
                'dateline' => $_G['timestamp'],
            );
            if ($attach['aid'] = C::t('attachment')->insert($attach, 1)) {
                C::t('local_storage')->update_usesize_by_remoteid($attach['remote'], $attach['filesize']);
                dfsockopen($_G['siteurl'] . 'misc.php?mod=movetospace&aid=' . $attach['aid'] . '&remoteid=0', 0, '', '', FALSE, '', 1);
                unset($attach['attachment']);
                return $attach;
            } else {
                return false;
            }
        }
    }


    public function multiUpload($opath, $path, $filename, $attach = array(), $ondup = "newcopy")
    {
        /*
	 * 分块上传文件
	 * param $file:文件路径（可以是url路径，需要服务器开启allow_url_fopen);
	*/
        $data = IO::getMeta($opath);
        if ($data['error']) return $data;
        $size = $data['size'];
        if (is_array($filepath = IO::getStream($opath))) {
            return array('error' => $filepath['error']);
        }
        //判断大小
        //判断空间大小
        $filename = self::name_filter($filename);

        if (strpos($path, 'dzz::') === false && strpos($path, 'TMP::') === false) {
            $gid = DB::result_first("select gid from %t where fid=%d", array('folder', $path));
            if (!SpaceSize($size, $gid)) {
                return array('error' => lang('inadequate_capacity_space'));
            }
        }
        if (!$handle = fopen($filepath, 'rb')) {
            return array('error' => lang('open_file_error'));
        }
        if (strpos($path, 'dzz::') !== false || strpos($path, 'TMP::') !== false) {
            $file = self::getStream($path . '/' . $filename);
            while (!feof($handle)) {
                $fileContent = fread($handle, 8192);
                file_put_contents($file, $fileContent, FILE_APPEND);
                unset($fileContent);
            }
            fclose($handle);
            return true;
        } else {
            $pathinfo = pathinfo($filename);
            $ext = strtolower($pathinfo['extension']);
            $target = $this->getPath($ext ? ('.' . $ext) : '', 'dzz');
            $file = getglobal('setting/attachdir') . '/' . $target;
            while (!feof($handle)) {
                $fileContent = fread($handle, 8192);
                file_put_contents($file, $fileContent, FILE_APPEND);
                unset($fileContent);
            }
            fclose($handle);
        }

        $nfilename = self::getFileName($filename, $path); //重命名

        if ($attach = $this->save($target, $nfilename)) {
            //return array('error'=>json_encode($attach));
            if ($attach['error']) {
                return array('error' => $attach['error']);
            } else {
                return $this->uploadToattachment($attach, $path);
            }
        } else {
            return array('error' => 'failure');
        }

    }

    public function shenpiCreateFile($fid, $path, $attach)
    {
        $data = self::createFolderByPath($path, $fid);;
        return self::uploadToattachment($attach, $data['pfid']);
    }
}

?>
