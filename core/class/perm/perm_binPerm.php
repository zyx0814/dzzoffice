<?php
/*
* @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
* @license     http://www.dzzoffice.com/licenses/license.txt
* @package     DzzOffice
* @version     DzzOffice 1.1 2014.07.05
* @link        http://www.dzzoffice.com
* @author      zyx(zyx@dzz.cc)
*/

/*php 权限控制类
 *有新的权限在这里添加
 *由于数据库存储是smallint(10),最大支持32位权限；(32位系统最多支持32位，64位系统最多支持64位；)
*/

class perm_binPerm
{

    var $power = "";  //权限存贮变量,十进制整数

    //共享文件夹权限表；


    function __construct($power)
    {
        $this->power = intval($power);
        $this->powerarr = $this->getPowerArr();
    }

    function getPowerArr()
    {
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
            'approve' => 524288,//审批

        );
    }

    function getPowerTitle()
    {
        return array(
            'flag'  => lang('flag_purview_setting'),
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
        );
    }
    //获取权限对应图标
    function getPowerIcos(){
        return array(
            'flag'  => '',
            'read1' => 'dzz dzz-visibility',
            'read2' => 'dzz dzz-all-check',
            'delete1' => 'dzz dzz-delete',
            'delete2' => 'dzz dzz-all-delete',
            'edit1' => 'dzz dzz-netdisk-edit',
            'edit2' => 'dzz dzz-all-edit',
            'download1' => 'dzz dzz-download',
            'download2' => 'dzz dzz-all-download',
            'copy1' => 'dzz dzz-copy',
            'copy2' => 'dzz dzz-all-copy',
            'upload' => 'dzz dzz-upload',
            //'newtype' => lang('new_other_types_files'),
            'folder' => 'dzz dzz-folder',
            //'link' => lang('newlink'),
            //'dzzdoc' => lang('new_document'),
            //'video' => lang('new_video'),
            //'shortcut' => lang('typename_shortcut'),
            'share' => 'dzz dzz-share',
            'approve' => 'dzz dzz-check-box',
        );
    }

    function getMyPower()
    {//获取用户桌面默认的权限
        return self::getSumByAction(array('read1', 'read2', 'delete1', 'edit1', 'download1', 'download2', 'copy1', 'copy2', 'upload', 'newtype', 'folder', 'link', 'dzzdoc', 'video', 'shortcut', 'share'));
    }

    function groupPowerPack()
    {
        $data = array('read' => array('title' => lang('read_only'), 'flag' => 'read', 'permitem' => array('read1', 'read2'), 'tip' => lang('read_only_state')),
            'only-download' => array('title' => lang('upload_only'), 'flag' => 'only-download', 'permitem' => array('read1', 'read2', 'download1', 'download2', 'copy1', 'copy2'), 'tip' => lang('upload_only_state')),
            'read-write1' => array('title' => lang('read_write') . '1', 'flag' => 'read-write1', 'permitem' => array('read1', 'read2', 'delete1', 'edit1', 'download1', 'copy1', 'upload','folder'), 'tip' => lang('read_write_state')),
            'read-write2' => array('title' => lang('read_write') . '2', 'flag' => 'read-write2', 'permitem' => array('read1', 'read2', 'delete1', 'edit1', 'edit2', 'download1', 'download2', 'copy1', 'copy2', 'upload', 'folder'), 'tip' => lang('read_write_state1')),
            'read-write3' => array('title' => lang('read_write') . '3', 'flag' => 'read-write3', 'permitem' => array('read1', 'read2', 'edit1', 'edit2', 'download1', 'download2', 'copy1', 'copy2', 'upload', 'folder'), 'tip' => lang('read_write_state2')),
            'only-write1' => array('title' => lang('write_only'), 'flag' => 'only-write1', 'permitem' => array('read1', 'upload', 'folder'), 'tip' => lang('write_only_state')),
            'all' => array('title' => lang('full_control'), 'flag' => 'all', 'permitem' => 'all', 'tip' => lang('full_control_state'))
        );
        foreach ($data as $key => $value) {
            $data[$key]['power'] = self::getSumByAction($value['permitem']);
        }
        return $data;
    }

    function addPower($action)
    {

        //利用逻辑或添加权限
        if (isset($this->powerarr[$action])) return $this->power = $this->power | intval($this->powerarr[$action]);
    }

    function mergePower($perm)
    { //合成权限，使用于系统权限和用户权限合成
        return $this->power = intval($this->power & intval($perm));
    }

    function delPower($action)
    {
        //删除权限，先将预删除的权限取反，再进行与操作
        if (isset($this->powerarr[$action])) return $this->power = $this->power & ~intval($this->powerarr[$action]);
    }

    function isPower($action)
    {
        //权限比较时，进行与操作，得到0的话，表示没有权限
        if (!$this->powerarr[$action]) return 0;
        return $this->power & intval($this->powerarr[$action]);
    }

    function returnPower()
    {
        //为了减少存贮位数，返回也可以转化为十六进制
        return $this->power;
    }


    function havePower($action, $perm)
    {
        //权限比较时，进行与操作，得到0的话，表示没有权限
        $perm = intval($perm);
        $powerarr = self::getPowerArr();
        if (!$powerarr[$action]) return 0;
        if (!$perm) return 0;
        return $perm & intval($powerarr[$action]);
    }

    function getSumByAction($action = array())
    { //$action==all 时返回所有的值相加
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

    function getGroupPower($type)
    { //权限包
        $data = self::groupPowerPack();
        return $data[$type]['power'];
    }

    function getGroupTitleByPower($power)
    {
        $data = self::groupPowerPack();
        foreach ($data as $key => $value) {
            if ($value['power'] == $power) return $value;
        }
        return $data['read'];
    }

}