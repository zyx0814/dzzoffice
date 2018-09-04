<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

//这里定义一个类brower来判断浏览器种类和操作系统平台
class helper_browser
{
    //判断返回的字符串提取对应的字符做出判断
    //A function to determine what browser and version we are using.

    static function getBrowser($useragent = null)
    {
        // check for most popular browsers first
        // unfortunately, that's IE. We also ignore Opera and Netscape 8
        // because they sometimes send msie agent
        $useragent = $useragent ? $useragent : $_SERVER['HTTP_USER_AGENT'];
        if (strpos($useragent, 'MSIE') !== FALSE && strpos($useragent, 'Opera') === FALSE && strpos($useragent, 'Netscape') === FALSE) {
            //deal with Blazer
            if (preg_match("/Blazer\/([0-9]{1}\.[0-9]{1}(\.[0-9])?)/", $useragent, $matches)) {
                return array('blazer' => $matches[1]);
            }
            //deal with IE
            if (preg_match("/MSIE ([0-9]{1,2}\.[0-9]{1,2})/", $useragent, $matches)) {
                return array('ie' => $matches[1]);
            }
        } elseif (strpos($useragent, 'IEMobile') !== FALSE) {
            if (preg_match("/IEMobile\/([0-9]{1,2}\.[0-9]{1,2})/", $useragent, $matches)) {
                return array('ie' => $matches[1], 'ismobile' => $matches[1]);

            }
        } elseif (strpos($useragent, 'Gecko')) {
            //deal with Gecko based
            if (strpos($useragent, 'Trident/7.0') !== FALSE && strpos($useragent, 'rv:11.0') !== FALSE) {
                return array('ie' => 11);
            } //if firefox
            elseif (preg_match("/Firefox\/([0-9]{1,2}\.[0-9]{1,2}(\.[0-9]{1,2})?)/", $useragent, $matches)) {
                return array('firefox' => $matches[1]);
            }

            //if Netscape (based on gecko)
            if (preg_match("/Netscape\/([0-9]{1}\.[0-9]{1}(\.[0-9])?)/", $useragent, $matches)) {
                return array('netscape' => $matches[1]);
            }

            //check chrome before safari because chrome agent contains both
            if (preg_match("/Chrome\/([^\s]+)/", $useragent, $matches)) {
                return array('chrome' => $matches[1]);
            }

            //if Safari (based on gecko)
            if (preg_match("/Safari\/([0-9]{2,4}(\.[0-9])?)/", $useragent, $matches)) {
                return array('safari' => $matches[1]);
            }

            //if Galeon (based on gecko)
            if (preg_match("/Galeon\/([0-9]{1}\.[0-9]{1}(\.[0-9])?)/", $useragent, $matches)) {
                return array('galeon' => $matches[1]);
            }

            //if Konqueror (based on gecko)
            if (preg_match("/Konqueror\/([0-9]{1}\.[0-9]{1}(\.[0-9])?)/", $useragent, $matches)) {
                return array('konqueror' => $matches[1]);
            }

            // if Fennec (based on gecko)
            if (preg_match("/Fennec\/([0-9]{1}\.[0-9]{1}(\.[0-9])?)/", $useragent, $matches)) {
                return array('fennec' => $matches[1]);
            }

            // if Maemo (based on gecko)
            if (preg_match("/Maemo\/([0-9]{1}\.[0-9]{1}(\.[0-9])?)/", $useragent, $matches)) {
                return array('maemo' => $matches[1]);
            }

            //no specific Gecko found
            //return generic Gecko
            return array('Gecko based' => true);
        } elseif (strpos($useragent, 'Opera') !== FALSE) {
            //deal with Opera
            if (preg_match("/Opera[\/ ]([0-9]{1}\.[0-9]{1}([0-9])?)/", $useragent, $matches)) {
                return array('opera' => $matches[1]);
            }
        } elseif (strpos($useragent, 'Lynx') !== FALSE) {
            //deal with Lynx
            if (preg_match("/Lynx\/([0-9]{1}\.[0-9]{1}(\.[0-9])?)/", $useragent, $matches)) {
                return array('lynx' => $matches[1]);
            }
        } elseif (strpos($useragent, 'Netscape') !== FALSE) {
            //NN8 with IE string
            if (preg_match("/Netscape\/([0-9]{1}\.[0-9]{1}(\.[0-9])?)/", $useragent, $matches)) {
                return array('netscape' => $matches[1]);
            }
        } else {
            //unrecognized, this should be less than 1% of browsers (not counting bots like google etc)!
            return 'unknown';
        }
    }

    //判断是否为企业微信
    static function is_wxwork()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'wxwork') !== false) {
            return true;
        } else {
            return false;
        }
    }

    static function ismobile()
    {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (preg_match("/WindowsWechat/i", $agent)) {
            return false;//return 'WindowsWechat';pc微信客户端打开pc版
        }
        elseif (preg_match("/macintosh/i", $agent) && preg_match("/MicroMessenger/i", $agent)) {
             return false;//苹果电脑系统pc端
        }
        elseif (preg_match("/MicroMessenger/i", $agent)) {
            return 'wechat';
        }
        elseif (preg_match("/iphone/i", $agent) && preg_match("/mac os/i", $agent)) {
            return 'iPhone';
        } elseif (preg_match("/ipod/i", $agent) && preg_match("/mac os/i", $agent)) {
            return 'iPod';
        } elseif (preg_match("/linux/i", $agent) && preg_match("/Android/i", $agent)) {
            return 'Android';
        }
        return false;
    }

    //A function to determine the platform we are on.
    //判断平台的种类

    static function getplatform()
    {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $os = array();;

        if (preg_match("/win/i", $agent) && preg_match('/nt 5.1/', $agent)) {
            $os = array('Windows' => 'XP');
        } elseif (preg_match('win', $agent) && preg_match('/nt 5.0/', $agent)) {
            $os = array('Windows' => '2000');
        } elseif (preg_match('win', $agent) && preg_match("/nt 5.2/i", $agent)) {
            $os = array('Windows' => '2003');
        } elseif (preg_match("/win/i", $agent) && preg_match("/nt 6.0/i", $agent)) {
            $os = array('Windows' => '2008');
        } elseif (preg_match("/win/i", $agent) && preg_match("/6.0/i", $agent)) {
            $os = array('Windows' => 'vasta');
        } elseif (preg_match("/win/i", $agent) && preg_match("/6.1/i", $agent)) {
            $os = array('Windows' => '7');
        } elseif (preg_match("/win/i", $agent) && preg_match("/6.2/i", $agent)) {
            $os = array('Windows' => '8');
        } elseif (preg_match("/win/i", $agent) && preg_match("/nt 6.3/i", $agent)) {
            $os = array('Windows' => '8.1');
        } elseif (preg_match("/win/i", $agent) && preg_match("/nt/i", $agent)) {
            $os = array('Windows' => 'nt');
        } elseif (preg_match("/ipad/i", $agent) && preg_match('/mac os/i', $agent)) {
            $os = array('iPad' => true);
        } elseif (preg_match("/iphone/i", $agent) && preg_match('/mac os/i', $agent)) {
            $os = array('iPhone' => true);
        } elseif (preg_match("/ipod/i", $agent) && preg_match('/mac os/i', $agent)) {
            $os = array('iPod' => true);
        } elseif (preg_match("/linux/i", $agent) && preg_match('/Android/i', $agent)) {
            $os = array('Android' => true);
        } elseif (preg_match("/linux/i", $agent)) {
            $os = array('Linux' => true);
        } elseif (preg_match("/unix/i", $agent)) {
            $os = array('Unix' => true);
        } elseif (preg_match("/Mac/i", $agent) && preg_match("/Macintosh/i", $agent)) {
            $os = array('Macintosh' => true);
        }


        return $os;
    }
}

?>
