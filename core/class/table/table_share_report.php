<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class table_share_report extends dzz_table {
    public function __construct() {

        $this->_table = 'share_report';
        $this->_pk = 'id';
        parent::__construct();
    }

    /**
     * 获取举报类型
     * @return array 举报类型数组
     */
    public function get_report_types() {
        return array(
            1 => '侵权',
            2 => '色情',
            3 => '暴力',
            4 => '政治',
            5 => '其他'
        );
    }

    /**
     * 添加举报记录
     * @param int $uid 用户ID
     * @param array $setarr 举报信息
     * @return array 添加结果
     */
    public function addreport($uid, $setarr) {
        $ret = array();
        if (!$uid) {
            $ret['error'] = '用户ID不能为空';
            return $ret;
        }
        
        if (!$setarr['sid']) {
            $ret['error'] = '分享ID不能为空';
            return $ret;
        }
        
        if (!$setarr['type']) {
            $ret['error'] = '举报类型不能为空';
            return $ret;
        }

        $reporttypes = self::get_report_types();
        if (!$reporttypes[$setarr['type']]) {
            $ret['error'] = '举报类型错误';
            return $ret;
        }

        if ($setarr['type'] == 5 && !$setarr['desc']) {
            $ret['error'] = lang('please_input_report_reason');
            return $ret;
        }
        // 检查用户是否已经举报过该分享
        $isreport = DB::fetch_first("SELECT * FROM %t WHERE sid=%d AND uid=%d", array($this->_table, $setarr['sid'], $uid));
        
        if ($isreport) {
            $ret['error'] = '您已举报过该分享，请等待管理员处理';
            return $ret;
        }
        // 未举报，添加举报记录
        $data = array(
            'sid' => $setarr['sid'],
            'uid' => $uid,
            'username' => $setarr['username'],
            'type' => $setarr['type'],
            'desc' => $setarr['desc'],
            'dateline' => TIMESTAMP
        );

        if (parent::insert($data)) {
            global $_G;
            //发送通知给管理员
            $reporttxt = '用户 ' . $_G['username'] . ' 举报了分享标题为 ' . dhtmlspecialchars($setarr['title']) . ' 的分享，举报类型：' . $reporttypes[$setarr['type']] . '，请管理员及时处理。';
            foreach (C::t('user')->fetch_all_by_adminid(1) as $value) {
                if ($value['uid'] != $_G['uid']) {
                    $notevars = array(
                        'from_id' => 0,
                        'from_idtype' => 'sharereport',
                        'note_url' => DZZSCRIPT . '?mod=share&op=report',
                        'author' => $_G['username'],
                        'authorid' => $_G['uid'],
                        'note_title' => '分享举报通知',
                        'note_message' => $reporttxt
                    );
                    $action = 'share_report';
                    $type = 'share_report_' . $setarr['sid'] . '_' . $value['uid'];

                    dzz_notification::notification_add($value['uid'], $type, $action, $notevars);
                }
            }
            $ret['success'] = true;
            return $ret;
        }
        $ret['error'] = '举报提交失败';
        return $ret;
    }

    /**
     * 处理举报
     * @param int $id 举报ID
     * @param int $status 处理状态
     * @return bool 是否成功
     */
    public function handle_share_report($id, $status) {
        // 检查举报是否存在
        $report = DB::fetch_first("SELECT * FROM %t WHERE id=%d", array('share_report', $id));
        if (!$report) {
            return false;
        }
        
        // 更新举报状态
        $result = DB::update('share_report', array(
            'status' => $status,
            'modifyTime' => TIMESTAMP
        ), array('id' => $id));
        
        // 如果设置为禁止分享，则同时更新分享状态
        if ($result && $status == 2) {
            DB::update('shares', array('status' => -4), array('id' => $report['sid']));
        }
        
        return $result ? true : false;
    }

    /**
     * 根据分享ID删除所有相关的举报记录
     * @param int $sid 分享ID
     * @return bool 是否成功
     */
    public function delete_by_sid($sid) {
        if (!$sid) return false;
        
        // 删除所有与该分享ID相关的举报记录
        $result = DB::delete($this->_table, array('sid' => $sid));
        
        // 返回影响的行数，大于等于0表示删除成功
        return $result !== false ? true : false;
    }
}