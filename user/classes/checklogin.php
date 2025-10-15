<?php
namespace user\classes;
class Checklogin {
    public function run() {
        global $_G;
        if (!$_G['uid']) {
            if($_GET['ajaxdata'] == 'json') {
                exit(json_encode(array('code' => 1, 'msg' => '请先登录','message' => '请先登录')));
            } else {
                include template('common/header_reload');
                echo "<script type=\"text/javascript\">";
                echo "location.href='user.php?mod=login';";
                echo "</script>";
                include template('common/footer_reload');
                exit();
            }
        }
    }
}