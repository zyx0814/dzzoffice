<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

//php 权限控制类
	
/*权限表说明:
位   描述       	权限值  描述
1    delete			1       不允许删除；
2 	 edit     		2		不允许编辑	
3 	 rename   		4		不允许重命名
4	 move			8		不允许移动
5 	 down			16		不允许下载
6	 share			32		不允许分享
7 	 widget			64		不允许添加挂件
8 	 wallpaper		128		不允许设为壁纸



有新的权限在这里添加 
由于数据库存储是smallint(6),最大支持16位权限；（32位系统最多支持32位，64位系统最多支持64位；
 */ 
class perm_FileSPerm{  

  public function getPowerarr(){
	 return array(   
		   'flag' => 1,        //标志位为1表示权限设置,否则表示未设置，继承上级；
            'read1' => 2,        //读取自己的文件
            'read2' => 4,        //读取所有文件
            'delete1' => 8,        //删除自己的文件
            'delete2' => 16,        //删除所有文件
            'edit1' => 32,        //编辑自己的文件
            'edit2' => 64,        //编辑所有文件
            'download1' => 128,        //下载自己的文件
            'download2' => 256,        //下载所有文件
            'copy1' => 512,        //拷贝自己的文件
            'copy2' => 1024,    //拷贝所有文件
            'upload' => 2048,    //新建和上传
            //'newtype' => 4096,    //新建其他类型文件（除文件夹以外）
            'folder' => 8192,    //新建文件夹
            //'link' => 16384,    //新建网址
            //'dzzdoc' => 32768,    //新建dzz文档
            //'video' => 65536,    //新建视频
           // 'shortcut' => 131072,    //快捷方式
            'share' => 262144,    //分享
            'approve' => 524288,//审批
			);
  } 
  public function getPerm($action){
	 $powerarr=self::getPowerarr();
	 return  isset($powerarr[$action])?intval($powerarr[$action]):0;
  }
   public function getSumByAction($action=array()){ //$action==all 时返回所有的值相加
	  $i=0;
	  $powerarr=self::getPowerarr();
	  if($action=='all'){
		  foreach($powerarr as $key=> $val){
				  $i+=$val;
		  }
	  }else{
		 foreach($action as $val){
			 $i+=intval($powerarr[$val]);
		 }
	  }
	  return $i;
  }
  public function isPower($perm,$action){  
    //权限比较时，进行与操作，得到0的话，表示没有权限
	if(self::getPerm($action)<1) return true;
    if((intval($perm) & self::getPerm($action)) == self::getPerm($action) ) return false;  
    return true;  
  }  
 
 public function typePower($type,$ext=''){ //返回类型的权限
 	global $textexts;
	  switch($type){
		default:
			return 0;
	  }
  }
  public function flagPower($flag){ //返回默认目录的权限 
	  switch($flag){
		  case 'home':case 'document': case 'image': case 'video': case 'music': case 'app':case 'desktop':case 'dock':
			return self::getSumByAction(array('delete1','delete2','share','edit1','edit2','copy1','copy2','download1','download2'));
		case 'recycle':
			return self::getSumByAction(array('delete1','delete2','share','edit1','edit2','copy1','copy2','folder','upload','download1','download2'));
		default:
			return 0;
	  }
  }
}  
?>
