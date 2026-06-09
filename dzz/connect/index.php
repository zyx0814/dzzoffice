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
$data = [];
$query = DB::query("SELECT * FROM " . DB::table('connect') . " WHERE available > 0");
while ($value = DB::fetch($query)) {
    $type = $value['type'];
    if (in_array($type, ['pan', 'storage', 'ftp', 'disk'])) {
        if (!$_G['adminid']) {
            $subQuery = DB::fetch_all("SELECT * FROM %t WHERE bz=%s AND uid=%d", array($value['dname'], $value['bz'], $_G['uid']));
        } else {
            $subQuery = DB::fetch_all("SELECT * FROM %t WHERE bz=%s", array($value['dname'], $value['bz']));
        }
        foreach ($subQuery as $value1) {
            $cloudid = "{$value['bz']}:{$value1['id']}:";
            $data[] = [
                'cloudname' => $value1['cloudname'] ?: $value['name'],
                'img' => 'dzz/images/default/system/'.$value['bz'].'.png',
                'hashbz' => $cloudid,
            ];
        }
    }
}
include template("connect_index");

