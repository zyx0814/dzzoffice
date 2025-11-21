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
$orgid = intval($_GET['orgid']);
$navtitle = lang('export_user') . ' - ' . lang('appname');
if($_G['adminid']==1) {

}elseif($orgid) {
	if (!C::t('organization_admin')->ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
        showmessage('system_administrator_export');
    }
} else {
	showmessage('system_administrator_export');
}
require_once libfile('function/orguser');
$h1 = getProfileForImport();
$h0 = array_merge($h0, $h1);
if (!submitcheck('exportsubmit')) {
    $orgpath = C::t('organization')->getPathByOrgid($orgid);
    if (empty($orgpath)) $orgpath = lang('please_select_range_export');

    //默认选中
    $open = array();
    $patharr = C::t('organization')->getPathByOrgid($orgid, false);
    $arr = array_keys($patharr);
    array_pop($arr);
    $count = count($arr);
    if ($open[$arr[$count - 1]]) {
        if (count($open[$arr[$count - 1]]) > $count) $open[$arr[count($arr) - 1]] = $arr;
    } else {
        $open[$arr[$count - 1]] = $arr;
    }
    $openarr = json_encode(array('orgid' => $open));

    include template('export');
    exit();
} else {
    if (!is_array($_GET['item'])) showmessage('please_select_project_export');
    foreach ($h0 as $key => $value) {
        if (!in_array($key, $_GET['item'])) unset($h0[$key]);
    }
    require_once libfile('function/organization');
    $title = '';
    if ($org = C::t('organization')->fetch($orgid)) {
        $orgids = getOrgidTree($org['orgid']);
        if ($org['forgid'] > 0) {
            $toporgid = C::t('organization')->getTopOrgid($orgid);
            $toporg = C::t('organization')->fetch($toporgid);
            $title = $_G['setting']['sitename'] . '-' . $toporg['orgname'] . '-' . $org['orgname'];
        } else {
            $title = $_G['setting']['sitename'] . '-' . $org['orgname'];
        }
    } else {
        $title = $_G['setting']['sitename'];
    }
    require_once DZZ_ROOT . './core/class/class_PHPExcel.php';
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()->setCreator($_G['username'])
        ->setTitle($title . ' - ' . lang('user_information_table') . ' - DzzOffice')
        ->setSubject($title . ' - ' . lang('user_information_table'))
        ->setDescription($title . ' - ' . lang('user_information_table') . ' Export By DzzOffice  ' . date('Y-m-d H:i:s'))
        ->setKeywords($title . ' - ' . lang('user_information_table'))
        ->setCategory(lang('user_information_table'));
    $list = array();
    // Create a first sheet
    $objPHPExcel->setActiveSheetIndex(0);
    $j = 0;
    foreach ($h0 as $key => $value) {
        $index = getColIndex($j) . '1';
        $objPHPExcel->getActiveSheet()->setCellValue($index, $value);
        $list[1][$index] = $value;
        $j++;
    }
    $i = 2;
    $wheresql = 1;
    if ($orgid) {
        $uids = C::t('organization_user')->fetch_uids_by_orgid($orgids);
        $wheresql = " where  uid IN (" . dimplode($uids) . ")";
    } else {
        $wheresql = " where 1 ";
    }

    foreach (DB::fetch_all("select * from %t $wheresql", array('user')) as $user) {
        $profile = C::t('user_profile')->fetch_all($user['uid']);
        if ($profile) $value = array_merge($user, $profile[$user['uid']]);
        else $value = $user;
        if ($value['birthyear'] && $value['birthmonth'] && $value['birthday']) $value['birth'] = $value['birthyear'] . '-' . $value['birthmonth'] . '-' . $value['birthday'];
        if ($value['gender']) {
            if ($value['gender'] == 2) $value['gender'] = lang('woman');
            elseif ($value['gender'] == 1) $value['gender'] = lang('man');
            else $value['gender'] = '';
        }
        //获取用户的部门和职位
        if ($orgids = C::t('organization_user')->fetch_orgids_by_uid($value['uid'])) {
            $orgnames = array();
            $jobs = array();
            
            // 收集所有部门和职位信息
            foreach ($orgids as $key => $gid) {
                $orgpath = C::t('organization')->getPathByOrgid($gid);
                $orgname = str_replace('-', '/', $orgpath);
                if (!empty($orgname)) {
                    $orgnames[] = $orgname;
                    if ($job = DB::fetch_first("select j.name from %t u LEFT JOIN %t j ON u.jobid=j.jobid  where u.orgid=%d and u.uid=%d", array('organization_user', 'organization_job', $gid, $user['uid']))) {
                        if (!empty($job['name'])) {
                            $jobs[] = $job['name'];
                        } else {
                            $jobs[] = '';
                        }
                    } else {
                        $jobs[] = '';
                    }
                }
            }
            
            // 将多个部门和职位用逗号连接，过滤空值
            $value['orgname'] = implode(',', array_filter($orgnames));
            $value['job'] = implode(',', array_filter($jobs));
        }
        $j = 0;
        foreach ($h0 as $key1 => $fieldid) {
            $index = getColIndex($j) . ($i);
            $objPHPExcel->getActiveSheet()->setCellValue($index, $value[$key1]);
            $j++;
            $list[$i][$index] = $value[$key1];
        }
        $i++;
    }
    $objPHPExcel->setActiveSheetIndex(0);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $filename = $_G['setting']['attachdir'] . './cache/' . random(5) . '.xlsx';
    $objWriter->save($filename);

    $name = $title . ' - ' . lang('user_information_table') . '.xlsx';
    $name = '"' . (strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($name) : $name) . '"';

    $filesize = filesize($filename);
    $chunk = 10 * 1024 * 1024;
    if (!$fp = @fopen($filename, 'rb')) {
        exit(lang('export_failure'));
    }
    dheader('Date: ' . gmdate('D, d M Y H:i:s', TIMESTAMP) . ' GMT');
    dheader('Last-Modified: ' . gmdate('D, d M Y H:i:s', TIMESTAMP) . ' GMT');
    dheader('Content-Encoding: none');
    dheader('Content-Disposition: attachment; filename=' . $name);
    dheader('Content-Type: application/octet-stream');
    dheader('Content-Length: ' . $filesize);
    @ob_end_clean();
    if (getglobal('gzipcompress')) @ob_start('ob_gzhandler');
    while (!feof($fp)) {
        echo fread($fp, $chunk);
        @ob_flush();  // flush output
        @flush();
    }
    @unlink($filename);
    exit();
}
?>