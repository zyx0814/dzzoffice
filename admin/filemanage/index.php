<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {
    exit('Access Denied');
}
$navtitle = lang('appname');
define('DZZSCRIPT', 'index.php');
$typearr = array('image' => lang('photo'),
    'document' => lang('type_attach'),
    'link' => lang('type_link'),
    'video' => lang('online_video'),
    'dzzdoc' => 'DZZ' . lang('type_attach'),
    'attach' => lang('rest_attachment')
);
require libfile('function/organization');
if ($_GET['do'] == 'delete') {
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

} else {
    $perpage = 20;
    $pfid = isset($_GET['pfid']) ? intval($_GET['pfid']) : '';
    $type = isset($_GET['type']) ? trim($_GET['type']) : '';
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $orgid = isset($_GET['orgid']) ? intval($_GET['orgid']) : '';
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
        }/*else{
            $sql .= ' and pfid > 0';
        }*//* else {
            $flags = array('home', 'organization');
            $fids = C::t('folder')->fetch_fid_by_flags($flags);
            $sql .= ' and  pfid IN(%n)';
            $param[] = $fids;
        }*/
    }
    $limitsql = 'limit ' . $start . ',' . $perpage;
    if ($count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('resources') . " WHERE $sql", $param)) {
        $data = DB::fetch_all("SELECT rid FROM " . DB::table('resources') . " WHERE $sql $order $limitsql", $param);
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
    if ($org = C::t('organization')->fetch($orgid)) {
        $orgpath = getPathByOrgid($org['orgid']);
        $org['depart'] = implode('-', ($orgpath));
    } else {
        $org = array();
        $org['depart'] = lang('select_a_organization_or_department');
        $org['orgid'] = $orgid;
    }
    /*if ($count > $perpage*$page) {
        $nextpage = $page + 1;
    } else {
        $naxtpage = 0;
    }*/
    include template('list');
}
?>
