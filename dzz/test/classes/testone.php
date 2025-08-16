<?php
namespace   dzz\test\classes;//命名空间防止协助开发冲突
use \core as C;//调用C数据模型调用
use \DB as DB;//调用底层数据库基类
class Testone{
    public function run( $arr=array() ){//默认 run
       echo '<a  style="margin:0 5px" href="javascript:;" title="'.lang('info_test_hook_one').'">'.lang( 'info_test_hook_one') .'</a>';
    } 
    
    /* 通过挂载点test_diaoyong调用钩子程序下的testDiaoyong函数，如果testDiaoyong程序不存在，默认调用钩子程序下run函数
    */
    public function testDiaoyong( $arr=array() ){
        global $_G; 
        echo '<a  style="margin:0 5px" href="javascript:;" title="'.lang('info_test_hook_two').'">'.lang( 'info_test_hook_two') .'</a><br/>';
        $data=C::t('#test#test')->fetchall();// 此处本应该是 C::C::t('test')->fetchall(); 换成 #test#test 主要还是是防止在其他应用里面调用该钩子程序时数据模型找不到的错误。
        echo( var_export($data,true) );
    }
}