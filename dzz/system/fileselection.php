<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
include libfile('function/filerouterule');
$do = isset($_GET['do']) ? trim($_GET['do']):'';
$callback = isset($_GET['callback']) ? $_GET['callback'] : 'callback_selectposition';//回调函数名称
$allowcreate = isset($_GET['allowcreate']) ? intval($_GET['allowcreate']):1;//是否允许新建文件夹，默认允许
//获取配置设置值
$explorer_setting = get_resources_some_setting();
$range = isset($_GET['range']) ? trim($_GET['range']):'';//指定范围
$defaultselect = isset($_GET['defaultsel']) ? filerouteParse(trim($_GET['defaultsel'])):filerouteParse('我的网盘');//默认选中,支持路径如：我的网盘/xxx,群组xxx/xxx,群组或机构|xxx，群组或机构|xxx/新建文件夹
$type =  isset($_GET['type']) ? intval($_GET['type']):0;//2,选择位置；1，保存文件；0，选择文件；默认为0
$exttype =  isset($_GET['exttype']) ? trim($_GET['exttype']):'';//类型范围
$filename = isset($_GET['filename'])?trim($_GET['filename']):'';
$token = isset($_GET['token']) ? trim($_GET['token']):'';//调用地方传递参数，将原样返回给回调函数
$perm = isset($_GET['perm']) ? trim($_GET['perm']):'';//权限判断值：比如 write,判断是否有写入权限；再如，write,copy，判断是否有写入和copy权限(即多个权限用逗号分隔)
$mulitype =  isset($_GET['mulitype']) ? intval($_GET['mulitype']):0;//0，不允许多选；1，允许多选
if($type == 1){
    $rid = isset($_GET['rid']) ? trim($_GET['rid']):'';
    $savefile = array();
    if($rid){
        $savefile = C::t('resources')->fetch_info_by_rid($rid);
        $filename = $savefile['name'];
    }else{
        $savefile['name'] = $filename;
    }
}
$gets = array(
    'allowcreate' => $allowcreate,
    'type'=>$type,
    'rid'=>$rid,
    'filename'=>$filename,
    'range'=>$range,
    'defaultselect'=>$defaultselect['hash'],
    'exttype'=>$exttype,
    'mulitype'=>$mulitype,
    'perm'=>$perm
);
$urldefined= '&'. url_implode($gets);
$allowvisit = array('file','listtree','explorerfile','json','ajax','dzzcp','save');
//如果是移动端
$ismobile = helper_browser::ismobile();
if($ismobile){
    require MOD_PATH.'/mobilefileselection.php';
}else{
    if($do){
        if(!in_array($do,$allowvisit)){
            showmessage(lang('access_denied'),dreferer());
        }else{
            require MOD_PATH.'/fileselection/'.$do.'.php';
        }
    }else{
        include template('fileselection/index');
        exit();
    }
}

