<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

class perm_check {
    /**
     * 获取用户基础权限（个人权限与用户组权限的合并处理）
     * 逻辑：优先读取用户个人权限，若无则使用用户组权限；若开启分享，移除分享权限
     * @return int 权限值（十进制整数，通过位运算表示多种权限）
     */
    public static function getuserPerm() {
        global $_G;
        static $userPermCache = [];
        $cacheKey = $_G['uid'];
        if (isset($userPermCache[$cacheKey])) {
            return $userPermCache[$cacheKey];
        }
        $perm = DB::result_first("select perm from %t where uid=%d", array('user_field', $_G['uid']));
        // 个人权限无效时，使用用户组权限
        if ($perm < 1) $perm = intval($_G['group']['perm']);
        // 若系统允许分享，移除当前用户的分享权限
        if ($_G['setting']['allowshare']) {
            $power = new perm_binPerm($perm);
            $perm = $power->delPower('share');
        }
        $userPermCache[$cacheKey] = $perm;
        return $perm;
    }

    /**
     * 获取文件夹权限
     * @param int $fid 文件夹ID
     * @param string $bz 标识（用于第三方挂载场景）
     * @param int $i 递归次数计数器（防死循环）
     * @return int 权限值（0表示无权限，其他值通过位运算表示具体权限）
     */
    public static function getPerm($fid, $bz = '', $i = 0) {
        global $_G;
        // 未登录用户无权限
        if (!$_G['uid']) return 0;
        // 超级管理员直接拥有全部权限
        if ($_G['adminid'] == 1) return perm_binPerm::getGroupPower('all');
        if (isset($_G['gperm'])) return intval($_G['gperm']);//可以通过这个参数直接使用此权限值不去查询权限

        $i++;
        if ($i > 20) { //防死循环，如果循环20次以上，直接退出；
            return perm_binPerm::getGroupPower('read');
        }

        //查不到文件夹信息时，返回默认只读权限
        $folder = C::t('folder')->fetch_folderinfo_by_fid($fid);
        if (!$folder) {
            return perm_binPerm::getGroupPower('read');
        }
        $perm = intval($folder['perm']);
        $power = new perm_binPerm($perm);
        //机构/部门/群组文件夹
        if ($folder['gid']) {
            // 机构管理员拥有全部权限
            if (self::checkgroupPerm($folder['gid'], 'admin')) {
                return perm_binPerm::getGroupPower('all');
            }
            // 非成员：无权限
            if (!self::checkgroupPerm($folder['gid'])) {
                return 0;
            }
            // 权限不继承上级（flag标识）：合并用户基础权限
            if ($power->isPower('flag')) {
                return $power->mergePower(self::getuserPerm());//$power1->power;
            }
            // 权限继承上级：递归查上级文件夹权限，合并用户基础权限
            if ($folder['pfid'] > 0 && $folder['pfid'] != $folder['fid']) { //有上级目录
                $perm = self::getPerm($folder['pfid'], $bz, $i);
                $power1 = new perm_binPerm($perm);
                return $power1->mergePower(self::getuserPerm());//$power1->power;
            }
            // 无上级/异常场景，返回默认只读权限
            return perm_binPerm::getGroupPower('read');
        }
        //判断是否是自己的网盘（路径归属校验）
        $isOwnDisk = preg_match('/^dzz:uid_(\d+):/', $folder['path'], $matches) && $matches[1] == $_G['uid'];
        if (!$isOwnDisk) {
            return 0; // 非本人个人网盘，无权限
        }
        // 检查用户组权限是否对个人网盘生效：
        // - 若my_disk权限开启，使用用户/组权限；
        // - 否则默认拥有全部权限
        $my_disk = (intval($_G['group']['perm']) & perm_binPerm::getPowerArr()['my_disk']) ? true : false;
        if ($my_disk) {
            return self::getuserPerm();
        } else { 
            return perm_binPerm::getGroupPower('all');
        }
    }

    public static function getPerm1($fid, $bz = '', $i = 0, $newperm = 0) {
        global $_G;

        $i++;
        if ($i > 20) { //防死循环，如果循环20次以上，直接退出；
            return perm_binPerm::getGroupPower('all');
        }
        $folder = C::t('folder')->fetch($fid);
        if (!$folder) {
            return perm_binPerm::getGroupPower('read');
        }
        $perm = ($newperm) ? intval($newperm) : intval($folder['perm']);
        if ($folder['gid']) {
            $power = new perm_binPerm($perm);
            if ($power->isPower('flag')) {//不继承，使用此权限
                return $perm;
            } else { //继承上级，查找上级
                if ($folder['pfid'] > 0 && $folder['pfid'] != $folder['fid']) { //有上级目录
                    return self::getPerm1($folder['pfid'], $bz, $i, $newperm);
                } else {   //其他的情况使用
                    return perm_binPerm::getGroupPower('read');
                }
            }
        } else {
            $power = new perm_binPerm($perm);
            if ($power->isPower('flag')) {//不继承，使用此权限
                return $power->mergePower(self::getuserPerm());
            } else { //继承上级，查找上级
                if ($folder['pfid'] > 0 && $folder['pfid'] != $folder['fid']) { //有上级目录
                    return self::getPerm1($folder['pfid'], $bz, $i);
                } else {   //其他的情况使用
                    return self::getuserPerm();
                }
            }
        }
    }

    /**
     * 获取文件权限
     * @param int $arr 文件信息
     * @return int 权限值（0表示无权限，其他值通过位运算表示具体权限）
     */
    public static function getridPerm($arr) {
        global $_G;
        // 未登录用户无权限
        if (!$_G['uid']) return 0;
        // 超级管理员直接拥有全部权限
        if ($_G['adminid'] == 1) return perm_binPerm::getGroupPower('all');
        //机构/部门/群组文件夹
        if ($arr['gid']) {
            if (self::checkgroupPerm($arr['gid'], 'admin')) return perm_binPerm::getGroupPower('all');// 机构管理员拥有全部权限
            if (!self::checkgroupPerm($arr['gid'])) return 0;// 非成员：无权限
        }
        // 文件权限优先：若设置了sperm，直接以文件权限为基础
        if (!empty($arr['sperm'])) {
            $power = new perm_binPerm($arr['sperm']);
            // 合并用户组权限（最终限制）
            return $power->mergePower(self::getuserPerm());
        }
        // 无文件权限：继承所在目录的权限
        if ($arr['fid']) {
            return self::getPerm($arr['fid']);
        } elseif ($arr['pfid']) {
            return self::getPerm($arr['pfid']);
        }
        return 0;
    }

    /**
     * 判断容器（文件夹）是否有指定操作权限（兼容个人/机构场景）
     * @param int $fid 容器ID
     * @param string $action 操作类型（如'read1'、'edit2'等，需与perm_binPerm中的权限键对应）
     * @return bool 是否有权限
     */
    public static function containerPerm($fid, $action) {
        $perm = self::getPerm($fid);
        if ($perm > 0) {
            return perm_binPerm::havePower($action, $perm);
        }
        return false;
    }

    /**
     * 检查用户在用户组中的权限
     * @param string $action 操作类型
     * @return bool 是否有权限
     */
    public static function checkuserperm($action) {
        global $_G;
        if (empty($_G['uid'])) {
            return false;
        }
        if ($_G['adminid'] == 1) return true;
        $perm = intval($_G['group']['perm']);
        if ($perm > 0) {
            $power = new perm_binPerm($perm);
            return $power->isPower($action);
        } else {
            return false;
        }
    }

    /**
     * 检查用户在机构中的权限（管理员/成员/非成员）
     * @param int $gid 机构ID
     * @param string $action 操作类型（'admin'表示需要管理员权限）
     * @return bool 是否有权限
     */
    public static function checkgroupPerm($gid, $action = '') {
        global $_G;
        if (!$_G['uid']) return false;// 未登录无权限
        if ($_G['adminid'] == 1) return true;// 超级管理员有权限
        if (!$gid) return false; // 无效机构ID无权限

        // 缓存机构成员/管理员判断结果（减少数据库查询）
        static $orgcache = [];
        $cachekey = "gid_{$gid}_uid_{$_G['uid']}";
        $ismember = false;

        if (!isset($orgcache[$cachekey])) {
            // 检查是部门管理员或上级部门管理员
            $ismoderator = C::t('organization_admin')->chk_memberperm($gid, $_G['uid']);
            if (!$ismoderator) {
                // 非管理员时，检查是否为普通成员
                $ismember = !$ismoderator && C::t('organization')->ismember($gid, $_G['uid'], false);
            }

            // 缓存结果：1=管理员；0=成员；-1=非成员
            $orgcache[$cachekey] = $ismoderator ? 1 : ($ismember ? 0 : -1);
        }

        $result = $orgcache[$cachekey];
        if ($result == -1) {
            return false; // 非成员：无任何权限
        }
        if ($action == 'admin' && $result != 1) {
            return false; // 需管理员权限但当前是普通成员
        }
        return true; // 管理员或满足条件的成员
    }

    /**
     * 检查分享权限
     * @param int $sid 分享ID
     * @param string $action 操作类型
     * @return bool 是否有权限
     */
    public static function checkshareperm($sid, $action) {
        global $_G;
        if (!$sid) return false;
        // 超级管理员拥有全部权限
        if ($_G['uid'] > 0 && $_G['adminid'] == 1) return true;
        $share = C::t('shares')->fetch($sid);
        if (!$share) return false;// 分享不存在
        // 分享状态无效（如被删除、过期）
        if (in_array($share['status'], [-3, -4, -5]) || ($share['endtime'] && $share['endtime'] < TIMESTAMP)) {
            return false;
        }
        $perms = $share['perm'] ? array_flip(explode(',', $share['perm'])) : [];
        // 需登录才能访问的分享，未登录则拦截
        if (isset($perms[3]) && $_G['uid'] < 1) return false;

        // 根据分享权限配置判断操作权限
        switch ($action) {
            case 'read': return !isset($perms[2]); // 无禁用预览权限
            case 'edit': return isset($perms[4]);  // 有允许编辑权限
            case 'download':
            case 'copy': return !isset($perms[1]); // 无禁用下载/复制权限
            case 'comment': return isset($perms[6]); // 有允许评论权限
            default: return empty($share['perm']) && in_array($action, ['download', 'read', 'copy']);
        }
    }

    /**
     * 检查文件的操作权限（综合判断：超级管理员、预览、分享、机构/个人身份、文件自身权限等）
     * 权限优先级：超级管理员 → 预览权限 → 分享权限 → 机构/个人身份校验 → 文件自身权限（sperm） → 容器继承权限
     * @param string $action 操作类型（如'read'、'edit'等）
     * @param array $arr 文件信息
     * @param string $bz 标识（用于第三方挂载场景）
     * @return bool 是否有权限
     */
    public static function checkperm($action, $arr, $bz = '') {
        global $_G;
        // 超级管理员拥有全部权限
        if ($_G['uid'] > 0 && $_G['adminid'] == 1) return true;
        // 预览权限特殊处理：允许预览、复制、下载
        if ($arr['preview'] && ($action == 'read' || $action == 'copy' || $action == 'download')) {
            return true;
        }
        // 分享权限处理
        if ($arr['sid']) {
            return self::checkshareperm($arr['sid'], $action);
        }
        // 未登录用户无权限
        if ($_G['uid'] < 1) return false;
        // 网络挂载文件：仅本人可访问
        if ($arr['bz'] && $arr['bz'] !== 'dzz') {
            if ($arr['uid'] !== $_G['uid']) return false;
        }
        // 第三方挂载权限检查（非Dzz盘场景）
        if (($bz && $bz != 'dzz') || ($arr['bz'] && $arr['bz'] != 'dzz')) {
            return self::checkperm_Container($arr['pfid'], $action, $bz ? $bz : $arr['bz']);
        } else {
            // 处理操作类型：rename等效于edit；根据文件归属拼接权限后缀（1=本人，2=他人）
            $action = ($action == 'rename') ? 'edit' : $action;
            if (in_array($action, ['read', 'delete', 'edit', 'download', 'copy'])) {
                $action .= ($_G['uid'] == $arr['uid']) ? '1' : '2';
            }

            // 机构文件：先校验机构成员身份，再判断文件自身权限（sperm），最后继承容器权限
            if ($arr['gid']) {
                if (self::checkgroupPerm($arr['gid'], 'admin')) return true;// 机构管理员拥有全部权限
                if (!self::checkgroupPerm($arr['gid'])) return false; // 非机构成员无权限
                if (!empty($arr['sperm'])) { // 文件自身有权限时，优先判断
                    $power = new perm_binPerm($arr['sperm']);
                    $perm = $power->mergePower(self::getuserPerm());
                    return perm_binPerm::havePower($action, $perm);
                }
            }

            // 个人文件或无自身权限的机构文件：继承容器（上级文件夹）权限
            return self::containerPerm($arr['pfid'], $action);
        }
    }

    /**
     * 检查容器（文件夹）的操作权限
     * @param int $pfid 容器ID
     * @param string $action 操作类型
     * @param string $bz 标识（第三方挂载场景）
     * @param int $uid 用户ID
     * @return bool 是否有权限
     * @note 对于文件夹操作，权限检查应基于其父级目录的权限。
     *       在resources表中，文件夹的oid字段记录其自身ID，而pfid字段记录其所在的父级目录ID，
     *       因此在进行文件夹操作时应当检查其父级目录的权限而非自身权限。
     */
    public static function checkperm_Container($pfid, $action = '', $bz = '', $uid = 0) {
        global $_G;
        if (!$pfid) return false;// 无容器ID时无权限
        if ($_G['uid'] < 1) return false; // 未登录无权限
        if ($_G['adminid'] == 1) return true; // 超级管理员有权限
        // 第三方挂载场景：使用对应挂载的权限配置
        if ($bz) {
            return perm_FolderSPerm::isPower(perm_FolderSPerm::flagPower($bz), $action);
        }
        // 处理操作类型：rename等效于edit；根据容器归属拼接权限后缀
        if (!$uid) {
            $folder = C::t('folder')->fetch($pfid);
            if (empty($folder)) return false;
            $uid = $folder['uid'] ?? 0;
        }
        $action = ($action == 'rename') ? 'edit' : $action;
        if (in_array($action, ['read', 'delete', 'edit', 'download', 'copy'])) {
            $action .= ($_G['uid'] == $uid) ? '1' : '2';
        }
        // 校验容器自身权限
        return self::containerPerm($pfid, $action);
    }
}