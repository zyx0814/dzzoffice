<?php
/*
* @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
* @license     http://www.dzzoffice.com/licenses/license.txt
* @package     DzzOffice
* @version     DzzOffice 1.1 2014.07.05
* @link        http://www.dzzoffice.com
* @author      zyx(zyx@dzz.cc)
*/
/*php 权限控制类
 *有新的权限在这里添加 
 *由于数据库存储是smallint(10),最大支持32位权限；(32位系统最多支持32位，64位系统最多支持64位；)
*/ 
class perm_binPerm{  
	
	var $power = "";  //权限存贮变量,十进制整数
	//共享文件夹权限表； 
	
	
	
	function __construct($power){  
		$this->power = intval($power);  
		$this->powerarr=$this->getPowerArr();
	} 
	
	function getPowerArr(){
		return  array('flag' 		=> 1,		//标志位为1表示权限设置,否则表示未设置，继承上级；
					  'read1'		=> 2,		//读取自己的文件
					  'read2'		=> 4,		//读取所有文件
					  'delete1'		=> 8,		//删除自己的文件
					  'delete2'		=> 16,		//删除所有文件
					  'edit1'		=> 32,		//编辑自己的文件
					  'edit2'		=> 64,		//编辑所有文件
					  'download1'   => 128,		//下载自己的文件
					  'download2'	=> 256,		//下载所有文件
					  'copy1'       => 512,		//拷贝自己的文件
					  'copy2'       => 1024,	//拷贝所有文件
					  'upload'		=> 2048,	//上传
					  'newtype'		=> 4096,	//新建其他类型文件（除文件夹、网址、dzz文档、视频、快捷方式以外）
					  'folder'      => 8192,	//新建文件夹
					  'link'    	=> 16384,	//新建网址
					  'dzzdoc'   	=> 32768,	//新建dzz文档  
					  'video'		=> 65536,	//新建视频
					  'shortcut'	=> 131072,	//快捷方式
					  'share'   	=> 262144,	//分享
					
					);
	}
	function getPowerTitle(){
		return array( 
					  'flag' 		=> '标志位为1表示权限设置,否则表示未设置，继承上级',
					  'read1'		=> '读取自己的文件',
					  'read2'		=> '读取所有文件',
					  'delete1'		=> '删除自己的文件',
					  'delete2'		=> '删除所有文件',
					  'edit1'		=> '编辑自己的文件',
					  'edit2'		=> '编辑所有文件',
					  'download1'   =>'下载自己的文件',
					  'download2'   =>'下载所有文件',
					  'copy1'       =>'复制自己的文件',
					  'copy2'  		=>'复制所有文件',
					  'upload'		=> '上传',
					  'newtype'		=> '新建其他类型文件（除文件夹、网址、文档、视频以外）',
					  'folder'      => '新建文件夹',
					  'link'        => '新建网址',
					  'dzzdoc'      => '新建文档', 
					  'video'	    => '新建视频',
					  'shortcut'	=> '快捷方式',
					  'share'	    => '分享',
					);
	}
	function getMyPower(){//获取用户桌面默认的权限
		return self::getSumByAction(array('read1','read2','delete1','edit1','download1','download2','copy1','copy2','upload','newtype','folder','link','dzzdoc','video','shortcut','share'));
	}
	function groupPowerPack(){
		$data= array('read'         =>array('title' =>'只读', 'flag'=>'read', 'permitem'=>array('read1','read2'),'tip'=>'只允许成员查看由管理员添加的内容，不能上传、新建、复制、编辑、删除内容。'),
					 'only-download'=>array('title' =>'仅下载','flag'=>'only-download', 'permitem'=>array('read1','read2','download1','download2','copy1','copy2'),'tip'=>'只允许成员查看由管理员添加的内容。可以下载、拷贝内容。'),
					 'read-write1'  =>array('title' =>'读写1', 'flag'=>'read-write1', 'permitem'=>array('read1','read2','delete1','edit1','download1','copy1','upload','newtype','folder','link','dzzdoc','video'),'tip'=>'允许成员上传、新建、编辑、复制、查看、删除自己的内容，不能编辑、复制、删除其他成员添加的内容'),
					 'read-write2'  =>array('title' =>'读写2', 'flag'=>'read-write2', 'permitem'=>array('read1','read2','delete1','edit1','edit2','download1','download2','copy1','copy2','upload','newtype','folder','link','dzzdoc','video'),'tip'=>'允许成员上传、新建、编辑、复制、查看、删除自己的内容，不能删除其他成员的内容。'),
					 'only-write1'  =>array('title' =>'只写', 'flag'=>'only-write1',  'permitem'=>array('read1','upload','newtype','folder','link','dzzdoc','video'),'tip'=>'只允许成员上传、新建、添加内容。成员只能查看自己添加的内容。不能编辑、删除内容。只写目录下的只能创建只写目录（管理员也不例外）。'),
					 'all'          =>array('title' =>'完全控制','flag'=>'all', 'permitem'=>'all','tip'=>'允许成员上传、新建、添加、编辑、复制、查看、删除所有内容，包括其他成员的内容。')
			  );
		foreach($data as $key=>$value){
			$data[$key]['power']=self::getSumByAction($value['permitem']);
		}
		return $data;
	}
	function addPower($action){ 
	 
		//利用逻辑或添加权限
	 	if(isset($this->powerarr[$action])) return $this->power = $this->power | intval($this->powerarr[$action]);  
	}  
	
	function mergePower($perm) { //合成权限，使用于系统权限和用户权限合成
	 	return $this->power = intval($this->power & intval($perm));  
	}
	
	function delPower($action){  
		//删除权限，先将预删除的权限取反，再进行与操作  
		if(isset($this->powerarr[$action]))  return $this->power = $this->power & ~intval($this->powerarr[$action]);  
	}  
	
	function isPower($action){  
		//权限比较时，进行与操作，得到0的话，表示没有权限
		if(!$this->powerarr[$action]) return 0;
		return $this->power & intval($this->powerarr[$action]);
	}  
	
	function returnPower(){  
		//为了减少存贮位数，返回也可以转化为十六进制  
		return $this->power;  
	}
	
	
	function havePower($action,$perm){  
		//权限比较时，进行与操作，得到0的话，表示没有权限
		$perm=intval($perm);
		$powerarr=self::getPowerArr();
		if(!$powerarr[$action]) return 0;
		if(!$perm) return 0;
		return $perm & intval($powerarr[$action]);
	}  
	function getSumByAction($action=array()){ //$action==all 时返回所有的值相加
		$i=0;
		$powerarr=self::getPowerArr();
		if($action=='all'){
			foreach($powerarr as $key=> $val){
				$i+=$val;
			}
		}else{
			$i=1;
			foreach($action as $val){
				$i+=intval($powerarr[$val]);
			}
		}
		if(getglobal('setting/allowshare')){
			$power=new perm_binPerm($i);
			$i=$power->delPower('share');
		}
		return $i;
	}
	function getGroupPower($type){ //权限包
		$data=self::groupPowerPack();
		return $data[$type]['power'];
	}
	function getGroupTitleByPower($power){
		$data=self::groupPowerPack();
		foreach($data as $key=>$value){
			if($value['power']==$power) return $value;
		}
	   return $data['read'];
	}
	
}  
?>
