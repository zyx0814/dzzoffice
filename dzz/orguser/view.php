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
include_once libfile('function/organization');
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
$orgid = intval($_GET['orgid']);
$response = [
    "code" => 0,
    "msg" => "",
    "count" => 0,
    "data" => [],
    "orgname" => lang('no_institution_users'),
    "orgid" => $orgid,
    "adminid" => $_G['adminid'],
];
/**
 * 检查用户在线状态
 * @param int $uid 用户ID
 * @return bool 是否在线
 */
function isUserOnline($uid) {
    global $_G;
    return isset($_G['ols'][$uid]);
}
/**
 * 生成部门路径HTML
 * @param int $uid 用户ID
 * @return string 部门路径HTML
 */
function generateDepartmentPath($uid) {
    $department = [];
    foreach (C::t('organization_user')->fetch_orgids_by_uid($uid) as $orgId) {
        if ($org = C::t('organization')->fetch($orgId)) {
            $pathIds = explode('-', str_replace('_', '', $org['pathkey']));
            $organizations = C::t('organization')->fetch_all($pathIds);
            $pathNames = [];
            foreach ($pathIds as $pathId) {
                if (!empty($organizations[$pathId])) {
                    $pathNames[] = $organizations[$pathId]['orgname'];
                }
            }
            $department[] = '<a href="javascript:;" class="" onclick="showDetail(\''. $pathId .'\')">' . implode('-', $pathNames). '</a>';
        }
    }
    return implode(',', $department) ?: lang('no_institution_users');
}
/**
 * 生成用户列表项
 * @param array $user 用户数据
 * @param string $department 部门信息
 * @return array 用户列表项
 */
function generateUserItem($user, $department = '') {
    return [
        "uid" => $user['uid'],
        "avatar" => avatar_block($user['uid']),
        "username" => $user['username'],
        "email" => $user['email'],
        "status" => $user['status'],
        "groupid" => $user['groupid'],
        "online" => isUserOnline($user['uid']),
        "department" => $department ?: generateDepartmentPath($user['uid']),
    ];
}
/**
 * 获取原始用户数据（按场景区分）
 * @param int $orgid 组织ID
 * @param bool $issearch 是否搜索模式
 * @param string $limitsql 分页SQL
 * @return array [用户数据, 总数, 组织信息]
 */
function getRawUserData($orgid, $issearch, $limitsql) {
    global $_G;
    $userData = [];
    $count = 0;
    $org = [];
    $order = isset($_GET['order']) ? $_GET['order'] : 'asc';
    $field = isset($_GET['sort']) ? $_GET['sort'] : 'uid';
    $validFields = ['username', 'uid', 'email', 'status', 'groupid'];
    $validSortOrders = ['asc', 'desc'];
    if (in_array($field, $validFields) && in_array(strtolower($order), $validSortOrders)) {
        $sortField = $field;
        $sortOrder = strtoupper($order);
    } else {
        $sortField = 'uid';
        $sortOrder = 'asc';
    }
    $sortSql = "ORDER BY $sortField $sortOrder";
    
    if ($issearch) {
        // 搜索模式处理
        $where = '1';
        $params = [];
        $username = isset($_GET['username']) ? trim($_GET['username']) : '';
        $uid = isset($_GET['uid']) ? intval($_GET['uid']) : '';
        $status = isset($_GET['status']) ? intval($_GET['status']) : '';
        $groupid = isset($_GET['groupid']) ? intval($_GET['groupid']) : '';
        $email = isset($_GET['email']) ? trim($_GET['email']) : '';
        
        if ($username) {
            $where .= ' and username LIKE %s';
            $params[] = "%{$username}%";
        }
        if ($uid) {
            $where .= ' and uid = %d';
            $params[] = $uid;
        }
        if ($status) {
            $where .= ' and status = %d';
            $params[] = $status;
        }
        if ($groupid) {
            $where .= ' and groupid = %d';
            $params[] = $groupid;
        }
        if ($email) {
            $where .= ' and email LIKE %s';
            $params[] = "%{$email}%";
        }
        $count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('user') . " WHERE $where",$params);
        if ($count) {
            $userData = DB::fetch_all("SELECT username, uid, email, groupid, `status` FROM " . DB::table('user') . " WHERE $where $sortSql limit $limitsql", $params);
        }
        $org['orgname'] = '搜索结果';
        $orgid = 0;
    } else {
        // 组织列表模式处理
        if ($orgid) {
            // 检查权限
            if (!C::t('organization_admin')->ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
                return ['error' => lang('orguser_vidw_delete')];
            }
            
            $count = C::t('organization_user')->fetch_user_by_orgid($orgid, $limitsql, true);
            if ($count) {
                $userData = C::t('organization_user')->fetch_user_by_orgid($orgid, $limitsql, false, $sortSql);
            }
            $org = C::t('organization')->fetch($orgid) ?: [];
        } else {
            // 无组织用户
            $data = C::t('organization_user')->fetch_user_not_in_orgid($limitsql, true, $sortSql);
            $count = $data['count'] ?? 0;
            $userData = $data['list'] ?? [];
        }
    }

    return [
        'userData' => $userData,
        'count' => $count,
        'org' => $org,
        'orgid' => $orgid
    ];
}
if($do == 'userlist') {
    if (!$orgid && $_G['adminid'] != 1) {
        //获取用户的有权限的部门树
        $orgids = C::t('organization_admin')->fetch_orgids_by_uid($_G['uid']);
        if (!$orgids) {
            $response = [
                "code" => 1,
                "msg" => lang('no_parallelism_jurisdiction'),
                "count" => 0,
                "data" => [],
                "orgid" => 0,
                "orgname" => '',
            ];
            exit(json_encode($response));
        }
        $orgid = reset($orgids);
    }
    $limit = empty($_GET['limit']) ? 20 : intval($_GET['limit']);
    $page = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
    $start = ($page - 1) * $limit;
    $limitsql = "$start,$limit";
    $issearch = isset($_GET['issearch']) ? intval($_GET['issearch']) : 0;
    
    $rawData = getRawUserData($orgid, $issearch, $limitsql);
    if (isset($rawData['error'])) {
        $response['code'] = 1;
        $response['msg'] = $rawData['error'];
        exit(json_encode($response));
    }

    //批量查询在线状态
    $uids = array_column($rawData['userData'], 'uid');
    if (!empty($uids)) {
        getonlinemember($uids);
    }
    //生成最终用户列表
    $list = [];
    $defaultDepartment = ($rawData['orgid'] == 0 && !$issearch) ? lang('no_institution_users') : '';
    foreach ($rawData['userData'] as $user) {
        if (empty($user['uid'])) continue;
        $list[] = generateUserItem($user, $defaultDepartment);
    }
    //构建响应
    $response['count'] = $rawData['count'];
    $response['data'] = $list;
    $response['orgname'] = $rawData['org']['orgname'] ?? lang('no_institution_users');
    $response['orgid'] = $rawData['orgid'];
    header('Content-Type: application/json');
    $jsonReturn = json_encode($response);
    if ($jsonReturn === false) {
        $errorMessage = json_last_error_msg();
        $errorResponse = [
            "code" => 1,
            "msg" => "JSON 编码失败，请刷新重试: " . $errorMessage,
            "count" => 0,
            "data" => [],
        ];
        exit(json_encode($errorResponse));
    }
    exit($jsonReturn);
} elseif($do == 'orgedit') {
    if (!C::t('organization_admin')->ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
        showmessage('orguser_vidw_delete');
    }
    $org = C::t('organization')->fetch($orgid) ?: [];
    if ($org) {
        $org['avatar'] = avatar_group($org['orgid'], [$org['orgid'] => [
            'aid' => $org['aid'], 
            'orgname' => $org['orgname']
        ]]);
    }
    
    // 处理上级组织信息
    if ($org && $org['forgid'] > 0) {
        $toporgid = C::t('organization')->getTopOrgid($orgid);
        $toporg = C::t('organization')->fetch($toporgid);
        $folder_available = $toporg['available'];
        $group_on = $toporg['syatemon'];
    } else {
        $folder_available = 1;
        $group_on = 1;
    }
    //可分配空间
    $allowallotspace = C::t('organization')->get_allowallotspacesize_by_orgid($orgid);
    //获取已使用空间
    $org['usesize'] = C::t('organization')->get_orgallotspace_by_orgid($orgid, 0, false);
    
    // 处理最大空间
    if ($org['maxspacesize'] == 0) {
        $maxspacesize = C::t('organization')->get_parent_maxspacesize_by_pathkey($org['pathkey'], $orgid);
        $org['maxallotspacesize'] = $maxspacesize['maxspacesize'];
    } else {
        $org['maxallotspacesize'] = $org['maxspacesize'] == -1 ? -1 : $org['maxspacesize'] * 1024 * 1024;
    }
    
    // 处理管理员信息
    $pmoderator = C::t('organization_admin')->ismoderator_by_uid_orgid($org['forgid'], $_G['uid']);
    $jobs = C::t('organization_job')->fetch_all_by_orgid($orgid);
    $moderators = C::t('organization_admin')->fetch_moderators_by_orgid($orgid);
    $open = $sel = $uids = [];
    if ($moderators) {
        $uids = array_column($moderators, 'uid');
        $sel_user = C::t('user')->fetch_all($uids);
        $sel = array_map(function($user) {
            return 'uid_' . $user['uid'];
        }, $sel_user);
        
        if ($aorgids = C::t('organization_user')->fetch_orgids_by_uid($uids)) {
            foreach ($aorgids as $id) {
                $arr = C::t('organization')->fetch_parent_by_orgid($id, true);
                $count = count($arr);
                $lastOrgId = $arr[$count - 1];
                
                if (empty($open[$lastOrgId]) || count($open[$lastOrgId]) > $count) {
                    $open[$lastOrgId] = $arr;
                }
            }
        }
    }

    $sel = implode(',', $sel);
    $openarr = json_encode(['orgids' => $open]);
    //$grouppic= C::t('resources_grouppic')->fetch_user_pic();
    include template('detail_org');
    exit();
} else {
    if ($orgid) {
        $org = C::t('organization')->fetch($orgid) ?: [];
        $org['orgname'] ?? lang('no_institution_users');
    }
    include template('user');
}
?>