<?php
/* @authorcode  codestrings
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');
require libfile('function/code');
$filter=trim($_GET['filter']);
if($filter=='new'){//列出所有新通知
    $list=array();
    $nids=array();//new>0
    foreach(DB::fetch_all("select n.*,u.avatarstatus from %t n LEFT JOIN %t u ON n.authorid=u.uid where n.new>0 and n.uid=%d  order by dateline DESC",array('notification','user',$_G['uid'])) as $value){
        $value['dateline']=dgmdate($value['dateline'],'u');
		$value['note']=dzzcode($value['note'],1,1,1,1,1);
        $nids[]=$value['id'];
        $list[]=$value;
    }
    if($nids){//去除新标志
        C::t('notification')->update($nids,array('new'=>0));
    }
}elseif($filter=='checknew'){//检查有没有新通知
    $num=DB::result_first("select COUNT(*) from %t where new>0 and uid=%d",array('notification',$_G['uid']));
    exit(json_encode(array('sum'=>$num,'timeout'=>60*1000)));
}else{
    $list=array();
    $page = empty($_GET['page'])?1:intval($_GET['page']);
    $fromid = isset($_GET['appid']) ? intval($_GET['appid']):'';
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']):'';
    $searchsql = " and 1 ";
    $perpage=20;
    $start=($page-1)*$perpage;
    $gets = array(
        'mod'=>'system',
        'op' =>'notification',
        'filter'=>'all'
    );
    //获取通知包含类型
    $searchappid = array();
    foreach(DB::fetch_all("select distinct(from_id) from %t where uid = %d",array('notification',$_G['uid'])) as $v){
        $searchappid[] = $v['from_id'];
    }
    $searchcats = array();
    if(in_array(0,$searchappid)){
        $systemindex = array_search(0,$searchappid);
        unset($searchappid[$systemindex]);
        $searchcats[1] = array('appid'=>1,'appname'=>'系统','appico'=>'dzz/images/default/notice_system.png');
    }
    if(count($searchappid) > 0){
        foreach(DB::fetch_all("select appname,appid,appico from %t where appid in(%n)",array('app_market',$searchappid)) as $v){
            $searchcats[$v['appid']+1] = array('appid'=>$v['appid']+1,'appname'=>$v['appname'],'appico'=>$_G['setting']['attachurl'].$v['appico']);
        }
    }
    //如果接收到搜索条件按条件搜索
    //通知类型
    if(!is_string($fromid)){
        $gets['appid'] = $fromid;
        $appid = $fromid -1;
        $searchsql .= " and n.from_id = {$appid}";
        $navtitle=$searchcats[$fromid]['appname'];
    }else{
        $navtitle='全部通知';
    }
    $params = array('notification','user','app_market',$_G['uid']);
    $countparam = array('notification',$_G['uid']);
    //通知内容
    if($keyword){
        $searchsql .= ' and n.wx_note like(%s)';
        $gets['keyword'] = $keyword;
        $countparam[] = '%' . $keyword . '%';
        $params[] = '%' . $keyword . '%';
    }


    $theurl = BASESCRIPT."?".url_implode($gets);
    $list=array();
    if($count=DB::result_first("select COUNT(*) from %t n where n.uid=%d $searchsql",$countparam)){
        foreach(DB::fetch_all("select n.*,u.avatarstatus,a.appico from %t n LEFT JOIN %t u ON n.authorid=u.uid left join %t a on n.from_id = a.appid where n.uid=%d $searchsql order by n.dateline DESC limit $start,$perpage",$params) as $value){
            $value['dateline']=dgmdate($value['dateline'],'u');
			$value['note']=dzzcode($value['note'],1,1,1,1,1);
			if(!$value['appico']){
			    $value['appico'] = 'dzz/images/default/notice_system.png';
            }else{
                $value['appico'] = $_G['setting']['attachurl']. $value['appico'];
            }
            $list[]=$value;
        }
    }
    $next=false;
    if($count && $count>$start+count($list)) $next=true;
    $theurl = DZZSCRIPT . "?" . url_implode ($gets);//分页链接
    $multi = multi($count , $perpage ,$page, $theurl  );
  /* if($_GET['do']=='list'){
        include template('notification_list_item');
    }else{*/
        include template('notification_list');
  //  }
    dexit();
}

include template('notification');
dexit();
?>