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

Hook::listen('check_login');

if (submitcheck('avatarsubmit')) {
    if($_G['adminid'] == 1) {
        $my_info = false;
    } else {
        $my_info = perm_check::checkuserperm('my_info');
    }
    if ($my_info) exit(json_encode(array('error' => '您所在的用户组没有权限修改头像！')));
    if ($_GET['imagedata']) $success = upbase64($_GET['imagedata'], $_G['uid']);
    if ($_GET['aid']) IO::delete('attach::' . intval($_GET['aid']));
    if ($success) {
        exit(json_encode(array('msg' => 'success')));
    } else {
        exit(json_encode(array('error' => '头像保存错误，请稍候重试')));
    }
} elseif ($_GET['do'] == 'imageupload') {
    include libfile('class/uploadhandler');
    $options = array('accept_file_types' => '/\.(gif|jpe?g|png)$/i',
        'upload_dir' => $_G['setting']['attachdir'] . 'cache/',
        'upload_url' => $_G['setting']['attachurl'] . 'cache/',
        'thumbnail' => array('max-width' => 512, 'max-height' => 512)
    );
    $upload_handler = new uploadhandler($options);
    exit();
}

function upBase64($base64Data, $uid) {
    $img = base64_decode(str_replace('data:image/png;base64,', '', $base64Data));
    $temp = getglobal('setting/attachdir') . 'cache/' . random(5) . '.png';
    //移动文件
    if (!(file_put_contents($temp, $img))) { //移动失败
        return false;
    } else { //移动成功,生成3种尺寸头像
        $home = get_home($uid);
        if (!is_dir(DZZ_ROOT . './data/avatar/' . $home)) {
            set_home($uid, DZZ_ROOT . './data/avatar/');
        }
        $bigavatarfile = DZZ_ROOT . './data/avatar/' . get_avatar($uid, 'big');
        $middleavatarfile = DZZ_ROOT . './data/avatar/' . get_avatar($uid, 'middle');
        $smallavatarfile = DZZ_ROOT . './data/avatar/' . get_avatar($uid, 'small');
        include_once libfile('class/image');
        $image = new image();
        $success = 0;
        if ($thumb = $image->Thumb($temp, $bigavatarfile, 200, 200, 1)) {
            $success++;
        }
        if ($thumb = $image->Thumb($temp, $middleavatarfile, 120, 120, 1)) {
            $success++;
        }
        if ($thumb = $image->Thumb($temp, $smallavatarfile, 48, 48, 1)) {
            $success++;
        }
        if ($success > 2) {
            C::t('user')->update($uid, array('avatarstatus' => '1'));
        }
        @unlink($temp);
        return $success;
    }
}