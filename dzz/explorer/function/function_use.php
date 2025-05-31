<?php

//解析类型条件
function parsefileType($type) {
    $ext = array();
    $extstr = '';
    switch ($type) {
        case 'image' :
            $ext = array('jpg', 'png', 'gif', 'jpeg');
            break;
        case 'pdf':
            $ext = array('pdf');
            break;
        case 'document':
            $ext = array('doc', 'docx', 'xls', 'ppt', 'pdf', 'dzzdoc', 'txt', 'html');
            break;
        case 'excel':
            $ext = array('xlsx', 'xls');
            break;
        case 'ppt':
            $ext = array('ppt', 'pptx');
            break;
    }
    return $ext;
}

//解析位置标志条件
function parsePositionFlag($flags) {
    $positionarr = array();
    foreach ($flags as $flag) {
        switch ($flag) {
            case 'isstarted':
                $rids = C::t('resources_collect')->fetch_rid_by_uid();
                $ridstr = '';
                foreach ($rids as $v) {
                    $ridstr .= "'" . $v['rid'] . "'" . ',';
                }
                $ridstr = substr($ridstr, 0, -1);
                $positionarr['rid'] = array($ridstr, 'in', 'and');
                break;
            case 'isdelete':
                $positionarr['isdelete'] = array(0, '>', 'and');
                break;
            default :

                break;
        }
    }

    return $positionarr;
}

/*function parseDateRange($daterange){
    $enddate = strtotime(date('Y-m-d',TIMESTAMP)) + 86400;
    $startdate = 0;
    $ext = array();
    switch ($daterange){
        case 1 :
            $startdate = strtotime(date('Y-m-d',TIMESTAMP));
            break;
        case -1:
            $startdate = date("Y-m-d",strtotime("-1 day"));
            break;
        case -7:
            $startdate = date("Y-m-d",strtotime("-7 day"));
            break;
        case -30:
            $startdate = date("Y-m-d",strtotime("-30 day"));
            break;
        case -90:
            $startdate = date("Y-m-d",strtotime("-90 day"));
            break;
    }
    return array('start'=>$startdate,'end'=>$enddate);
}*/
//解析用户条件
function parseUsers($users) {
    $conditionarr = array();
    switch ($users) {
        case 'self':
            $conditionarr = array(getglobal('uid'), '=', 'and');
            break;
        case 'noself':
            $conditionarr = array(' uid != ' . getglobal('uid') . ' and gid != 0 ', 'stringsql', 'and');
            break;
        default :
            $userstr = '';
            foreach ($users as $v) {
                $userstr .= "'" . $v . "'" . ',';
            }
            $conditionarr = substr($userstr, 0, -1);
            break;
    }
    return $conditionarr;
}

function atreplacement($matches) {
    global $at_users;
    include_once libfile('function/code');
    $uid = str_replace('u', '', $matches[2]);
    if (($user = C::t('user')->fetch($uid)) && $user['uid'] != $_G['uid']) {
        $at_users[] = $user['uid'];
        return '[uid=' . $user['uid'] . ']@' . $user['username'] . '[/uid]';
    } else {
        return $matches[0];
    }
}

function stripsAT($message, $all = 0) { //$all>0 时去除包裹的内容
    if ($all) {
        $message = preg_replace("/\[uid=(\d+)\](.+?)\[\/uid\]/i", '', $message);
        $message = preg_replace("/\[org=(\d+)\](.+?)\[\/org\]/i", '', $message);
    } else {
        $message = preg_replace("/\[uid=(\d+)\]/i", '', $message);
        $message = preg_replace("/\[\/uid\]/i", '', $message);
        $message = preg_replace("/\[org=(\d+)\]/i", '', $message);
        $message = preg_replace("/\[\/org\]/i", '', $message);
    }
    return $message;
}