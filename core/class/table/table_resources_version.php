<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class table_resources_version extends dzz_table {
    public function __construct() {

        $this->_table = 'resources_version';
        $this->_pk = 'vid';
        $this->_pre_cache_key = 'resources_version_';
        $this->_cache_ttl = 5 * 60;
        parent::__construct();
    }

    //获取文件图标
    public function getfileimg($data) {
        if ($data['type'] == 'image') {
            $data['img'] = DZZSCRIPT . '?mod=io&op=thumbnail&size=small&path=' . dzzencode('attach::' . $data['aid']);
        } elseif ($data['type'] == 'attach' || $data['type'] == 'document') {
            $data['img'] = geticonfromext($data['ext'], $data['type']);
        } else {
            $data['img'] = isset($data['img']) ? $data['img'] : geticonfromext($data['ext'], $data['type']);
        }
        return $data['img'];
    }

    public function fetch_all_by_rid($rid, $limit = '', $count = false) {
        $rid = trim($rid);
        $versions = [];
        $limitsql = '';
        if ($limit) {
            $limitarr = explode('-', $limit);
            if (count($limitarr) > 1) {
                $limitsql = "limit $limitarr[0],$limitarr[1]";
            } else {
                $limitsql = "limit 0,$limitarr[0]";
            }
        }
        if ($count) {
            return DB::result_first("select count(*) from %t where rid = %s", [$this->_table, $rid]);
        }
        $resources = C::t('resources')->fetch_info_by_rid($rid);
        if ($resources['vid'] == 0) {
            $attrdata = C::t('resources_attr')->fetch_by_rid($rid, 0);
            $filedata = [
                'vid' => 0,
                'rid' => $rid,
                'uid' => $resources['uid'],
                'username' => $resources['username'],
                'vname' => '',
                'aid' => $attrdata['aid'],
                'type' => $resources['type'],
                'ext' => $resources['ext'],
                'size' => $resources['size'],
                'dateline' => $resources['dateline'],
                'img' => $attrdata['img']
            ];
            $filedata['img'] = self::getfileimg($filedata);
            $versions[$filedata['vid']] = $filedata;
        } else {
            foreach (DB::fetch_all("select * from %t where rid = %s order by dateline desc $limitsql ", [$this->_table, $rid]) as $val) {
                $attrdata = C::t('resources_attr')->fetch_by_rid($rid, $val['vid']);
                $val['img'] = isset($attrdata['img']) ? $attrdata['img'] : '';
                $filedata = $val;
                $filedata['img'] = self::getfileimg($filedata);
                $versions[$val['vid']] = $filedata;
            }
        }
        return $versions;
    }

    public function delete_by_vid($vid, $rid = '', $event = false) {
        $vid = intval($vid);
        if (!$vinfo = parent::fetch($vid)) return false;
        if (empty($rid)) $rid = $vinfo['rid'];
        $datainfo = C::t('resources')->fetch_info_by_rid($rid);
        if ($datainfo['vid'] == $vinfo['vid']) {//如果删除的是主版本，判断是否是最后一个版本，最后一个版本不让删除
            if (!$nvid = DB::result_first("select vid from %t where rid=%s and vid!=%d order by vid DESC", [$this->_table, $rid, $vid])) {
                return false;
            }
        }
        if (parent::delete($vid)) {
            if ($vinfo['aid']) C::t('attachment')->delete_by_aid($vinfo['aid']);
            SpaceSize(-$vinfo['size'], $datainfo['gid'], 1, $datainfo['uid']);
            C::t('resources_attr')->delete_by_rvid($rid, $vid);
            if ($event) {
                $position = C::t('resources_path')->fetch_pathby_pfid($datainfo['pfid']);
                $position = preg_replace('/dzz:(.+?):/', '', $position);
                $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($datainfo['pfid'], $datainfo['gid']);
                $eventdata = ['username' => $_G['username'], 'position' => $position, 'filename' => $datainfo['name'], 'version' => $vid, 'hash' => $hash];
                C::t('resources_event')->addevent_by_pfid($datainfo['pfid'], 'delete_version', 'delversion', $eventdata, $datainfo['gid'], $rid, $datainfo['name']);
            }
            $cachekey = 'resourcesversiondata_' . $rid;
            $this->clear_cache($cachekey);
            if ($nvid) {//如果删除的是主版本，需要重新设置文件其他版本为主版本
                self::set_primary_version_by_vid($nvid, true);
            }
        }
        return true;
    }

    public function delete_by_rid($rid) {
        $vids = [];
        $aids = [];
        $size = 0;
        $datainfo = C::t('resources')->fetch($rid);
        foreach (DB::fetch_all("select * from %t where rid = %s", [$this->_table, $rid]) as $value) {
            $vids[] = $value['vid'];
            $aids[] = $value['aid'];
            $size += intval($value['size']);
        }
        if ($ret = parent::delete($vids)) {
            $cachekey = 'resourcesversiondata_' . $rid;
            $this->clear_cache($cachekey);
            foreach ($aids as $aid) {
                C::t('attachment')->delete_by_aid($aid);
            }
            SpaceSize(-$size, $datainfo['gid'], 1, $datainfo['uid']);
        }
        return $ret;
    }

    //上传新版本
    public function add_new_version_by_rid($rid, $setarr, $force = false) {
        global $_G, $documentexts;
        if (!$resources = C::t('resources')->fetch_info_by_rid($rid)) {
            return ['error' => lang('file_not_exist')];
        }
        if ($resources['type'] == 'folder') {
            return ['error' => lang('folder_not_allowed_history')];
        }
        if (!$setarr['name']) $setarr['name'] = $resources['name'];
        if (!$setarr['name']) return ['error' => lang('name_cannot_empty')];
        //检测权限
        if (!$force && !perm_check::checkperm('edit', $resources)) {
            return ['error' => lang('file_edit_no_privilege')];
        }
        //文件类型获取
        $imgexts = ['jpg', 'jpeg', 'gif', 'png', 'bmp', 'webp'];
        if (in_array(strtolower($setarr['ext']), $imgexts)) {
            $setarr['type'] = 'image';
        } elseif (in_array(strtoupper($setarr['ext']), $documentexts)) {
            $setarr['type'] = 'document';
        } else {
            $setarr['type'] = 'attach';
        }

        //没有版本时,属性表和版本数据处理
        if ($resources['vid'] == 0) {
            $oldattr = C::t('resources_attr')->fetch_by_rid($rid);
            $setarr1 = [
                'rid' => $rid,
                'uid' => $resources['uid'],
                'username' => $resources['username'],
                'vname' => '',
                'size' => $resources['size'],
                'ext' => $resources['ext'],
                'type' => $resources['type'],
                'dateline' => $resources['dateline'],
                'aid' => intval($oldattr['aid'])
            ];
            //将原数据插入版本表
            if ($oldvid = parent::insert($setarr1, 1)) {
                C::t('resources_attr')->update_vid_by_rvid($rid, 0, $oldvid);
            } else {
                return ['error' => lang('failure')];
            }
        }

        //文件名
        $filename = $setarr['name'];
        $filename = self::getFileName($setarr['name'], $resources['pfid'], $rid);
        unset($setarr['name']);
        $setarr['rid'] = $rid;

        //新数据插入版本表
        if ($vid = parent::insert($setarr, 1)) {
            $cachekey = 'resourcesversiondata_' . $rid;
            $this->clear_cache($cachekey);
            //更新主表数据
            if (C::t('resources')->update_by_rid($rid, ['vid' => $vid, 'size' => $setarr['size'], 'ext' => $setarr['ext'], 'type' => $setarr['type'], 'name' => $filename])) {
                SpaceSize($setarr['size'], $resources['gid'], true);
                //插入属性表数据
                $sourceattrdata = [
                    'postip' => $_G['clientip'],
                    'title' => $filename,
                    'aid' => isset($setarr['aid']) ? $setarr['aid'] : '',
                    //  'img'=>geticonfromext($setarr['ext'],$setarr['type'])
                ];
                //插入属性表
                if (C::t('resources_attr')->insert_attr($rid, $vid, $sourceattrdata)) {
                    if ($setarr['aid']) {
                        $attach = C::t('attachment')->fetch($setarr['aid']);
                        C::t('attachment')->update($setarr['aid'], ['copys' => $attach['copys'] + 1]);//增加使用数
                    }
                }
                //记录事件
                $path = C::t('resources_path')->fetch_pathby_pfid($resources['pfid']);
                $path = preg_replace('/dzz:(.+?):/', '', $path);
                $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($resources['pfid'], $resources['gid']);
                $event = 'update_version';
                $eventdata = [
                    'title' => $resources['name'],
                    'aid' => $setarr['aid'],
                    'username' => $setarr['username'],
                    'uid' => $setarr['uid'],
                    'position' => $path,
                    'hash' => $hash,
                ];
                C::t('resources_event')->addevent_by_pfid($resources['pfid'], $event, 'updatevesion', $eventdata, $resources['gid'], $rid, $resources['name']);
                //增加统计数据
                $statis = [
                    'edits' => 1,
                    'uid' => $_G['uid'],
                    'editdateline' => TIMESTAMP
                ];
                C::t('resources_statis')->add_statis_by_rid($rid, $statis);
                $setarr['fdateline'] = dgmdate($setarr['dateline'], 'Y-m-d H:i:s');
                $setarr['vid'] = $vid;
                $setarr['size'] = formatsize($setarr['size']);
                if ($resources['vid'] == 0) {
                    $setarr['olddatavid'] = $oldvid;
                }
                $indexarr = ['rid' => $rid];
                Hook::listen('createafter_addindex', $indexarr);
                $setarr['dpath'] = dzzencode($rid);
                return $setarr;
            } else {
                parent::delete($vid);
                return ['error' => lang('failure')];
            }
        }

    }

    //设置主版本
    public function set_primary_version_by_vid($vid) {
        global $_G;
        if (!$versioninfo = parent::fetch($vid)) {
            return ['error' => lang('file_not_exist')];
        }
        if (!$fileinfo = C::t('resources')->fetch($versioninfo['rid'])) return ['error' => lang('file_not_exist')];

        //判断编辑权限
        if (!perm_check::checkperm('edit', $fileinfo)) {
            return ['error' => lang('file_edit_no_privilege')];
        }

        $vfilename = DB::result_first("select sval from %t where vid = %d and rid = %s and skey = %s", ['resources_attr', $vid, $versioninfo['rid'], 'title']);
        if (!$vfilename) $vfilename = $fileinfo['name'];
        if (!$vfilename) {
            return ['error' => lang('name_cannot_empty')];
        }

        //获取不重复的名字
        $filename = self::getFileName($vfilename, $fileinfo['pfid'], $versioninfo['rid']);
        if (!$filename) {
            $filename = $versioninfo['vname'];
            if ($filename != $vfilename) {
                C::t('resources_attr')->update_by_skey($fileinfo['rid'], $vid, ['title' => $filename]);
            }
        }
        //更改resources表数据
        $updatearr = ['vid' => $vid, 'name' => $filename, 'size' => $versioninfo['size'], 'ext' => $versioninfo['ext'], 'type' => $versioninfo['type']];
        if (C::t('resources')->update_by_rid($versioninfo['rid'], $updatearr)) {
            //文件路径信息
            $path = C::t('resources_path')->fetch_pathby_pfid($fileinfo['pfid']);
            $path = preg_replace('/dzz:(.+?):/', '', $path);
            $event = 'setprimary_version';
            $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($fileinfo['pfid'], $fileinfo['gid']);
            $eventdata = [
                'name' => $filename,
                'oldname' => $fileinfo['name'],
                'aid' => $versioninfo['aid'],
                'username' => $_G['username'] ?: $_G['clientip'],
                'uid' => $_G['uid'],
                'position' => $path,
                'hash' => $hash
            ];
            $statis = [
                'edits' => 1,
                'uid' => $_G['uid'],
                'editdateline' => TIMESTAMP
            ];
            C::t('resources_statis')->add_statis_by_rid($versioninfo['rid'], $statis);
            C::t('resources_event')->addevent_by_pfid($fileinfo['pfid'], $event, 'setprimaryversion', $eventdata, $fileinfo['gid'], $fileinfo['rid'], $fileinfo['name']);
            $indexarr = ['rid' => $versioninfo['rid']];
            Hook::listen('createafter_addindex', $indexarr);
            return ['rid' => $versioninfo['rid']];
        } else {
            return ['error' => lang('explorer_do_failed')];
        }

    }

    //判断文件重名
    public function getFileName($name, $pfid, $rid = '') {
        static $i = 0;
        $params = ['resources', $name, $pfid];
        $wheresql = '';
        if ($rid) {
            $wheresql .= " and rid != %s ";
            $params[] = $rid;
        }
        $name = IO::name_filter($name);
        if (DB::result_first("select COUNT(*) from %t where type!='folder' and name=%s and isdelete<1 and pfid=%d $wheresql", $params)) {
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
            return self::getFileName($name, $pfid, $rid);
        } else {
            return $name;
        }
    }

    //根据版本id修改版本名称
    public function update_versionname_by_vid($vid, $vname, $vdesc = '') {
        global $_G;
        if (!$versioninfo = parent::fetch($vid)) {
            return ['error' => lang('file_not_exist')];
        }
        $eventdesc = $eventname = '';
        $sertarr = [];
        if ($versioninfo['vname'] != $vname) {
            if (DB::result_first("select count(*) from %t where vname = %s and rid = %s", [$this->_table, $vname, $versioninfo['rid']]) > 0) {
                return ['error' => lang('explorer_name_repeat')];
            }
            $sertarr['vname'] = $vname;
            $eventname = 'edit_versionname';
            $sertarr['dateline'] = TIMESTAMP;
        }
        if ($versioninfo['vdesc'] != $vdesc) {
            $sertarr['vdesc'] = $vdesc;
            $eventdesc = 'edit_versiondesc';
            $sertarr['dateline'] = TIMESTAMP;
        }

        //文件基本信息
        $fileinfo = C::t('resources')->fetch_info_by_rid($versioninfo['rid']);

        //判断编辑权限
        if (!perm_check::checkperm('edit', $fileinfo)) {
            return ['error' => lang('file_edit_no_privilege')];
        }
        if (empty($sertarr)) {
            return ['error' => lang('explorer_do_failed')];
        }
        if (parent::update($vid, $sertarr)) {
            $path = C::t('resources_path')->fetch_pathby_pfid($fileinfo['pfid']);
            $path = preg_replace('/dzz:(.+?):/', '', $path);
            $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($fileinfo['pfid'], $fileinfo['gid']);
            $statis = [
                'edits' => 1,
                'uid' => $_G['uid'],
                'editdateline' => TIMESTAMP
            ];
            C::t('resources_statis')->add_statis_by_rid($versioninfo['rid'], $statis);
            if ($eventname) {
                $eventdata = [
                    'name' => $vname,
                    'filename' => $fileinfo['name'],
                    'username' => $_G['username'] ?: $_G['clientip'],
                    'uid' => $_G['uid'],
                    'position' => $path,
                    'hash' => $hash
                ];
                C::t('resources_event')->addevent_by_pfid($fileinfo['pfid'], $eventname, 'editversionname', $eventdata, $fileinfo['gid'], $fileinfo['rid'], $fileinfo['name']);
            }
            if ($eventdesc) {
                $eventdata1 = [
                    'filename' => $fileinfo['name'],
                    'username' => $_G['username'] ?: $_G['clientip'],
                    'uid' => $_G['uid'],
                    'position' => $path,
                    'hash' => $hash
                ];
                C::t('resources_event')->addevent_by_pfid($fileinfo['pfid'], $eventdesc, 'editversiondesc', $eventdata1, $fileinfo['gid'], $fileinfo['rid'], $fileinfo['name']);
            }
            $cachekey = 'resourcesversiondata_' . $versioninfo['rid'];
            $this->clear_cache($cachekey);
            return ['vid' => $vid, 'primaryvid' => $fileinfo['vid'], 'fdateline' => dgmdate($versioninfo['dateline'], 'Y-m-d H:i:s')];
        } else {
            return ['error' => lang('explorer_do_failed')];
        }
    }

    //根据rid修改版本名称,因版本表无数据,需先将主表数据放入版本表，然后更新主表和属性表
    public function update_versionname_by_rid($rid, $vname, $vdesc = '') {
        global $_G;
        if (!$fileinfo = C::t('resources')->fetch_info_by_rid($rid)) {
            return ['error' => lang('file_not_exist')];
        }

        //判断编辑权限
        if (!perm_check::checkperm('edit', $fileinfo)) {
            return ['error' => lang('file_edit_no_privilege')];
        }
        //没有版本时,属性表和版本数据处理
        $setarr = [
            'rid' => $rid,
            'uid' => $fileinfo['uid'],
            'username' => $fileinfo['username'],
            'vname' => $vname,
            'vdesc' => $vdesc,
            'size' => $fileinfo['size'],
            'ext' => $fileinfo['ext'],
            'type' => $fileinfo['type'],
            'dateline' => TIMESTAMP
        ];
        //将数据插入版本表
        if ($vid = parent::insert($setarr, 1)) {
            //更新属性表数据
            C::t('resources_attr')->update_by_skey($rid, 0, ['vid' => $vid]);
            //更新主表数据
            if (C::t('resources')->update_by_rid($rid, ['vid' => $vid])) {
                $path = C::t('resources_path')->fetch_pathby_pfid($fileinfo['pfid']);
                $path = preg_replace('/dzz:(.+?):/', '', $path);
                $event = 'edit_versionname';
                $vfilename = DB::result_first("select sval from %t where vid = %d and rid = %s and skey = %s", ['resources_attr', $vid, $fileinfo['rid'], 'title']);
                $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($fileinfo['pfid'], $fileinfo['gid']);
                $eventdata = [
                    'name' => $vname,
                    'filename' => $fileinfo['name'],
                    'username' => $_G['username'] ?: $_G['clientip'],
                    'uid' => $_G['uid'],
                    'position' => $path,
                    'hash' => $hash
                ];
                if ($vdesc) {
                    $eventdata1 = [
                        'filename' => $fileinfo['name'],
                        'username' => $_G['username'] ?: $_G['clientip'],
                        'uid' => $_G['uid'],
                        'position' => $path,
                        'hash' => $hash
                    ];
                    C::t('resources_event')->addevent_by_pfid($fileinfo['pfid'], 'edit_versiondesc', 'editversiondesc', $eventdata1, $fileinfo['gid'], $fileinfo['rid'], $fileinfo['name']);
                }
                $statis = [
                    'edits' => 1,
                    'uid' => $_G['uid'],
                    'editdateline' => TIMESTAMP
                ];
                C::t('resources_statis')->add_statis_by_rid($fileinfo['rid'], $statis);
                C::t('resources_event')->addevent_by_pfid($fileinfo['pfid'], $event, 'editversionname', $eventdata, $fileinfo['gid'], $fileinfo['rid'], $fileinfo['name']);
                return ['vid' => $vid, 'primaryvid' => $fileinfo['vid'], 'fdateline' => dgmdate($setarr['dateline'], 'Y-m-d H:i:s')];
            } else {
                parent::delete($vid);
                return ['error' => lang('explorer_do_failed')];
            }
        } else {
            return ['error' => lang('explorer_do_failed')];
        }
    }

    public function get_versioninfo_by_rid_vid($rid, $vid = 0) {
        $rid = trim($rid);
        if (!$rid)
        $vid = intval($vid);
        $data = [];
        if ($vid) {
            $data = DB::fetch_first("select * from %t where rid = %s and vid = %d", [$this->_table, $rid, $vid]);
        } else {
            $data = C::t('resources')->fetch_info_by_rid($rid);
        }
        if ($data) {
            $data['ffsize'] = lang('property_info_size', ['fsize' => formatsize($data['size']), 'size' => $data['size']]);
            $data['fdateline'] = dgmdate($data['dateline'], 'Y-m-d H:i:s');
            $data['ftype'] = getFileTypeName($data['type'], $data['ext']);
        }
        
        return $data;
    }

    public function fetch_version_by_rid_vid($rid, $vid) {
        $rid = trim($rid);
        $vid = intval($vid);
        $data = [];
        if (!$data = C::t('resources')->fetch_info_by_rid($rid)) {
            return $data;
        }
        $versiondata = DB::fetch_first("select * from %t where rid = %s and vid = %d", [$this->_table, $rid, $vid]);
        $data = array_merge($data, $versiondata);
        $attrdata = C::t('resources_attr')->fetch_by_rid($rid, $vid);
        $data = array_merge($data, $attrdata);
        $data['icoid'] = dzzencode('attach::' . $data['aid']);
        return $data;
    }

}