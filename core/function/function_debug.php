<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

function debugmessage($ajax = 0) {
    $m = function_exists('memory_get_usage') ? number_format(memory_get_usage()) : '';
    $mt = function_exists('memory_get_peak_usage') ? number_format(memory_get_peak_usage()) : '';
    if ($m) {
        $m = '<em>内存:</em> <s>' . $m . '</s> bytes' . ($mt ? ', 峰值 <s>' . $mt . '</s> bytes' : '') . '<br />';
    }
    global $_G;
    $debugfile = $_G['adminid'] == 1 ? '_debugadmin.php' : '_debug.php';
    $akey = md5($_G['authkey'] . random(8));
    if (!defined('DZZ_DEBUG') || !DZZ_DEBUG || defined('IN_ARCHIVER') || defined('IN_MOBILE')) {
        return;
    }
    $phpinfok = 'I';
    $viewcachek = 'C';
    $mysqlplek = 'P';
    $includes = get_included_files();
    require_once DZZ_ROOT . './core/core_version.php';

    $sqldebug = '';
    $ismysqli = DB::$driver == 'db_driver_mysqli' ? 1 : 0;
    $n = $dzz_table = 0;
    $sqlw = array('Using filesort' => 0, 'Using temporary' => 0);
    $db = DB::object();
    $queries = count($db->sqldebug);
    $sqltime = 0;
    foreach ($db->sqldebug as $string) {
        $sqltime += $string[1];
        $extra = $dt = '';
        $n++;
        $sql = preg_replace('/' . preg_quote($_G['config']['db']['1']['tablepre']) . '[\w_]+/', '<font color=blue>\\0</font>', nl2br(dhtmlspecialchars($string[0])));
        !empty($string[4]) && $sql .= '; // '.print_r($string[4], 1);
        $sqldebugrow = '<div id="sql_' . $n . '" style="display:none;padding:0">';
        if (preg_match('/^SELECT /', $string[0])) {
            $query = DB::query('EXPLAIN '.$string[0], !empty($string[4]) ? $string[4] : []);
            $i = 0;
            $sqldebugrow .= '<table style="border-bottom:none">';
            while ($row = DB::fetch($query)) {
                if (!$i) {
                    $sqldebugrow .= '<tr style="border-bottom:1px dotted gray"><td>&nbsp;' . implode('&nbsp;</td><td>&nbsp;', array_keys($row)) . '&nbsp;</td></tr>';
                    $i++;
                }
                if (strexists($row['Extra'], 'Using filesort')) {
                    $sqlw['Using filesort']++;
                    $extra .= $row['Extra'] = str_replace('Using filesort', '<font color=red>Using filesort</font>', $row['Extra']);
                }
                if (strexists($row['Extra'], 'Using temporary')) {
                    $sqlw['Using temporary']++;
                    $extra .= $row['Extra'] = str_replace('Using temporary', '<font color=red>Using temporary</font>', $row['Extra']);
                }
                $sqldebugrow .= '<tr><td>&nbsp;' . implode('&nbsp;</td><td>&nbsp;', $row) . '&nbsp;</td></tr>';
            }
            $sqldebugrow .= '</table>';
        }
        $sqldebugrow .= '<table><tr style="border-bottom:1px dotted gray"><td width="400">File</td><td width="80">Line</td><td>Function</td></tr>';
        foreach ($string[2] as $error) {
            $error['file'] = str_replace([DZZ_ROOT, '\\'], ['', '/'], $error['file']);
            $error['class'] = $error['class'] ?? '';
            $error['type'] = $error['type'] ?? '';
            $error['function'] = $error['function'] ?? '';
            $sqldebugrow .= "<tr><td>{$error['file']}</td><td>{$error['line']}</td><td>{$error['class']}{$error['type']}{$error['function']}()</td></tr>";
            if (strexists($error['file'], 'dzz/dzz_table') || strexists($error['file'], 'table/table')) {
                $dt = ' &bull; ' . $error['file'];
                $dzz_table++;
            }
        }
        $sqldebugrow .= '</table></div>' . ($extra ? $extra . '<br />' : '') . '<br />';

        $sqldebug .= '<li><span style="cursor:pointer" onclick="document.getElementById(\'sql_' . $n . '\').style.display = document.getElementById(\'sql_' . $n . '\').style.display == \'\' ? \'none\' : \'\'"><s>' . $string[1] . 's</s> ' . $dt . '<br />' . $sql . '</span><br /></li>' . $sqldebugrow;
    }
    $ajaxhtml = 'data/' . $debugfile . '_ajax.php';
    if ($ajax) {
        $idk = substr(md5($_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING']), 0, 4);
        $sqldebug = '<b style="cursor:pointer" onclick="document.getElementById(\'' . $idk . '\').style.display=document.getElementById(\'' . $idk . '\').style.display == \'\' ? \'none\' : \'\'">Queries: </b> ' . $queries . ' (' . $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'] . ')<ol id="' . $idk . '" style="display:none">' . $sqldebug . '</ol><br>';
        file_put_contents(DZZ_ROOT . './' . $ajaxhtml, $sqldebug, FILE_APPEND);
        return;
    }
    file_put_contents(DZZ_ROOT . './' . $ajaxhtml, '<?php ' . _get_addslashes() . ' if(empty($_GET[\'k\']) || $_GET[\'k\'] != \'' . $akey . '\') { exit; } ?><style>body,table { font-size:12px; }table { width:90%;border:1px solid gray; }</style><a href="javascript:;" onclick="location.href=location.href">Refresh</a><br />');
    foreach ($sqlw as $k => $v) {
        $sqlw[$k] = $k . ': ' . $v;
    }
    $sqlw = '(' . ($dzz_table ? 'dzz_table: ' . $dzz_table . ($sqlw ? ', ' : '') : '') . ($sqlw ? '<s>' . implode(', ', $sqlw) . '</s>' : '') . ')';

    $debug = '<?php ' . _get_addslashes() . ' if(empty($_GET[\'k\']) || $_GET[\'k\'] != \'' . $akey . '\') { exit; }';
    $debug .= "header('Content-Type: text/html; charset=" . CHARSET . "'); ?>";
    if ($_G['adminid'] == 1 && !$ajax) {
        $debug .= '<?php
if(isset($_GET[\'' . $phpinfok . '\'])) { phpinfo(); exit; }
elseif(isset($_GET[\'' . $viewcachek . '\'])) {
	chdir(\'../\');
	define(\'APPTYPEID\', 200);
	define(\'DZZSCRIPT\', \'index.php\');
	define(\'CURSCRIPT\', \'dzz\');
	require \'./core/coreBase.php\';
	$dzz = C::app();
	$dzz->init();
	echo \'<style>body { font-size:12px; }</style>\';
	if(!isset($_GET[\'c\'])) {
		$query = DB::query("SELECT cname FROM ".DB::table("syscache"));
		while($names = DB::fetch($query)) {
			echo \'<a href="' . $debugfile . '?k=' . $akey . '&' . $viewcachek . '&c=\'.$names[\'cname\'].\'" target="_blank" style="float:left;width:200px">\'.$names[\'cname\'].\'</a>\';
		}
	} else {
		$cache = DB::fetch_first("SELECT * FROM ".DB::table("syscache")." WHERE cname=\'".$_GET[\'c\']."\'");
		echo \'$_G[\\\'cache\\\'][\'.$_GET[\'c\'].\']<br>\';
		debug($cache[\'ctype\'] ? dunserialize($cache[\'data\']) : $cache[\'data\']);
	}
	exit;
}
elseif(isset($_GET[\'' . $mysqlplek . '\'])) {
	chdir(\'../\');
	define(\'APPTYPEID\', 200);
	define(\'DZZSCRIPT\', \'index.php\');
	define(\'CURSCRIPT\', \'dzz\');
	require \'./core/coreBase.php\';
	$dzz = C::app();
	$dzz->init();
	if(!empty($_GET[\'Id\'])) {
		$query = DB::query("KILL ".floatval($_GET[\'Id\']), \'SILENT\');
	}
	$i = 0;
	$query = DB::query("SHOW FULL PROCESSLIST");
	echo \'<style>table { font-size:12px; }</style>\';
	echo \'<table style="border-bottom:none">\';
	while($row = DB::fetch($query)) {
		if(!$i) {
			echo \'<tr style="border-bottom:1px dotted gray"><td>&nbsp;</td><td>&nbsp;\'.implode(\'&nbsp;</td><td>&nbsp;\', array_keys($row)).\'&nbsp;</td></tr>\';
			$i++;
		}
		echo \'<tr><td><a href="' . $debugfile . '?k=' . $akey . '&P&Id=\'.$row[\'Id\'].\'">[Kill]</a></td><td>&nbsp;\'.implode(\'&nbsp;</td><td>&nbsp;\', $row).\'&nbsp;</td></tr>\';
	}
	echo \'</table>\';
	exit;
}
		?>';
    }
    $debug .= '<!DOCTYPE html><html><head>';
    $debug .= '<meta charset="' . CHARSET . '" />';
    $debug .= '<meta name="renderer" content="webkit" /><meta http-equiv="X-UA-Compatible" content="IE=edge" />';
    $debug .= "<script src='../static/jquery/jquery.min.js'></script><script src='../static/js/common.js'></script><script>
	function switchTab(prefix, current, total, activeclass) {
	activeclass = !activeclass ? 'a' : activeclass;
	for(var i = 1; i <= total;i++) {
		if(!document.getElementById(prefix + '_' + i)) {
			continue;
		}
		var classname = ' '+document.getElementById(prefix + '_' + i).className+' ';
		document.getElementById(prefix + '_' + i).className = classname.replace(' '+activeclass+' ','').substr(1);
		document.getElementById(prefix + '_c_' + i).style.display = 'none';
	}
	document.getElementById(prefix + '_' + current).className = document.getElementById(prefix + '_' + current).className + ' '+activeclass;
	document.getElementById(prefix + '_c_' + current).style.display = '';
	parent.document.getElementById('_debug_iframe').height = (Math.max(document.documentElement.clientHeight, document.body.offsetHeight) + 150) + 'px';
	}
	</script>";

    if (!defined('IN_ADMINCP') && file_exists(DZZ_ROOT . './static/image/common/temp-grid.png')) $debug .= <<<EOF
<script type="text/javascript">
var s = '<button style="position: fixed; width: 40px; right: 0; top: 30px; border: none; border:1px solid orange;background: yellow; color: red; cursor: pointer;" onclick="var pageHight = top.document.body.clientHeight;document.getElementById(\'tempgrid\').style.height = pageHight + \'px\';document.getElementById(\'tempgrid\').style.visibility = top.document.getElementById(\'tempgrid\').style.visibility == \'hidden\'?\'\':\'hidden\';o.innerHTML = o.innerHTML == \'网格\'?\'关闭\':\'网格\';">网格</button>';
s += '<div id="tempgrid" style="position: absolute; top: 0px; left: 50%; margin-left: -500px; width: 1000px; height: 0; background: url(static/image/common/temp-grid.png); visibility :hidden;"></div>';
top.document.getElementById('_debug_div').innerHTML = s;
</script>
EOF;

    $_GS = $_GA = '';
    if ($_G['adminid'] == 1) {
        foreach ($_G as $k => $v) {
            if (is_array($v)) {
                if ($k != 'lang') {
                    $_GA .= "<li><a name=\"S_$k\"></a>['$k'] => " . nl2br(str_replace('  ', '&nbsp;', dhtmlspecialchars(print_r($v, true)))) . '</li>';
                }
            } elseif (is_object($v)) {
                $_GA .= "<li>['$k'] => <i>object of " . get_class($v) . "</i></li>";
            } else {
                $_GS .= "<li>['$k'] => " . dhtmlspecialchars($v) . "</li>";
            }
        }
    }
    $modid = $_G['basescript'] . (!defined('IN_ADMINCP') ? '::' . CURMODULE : '');
    $svn = '';
    if (file_exists(DZZ_ROOT . './.svn/entries')) {
        $svn = @file(DZZ_ROOT . './.svn/entries');
        $time = $svn[9];
        preg_match('/([\d\-]+)T([\d:]+)/', $time, $a);
        $svn = '.r' . $svn[10] . ' (最后由 ' . $svn[11] . ' 于 ' . dgmdate(strtotime($a[1] . ' ' . $a[2]) + $_G['setting']['timeoffset'] * 3600) . ' 提交)';
    }
    $max = 10;
    $mc = $mco = '';
    if (class_exists('C') && C::memory()->enable) {
        $mcarray = C::memory()->debug;
        $i = 0;
        $max += count($mcarray);
        foreach ($mcarray as $key => $value) {
            $mco .= '<div id="__debug_c_' . (7 + $i) . '" style="display:none"><br /><pre>' . print_r($value, 1) . '</pre></div>';
            $mc .= '<a id="__debug_' . (7 + $i) . '" href="#debugbar" onclick="switchTab(\'__debug\', ' . (7 + $i) . ', ' . $max . ')">[' . $key . ']</a>' . ($value ? '<s>(' . count($value) . ')</s>' : '');
            $i++;
        }
    }
    $debug .= '
		<style>#__debugbarwrap__ { line-height:10px; text-align:left;font:12px Monaco,Consolas,"Lucida Console","Courier New",serif;}
		body { font-size:12px; }
		a, a:hover { color: black;text-decoration:none; }
		s { text-decoration:none;color: red; }
		.code { text-decoration:none; color: #00b; cursor:pointer; line-height: 18px; }
		img { vertical-align:middle; }
		.w td em { margin-left:10px;font-style: normal; }
		tr.hbb td{ border-bottom:1px solid #ccc;}
		table.data tr:hover{ background-color: #ccc;}
		.hide{ display : none;}
		#__debugbar__ { padding: 80px 1px 0 1px;  }
		#__debugbar__ table { width:90%;border:1px solid gray; }
		#__debugbar__ div { padding-top: 20px; }
		#__debugbar_s { border-bottom:1px dotted #EFEFEF;background:#FFF;width:100%;font-size:12px;position: fixed; top:0px; left:5px; }
		#__debugbar_s a { color:blue; }
		#__debugbar_s a.a { border-bottom: 1px dotted gray; }
		#__debug_c_1 ol { margin-left: 20px; padding: 0px; }
		#__debug_c_4_nav { background:#FFF; border:1px solid black; border-top:none; padding:5px; position: fixed;right:0px }
		</style>
		<script>function toggle(dom){dom.style.display = dom.style.display != "block" ? "block" : "none";}</script>
		</head><body>' .
        '<div id="__debugbarwrap__">' .
        '<div id="__debugbar_s">
			<table class="w" width=99%><tr><td valign=top width=50%>' .
        '<b style="float:left;width:1em;height:4em">文件</b>' .
        '<em>版本:</em> DZZ! ' . CORE_VERSION . ($svn ? $svn : ' ' . CORE_RELEASE) . '<br />' .
        '<em>ModID:</em> <s>' . $modid . '</s><br />' .
        '<em>包含:</em> ' .
        '<a id="__debug_3" href="#debugbar" onclick="switchTab(\'__debug\', 3, ' . $max . ')">[文件列表]</a>' .
        ' <s>' . (count($includes) - 1) . ($_G['debuginfo']['time'] ? ' in ' . number_format(($_G['debuginfo']['time'] - $sqltime), 6) . 's' : '') . '</s><br />' .
        '<em>执行:</em> ' .
        (isset($_ENV['analysis']['function']) ? '<a id="__debug_9" href="#debugbar" onclick="switchTab(\'__debug\', 9, ' . $max . ')">[函数列表]</a>' .
            ' <s>' . (count($_ENV['analysis']['function']) - 1) . (' in ' . number_format(($_ENV['analysis']['function']['sum'] / 1000), 6) . 's') . '</s>' : '') .
        '<td valign=top>' .
        '<b style="float:left;width:1em;height:5em">服务器</b>' .
        '<em>环境:</em> ' . PHP_OS . ', PHP/' . PHP_VERSION . ', ' . $_SERVER['SERVER_SOFTWARE'] . ' MySQL/' . DB::object()->version() . '(' . (DB::$driver) . ')<br />' .
        $m .
        '<em>SQL:</em> ' .
        '<a id="__debug_1" href="#debugbar" onclick="switchTab(\'__debug\', 1, ' . $max . ')">[SQL列表]</a>' .
        '<a id="__debug_4" href="#debugbar" onclick="switchTab(\'__debug\', 4, ' . $max . ');sqldebug_ajax.location.href = sqldebug_ajax.location.href;">[AjaxSQL列表]</a>' .
        ' <s>' . $queries . $sqlw . ($_G['debuginfo']['time'] ? ' in ' . $sqltime . 's' : '') . '</s><br />' .
        '<em>内存缓存:</em> ' . $mc .
        '<tr><td valign=top colspan="2">' .
        '<b>客户端</b> <a id="__debug_2" href="#debugbar" onclick="switchTab(\'__debug\', 2, ' . $max . ')">[详情]</a> <span id="__debug_b"></span>' .
        '<tr><td colspan=2><a name="debugbar">&nbsp;</a>' .
        '<a href="javascript:;" onclick="parent.scrollTo(0,0)" style="float:right">[TOP]&nbsp;&nbsp;&nbsp;</a>' .
        '<img src="../static/image/common/arw_r.gif" /><a id="__debug_5" href="#debugbar" onclick="switchTab(\'__debug\', 5, ' . $max . ')">$_COOKIE</a>' .
        ($_G['adminid'] == 1 ? '<img src="../static/image/common/arw_r.gif" /><a id="__debug_6" href="#debugbar" onclick="switchTab(\'__debug\', 6, 6)">$_G</a>' : '') .
        ($_G['adminid'] == 1 ?
            '<img src="../static/image/common/arw_r.gif" /><a href="' . $debugfile . '?k=' . $akey . '&' . $phpinfok . '" target="_blank">phpinfo()</a>' .
            '<img src="../static/image/common/arw_r.gif" /><a href="' . $debugfile . '?k=' . $akey . '&' . $mysqlplek . '" target="_blank">MySQL 进程列表</a>' .
            '<img src="../static/image/common/arw_r.gif" /><a href="' . $debugfile . '?k=' . $akey . '&' . $viewcachek . '" target="_blank">查看缓存</a>' .
            '<img src="../static/image/common/arw_r.gif" /><a href="../misc.php?mod=syscache" target="_debug_initframe" onclick="parent.$(\'_debug_initframe\').onload = function () {parent.location.href=parent.location.href;}">更新缓存</a>' : '') .
        '<img src="../static/image/common/arw_r.gif" /><a href="../install/update.php" target="_blank">执行 update.php</a>' .
        '</table>' .
        '</div>' .
        '<div id="__debugbar__" style="clear:both">' .
        '<div id="__debug_c_1" style="display:none"><b>Queries: </b> ' . $queries . '<ol>';
    $debug .= $sqldebug . '';
    $debug .= '</ol></div>' .
        '<div id="__debug_c_4" style="display:none"><iframe id="sqldebug_ajax" name="sqldebug_ajax" src="../' . $ajaxhtml . '?k=' . $akey . '" frameborder="0" width="100%" height="800"></iframe></div>' .
        '<div id="__debug_c_2" style="display:none"><b>IP: </b>' . $_G['clientip'] . '<br /><b>User Agent: </b>' . $_SERVER['HTTP_USER_AGENT'] . '<br /><b>BROWSER.x: </b><script>for(BROWSERi in BROWSER) {var __s=BROWSERi+\':\'+BROWSER[BROWSERi]+\' \';jQuery(\'__debug_b\').innerHTML+=BROWSER[BROWSERi]!==0?__s:\'\';document.write(__s);}</script></div>' .
        '<div id="__debug_c_3" style="display:none"><ol>';
    foreach ($includes as $fn) {
        $fn = str_replace(array(DZZ_ROOT, "\\"), array('', '/'), $fn);
        $debug .= '<li>';
        if (preg_match('/^dzz\//', $fn)) {
            $debug .= '[插件]';
        } elseif (preg_match('/^admin\//', $fn)) {
            $debug .= '[管理页]';
        } elseif (preg_match('/^core\//', $fn)) {
            $debug .= '[脚本]';
        } elseif (preg_match('/^data\/template\//', $fn)) {
            $debug .= '[模板]';
        } elseif (preg_match('/^data/', $fn)) {
            $debug .= '[缓存]';
        } elseif (preg_match('/^config/', $fn)) {
            $debug .= '[配置]';
        }
        if (isset($_ENV['analysis']['file'][$fn]['time'])) {
            $time = ' (<s>' . $_ENV['analysis']['file'][$fn]['time'] . 'ms</s>)';
            $debug .= '<span' . (isset($_ENV['analysis']['file'][$fn]['time']) ? ' class="code" onclick="toggle($(\'f_m_' . $fn . '\'))"' : '') . '>' . $fn . $time . '</span>';
        } else {
            $debug .= $fn;
        }
        if (isset($_ENV['analysis']['file'][$fn])) {
            memory_info($debug, $fn, $_ENV['analysis']['file'][$fn]);
        } else {
            memory_info($debug, $fn, array('start_memory_get_usage' => 0, 'stop_memory_get_usage' => 0, 'start_memory_get_real_usage' => 0, 'stop_memory_get_real_usage' => 0, 'start_memory_get_peak_usage' => 0, 'stop_memory_get_peak_usage' => 0, 'start_memory_get_peak_real_usage' => 0, 'stop_memory_get_peak_real_usage' => 0));
        }
        $debug .= '</li>';
    }
    if (isset($_ENV['analysis']['file']['sum'])) {
        $debug .= '<li style="color:red">count: ' . ($_ENV['analysis']['file']['sum'] / 1000) . 's</li>';
    }
    $debug .= '<ol></div><div id="__debug_c_5" style="display:none"><ol>';
    foreach ($_COOKIE as $k => $v) {
        if (strexists($k, $_G['config']['cookie']['cookiepre'])) {
            $k = '<font color=blue>' . $k . '</font>';
        }
        $debug .= "<li>['$k'] => " . dhtmlspecialchars($v) . "</li>";
    }
    if (isset($_ENV['analysis']['function'])) {
        unset($_ENV['analysis']['function']['sum']);
        $debug .= '<ol></div><div id="__debug_c_9" style="display:none"><ol>';
        foreach ($_ENV['analysis']['function'] as $_fn => $function) {
            $debug .= '<li> ';
            $debug .= '<span class="code" onclick="toggle($(\'f_m_' . $_fn . '\'))">' . $_fn . '</span>(<s>' . $function['time'] . 'ms</s>)';
            memory_info($debug, $_fn, $function);
            $debug .= '</table></li>';
        }
    }
    $debug .= '</ol></div><div id="__debug_c_6" style="display:none">' .
        '<div id="__debug_c_4_nav"><a href="#S_config">Nav:<br />
			<a href="#top">#top</a><br />
			<a href="#S_config">$_G[\'config\']</a><br />
			<a href="#S_setting">$_G[\'setting\']</a><br />
			<a href="#S_member">$_G[\'member\']</a><br />
			<a href="#S_group">$_G[\'group\']</a><br />
			<a href="#S_cookie">$_G[\'cookie\']</a><br />
			<a href="#S_style">$_G[\'style\']</a><br />
			<a href="#S_cache">$_G[\'cache\']</a><br />
			</div>' .
        '<ol>' . $_GS . $_GA . '</ol></div>' . $mco . '</body></html>';
    $fn = 'data/' . $debugfile;
    file_put_contents(DZZ_ROOT . './' . $fn, $debug);
    echo '<iframe src="' . $fn . '?k=' . $akey . '" class="debug_iframe" name="_debug_iframe" id="_debug_iframe" style="border-top:1px solid gray;overflow-x:hidden;overflow-y:auto" width="100%" height="200" frameborder="0"></iframe><div id="_debug_div"></div><iframe name="_debug_initframe" id="_debug_initframe" style="display:none"></iframe>';
}

function memory_info(&$debug, $_fn, $function) {
    $debug .= '<table id="f_m_' . $_fn . '" class="data hide"><tr class="hbb"><td>memory_usage</td><td>start_memory(bytes)</td><td>stop_memory(bytes)</td><td>diff_memory(bytes)</td></tr>';
    $debug .= '<tr><td>memory_get_usage</td><td>' . number_format($function['start_memory_get_usage']) . '</td><td>' . number_format($function['stop_memory_get_usage']) . '</td><td>' . number_format(($function['stop_memory_get_usage']) - ($function['start_memory_get_usage'])) . '</td></tr>';
    $debug .= '<tr><td>memory_get_real_usage</td><td>' . number_format($function['start_memory_get_real_usage']) . '</td><td>' . number_format($function['stop_memory_get_real_usage']) . '</td><td>' . number_format(($function['stop_memory_get_real_usage']) - ($function['start_memory_get_real_usage'])) . '</td></tr>';
    $debug .= '<tr><td>memory_get_peak_usage</td><td>' . number_format($function['start_memory_get_peak_usage']) . '</td><td>' . number_format($function['stop_memory_get_peak_usage']) . '</td><td>' . number_format(($function['stop_memory_get_peak_usage']) - ($function['start_memory_get_peak_usage'])) . '</td></tr>';
    $debug .= '<tr><td>memory_get_peak_real_usage</td><td>' . number_format($function['start_memory_get_peak_real_usage']) . '</td><td>' . number_format($function['stop_memory_get_peak_real_usage']) . '</td><td>' . number_format(($function['stop_memory_get_peak_real_usage']) - ($function['start_memory_get_peak_real_usage'])) . '</td></tr>';
    $debug .= '</table>';
}

function _get_addslashes() {
    return ' function debugaddslashes($string, $force = 1) {
	if(is_array($string)) {
		$keys = array_keys($string);
		foreach($keys as $key) {
			$val = $string[$key];
			unset($string[$key]);
			$string[addslashes($key)] = debugaddslashes($val, $force);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}
$_GET = debugaddslashes($_GET); ';
}


?>