<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/5/11
 * Time: 10:58
 */
function dzz_explorer_init() {//初始化用户信息
    global $_G;
    //初始化默认目录
    $rootfid = dzz_explorer_folder_init();
    dzz_explorer_searchcat_info_init();
    $userconfig = C::t('user_field')->fetch($_G['uid']);
    $userconfig['rootfid'] = $rootfid;
    return $userconfig;
}

//检查类型数据
function check_default_explorer_init() {
    dzz_explorer_searchcat_info_init();
}

function dzz_explorer_folder_init() {//初始化目录
    global $_G, $space;

    //创建资源管理器个人根目录
    $root = array(
        'pfid' => 0,
        'uid' => $_G['uid'],
        'username' => $_G['username'],
        'perm' => 0,
        'fname' => lang('explorer_user_root_dirname'),
        'flag' => 'home',
        'innav' => 1,
        'fsperm' => perm_FolderSPerm::flagPower('home')

    );
    if ($rootfid = DB::result_first("select fid from " . DB::table('folder') . " where uid='{$_G['uid']}' and flag='home' ")) {
        C::t('folder')->update($rootfid, array('fname' => $root['fname'], 'isdelete' => 0, 'pfid' => 0, 'fsperm' => $root['fsperm'], 'perm' => $root['perm']));
    } else {
        $rootfid = C::t('folder')->insert($root);
        C::t('folder')->update_perm_inherit_by_fid($rootfid);
    }
    return $rootfid;
}

function dzz_explorer_searchcat_info_init() {
    global $_G;
    //创建资源管理器用户默认搜索类型
    $searchcat = array(
        array(
            'catname' => '图片',
            'uid' => $_G['uid'],
            'ext' => '.jpg,.png,.gif,jpeg,.bmp',
            'default' => '1'
        ),
        array(
            'catname' => '文档',
            'uid' => $_G['uid'],
            'ext' => '.doc,.docx,.xls,.xlsx,.ppt,.pptx,.pdf,.dzzdoc,.txt',

        ),
    );
    foreach ($searchcat as $v) {
        if (DB::result_first("select count(*) from " . DB::table('resources_cat') . " where catname = '" . $v['catname'] . "' and uid =" . $_G['uid']) > 0) {
            continue;
        } else {
            C::t('resources_cat')->insert_cat($v);
        }
    }
}