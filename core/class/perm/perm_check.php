<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

class perm_check {

    public static function getuserPerm() {
        global $_G;
        $perm = DB::result_first("select perm from %t where uid=%d", array('user_field', $_G['uid']));
        $groupperm = $_G['group']['perm'];
        if ($perm < 1) $perm = intval($_G['group']['perm']);
        if ($_G['setting']['allowshare']) {
            $power = new perm_binPerm($perm);
            $perm = $power->delPower('share');
        }
        return $perm;
    }

    public static function getPerm($fid, $bz = '', $i = 0) {
        global $_G;
        if (isset($_G['gperm'])) return intval($_G['gperm']);//可以通过这个参数直接使用此权限值不去查询权限

        $i++;
        if ($i > 20) { //防死循环，如果循环20次以上，直接退出；
            return perm_binPerm::getGroupPower('read');
        }

        if ($folder = C::t('folder')->fetch($fid)) {
            $perm = intval($folder['perm']);
            $power = new perm_binPerm($perm);
            if ($folder['gid']) {
                if (C::t('organization_admin')->chk_memberperm($folder['gid'], $_G['uid'])) return (perm_binPerm::getGroupPower('all'));

                if ($power->isPower('flag')) {//不继承，使用此权限
                    if ($_G['setting']['allowshare']) {
                        $perm = $power->delPower('share');
                    }
                    $power1 = new perm_binPerm($perm);
                    return $power1->power;//mergePower(self::getuserPerm());
                } else { //继承上级，查找上级
                    if ($folder['pfid'] > 0 && $folder['pfid'] != $folder['fid']) { //有上级目录
                        //return self::getPerm($folder['pfid'],$bz,$i);
                        $perm = self::getPerm($folder['pfid'], $bz, $i);
                        $power1 = new perm_binPerm($perm);
                        return $power1->power;//mergePower(self::getuserPerm());
                    } else {   //其他的情况使用
                        return perm_binPerm::getGroupPower('read');
                    }
                }
            } else {
                if ($folder['uid'] == $_G['uid'] || $_G['adminid'] == 1) return perm_binPerm::getGroupPower('all');
                if ($power->isPower('flag')) {//不继承，使用此权限
                    if ($_G['setting']['allowshare']) {
                        $perm = $power->delPower('share');
                    }
                    $power1 = new perm_binPerm($perm);
                    return $power1->mergePower(self::getuserPerm());
                } else { //继承上级，查找上级
                    if ($folder['pfid'] > 0 && $folder['pfid'] != $folder['fid']) { //有上级目录
                        return self::getPerm($folder['pfid'], $bz, $i);
                    } else {   //其他的情况使用
                        return self::getuserPerm();
                    }
                }
            }
        } else {
            return perm_binPerm::getGroupPower('read');
        }
    }

    public static function getPerm1($fid, $bz = '', $i = 0, $newperm = 0) {
        global $_G;

        $i++;
        if ($i > 20) { //防死循环，如果循环20次以上，直接退出；
            return perm_binPerm::getGroupPower('all');
        }
        if ($folder = C::t('folder')->fetch($fid)) {
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
                    if ($_G['setting']['allowshare']) {
                        $perm = $power->delPower('share');
                    }
                    $power1 = new perm_binPerm($perm);
                    return $power1->mergePower(self::getuserPerm());
                } else { //继承上级，查找上级
                    if ($folder['pfid'] > 0 && $folder['pfid'] != $folder['fid']) { //有上级目录
                        return self::getPerm1($folder['pfid'], $bz, $i);
                    } else {   //其他的情况使用
                        return self::getuserPerm();
                    }
                }
            }
        } else {
            return perm_binPerm::getGroupPower('read');
        }
    }

    public static function userPerm($fid, $action) { //判断容器有没有指定的权限
        global $_G;
        if ($_G['adminid'] == 1) { //是管理员
            return true;
        }

        if (!$_G['uid']) { //如果不是登录用户，返回false;
            return false;
        }

        //if($action=='download' || $action=='saveto' || $action=='copy' ) return true;
        $perm = self::getPerm($fid);
        //exit($perm.'===='.$action);
        return perm_binPerm::havePower($action, $perm);
        /* if($perm<5){
             if($action=='view') return true;
             else return false;
         }
         return true;*/
    }

    public static function groupPerm($fid, $action, $gid) { //判断容器有没有指定的权限
        global $_G;

        $ismoderator = C::t('organization_admin')->chk_memberperm($gid, $_G['uid']);
        if ($action == 'admin' && !$ismoderator) return false;
        if ($ismoderator) { //是部门管理员或上级部门管理员
            return true;
        }
        //不是部门成员或下级部门成员没有权限
        if (!C::t('organization')->ismember($gid, $_G['uid'], false)) return false;
        //if($action=='download' || $action=='saveto' || $action=='copy' ) return true;

        $perm = self::getPerm($fid);
        //exit($perm.'====='.$fid.'======='.$gid.'===='.$action);
        if ($perm > 0) {
            $power = new perm_binPerm($perm);
            //exit($perm.'====='.$fid.'======='.$gid.'===='.$action.'==='.$power->isPower($action));
            return $power->isPower($action);
        }

        return false;
    }

    //$arr=array('uid','gid','desktop');其中这几项必须
    public static function checkperm($action, $arr, $bz = '') { //检查某个图标是否有权限;
        global $_G;
        if ($_G['uid'] > 0 && $_G['adminid'] == 1) return true; //网站管理员 有权限;
        if ($arr['preview'] && ($action == 'read' || $action == 'copy' || $action == 'download')) {
            return true;
        }
        if ($arr['sid']) {
            $share = C::t('shares')->fetch($arr['sid']);
            if (!$share) {
                return false; // 资源不存在
            }
            switch ($share['status']) {
                case -4:
                case -5:
                case -3:
                    return false;
            }
            if ($share['endtime'] && $share['endtime'] < TIMESTAMP) {
                return false;
            }
            $perms = $share['perm'] ? array_flip(explode(',', $share['perm'])) : [];
            // 登录态检查（权限3：需要登录）
            if (isset($perms[3]) && $_G['uid'] < 1) {
                return false; // 未登录拦截
            }
            switch ($action) {
                case 'read':
                    return !isset($perms[2]); // 权限2：禁用预览
                case 'edit':
                    return isset($perms[4]);  // 权限4：允许编辑
                case 'download':
                case 'copy':
                    return !isset($perms[1]); // 权限1：禁用下载/复制
                case 'comment':
                    return isset($perms[6]); // 6：禁用评论
                default:
                    // 无权限配置时默认放行下载/读取/复制
                    return empty($share['perm']) && in_array($action, ['download', 'read', 'copy']);
            }
        }
        if ($_G['uid'] < 1) { //游客没有权限
            return false;
        }
        if ($arr['bz'] && $arr['bz'] !== 'dzz') {
            if ($arr['uid'] !== $_G['uid']) return false;
        }
        if (!$arr['gid'] && $arr['uid'] !== $_G['uid']) {//我的网盘文件只限于当前用户
            return false;
        }
        if (($bz && $bz != 'dzz') || ($arr['bz'] && $arr['bz'] != 'dzz')) {
            return self::checkperm_Container($arr['pfid'], $action, $bz ? $bz : $arr['bz']);
        } else {
            if ($action == 'rename') {
                $action = 'edit';
            }
            if (in_array($action, array('read', 'delete', 'edit', 'download', 'copy'))) {
                if ($_G['uid'] == $arr['uid']) $action .= '1';
                else $action .= '2';
            }
            //首先判断ico的超级权限；
            if (!perm_FileSPerm::isPower($arr['sperm'], $action)) return false;

            if ($folder = C::t('folder')->fetch_fsperm_by_fid($arr['pfid'])) {
                //首先判断目录的超级权限；
                if (!perm_FolderSPerm::isPower($folder['fsperm'], $action)) return false;
            }
            return self::checkperm_Container($arr['pfid'], $action, $bz);
        }
    }

    public static function checkperm_Container($pfid, $action = '', $bz = '') { //检查容器是否有权限操作;
        global $_G;
        if ($_G['uid'] < 1) { //游客没有权限
            return false;
        }
        if ($bz) {
            if (!perm_FolderSPerm::isPower(perm_FolderSPerm::flagPower($bz), $action)) return false;
            return true;
        } else {
            if ($folder = C::t('folder')->fetch($pfid)) {
                //首先判断目录的超级权限；
                if ($action == 'rename') {
                    $action = 'edit';
                }
                if (in_array($action, array('read', 'delete', 'edit', 'download', 'copy'))) {
                    if ($_G['uid'] == $folder['uid']) $action .= '1';
                    else $action .= '2';
                }
                if (!perm_FolderSPerm::isPower($folder['fsperm'], $action)) return false;
                //默认目录只有管理员有权限改变排列
                //if($action=='admin' && $_G['adminid']!=1 && $folder['flag']!='folder') return false;
            }
            if ($_G['adminid'] == 1) return true; //网址管理员 有权限;

            if ($folder['gid']) {
                return self::groupPerm($pfid, $action, $folder['gid']);
            } else {
                return self::userPerm($pfid, $action);
            }
        }
    }
}
