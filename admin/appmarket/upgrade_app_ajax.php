<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      qchlian 3580164@qq.com
 */
if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {
    exit('Access Denied');
}

@set_time_limit(0);
include_once DZZ_ROOT . './core/core_version.php';
include_once libfile('function/admin');
include_once libfile('function/cache');
include_once libfile('function/appmarket'); 
$step = intval($_GET['step']);
$op = $_GET['op'];
$step = $step ? $step : 1;
$operation = $_GET['operation'] ? trim($_GET['operation']) : 'upgrade';
header('Content-type:text/json');  
$steplang = array('', lang('founder_upgrade_updatelist'), lang('founder_upgrade_download'), lang('founder_upgrade_compare'), lang('founder_upgrade_upgrading'), lang('founder_upgrade_complete'), 'dbupdate' => lang('founder_upgrade_dbupdate'));
if ($operation == 'check_upgrade' ) {//根据appid检查app应用是否需要更新
    $appid = $_GET["appid"]; 
    $appinfo = C::t('app_market')->fetch($appid);//C::tp_t('app_market')->find( $appid );
    $time =dgmdate(TIMESTAMP,'Ymd');
    $return=array(
        "url"=>ADMINSCRIPT .'?mod=appmarket&op=upgrade', 
        "status"=>1,
        "percent"=>5,
        "second"=>500,
        "mid"=>$appinfo["mid"],
        "msg"=> lang("app_upgrade_check_need_update")
    );
    //删除安装临时文件
    $temp_download=DZZ_ROOT.'./data/update/app/'.$appinfo['app_path'].'/'.$appinfo['identifier'];
    removedirectory($temp_download);
    
    if($appinfo["check_upgrade_time"]==$time){//今天已经检查过是否需要更新 
        if(  $appinfo["upgrade_version"]!="" ){
            $return["url"] = ADMINSCRIPT .'?mod=appmarket&op=upgrade_app_ajax&appid='.$appid;
            if($appinfo["mid"]==0){
                $return["url"] = ADMINSCRIPT .'?mod=appmarket&op=upgrade_app_ajax&operation=localupgrade&appid='.$appid; 
            }
            exit(json_encode($return));//已获取更新版本信息,待更新
        }else{
            $return["status"]=0;
            $return["msg"]=lang("app_upgrade_to_lastversion");
            exit(json_encode($return));//不需要更新
        } 
    }
  
    if( $appinfo["mid"]==0 ){//判断本地应用是否升级
        $file = DZZ_ROOT . './'.$appinfo['app_path'].'/' . $appinfo['identifier'] . '/dzz_app_' . $appinfo['identifier'] . '.xml'; 
        if ( file_exists($file) ) {
            $importtxt = @implode('', file($file));
            $apparray = getimportdata('Dzz! app'); 
            if($apparray["app"]["version"]!=$appinfo["version"]){
                $return["url"] = ADMINSCRIPT .'?mod=appmarket&op=upgrade_app_ajax&operation=localupgrade&appid='.$appid; 
            } else{
                $return["status"]=0;
                $return["msg"]=lang("app_upgrade_to_lastversion"); 
            }
        }else{
            $return["status"]=0;
            $return["msg"]=lang("app_upgrade_to_lastversion"); 
        }
        exit(json_encode($return)); 
    }else{
        $dzz_upgrade = new dzz_upgrade_app();
        //根据当前版本查询是否需要更新 
        $appinfo["mysqlversion"] = helper_dbtool::dbversion();
        $appinfo["phpversion"] = PHP_VERSION ;
        $appinfo["dzzversion"] = CORE_VERSION; 
        $response = $dzz_upgrade->check_upgrade_byversion( $appinfo );
        
        if($response && $response["status"]==1 ) {
            $map=array(
                "appid"=>$appid,
                "upgrade_version"=>serialize($response["data"]),
                "check_upgrade_time"=>dgmdate(TIMESTAMP,'Ymd')
            );
            $re=C::t('app_market')->update($appid,$map);//C::tp_t('app_market')->where("appid=".$appid)->save( $map );
            $return["url"] = ADMINSCRIPT .'?mod=appmarket&op=upgrade_app_ajax&appid='.$appid;
            exit(json_encode($return));//需要更新
        } else {
            $map=array(
                "appid"=>$appid,
                "upgrade_version"=>"",
                "check_upgrade_time"=>$time
            );
            $re=C::t('app_market')->update($appid,$map);//C::tp_t('app_market')->where("appid=".$appid)->save( $map );
            $return["status"]=0;
            $return["msg"]=lang("app_upgrade_to_lastversion");
            exit(json_encode($return));//不需要更新
        }
    }
}
elseif($operation == 'upgrade' ){ 
    $appid = $_GET["appid"];
    $return=array(
        "url"=>ADMINSCRIPT .'?mod=appmarket&op=upgrade',
        "status"=>1,
        "percent"=>10,
        "second"=>1, 
        "msg"=>lang("app_upgrade_newversion_will_start")
    );
    $appinfo = C::t('app_market')->fetch($appid);//C::tp_t('app_market')->find( $appid );
    $respon =$appinfo["upgrade_version"];
    if( $respon=="" ){
        $return["status"]=0;
        $return["msg"]= lang("app_upgrade_newversioninfo_error");
        exit(json_encode($return));
        exit;
    }
    
    $version =$appinfo["version"];
    $release ="";
    $charset = str_replace('-', '', strtoupper($_G['config']['output']['charset']));
    $locale = ''; 
    if ($charset == 'BIG5') {
        $locale = 'TC';
    } elseif ($charset == 'GBK') {
        $locale = 'SC';
    } elseif ($charset == 'UTF8') {
        if ($_G['config']['output']['language'] == 'zh-cn' || $_G['config']['output']['language'] == 'zh_cn') {
            $locale = 'SC';
        } elseif ($_G['config']['output']['language'] == 'zh-tw' || $_G['config']['output']['language'] == 'zh_tw') {
            $locale = 'TC';
        }else{
            $locale = 'SC';
        }
    }
     
    $upgrade = unserialize($respon); 
    $dbversion = helper_dbtool::dbversion();
    $dzzversion = CORE_VERSION;
    //判断是否升级mysql 或者php 
    $charset='UTF8';
    $upgraderow=array();
    
    if(version_compare($upgrade['dzzversion'], $dzzversion) > 0 ) {
        $return["status"]=0;
        $return["msg"]= lang('app_upgrade_dzzversion_error', array('version' => $upgrade['dzzversion']));
        exit(json_encode($return));//不需要安装
    }
    if( version_compare($upgrade['phpversion'], PHP_VERSION) > 0 ) {
        $return["status"]=0;
        $return["msg"]= lang('app_upgrade_phpversion_error', array('version' => $upgrade['phpversion'])); 
        exit(json_encode($return));//不需要安装
    }
    if( version_compare($upgrade['mysqlversion'], $dbversion) > 0) {
        $return["status"]=0;
        $return["msg"]= lang('app_upgrade_mysqlversion_error', array('version' => $upgrade['mysqlversion'])); 
        exit(json_encode($return));//不需要安装
    }
     
    //检测新版本目录是否有变化，如果有变化则查询是否已存在插件
    /*if(  md5($upgrade["app_path"].$upgrade["identifier"])!= md5($appinfo["app_path"].$appinfo["identifier"])) { 
        $hasappinfo = C::tp_t('app_market')->where( array("identifier"=>$upgrade['identifier'], "app_path"=>$upgrade["app_path"] ) )->find();
        if( $hasappinfo ){
            $return["status"]=0;
            $return["msg"]= lang('app_upgrade_newversion_folder_error', array('path' =>$upgrade["app_path"]."/".$upgrade['identifier']));
            exit(json_encode($return));//不需要安装
        } 
    }*/
    
    $linkurl = ADMINSCRIPT . '?mod=appmarket&op=upgrade_app_ajax&operation=patch&appid=' .$appid. '&locale=' . $locale . '&charset=' . $charset;
    $return["url"]=$linkurl;
    exit(json_encode($return)); 
}
elseif($operation == 'cross' || $operation == 'patch'){
    $return=array(
        "url"=>ADMINSCRIPT .'?mod=appmarket&op=upgrade',
        "status"=>1,
        "percent"=>10,
        "second"=>0,
        "step"=>0,
        "msg"=> lang("app_upgrade_newversion_start")
    );
    
    if (0 && !$_G['setting']['bbclosed']) {//应用升级暂时可不关闭站点
        $msg = '<p style="margin:10px 0;color:red">' . lang('upgrade_close_site') . '</p>';
        $msg .= '<p style="margin:10px 0"><input type="button" class="btn btn-primary" onclick="window.location.reload();" value="' . lang('founder_upgrade_reset') . '" /></p>';

        $msg .= "<p style=\"margin:10px 0\"><script type=\"text/javascript\">";
        $msg .= "if(history.length > (BROWSER.ie ? 0 : 1)) document.write('<a href=\"javascript:history.go(-1);\" >" . lang('message_return') . "</a>');";
        $msg .= "</script></p>";
        $return["status"]=0;
        $return["msg"]=lang('upgrade_close_site') ;
        exit(json_encode($return)); 
        exit();
    }
    
    $step = intval($_REQUEST['step']);
    $step = $step ? $step : 1;
     
    $release = trim($_GET['release']);
    $locale = trim($_GET['locale']);
    $charset = trim($_GET['charset']);
    $upgradeinfo = $upgrade_step = array();
    
    $appid = $_GET["appid"]; 
    $appinfo = C::t('app_market')->fetch($appid);//C::tp_t('app_market')->find( $appid );
    if( !$appinfo["upgrade_version"] ){
        $linkurl=ADMINSCRIPT.'?mod=appmarket&op=upgrade';
        $return["url"]=$linkurl;
        $return["status"]=0;
        $return["msg"]= lang( "app_upgrade_data_error" );
        exit(json_encode($return));
        exit;
    }
    $appinfo["app_path_old"]=$appinfo["app_path"];
    $appinfo["identifier_old"]=$appinfo["identifier"];
    
    $upgradeinfo = unserialize($appinfo["upgrade_version"]); 
    $upgradeinfo["latestversion"]= $upgradeinfo["version"]; 
    
    //判断目录或标识发生变化  $upgradeinfo最新版本信息
    if(  md5($upgradeinfo["app_path"].$upgradeinfo["identifier"])!= md5($appinfo["app_path"].$appinfo["identifier"])) {
        $upgradeinfo=getappidentifier($upgradeinfo); //获取并判断新标识名称
        //如果有新标识名称 抛出错误停止更新
        if( isset( $upgradeinfo["new_identifier"]) &&  $upgradeinfo["new_identifier"] ){
            $linkurl=ADMINSCRIPT.'?mod=appmarket&op=upgrade';
            $return["url"]=$linkurl;
            $return["status"]=0;
            $return["msg"]= lang( $upgradeinfo["app_path"]."/".$upgradeinfo["identifier"]." 目录已存在,请重命名该目录或移除,防止重复或覆盖" );
            exit(json_encode($return));
        }
        //判断是否是路径不相等
        if( $appinfo["app_path"]!=$upgradeinfo["app_path"]) {
            $appinfo["app_path"]=$upgradeinfo["app_path"]; 
        }
        //版本判断
        if( $appinfo["identifier"]!=$upgradeinfo["identifier"]) {
            //如果新版本目录存在则 获取另外一个新版本标识名称 
            $appinfo["identifier"]=$upgradeinfo["identifier"];
            //禁止标识相同的直接修改
            if( isset( $upgradeinfo["new_identifier"]) &&  $upgradeinfo["new_identifier"] ){
                $appinfo["identifier"]=$upgradeinfo["new_identifier"];
                $appinfo["new_identifier"]=$upgradeinfo["new_identifier"]; 
            }
        }
    }
    $appinfo["upgradeinfo"]=$upgradeinfo; 
     
    $dzz_upgrade = new dzz_upgrade_app(); 
    if($step != 5 &&  !$_GET['fileupgrade'] ) {//$_GET['fileupgrade']是判断还未移动文件。因为移动文件后 updatefilelist 为空
        //获取新版本文件列表
        /*$updatefilelist = $dzz_upgrade->fetch_updatefile_list_bymd5($appinfo);
        if(empty($updatefilelist)) {
            $return["status"]=0;
            $return["msg"]=  lang('app_upgrade_none', array('upgradeurl' => upgradeinformation_app(-1))); 
            exit(json_encode($return));  
        }
        
        $updatemd5filelist = $updatefilelist['md5'];
        $updatefilelist = $updatefilelist['file'];
        
        //与本地文件对比过滤出更新文件
        list($updatefilelist, $updatemd5filelist) = $dzz_upgrade->compare_basefile_bymd5($appinfo, $updatefilelist,$updatemd5filelist);
        $theurl = ADMINSCRIPT . '?mod=appmarket&op=upgrade_app_ajax&operation=' . $operation . '&appid=' .$appid. '&locale=' . $locale . '&charset=' . $charset;
        if(empty($updatefilelist)) {
            $return["status"]=0;
            $return["msg"]=  lang('app_upgrade_exchange_none', array('upgradeurl' => upgradeinformation_app(-9))); 
            exit(json_encode($return));  
        }*/
        $theurl = ADMINSCRIPT . '?mod=appmarket&op=upgrade_app_ajax&operation=' . $operation . '&appid=' .$appid. '&locale=' . $locale . '&charset=' . $charset;
    }
    
    if($step == 1) {
        $linkurl=$theurl. '&step=2'; 
        $return["percent"]=15;
        $return["second"]=1;
        $return["url"]=$linkurl; 
        $return["msg"]= lang('app_upgrade_already_downloadfile' ); 
        $return["step"]=2; 
        exit(json_encode($return)); 
        exit;
    }
     
    elseif($step == 2) {
        //start 下载zip.md5 
        $updatefilelist = $dzz_upgrade->fetch_installapp_zip( $upgradeinfo );
        if(empty($updatefilelist)) {
            $return["status"]=0;
            $return["msg"]=  lang('app_upgrade_none', array('upgradeurl' => upgradeinformation_app(-1))); 
            exit(json_encode($return));  
        }
        $updatemd5filelist = $updatefilelist['md5'];
        $updatefilelist = $updatefilelist['file'];
        //end
        
        $return["msg"]= lang('app_upgrade_downloading' ); 
        $return["step"]=2;
        $percent = 60;
        
        $fileseq = intval($_GET['fileseq']);
        $fileseq = $fileseq ? $fileseq : 1;
        $position = intval($_GET['position']);
        $position = $position ? $position : 0;
        $offset =  1024 * 1024;
        $packagesize = $upgradeinfo["packagesize"]; 
        
        if($fileseq > count($updatefilelist)) { 
            $linkurl = $theurl.'&step=3';
            $percent = 100;
            $return["step"]=3; 
            $return["msg"]=lang('app_upgrade_download_complete', array('upgradeurl' => upgradeinformation_app(6))); 
        } else {
            $downloadstatus = $dzz_upgrade->download_file($appinfo, $updatefilelist[$fileseq-1], '', $updatemd5filelist[$fileseq-1] , $position, $offset);
             
            if($downloadstatus == 1) {
                $linkurl = $theurl.'&step=2&fileseq='.$fileseq.'&position='.($position+$offset);
                $percent =60+ sprintf("%2d", 40 * $position/$packagesize);//60+ sprintf("%2d", 40 * $fileseq/count($updatefilelist));
                $file =  $updatefilelist[$fileseq-1]; 
            } elseif($downloadstatus == 2) {
                $linkurl = $theurl.'&step=2&fileseq='.($fileseq+1);
                $percent =60+ sprintf("%2d", 40 * $position/$packagesize);//60+ sprintf("%2d", 40 * $fileseq/count($updatefilelist));
                $file =  $updatefilelist[$fileseq-1]; 
            } else {
                $return["status"]=0; 
                $return["msg"]= lang('app_upgrade_downloading_error', array('file' => $updatefilelist[$fileseq-1],  'upgradeurl'=>upgradeinformation_app(-3) )) ;
                exit(json_encode($return));  
                exit;
            }
            $msg = lang('app_upgrade_downloading_file', array('file' => $updatefilelist[$fileseq - 1], 'percent' =>$percent. '%','upgradeurl'=>'')) ;
        } 
        $stepover= 1; 
        $return["url"]=$linkurl; 
        $return["percent"]=intval(50*$percent/100);
        $return["second"]=1;
        exit(json_encode($return));  
        exit; 
    }
    elseif($step == 3) { 
        $return["percent"]=55;
        $return["second"]=300; 
        $return["msg"]= lang('app_upgrade_newversion_ing' ); 
        $return["step"]=3;
        
        //start此处应下载压缩包内文件的md5文件 并对比
        $updatefilelist = $dzz_upgrade->fetch_updatefile_list_bymd5($appinfo);
        if(empty($updatefilelist)) {
            $return["status"]=0;
            $return["msg"]=  lang('app_upgrade_none', array('upgradeurl' => upgradeinformation_app(-1))); 
            exit(json_encode($return));  
        }
        
        $updatemd5filelist = $updatefilelist['md5'];
        $updatefilelist = $updatefilelist['file'];
        
        //与本地文件对比过滤出更新文件
        list($updatefilelist, $updatemd5filelist) = $dzz_upgrade->compare_basefile_bymd5($appinfo, $updatefilelist,$updatemd5filelist);
        $theurl = ADMINSCRIPT . '?mod=appmarket&op=upgrade_app_ajax&operation=' . $operation . '&appid=' .$appid. '&locale=' . $locale . '&charset=' . $charset;
        if(empty($updatefilelist)) {//不存在修改的继续执行
            //$return["status"]=0;
            //$return["msg"]=  lang('app_upgrade_exchange_none', array('upgradeurl' => upgradeinformation_app(-9))); 
            //exit(json_encode($return));  
        }
        //end
         
        //start 解压压缩包
        $zippath= DZZ_ROOT . 'data/update/app/'.$upgradeinfo["app_path"].'/'.$upgradeinfo["identifier"].'/'.$upgradeinfo['version'].'/';
        $zipfile=$zippath.$upgradeinfo["identifier"].".zip";
        $md5file =$zippath.$upgradeinfo["identifier"].".md5.dzz";
        dzzunzip($zipfile,$zippath,$md5file);
        //end
         
        $linkurl = $theurl.'&step=4';
        $return["url"]=$linkurl;
        exit(json_encode($return));  
        exit; 
    }
    elseif($step==4){
        $return["percent"]=80;
        $return["second"]=3000; 
        $return["msg"]=lang('app_upgrade_newversion_ing' ); 
        $return["step"]=4;
        
        $updatefilelist = $dzz_upgrade->fetch_updatefile_list_bymd5($appinfo);
        $updatemd5filelist = $updatefilelist['md5'];
        $updatefilelist = $updatefilelist['file'];
        //与本地文件对比过滤出更新文件
        list($updatefilelist, $updatemd5filelist) = $dzz_upgrade->compare_basefile_bymd5($appinfo, $updatefilelist,$updatemd5filelist);
          
        $confirm = $_GET['confirm'];
        if (!$confirm) { 
            $checkupdatefilelist = $updatefilelist;
            if ($dzz_upgrade -> check_folder_perm($appinfo,$checkupdatefilelist)) {
                $confirm = 'file';
            } else {
                $linkurl = $theurl . '&step=4';
                $return["status"]=0;
                $return["percent"]=55;
                $return["second"]=0;
                $return["msg"]=lang('app_upgrade_cannot_access_file', array('upgradeurl' => upgradeinformation_app(-4)));
                $return["step"]=1;
                exit(json_encode($return));  
            } 
        } 
        $paraftp = '';
        if ($_GET['siteftp']) {
            foreach ($_GET['siteftp'] as $k => $v) {
                $paraftp .= '&siteftp[' . $k . ']=' . $v;
            }
        }

        if(!$_GET['startupgrade']) {
            if (!$_GET['backfile']) {
                $linkurl = $theurl . '&step=4&backfile=1&confirm=' . $confirm . $paraftp;
                $msg = '<p style="margin:10px 0">' . lang('upgrade_backuping', array('upgradeurl' => upgradeinformation(2))) . '</p>';
                $msg .= '<script type="text/JavaScript">setTimeout("location.href=\'' . ($linkurl) . '\';", 1000);</script>';
                $msg .= ' <p style="margin:10px 0"><a href="' . $linkurl . '">' . lang('message_redirect') . '</p>';
                
                $return["url"]=$linkurl;
                $return["percent"]=55;
                $return["second"]=0;
                $return["msg"]= lang('app_upgrade_backuping', array('upgradeurl' => upgradeinformation(2))) ;
                $return["step"]=4;
                exit(json_encode($return)); 
                exit();
            } 
            foreach ($updatefilelist as $updatefile) {
                $destfile = DZZ_ROOT .$appinfo['app_path_old'].'/' . $appinfo['identifier_old'].'/'.$updatefile; 
                $backfile = DZZ_ROOT .'data/update/app/'.$upgradeinfo['app_path'].'/' .$upgradeinfo["identifier"].'/'.$appinfo["version"].'_bak/'.$updatefile;
               
                if (is_file($destfile)) {
                    if (!$dzz_upgrade -> copy_file($destfile, $backfile, 'file')) { 
                        $return["status"]=0;  
                        $return["msg"]=lang('app_upgrade_backup_error', array('upgradeurl' => upgradeinformation(-5))) ;
                        exit(json_encode($return));    
                    }
                } 
            }
             
            $return["url"]=$theurl . '&step=4&startupgrade=1&confirm=' . $confirm . $paraftp;
            $return["percent"]=60;
            $return["second"]=300;
            $return["msg"]=lang('app_upgrade_backup_complete', array('upgradeurl' => upgradeinformation(3))) ;
            exit(json_encode($return));  
            exit(); 
        } 
        
        if(!$_GET['fileupgrade']) {
            foreach ($updatefilelist as $updatefile) {
                $srcfile = DZZ_ROOT . 'data/update/app/'.$upgradeinfo['app_path'].'/' .$upgradeinfo["identifier"].'/'.$upgradeinfo['latestversion'].'/'.$updatefile;
                if ($confirm == 'ftp') {//待测试
                    $destfile = $updatefile;
                } else { 
                    $destfile = DZZ_ROOT .$appinfo['app_path'].'/' . $appinfo['identifier'].'/'.$updatefile; 
                }
                if (!$dzz_upgrade -> copy_file($srcfile, $destfile, $confirm)) {
                    if ($confirm == 'ftp') {
                        $return["status"]=0;  
                        $return["msg"]= lang('app_upgrade_ftp_upload_error', array('file'=>$updatefile,'upgradeurl' => upgradeinformation_app(-6))); 
                        exit(json_encode($return)); 
    
                    } else {
                        $return["status"]=0;  
                        $return["msg"]=lang('app_upgrade_copy_error', array('file'=>$updatefile,'upgradeurl' => upgradeinformation_app(-7))); 
                        exit(json_encode($return)); 
                    }
                }
            } 
            
            $linkurl = $theurl . '&step=4&startupgrade=1&backfile=1&fileupgrade=1&dodabase=1&confirm=' . $confirm; 
            $return["url"]=$linkurl;
            $return["percent"]=75;
            $return["second"]=2000;
            $return["msg"]=lang('app_upgrade_file_success', array( 'upgradeurl' => upgradeinformation_app(4))); 
            exit(json_encode($return)); 
            exit();
        }
          
        if($_GET['dodabase']){ 
            $finish = FALSE;
            $dir = $appinfo['app_path'];
            $appname = $appinfo['identifier'];
            $xmlfile = 'dzz_app_' . $appname . '.xml'; 
            $importfile = DZZ_ROOT . './'.$dir.'/' . $appname . '/' . $xmlfile; 
            if ( $appinfo['identifier']!=$upgradeinfo['identifier'] ) {
                @unlink( $importfile );
            } 
            
            if (!file_exists($importfile)) {
                $importfile2 = DZZ_ROOT . './'.$dir.'/' . $appname . '/dzz_app_' . $upgradeinfo['identifier'] . '.xml';
                if(!file_exists($importfile2)){
                    $return["status"]=0;  
                    $return["msg"]=  lang("app_upgrade_xmlfile_error" ,array('file'=>$xmlfile,'upgradeurl' => upgradeinformation_app(-8))); 
                    exit(json_encode($return));
                }else{
                    @rename($importfile2,$importfile);
                }
            }  
            $importtxt = @implode('', file($importfile));
        
            $apparray = getimportdata('Dzz! app');
            $filename = $apparray['app']['extra']['upgradefile']; 
            if (!empty($filename) && preg_match('/^[\w\.]+$/', $filename)) {
                $filename = DZZ_ROOT . './'.$dir.'/' . $appname . '/' . $filename;
                if (file_exists($filename)) {
                    @include_once $filename;
                } else {
                    $finish = TRUE;
                }
            } else {
                $finish = TRUE;
            }
            
            if ($finish) {
                unset( $apparray["app"]["upgrade_version"]);//此信息xml里面已取消
                 
                //保存对应的应用名及应用地址
                if ( $appinfo['identifier']!=$upgradeinfo['identifier'] ) {
                    $apparray['app']['identifier']=$appinfo['identifier'];//$appinfo['new_identifier'];
                    $apparray['app']['appurl']= str_replace("mod=".$upgradeinfo['identifier'],"mod=".$appinfo['identifier'],$appinfo['app']['appurl']);
                    $apparray['app']['appadminurl']= str_replace("mod=".$upgradeinfo['identifier'],"mod=".$appinfo['identifier'],$appinfo['app']['appadminurl']);
                    $apparray['app']['noticeurl']= str_replace("mod=".$upgradeinfo['identifier'],"mod=".$appinfo['identifier'],$appinfo['app']['noticeurl']);
                    $apparray['app']['identifier']=  $appinfo['identifier'];  
                    $apparray['app']['check_upgrade_time']= 0 ; 
                }
                if( isset($apparray['app']['app_path']) && $apparray['app']['app_path']=="" ) unset($apparray['app']['app_path']);
                if ($app = importByarray($apparray, 1)) {
                    cron_create( $app );
                }
                writelog('otherlog', "更新应用 ".$apparray['app']['appname']); 
            } 
            
            $linkurl = ADMINSCRIPT . '?mod=appmarket&op=upgrade_app_ajax&operation=' . $operation . '&appid=' .$_GET["appid"]. '&step=5&confirm=' . $confirm; 
            $return["url"]=$linkurl;
            $return["percent"]=80;
            $return["second"]=300;
            $return["step"]=5;
            $return["msg"]=lang('app_upgrade_database_success', array( 'upgradeurl' => upgradeinformation_app(5))); 
            exit(json_encode($return)); 
            exit;
        }
        
        $linkurl = ADMINSCRIPT . '?mod=appmarket&op=upgrade_app_ajax&operation=' . $operation . '&appid=' .$appid. '&step=5&confirm=' . $confirm; 
        
        $return["url"]=$linkurl;
        $return["percent"]=80;
        $return["second"]=300;
        $return["step"]=5;
        $return["msg"]=lang('app_upgrade_newversion_will_success'); 
        exit(json_encode($return));
        exit;
    }
    elseif($step==5){ 
        //删除更新文件列表tmp临时文件
        $srcfile = DZZ_ROOT .$appinfo['app_path'].'/' . $appinfo['identifier'].'/updatelist.tmp';  
        if (file_exists($srcfile)) {
            $desfile = DZZ_ROOT .$appinfo['app_path'].'/' . $appinfo['identifier'].'/'.$appinfo['identifier'].'.md5';
            @unlink($desfile);
            @rename($srcfile, $desfile); 
        }
      
        $map=array( 
            "upgrade_version"=>"" 
        );
           
        $re=C::t('app_market')->update( $appid,$map);
        updatecache('setting'); 
         
        $return["url"] = ADMINSCRIPT . '?mod=appmarket&op=upgrade_app_ajax&operation=check_upgrade&appid='.$appinfo["appid"];
        $return["percent"]=100;
        $return["second"]=300;
        $return["step"]=5;
        $return["msg"]=lang("app_upgrade_newversion_success" ,array( 'upgradeurl' => upgradeinformation_app(1))); 
        exit(json_encode($return)); 
    }
}
elseif($operation == 'localupgrade' ){
    $appid = intval($_GET['appid']);
	if (!$app = C::t('app_market') -> fetch($appid)) {
		$return["status"]=0;
        $return["msg"]="应用不存在";
        exit(json_encode($return)); 
	}
	if(empty($app['app_path'])) $app['app_path']='dzz';
	$finish = FALSE;
	$msg = lang('application_upgrade_successful');

	$entrydir = DZZ_ROOT . './'.$app['app_path'].'/' . $app['identifier'];
	$file = $entrydir . '/dzz_app_' . $app['identifier'] . '.xml';
	if (!file_exists($file)) { 
        $return["status"]=0;
        $return["msg"]=lang("list_cp_Application_tautology");
        exit(json_encode($return)); 
	}
	$importtxt = @implode('', file($file));
	$apparray = getimportdata('Dzz! app', 0, 0, $importtxt);

	$filename = $apparray['app']['extra']['upgradefile'];
	$toversion = $apparray['app']['version'];
    $mid = isset($apparray['app']['mid'])?intval($apparray['app']['mid']):0;
	if (!empty($apparray['app']['extra']['upgradefile']) && preg_match('/^[\w\.]+$/', $apparray['app']['extra']['upgradefile'])) {
		$filename = $entrydir . '/' . $apparray['app']['extra']['upgradefile'];
		if (file_exists($filename)) {
			@include $filename;
		} else {
			$finish = TRUE;
		}
	} else {
		$finish = TRUE;
	}
	if ($finish) {
        $map=array(
            "mid"=>$mid,
            "version"=>$toversion, 
            "upgrade_version"=>"",
            "check_upgrade_time"=>0
        );
        
        $re=C::t('app_market')->update($appid,$map);//C::tp_t('app_market')->where("appid=".$appid)->save( $map );  
	}
    
    $return["url"] = ADMINSCRIPT . '?mod=appmarket&op=upgrade_app_ajax&operation=check_upgrade&appid='.$app["appid"];
    $return["percent"]=100;
    $return["second"]=300;
    $return["step"]=5;
    $return["msg"]=lang( "application_upgrade_successful" ); 
    exit(json_encode($return)); 
}


//获取安装时对应目录名称
function getappidentifier( $baseinfo=array() ) {
	if( $baseinfo ){
		$map=array(
            "mid"=>array("neq",$baseinfo["mid"]),
			"identifier"=>$baseinfo["identifier"],
			"app_path"=>$baseinfo["app_path"]
		);
		$count=DB::result_first("select COUNT(*) from %t where mid!=%d and identifier=%s and app_path=%s",array('app_market',$baseinfo["mid"],$baseinfo["identifier"],$baseinfo["app_path"]));// C::tp_t('app_market')->where($map)->count(); 
		if( $count>0 ){
			$pos=true;
			$i=1;
			while($pos !== false) {
				$map["identifier"]=$baseinfo["identifier"]."_".$baseinfo["mid"]."_".$i ;
				$count=DB::result_first("select COUNT(*) from %t where mid!=%d and identifier=%s and app_path=%s",array('app_market',$baseinfo["mid"],$map["identifier"],$baseinfo["app_path"]));
				//$count= C::tp_t('app_market')->where($map)->count(); 
				if( $count==0 ){
					$pos=false;
				}else{
					$i++;
				}
			}
			$baseinfo["new_identifier"]=$map["identifier"];
		}
	}
	return $baseinfo;
} 
?>