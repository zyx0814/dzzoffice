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
$data = array();
$query = DB::query("SELECT * FROM " . DB::table('connect') . " WHERE available > 0");
while ($value = DB::fetch($query)) {
    $type = $value['type'];
    if (in_array($type, ['pan', 'storage', 'ftp', 'disk'])) {
        $baseWhere = "bz = '{$value['bz']}'";
        if (!$_G['adminid']) {
            $baseWhere .= " AND uid = '{$_G['uid']}'";
        }
        $subQuery = DB::fetch_all("SELECT * FROM " . DB::table($value['dname']) . " WHERE {$baseWhere}");
        foreach ($subQuery as $value1) {
            $cloudid = "{$value['bz']}:{$value1['id']}:";
            $data[] = [
                'cloudname' => $value1['cloudname'] ? $value1['cloudname'] : $value['name'],
                'img' => 'dzz/images/default/system/'.$value['bz'].'.png',
                'hashbz' => $cloudid,
            ];
        }
    }
}
include template("connect_index");
?>
