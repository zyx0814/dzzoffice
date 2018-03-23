<?php
namespace  misc\classes;

use \C;
class Init{
    public function dzzInitbefore(){

        $dzz = C::app();

		$dzz->reject_robot();
		$modarray = array('seccode','sendmail', 'stat', 'seluser','ajax','syscache','movetospace','setunrun','upgrade','sendwx');

		
		$mod = getgpc('mod');
		$mod = (empty($mod) || !in_array($mod, $modarray)) ? 'error' : $mod;
		if(in_array($mod, array('seccode', 'movetospace','setunrun','ajax','syscache','stat','sendmail','sendwx'))) {
			define('ALLOWGUEST', 1);
		}
		$dzz->cachelist = array();
		switch ($mod) {
			case 'seccode':
			case 'syscache':
			case 'seandmail':
			case 'sendwx':
			case 'movetospace':
				$dzz->init_cron = false;
				$dzz->init_session = false;
				break;
			default:
				break;
		}
		
    }
}