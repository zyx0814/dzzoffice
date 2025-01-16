<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ')) {
	exit('Access Denied');
}
$navtitle = lang('appname');
$typearr = array('folder' => lang('catalogue'), 'image' => lang('photo'), 'document' => lang('type_attach'), 'dzzdoc' => 'Dzz'.lang('type_attach'), 'video' => lang('type_video'), 'attach' => lang('attachment'), 'link' => lang('type_link'), 'url' => lang('other'));
$type = trim($_GET['type']);
$do = isset($_GET['do']) ? $_GET['do'] : '';
$page = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
$limit = empty($_GET['limit']) ? 20 : $_GET['limit'];
$start = ($page - 1) * $limit;
if ($do == 'getinfo') {
	$field = in_array($_GET['field'], array('title', 'dateline', 'type', 'count', 'username')) ? trim($_GET['field']) : 'dateline';
  $order = in_array($_GET['order'], array('asc', 'desc')) ? trim($_GET['order']) : 'DESC';
	$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
	$username = trim($_GET['username']);
  $uid = intval($_GET['uid']);
  $uid1=$_G['uid'];
  $orderby = " order by $field " . $order;
  $sql = "1";
  $param = array('shares');
  if ($type) {
    $sql .= " and type=%s";
    $param[] = $type;
  }
  if ($keyword) {
    $sql .= " and title LIKE %s";
    $param[] = '%' . $keyword . '%';
  }
  if ($username) {
    $sql .= " and username=%s";
    $param[] = $username;
  }
  if ($uid) {
    $sql .= " and uid=%d";
    $param[] = $uid;
  }
  if ($_G['adminid']) {
    if ($count = DB::result_first("SELECT COUNT(*) FROM %t WHERE $sql", $param)) {
      $list = DB::fetch_all("SELECT * FROM %t WHERE $sql $orderby limit $start,$limit", $param);
    }
  }else{
    if ($count = DB::result_first("SELECT COUNT(*) FROM %t WHERE uid =$uid1 and $sql", $param)) {
      $list = DB::fetch_all("SELECT * FROM %t WHERE uid =$uid1 and $sql $orderby limit $start,$limit", $param);
    }
  }
  $sharestatus = array('-5'=>lang('sharefile_isdeleted_or_positionchange'),'-4' => '<span class="layui-badge">'.lang('been_blocked').'</span>', '-3' => '<span class="layui-badge">'.lang('file_been_deleted').'</span>', '-2' => '<span class="layui-badge layui-bg-gray">'.lang('degree_exhaust').'</span>', '-1' => '<span class="layui-badge layui-bg-gray">'.lang('logs_invite_status_4').'</span>', '0' => '<span class="layui-badge layui-bg-blue">'.lang('founder_upgrade_normal').'</span>');
  $id = $start + 1;
  $data = array();
  foreach ($list as $value) {
      $data[] = [
        "id" => $id++,
        "sid" => $value['id'],
        "username" => '<a href="user.php?uid='.$value['uid'].'" target="_blank">'.$value['username'].'</a>',
        "title" => $value['title'],
        "status" => $sharestatus[$value['status']],
        "type" => getFileTypeName($value['type'], $value['ext']),
        "endtime" => $value['endtime'] ? dgmdate($value['endtime'], 'Y-m-d') : '',
        "password" => $value['password'] ? dzzdecode($value['password']) : '',
        "dateline" => $value['dateline'] ? dgmdate($value['dateline']) : '',
        "count" => $value['count'] ? $value['count'] : '',
        "downs" => $value['downs'] ? $value['downs'] : '',
        "sharelink" => C::t('shorturl')->getShortUrl(getglobal('siteurl').'index.php?mod=shares&sid='.dzzencode($value['id'])),
        "number" => $value['times'] ? $value['count'] .'/'.$value['times'] : '',
      ];
  }
	$return = [
		"code"=> 0,
		"msg"=> "",
		"count"=> $count? $count : 0,
		"data" => $data? $data : [],
		"breadcrumb" => $breadcrumb,
	];
	$jsonReturn = json_encode($return);
	if ($jsonReturn === false) {
		$errorMessage = json_last_error_msg();
		$errorResponse = [
			"code" => 1,
			"msg" => "JSON 编码失败，请刷新重试: " . $errorMessage,
			"count" => 0,
			"data" => [],
		];
		exit(json_encode($errorResponse));
	}
	exit($jsonReturn);
  } elseif (isset($_G['setting']['template']) && $_G['setting']['template'] == 'lyear') {
  } else {
    $keyword = trim($_GET['keyword']);
    $username = trim($_GET['username']);
    $asc = isset($_GET['asc']) ? intval($_GET['asc']) : 1;
    $uid = intval($_GET['uid']);
    $order = in_array($_GET['order'], array('title', 'dateline', 'type', 'size', 'count')) ? trim($_GET['order']) : 'dateline';
    $gets = array('mod' => 'share', 'type' => $type, 'keyword' => $keyword, 'order' => $order, 'asc' => $asc, 'uid' => $uid, 'username' => $username);
    $theurl = BASESCRIPT . "?" . url_implode($gets);
    $orderby = " order by $order " . ($asc ? 'DESC' : '');

    $sql = "1";
    $param = array('shares');
    if ($type) {
      $sql .= " and type=%s";
      $param[] = $type;
      $navtitle=$typearr[$type].' - '.lang('appname');
    }else{
      $navtitle= lang('appname');
    }
    if ($keyword) {
      $sql .= " and title LIKE %s";
      $param[] = '%' . $keyword . '%';
    }
    if ($username) {
      $sql .= " and username=%s";
      $param[] = $username;
    }
    if ($uid) {
      $sql .= " and uid=%d";
      $param[] = $uid;
    } 
    $list = array();
    if ($count = DB::result_first("SELECT COUNT(*) FROM %t WHERE $sql", $param)) {
      $list = DB::fetch_all("SELECT * FROM %t WHERE $sql $orderby limit $start,$limit", $param); 
      foreach ($list as $k=> $value) {
        $value['sharelink'] =  C::t('shorturl')->getShortUrl(getglobal('siteurl').'index.php?mod=shares&sid='.dzzencode($value['id']));
        if ($value['dateline'])
          $value['fdateline'] = dgmdate($value['dateline']);
        if ($value['password'])
          $value['password'] = dzzdecode($value['password']);
        if ($value['endtime'])
          $value['fendtime'] = dgmdate($value['endtime'], 'Y-m-d');
        $value['fsize'] = formatsize($value['size']);
        $value['ftype'] = getFileTypeName($value['type'], $value['ext']);
        if ($value['type'] == 'folder')
          $value['img'] = 'dzz/images/extimg/folder.png';
        if ($value['img'])
          $value['img'] = str_replace('dzz/images/extimg/', 'dzz/images/extimg_small/', $value['img']);
        if ($value['type'] == 'image' && $value['status'] == -3)
          $value['img'] = '';
        $value['fstatus'] = $sharestatus[$value['status']];
        if (is_file($_G['setting']['attachdir'] . './qrcode/' . $value['sid'][0] . '/' . $value['sid'] . '.png'))
          $value['qrcode'] = $_G['setting']['attachurl'] . './qrcode/' . $value['sid'][0] . '/' . $value['sid'] . '.png';
        $value['shareurl'] = $_G['siteurl'] . 's.php?sid=' . $value['sid'];
        $list[$k] = $value;
      }
      $multi = multi($count, $limit, $page, $theurl, 'pull-right');
    }
  }
  include template('share');
?>
