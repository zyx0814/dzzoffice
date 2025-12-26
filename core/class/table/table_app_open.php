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

class table_app_open extends dzz_table {
    public function __construct() {
        $this->_table = 'app_open';
        $this->_pk = 'extid';
        $this->_pre_cache_key = 'app_open_';
        $this->_cache_ttl = 60 * 60;
        parent::__construct();
    }

    public function setDefault($extid) {
        $data = self::fetch($extid);
        DB::update($this->_table, array('isdefault' => 0), "ext='{$data['ext']}'");
        $this->clear_cache('ext_all');
        return self::update($extid, array('isdefault' => 1));
    }

    public function setOrders($extid) {
        foreach ($extid as $k => $v) {
            $result = self::update($v, array('disp' => $k));
        }
        $this->clear_cache('ext_all');
        return true;
    }

    public function delete_by_appid($appid) {
        if (!$appid) return false;
        $query = DB::query("SELECT * FROM %t WHERE appid=%d ", array($this->_table, $appid));
        while ($value = DB::fetch($query)) {
            if ($value['extid']) {
                $result = C::t('app_open_default')->delete_by_extid($value['extid']);
            }
        }
        $this->clear_cache('ext_all');
        return DB::delete($this->_table, " appid='{$appid}'");
    }

    public function insert_by_exts($appid, $exts) {
        if (!$appid) return false;
        if (!is_array($exts)) $exts = $exts ? explode(',', $exts) : array();
        //删除原来的ext
        $oexts = array();
        $delids = array();
        $oextarr = DB::fetch_all("select * from " . DB::table('app_open') . " where appid='{$appid}'");
        foreach ($oextarr as $value) {
            $oexts[] = $value['ext'];
            if (!in_array($value['ext'], $exts)) $delids[] = $value['extid'];
        }
        if ($delids) {
            self::delete($delids);
        }
        foreach ($exts as $ext) {
            if ($ext && !in_array($ext, $oexts)) parent::insert(array('ext' => $ext, 'appid' => $appid));
        }
        $this->clear_cache('ext_all');
        return true;
    }

    public function fetch_all_ext() {
        global $_G;
        $ext_all = $this->fetch_cache('ext_all');
        if ($ext_all === false) {
            $ext_all = array();
            $app_cache = array();// 临时缓存app数据，避免重复调用
            $query = DB::query("SELECT * FROM %t WHERE 1 ", array($this->_table));
            while ($value = DB::fetch($query)) {
                if ($value['appid']) {
                    if (!isset($app_cache[$value['appid']])) {
                        $app_cache[$value['appid']] = C::t('app_market')->fetch_by_appid($value['appid'], false, true);
                    }
                    $app = $app_cache[$value['appid']];
                    if ($app) {
                        if ($app['available'] < 1) continue;
                        $value['icon'] = $app['appico'];
                        $value['name'] = $app['appname'];
                        $value['url'] = $app['appurl'];
                        $value['nodup'] = $app['nodup'];
                        $value['feature'] = $app['feature'];
                        $value['group'] = $app['group'];
                        $value['url'] = $app['url'];
                        $ext_all[] = $value;
                    }
                }
            }
            unset($app_cache);
            $this->store_cache('ext_all', $ext_all);
        }

        $data = array();
        foreach ($ext_all as $value) {
            if (!$_G['uid'] && $value['group'] > 0) continue;
            $data[$value['extid']] = $value;
        }

        return $data;
    }

    public function fetch_all_orderby_ext($uid, $ext_all = array(), $appids = array()) {
        $data = array();
        if (!$appids && $config = C::t('user_field')->fetch($uid)) {
            if ($config['applist']) {
                $appids = explode(',', $config['applist']);
            }
        }
        if (!$ext_all) $ext_all = self::fetch_all_ext();
        foreach ($ext_all as $value) {
            if ($uid == 0 || (!$value['appid'] || in_array($value['appid'], $appids))) {
                $data[$value['ext']][] = $value['extid'];
            }
        }
        return $data;
    }
}
?>
