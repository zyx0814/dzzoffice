<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
require_once libfile('function/code');

class table_resources_event extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'resources_event';
        $this->_pk = 'id';

        parent::__construct();
    }

    //添加群组动态
    public function addevent_by_pfid($pfid, $event, $do, $eventdata, $gid = '', $rid = '', $do_obj = '', $type = 0)
    {
        if (!$pfid) return false;
        $eventArr = array(
            'rid' => $rid,
            'event_body' => $event,
            'uid' => getglobal('uid'),
            'username' => getglobal('username'),
            'dateline' => time(),
            'body_data' => serialize($eventdata),
            'gid' => $gid,
            'pfid' => $pfid,
            'do' => $do,
            'do_obj' => $do_obj,
            'type' => $type
        );
        if ($insert = parent::insert($eventArr, 1)) {
            return $insert;
        } else {
            return false;
        }
    }

    public function delete_by_gid($gid)
    {
        DB::delete($this->table, array('gid' => $gid));
    }

    //删除文件夹动态，仅限于文件夹，其下文件动态不删除
    public function delete_by_pfid_and_notrid($fid)
    {
        return DB::delete($this->_table, array('rid' => '', 'pfid' => $fid));
    }

    //删除动态
    public function delete_by_rid($rid)
    {
        if (!is_array($rid)) $rid = (array)$rid;
        if (DB::delete($this->_table, 'rid in(' . dimplode($rid) . ')')) {
            return array('success' => lang('exploder_do_succeed'));
        }
        return array('error' => lang('exploder_do_failed'));

    }

    //更改动态归属位置信息(移动文件时使用)
    public function update_position_by_rid($rid, $pfid, $gid)
    {
        if (!is_array($rid)) $rid = (array)$rid;
        DB::update($this->_table, array('pfid' => $pfid, 'gid' => $gid), "rid IN(" . dimplode($rid) . ")");
        return true;
    }

    public function fetch_event_by_gid($gid)
    {
        $gid = intval($gid);
        $time = date('Y-m-d');
        $starttime = strtotime($time);
        $endtime = $starttime + 3600 * 24;
        $events = array();
        foreach (DB::fetch_all("select * from %t where gid = %d and dateline > %d and dateline < %d order by dateline desc", array($this->_table, $gid, $starttime, $endtime)) as $v) {
            $v['body_data'] = unserialize($v['body_data']);
            $v['body_data']['msg'] = self::emoji_decode($v['body_data']['msg']);
            $v['body_data']['msg'] = preg_replace_callback("/@\[(.+?):(.+?)\]/i", "atreplacement", $v['body_data']['msg']);
            $v['body_data']['msg'] = dzzcode($v['body_data']['msg']);
            $v['do_lang'] = lang($v['do']);
            $v['details'] = lang($v['event_body'], $v['body_data']);

            $events[] = $v;
        }
        return $events;
    }

    public function emoji_decode($str)
    {
        $text = json_encode($str); //暴露出unicode
        $text = preg_replace_callback('/\\\\\\\\/i', function ($str) {
            return '\\';
        }, $text); //将两条斜杠变成一条，其他不动
        return json_decode($text);
    }

    //根据fid查询评论
    public function fetch_comment_by_fid($fid, $count = false, $start = 0, $limit = 0)
    {
        $fid = intval($fid);
        $params = array($this->_table, $fid, 1);
        $limitsql = $limit ? DB::limit($start, $limit) : '';
        if ($count) {
            return DB::result_first("select count(*) from %t where pfid = %d and rid = '' and `type`= %d", $params);
        }
        $events = array();
        foreach (DB::fetch_all("select * from %t where pfid = %d and rid = '' and `type`= %d order by dateline desc $limitsql", $params) as $v) {
            $v['body_data'] = unserialize($v['body_data']);
            $v['body_data']['msg'] = self::emoji_decode($v['body_data']['msg']);
            $v['body_data']['msg'] = preg_replace_callback("/@\[(.+?):(.+?)\]/i", "atreplacement", $v['body_data']['msg']);
            $v['body_data']['msg'] = dzzcode($v['body_data']['msg']);
            $v['do_lang'] = lang($v['do']);
            $v['details'] = lang($v['event_body'], $v['body_data']);
            $v['fdate'] = dgmdate($v['dateline'], 'u');
            $uids[] = $v['uid'];
            $events[] = $v;
        }
        if (count($events)) {
            $events = self::result_events_has_avatarstatusinfo($uids, $events);
        }


        return $events;
    }

    //根据fid查询评论
    public function fetch_comment_by_rid($rid, $count = false, $start = 0, $limit = 0)
    {
        $rid = trim($rid);
        $params = array($this->_table, $rid, 1);
        $limitsql = $limit ? DB::limit($start, $limit) : '';
        if ($count) {
            return DB::result_first("select count(*) from %t where rid = %s and `type`= %d", $params);
        }
        $uid = array();
        $events = array();
        foreach (DB::fetch_all("select * from %t where rid = %s and `type`= %d order by dateline desc $limitsql", $params) as $v) {
            $v['body_data'] = unserialize($v['body_data']);
            $v['body_data']['msg'] = self::emoji_decode($v['body_data']['msg']);
            $v['body_data']['msg'] = preg_replace_callback("/@\[(.+?):(.+?)\]/i", "atreplacement", $v['body_data']['msg']);
            $v['body_data']['msg'] = dzzcode($v['body_data']['msg']);
            $v['do_lang'] = lang($v['do']);
            $v['details'] = lang($v['event_body'], $v['body_data']);
            $v['fdate'] = dgmdate($v['dateline'], 'u');
            $uids[] = $v['uid'];
            $events[] = $v;
        }
        if (count($events)) {
            $events = self::result_events_has_avatarstatusinfo($uids, $events);
        }

        return $events;
    }

    //根据rid查询动态
    public function fetch_by_rid($rids, $start = 0, $limit = 0, $count = false, $type = false)
    {
        if (!is_array($rids)) $rids = (array)$rids;
        $fids = array();
        foreach (DB::fetch_all("select * from %t where rid in(%n)", array('resources', $rids)) as $v) {
            if ($v['type'] == 'folder') {
                $fids[] = $v['oid'];
            }
        }
        $wheresql = " where rid in(%n) ";
        $params = array($this->_table, $rids);
        if (count($fids) > 0) {
            $wheresql .= " or (pfid in(%n))";
            $params[] = $fids;
        }
        if ($type) {
            $type = $type - 1;
            $wheresql .= ' and `type` = ' . $type;
        }
        if ($count) {
            return DB::result_first("select count(*) from %t $wheresql", $params);
        }
        $limitsql = $limit ? DB::limit($start, $limit) : '';
        $events = array();
        $uids = array();
        foreach (DB::fetch_all("select * from %t $wheresql order by dateline desc $limitsql", $params) as $v) {
            $v['body_data'] = unserialize($v['body_data']);
            $v['body_data']['msg'] = self::emoji_decode($v['body_data']['msg']);
            $v['body_data']['msg'] = preg_replace_callback("/@\[(.+?):(.+?)\]/i", "atreplacement", $v['body_data']['msg']);
            $v['body_data']['msg'] = dzzcode($v['body_data']['msg']);
            $v['do_lang'] = lang($v['do']);
            $v['details'] = lang($v['event_body'], $v['body_data']);
            $v['fdate'] = dgmdate($v['dateline'], 'u');
            $uids[] = $v['uid'];
            $events[] = $v;
        }
        $events = self::result_events_has_avatarstatusinfo($uids, $events);

        return $events;
    }

    //根据文件夹id查询动态
    public function fetch_by_pfid_rid($fid, $counts = false, $start = 0, $limit = 0, $rid = '', $type = false)
    {
        //查询文件夹所有下级
        $fids = C::t('resources_path')->get_child_fids($fid);

        if ($type) {
            $type = $type - 1;
            $wheresql = " where (pfid in(%n) and `type` = " . $type . ")";
        } else {
            $wheresql = " where (pfid in(%n) and `type` = 0)";
        }
        $wheresql = " where (pfid in(%n) and `type` = 0)";
        $params = array($this->_table, $fids);

        if ($rid) {
            if ($type) {
                $type = $type - 1;
                $wheresql .= " or ((rid = %s and  `type` = 1) or `type` = 0)";
            } else {
                $wheresql .= " or ((rid = %s and  `type` = 1) or `type` = 0)";
            }

            $params[] = $rid;
        } else {
            if ($type) {
                $type = $type - 1;
                $wheresql .= " or ((rid = %s and  `type` = 1) or `type` = 0)";
            } else {
                $wheresql .= " or (pfid = %d and `type` = 1 and rid = '')";
            }
            $params[] = $fid;
        }
        if ($counts) {
            return DB::result_first("select count(*) from %t $wheresql", $params);
        }
        $limitsql = $limit ? DB::limit($start, $limit) : '';
        $events = array();
        $uids = array();
        include_once libfile('function/use');
        foreach (DB::fetch_all("select * from %t $wheresql order by dateline desc $limitsql", $params) as $v) {
            $v['body_data'] = unserialize($v['body_data']);
            $v['body_data']['msg'] = preg_replace_callback("/@\[(.+?):(.+?)\]/i", "atreplacement", $v['body_data']['msg']);
            $v['body_data']['msg'] = dzzcode($v['body_data']['msg']);
            $at_users = array();
            $v['do_lang'] = lang($v['do']);
            $v['details'] = lang($v['event_body'], $v['body_data']);
            $v['fdate'] = dgmdate($v['dateline'], 'u');
            $uids[] = $v['uid'];
            $events[] = $v;
        }
        $events = self::result_events_has_avatarstatusinfo($uids, $events);
        return $events;
    }

    public function result_events_has_avatarstatusinfo($uids, $events)
    {
        $uids = array_unique($uids);
        $avatars = array();
        foreach (DB::fetch_all("select u.avatarstatus,u.uid,s.svalue from %t u left join %t s on u.uid=s.uid and s.skey=%s where u.uid in(%n)", array('user', 'user_setting', 'headerColor', $uids)) as $v) {
            if ($v['avatarstatus'] == 1) {
                $avatars[$v['uid']]['avatarstatus'] = 1;
            } else {
                $avatars[$v['uid']]['avatarstatus'] = 0;
                $avatars[$v['uid']]['headerColor'] = $v['svalue'];
            }
        }
        $fevents = array();
        foreach ($events as $v) {
            $v['avatarstatus'] = $avatars[$v['uid']]['avatarstatus'];
            if (!$avatars[$v['uid']]['avatarstatus'] && isset($avatars[$v['uid']]['headerColor'])) {
                $v['headerColor'] = $avatars[$v['uid']]['headerColor'];
            }
            $fevents[] = $v;
        }
        return $fevents;
    }

    //查询该文件最近的动态
    public function fetch_by_ridlast($rid)
    {
        $event = array();
        $result = DB::fetch_first("select * from %t where rid = %s and `type` = %d", array($this->_table, $rid, 0));
        $body_data = unserialize($result['body_data']);
        $body_data['msg'] = dzzcode($body_data['msg']);
        $event = array(
            'details' => lang($result['event_body'], $body_data),
            'fdate' => dgmdate($result['dateline'], 'u'),
        );
        return $event;
    }

    //查询当前用户所有动态
    public function fetch_all_event($start = 0, $limit = 0, $condition = array(), $ordersql = '', $count = false)
    {
        $limitsql = $limit ? DB::limit($start, $limit) : '';
        $wheresql = ' 1 ';
        $uid = getglobal('uid');
        $params = array($this->_table, 'folder');
        $explorer_setting = get_resources_some_setting();//获取系统设置
        $powerarr = perm_binPerm::getPowerArr();

        //用户条件
        $usercondition = array();
        //如果筛选条件没有用户限制，默认查询当前用户网盘数据
        if (!isset($condition['uidval'])) {
            //用户自己的文件
            if ($explorer_setting['useronperm']) {//判断当前用户存储是否开启，如果开启则查询当前用户网盘数据
                $usercondition ['nogid'] = " e.gid=0 and e.uid=%d ";
                $params[] = $uid;
            }
        } else {
            $uids = $condition['uidval'][0];
            if (in_array($uid, $uids)) {
                if ($explorer_setting['useronperm']) {//判断当前用户存储是否开启，如果开启则查询当前用户网盘数据
                    $usercondition ['nogid'] = " e.gid=0 and e.uid=%d ";
                    $params[] = $uid;
                }
            }
            if (count($uids) > 0) {//群组用户限制
                $usercondition ['hasgid'] = " (e.uid in(%n)) ";
            }
        }

        if (isset($usercondition['nogid'])) $wheresql .= 'and (' . $usercondition ['nogid'] . ')';


        //群组条件后需判断有无用户条件
        $orgcondition = array();
        $orgids = C::t('organization')->fetch_all_orgid();//获取所有有管理权限的部门，并排除已关闭的群组或机构
        //我管理的群组或部门
        if ($orgids['orgids_admin']) {

            $orgcondition[] = "  e.gid IN (%n) ";

            $params[] = $orgids['orgids_admin'];
        }
        //我参与的群组
        if ($orgids['orgids_member']) {
            $orgcondition[] = "  (e.gid IN(%n) and ((f.perm_inherit & %d) OR (e.uid=%d and f.perm_inherit & %d))) ";
            $params[] = $orgids['orgids_member'];
            $params[] = $powerarr['read2'];
            $params[] = $uid;
            $params[] = $powerarr['read1'];
        }
        if ($orgcondition) {//如果有群组条件
            $or = isset($usercondition ['nogid']) ? 'or' : 'and';//判断是否有网盘数据
            if ($usercondition ['hasgid']) {//如果有网盘数据，则与群组条件组合为或的关系
                $wheresql .= " $or ((" . implode(' OR ', $orgcondition) . ") and " . $usercondition ['hasgid'] . ") ";
                $params[] = $uids;
            } else {
                $wheresql .= " $or (" . implode(' OR ', $orgcondition) . ") ";
            }
            $wheresql = '(' . $wheresql . ')';
        } else {
            if (!isset($usercondition ['nogid'])) {
                $wheresql .= ' and 0 ';
            }
        }

        //解析搜索条件
        if ($condition && is_string($condition)) {//字符串条件语句
            $wheresql .= $condition;
        } elseif (is_array($condition)) {
            foreach ($condition as $k => $v) {
                if (!is_array($v)) {
                    $connect = 'and';
                    $wheresql .= $connect . ' e.' . $k . " = '" . $v . "' ";
                } else {
                    $relative = isset($v[1]) ? $v[1] : '=';
                    $connect = isset($v[2]) ? $v[2] : 'and';
                    if ($relative == 'in') {
                        $wheresql .= $connect . "  e." . $k . " " . $relative . " (" . $v[0] . ") ";
                    } elseif ($relative == 'nowhere') {
                        continue;
                    } elseif ($relative == 'stringsql') {
                        $wheresql .= $connect . " " . $v[0] . " ";
                    } elseif ($relative == 'like') {
                        $wheresql .= $connect . " e." . $k . " like %s ";
                        $params[] = '%' . $v[0] . '%';
                    } else {
                        $wheresql .= $connect . ' e.' . $k . ' ' . $relative . ' ' . $v[0] . ' ';
                    }

                }
            }
        }
        if ($count) {
            return DB::result_first("select count(*) from %t e left join %t f on e.pfid = f.fid where  $wheresql  $ordersql", $params);
        }
        $uids = array();
        $events = array();
        foreach (DB::fetch_all("select e.* from %t e left join %t f on e.pfid = f.fid where  $wheresql  $ordersql  $limitsql", $params) as $v) {
            $v['body_data'] = unserialize($v['body_data']);
            $v['body_data']['msg'] = dzzcode($v['body_data']['msg']);
            $v['do_lang'] = lang($v['do']);
            $v['details'] = lang($v['event_body'], $v['body_data']);
            $v['fdate'] = dgmdate($v['dateline'], 'u');
            $uids[] = $v['uid'];
            $events[] = $v;
        }
        $events = self::result_events_has_avatarstatusinfo($uids, $events);
        return $events;

    }

    //删除评论
    public function delete_comment_by_id($id)
    {
        $id = intval($id);
        $uid = getglobal('uid');
        if (!$comment = parent::fetch($id)) {
            return array('error' => lang('comment_not_exists'));
        }
        //检测删除权限
        $pfid = $comment['pfid'];
        if ($folder = C::t('folder')->fetch($pfid)) {
            if(($uid != $comment['uid']) && !perm_check::checkperm_Container($folder['fid'], 'delete2') && !($uid == $folder['uid'] && perm_check::checkperm_Container($folder['fid'], 'delete1'))) {
                return array('error' => lang('no_privilege'));
            }
        }
        if (parent::delete($id)) {
            return array('success' => true);
        } else {
            return array('error' => lang('delete_error'));
        }

    }

    /*
     * #group&do=file&gid=1&fid=13
     * #group&gid=1
     * #home&fid=1
     * #home&do=file&fid=11
     * */
    public function get_showtpl_hash_by_gpfid($pfid, $gid = 0)
    {
        $hash = '';
        //判断是否是群组内操作
        if ($gid > 0) {
            $gfid = DB::result_first("select fid from %t where orgid = %d", array('organization', $gid));
            //判断是否是群组跟目录
            if ($pfid == $gfid) {
                //$hash=MOD_URL.'#group&gid='.$gid;
                $hash = '#group&gid=' . $gid;
            } else {
                //$hash=MOD_URL.'#group&do=file&gid='.$gid.'&fid='.$pfid;
                $hash = '#group&do=file&gid=' . $gid . '&fid=' . $pfid;
            }
        } else {
            $hfid = DB::result_first("select pfid from %t where fid = %d", array('folder', $pfid));
            //判断是否是个人根目录
            if ($hfid == 0) {
                //$hash=getglobal('siteurl').MOD_URL.'#home&fid='.$pfid;
                $hash = '#home&fid=' . $pfid;
            } else {
                //$hash=getglobal('siteurl').MOD_URL.'#home&do=file&fid='.$pfid;
                $hash = '#home&do=file&fid=' . $pfid;
            }
        }
        return $hash;
    }

    public function update_event_by_pfid($pfid, $opfid)
    {
        DB::update($this->_table, array('pfid' => $opfid), array('pfid' => $opfid));
    }

}