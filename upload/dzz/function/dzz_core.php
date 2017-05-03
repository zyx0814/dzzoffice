<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

static $documentexts=array('DZZDOC','HTM','HTML','SHTM','SHTML','HTA','HTC','XHTML','STM','SSI','JS','JSON','AS','ASC','ASR','XML','XSL','XSD','DTD','XSLT','RSS','RDF','LBI','DWT','ASP','ASA','ASPX','ASCX','ASMX','CONFIG','CS','CSS','CFM','CFML','CFC','TLD','TXT','PHP','PHP3','PHP4','PHP5','PHP-DIST','PHTML','JSP','WML','TPL','LASSO','JSF','VB','VBS','VTM','VTML','INC','SQL','JAVA','EDML','MASTER','INFO','INSTALL','THEME','CONFIG','MODULE','PROFILE','ENGINE','DOC','DOCX','XLS','XLSX','PPT','PPTX','ODT','ODS','ODG','RTF','ET','DPX','WPS');
static $textexts=array('DZZDOC','HTM','HTML','SHTM','SHTML','HTA','HTC','XHTML','STM','SSI','JS','JSON','AS','ASC','ASR','XML','XSL','XSD','DTD','XSLT','RSS','RDF','LBI','DWT','ASP','ASA','ASPX','ASCX','ASMX','CONFIG','CS','CSS','CFM','CFML','CFC','TLD','TXT','PHP','PHP3','PHP4','PHP5','PHP-DIST','PHTML','JSP','WML','TPL','LASSO','JSF','VB','VBS','VTM','VTML','INC','SQL','JAVA','EDML','MASTER','INFO','INSTALL','THEME','CONFIG','MODULE','PROFILE','ENGINE');
static $unRunExts=array('htm','html','js','php','jsp','asp','aspx','xml','htc','shtml','shtm','vbs'); //需要阻止运行的后缀名；
static $docexts=array('DOC','DOCX','XLS','XLSX','PPT','PPTX','ODT','ODS','ODG','RTF','ET','DPX','WPS');
//echo strtolower(implode(',',$docexts));
static $imageexts=array('JPG', 'JPEG', 'GIF', 'PNG', 'BMP');
static $idtype2type=array(	
							'picid'=>'image',
							'lid'=>'link',
							'mid'=>'music',
							'vid'=>'video',
							'did'=>'document',
							'appid'=>'app',
							'qid'=>'attach',
							'uid'=>'user'
						);
function get_os()
{
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $os = false;

    if (eregi('win', $agent) && eregi('nt 5.1', $agent))
	{
      $os = 'Windows XP';
    }
    else if (eregi('win', $agent) && eregi('nt 5.0', $agent))
	{
      $os = 'Windows 2000';
    }
	else if (eregi('win', $agent) && eregi('nt 5.2', $agent))
	{
      $os = 'Windows 2003';
    }
	else if (eregi('win', $agent) && eregi('nt 6.0', $agent))
	{
      $os = 'Windows 2008';
    }
	else if (eregi('win', $agent) && eregi('6.0', $agent))
	{
      $os = 'Windows vista';
    }
	else if (eregi('win', $agent) && eregi('6.1', $agent))
	{
      $os = 'Windows 7';
    }
	else if (eregi('win', $agent) && eregi('6.2', $agent))
	{
      $os = 'Windows 8';
    }
    else if (eregi('win', $agent) && eregi('nt', $agent))
	{
      $os = 'Windows NT';
    }
    else if (eregi('win', $agent) && ereg('32', $agent))
	{
      $os = 'Windows 32';
    }
	else if (eregi('linux', $agent) && ereg('Android', $agent))
	{
      $os = 'Android';
    }
    else if (eregi('linux', $agent))
	{
      $os = 'Linux';
    }
    else if (eregi('unix', $agent))
	{
      $os = 'Unix';
    }
    else if (eregi('sun', $agent) && eregi('os', $agent))
	{
      $os = 'SunOS';
    }
    else if (eregi('ibm', $agent) && eregi('os', $agent))
	{
      $os = 'IBM OS/2';
    }
    else if (eregi('Mac', $agent) && eregi('Macintosh', $agent))
	{
      $os = 'Macintosh';
    }
    else if (eregi('PowerPC', $agent))
	{
      $os = 'PowerPC';
    }
   /* else if (eregi('AIX', $agent))
	{
      $os = 'AIX';
    }
    else if (eregi('HPUX', $agent))
	{
      $os = 'HPUX';
    }
    else if (eregi('NetBSD', $agent))
	{
      $os = 'NetBSD';
    }
    else if (eregi('BSD', $agent))
	{
      $os = 'BSD';
    }
    else if (ereg('OSF1', $agent))
	{
      $os = 'OSF1';
    }
    else if (ereg('IRIX', $agent))
	{
      $os = 'IRIX';
    }
    else if (eregi('FreeBSD', $agent))
	{
      $os = 'FreeBSD';
    }
    else if (eregi('teleport', $agent))
	{
      $os = 'teleport';
    }
    else if (eregi('flashget', $agent))
	{
      $os = 'flashget';
    }
    else if (eregi('webzip', $agent))
	{
      $os = 'webzip';
    }
    else if (eregi('offline', $agent))
	{
      $os = 'offline';
    }*/
    else 
    {
      $os = 'Unknown';
    }
    return $os;
}
function array_sort($arr,$keys,$type='asc'){ //二维数组排序；
	$keysvalue = $new_array = array();
	foreach ($arr as $k=>$v){
		$keysvalue[$k] = $v[$keys];
	}
	if($type == 'asc'){
		asort($keysvalue);
	}else{
		arsort($keysvalue);
	}
	reset($keysvalue);
	foreach ($keysvalue as $k=>$v){
		$new_array[$k] = $arr[$k];
	}
	return $new_array;
} 


if (!function_exists('json_decode') ){
    function json_decode($content, $assoc=false){
        require_once DZZ_ROOT.'/dzz/class/class_json.php'; 
        if ( $assoc ){
                    $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        } else {
                    $json = new Services_JSON;
        }
        return $json->decode($content);
    }
}

if ( !function_exists('json_encode') ){
    function json_encode($content){
	   require_once DZZ_ROOT.'/dzz/class/class_json.php'; 
		$json = new Services_JSON;
        return $json->encode($content);
    }
}

function getThames(){//处理风格
	global $_G;
	$thames=DB::fetch_first("SELECT * FROM ".DB::table('user_thame')." WHERE uid='{$_G['uid']}'");
	$return=$data=array();
	$arr=array();
	if(!$arr=DB::fetch_first("select * from ".DB::table('thame')." where id='{$thames['thame']}'")){
	   $arr=DB::fetch_first("select * from ".DB::table('thame')." where 1 ORDER BY `default` DESC LIMIT 1");
	}
	if(empty($arr['folder'])) $arr['folder']='colorful';
	$arr['modules']=unserialize(stripslashes($arr['modules']));

	if(empty($arr['modules']['window'])) {
		$arr['modules']['window']='colorful';
	}
	if(empty($arr['modules']['filemanage'])){
		$arr['modules']['filemanage']='window_jd';
	}
	if(empty($arr['modules']['icoblock'])){
		$arr['modules']['icoblock']='default';
	}
	if(empty($arr['modules']['menu'])){
		$arr['modules']['menu']='default';
	}
	if(empty($arr['modules']['startmenu'])){
		$arr['modules']['startmenu']='default';
	}
	if(empty($arr['modules']['taskbar'])){
		$arr['modules']['taskbar']='default';
	}
	if(!$arr['backimg']) $arr['backimg']='dzz/styles/thame/'.$arr['folder'].'/back.jpg';
	$data['system']=$arr;
	$data['custom']=array(
							'custom_backimg'=>$thames['custom_backimg']?$thames['custom_backimg']:'',
							'custom_url'=>$thames['custom_url']?$thames['custom_url']:'',
							'custom_color'=>$thames['custom_color']?$thames['custom_color']:'',
							'custom_btype'=>$thames['custom_btype']?$thames['custom_btype']:'',
							
						);
	$return['data']=$data;
	$return['thame']=array( 
							'folder'=>$arr['folder'],
							'backimg'=>$thames['custom_backimg']?$thames['custom_backimg']:$arr['backimg'],
							'color'=>$arr['enable_color']?($thames['custom_color']?$thames['custom_color']:$arr['color']):'',
							'modules'=>$arr['modules'],
				          );
	return $return;
}
function getTableBytype($type){
	switch($type){
			case 'folder':
				return array('fid','folder');
			case 'attach':
				return array('qid','source_attach');
			case 'document':
				return array('did','source_document');
			case 'image':
				return array('picid','source_image');
			case 'link':
				return array('lid','source_link');
			case 'video':
				return array('vid','source_video');
			case 'music':
				return array('mid','source_music');
			case 'topic':
				return array('tid','source_topic');
			case 'app':
				return array('appid','app_market');
			case 'user':
				return array('uid','user');
	}
	return false;
}
function getsource_by_idtype($type,$oid){
		global $_G;
		if($arr=getTableBytype($type)){
			return C::t($arr[1])->fetch($oid);
		}else{
			return false;	
		}
}

function topshowmessage($msg){
	 include template('common/header_common');
		echo "<script type=\"text/javascript\">";
		echo "try{if(top._config){top.showDialog('".$msg."');}else{alert('".$msg."');}}catch(e){alert('".$msg."');}";
		echo "</script>";
	include template('common/footer_reload');
	exit();
}
	
function SpaceSize($size,$gid,$isupdate=0,$uid=0){
	//size: 增加的话为正值，减少的话为负值；
	//gid : 大于零位群组空间，否则为$_G['uid']的空间，
	//isupdate: 为true，则实际改变空间，否则只是检查是否有空间
	//$uid:留空为当前用户 
	global $_G,$space;
	if(empty($uid)) $uid=$_G['uid'];
	if($gid>0){
		 if(!$org=C::t('organization')->fetch($gid)){ //机构不存在时，返回错误；
			 //return false;
		 }
		 $spacearr['usesize']=intval($org['usesize']);
		 $spacearr['maxspacesize']=intval($org['maxspacesize']);
	}else{
		if(!$space){
			$space=dzzgetspace($uid);
		}else{
			$space['usesize']=DB::result_first("select usesize from %t where uid=%d",array('user_field',$uid));
		}
		$spacearr=$space;
	}
	if($isupdate){
		$new_usesize=($spacearr['usesize']+$size)>0 ? ($spacearr['usesize']+$size) : 0;
		if($gid>0){
			C::t('organization')->update($gid,array('usesize'=>$new_usesize));
		}else{
			C::t('user_field')->update($uid,array('usesize'=>$new_usesize));
		}
		return true;
	}else{
		if($gid){
			if($spacearr['maxspacesize']==0) return true; //机构最大空间为0 表示不限制
			if(($spacearr['usesize']+$size)>$spacearr['maxspacesize']){
				return false;
			}else{
				return true;
			}
			return true;
		}else{
			if($space['maxspacesize']==0) return true; //用户组最大空间为0 表示不限制
			elseif($space['maxspacesize']<0) return false; //用户组最大空间<0 表示没有空间
			if(($spacearr['usesize']+$size)>$spacearr['maxspacesize']){
				return false;
			}else{
				return true;
			}
		}
	}
}
function getPositionName($fid){
	$return='';
	$folder=C::t('folder')->fetch($fid);
	if($folder['flag']=='dock'){
		$return=lang('dock');
	
	}elseif($folder['flag']=='desktop'){
		$return =lang('desktop');
	}else{
		$return=$folder['fname'];
	}
	if($return)	return '"'.$return.'"';
	else return '';
}
function getPathByPfid($pfid,$arr=array(),$count=0){
	//static $arr=array();
	//static $count=0;
	if($count>100) return $arr; //防止死循环；
	else $count++;
	if($value=DB::fetch_first("select pfid,fid,fname from ".DB::table('folder')." where fid='{$pfid}'")){
		$arr[$value['fid']]=$value['fname'];
		if($value['pfid']>0 && $value['pfid']!=$pfid) $arr=getPathByPfid($value['pfid'],$arr,$count);
	}
	//$arr=array_reverse($arr);

	return $arr;
	
}
//获取目录的信息(总大小，文件数和目录数);
function getContainsByFid($fid){
	static $contains=array('size'=>0,'contain'=>array(0,0));
	//$folder=C::t('folder')->fetch($fid);
	foreach(C::t('icos')->fetch_all_by_pfid($fid) as $value){
		$contains['size']+=$value['size'];
		if($value['type']=='folder'){
			$contains['contain'][1]+=1;
			getContainsByFid($value['oid']);
		}else{
			$contains['contain'][0]+=1;
		}
	}
	
	return $contains;
}
//返回自己和上级目录fid数组；
function getTopFid($fid,$i=0,$arr=array()){
	$arr[]=$fid;
	if($i>100) return $arr; //防止死循环；
	else $i++;
	if($pfid=DB::result_first("select pfid from ".DB::table('folder')." where fid='{$fid}'")){
		if($pfid!=$fid) $arr=getTopFid($pfid,$i,$arr);
	}
	return $arr;
}

function getGidByContainer($container){
	global $_G;
	if(strpos($container,'icosContainer_folder_')!==false){
		$fid=intval(str_replace('icosContainer_folder_','',$container));
		if($fid>0) return DB::result_first("select gid from ".DB::table('folder')." where fid='{$fid}'");
		else return 0;
	}else{
		return 0;
	}
}
function getFidByContainer($container){
	global $_G;
	if(strpos($container,'icosContainer_body_')!==false){
		 $fid=intval(str_replace('icosContainer_folder_','',$container));
		return DB::result_first("select fid from ".DB::table('folder')." where flag='desktop' and uid='".$_G['uid']."'");
	}elseif(strpos($container,'icosContainer_folder_')!==false){
		 $fid=intval(str_replace('icosContainer_folder_','',$container));
		 return DB::result_first("select fid from ".DB::table('folder')." where fid='{$fid}'");
	}elseif(strpos($container,'_dock_')!==false){
		return DB::result_first("select fid from ".DB::table('folder')." where flag='dock' and uid='".$_G['uid']."'");
	}elseif($container=='_dock'){
		return DB::result_first("select fid from ".DB::table('folder')." where flag='dock' and uid='".$_G['uid']."'");
	}else{
		return false;	
	}
}
function getContainerByFid($pfid){
	global $_G;
	$folder=C::t('folder')->fetch($pfid);
	switch($folder['flag']){
		case 'desktop':
			return 'icosContainer_body_'.$pfid;
		case 'dock':
			return '_dock';
		case 'folder':
			return 'icosContainer_folder_'.$pfid;
		case 'organization':
			return 'icosContainer_folder_'.$pfid;
		default:
			return '';
	}
}


/*function replace_remote($icoarr){
	global $_G;
	switch($icoarr['type']){
		case 'attach':case 'document': 
			$icoarr['url']='';
			break;
		case 'image': 
			if($icoarr['thumb']) $icoarr['img']=$_G['setting']['attachurl'].getimgthumbname($icoarr['url']);
			else $icoarr['img']=getAttachUrl(array('attachment'=>$icoarr['url'],'remote'=>$icoarr['remote']),true);
			$icoarr['_bz']=$bz;
			$icoarr['url']=getAttachUrl(array('attachment'=>$icoarr['url'],'remote'=>$icoarr['remote']),true);
			break;
	}
	return $icoarr;
}*/
function replace_canshu($str,$data=array()){
	global $_G;
	$replacearr=array('{dzzscript}'=>'index.php','{DZZSCRIPT}'=>'index.php','{adminscript}'=>'admin.php','{ADMINSCRIPT}'=>'admin.php','{uid}'=>$_G['uid']);
	$search=array();
	$replace=array();
	foreach($replacearr as $key=>$value){
		$search[]=$key;
		$replace[]=$value;
	}
	return str_replace($search,$replace,$str);
}
function dzz_libfile($libname, $folder = '') {
	$libpath = DZZ_ROOT.'/dzz/'.$folder;
	if(strstr($libname, '/')) {
		list($pre, $name) = explode('/', $libname);
		return realpath("{$libpath}/{$pre}/{$pre}_{$name}.php");
	} else {
		return realpath("{$libpath}/{$libname}.php");
	}
}
function dzzlang($file, $langvar = null, $vars = array(), $default = null) {
	global $_G;
//	return lang($file,$langvar,$vars,$defualt,'dzz/admin');
	list($path, $file) = explode('/', $file);
	if(!$file) {
		$file = $path;
		$path = '';
	}
	
	if($path==''){
		$vars1=explode(':',$file);
		if(count($vars1)==2){
			list($plugfolder,$file)=explode(':',$file);
			$key = 'plugin_'. $plugfolder.'_'.$file;
			if(!isset($_G['lang'][$key])) {
				include DZZ_ROOT.'./dzz/plugin/'.$plugfolder.'/language/'.'lang_'.$file.'.php';
				$_G['lang'][$key] = $lang;
			}
		}else{
			$key = $file;
			if(!isset($_G['lang'][$key])) {
				include DZZ_ROOT.'./dzz/language/'.($path == '' ? '' : $path.'/').'lang_'.$file.'.php';
				$_G['lang'][$key] = $lang;
			}
		}
		$returnvalue = &$_G['lang'];
	}else{
		$key = $path == '' ? $file : $path.'_'.$file;
		if(!isset($_G['lang'][$key])) {
			include DZZ_ROOT.'./dzz/'.$path.'/language/lang_'.$file.'.php';
			$_G['lang'][$key] = $lang;
		}
		
		$returnvalue = &$_G['lang'];
	}
	$return = $langvar !== null ? (isset($returnvalue[$key][$langvar]) ? $returnvalue[$key][$langvar] : null) : $returnvalue[$key];
	$return = $return === null ? ($default !== null ? $default : $langvar) : $return;
	$searchs = $replaces = array();
	if($vars && is_array($vars)) {
		foreach($vars as $k => $v) {
			$searchs[] = '{'.$k.'}';
			$replaces[] = $v;
		}
	}
	if(is_string($return) && strpos($return, '{_G/') !== false) {
		preg_match_all('/\{_G\/(.+?)\}/', $return, $gvar);
		foreach($gvar[0] as $k => $v) {
			$searchs[] = $v;
			$replaces[] = getglobal($gvar[1][$k]);
		}
	}
	$return = str_replace($searchs, $replaces, $return);
	return $return;
}
function getFileTypeName($type,$ext){
	$typename='';
	switch($type){
		case 'image':
			$typename=lang('type_image');
			break;
		case 'video':
			$typename=lang('type_video');
			break;
		case 'music':
			$typename=lang('type_music');
			break;
		case 'attach':
			$typename=lang('typename_attach');
			break;
		case 'app':
			$typename=lang('type_app');
			break;
		case 'user':
			$typename=lang('typename_user');
			break;
		case 'link':
			$typename=lang('type_link');
			break;
		case 'folder':
			$typename=lang('type_folder');
			break;
		case 'document':
			$typename=lang('type_attach');
			break;
		case 'pan':
			$typename=lang('typename_pan');
			break;
		case 'storage':
			$typename=lang('typename_storage');
			break;
		case 'shortcut':
			$typename=lang('typename_shortcut');
			return $typename;
	}

	$name='';
	if($ext =='dzzdoc'){
		$name=lang('extname_dzzdoc');
	}elseif($ext=='txt'){
		$name=lang('extname_txt');
	}else{
		$name=strtoupper($ext).' '.$typename;
	}
	
	return $name;
}
function getmyappid(){
	global $_G;
	if(!$_G['uid']) return array();
	$var="dzz_myicos_".$_G['uid'];
	if($_G[$var]) return $_G[$var];
	else{
		$arr=array('0'=>'');
		$query=DB::query("select oid,icoid from ".DB::table('icos')." where type='app' and (uid='{$_G[uid]}' OR (uid='-1' and notdelete='1'))");
		while($value=DB::fetch($query)){
			$arr[$value['oid']]=$value['icoid'];
		}
		$_G[$var]=$arr;
 	}
	return $_G[$var];
}
function dzzgetspace($uid){
	global $_G;
	$space=array();
	if($uid==0){
		$space=array( 'uid' => 0,'self'=>0, 'username' => '', 'adminid' => 0, 'groupid' => 7, 'credits' => 0, 'timeoffset' => 9999);
	}else{
		$space=getuserbyuid($uid);
	}
	if($_G['adminid']==1){ $space['self']=2;}

	//用户组信息
	if(!$_G['cache']['usergroups']) loadcache('usergroups');
	$usergroup=$_G['cache']['usergroups'][$space['groupid']];
	//$space['groupsize']=$usergroup['maxspacesize']*1024*1024;
	
	if( $config=DB::fetch_first("select usesize,attachextensions,maxattachsize,addsize,buysize,perm ,taskbar from ".DB::table('user_field')." where uid='{$uid}'")){
		$config['perm']=($config['perm']<1) ?$usergroup['perm']:$config['perm'];
		$config['attachextensions']=($config['attachextensions']<0)?$usergroup['attachextensions']:$config['attachextensions'];
		$config['maxattachsize']=($config['maxattachsize']<0)?$usergroup['maxattachsize']*1024*1024:$config['maxattachsize']*1024*1024;
		
		if($usergroup['maxspacesize']==0){
			$config['maxspacesize']=0;
		}elseif($usergroup['maxspacesize']<0){
			if(($config['addsize']+$config['buysize'])>0){
				$config['maxspacesize']=($config['addsize']+$config['buysize'])*1024*1024;
			}else{
				$config['maxspacesize']=-1;
			}
		}else{
			$config['maxspacesize']=($usergroup['maxspacesize']+$config['addsize']+$config['buysize'])*1024*1024;
		}
		$space=array_merge($space,$config);
	}
	$space['fusesize']=formatsize($space['usesize']);
	if($space['maxspacesize']>0){
		$space['fmaxspacesize']=formatsize($space['maxspacesize']);
	}elseif($space['maxspacesize']==0){
		$space['fmaxspacesize']=lang('unlimited');
	}else{
		$space['fmaxspacesize']=lang('unallocated_space');
	}
	$space['attachextensions']=str_replace(' ','',$space['attachextensions']);
	$typefid=array();
	
	$space['typefid']=C::t('folder')->fetch_typefid_by_uid($uid);
	$space['maxChunkSize']=$_G['setting']['maxChunkSize'];
	return $space;
	
}

function microtime_float()
{
    list($usec, $sec) = explode(' ', microtime());
	return (floatval($usec) + floatval($sec));
}

function dzz_file_get_contents($source,$redirect=0,$proxy=''){
	if(function_exists('curl_init')!==false ){
		return curl_file_get_contents($source,$redirect,$proxy);
	}else{
		return file_get_contents($source);
	}
}
function curl_file_get_contents($durl,$redirect=0,$proxy=''){
	global $_SERVER;
	set_time_limit(0);
  	$ch = curl_init();
	 curl_setopt($ch, CURLOPT_URL, $durl);
	 curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	 if($proxy){
		curl_setopt ($ch, CURLOPT_PROXY, $proxy); 
	 }
	 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	 curl_setopt($ch, CURLOPT_ENCODING ,'');
	 curl_setopt($ch, CURLOPT_USERAGENT, '');
	 curl_setopt($ch, CURLOPT_REFERER,'');
	 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	 curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
	 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
	 if($redirect) $r = curl_redir_exec($ch);
	 else $r = curl_exec($ch);
	 curl_close($ch);
	 return $r;
}
 function curl_redir_exec($ch,$debug="") 
{ 
    static $curl_loops = 0; 
    static $curl_max_loops = 20; 
	set_time_limit(0);
    if ($curl_loops++ >= $curl_max_loops) 
    { 
        $curl_loops = 0; 
        return FALSE; 
    } 
    curl_setopt($ch, CURLOPT_HEADER, true); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    $data = curl_exec($ch); 
    $debbbb = $data; 
    list($header, $data) = explode("\n\n", $data, 2); 
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    if ($http_code == 301 || $http_code == 302) { 
        $matches = array(); 
        preg_match('/Location:(.*?)\n/', $header, $matches); 
        $url = @parse_url(trim(array_pop($matches))); 
        if (!$url) 
        { 
            //couldn't process the url to redirect to 
            $curl_loops = 0; 
            return $data; 
        } 
        $last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL)); 
    /*    if (!$url['scheme']) 
            $url['scheme'] = $last_url['scheme']; 
        if (!$url['host']) 
            $url['host'] = $last_url['host']; 
        if (!$url['path']) 
            $url['path'] = $last_url['path'];*/ 
        $new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query']?'?'.$url['query']:''); 
        curl_setopt($ch, CURLOPT_URL, $new_url); 
    //    debug('Redirecting to', $new_url); 

        return curl_redir_exec($ch); 
    } else { 
        $curl_loops=0; 
        return $debbbb; 
    } 
} 
function ico_png($source,$target,$proxy=''){
		$ext=strtolower(substr(strrchr($source, '.'), 1, 10));
		$imgexts=array('png','jpg','jpeg','gif');
		if(in_array($ext,$imgexts)){
			exit($source);
			$data=dzz_file_get_contents($source,0,$proxy);
			if($data && file_put_contents($target,$data)){
				return true;
			}else{
				return false;
			}
		}elseif($ext=='ico'){
			require_once dzz_libfile('class/ico');
			$oico=new Ico($source,$proxy);
			$max=-1;
			$data_length=0;
			for($i=0; $i<$oico->TotalIcons(); $i++){
				$data=$oico->GetIconInfo($i);
				if($data['data_length']>$data_length){
					$data_length=$data['data_length'];
					$max=$i;
				}
			} 
			if($max>=0 && imagepng($oico->GetIcon($max),$target)){
				return true;
			}else return false;
		}else{
			return false;
		}
}

function check_remote_file_exists($url,$proxy='')
{
	set_time_limit(0);
	$u = parse_url($url);
	if(!$u || !isset($u['host'])) return false;
	if(function_exists('curl_init')!==false){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		//curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, 500);
	    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
		
		 if($proxy){
			curl_setopt ($ch, CURLOPT_PROXY, $proxy); 
		 }
		// 不取回数据
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_NOBODY, true);
	    curl_setopt($curl, CURLOPT_REFERER,'');
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET'); //不加这个会返回403，加了才返回正确的200，原因不明
		// 发送请求
		$result = curl_exec($curl);
		$found = false;
		// 如果请求没有发送失败
		if ($result !== false)
		{
			// 再检查http响应码是否为200
			$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if ($statusCode == 200)
			{
				$found = true;
			}
		}
		curl_close($curl);
		return $found;
	}else{
  		$h = get_headers($url);
		//print_r($h);
		if(!$h || !isset($h[0])) return false;
    	$status = $h[0];
		//echo $status;
    	return preg_match("/.*?200\s*OK/i", $status) ? true : false;
	}
} 
function imagetolocal($source, $dir='appimg',$target='') {
	global $_G;
	if(empty($source)) return false;
	if(!$data =dzz_file_get_contents($source)) {
		return false;
	}
	if($target=='dzz/images/default/icodefault.png' || $target=='dzz/images/default/widgetdefault.png' || preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $target)){
		$target='';
	}
	if(!$target) {
		$imageext=array('jpg','jpeg','png','gif');
		$ext=strtolower(substr(strrchr($source, '.'), 1, 10));
		if(!in_array($ext,$imageext)) return false;
		$subdir = $subdir1 = $subdir2 = '';
		$subdir1 = date('Ym');
		$subdir2 = date('d');
		$subdir = $subdir1.'/'.$subdir2.'/';
		$target1=$_G['setting']['attachdir'].$dir.'/'.$subdir.''.date('His').''.strtolower(random(16)).'.'.$ext;
		$target=str_replace($_G['setting']['attachdir'],'',$target1);
	}else{
		$target1=$_G['setting']['attachdir'].$target;
	}
	$targetpath = dirname($target1);
	dmkdir($targetpath);
	if(file_put_contents($target1, $data)){
		if(@filesize($target1)<200) {
			@unlink($target1);
			return false;
		}
		return $target;
	}else return false;
}

function image_to_icon($source,$target,$domain){
	global $_G;
		if(!$data = dzz_file_get_contents($source)){
			return false;
		}
		if(!$target){
			$imageext=array('jpg','jpeg','png','gif');
			$ext=str_replace("/\?.+?/i",'',strtolower(substr(strrchr($source, '.'), 1, 10)));
			if(!in_array($ext,$imageext)) $ext='jpg';
			$subdir = $subdir1 = $subdir2 = '';
			$subdir1 = date('Ym');
			$subdir2 = date('d');
			$subdir = $subdir1.'/'.$subdir2.'/';
			$target='icon/'.$subdir.''.$domain.'_'.strtolower(random(8)).'.'.$ext;
			$target_attach=$_G['setting']['attachdir'].$target;
		}else{
			$target_attach=$_G['setting']['attachdir'].$target;
		}
		$targetpath = dirname($target_attach);
		dmkdir($targetpath);
		if(file_put_contents($target_attach, $data)){
			return $target;
		}else{
			return false;
		}
}

function dzz_default_folder_init(){
	global $_G ,$space;
	
	//建立默认目录
	//创建此用户的根目录
		$root=array(	//添默认目录到dzz_folder
					'pfid'=>0,
					'uid'=>$_G['uid'],
					'username'=>$_G['username'],
					'perm'=>0,
					'fname'=>DB::result_first("select name from ".DB::table('connect')." where bz='dzz'"),
					'flag'=>'home',
					'innav'=>1,
					'fsperm'=>perm_FolderSPerm::flagPower('home')
					
					);
		if($rootfid=DB::result_first("select fid from ".DB::table('folder')." where uid='{$_G[uid]}' and flag='home' ")){
			C::t('folder')->update($rootfid,array('fname'=>$root['fname'],'isdelete'=>0,'pfid'=>0,'fsperm'=>$root['fsperm'],'perm'=>$root['perm']));
		}else{
			$rootfid=C::t('folder')->insert($root,true);
		}
		if($rootfid){
			$space['typefid']['home']=$rootfid;
			foreach(C::t('folder_default')->fetch_all() as $value){
					unset($value['fid']);
				if($fid=DB::result_first("select fid from ".DB::table('folder')." where uid='{$_G[uid]}' and flag='{$value[flag]}'")){
					C::t('folder')->update($fid,array('pfid'=>$rootfid,'default'=>$value['default']));
				}else{
					$value['fsperm']=perm_FolderSPerm::flagPower($value['flag']);
					$value['pfid']=$rootfid;
					$value['uid']=$_G['uid'];
					$value['username']=$_G['username'];
					$fid=C::t('folder')->insert($value,true);
				}
				$space['typefid'][$value['flag']]=$fid;
			}
		}
}
function dzz_organization_shortcut(){
	global $_G;
	$cutids=array();
	foreach(DB::fetch_all("select o.* from %t u LEFT JOIN %t o ON o.orgid=u.orgid where o.`available`>0 and o.indesk>0 and o.fid>0 and u.uid=%d",array('organization_user','organization',$_G['uid'])) as $org){
		
		if(!$cutid=DB::result_first("select cutid from %t where bz=%s ",array('source_shortcut','org_'.$_G['uid'].'_'.$org['orgid']))){
			$tdata=array();
			$tdata=C::t('source_shortcut')->getDataByPath('fid_'.$org['fid']);
			if($tdata['error']){
				continue;
			}
			$shortcut=array(
							'path'=>'fid_'.$org['fid'],
							'data'=>serialize($tdata),
							'bz'=>'org_'.$_G['uid'].'_'.$org['orgid']
							);
			$cutid=C::t('source_shortcut')->insert($shortcut,1);
		}
		if(!$cutid) continue;
		$cutids[]=$cutid;
		if($icoid=DB::result_first("select icoid from %t where type='shortcut' and oid=%d and uid=%d",array('icos',$cutid,$_G['uid']))){
			C::t('icos')->update($icoid,array('name'=>$org['orgname'],'isdelete'=>0));
			continue;	
		}
		
		$pfid=DB::result_first("select fid from ".DB::table('folder')." where uid='{$_G[uid]}' and flag='desktop'");
	
		$icoarr=array(
					'uid'=>$_G['uid'],
					'username'=>$_G['username'],
					'oid'=>$cutid,
					'name'=>$org['orgname'],
					'flag'=>'organization',
					'type'=>'shortcut',
					'dateline'=>$_G['timestamp'],
					'pfid'=>$pfid,
					'gid'=>0,
					'ext'=>'',
					'size'=>0
				);
		
		if($icoarr['icoid']=C::t('icos')->insert($icoarr,1)){
			addtoconfig($icoarr);
		}
	}
	//检查是否有多余的部门快捷方式
	$sql="bz LIKE %s";
	$param=array('source_shortcut','org_'.$_G['uid'].'_%');
	if($cutids){
		$sql.=" and cutid NOT IN(%n)";
		$param[]=$cutids;
	}
	$cutids_del=array();
	foreach(DB::fetch_all("select cutid from %t where $sql",$param) as $value){
		$cutids_del[]=$value['cutid'];
	};
	if($cutids_del){
		foreach(DB::fetch_all("select icoid from %t where type='shortcut' and oid IN(%n)",array('icos',$cutids_del)) as $value){
			C::t('icos')->delete_by_icoid($value['icoid'],true);
		}
	}
}
function dzz_check_default(){
	global $_G,$space;
		//初始化默认目录
		dzz_default_folder_init();
		if($_G['uid']>0){
			//创建机构部门的快捷方式；
			dzz_organization_shortcut();
		}
		
		//处理默认目录类
	if($_G['uid']>0){ //游客不生成默认目录到桌面
		foreach(C::t('folder')->fetch_all_default_by_uid($_G['uid']) as $value){
			
			$type=str_replace('m:','',$value['default']);
			$pfid=DB::result_first("select fid from ".DB::table('folder')." where uid='{$_G[uid]}' and flag='{$type}'");
			$sperm=perm_FileSPerm::flagPower($value['flag']);
			if($icoid=DB::result_first("select icoid from ".DB::table('icos')." where uid='{$_G[uid]}' and oid='{$value[fid]}' and flag='{$value[flag]}' and type='folder'")){
				C::t('icos')->update($icoid,array('pfid'=>$pfid,'sperm'=>$sperm));
				
			}else{
				$icoarr=array(
								'uid'=>$_G['uid'],
								'username'=>$_G['username'],
								'oid'=>$value['fid'],
								'name'=>$value['fname'],
								'type'=>'folder',
								'flag'=>$value['flag'],
								'dateline'=>$_G['timestamp'],
								'pfid'=>$pfid,
								'sperm'=>$sperm
								
							);
							
				if($icoarr['icoid']=C::t('icos')->insert($icoarr,1)){
					addtoconfig($icoarr);
				}
			}
		}
	}
	if($_G['uid']>0){
		$apps=C::t('app_market')->fetch_all_by_notdelete($_G['uid']);
	}else{
		$apps=C::t('app_market')->fetch_all_by_default($_G['uid']);
	}
	
	$applist=array();
	$screenlist=array();
	foreach($apps as $appid => $app){
		$applist[]=$appid;
		if($app['position']==1){ //开始菜单
			/*if($value=DB::fetch_first("select icoid,opuid from ".DB::table('icos')." where uid='{$_G[uid]}' and oid='{$appid}' and type='app'")){
				//if(!$value['opuid']) C::t('icos')->delete_by_icoid($icoid);
			}*/
			continue;
		}else{
			if($app['position']==2){ //桌面
				$fid=DB::result_first("select fid from ".DB::table('folder')." where uid='{$_G[uid]}' and flag='desktop'");
			}else{ //dock条
				$fid=DB::result_first("select fid from ".DB::table('folder')." where uid='{$_G[uid]}' and flag='dock'");
			}
			if(!$fid) continue;
			if($icoid=DB::result_first("select icoid from ".DB::table('icos')." where uid='{$_G[uid]}' and oid='{$appid}' and type='app'")){
				C::t('icos')->update($icoid,array('isdelete'=>0,'pfid'=>$fid));
				$screenlist[]=$icoid;
			}else{
				$icoarr=array(
								'uid'=>$_G['uid'],
								'username'=>$_G['username'],
								'oid'=>$appid,
								'name'=>'',
								'type'=>'app',
								'dateline'=>$_G['timestamp'],
								'pfid'=>$fid,
								'ext'=>'',
								'size'=>0,
							);
				if($icoarr['icoid']=C::t('icos')->insert($icoarr,1)){
					addtoconfig($icoarr);
					$screenlist[]=$icoarr['icoid'];
				}
			}
		}
	}
	if($_G['uid']>0){
		if($applist){
			$oapplist=DB::result_first("select applist from ".DB::table('user_field')." where uid='{$_G[uid]}'");
			if($oapplist) $oapplist=explode(',',$oapplist);
			else $oapplist=array();
			$newappids=array();
			foreach($applist as $appid){
				if(!in_array($appid,$oapplist)){
					 $oapplist[]=$appid;
					 $newappids[]=$appid;
				}
			}
			if($newappids) C::t('app_user')->insert_by_uid($_G['uid'],$newappids);
			C::t('user_field')->update($_G['uid'],array('applist'=>implode(',',$oapplist)));
		}
	}else{
		C::t('user_field')->update($_G['uid'],array('applist'=>implode(',',$applist),'screenlist'=>implode(',',$screenlist)));
	}
	return C::t('user_field')->fetch($_G['uid']);
}
function dzz_desktop_init(){
	global $_G;
	//建立用户设置主表
	$userconfig=array(
						'uid'=>$_G['uid'],
						'applist'=>array(),
						'screenlist'=>array(),
						'docklist'=>array(),
						'dateline'=>$_G['timestamp'],
						'updatetime'=>$_G['timestamp'],
						'wins'=>serialize(array()),
						'perm'=>0,
						'iconview'=>$_G['setting']['desktop_default']['iconview']?$_G['setting']['desktop_default']['iconview']:2,
						'taskbar'=>$_G['setting']['desktop_default']['taskbar']?$_G['setting']['desktop_default']['taskbar']:'bottom',
						'iconposition'=>intval($_G['setting']['desktop_default']['iconposition']),
						'direction'=>intval($_G['setting']['desktop_default']['direction']),
					);
	
	//初始化默认目录
	dzz_default_folder_init();
	if($_G['uid']>0){
			//创建机构部门的快捷方式；
			dzz_organization_shortcut();
		
	}/*else{
		$userconfig['iconview']=1;
		$userconfig['iconposition']=4;
		$userconfig['direction']=1;
	}*/
	//添加默认数据
	include DZZ_ROOT.'./dzz/defaultdata.php';
	
	//处理默认目录
	if($_G['uid']>0){ //游客不生成默认目录到桌面
		foreach(C::t('folder')->fetch_all_default_by_uid($_G['uid']) as $value){
	   
			$type=str_replace('m:','',$value['default']);
			$pfid=DB::result_first("select fid from ".DB::table('folder')." where uid='{$_G[uid]}' and flag='{$type}'");
					
			$sperm=perm_FileSPerm::flagPower($value['flag']);
			if($icoid=DB::result_first("select icoid from ".DB::table('icos')." where uid='{$_G[uid]}' and oid='{$value[fid]}' and flag='{$value[flag]}' and type='folder'")){
				C::t('icos')->update($icoid,array('pfid'=>$pfid,'sperm'=>$sperm));
				if($type=='desktop')  $userconfig['screenlist'][]=$icoarr['icoid'];
				elseif($type=='dock') $userconfig['docklist'][]=$icoarr['icoid'];
			}else{
				$icoarr=array(
								'uid'=>$_G['uid'],
								'username'=>$_G['username'],
								'oid'=>$value['fid'],
								'name'=>$value['fname'],
								'type'=>'folder',
								'flag'=>$value['flag'],
								'dateline'=>$_G['timestamp'],
								'pfid'=>$pfid,
								'sperm'=>$sperm
							);
							
				if($icoarr['icoid']=C::t('icos')->insert($icoarr,1)){
					if($type=='desktop')  $userconfig['screenlist'][]=$icoarr['icoid'];
					elseif($type=='dock') $userconfig['docklist'][]=$icoarr['icoid'];
					//addtoconfig($icoarr);
				}
			}
		}
	
	}
	//处理理默认应用;
	$apps=C::t('app_market')->fetch_all_by_default($_G['uid']);

	foreach($apps as $appid => $app){
		
			$userconfig['applist'][]=$appid;
			if($app['position']==1){
				continue;
			}elseif($app['position']==2){ //桌面
				$fid=DB::result_first("select fid from ".DB::table('folder')." where uid='{$_G[uid]}' and flag='desktop'");
			}else{ //dock条
				$fid=DB::result_first("select fid from ".DB::table('folder')." where uid='{$_G[uid]}' and flag='dock'");
			}
			if(!$fid) continue;
			if($icoid=DB::result_first("select icoid from ".DB::table('icos')." where uid='{$_G[uid]}' and oid='{$appid}' and type='app'")){
				C::t('icos')->update($icoid,array('pfid'=>$fid,'isdelete'=>0));
				if($app['position']==2) $userconfig['screenlist'][]=$icoid;
				else $userconfig['docklist'][]=$icoid;
			}else{
				$icoarr=array(
								'uid'=>$_G['uid'],
								'username'=>$_G['username'],
								'oid'=>$appid,
								'name'=>'',
								'type'=>'app',
								'dateline'=>$_G['timestamp'],
								'pfid'=>$fid,
								'ext'=>'',
								'size'=>0,
							);
				if($icoarr['icoid']=C::t('icos')->insert($icoarr,1)){
					if($app['position']==2) $userconfig['screenlist'][]=$icoarr['icoid'];
					else $userconfig['docklist'][]=$icoarr['icoid'];
				}
			}
		
	}
	$userconfig['applist']=$userconfig['applist']?implode(',',$userconfig['applist']):'';
	$userconfig['screenlist']=$userconfig['screenlist']?implode(',',$userconfig['screenlist']):'';
	$userconfig['docklist']=$userconfig['docklist']?implode(',',$userconfig['docklist']):'';
	if($_G['uid']){
		C::t('user_field')->insert($userconfig,false,true);
		if($userconfig['applist']) C::t('app_user')->insert_by_uid($_G['uid'],$userconfig['applist'],1);
		return C::t('user_field')->fetch($_G['uid']);
	}else{
		return $userconfig;
	}
}

function getTxtAttachByMd5($message,$filename_title,$ext='dzzdoc'){
	global $_G;
	@set_time_limit(0);
	$filename =date('His').''.strtolower(random(16));
	//$ext=strtolower(substr(strrchr($filename_title, '.'), 1, 10));
	
	if(!$ext) $ext='dzzdoc';
	if($ext && in_array($ext,$_G['setting']['unRunExts'])){
		$unrun=1;
	}else{
		$unrun=0;
	}
	//保存附件并且生成附件MD5;
	$subdir = $subdir1 = $subdir2 = '';
	$subdir1 = date('Ym');
	$subdir2 = date('d');
	$subdir = $subdir1.'/'.$subdir2.'/';
	$target1='dzz/'.$subdir.'index.html';
	$target='dzz/'.$subdir;
	$target_attach=$_G['setting']['attachdir'].$target1;
	$targetpath = dirname($target_attach);
	dmkdir($targetpath);
	
	if(is_resource($message)){
		while (!feof($message)) {
			if (!file_put_contents($_G['setting']['attachdir'].$target.$filename.'.'.($unrun?'dzz':$ext),fread($message, 8192),FILE_APPEND)) {
				 return false;
			 }
		}
		fclose($message);
	}else{
		if($message=='') $message=' ';
		if(!file_put_contents($_G['setting']['attachdir'].$target.$filename.'.'.($unrun?'dzz':$ext),$message)){
			return false;
		}
	}
	$size=@filesize($_G['setting']['attachdir'].$target.$filename.'.'.($unrun?'dzz':$ext));
	
	$md5=md5_file($_G['setting']['attachdir'].$target.$filename.'.'.($unrun?'dzz':$ext));
	if($md5 && $attach=C::t('attachment')->fetch_by_md5($md5)){
		$attach['filename']=$filename_title;
		$attach['filetype']=strtolower($ext);
		@unlink($_G['setting']['attachdir'].$target.$filename.'.'.($unrun?'dzz':$ext));
		
	}else{
		$remote=0;
		$attach=array(
						'filesize'=>$size,
						'attachment'=>$target.$filename.'.'.($unrun?'dzz':$ext),
						'filetype'=>strtolower($ext),
						'filename' =>$filename_title,
						'remote'=>$remote,
						'copys' => 0,
						'md5'=>$md5,
						'unrun'=>$unrun,
						'dateline' => $_G['timestamp'],
		);
		if(!$attach['aid']=DB::insert('attachment',($attach),1)){
			return false;
		}
		try{
			if($remoteid=io_remote::MoveToSpace($attach)){
				$attach['remote']=$remoteid;
				C::t('attachment')->update($attach['aid'],array('remote'=>$remoteid));
				@unlink($_G['setting']['attachdir'].$target.$filename.'.'.($unrun?'dzz':$ext));
			}
		}catch(Exception $e){
			//return array('error'=>$e->getMessage());
			return false;
		}
	}
	return $attach;
}


function checkCopy($icoid=0,$sourcetype='',$iscut=0,$obz='',$tbz=''){
	global $_G;
	$copy=1;
	if($sourcetype=='uid'){
		return 1;
	}elseif($iscut==2){
		return 1;
	}elseif($iscut==1){
		return 0;
	}elseif($obz != $tbz){
		return 1;//不同api之间复制	;
	}elseif($obz==$tbz){
		return 0;//相同api之间移动;
	
	}
	return $copy;
}

function delete_icoid_from_container($icoid,$pfid){
	global $_G;
	$typefid=C::t('folder')->fetch_typefid_by_uid($_G['uid']);
	if($pfid==$typefid['dock']){
		$docklist=DB::result_first("select docklist from ".DB::table('user_field')." where uid='{$_G[uid]}'");
		$docklist=$docklist?explode(',',$docklist):array();
		foreach($docklist as $key=>$value){
			if($value==$icoid){
				 unset($docklist[$key]);
			}
		}
		C::t('user_field')->update($_G['uid'],array('docklist'=>implode(',',$docklist)));
	}elseif($pfid==$typefid['desktop']){
	
		$icos=DB::result_first("select screenlist from ".DB::table('user_field')." where uid='{$_G[uid]}'");
	 	$icos=$icos?explode(',',$icos):array();
		foreach($icos as $key=>$value){
			if($value==$icoid){
				 unset($icos[$key]);
			}
		}
		C::t('user_field')->update($_G['uid'],array('screenlist'=>implode(',',$icos)));
	}
}

function dzz_update_source($type,$oid,$data,$istype=false){
	$idtypearr=array('lid','vid','mid','qid','picid','did','fid');
	$typearr=array('link','video','music','attach','image','document','folder');
	$table='';
	$idtype='';
	$pre='source_';
	if($isidtype){
		if(in_array($type,$idtypearr)){
			if($type=='fid') $pre='';
			$table=''.$pre.str_replace($idtypearr,$typearr,$type);
			$idtype=$type;
		}
	}else{
		if($type=='folder') $pre='';
		if(in_array($type,$typearr)){
			$table=''.$pre.$type;
			$idtype=str_replace($typearr,$idtypearr,$type);
		}
	}
	if($table) return C::t($table)->update($oid,$data);
	else return false;
}

function getAttachUrl($attach,$absolute=false){
	global $_G;
	$attachment='';
	$bz=io_remote::getBzByRemoteid($attach['remote']);
	if($bz=='dzz'){
		if($absolute){
			$attachment=$_G['setting']['attachdir'].'./'.$attach['attachment'];
		}else{
			$attachment=$_G['siteurl'].$_G['setting']['attachurl'].$attach['attachment'];
		}
		return $attachment;
	}elseif(strpos($bz,'FTP')===0){
		return $_G['siteurl'].DZZSCRIPT.'?mod=io&op=getStream&path='.dzzencode($bz.'/'.$attach['attachment']);
	}else{
		return IO::getFileUri($bz.'/'.$attach['attachment']);
	}
	
}
function getBzByPath($path){
	$bzarr=explode(':',$path);
	$allowbz=C::t('connect')->fetch_all_bz();
	if(strpos($path,'dzz::')!==false){
		return '';
	}elseif(strpos($path,'attach::')!==false){
		return '';
	}elseif(is_numeric($bzarr[0])){
		return '';
	}elseif(in_array($bzarr[0],$allowbz)){
		return $bzarr[0];
	}else{
		return '';
	}
}
function getDzzPath($attach){
	global $_G;
	$url='';
	$bz=io_remote::getBzByRemoteid($attach['remote']);
	if($bz=='dzz'){
		$url='attach::'.$attach['aid'];
	}else{
		$url=$bz.'/'.$attach['attachment'];
	}
	return $url;
}

function geticonfromext($ext,$type=''){
	global $_G;
	$img='dzz/images/extimg/'.strtolower($ext).'.png';
	if(!is_file(DZZ_ROOT.$img)){
		switch($type){
			case 'video':
				$img='dzz/images/extimg/video.png';
				break;
			case 'music':
				$img='dzz/images/extimg/music.png';
				break;
			case 'document':
				$img='dzz/images/extimg/document.png';
				break;
			case 'folder':
				$img='';
				break;
			case 'link':
				$img='dzz/images/extimg/link.png';
				break;
			case 'dzzdoc':
				$img='dzz/images/extimg/dzzdoc.png';
				break;
			case 'topic':
				$img='dzz/images/extimg/topic.png';
				break;
			default:
				$img='dzz/images/extimg/unknow.png';
		}
	}
	return $img;
}

function getUrlIcon($link){
	global $_G;
	$rarr=array();
	$parse_url=parse_url($link);
	$host=$parse_url['host'];
	$host=preg_replace("/^www./",'',$host);//strstr('.',$host);
	//查询网址特征库
	
	if($icon=C::t('icon')->fetch_by_link($link)){
		return array('img'=>$_G['setting']['attachurl'].$icon['pic'],'did'=>$icon['did'],'ext'=>$icon['ext']);
	}else{
		
		require_once dzz_libfile('class/caiji');
		$caiji=new caiji($link);
		$source=$caiji->getFavicon();
		if($source){
			$subdir = $subdir1 = $subdir2 = '';
			$subdir1 = date('Ym');
			$subdir2 = date('d');
			$subdir = $subdir1.'/'.$subdir2.'/';
			$target='icon/'.$subdir.''.$host.'_'.strtolower(random(8)).'.png';
			$target_attach=$_G['setting']['attachdir'].$target;
			$targetpath = dirname($target_attach);
			dmkdir($targetpath);
			ico_png($source,$target_attach,$caiji->getProxy());
			if(is_file($target_attach)){
				if($did=C::t("icon")->insert(array('domain'=>$host,'pic'=>$target,'check'=>0,'dateline'=>$_G['timestamp'],'uid'=>$_G['uid'],'username'=>$_G['username'],'copys'=>0),1)){
					return array('img'=>$_G['setting']['attachurl'].$target,'did'=>$did);
				}
			}
		}
	}
	return array('img'=>'dzz/images/default/e.png','did'=>0);
}
function addtoconfig($icoarr,$ticoid=0){
	global $_G,$space;
	$oposition=10000;
	$icoid=$icoarr['icoid'];
	if($folder=C::t('folder')->fetch($icoarr['pfid'])){
		if($folder['flag']=='dock'){
			if($docklistarr=C::t('user_field')->fetch($_G['uid'])){
				$docklist=$docklistarr['docklist']?explode(',',$docklistarr['docklist']):array();
				if(in_array($icoid,$docklist)){//已经存在则先删除
					foreach($docklist as $key => $id){
						if(intval($id)<0 || $id==$icoid){
							 unset($docklist[$key]);
							 $oposition=$key;
						}
					}
				}
				if($ticoid && in_array($ticoid,$docklist)){
					$temp=array();
					foreach($docklist as $key => $id){
						if($id==$ticoid){
							if($oposition>$key){
								$temp[]=$icoid;
								$temp[]=$id;
							}else{
								$temp[]=$id;
								$temp[]=$icoid;
							}
						}else{
							$temp[]=$id;
						}
					}
					$docklist=$temp;
				}else{
					$docklist[]=$icoid;
				}
				C::t('user_field')->update($_G['uid'],array('docklist'=>trim(implode(',',$docklist),',')));
			}
	
	
		}elseif($folder['flag']=='desktop'){
		
			if($nav=C::t('user_field')->fetch($_G['uid'])){
				$icos=$nav['screenlist']?explode(',',$nav['screenlist']):array();
				if(in_array($icoid,$icos)){//已经存在则先删除
					foreach($icos as $key => $id){
						if(intval($id)<0 || $id==$icoid){
							 unset($icos[$key]);
							 $oposition=$key;
						}
					}
				}
				if($ticoid && in_array($ticoid,$icos)){
					$temp=array();
					foreach($icos as $key => $id){
						if($id==$ticoid){
							if($oposition>$key){
								$temp[]=$icoid;
								$temp[]=$id;
							}else{
								$temp[]=$id;
								$temp[]=$icoid;
							}
						}else{
							$temp[]=$id;
						}
					}
					$icos=$temp;
				}else{
					$icos[]=$icoid;
				}
				 C::t('user_field')->update($_G['uid'],array('screenlist'=>implode(',',$icos)));
			}
		}
		if($icoarr['type']=='folder' && $icoarr['flag']==''){
			C::t('folder')->update($icoarr['oid'],array('pfid'=>$folder['fid'],'gid'=>$folder['gid']));
		}
	}
	return true;
}
 
function is_upload_files($source) {
	return $source && ($source != 'none') && (is_uploaded_file($source) || is_uploaded_file(str_replace('\\\\', '\\', $source)));
}
function save_to_local($source, $target) {
	$targetpath = dirname($target);
	dmkdir($targetpath);
	if(!is_upload_files($source)) {
		$succeed = false;
	}elseif(@copy($source, $target)) {
		$succeed = true;
	}elseif(function_exists('move_uploaded_file') && @move_uploaded_file($source, $target)) {
		$succeed = true;
	}elseif (@is_readable($source) && (@$fp_s = fopen($source, 'rb')) && (@$fp_t = fopen($target, 'wb'))) {
		while (!feof($fp_s)) {
			$s = @fread($fp_s, 1024 * 512);
			@fwrite($fp_t, $s);
		}
		fclose($fp_s); fclose($fp_t);
		$succeed = true;
	}

	if($succeed)  {
		@chmod($target, 0644); @unlink($source);
	} 
	return $succeed;
}


function uploadtolocal($upload,$dir='appimg',$target='',$exts=array('jpg','jpeg','png','gif')){
		global $_G;
		if($target=='dzz/images/default/icodefault.png' || $target=='dzz/images/default/widgetdefault.png' || preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $target)){
			$target='';
		}
		$source=$upload['tmp_name'];
		$ext=strtolower(substr(strrchr($upload['name'], '.'), 1, 10));
		if(!in_array($ext,$exts)) return false;
		if($target) {
			$target1=$_G['setting']['attachdir'].$target;
		}else{
			$subdir = $subdir1 = $subdir2 = '';
			$subdir1 = date('Ym');
			$subdir2 = date('d');
			$subdir = $subdir1.'/'.$subdir2.'/';
			$target1=$_G['setting']['attachdir'].$dir.'/'.$subdir.''.date('His').''.strtolower(random(16)).'.'.$ext;
			$target=str_replace($_G['setting']['attachdir'],'',$target1);
		}
		
		if(save_to_local($source, $target1)){
			return $target;
		}else{
			return false;
		}
	}
function upload_to_icon($upload,$target,$domain){
		global $_G;
			$source=$upload['tmp_name'];
			if(!$target){
				$imageext=array('jpg','jpeg','png','gif');
				$ext=strtolower(substr(strrchr($upload['name'], '.'), 1, 10));
				if(!in_array($ext,$imageext)) return false;
				$subdir = $subdir1 = $subdir2 = '';
				$subdir1 = date('Ym');
				$subdir2 = date('d');
				$subdir = $subdir1.'/'.$subdir2.'/';
				$target='icon/'.$subdir.''.$domain.'_'.strtolower(random(8)).'.'.$ext;
				$target_attach=$_G['setting']['attachdir'].$target;
			}else{
				$target_attach=$_G['setting']['attachdir'].$target;
			}
			if(save_to_local($source, $target_attach)){
				return $target;
			}else{
				return false;
			}
	}

function dzz_app_pic_save($FILE,$dir='appimg') {
	global $_G;
	$imageext=array('jpg','jpeg','png','gif');
	$ext=strtolower(substr(strrchr($FILE['name'], '.'), 1, 10));
	if(!in_array($ext,$imageext)) return lang('file_format_allowed');
		$subdir = $subdir1 = $subdir2 = '';
		$subdir1 = date('Ym');
		$subdir2 = date('d');
		$subdir = $subdir1.'/'.$subdir2.'/';
	$target=$dir.'/'.$subdir;
	$filename=date('His').''.strtolower(random(16));
	if(!$attach=io_dzz::UploadSave($FILE)){
		return lang('app_image_upload_failed');
	}
	$setarr = array(
		'uid' => $_G['uid'],
		'username' => $_G['username'],
		'dateline' => $_G['timestamp'],
		'aid' => $attach['aid'],
	);
	if($setarr['picid'] = DB::insert('app_pic', $setarr, 1)){
		C::t('attachment')->addcopy_by_aid($attach['aid']);
		return $setarr;
	}
	return false;
}

?>
