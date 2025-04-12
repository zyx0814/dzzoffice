<?php
$_config = array();
$_config['namespacelist'] = array(
    'root'      =>DZZ_ROOT,
    'coreroot'  => DZZ_ROOT.'core',
    'admin'     => DZZ_ROOT.'admin',
    'core'      => CORE_PATH,
    'dzz'       => DZZ_ROOT.APP_DIRNAME,
    'user'      => DZZ_ROOT.'user',
    'misc'      => DZZ_ROOT.'misc'
);

$_config['default_mod'] = 'index';

$_config['default_op'] = 'index';

$_config['default_action'] = 'index';


/**
 * 其它配置
 */
$_config['allow_robot'] = false;
$_config['allow_view'] = 0;//(0=>所有人,1=>用户,2=>管理员,3=>创始人)
$_config['libfile'] = '';
$_config['language'] = '';
$_config['mod_view_perm'] = '';
$_config['action_name'] = 'do';
$_config['do_name'] = 'action';

$_config['profile']['privacy'] = array(
    '-1'=>'私密',
    '0'=>'公开',
    '1'=>'本部门可见',
    '2'=>'本机构可见',
);
return $_config;