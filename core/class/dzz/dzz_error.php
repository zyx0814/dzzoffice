<?php
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
class dzz_error
{

	public static function system_error($message, $show = true, $save = true, $halt = true) {
		if(!empty($message)) {
			$message = lang($message);
		} else {
			$message = lang('error_unknow');
		}

		list($showtrace, $logtrace) = dzz_error::debug_backtrace();

		if($save) {
			$messagesave = '<b>'.$message.'</b><br><b>PHP:</b>'.$logtrace;
			dzz_error::write_error_log($messagesave);
		}

		if($show) {
			if(!defined('IN_MOBILE')) {
				dzz_error::show_error('system', "<li>$message</li>", $showtrace, 0);
			} else {
				dzz_error::mobile_show_error('system', "<li>$message</li>", $showtrace, 0);
			}
		}

		if($halt) {
			exit();
		} else {
			return $message;
		}
	}

	public static function template_error($message, $tplname) {
		$message = lang($message);
		$tplname = str_replace(DZZ_ROOT, '', $tplname);
		$message = $message.': '.$tplname;
		dzz_error::system_error($message);
	}

	public static function debug_backtrace() {
		$skipfunc[] = 'error->debug_backtrace';
		$skipfunc[] = 'error->db_error';
		$skipfunc[] = 'error->template_error';
		$skipfunc[] = 'error->system_error';
		$skipfunc[] = 'db_mysql->halt';
		$skipfunc[] = 'db_mysql->query';
		$skipfunc[] = 'DB::_execute';

		$show = $log = '';
		$debug_backtrace = debug_backtrace();
		krsort($debug_backtrace);
		foreach ($debug_backtrace as $k => $error) {
			$file = str_replace(DZZ_ROOT, '', $error['file']);
			$func = isset($error['class']) ? $error['class'] : '';
			$func .= isset($error['type']) ? $error['type'] : '';
			$func .= isset($error['function']) ? $error['function'] : '';
			if(in_array($func, $skipfunc)) {
				break;
			}
			$error[line] = sprintf('%04d', $error['line']);

			$show .= "<li>[Line: $error[line]]".$file."($func)</li>";
			$log .= !empty($log) ? ' -> ' : '';$file.':'.$error['line'];
			$log .= $file.':'.$error['line'];
		}
		return array($show, $log);
	}

	public static function db_error($message, $sql) {
		global $_G;

		list($showtrace, $logtrace) = dzz_error::debug_backtrace();

		$title = lang('db_'.$message);
		$title_msg = lang('db_error_message');
		$title_sql = lang('db_query_sql');
		$title_backtrace = lang('backtrace');
		$title_help = lang('db_help_link');

		$db = &DB::object();
		$dberrno = $db->errno();
		$dberror = str_replace($db->tablepre,  '', $db->error());
		$sql = dhtmlspecialchars(str_replace($db->tablepre,  '', $sql));

		$msg = '<li>[Type] '.$title.'</li>';
		$msg .= $dberrno ? '<li>['.$dberrno.'] '.$dberror.'</li>' : '';
		$msg .= $sql ? '<li>[Query] '.$sql.'</li>' : '';

		dzz_error::show_error('db', $msg, $showtrace, false);
		unset($msg, $phperror);

		$errormsg = '<b>'.$title.'</b>';
		$errormsg .= "[$dberrno]<br /><b>ERR:</b> $dberror<br />";
		if($sql) {
			$errormsg .= '<b>SQL:</b> '.$sql;
		}
		$errormsg .= "<br />";
		$errormsg .= '<b>PHP:</b> '.$logtrace;

		dzz_error::write_error_log($errormsg);
		exit();

	}

	public static function exception_error($exception) {

		if($exception instanceof DbException) {
			$type = 'db';
		} else {
			$type = 'system';
		}

		if($type == 'db') {
			$errormsg = '('.$exception->getCode().') ';
			$errormsg .= self::sql_clear($exception->getMessage());
			if($exception->getSql()) {
				$errormsg .= '<div class="sql">';
				$errormsg .= self::sql_clear($exception->getSql());
				$errormsg .= '</div>';
			}
		} else {
			$errormsg = $exception->getMessage();
		}

		$trace = $exception->getTrace();
		krsort($trace);

		$trace[] = array('file'=>$exception->getFile(), 'line'=>$exception->getLine(), 'function'=> 'break');
		$phpmsg = array();
		foreach ($trace as $error) {
			if(!empty($error['function'])) {
				$fun = '';
				if(!empty($error['class'])) {
					$fun .= $error['class'].$error['type'];
				}
				$fun .= $error['function'].'(';
				if(!empty($error['args'])) {
					$mark = '';
					foreach($error['args'] as $arg) {
						$fun .= $mark;
						if(is_array($arg)) {
							$fun .= 'Array';
						} elseif(is_bool($arg)) {
							$fun .= $arg ? 'true' : 'false';
						} elseif(is_int($arg)) {
							$fun .= (defined('DZZ_DEBUG') && DZZ_DEBUG) ? $arg : '%d';
						} elseif(is_float($arg)) {
							$fun .= (defined('DZZ_DEBUG') && DZZ_DEBUG) ? $arg : '%f';
						} else {
							$fun .= (defined('DZZ_DEBUG') && DZZ_DEBUG) ? '\''.dhtmlspecialchars(substr(self::clear($arg), 0, 10)).(strlen($arg) > 10 ? ' ...' : '').'\'' : '%s';
						}
						$mark = ', ';
					}
				}

				$fun .= ')';
				$error['function'] = $fun;
			}
			$phpmsg[] = array(
			    'file' => str_replace(array(DZZ_ROOT, '\\'), array('', '/'), $error['file']),
			    'line' => $error['line'],
			    'function' => $error['function'],
			);
		}

		self::show_error($type, $errormsg, $phpmsg);
		exit();

	}

	public static function show_error($type, $errormsg, $phpmsg = '', $typemsg = '') {
		global $_G;

		ob_end_clean();
		$gzip = getglobal('gzipcompress');
		ob_start($gzip ? 'ob_gzhandler' : null);

		$host = $_SERVER['HTTP_HOST'];
		$title = $type == 'db' ? 'Database' : 'System';
		echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>$host - $title Error</title>
	<meta http-equiv="Content-Type" content="text/html; charset={$_G['config']['output']['charset']}" />
	<meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" />
	<style type="text/css">
	<!--
	body { background-color: white; color: black; font: 9pt/11pt verdana, arial, sans-serif;}
	#container { width: 1024px; }
	#message   { width: 1024px; color: black; }

	.red  {color: red;}
	a:link     { font: 9pt/11pt verdana, arial, sans-serif; color: red; }
	a:visited  { font: 9pt/11pt verdana, arial, sans-serif; color: #4e4e4e; }
	h1 { color: #FF0000; font: 18pt "Verdana"; margin-bottom: 0.5em;}
	.bg1{ background-color: #FFFFCC;}
	.bg2{ background-color: #EEEEEE;}
	.table {background: #AAAAAA; font: 11pt Menlo,Consolas,"Lucida Console"}
	.info {
	    background: none repeat scroll 0 0 #F3F3F3;
	    border: 0px solid #aaaaaa;
	    border-radius: 10px 10px 10px 10px;
	    color: #000000;
	    font-size: 11pt;
	    line-height: 160%;
	    margin-bottom: 1em;
	    padding: 1em;
	}

	.help {
	    background: #F3F3F3;
	    border-radius: 10px 10px 10px 10px;
	    font: 12px verdana, arial, sans-serif;
	    text-align: center;
	    line-height: 160%;
	    padding: 1em;
	}

	.sql {
	    background: none repeat scroll 0 0 #FFFFCC;
	    border: 1px solid #aaaaaa;
	    color: #000000;
	    font: arial, sans-serif;
	    font-size: 9pt;
	    line-height: 160%;
	    margin-top: 1em;
	    padding: 4px;
	}
	-->
	</style>
</head>
<body>
<div id="container">
<h1>Dzz! $title Error</h1>
<div class='info'>$errormsg</div>


EOT;
		if(!empty($phpmsg)) {
			echo '<div class="info">';
			echo '<p><strong>PHP Debug</strong></p>';
			echo '<table cellpadding="5" cellspacing="1" width="100%" class="table">';
			echo '<tr class="bg2"><td>No.</td><td>File</td><td>Line</td><td>Code</td></tr>';
			if(is_array($phpmsg)) {
				foreach($phpmsg as $k => $msg) {
					$k++;
					echo '<tr class="bg1">';
					echo '<td>'.$k.'</td>';
					echo '<td>'.$msg['file'].'</td>';
					echo '<td>'.$msg['line'].'</td>';
					echo '<td>'.$msg['function'].'</td>';
					echo '</tr>';
				}
			} else {
				echo '<tr><td><ul>'.$phpmsg.'</ul></td></tr>';
			}
			echo '</table></div>';
		}


		$helplink = '';
		

		$endmsg = lang('error_end_message', array('host'=>$host));
		echo <<<EOT
<div class="help">$endmsg. $helplink</div>
</div>
</body>
</html>
EOT;
		$exit && exit();

	}

	public static function mobile_show_error($type, $errormsg, $phpmsg) {
		global $_G;

		ob_end_clean();
		ob_start();

		$host = $_SERVER['HTTP_HOST'];
		$phpmsg = trim($phpmsg);
		$title = 'Mobile '.($type == 'db' ? 'Database' : 'System');
		echo <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html>
<head>
	<title>$host - $title Error</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" />
	<style type="text/css">
	<!--
	body { background-color: white; color: black; }
	UL, LI { margin: 0; padding: 2px; list-style: none; }
	#message   { color: black; background-color: #FFFFCC; }
	#bodytitle { font: 11pt/13pt verdana, arial, sans-serif; height: 20px; vertical-align: top; }
	.bodytext  { font: 8pt/11pt verdana, arial, sans-serif; }
	.help  { font: 12px verdana, arial, sans-serif; color: red;}
	.red  {color: red;}
	a:link     { font: 8pt/11pt verdana, arial, sans-serif; color: red; }
	a:visited  { font: 8pt/11pt verdana, arial, sans-serif; color: #4e4e4e; }
	-->
	</style>
</head>
<body>
<table cellpadding="1" cellspacing="1" id="container">
<tr>
	<td id="bodytitle" width="100%">Dzz! $title Error </td>
</tr>
EOT;

		echo <<<EOT
<tr><td><hr size="1"/></td></tr>
<tr><td class="bodytext">Error messages: </td></tr>
<tr>
	<td class="bodytext" id="message">
		<ul> $errormsg</ul>
	</td>
</tr>
EOT;
		if(!empty($phpmsg)  && $type == 'db') {
			echo <<<EOT
<tr><td class="bodytext">&nbsp;</td></tr>
<tr><td class="bodytext">Program messages: </td></tr>
<tr>
	<td class="bodytext">
		<ul> $phpmsg </ul>
	</td>
</tr>
EOT;
		}
		$endmsg = lang('mobile_error_end_message', array('host'=>$host));
		echo <<<EOT
<tr>
	<td class="help"><br />$endmsg</td>
</tr>
</table>
</body>
</html>
EOT;
		$exit && exit();
	}

	public static function clear($message) {
		return str_replace(array("\t", "\r", "\n"), " ", $message);
	}

	public static function sql_clear($message) {
		$message = self::clear($message);
		$message = str_replace(DB::object()->tablepre, '', $message);
		$message = dhtmlspecialchars($message);
		return $message;
	}

	public static function write_error_log($message) {
		$loginfo=array("mark"=>"errorlog","content"=>$message);
        Hook::listen('systemlog',$loginfo);
		return;
	
		$message = dzz_error::clear($message);
		$time = time();
		$file =  DZZ_ROOT.'./data/log/'.date("Ym").'_errorlog.php';
		$hash = md5($message);

		$uid = getglobal('uid');
		$ip = getglobal('clientip');

		$user = '<b>User:</b> uid='.intval($uid).'; IP='.$ip.'; RIP:'.$_SERVER['REMOTE_ADDR'];
		$uri = 'Request: '.dhtmlspecialchars(dzz_error::clear($_SERVER['REQUEST_URI']));
		$message = "<?PHP exit;?>\t{$time}\t$message\t$hash\t$user $uri\n";
		if($fp = @fopen($file, 'rb')) {
			$lastlen = 50000;
			$maxtime = 60 * 10;
			$offset = filesize($file) - $lastlen;
			if($offset > 0) {
				fseek($fp, $offset);
			}
			if($data = fread($fp, $lastlen)) {
				$array = explode("\n", $data);
				if(is_array($array)) foreach($array as $key => $val) {
					$row = explode("\t", $val);
					if($row[0] != '<?PHP exit;?>') continue;
					if($row[3] == $hash && ($row[1] > $time - $maxtime)) {
						return;
					}
				}
			}
		}
		error_log($message, 3, $file);
	}

}