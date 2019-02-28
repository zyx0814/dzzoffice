<?php
namespace  user\classes;

class Checklogin{
    public function run(){


        global $_G;

        if (!$_G['uid']) {

            include template('common/header_reload');

            echo "<script type=\"text/javascript\">";

            echo "location.href='user.php?mod=login';";

            echo "</script>";

            include template('common/footer_reload');

            exit();
        }
    }
}