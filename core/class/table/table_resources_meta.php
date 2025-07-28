<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class table_resources_meta extends dzz_table {
    public function __construct() {

        $this->_table = 'resources_meta';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'resources_meta_';
        $this->_cache_ttl = 60 * 60;
        parent::__construct();
    }

    public function delete_by_id($id) {
        if (!$data = parent::fetch($id)) return false;
        if ($ret = parent::delete($id)) {
            $cachekey = $this->_pre_cache_key.'data_'.$data['rid'];
            $this->clear_cache($cachekey);
        }
        return $ret;
    }

    public function update($id, $setarr, $unbuffered = false, $low_priority = false) {
        if (!$data = parent::fetch($id)) return false;
        $setarr['editdateline'] = TIMESTAMP;
        $ret = parent::update($id, $setarr);
        if ($ret) {
            $cachekey = $this->_pre_cache_key.'data_'.$data['rid'];
            $this->clear_cache($cachekey);
        }
        return $ret;
    }

    public function insert($setarr, $return_insert_id = false, $replace = false, $silent = false) {
        if ($id = DB::result_first("select id from %t where rid=%s and `key`=%s", array($this->_table, $setarr['rid'], $setarr['key']))) {
            $ret = self::update($id, $setarr);
        } else {
            $setarr['dateline'] = TIMESTAMP;
            if ($id = parent::insert($setarr, 1)) {
                $cachekey = $this->_pre_cache_key.'data_'.$setarr['rid'];
                $this->clear_cache($cachekey);
            }
        }
        return $id;
    }

    public function fetch_by_rid($rid, $isval = false) {
        $cachekey = $this->_pre_cache_key.'data_'.$rid;
        if ($returndata = $this->fetch_cache($cachekey)) {
            return $isval ? $this->extract_values($returndata) : $returndata;
        }
        $returndata = array();
        foreach (DB::fetch_all("select * from %t where rid = %s", array($this->_table, $rid)) as $val) {
            $returndata[$val['key']] = array(
                'value' => $val['value'],
                'dateline' => $val['dateline'],
                'editdateline' => $val['editdateline']
            );
        }
        $this->store_cache($cachekey, $returndata);
        return $isval ? $this->extract_values($returndata) : $returndata;
    }

    private function extract_values($data) {
        return array_map(function($item) {
            return is_array($item) ? $item['value'] : $item;
        }, $data);
    }

    public function fetch_by_key($rid, $key,$isval = false) {
        $cachekey = $this->_pre_cache_key.'data_'.$rid.'_'.$key;
        if ($returndata = $this->fetch_cache($cachekey)) {
            if ($isval) {
                return $returndata['value'];
            }
            return $returndata;
        }
        $returndata = DB::fetch_first("SELECT * FROM %t WHERE rid = %s AND `key` = %s", array($this->_table, $rid, $key));
        if ($returndata) {
            $this->store_cache($cachekey, $returndata);
            if ($isval) {
                return $returndata['value'];
            }
        }
        return $returndata;
    }

    public function delete_by_rid($rid) {
        $i = 0;
        foreach (DB::fetch_all("select id from %t where rid=%s", array($this->_table, $rid)) as $value) {
            if (self::delete_by_id($value['id'])) {
                $i++;
            }
        }
        return $i;
    }

    public function delete_by_key($rid, $key) {
        $i = 0;
        foreach (DB::fetch_all("select id from %t where rid=%s and `key`=%d", array($this->_table, $rid, $key)) as $value) {
            if (self::delete_by_id($value['id'])) {
                $i++;
            }
        }
        return $i;
    }

    public function update_by_key($rid, $keyarr) {
        $i = 0;
        foreach ($keyarr as $k => $v) {
             $setarr = array(
                'rid' => $rid,
                'key' => $k,
                'value' => $v
            );
            if (self::insert($setarr)) {
                $i++;
            }
        }
        return $i;
    }
}