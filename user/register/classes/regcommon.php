<?php
namespace  user\register\classes;
use \core as C;
class Regcommon{

    public function run(&$params){

        global $_G,$_GET;

        $setting= $_G['setting'];

        $type = isset($params['returnType']) ? $params['returnType']:'';

        //执行注册
        $result = C::t('user')->user_register($params);
        //获取注册状态
        if(is_array($result)){
            $uid = $result['uid'];
            $params = array_merge($params,$result);
        }else{
            $uid= $result;
            $params['uid'] = $result;
        }

        //判断注册状态，返回提示信息
        if($uid <= 0) {

            if($uid == -1) {

                showTips(array('error'=>lang('profile_nickname_illegal')),$type);

            } elseif($uid == -2) {

                showTips(array('error'=>lang('profile_nickname_protect')),$type);

            } elseif($uid == -3) {

                showTips(array('error'=>lang('profile_nickname_duplicate')),$type);

            } elseif($uid == -4) {

                showTips(array('error'=>lang('profile_email_illegal')),$type);

            } elseif($uid == -5) {

                showTips(array('error'=>lang('profile_email_domain_illegal')),$type);

            } elseif($uid == -6) {

                showTips(array('error'=>lang('profile_email_duplicate')),$type);

            } elseif($uid == -7) {

                showTips(array('error'=>lang('profile_username_illegal')),$type);

            }else {

                showTips(array('error'=>lang('undefined_action')),$type);
            }

        }elseif(empty($uid)){

            showTips(array('error'=>lang('register_empty_data')),$type);

        }

    }
}