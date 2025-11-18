<?php
/*
* @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
* @license     http://www.dzzoffice.com/licenses/license.txt
* @package     DzzOffice
* @link        http://www.dzzoffice.com
* @author      zyx(zyx@dzz.cc)
*/

/*php 权限控制类
 *有新的权限在这里添加
 *由于数据库存储是smallint(10),最大支持32位权限；(32位系统最多支持32位，64位系统最多支持64位；)
*/

class perm_binPerm {
    protected $powe;  //权限存贮变量,十进制整数
    protected $powerarr;
    protected static $groupPowerCache = null;

    public function __construct($power) {
        $this->power = intval($power);
        $this->powerarr = self::getPowerArr();
    }

    public static function getPowerArr() {
        return array(
            'flag' => 1,        //标志位为1表示权限设置,否则表示未设置，继承上级；
            'read1' => 2,        //读取自己的文件
            'read2' => 4,        //读取所有文件
            'delete1' => 8,        //删除自己的文件
            'delete2' => 16,        //删除所有文件
            'edit1' => 32,        //编辑自己的文件
            'edit2' => 64,        //编辑所有文件
            'download1' => 128,        //下载自己的文件
            'download2' => 256,        //下载所有文件
            'copy1' => 512,        //拷贝自己的文件
            'copy2' => 1024,    //拷贝所有文件
            'upload' => 2048,    //新建和上传
            //'newtype' => 4096,    //新建其他类型文件（除文件夹以外）
            'folder' => 8192,    //新建文件夹
            //'link' => 16384,    //新建网址
            //'dzzdoc' => 32768,    //新建dzz文档
            //'video' => 65536,    //新建视频
            // 'shortcut' => 131072,    //快捷方式
            'share' => 262144,    //分享
            'approve' => 524288, //审批
            'comment' => 1048576, //评论
            'my_disk' => 2097152, //个人网盘
            'my_info' => 4194304, //个人头像
            'my_username' => 8388608, //个人用户名
        );
    }

    public static function getPowerTitle() {
        return array(
            'flag' => lang('flag_purview_setting'),
            'read1' => lang('read_my_file'),
            'read2' => lang('read_my_file1'),
            'delete1' => lang('delete_my_file'),
            'delete2' => lang('delete_all_file'),
            'edit1' => lang('edit_my_file'),
            'edit2' => lang('edit_all_file'),
            'download1' => lang('upload_my_file'),
            'download2' => lang('upload_all_file'),
            'copy1' => lang('copy_my_file'),
            'copy2' => lang('copy_all_file'),
            'upload' => lang('uploading'),
            //'newtype' => lang('new_other_types_files'),
            'folder' => lang('newfolder'),
            //'link' => lang('newlink'),
            //'dzzdoc' => lang('new_document'),
            //'video' => lang('new_video'),
            //'shortcut' => lang('typename_shortcut'),
            'share' => lang('share'),
            'approve' => lang('approve'),
            'comment' => lang('comment'),
            'my_disk' => lang('my_disk'),
            'my_info' => lang('my_info'),
            'my_username' => lang('my_username'),
        );
    }

    //获取权限对应图标
    public static function getPowerIcos() {
        return array(
            'flag' => '',
            'read1' => 'dzz dzz-visibility mdi mdi-eye',
            'read2' => 'dzz dzz-all-check mdi mdi-eye-plus',
            'delete1' => 'dzz dzz-delete mdi mdi-delete',
            'delete2' => 'dzz dzz-all-delete mdi mdi-delete-sweep',
            'edit1' => 'dzz dzz-netdisk-edit mdi mdi-pencil',
            'edit2' => 'dzz dzz-all-edit mdi mdi-pencil-plus',
            'download1' => 'dzz dzz-download mdi mdi-download',
            'download2' => 'dzz dzz-all-download mdi mdi-download-multiple',
            'copy1' => 'dzz dzz-copy mdi mdi-file-multiple',
            'copy2' => 'dzz dzz-all-copy mdi mdi-folder-multiple-plus',
            'upload' => 'dzz dzz-upload mdi mdi-upload',
            //'newtype' => lang('new_other_types_files'),
            'folder' => 'dzz dzz-folder mdi mdi-folder',
            //'link' => lang('newlink'),
            //'dzzdoc' => lang('new_document'),
            //'video' => lang('new_video'),
            //'shortcut' => lang('typename_shortcut'),
            'share' => 'dzz dzz-share mdi mdi-share-variant',
            'approve' => 'dzz dzz-check-box mdi mdi-checkbox-marked',
            'comment' => 'dzz dzz-comment mdi mdi-comment',
            'my_disk' => 'mdi mdi-account-box',
            'my_info' => 'mdi mdi-account-circle',
            'my_username' => 'mdi mdi-account-edit',
        );
    }

    /**
     * 权限类型映射（区分文件夹操作权限/控制类权限）
     * @return array 键：权限键名，值：权限类型（folder=文件夹操作，control=控制类）
     */
    public static function getPowerType() {
        return array(
            'read1' => 'folder',
            'read2' => 'folder',
            'delete1' => 'folder',
            'delete2' => 'folder',
            'edit1' => 'folder',
            'edit2' => 'folder',
            'download1' => 'folder',
            'download2' => 'folder',
            'copy1' => 'folder',
            'copy2' => 'folder',
            'upload' => 'folder',
            'folder' => 'folder',
            'share' => 'folder',
            'approve' => 'folder',
            'comment' => 'folder',

            'flag' => '',
            'my_disk' => 'control', // 对个人网盘生效
            'my_info' => 'user',
            'my_username' => 'user',
        );
    }

    public static function groupPowerPack() {
        if (self::$groupPowerCache !== null) {
            return self::$groupPowerCache;
        }
        $groups = [
            'read' => ['read1', 'read2'],
            'all' => 'all'
        ];
        $data = [];
        foreach ($groups as $key => $value) {
            $data[$key] = self::getSumByAction($value);
        }
        self::$groupPowerCache = $data;
        return $data;
    }

    public function addPower($action) {
        //利用逻辑或添加权限
        if (isset($this->powerarr[$action])) return $this->power = $this->power | intval($this->powerarr[$action]);
    }

    public function mergePower($perm) { //合成权限，使用于系统权限和用户权限合成
        return $this->power = intval($this->power & intval($perm));
    }

    public function delPower($action) {
        //删除权限，先将预删除的权限取反，再进行与操作
        if (isset($this->powerarr[$action])) return $this->power = $this->power & ~intval($this->powerarr[$action]);
    }

    public function isPower($action) {
        //权限比较时，进行与操作，得到0的话，表示没有权限
        if (!$this->powerarr[$action]) return 0;
        return $this->power & intval($this->powerarr[$action]);
    }

    public static function havePower($action, $perm) {
        //权限比较时，进行与操作，得到0的话，表示没有权限
        $perm = intval($perm);
        $powerarr = self::getPowerArr();
        if (!$powerarr[$action]) return 0;
        if (!$perm) return 0;
        return $perm & intval($powerarr[$action]);
    }

    public static function getSumByAction($action = array()) { //$action==all 时返回所有的值相加
        $i = 0;
        $powerarr = self::getPowerArr();
        if ($action == 'all') {
            foreach ($powerarr as $key => $val) {
                $i += $val;
            }
        } else {
            $i = 1;
            foreach ($action as $val) {
                $i += intval($powerarr[$val]);
            }
        }
        if (getglobal('setting/allowshare')) {
            $power = new perm_binPerm($i);
            $i = $power->delPower('share');
        }
        return $i;
    }

    public static function getGroupPower($type) { //权限包
        $data = self::groupPowerPack();
        return $data[$type] ?? $data['read'];
    }
}