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
	 return array(  'delete'	=>	1,       // 不允许删除；
					'edit'     	=>	2,		//不允许编辑	
					'rename'   	=>	4,		//不允许重命名
					'move'		=>	8,		//不允许移动
					'download'	=>	16,		//不允许下载
					'share'		=>	32,		//不允许分享
					'widget'	=>	64,		//不允许添加挂件
					'wallpaper'	=>	128,	//不允许设为壁纸
					'copy'      =>  256,	//不允许拷贝
					'shortcut'  =>  512,    //不允许创建快捷方式
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
		case 'document'://('wallpaper','widget','share','edit');
		    return self::getSumByAction(array('wallpaper','widget'));
		case 'attach'://edit,widget,wallpaper
			return self::getSumByAction(array('wallpaper','widget'));
		case 'shortcut': case 'app'://不能设为壁纸，不能下载，不能编辑
			return self::getSumByAction(array('wallpaper','widget','download','share'));
		case 'user'://不能设为壁纸，不能下载，不能编辑，不能设为挂件，不能分享
			return self::getSumByAction(array('wallpaper','widget','download','share'));
		case 'dzzdoc': //edit
			return self::getSumByAction('wallpaper','widget','download');
		case 'video': case 'music':case 'link'://edit,down,wallpaper
			return self::getSumByAction(array('wallpaper','widget','download'));
		case 'folder': //edit，widget,wallpaper
			return self::getSumByAction(array('wallpaper','widget'));
		  case 'attachment': //通过attach::xxx和dzz://方式获取的文件不给编辑权限
			return self::getSumByAction(array('edit','rename','move','wallpaper','widget'));
		default:
			return 0;
	  }
  }
  public function flagPower($flag){ //返回默认目录的权限 
	  switch($flag){
		  case 'home':case 'document': case 'image': case 'video': case 'music': case 'app':case 'desktop':case 'dock':
			return self::getSumByAction(array('delete','wallpaper','widget','share','edit','copy','rename','move','download'));
		case 'recycle':
			return self::getSumByAction(array('delete','wallpaper','widget','share','edit','copy','rename','move','download'));
		default:
			return self::getSumByAction(array('wallpaper','widget','share','edit','copy','rename','delete','move'));
	  }
  }
}  
?>
