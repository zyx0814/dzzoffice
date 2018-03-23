<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/2/9
 * Time: 11:33
 */
namespace core\dzz;

class HookRead
{

    public static $tags = array();//钩子数组

    public static $tagcachefile = '';//钩子列表缓存文件

    private static $cacheTagFileCtime = 0;//钩子列表缓存文件生成时间

    /*初始化
     * @description 增加系统预埋钩子
     * CACHE_DIR 缓存目录
     * */
    public static function _init()
    {
        self::$tagcachefile = CACHE_DIR . BS . 'tags' . EXT;//钩子列表缓存文件路径

        if (file_exists(self::$tagcachefile)) {

            self::$tags = is_array(include self::$tagcachefile) ? include self::$tagcachefile:array();

            self::$cacheTagFileCtime = filectime(self::$tagcachefile);
        }

        self::readTags();//读取系统预埋钩子

        if(file_exists(self::$tagcachefile)){//判断钩子列表缓存文件是否生成

            self::$tags = is_array(include self::$tagcachefile) ? include self::$tagcachefile:array();//读取钩子列表缓存文件

            self::$tags = self::tagsArrayParse(self::$tags);//缓存钩子列表文件内容处理

            Hook::import(self::$tags);//导入钩子
        }

    }


    /*
     * @description 递归读取所有的钩子列表文件(根目录，应用目录，模块目录)
     * @$dirname 默认为根目录
     * @$deep 读取文件层级
     * @return null
     * */
    private static function readTags($dirname = DZZ_ROOT,$deep = 0)
    {
        if(!is_dir($dirname)){

            return false;
        }

        $dirname = rtrim($dirname,BS);

        $deep++;

        $openfile = opendir($dirname);

        while (($file = readdir($openfile)) !== false) {

            if ($file != '.' && $file != '..' && is_dir($dirname.BS.$file) && strpos($file,'.') !== 0) {

                $dir = $dirname .BS. $file;


                if($file == CONFIG_NAME){

                    $tagfile = $dir.BS. 'tags' . EXT;

                    if (file_exists($tagfile)) {

                        if ( !file_exists(self::$tagcachefile) || (filemtime($tagfile) > self::$cacheTagFileCtime)) {

                            $key = str_replace(array(DZZ_ROOT,CONFIG_NAME.BS),'',$dir);

                            $tagsArr = include $tagfile;

                            if(is_array($tagsArr)){

                                self::$tags[$key] = $tagsArr;

                                $writestr = "<?php \t\n return ";

                                $writestr .= var_export(self::$tags,true).";";

                                $fp = fopen(self::$tagcachefile,'w+');

                                fwrite($fp,$writestr);

                                fclose($fp);
                            }
                        }

                    }
                }else {
                    if($deep <= 3){

                        self::readTags($dir,$deep);
                    }

                }
            }
        }
        closedir($openfile);
    }
    /*@description钩子缓存数组处理函数
     * @$arr array
     * @return array
     * */
    private static function tagsArrayParse($arr = null){

        if(!is_array($arr)) return false;

        $tagArrReturn = array();

        foreach($arr as $v){

            foreach($v as $key=>$val){

                $keyArr = array_keys($val);

                foreach($keyArr as $k=>$value){//取出所有键，加上p

                    $keyArr[$k] = 'p'.$value;
                }

                $val = array_combine($keyArr,$val);//用新键值组成数组

                if(isset($tagArrReturn[$key])){

                    $tagArrReturn[$key] = array_merge_recursive($tagArrReturn[$key],$val);

                }else{

                    $tagArrReturn[$key] = $val;
                }

            }
        }

        //去掉键所加的p,并对数组进行排序
        foreach($tagArrReturn as $item=>$ival){

            $pkey = array_keys($ival);

            foreach ($pkey as $n=>$ivalue){

                $pkey[$n] = str_replace('p','',$ivalue);
            }

            $ival = array_combine($pkey,$ival);

            krsort($ival,SORT_NUMERIC);

            $tagArrReturn[$item] = $ival;

        }
        return $tagArrReturn;

    }
}