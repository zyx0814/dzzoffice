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
$uid=$_G['uid'];
$do = isset($_GET['do']) ? $_GET['do'] : '';
$orgid = isset($_GET['orgid']) ? intval($_GET['orgid']) : '';
$typearr = array('image' => lang('photo'),
    'document' => lang('type_attach'),
    'link' => lang('type_link'),
    'video' => lang('video'),
	'folder' => lang('folder'),
    'dzzdoc' => 'DZZ' . lang('type_attach'),
    'attach' => lang('rest_attachment')
);
require libfile('function/organization');
if ($do == 'delete') {
	if (isset($_G['setting']['template']) && $_G['setting']['template'] === 'lyear') {
		$icoid = isset($_GET['icoid']) ? trim($_GET['icoid']) : '';
		if (empty($icoid)) {
			exit(json_encode(['msg' => 'access denied']));
		}
		$icoids = explode(',', $icoid);
		$sucessicoids = [];
		$failedicoids = [];

		foreach ($icoids as $icoid) {
			try {
				$return = IO::Delete($icoid, true);
				if (!$return['error']) {
					$sucessicoids[$return['rid']] = [
						'msg' => 'success',
						'name' => $return['name']
					];
					$dels[] =  $icoid . '_0';
				} else {
					$failedicoids[$icoid] = $return['error'];
				}
			} catch (Exception $e) {
				$failedicoids[$icoid] = 'An unexpected error occurred: ' . $e->getMessage();
			}
		}
		// 执行成功的条目数检查
		if (!empty($dels)) {
			Hook::listen('solrdel', $dels);
		}

		$response = [
			'msg' => !empty($failedicoids) ? '部分文件删除失败' : 'success',
			'success' => $sucessicoids,
			'failed' => $failedicoids
		];
		exit(json_encode($response));
	} else {
		$icoid = isset($_GET['icoid']) ? trim($_GET['icoid']) : '';
		$icoids = explode(',', $icoid);
		$ridarr = array();
		$bz = isset($_GET['bz']) ? trim($_GET['bz']) : '';
		foreach ($icoids as $icoid) {
			if (empty($icoid)) {
				continue;
			}
			$return = IO::Delete($icoid, true);
			if (!$return['error']) {
				//处理数据
				$arr['sucessicoids'][$return['rid']] = $return['rid'];
				$arr['msg'][$return['rid']] = 'success';
				$arr['name'][$return['rid']] = $return['name'];
				$ridarr[] = $return['rid'];
				$i++;
			} else {
				$arr['msg'][$return['rid']] = $return['error'];
				$dels[] =  $icoid.'_0';
			}
		}
		if (!$return['error']) {
			Hook::listen('solrdel',$dels);
			showmessage('do_success', $_GET['refer']);
		} else {
			showmessage($return['error'], $_GET['refer']);
		}
	}
  } elseif ($do == 'getinfo') {
	$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
	$type = isset($_GET['type']) ? trim($_GET['type']) : '';
	$pfid = isset($_GET['pfid']) ? intval($_GET['pfid']) : '';
	$field = isset($_GET['field']) ? $_GET['field'] : 'dateline';
	$limit = empty($_GET['limit']) ? 20 : $_GET['limit'];
	$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
	$page = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
	$start = ($page - 1) * $limit;
	$validfields = ['name', 'size', 'type', 'username', 'dateline'];
	$validSortOrders = ['asc', 'desc'];
	if (in_array($field, $validfields) && in_array($order, $validSortOrders)) {
		$order = "ORDER BY $field $order";
	} else {
		$order = 'ORDER BY dateline DESC';
	}
	$sql = "type!='app' and type!='shortcut'";
	$foldername = array();
	$param = array();
	if ($keyword) {
			$sql .= ' and (name like %s OR username=%s)';
			$param[] = '%' . $keyword . '%';
			$param[] = $keyword;
	}
	if ($type) {
			$sql .= ' and type=%s';
			$param[] = $type;
	}
	if ($pfid) {
			$sql .= ' and (pfid = %d)';
			$param[] = $pfid;
			$pathkey = DB::result_first("select pathkey from %t where fid = %d", array('resources_path', $pfid));
			$patharr = explode('-', str_replace('_', '', $pathkey));
			unset($patharr[0]);
			foreach (DB::fetch_all("select fname,fid from %t where fid in(%n)", array('folder', $patharr)) as $v) {
				
					$foldername[] = array('fid' => $v['fid'], 'fname' => $v['fname']);
			}
	} else {
			if ($orgid) {
				if ($org = C::t('organization')->fetch($orgid)) {
						$fids = array($org['fid']);
						foreach (DB::fetch_all("select fid from %t where pfid=%d", array('folder', $org['fid'])) as $value) {
								$fids[] = $value['fid'];
						}
						$sql .= ' and  pfid IN(%n)';
						$param[] = $fids;
				}
			}
	}
	$limitsql = 'limit ' . $start . ',' . $limit;
	if ($_G['adminid']) {
		$whereClause = $sql;
	} else {
		$whereClause = "uid = $uid AND $sql";
	}
	$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('resources') . " WHERE $whereClause", $param);
	if ($count) {
		$data = DB::fetch_all("SELECT rid FROM " . DB::table('resources') . " WHERE $whereClause $order $limitsql", $param);
	}
	$list = array();
	$id = $start + 1;
	foreach ($data as $value) {
		if (!$data = C::t('resources')->fetch_by_rid($value['rid'])) {
			continue;
		}
		//文件统计信息
		$filestatis = C::t('resources_statis')->fetch_by_rid($value['rid']);
		if($data['relpath'] == '/'){
			$data['relpath'] = '回收站';
		}
		if($data['isdelete']){
			$isdelete = '是';
		} else {
			$isdelete = '否';
		}
		if($data['type']!=='folder'){
			$copys = $data['copys'];
			$FileUri = IO::getFileUri($data['path'])?:'';
		} else {
			$copys = '';
			$FileUri = '';
		}
		$list[] = [
			"id" => $id++,
			"username" => '<a href="user.php?uid='.$data['uid'].'" target="_blank">'.$data['username'].'</a>',
			"rid" => $data['rid'],
			"name" => '<img class="icon" src="'.$data['img'].'">'.$data['name'],
			"dpath" => $data['dpath'],
			"size" => $data['fsize'],
			"type" => $data['ftype'],
			"ftype" => $data['type'],
			"oid" => $data['oid'],
			"relpath" => $data['relpath'],
			"dateline" => $data['fdateline'],
			"isdelete" => $isdelete?:'',
			"copys" => $copys?:'',
			"FileUri" => $FileUri,
			"downs" => $filestatis['downs']?:'',
			"views" => $filestatis['views']?:'',
			"edits" => $filestatis['edits']?:'',
		];
	}
	$breadcrumb = '<a href="javascript:;" class="btn btn-primary fid-btn" data-fid="">' . lang('all_typename_attach') . '</a>';
	if (!empty($foldername)) {
		$i = 0;
		foreach ($foldername as $v) {
			$i++;
			if ($i == count($foldername)) {
				$breadcrumb .= '<a href="javascript:;" class="btn btn-outline-primary fid-btn" data-fid="' . $v['fid'] . '">' .$v['fname']. '</a>';
			} else {
				$breadcrumb .= '<a href="javascript:;" class="btn btn-primary fid-btn" data-fid="' . $v['fid'] . '">' . $v['fname'] . '</a>';
			}
		}
	}
	$return = [
		"code"=> 0,
		"msg"=> "",
		"count"=> $count? $count : 0,
		"data" => $list? $list : [],
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
  } else {
	if (isset($_G['setting']['template']) && $_G['setting']['template'] == 'lyear') {
    } else {
		$perpage = 20;
		$pfid = isset($_GET['pfid']) ? intval($_GET['pfid']) : '';
		$type = isset($_GET['type']) ? trim($_GET['type']) : '';
		$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
		$page = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
		$start = ($page - 1) * $perpage;
		$gets = array(
			'mod' => 'filemanage',
			'keyword' => $keyword,
			'type' => $_GET['type'],
			'size' => $_GET['size'],
			'dateline' => $_GET['dateline'],
			'orgid' => $orgid,
			'pfid' => $pfid
		);
		$theurl = BASESCRIPT . "?" . url_implode($gets);
		$refer = $theurl . '&page=' . $page;
		if ($_GET['size'] == 'desc') {
			$order = 'ORDER BY size DESC';
		} elseif ($_GET['size'] == 'asc') {
			$order = 'ORDER BY size ASC';
		} elseif ($_GET['dateline'] == 'asc') {
			$order = 'ORDER BY dateline ASC';
		} else {
			$_GET['dateline'] = 'desc';
			$order = 'ORDER BY size DESC';
		}
		$sql = "type!='app' and type!='shortcut'";
		$foldername = array();
		$param = array();
		if ($keyword) {
			$sql .= ' and (name like %s OR username=%s)';
			$param[] = '%' . $keyword . '%';
			$param[] = $keyword;
		}
		if ($type) {
			$sql .= ' and type=%s';
			$param[] = $type;
		}
		if ($pfid) {
			$sql .= ' and (pfid = %d)';
			$param[] = $pfid;
			$pathkey = DB::result_first("select pathkey from %t where fid = %d", array('resources_path', $pfid));
			$patharr = explode('-', str_replace('_', '', $pathkey));
			unset($patharr[0]);
			foreach (DB::fetch_all("select fname,fid from %t where fid in(%n)", array('folder', $patharr)) as $v) {
				$foldername[] = array('fid' => $v['fid'], 'fname' => $v['fname']);
			}
		} else {
			if ($orgid) {
				if ($org = C::t('organization')->fetch($orgid)) {
					$fids = array($org['fid']);
					foreach (DB::fetch_all("select fid from %t where pfid=%d", array('folder', $org['fid'])) as $value) {
						$fids[] = $value['fid'];
					}
					$sql .= ' and  pfid IN(%n)';
					$param[] = $fids;
				}
			}
		}
		$limitsql = 'limit ' . $start . ',' . $perpage;
		if ($_G['adminid']) {
			$whereClause = $sql;
		} else {
			$whereClause = "uid = $uid AND $sql";
		}
		$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('resources') . " WHERE $whereClause", $param);
		if ($count) {
			$data = DB::fetch_all("SELECT rid FROM " . DB::table('resources') . " WHERE $whereClause $order $limitsql", $param);
			$multi = multi($count, $perpage, $page, $theurl);
		}
		$list = array();
		foreach ($data as $value) {
			if (!$sourcedata = C::t('resources')->fetch_by_rid($value['rid'])) {
				continue;
			}
			if($sourcedata['relpath'] == '/'){
				$sourcedata['relpath'] = '回收站';
			}
			$list[] = $sourcedata;
		}
    }
	if ($org = C::t('organization')->fetch($orgid)) {
		$orgpath = getPathByOrgid($org['orgid']);
		$org['depart'] = implode('-', ($orgpath));
	} else {
		$org = array();
		$org['depart'] = lang('select_a_organization_or_department');
		$org['orgid'] = $orgid;
	}
    include template('list');
}
?>