<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
include libfile('function/organization');
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;
$uid = $_G['uid'];
if($_G['adminid'] != 1){
    showmessage(lang('no_privilage'));
}
$do = isset($_GET['do']) ?  trim($_GET['do']):'';
if($do == 'updatesetting'){//更新设置
    include_once libfile('function/cache');
    $setting = $_GET['setting'];
	$setarr=array(
		'explorer_usermemoryOn' => (isset($setting['explorer_usermemoryOn']) && $setting['explorer_usermemoryOn'] == 'on' )?1:0,
		'explorer_mermoryusersetting' => $setting['explorer_mermoryusersetting'],
		'explorer_memoryorgusers' => $setting['explorer_memoryorgusers'],
		'explorer_organizationOn' => (isset($setting['explorer_organizationOn']) && $setting['explorer_organizationOn'] == 'on' )?1:0,//isset($setting['organizationOn'])?$setting['organizationOn']:'',
		'explorer_groupOn' =>  (isset($setting['explorer_groupOn']) && $setting['explorer_groupOn'] == 'on' )?1:0,//isset($setting['groupOn'])?$setting['groupOn']:'',
        'explorer_groupcreate'=>(isset($setting['explorer_groupcreate']) && $setting['explorer_groupcreate'] == 'on' )?1:0,
        'explorer_mermorygroupsetting' => $setting['explorer_mermorygroupsetting'],
        'explorer_memorygroupusers' => $setting['explorer_memorygroupusers'],
        'explorer_catcreate'=>(isset($setting['explorer_catcreate']) && $setting['explorer_catcreate'] == 'on' )?1:0,
        'explorer_finallydelete'=>(isset($setting['explorer_finallydelete']))?$setting['explorer_finallydelete']:-1
	);
    if(C::t('setting')->update_batch($setarr)){
        updatecache('setting');
        exit(json_encode(array('success'=>true,'msg'=>lang('update_setting_success'))));
    }else{
        exit(json_encode(array('error'=>true,'msg'=>lang('update_setting_failed'))));
    }
}else{
    //查询所有设置
    $setting = C::t('setting') -> fetch_all(
        array(
            'explorer_usermemoryOn',
            'explorer_mermoryusersetting',
            'explorer_memoryorgusers',
            'explorer_memorySpace',
            'explorer_organizationOn',
            'explorer_groupOn',
            'explorer_groupcreate',
            'explorer_mermorygroupsetting',
            'explorer_memorygroupusers',
            'explorer_catcreate',
            'explorer_finallydelete'
        ));
	//处理指定空间人员
	if($setting['explorer_memoryorgusers']){
			$muids=explode(',',$setting['explorer_memoryorgusers']);
		}
		$orgids=$uids=$sel_org=$sel_user=array();
		foreach($muids as $value){
			if(strpos($value,'uid_')!==false){
				$uids[]=str_replace('uid_','',$value);
			}else{
				$orgids[]=$value;
			}
		} 
		$open=array();
		if($orgids){
			$sel_org=C::t('organization')->fetch_all($orgids);
			
			foreach($sel_org  as $key=> $value){
				$orgpath=getPathByOrgid($value['orgid']);
				$sel_org[$key]['orgpath']=implode('-',($orgpath));
				$arr=(array_keys($orgpath));
				//print_r($arr);
				array_pop($arr);
				if($count=count($arr)){
					if($open[$arr[$count-1]]){
						if(count($open[$arr[$count-1]])>$count) $open[$arr[count($arr)-1]]=$arr;
					}else{
						$open[$arr[$count-1]]=$arr;
					}
				}
			}
			if(in_array('other',$orgids)){
				$sel_org[]=array('orgname'=>lang('no_org_user'),'orgid'=>'other','forgid'=>1);
			}
		}
		
		if($uids){
			$sel_user=C::t('user')->fetch_user_avatar_by_uids($uids);
			if($aorgids=C::t('organization_user')->fetch_orgids_by_uid($uids)){
				foreach($aorgids as $orgid){
					$arr= C::t('organization')->fetch_parent_by_orgid($orgid,true);
					
					if($count=count($arr)){
						if($open[$arr[$count-1]]){
							if(count($open[$arr[$count-1]])>$count) $open[$arr[count($arr)-1]]=$arr;
						}else{
							$open[$arr[$count-1]]=$arr;
						}
					}
				 }
			}
		}
    if($setting['explorer_memorygroupusers']){
        $muids=explode(',',$setting['explorer_memorygroupusers']);
    }
    $orgids1=$uids1=$sel_org1=$sel_user1=array();
    foreach($muids as $value){
        if(strpos($value,'uid_')!==false){
            $uids1[]=str_replace('uid_','',$value);
        }else{
            $orgids1[]=$value;
        }
    }
    //新建群组用户
    $open1=array();
    if($orgids1){
        $sel_org1=C::t('organization')->fetch_all($orgids1);

        foreach($sel_org1  as $key=> $value){
            $orgpath=getPathByOrgid($value['orgid']);
            $sel_org1[$key]['orgpath']=implode('-',($orgpath));
            $arr=(array_keys($orgpath));
            //print_r($arr);
            array_pop($arr);
            if($count=count($arr)){
                if($open1[$arr[$count-1]]){
                    if(count($open1[$arr[$count-1]])>$count) $open1[$arr[count($arr)-1]]=$arr;
                }else{
                    $open1[$arr[$count-1]]=$arr;
                }
            }
        }
        if(in_array('other',$orgids1)){
            $sel_org1[]=array('orgname'=>lang('no_org_user'),'orgid'=>'other','forgid'=>1);
        }
    }

    if($uids1){
        $sel_user1=C::t('user')->fetch_user_avatar_by_uids($uids1);
        if($aorgids=C::t('organization_user')->fetch_orgids_by_uid($uids1)){
            foreach($aorgids as $orgid){
                $arr= C::t('organization')->fetch_parent_by_orgid($orgid,true);
               
                if($count=count($arr)){
                    if($open1[$arr[$count-1]]){
                        if(count($open[$arr[$count-1]])>$count) $open1[$arr[count($arr)-1]]=$arr;
                    }else{
                        $open1[$arr[$count-1]]=$arr;
                    }
                }
            }
        }
    }
    $openarr=json_encode(array('orgids'=>$open,'orgids1'=>$open1));
    require template('app_manage');
}
