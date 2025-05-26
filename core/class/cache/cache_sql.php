<?php

if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class ultrax_cache {

    function __construct($conf) {
        $this->conf = $conf;
    }

    function get_cache($key) {
        static $data = array();
        if (!isset($data[$key])) {
            $cache = C::t('cache')->fetch($key);
            if (!$cache) {
                return false;
            }
            $data[$key] = dunserialize($cache['cachevalue']);
            if ($cache['life'] && ($cache['dateline'] < time() - $data[$key]['life'])) {
                return false;
            }
        }
        return $data[$key]['data'];
    }

    function set_cache($key, $value, $life) {
        $data = array(
            'cachekey' => $key,
            'cachevalue' => serialize(array('data' => $value, 'life' => $life)),
            'dateline' => time(),
        );
        return C::t('cache')->insert($data);
    }

    function del_cache($key) {
        return C::t('cache')->delete($key);
    }
}

?>