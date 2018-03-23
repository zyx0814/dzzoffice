<?php
namespace core\dzz;
class Hook
{

    private static $tags = array();
    public static $usetag = array();
    /**
     * 动态添加行为扩展到某个标签
     * @param string    $tag 标签名称
     * @param mixed     $behavior 行为名称
     * @param mixed     $behavior 行为名称
     * @param bool      $first 是否放到开头执行
     * @return void
     */
    public static function add($tag, $behavior, $first = false)
    {
        isset(self::$tags[$tag]) || self::$tags[$tag] = array();
        if (is_array($behavior) && !is_callable($behavior)) {//此处废弃，暂不调整
            if (!array_key_exists('_overlay', $behavior) || !$behavior['_overlay']) {
                unset($behavior['_overlay']);
                self::$tags[$tag] = array_merge(self::$tags[$tag], $behavior);
            } else {
                unset($behavior['_overlay']);
                self::$tags[$tag] = $behavior;
            }
        } elseif ($first) {
            array_unshift(self::$tags[$tag], $behavior);
        } else {
            self::$tags[$tag][] = $behavior;
        }
		 self::$tags[$tag]=array_unique( self::$tags[$tag]);
    }

    /**
     * 批量导入插件
     * @param array        $tags 插件信息
     * @param boolean     $recursive 是否递归合并
     */
    public static function import(array $tags, $recursive = true)
    {
        if ($recursive) {
            foreach ($tags as $tag => $behavior) {
                self::add($tag, $behavior);
            }
        } else {
            self::$tags = $tags + self::$tags;
        }
    }

    /**
     * 获取插件信息
     * @param string $tag 插件位置 留空获取全部
     * @return array
     */
    public static function get($tag = '')
    {
        if (empty($tag)) {
            //获取全部的插件信息
            return self::$tags;
        } else {
            return array_key_exists($tag, self::$tags) ? self::$tags[$tag] : array();
        }
    }

    /**
     * 监听标签的行为
     * @param string $tag    标签名称
     * @param mixed  $params 传入参数
     * @param mixed  $extra  额外参数
     * @param bool   $once   只获取一个有效返回值
     * @return mixed
     */
    public static function listen($tag, &$params = null, $extra = null,$once = false)
    {
        $results = array();

        $tags    = static::get($tag);

        $break = false;

        foreach ($tags as $key => $name) {

            if(is_array($name)){
                foreach($name as $val){
                    $results[$key] = self::exec($val, $tag, $params, $extra,$break);

                    if (false === $results[$key] || $break == true) {
                        break;

                    } elseif($once) {

                        break;
                    }
                }

            }else{


                $results[$key] = self::exec($name, $tag, $params, $extra,$break);

                if (false === $results[$key] || $break == true) {

                    // 如果返回false 则中断行为执行
                    break;

                } elseif ($once) {

                    break;

                }

            }

        }

        return $once ? end($results) : $results;
    }

    /**
     * 执行某个行为
     * @param mixed     $class 要执行的行为
     * @param string    $tag 方法名（标签名）
     * @param Mixed     $params 传人的参数
     * @param mixed     $extra 额外参数
     * @return mixed
     */
    public static function exec($class, $tag = '', &$params = null,$extra = null,&$break)
    {
        if(strpos($class,'|') !== false){//判断是否规定了作用域，并判断作用域确定是否执行钩子
            $rangArr = explode('|',$class);
            $class = $rangArr[0];
            $range = $rangArr[1];
            if(defined('CURMODULE')){
                $execrange = CURSCRIPT.'/'.CURMODULE;
            }else{
                $execrange = CURSCRIPT;
            }
			
           if(strpos($execrange,$range) !== 0){
                return true;
            }
        }
        //self::$usetag[] = $tag;
        $method = static::parseName($tag, 1, false);
        if ($class instanceof \Closure) {

            $result = call_user_func_array($class, array( & $params, $extra));
            $class  = 'Closure';

        } elseif (is_array($class)) {
            list($class, $method) = $class;
            $classobj = new $class();
            $result = $classobj->$method($params, $extra);
            //$result = (new $class())->$method($params, $extra);
            //$result = call_user_func_array(array($class,$method), array( & $params, $extra));
            $class  = $class . '->' . $method;

        } elseif (is_object($class)) {

            $result = $class->$method($params, $extra);
            $class  = get_class($class);

        } elseif (strpos($class, '::')) {

            $result = call_user_func_array($class, array( & $params, $extra));

        } else {
            $obj    = new $class();
            $method = ($tag && is_callable(array($obj, $method))) ? $method : 'run';
            $result = $obj->$method($params, $extra,$break);

        }
        return $result;
    }

    public static function parseName($name, $type = 0, $ucfirst = true)
    {
        if ($type) {
            $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name);
            return $ucfirst ? ucfirst($name) : lcfirst($name);
        } else {
            return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
        }
    }
}
