<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

class perm_check{

    function getuserPerm(){
        global $_G;
        $perm= DB::result_first("select perm from %t where uid=%d",array('user_field',$_G['uid']));
        $groupperm=$_G['group']['perm'];
        if($perm<1) $perm=intval($_G['group']['perm']);
        if($_G['setting']['allowshare']){
            $power=new perm_binPerm($perm);
            $perm=$power->delPower('share');
        }
        return $perm;
    }
	
    function getPerm($fid, $bz='',$i=0){
        global $_G;
		if(isset($_G['gperm'])) return intval($_G['gperm']);//可以通过这个参数直接使用此权限值不去查询权限
		
        $i++;
        if($i>20){ //防死循环，如果循环20次以上，直接退出；
            return perm_binPerm::getGroupPower('read');
        }

        if($folder=C::t('folder')->fetch($fid)){
            $perm=intval($folder['perm']);
            $power=new perm_binPerm($perm);
            if($folder['gid']){
                if(C::t('organization_admin')->chk_memberperm($folder['gid'],$_G['uid'])) return (perm_binPerm::getGroupPower('all'));

                if($power->isPower('flag')){//不继承，使用此权限
                    if($_G['setting']['allowshare']){
                        $perm=$power->delPower('share');
                    }
                    $power1=new perm_binPerm($perm);
                    return $power1->power;//mergePower(self::getuserPerm());
                }else{ //继承上级，查找上级
                    if($folder['pfid']>0 && $folder['pfid']!=$folder['fid']){ //有上级目录
                        //return self::getPerm($folder['pfid'],$bz,$i);
                        $perm=self::getPerm($folder['pfid'],$bz,$i);
                        $power1=new perm_binPerm($perm);
                        return $power1->power;//mergePower(self::getuserPerm());
                    }else{   //其他的情况使用
                        return perm_binPerm::getGroupPower('read');
                    }
                }
            }else{
				if($folder['uid']==$_G['uid'] || $_G['adminid']==1) return perm_binPerm::getGroupPower('all');
                if($power->isPower('flag')){//不继承，使用此权限
                    if($_G['setting']['allowshare']){
                        $perm=$power->delPower('share');
                    }
                    $power1=new perm_binPerm($perm);
                    return $power1->mergePower(self::getuserPerm());
                }else{ //继承上级，查找上级
                    if($folder['pfid']>0 && $folder['pfid']!=$folder['fid']){ //有上级目录
                        return self::getPerm($folder['pfid'],$bz,$i);
                    }else{   //其他的情况使用
                    	return self::getuserPerm();
                    }
                }
            }
        }else{
            return perm_binPerm::getGroupPower('read');
        }
    }

    function getPerm1($fid, $bz='',$i=0,$newperm = 0){
        global $_G;

        $i++;
        if($i>20){ //防死循环，如果循环20次以上，直接退出；
            return perm_binPerm::getGroupPower('all');
        }
        if($folder=C::t('folder')->fetch($fid)){
			$perm=($newperm) ? intval($newperm):intval($folder['perm']);
            if($folder['gid']){
                $power=new perm_binPerm($perm);
                if($power->isPower('flag')){//不继承，使用此权限
                    return $perm;
                }else{ //继承上级，查找上级
                    if($folder['pfid']>0 && $folder['pfid']!=$folder['fid']){ //有上级目录
                        return self::getPerm1($folder['pfid'],$bz,$i,$newperm);
                    }else{   //其他的情况使用
						return perm_binPerm::getGroupPower('read');
						
                    }
                }
           }else{
				$power=new perm_binPerm($perm);
                if($power->isPower('flag')){//不继承，使用此权限
                    if($_G['setting']['allowshare']){
                        $perm=$power->delPower('share');
                    }
                    $power1=new perm_binPerm($perm);
                    return $power1->mergePower(self::getuserPerm());;
                }else{ //继承上级，查找上级
                    if($folder['pfid']>0 && $folder['pfid']!=$folder['fid']){ //有上级目录
                        return self::getPerm1($folder['pfid'],$bz,$i);
                    }else{   //其他的情况使用
                    	return self::getuserPerm();
                    }
                }
            }
        }else{
            return perm_binPerm::getGroupPower('read');
        }
    }

    function userPerm($fid,$action){ //判断容器有没有指定的权限
        global $_G;
        if($_G['adminid']==1){ //是管理员
            return true;
        }

        if(!$_G['uid']){ //如果不是登录用户，返回false;
            return false;
        }
       
        //if($action=='download' || $action=='saveto' || $action=='copy' ) return true;
        $perm=self::getPerm($fid);
        //exit($perm.'===='.$action);
        return perm_binPerm::havePower($action,$perm);
       /* if($perm<5){
            if($action=='view') return true;
            else return false;
        }
        return true;*/
    }
    function groupPerm($fid,$action,$gid){ //判断容器有没有指定的权限
        global $_G;
		
        $ismoderator=C::t('organization_admin')->chk_memberperm($gid,$_G['uid']);
        if($action=='admin' && !$ismoderator)  return false;
        if($ismoderator){ //是部门管理员或上级部门管理员
            return true;
        }
		 //不是部门成员或下级部门成员没有权限
         if(!C::t('organization')->ismember($gid,$_G['uid'],false)) return false;
        //if($action=='download' || $action=='saveto' || $action=='copy' ) return true;
		
        $perm=self::getPerm($fid);
        //exit($perm.'====='.$fid.'======='.$gid.'===='.$action);
        if($perm>0){
            $power=new perm_binPerm($perm);
            //exit($perm.'====='.$fid.'======='.$gid.'===='.$action.'==='.$power->isPower($action));
            return $power->isPower($action);
        }

        return false;
    }

    //$arr=array('uid','gid','desktop');其中这几项必须
    function checkperm($action,$arr,$bz=''){ //检查某个图标是否有权限;
        global $_G;
        if($_G['uid']>0 && $_G['adminid']==1) return true; //网站管理员 有权限;
        if ($arr['sid']) {
            $share = C::t('shares')->fetch($arr['sid']);
            if ($share) {
                if ($share['status'] == -4) exit(lang('shared_links_screened_administrator'));
                if ($share['status'] == -5) exit(lang('sharefile_isdeleted_or_positionchange'));
                if ($share['endtime'] && $share['endtime'] < TIMESTAMP) {
                    exit(lang('share_link_expired'));
                }
                if ($share['status'] == -3) {
                    exit(lang('share_file_deleted'));
                }
                if ($share['perm']) {
                    $perms = array_flip(explode(',', $share['perm'])); // 将权限字符串转换为数组
                    if (isset($perms[3]) && $_G['uid']<1) { // 3 表示仅登录使用
                        return false; // 未登录，返回 false
                    }
                    if ($action == 'read') {
                        if (isset($perms[2])) { // 2 表示禁用预览权限
                            return false; // 预览权限被禁用，返回 false
                        } else {
                            return true; // 其他情况，默认允许访问
                        }
                    } elseif ($action == 'edit' && isset($perms[4])) {
                        return true; // 编辑权限
                    } elseif ($action == 'download' && isset($perms[1])) {
                        return false; // 下载权限被禁用
                    }
                } else {
                    if ($action == 'download' || $action == 'read') {
                        return true; // 默认允许下载和预览
                    }
                }
            } else {
                return false; // 资源不存在
            }
        }
        if ($arr['preview'] && ($action=='read') || $action=='copy' || $action=='download') {
            return true;
        }
        if($_G['uid']<1){ //游客没有权限
            return false;
        }
        if (!$arr['gid'] && $arr['uid'] !== $_G['uid']) {//我的网盘文件只限于当前用户
            return false;
        }
        if(($bz && $bz!='dzz') || ($arr['bz'] && $arr['bz']!='dzz')){
            return self::checkperm_Container($arr['pfid'],$action,$bz?$bz:$arr['bz']);
        }else{
			 if($action=='rename'){
                $action='edit';
            }
            if(in_array($action,array('read','delete','edit','download','copy'))){
                if($_G['uid']==$arr['uid']) $action.='1';
                else $action.='2';
            }
            //首先判断ico的超级权限；
            if(!perm_FileSPerm::isPower($arr['sperm'],$action)) return false;

            if($folder=C::t('folder')->fetch_by_fid($arr['pfid'])){
                //首先判断目录的超级权限；
                if(!perm_FolderSPerm::isPower($folder['fsperm'],$action)) return false;
            }
            return self::checkperm_Container($arr['pfid'],$action,$bz);
        }
    }
    function checkperm_Container($pfid,$action='',$bz=''){ //检查容器是否有权限操作;
        global $_G;
        if($_G['uid']<1){ //游客没有权限
            return false;
        }
        if($bz){
            if(!perm_FolderSPerm::isPower(perm_FolderSPerm::flagPower($bz),$action)) return false;
            return true;
        }else{
            if($folder=C::t('folder')->fetch($pfid)){
			//首先判断目录的超级权限；
				if($action=='rename'){
					$action='edit';
				}
				if(in_array($action,array('read','delete','edit','download','copy'))){
					if($_G['uid']==$folder['uid']) $action.='1';
					else $action.='2';
				}
                if(!perm_FolderSPerm::isPower($folder['fsperm'],$action)) return false;
                //默认目录只有管理员有权限改变排列
                //if($action=='admin' && $_G['adminid']!=1 && $folder['flag']!='folder') return false;
            }
            if($_G['adminid']==1) return true; //网址管理员 有权限;

            if($folder['gid']){
                return self::groupPerm($pfid,$action,$folder['gid']);
            }else{
                return self::userPerm($pfid,$action);
            }
        }
    }
}
