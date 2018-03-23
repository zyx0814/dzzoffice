<?php
namespace   user\classes;

use \core as C;

class Safechk{

    public function run(){
        global $_G;
        $uid = intval($_G['uid']);
        $seccodecheck = $_G['setting']['seccodestatus'] & 4;
        $errornumsession = isset($_SESSION['errornum'.$uid]) ? $_SESSION['errornum'.$uid]:0 ;
        $chkarr = C::t('user_profile')->get_userprofile_by_uid($uid);
        $hideemail = $this->mobileemail_safe( $chkarr['email']);
        $hidephone = $this->mobileemail_safe( $chkarr['phone']);
       $template =  include template('common/safechk');
    }
    public function mobileemail_safe($val)
    {
        $patten = '/^([A-Za-z0-9\-_.+]+)@([A-Za-z0-9\-]+[.][A-Za-z0-9\-.]+)$/';
        if (preg_match($patten, $val)) {
            $emailarr = explode('@', $val);

            $len = strlen($emailarr[0]);//字符长度
            $hlen = floor($len/2);//需要隐藏则字符长度

            if($hlen == 0){
                $emailarr[0] = substr_replace($emailarr[0],'*',0,1);
            }else{

                $start = ($hlen > 3) ?  3:$hlen;
                $hidestr = '';
                for($i = 0; $i <= $hlen; $i++){
                    $hidestr .= '*';
                }
                $emailarr[0] = substr_replace($emailarr[0],$hidestr,$start,$hlen);
            }
            $val = $emailarr[0].'@'.$emailarr[1];
        } else if(preg_match('/^1[34578]{1}\d{9}$/',$val)){
            $val = preg_replace('/(\d{3})\d{5}(\d{3})/', '$1*****$2', $val);
        }
        return $val;
    }
}