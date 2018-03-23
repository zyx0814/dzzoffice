<?php
namespace   user\sso\classes;

use \core as C;
class Oauth{

    private  $secretid = '';//秘钥

    private  $keyid = '';//验证key

    private  $token = '';//验证token

    public   $expire = 2592000;//token过期时间

    public   $backurl = '';//回调地址

    public   $usename = '';//应用名称

    public   $host = '';//绑定域名

    public   $refreshexpire = 31536000;//刷新令牌过期时间

    public   $state = '';


    public function setkeyserectid($backurl,$usename = ''){//设置key和秘钥

        $this->keyid = random(10);//生成验证key

        $this->secretid = random(30);//生成秘钥

        $this->backurl  = $backurl;//回调地址

        $this->usename  = $usename;//应用名称

        $backarr = parse_url($backurl);

        $host = $backarr['host'];
		if(empty($host)){
			return self::showError(4006);
			
		}
        if(C::t('user_sdk')->fetch_by_host($host)){//查询域名对应sdk

            $setarr = array('key'=>$this->keyid,'secret'=>$this->secretid,'backurl'=>$this->backurl,'usename'=>$this->usename);

            if(C::t('user_sdk')->update_by_host($host,$setarr)){//更新sdk

                return array('keyid'=>$this->keyid,'secretid'=>$this->secretid);

            }else{
               return self::showError(4003);
            }

        }else{//生成sdk

            $setarr = array('key'=>$this->keyid,'secret'=>$this->secretid,'host'=>$host,'backurl'=>$this->backurl,'usename'=>$this->useid);

            if(C::t('user_sdk')->insert($setarr)){

                return array('keyid'=>$this->keyid,'secretid'=>$this->secretid);

            }else{

              return self::showError(4003);
            }

        }

    }

    public function getrefererhost(){//获取之前页域名

        $url  = $_SERVER["HTTP_REFERER"];

        $parseurl = parse_url($url);

        $host    = $parseurl['host'];

        return $host;
    }

    public function gettoken($keyid){//获取token

        $this->keyid = $keyid;//key

        if($return = C::t('user_sdk')->fetch($this->keyid)){//检查对应sdk

            $this->secretid = $return['secret'];//获取对应秘钥

            $this->createToken();//生成token

            $refreshtoken = $this->createRefreshStr($this->secretid);////生成刷新令牌

            $setarr = array('tokenid'=>$this->token,'date'=>time(),'expire'=>$this->expire,'refreshtoken'=>$refreshtoken);

            if(C::t('user_salf')->update($this->uid,$setarr)){//存储安全信息


                return array('tokenid'=>$this->token,'date'=>$setarr['date'],'expire'=>$this->expire,'refreshtoken'=>$refreshtoken);
            }

        }else{

          return  self::showError(4001);
        }

    }


    public function chk_callback_host($keyid,$backurl = ''){//检查回调地址域名是否合法

        if($return = C::t('user_sdk')->fetch($keyid)){

           $this->host = $return['host'];

           $backarr = parse_url($backurl);

           $host = $backarr['host'];

            if($host != $this->host){

                return false;
            }

            if(strpos($return['backurl'],',') !== false){

                $backurls = explode(',',$return['backurl']);

            }else{

                $backurls[] = $return['backurl'];

            }

            if($backurl && in_array($backurl,$backurls)){

                return true;
            }

        }
        return false;
    }

    private function createRefreshStr($secretid){//生成刷新令牌

        return dzzencode($this->uid.$secretid.random(6),random(3));
    }

    public function refreshtoken($token,$refreshtokenid){//刷新token

        if($return  = C::t('user_salf')->fetch_by_tokenid($token)){//获取对应安全信息

            if($return['refreshtoken'] == $refreshtokenid && (time()-$return['date']) < $this->refreshexpire){//验证刷新令牌，判断刷新令牌是否过期

                $sdkinfo = C::t('user_sdk')->fetch($return['keyid']);

                $this->secretid = $sdkinfo['secret'];

                $this->keyid    = $sdkinfo['key'];

                $this->uid = $sdkinfo['uid'];

                $this->createToken();//生成token

                $refreshtoken = $this->createRefreshStr();//生成刷新令牌

                $setarr = array('tokenid'=>$this->token,'date'=>time(),'refreshtoken'=>$refreshtoken);

                if(C::t('user_salf')->update($this->keyid,$setarr)){//更新token

                    return array('tokenid'=>$this->token,'date'=>time(),'refreshtoken'=>$refreshtoken,'expire'=>$return['expire']);
                }
            }else{

              return   $this->showError(4005);

            }

        }else{

            return $this->showError(4001);
        }
    }

    public function chk_token($token){//检查token是否可用

        if(empty($token))self::showError(4004);

        if($return = C::t('user_salf')->fetch_by_tokenid($token)){//获取token安全信息

           return ((time() - $return['date']) < $return['expire']) ? $return['uid']:false;//验证token

        }else{

           return self::showError(4004);
        }

    }

    protected  function createToken(){//创建token

        $this->token = md5($this->uid.$this->secretid.$this->keyid.time());//生成token

    }

    public function showError($code,$desc = '$'){//错误输出

        if($desc == '$'){

            switch($code){
                case 4001: $msg = 'Key Not Validated';
                    break;
                case 4002: $msg = 'Domain Is Illegal';
                    break;
                case 4003: $msg = 'System Busy';
                    break;
                case 4004: $msg = 'Token Unavailable Or Expired';
                    break;
                case 4005: $msg = 'Refreshtoken Unavailable Or Expired';
                    break;
                case 4006: $msg = 'Domain Name Or Callback Address Is Illegal';
                    break;
                case 4007: $msg = 'Request Validation Not Passed';
                    break;
                default: $msg = "System Mistake";
            }

        }else{

            $msg = $desc;

        }

        return array('code'=>$code,'msg'=>$msg);

        exit();
    }

    public function safecode(){//code加密

        $code =  base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($this->secretid), $this->code, MCRYPT_MODE_CBC, md5(md5($this->secretid))));

        $code = base64_encode($code);

        $code = str_replace(array('+','/','='),array('-','_',''),$code);

        return $code;
    }

    public function dsafecode($code){//解密code

        $code = str_replace(array('-','_'),array('+','/'),$code);

        $mod4 = strlen($code) % 4;

        if ($mod4) {

            $code .= substr('====', $mod4);

        }
        $code = base64_decode($code);

       return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($this->secretid), base64_decode($code), MCRYPT_MODE_CBC, md5(md5($this->secretid))), "\0");
    }

    public function dsafesecret($keyid,$safesecret){

       return  mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($this->keyid), base64_decode($safesecret), MCRYPT_MODE_CBC, md5(md5($this->keyid)));
    }


}