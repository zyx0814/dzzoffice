<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class table_resources_tag extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'resources_tag';
        parent::__construct();
    }
		
    public function delete_by_rid($rid, $tid = '',$event=1)
    {
		
		global $_G;
       
       
		$tids = array();
		$deltagnames=array();
        if ($tid) {
           // return DB::delete($this->_table, 'rid in(' . dimplode($rid) . ') and tid = ' . $tid);
			$tids=array($tid);
        } else {
            foreach (DB::fetch_all("select tid from %t where rid = %s", array($this->_table, $rid)) as $v) {
                $tids[] = $v['tid'];
            }
        }
		if($tids){
			foreach(C::t('tag')->fetch_all($tids) as $tag){
				$deltagnames[]=$tag['tagname'];
			}
		}
		if($ret=DB::delete($this->_table, "rid ='{$rid}' and tid IN(".dimplode($tids).")")){
			 //减少使用数
			C::t('tag')->addhot_by_tid($tids, -1);
			if($event){
				//添加动态
				$uid = $_G['uid'];
				$username = $_G['username'];
				//查询文件信息
				if (!$fileinfo = DB::fetch_first("select * from %t where rid = %s", array('resources', $rid))) {
					return false;
				} else {
					$path = C::t('resources_path')->fetch_pathby_pfid($fileinfo['pfid']);
					$path = preg_replace('/dzz:(.+?):/', '', $path . $fileinfo['name']);
				}
				$eventdata = array('username' => $username, 'filename' => $fileinfo['name'], 'tagname' => implode(',', $deltagnames), 'position' => $path);
				C::t('resources_event')->addevent_by_pfid($fileinfo['pfid'], 'del_tags', 'deltag', $eventdata, $fileinfo['gid'], $rid, $fileinfo['name']);
			}
		}
        return true;
    }
	
	
    public function insert_data($rid, $tagnames,$isall=1,$idtype='explorer')
    {
        global $_G;
        $uid = $_G['uid'];
        $username = $_G['username'];
        //查询文件信息
        if (!$fileinfo = DB::fetch_first("select * from %t where rid = %s", array('resources', $rid))) {
            return false;
        } else {
            $path = C::t('resources_path')->fetch_pathby_pfid($fileinfo['pfid']);
            $path = preg_replace('/dzz:(.+?):/', '', $path . $fileinfo['name']);
        }
        //获取文件原有标签数据
        $return = DB::fetch_all("select rt.tid,t.tagname from %t rt left join %t t on rt.tid = t.tid where rt.rid = %s", array($this->_table, 'tag', $rid));

        $addtags = $deleted = $deltids = $nochangetids = $deltags = array();
        //获取标签数据
        $tags = C::t('tag')->insert_data($tagnames, $idtype);
        $tids = array();
        foreach ($tags as $v) {
            $tids[] = $v['tid'];
        }
        foreach ($return as $v) {
            if (!in_array($v['tid'], $tids)) {
                $deltids[] = $v['tid'];
                $deleted[] = array('tid' => $v['tid'], 'tagname' => $v['tagname']);
                $deltagnames[] = $v['tagname'];
            } else {
                $nochangetids[] = $v['tid'];
            }
        }
        //需要移除的标签
        if ($isall && count($deltids)) {
            //移除文件标签
            DB::query("delete from %t where rid = %s and tid in(%n)", array($this->_table, $rid, $deltids));
            //减少使用数
            C::t('tag')->addhot_by_tid($deltids, -1);
            //添加动态
            $eventdata = array('username' => $username, 'filename' => $fileinfo['name'], 'tagname' => implode(',', $deltagnames), 'position' => $path);
            C::t('resources_event')->addevent_by_pfid($fileinfo['pfid'], 'del_tags', 'deltag', $eventdata, $fileinfo['gid'], $rid, $fileinfo['name']);
        }
        //获取需要添加的标签
        $addtids = (count($nochangetids) > 0) ? array_diff($tids, $nochangetids) : $tids;
        if (count($addtids)) {
            $addtagnames = array();
            $insertsql = "insert into " . DB::table('resources_tag') . " (rid,tid,uid,username) values ";
            foreach ($addtids as $v) {
                $insertsql .= "(%s,%d,%d,%s),";
                $params[] = $rid;
                $params[] = $v;
                $params[] = $uid;
                $params[] = $username;
                $addtagnames[] = $tags[$v]['tagname'];
                $addtags[] = array('tid' => $v, 'tagname' => $tags[$v]['tagname']);
            }
            $insertsql = substr($insertsql, 0, -1);
            //添加文件标签
            DB::query($insertsql, $params);
            //增加标签使用数
            C::t('tag')->addhot_by_tid($addtids, 1);

            $addtagnames = implode(',', $addtagnames);
            $hash = C::t('resources_event')->get_showtpl_hash_by_gpfid($fileinfo['pfid'], $fileinfo['gid']);
            $eventdata = array('username' => $username, 'filename' => $fileinfo['name'], 'tagname' => $addtagnames, 'position' => $path, 'hash' => $hash);
            C::t('resources_event')->addevent_by_pfid($fileinfo['pfid'], 'add_tags', 'addtag', $eventdata, $fileinfo['gid'], $rid, $fileinfo['name']);
        }
        return array('success' => true, 'add' => $addtags, 'del' => $deleted);
    }

    public function fetch_tag_by_rid($rid)
    {
        $rid = trim($rid);
        $result = array();
        if ($result = DB::fetch_all("select rt.tid,t.tagname from %t rt left join %t t on rt.tid = t.tid where rt.rid = %s and t.idtype = %s", array($this->_table, 'tag', $rid, 'explorer'))) {
            return $result;
        }
        return $result;
    }

    public function fetch_rid_in_tid($tids)
    {
        if (!is_array($tids)) $tids = (array)$tids;
        $rids = array();
        foreach (DB::fetch_all("select rid from %t where tid in(%n)", array($this->_table, $tids)) as $v) {
            $rids[] = $v['rid'];
        }
        return $rids;
    }

    public function fetch_rid_by_tid($tids)
    {
        if (!is_array($tids)) $tids = (array)$tids;
        $arr = array();
        //获取标签对应的所有rid
        if ($rids = DB::fetch_all("select rid,tid from %t where tid in(%n)", array($this->_table, $tids))) {
            //遍历rid数组组成tid为键的数组
            $ridarr = array();
            foreach ($rids as $v) {
                $ridarr[$v['tid']][] = $v['rid'];
            }
            //如果rid数组和tid数组数量一致,即有符合条件rid(含有所有标签的rid)
            if (count($ridarr) == count($tids)) {
                $i = 0;
                //遍历rid数组取交集，得到最后结果
                foreach ($ridarr as $k => $val) {
                    if ($i == 0) {
                        $arr = $val;
                    } else {
                        $arr = array_intersect($arr, $val);
                    }
                    $i++;
                }
                return $arr;
            } else {
                return $arr;
            }
        } else {
            return $arr;

        }
    }

}