<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class table_shares extends dzz_table {
    public function __construct() {

        $this->_table = 'shares';
        $this->_pk = 'id';
        parent::__construct();
    }

    public function insert($setarr, $bz = false, $replace = false, $silent = false) {
        $rid = $setarr['filepath'];
        if (!$setarr['title']) {
            return ['error' => '分享标题不能为空'];
        }
        $more = false;
        if ($bz) {
            $setarr['gid'] = 0;
            $setarr['pfid'] = -1;
        } else {
            $rids = explode(',', $rid);
            $pfids = [];
            foreach (DB::fetch_all("select pfid from %t where rid in(%n)", ['resources', $rids]) as $v) {
                $pfids[] = $v['pfid'];
            }
            $pfids = array_unique($pfids);
            if (count($pfids) > 1) {
                return ['error' => lang('Only_allow_sharing_filesinsamedirectory')];
            }
            $fileinfo = C::t('resources')->fetch_info_by_rid($rids[0]);
            $setarr['gid'] = $fileinfo['gid'];
            $setarr['pfid'] = $fileinfo['pfid'];
            if (count($rids) > 1) $more = true;
            if ($more) {
                $fileinfo['name'] .= '等文件(文件夹)';
                $setarr['type'] = 'url';
            } else {
                $setarr['type'] = $fileinfo['type'];
            }
        }
        $setarr['dateline'] = time();
        $setarr['uid'] = getglobal('uid');
        $setarr['username'] = getglobal('username');
        if ($insert = parent::insert($setarr, 1)) {
            if($bz) return ['success' => $insert];
            //$share['qrcode'] = self::getQRcodeBySid($insert);
            $path = C::t('resources_path')->fetch_pathby_pfid($fileinfo['pfid']);
            $path = preg_replace('/dzz:(.+?):/', '', $path) ? preg_replace('/dzz:(.+?):/', '', $path) : '';
            $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($fileinfo['pfid'], $fileinfo['gid']);
            $eventdata = [
                'username' => $setarr['username'],
                'filename' => $fileinfo['name'],
                'position' => $path,
                'hash' => $hash,
            ];
            if (!C::t('resources_event')->addevent_by_pfid($fileinfo['pfid'], 'share_file', 'share', $eventdata, $fileinfo['gid'], $fileinfo['rid'], $fileinfo['name'])) {
                parent::delete($insert);
                return ['error' => lang('create_share_failer')];
            } else {
                return ['success' => $insert];
            }
        }
    }

    //更改分享状态
    public function change_by_rid($rid, $status = -3) {
        if (!is_array($rid)) $rid = (array)$rid;
        $wheresql = '0';
        $params = [$this->_table];
        foreach ($rid as $v) {
            $wheresql .= " or find_in_set(%s,filepath)";
            $params[] = $v;
        }
        $sids = [];
        foreach (DB::fetch_all("select id from %t where $wheresql", $params) as $v) {
            $sids[] = $v['id'];
        }
        DB::update($this->_table, ['status' => $status], "id in(" . dimplode($sids) . ")");
    }

    //文件进入回收站时，判断相关分享中是否还有对应数据，如果分享包含文件全部删除，改变分享状态
    public function recycle_by_rid($rids) {
        if (!is_array($rids)) $rids = (array)$rids;
        $params = [$this->_table];
        $wheresql = '0';
        foreach ($rids as $v) {
            $wheresql .= " or find_in_set(%s,filepath)";
            $params[] = $v;
        }
        $sharedata = DB::fetch_all("select * from %t where $wheresql", $params);
        foreach ($sharedata as $v) {
            $paths = explode(',', $v['filepath']);
            if (count($paths) == 1) {
                parent::update($v['id'], ['status' => '-3']);
            } else {
                foreach ($rids as $val) {
                    $index = array_search($val, $paths);
                    if ($index === false) {
                        continue;
                    } else {
                        unset($paths[$index]);
                        $newpath = implode(',', $paths);
                        $isdelete = false;
                        foreach (DB::fecth_all("select isdelete from %t where rid in(%n)", ['resources', $newpath]) as $v) {
                            if ($v['isdelete'] > 0) {
                                $isdelete = true;
                            }
                        }
                        if ($isdelete) {
                            parent::update($v['id'], ['status' => '-3']);
                        }
                    }
                }
            }
        }
    }

    //恢复文件时，改变分享状态
    public function recover_file_by_rid($rids) {
        if (!is_array($rids)) $rids = (array)$rids;
        $params = [$this->_table];
        $wheresql = '0';
        foreach ($rids as $v) {
            $wheresql .= " or find_in_set(%s,filepath)";
            $params[] = $v;
        }
        $sharedata = DB::fetch_all("select * from %t where $wheresql", $params);
        foreach ($sharedata as $v) {
            $paths = explode(',', $v['filepath']);
            foreach ($rids as $val) {
                $index = in_array($val, $paths);
                if ($index === false) {
                    continue;
                } else {
                    parent::update($v['id'], ['status' => '0']);
                }

            }
        }
    }

    public function fetch_by_path($rid) {
        $rid = trim($rid);
        if ($info = DB::fetch_first("select * from %t where filepath = %s", [$this->_table, $rid])) {
            return $info;
        }
        return false;
    }

    public function fetch_by_shareid($rid) {
        if ($info = DB::fetch_first("select id from %t where filepath = %s", [$this->_table, $rid])) {
            return $info['id'];
        }
        return false;
    }

    public function update_by_id($id, $setarr, $bz='') {
        if (empty($setarr)) return false;
        if (!$id) return false;
        global $_G;
        $rid = $setarr['filepath'];
        $more = false;
        if(!$bz) {
            $rids = explode(',', $rid);
            if (count($rids) > 1) $more = true;
            $fileinfo = C::t('resources')->fetch_info_by_rid($rids[0]);
            if ($more) {
                $fileinfo['name'] .= '等文件(文件夹)';
            }
            $setarr['gid'] = $fileinfo['gid'];
            $setarr['pfid'] = $fileinfo['pfid'];
        }
        
        $setarr['dateline'] = time();
        $setarr['uid'] = $_G['uid'];
        $setarr['username'] = $_G['username'];
        if (parent::update($id, $setarr)) {
            if($bz) return $id;
            $path = C::t('resources_path')->fetch_pathby_pfid($fileinfo['pfid']);
            $path = preg_replace('/dzz:(.+?):/', '', $path) ? preg_replace('/dzz:(.+?):/', '', $path) : '';
            $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($fileinfo['pfid'], $fileinfo['gid']);
            $eventdata = ['username' => $setarr['username'], 'filename' => $fileinfo['name'], 'position' => $path, 'hash' => $hash];
            if (!C::t('resources_event')->addevent_by_pfid($fileinfo['pfid'], 'edit_share_file', 'share', $eventdata, $fileinfo['gid'], $fileinfo['rid'], $fileinfo['name'])) {
                parent::delete($id);
                return ['error' => lang('file_not_exist')];
            } else {
                return ['success' => $id];
            }
        } else {
            return ['error' => lang('explorer_do_failed')];
        }
    }

    public function delete_by_id($id) {
        global $_G;
        if (!$shareinfo = parent::fetch($id)) {
            return ['error' => lang('share_not_exists')];
        }
        if ($_G['adminid'] != 1) {
            if (!perm_check::checkperm_Container($shareinfo['pfid'], 'share')) {
                return ['error' => lang('file_share_no_privilege')];
            }
        }
        $setarr['dateline'] = time();
        $setarr['uid'] = $_G['uid'];
        $setarr['username'] = $_G['username'];
        if (parent::delete($id)) {
            $url = $_G['siteurl'] . 'index.php?mod=shares&sid=' . dzzencode($id);
            C::t('shorturl')->delete_by_url($url);//删除短链接
            C::t('share_report')->delete_by_sid($id);//删除举报记录
            $eventdata = ['username' => $setarr['username'], 'filename' => $shareinfo['title']];
            C::t('resources_event')->addevent_by_pfid($shareinfo['pfid'], 'cancle_share', 'cancleshare', $eventdata, $shareinfo['gid'], '', $shareinfo['title']);
            return ['success' => true, 'shareid' => $id, 'sharetitle' => $shareinfo['title']];
        } else {
            return ['error' => lang('explorer_do_failed')];
        }
    }

    //查询当前用户的分享
    public function fetch_all_share_file($limitsql = '', $ordersql = '', $count = false) {
        global $_G;
        $uid = $_G['uid'];
        $params = [$this->_table];
        $wheresql = ' uid = %d';
        $params[] = $uid;
        $orgids = C::t('organization')->fetch_all_orgid(false);//获取所有有管理权限的部门();
        $manageorgids = $orgids['orgids_admin'];
        if (!empty($manageorgids)) {
            $wheresql .= ' or gid in(%n)';
            $params[] = $manageorgids;
        }
        $shareinfo = [];
        if ($count) {
            return DB::result_first("select count(*) from %t where $wheresql $ordersql ", $params);
        }
        $sharestatus = ['-5' => lang('sharefile_isdeleted_or_positionchange'), '-4' => lang('been_blocked'), '-3' => lang('file_been_deleted'), '-2' => lang('degree_exhaust'), '-1' => lang('logs_invite_status_4'), '0' => lang('founder_upgrade_normal')];
        foreach (DB::fetch_all("select * from %t where $wheresql $ordersql $limitsql", $params) as $val) {
            $val['sharelink'] = C::t('shorturl')->getShortUrl('index.php?mod=shares&sid=' . dzzencode($val['id']));
            $val['fdateline'] = dgmdate($val['dateline'], 'Y-m-d H:i:s');
            $val['password'] = ($val['password']) ? dzzdecode($val['password']) : '';
            $sid = dzzencode($val['id']);
            if (is_file($_G['setting']['attachdir'] . './qrcode/' . $sid[0] . '/' . $sid . '.png')) {
                $val['qrcode'] = $_G['setting']['attachurl'] . './qrcode/' . $sid[0] . '/' . $sid . '.png';
            } else {
                $val['qrcode'] = self::getQRcodeBySid($sid);
            }
            $val['expireday'] = getexpiretext($val['endtime']);
            $rids = explode(',', $val['filepath']);
            if (count($rids) > 1) {
                $val['img'] = '/dzz/explorer/images/ic-files.png';
            } elseif ($val['type'] == 'folder') {
                $val['img'] = '/dzz/images/extimg/folder.png';
            } else {
                $val['img'] = $_G['siteurl'] . DZZSCRIPT . '?mod=io&op=thumbnail&size=small&path=' . dzzencode($val['filepath']);
            }
            $val['name'] = $val['title'];
            $val['rid'] = $val['id'];
            $val['shared'] = true;
            //检查文件是否被恢复
            if ($val['status'] == -3) {
                $isdelete = false;
                $resources = DB::fetch_all("select isdelete from %t where rid in(%n)", ['resources', $rids]);
                // 如果查询不到资源记录，则维持删除状态
                if (empty($resources)) {
                    $isdelete = true;
                }
                foreach ($resources as $v) {
                    if ($v['isdelete'] > 0) {
                        $isdelete = true;
                        break; // 找到一个删除的就可以退出循环
                    }
                }
                if (!$isdelete) {
                    DB::update($this->_table, ['status' => 0], ['id' => $val['id']]);
                    $val['status'] = 0;
                }
            }
            //检查文件是否移动
            if ($val['status'] == -5) {
                $ischange = false;
                $pfids = [];
                foreach (DB::fetch_all("select pfid from %t where rid in(%n)", ['resources', $rids]) as $v) {
                    $pfids[] = $v['pfid'];
                }
                $pfids = array_unique($pfids);
                if (count($pfids) < 2 && $pfids[0] == $val['pfid']) {
                    DB::update($this->_table, ['status' => 0], ['id' => $val['id']]);
                    $val['status'] = 0;
                }
            }
            $val['fstatus'] = $sharestatus[$val['status']];
            $shareinfo[$val['rid']] = $val;
        }
        return $shareinfo;
    }

    public function getQRcodeBySid($sid) {
        $target = './qrcode/' . $sid[0] . '/' . $sid . '.png';
        $targetpath = dirname(getglobal('setting/attachdir') . $target);
        dmkdir($targetpath);
        if (!@getimagesize(getglobal('setting/attachdir') . $target)) {//生成二维码
            QRcode::png((getglobal('siteurl') . 'index.php?mod=shares&sid=' . $sid), getglobal('setting/attachdir') . $target, 'M', 4, 2);
        }
        return getglobal('setting/attachurl') . $target;
    }

    //增加浏览次数
    public function add_views_by_id($id) {
        return DB::query("update %t set views=views+1,count=count+1 where id = %d", [$this->_table, $id]);
    }

    //增加下载次数
    public function add_downs_by_id($id) {
        return DB::query("update %t set downs=downs+1 where id=%d", [$this->_table, $id]);
    }
}