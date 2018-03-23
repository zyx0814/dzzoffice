<?php
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class helper_log {

	public static function runlog($file, $message, $halt=0) {
		$loginfo=array("mark"=>$file,"content"=>$message);
        Hook::listen('systemlog',$loginfo);
		if($halt) {
			exit();
		}
		return;
	
		global $_G; 
		$nowurl = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
		$log = dgmdate($_G['timestamp'], 'Y-m-d H:i:s')."\t".$_G['clientip']."\t$_G[uid]\t{$nowurl}\t".str_replace(array("\r", "\n"), array(' ', ' '), trim($message))."\n";
		helper_log::writelog($file, $log);
		if($halt) {
			exit();
		}
	}

	public static function writelog($file, $log) {
		$loginfo=array("mark"=>$file,"content"=>$log);
        Hook::listen('systemlog',$loginfo);
		return;
		
		global $_G;
		$yearmonth = dgmdate(TIMESTAMP, 'Ym', $_G['setting']['timeoffset']);
		$logdir = DZZ_ROOT.'./data/log/';
		$logfile = $logdir.$yearmonth.'_'.$file.'.php';
		if(@filesize($logfile) > 1024) {
			$dir = opendir($logdir);
			$length = strlen($file);
			$maxid = $id = 0;
			while($entry = readdir($dir)) {
				if(strpos($entry, $yearmonth.'_'.$file) !== false) {
					$id = intval(substr($entry, $length + 8, -4));
					$id > $maxid && $maxid = $id;
				}
			}
			closedir($dir);

			$logfilebak = $logdir.$yearmonth.'_'.$file.'_'.($maxid + 1).'.php';
			@rename($logfile, $logfilebak);
		}
		if($fp = @fopen($logfile, 'a')) {
			@flock($fp, 2);
			if(!is_array($log)) {
				$log = array($log);
			}
			foreach($log as $tmp) {
				fwrite($fp, "<?PHP exit;?>\t".str_replace(array('<?', '?>'), '', $tmp)."\n");
			}
			fclose($fp);
		}
	}
}

?>