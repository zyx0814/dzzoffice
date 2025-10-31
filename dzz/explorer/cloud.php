<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
if (!$_G['uid']) {
    showmessage('no_privilege', dreferer());
}
$explorer_setting = get_resources_some_setting();
if (!$explorer_setting['cloudperm']) {
    showmessage('no_privilege', dreferer());
}
$bz = isset($_GET['bz']) ? trim($_GET['bz']) : '';
if ($bz && $bz !== 'dzz') {
    $path = isset($_GET['path']) ? trim($_GET['path']) : '';
    if (!$path) {
        $path = $bz;
    }
    if ($path) {
        $root = IO::getMeta($path);
        if ($root['error']) {
            showmessage($root['error'], dreferer());
        }
        $folderpatharr = getpath($root['relativepath']);
        $folderpathstr = implode('\\', $folderpatharr);
    }
    include template('cloud_content');
    exit();
} else {
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
                if ($value1['uid']) {
                    $user = getuserbyuid($value1['uid']);
                    if($user['uid']) {
                        $username =  $user['username'];
                    } else {
                        $username = '该用户已不存在！';
                    }
                } else {
                    $username = '系统盘';
                }
                $data[] = [
                    'id' => $value1['id'],
                    'cloudname' => '<img class="w-32 pe-2" src="dzz/images/default/system/'.$value['bz'].'.png" title="'.$value1['cloudname'].'">'.$value['name'],
                    'name' => $value1['cloudname'] ? $value1['cloudname'] : $value['name'],
                    'dateline' => dgmdate($value1['dateline'], 'Y-m-d H:i:s'),
                    'hashbz' => $cloudid,
                    'attachdir' => $value1['attachdir'],
                    'bz' =>  $value['bz'],
                    'username' => $username
                ];
            }
        }
    }
    $return = json_encode($data);
    if ($return === false) {
        showmessage('JSON 编码失败，请刷新重试', dreferer());
    }
    include template('cloud_list');
    exit();
}