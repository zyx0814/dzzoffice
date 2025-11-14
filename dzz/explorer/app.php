<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if ($do == 'updatesetting') {//更新设置
    $issave = false;
    $explorermyset = $_GET['explorermyset'];
    $setarr = array(
        'iconview' => intval($explorermyset['iconview']),
        'ondup' => intval($explorermyset['ondup']),
    );
    if (C::t('user_setting')->update_by_skey('explorermyset', serialize($setarr), $uid)) {
        $issave = true;
    } else {
        exit(json_encode(array('error' => true, 'msg' => lang('update_setting_failed'))));
    }
    if ($_G['adminid'] == 1) {
        include_once libfile('function/cache');
        $setting = $_GET['setting'];
        $setarr = array(
            'explorer_usermemoryOn' => (isset($setting['explorer_usermemoryOn']) && $setting['explorer_usermemoryOn'] == 'on') ? 1 : 0,
            'explorer_mermoryusersetting' => $setting['explorer_mermoryusersetting'],
            'explorer_mermorycloudsetting' => $setting['explorer_mermorycloudsetting'],
            'explorer_memoryorgusers' => $setting['explorer_memoryorgusers'],
            'explorer_organizationOn' => (isset($setting['explorer_organizationOn']) && $setting['explorer_organizationOn'] == 'on') ? 1 : 0,//isset($setting['organizationOn'])?$setting['organizationOn']:'',
            'explorer_groupOn' => (isset($setting['explorer_groupOn']) && $setting['explorer_groupOn'] == 'on') ? 1 : 0,//isset($setting['groupOn'])?$setting['groupOn']:'',
            'explorer_groupcreate' => (isset($setting['explorer_groupcreate']) && $setting['explorer_groupcreate'] == 'on') ? 1 : 0,
            'explorer_mermorygroupsetting' => $setting['explorer_mermorygroupsetting'],
            'explorer_mermoryonlymyorg' => $setting['explorer_mermoryonlymyorg'],
            'explorer_memorygroupusers' => $setting['explorer_memorygroupusers'],
            'explorer_memorycloudusers' => $setting['explorer_memorycloudusers'],
            'explorer_catcreate' => (isset($setting['explorer_catcreate']) && $setting['explorer_catcreate'] == 'on') ? 1 : 0,
            'explorer_finallydelete' => (isset($setting['explorer_finallydelete'])) ? intval($setting['explorer_finallydelete']) : -1,
            'explorer_limitConcurrentUploads' => intval($setting['explorer_limitConcurrentUploads']),
        );
        if (C::t('setting')->update_batch($setarr)) {
            updatecache('setting');
            $issave = true;
        } else {
            exit(json_encode(array('error' => true, 'msg' => lang('update_setting_failed'))));
        }
    }
    if ($issave) {
        exit(json_encode(array('success' => true, 'msg' => lang('update_setting_success'))));
    }
} else {
    $explorermyset = unserialize(C::t('user_setting')->fetch_by_skey('explorermyset', $uid));
    if ($_G['adminid'] == 1) {
        // 查询所有设置
        $setting = C::t('setting')->fetch_all([
            'explorer_usermemoryOn',
            'explorer_mermoryusersetting',
            'explorer_mermorycloudsetting',
            'explorer_memoryorgusers',
            'explorer_memorycloudusers',
            'explorer_memorySpace',
            'explorer_organizationOn',
            'explorer_groupOn',
            'explorer_groupcreate',
            'explorer_mermorygroupsetting',
            'explorer_mermoryonlymyorg',
            'explorer_memorygroupusers',
            'explorer_catcreate',
            'explorer_finallydelete',
            'explorer_limitConcurrentUploads'
        ]);

        /**
         * 处理用户/组织设置的通用函数
         * @param string $settingKey 设置项的键名（如 'explorer_memoryorgusers'）
         * @return array 包含解析后的组织、用户、展开状态
         */
        $processMemoryUsers = function($settingKey) use ($setting) {
            $memoryUsers = $setting[$settingKey] ?? '';
            $muids = $memoryUsers ? explode(',', $memoryUsers) : [];
            
            $orgids = [];
            $uids = [];
            foreach ($muids as $value) {
                if (strpos($value, 'uid_') !== false) {
                    $uids[] = str_replace('uid_', '', $value);
                } else {
                    $orgids[] = $value;
                }
            }

            $selOrg = [];
            $open = [];
            // 处理组织
            if ($orgids) {
                $selOrg = C::t('organization')->fetch_all($orgids);
                foreach ($selOrg as $key => $org) {
                    $orgPath = C::t('organization')->getPathByOrgid($org['orgid'], false);
                    $selOrg[$key]['orgpath'] = implode('-', $orgPath);
                    $arr = array_keys($orgPath);
                    array_pop($arr); // 移除最后一个元素
                    $count = count($arr);
                    if ($count) {
                        $lastKey = $arr[$count - 1];
                        // 保留最短路径（避免重复展开）
                        if (empty($open[$lastKey]) || count($open[$lastKey]) > $count) {
                            $open[$lastKey] = $arr;
                        }
                    }
                }
                // 处理"无组织用户"
                if (in_array('other', $orgids)) {
                    $selOrg[] = [
                        'orgname' => lang('no_org_user'),
                        'orgid' => 'other',
                        'forgid' => 1
                    ];
                }
            }

            // 处理用户及其所属组织的展开状态
            $selUser = [];
            if ($uids) {
                $selUser = C::t('user')->fetch_user_avatar_by_uids($uids);
                $aOrgIds = C::t('organization_user')->fetch_orgids_by_uid($uids);
                foreach ($aOrgIds as $orgid) {
                    $arr = C::t('organization')->fetch_parent_by_orgid($orgid, true);
                    $count = count($arr);
                    if ($count) {
                        $lastKey = $arr[$count - 1];
                        if (empty($open[$lastKey]) || count($open[$lastKey]) > $count) {
                            $open[$lastKey] = $arr;
                        }
                    }
                }
            }

            return [
                'selOrg' => $selOrg,
                'selUser' => $selUser,
                'open' => $open
            ];
        };

        $result1 = $processMemoryUsers('explorer_memoryorgusers');
        $result2 = $processMemoryUsers('explorer_memorygroupusers');
        $result3 = $processMemoryUsers('explorer_memorycloudusers');

        $openarr = json_encode([
            'orgids' => $result1['open'],
            'orgids1' => $result2['open'],
            'orgids2' => $result3['open']
        ]);
    }

    require template('app_manage');
}