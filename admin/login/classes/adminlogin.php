<?php
namespace  admin\login\classes; 
use \core as C; 
class Adminlogin{
    public function run(){ 
        $dzz = C::app();
        $dzz->init(); 
        $admincp = new \dzz_admincp();
        $admincp->core  =  $dzz;
        $admincp->init();
    }
}