<?php
if (!defined('IN_DZZ')) {
	exit('Access Denied');
}

class template {

	public $csscurmodules = '';
	public $replacecode = array('search' => array(), 'replace' => array());
	public $language = array();//语言列表
	public $file = '';
	public $default_language = '';//默认语言设置
    public $templateNotMust = false;//模板是否必须，如果为true,则模板不存在返回空字符串,不出现错误提示
	
	private $includeTemplate = array();//记录模版更新时间和路径
    private $tplkey = '';
    private $tplname = '';//模板名称

    //获取模板语言
	public function check_language(){
		$this->default_language = getglobal('language');
	}
	public function fetch_template($tplfile, $tpldir,$templateNotMust){
        $this->tplname = $tplfile;
        $this->templateNotMust = $templateNotMust;
	    $tplfile = $this->parse_tplfile($tplfile,$tpldir,true);
        $this->check_language();
        $cachefile = './data/template/'.$this->tplkey. '_'.str_replace('/', '_', $this->tplname).'_'.$this->default_language.'.tpl.php';
        $this->includeTemplate[$tplfile] = filemtime($tplfile);
		if(!$this->chakcacahefile($cachefile)){
			$content = file_get_contents($tplfile);
			$parsefile = $this->compiler($content,$cachefile);
		}else{
            $parsefile =DZZ_ROOT.'/'.$cachefile;
        }
        return $parsefile;
	}
	private function compiler($content,$cachefile){
        $this->parse_include($content);//解析include
        $this->parse_template($content);//解析模板
        //加入安全代码和模版记录
        $content = "<?php if(!defined('IN_DZZ')) exit('Access Denied'); /*".serialize($this->includeTemplate)."*/?>\n".$content;
        $this->includeTemplate = array();
        if (!@$fp = fopen(DZZ_ROOT . $cachefile, 'w')) {
            $this -> error('directory_notfound', dirname(DZZ_ROOT . $cachefile));
        }
        flock($fp, 2);
        fwrite($fp, $content);
        fclose($fp);
        return DZZ_ROOT.'/' . $cachefile;
	}
	private function chakcacahefile($cachefile){
		//判断是否存在编译文件
		if(!is_file($cachefile)){
			return false;
		}
		//读取编译文件失败
		if(!$handle = @fopen($cachefile,"r")){
			return false;
		}
	   //读取编译文件第一行
		preg_match('/\/\*(.+?)\*\//', fgets($handle), $matches);
		if (!isset($matches[1])) {
            return false;
        }
		$includeFile = unserialize($matches[1]);
        if (!is_array($includeFile)) {
            return false;
        }
		// 检查模板文件是否有更新
        foreach ($includeFile as $path => $time) {
            if (is_file($path) && filemtime($path) > $time) {
                // 模板文件如果有更新则缓存需要更新
                return false;
            }
        }
		return true;
	}
	//解析template包含的模板
	/*private function parse_include(&$content){
        // 替换模板中的include标签
        $this->parse_include_sub($content);
        return;
	}*/
	private function parse_include(&$content){
		 $tplreg = "/[\n\r\t]*(\<\!\-\-)?\{template\s+(.+?)\}(\-\-\>)?[\n\r\t]*/is";
		 if (preg_match_all($tplreg, $content, $matches)) {
                foreach($matches[2] as $k=>$match) {
                    // 分析模板文件名并读取内容
                    $parestr = $this->parse_template_include($match);
                    $content = str_replace($matches[0][$k], $parestr, $content);
                    // 再次对包含文件进行模板分析
                    $this->parse_include($content);
                }
                unset($matches);
            }
	}
    //解析模板路径
    private function parse_tplfile($tplfile, $tpldir = '',$master_template = false,$nomasttplfile = false){
        if(!$tpldir){
            if( defined('CURSCRIPT') && defined('CURMODULE') && file_exists (DZZ_ROOT.'./'.CURSCRIPT.'/'.CURMODULE.'/template/'.$tplfile.'.htm')){
				$tpldir= './'.CURSCRIPT.'/'.CURMODULE.'/template/';
				if($master_template)$this->tplkey=CURSCRIPT.'_'.str_replace('/','_',CURMODULE);
			}elseif(defined('CURSCRIPT') && file_exists (DZZ_ROOT.'./'.CURSCRIPT.'/template/'.$tplfile.'.htm')){
				$tpldir= './'.CURSCRIPT.'/template/';
				if($master_template)$this->tplkey=CURSCRIPT;
			}elseif(file_exists (DZZ_ROOT.'./core/template/default/'.$tplfile.'.htm')){
				$tpldir= './core/template/default/';
				if($master_template)$this->tplkey='core';
			}elseif(file_exists (DZZ_ROOT.'./core/template/default/common/'.$tplfile.'.htm')){
				$tpldir= './core/template/default/common/';
				if($master_template)$this->tplkey='corecommon';
		  	}
        }
        $file = $tplfile;
        $tplfile = $tpldir.$tplfile.'.htm';
        $basefile = basename(DZZ_ROOT . $tplfile, '.htm');
        $tplfile == 'common/header' && defined('CURMODULE') && CURMODULE && $file = 'common/header_' . CURMODULE;
        if (is_file(DZZ_ROOT.$tplfile)) {
            $tplfile =DZZ_ROOT.'/'.$tplfile;
        } elseif (is_file(substr(DZZ_ROOT . $tplfile, 0, -4).'.php')) {
            $tplfile = substr(DZZ_ROOT . $tplfile, 0, -4).'.php';
        } else {
            $tpl = $tpldir . '/' . $file . '.htm';
            $tplfile = $tplfile != $tpl ? $tpl.', '. $tplfile : $tplfile;
            if($this->templateNotMust || $nomasttplfile){
                return '';
            }else{
                $this -> error('template_notfound', $tplfile);
            }

        }
        return $tplfile;
    }
	//读取模板内容
	private function parse_template_include($tpl){
        $template = $this->parse_tplfile($tpl,'',false,true);
        $this->includeTemplate[$template] = filemtime($template);
        if(!is_file($template) || !$fp = fopen($template, 'r')){
            return;
        }
        $content = fread($fp, filesize($template));
        return $content;
	}

	function parse_template(&$template) {
		$var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\-\>)?[a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
		$const_regexp = "([A-Z_\x7f-\xff][A-Z0-9_\x7f-\xff]*)";

		$template = preg_replace("/([\n\r]+)\t+/s", "\\1", $template);

		$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
//	    js的lang替换
		$template = preg_replace_callback("/<script[^>]+?src=\"(.+?)\".*?>[\s\S]*?/is", array($this, 'parse_template_callback_javascript'), $template);
//	       模版lang替换
		$template = preg_replace_callback("/\{lang\s+(.+?)\}/is", array($this, 'parse_template_callback_languagevar_1'), $template);
//	       模版__lang替换
		$template = preg_replace_callback("/__lang\.(\w+)/i", array($this, 'parse_template_callback_languagevar_2'), $template);		
//		img的src替换
		$template = preg_replace_callback("/<img(.+?)src=([\"])(.+?)([\"])([^>]*?)>/is", array($this, 'parse_template_callback_img'), $template);
//		url的地址替换
		$template = preg_replace_callback("/:\s*url\([\"']?(.+?)[\"']?\)/i", array($this, 'parse_template_callback_url'), $template);
//		link的地址替换
		$template = preg_replace_callback("/<link(.+?)href=([\"])(.+?)([\"])([^>]*?)>/i", array($this, 'parse_template_callback_linkurl'), $template);
		
		$template = preg_replace_callback("/[\n\r\t]*\{ad\/(.+?)\}[\n\r\t]*/i", array($this, 'parse_template_callback_adtags_1'), $template);
		$template = preg_replace_callback("/[\n\r\t]*\{ad\s+([a-zA-Z0-9_\[\]]+)\/(.+?)\}[\n\r\t]*/i", array($this, 'parse_template_callback_adtags_21'), $template);
		//解析<!--{date(1482625254)-->
		$template = preg_replace_callback("/[\n\r\t]*\{date\((.+?)\)\}[\n\r\t]*/i", array($this, 'parse_template_callback_datetags_1'), $template);
		//解析<!--{avatar(5)-->
		$template = preg_replace_callback("/[\n\r\t]*\{avatar\((.+?)\)\}[\n\r\t]*/i", array($this, 'parse_template_callback_avatartags_1'), $template);
		$template = preg_replace_callback("/[\n\r\t]*\{eval\}\s*(\<\!\-\-)*(.+?)(\-\-\>)*\s*\{\/eval\}[\n\r\t]*/is", array($this, 'parse_template_callback_evaltags_2'), $template);
		$template = preg_replace_callback("/[\n\r\t]*\{eval\s+(.+?)\s*\}[\n\r\t]*/is", array($this, 'parse_template_callback_evaltags_1'), $template);
		$template = str_replace("{LF}", "<?=\"\\n\"?>", $template);
		$template = preg_replace("/\{(\\\$[a-zA-Z0-9_\-\>\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1?>", $template);
        $template = preg_replace_callback("/[\n\r\t]*\{Hook\s+([\w]+)\}[\n\r\t]*/is", array($this, 'parse_template_callback_hook'), $template);//钩子解析
        //$template = preg_replace_callback("/[\n\r\t]*\{Hook\s+([\w]+)\#(.+?)\#\}[\n\r\t]*/is", array($this, 'parse_template_callback_hook'), $template);//钩子解析,传参形式
		$template = preg_replace_callback("/$var_regexp/s", array($this, 'parse_template_callback_addquote_1'), $template);
		$template = preg_replace_callback("/\<\?\=\<\?\=$var_regexp\?\>\?\>/s", array($this, 'parse_template_callback_addquote_1'), $template);

		$template = preg_replace_callback("/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/is", array($this, 'parse_template_callback_stripvtags_echo1'), $template);

		$template = preg_replace_callback("/([\n\r\t]*)\{if\s+(.+?)\}([\n\r\t]*)/is", array($this, 'parse_template_callback_stripvtags_if123'), $template);
		$template = preg_replace_callback("/([\n\r\t]*)\{elseif\s+(.+?)\}([\n\r\t]*)/is", array($this, 'parse_template_callback_stripvtags_elseif123'), $template);
		$template = preg_replace("/\{else\}/i", "<? } else { ?>", $template);
		$template = preg_replace("/\{\/if\}/i", "<? } ?>", $template);
		$template = preg_replace_callback("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r\t]*/is", array($this, 'parse_template_callback_stripvtags_loop12'), $template);
		$template = preg_replace_callback("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*/is", array($this, 'parse_template_callback_stripvtags_loop123'), $template);
		$template = preg_replace("/\{\/loop\}/i", "<? } ?>", $template);

		$template = preg_replace("/\{$const_regexp\}/s", "<?=\\1?>", $template);
		if (!empty($this -> replacecode)) {
			$template = str_replace($this -> replacecode['search'], $this -> replacecode['replace'], $template);
		}
		$template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);

		$template = preg_replace_callback("/\"(http)?[\w\.\/:]+\?[^\"]+?&[^\"]+?\"/", array($this, 'parse_template_callback_transamp_0'), $template);
		$template = preg_replace_callback("/\<script[^\>]*?src=\"(.+?)\"(.*?)\>\s*\<\/script\>/is", array($this, 'parse_template_callback_stripscriptamp_12'), $template);
		$template = preg_replace_callback("/[\n\r\t]*\{block\s+([a-zA-Z0-9_\[\]]+)\}(.+?)\{\/block\}/is", array($this, 'parse_template_callback_stripblock_12'), $template);
		$template = preg_replace("/\<\?(\s{1})/is", "<?php\\1", $template);
		$template = preg_replace("/\<\?\=(.+?)\?\>/is", "<?php echo \\1;?>", $template);
	}

	function parse_template_callback_javascript($matches) {
		return $this -> loadjstemplate($matches);
	}

    function parse_template_callback_hook($matches){

        //return "<?php Hook::listen('".$matches[1]."',$".$matches[2].") //传参形式
        return "<?php Hook::listen('".$matches[1]."') ?>";
    }

	function replace_js_language_var($arr) {

		return $this->return_js_varvalue($arr[1]);

	}

	function parse_template_callback_loadsubtemplate_2($matches) {
		return $this -> loadsubtemplate($matches[2]);
	}

	function parse_template_callback_languagevar_1($matches) {
		return $this -> languagevar($matches[1]);
	}
	function parse_template_callback_languagevar_2($matches) {
		return $this -> languagevar1($matches[1]);
	}
	function parse_template_callback_img($matches) {
		return $this -> language_img($matches);
	}
	function parse_template_callback_url($matches) {
		return $this -> language_url($matches[1]);
	}
	function parse_template_callback_linkurl($matches) {
		return $this -> language_linkurl($matches);
	}
	function parse_template_callback_blocktags_1($matches) {
		return $this -> blocktags($matches[1]);
	}

	function parse_template_callback_blockdatatags_1($matches) {
		return $this -> blockdatatags($matches[1]);
	}

	function parse_template_callback_adtags_1($matches) {
		return $this -> adtags($matches[1]);
	}

	function parse_template_callback_adtags_21($matches) {
		return $this -> adtags($matches[2], $matches[1]);
	}

	function parse_template_callback_datetags_1($matches) {
		return $this -> datetags($matches[1]);
	}

	function parse_template_callback_avatartags_1($matches) {
		return $this -> avatartags($matches[1]);
	}

	function parse_template_callback_evaltags_2($matches) {
		return $this -> evaltags($matches[2]);
	}

	function parse_template_callback_evaltags_1($matches) {
		return $this -> evaltags($matches[1]);
	}


	function parse_template_callback_hooktags_13($matches) {
		return $this -> hooktags($matches[1], $matches[3]);
	}

	function parse_template_callback_addquote_1($matches) {
		return $this -> addquote('<?=' . $matches[1] . '?>');
	}

	function parse_template_callback_stripvtags_template1($matches) {
		return $this -> stripvtags('<? include template(\'' . $matches[1] . '\'); ?>');
	}

	function parse_template_callback_stripvtags_echo1($matches) {
		return $this -> stripvtags('<? echo ' . $matches[1] . '; ?>');
	}

	function parse_template_callback_stripvtags_if123($matches) {
		return $this -> stripvtags($matches[1] . '<? if(' . $matches[2] . ') { ?>' . $matches[3]);
	}

	function parse_template_callback_stripvtags_elseif123($matches) {
		return $this -> stripvtags($matches[1] . '<? } elseif(' . $matches[2] . ') { ?>' . $matches[3]);
	}

	function parse_template_callback_stripvtags_loop12($matches) {
		return $this -> stripvtags('<? if(is_array(' . $matches[1] . ')) foreach(' . $matches[1] . ' as ' . $matches[2] . ') { ?>');
	}

	function parse_template_callback_stripvtags_loop123($matches) {
		return $this -> stripvtags('<? if(is_array(' . $matches[1] . ')) foreach(' . $matches[1] . ' as ' . $matches[2] . ' => ' . $matches[3] . ') { ?>');
	}

	function parse_template_callback_transamp_0($matches) {
		return $this -> transamp($matches[0]);
	}

	function parse_template_callback_stripscriptamp_12($matches) {
		return $this -> stripscriptamp($matches);
	}

	function parse_template_callback_stripblock_12($matches) {
		return $this -> stripblock($matches[1], $matches[2]);
	}


	function return_js_varvalue($var,$jslang = false) {	
		$langvar = lang();
		if (!isset($langvar[$var])) {
	 		return "'".$var."'";
		}
		$jsonencode = json_encode($langvar[$var]);
	 	return $jsonencode;
	}

	function loadjstemplate($matches) {
        global $_G;
        $parameter = $matches[1];
		$paramet = trim($parameter,"\0");
        $parameter = preg_replace_callback('/\{(.+?)\}/i',function($m){
            $defineds = get_defined_constants();
            return $defineds[$m[1]];
        },$paramet);

		$src =DZZ_ROOT.'/' . $parameter;

		$src = preg_replace('/\?.*/i', '', $src);
		$jsname = str_replace('.','_',basename($src,'.js'));	
		$content = @file_get_contents($src);
        $_G['template_paramet_replace_value'] = $paramet;
		if(!$content){
		    $return = preg_replace_callback("/<script([^>]+?)src=\"(.+?)\"(.*?)>[\s\S]*?/is",function($m){
		        return '<script'.$m[1].'src="'.getglobal('template_paramet_replace_value').'"'.$m[3].'>';
            },$matches[0]);
		    unset($_G['template_paramet_replace_value']);
            return $return;
        }
		$jslangcontent = array();
        if(preg_match_all('/__lang\.(\w+)/i',$content,$match)){
            $jslangcontent[] = 'if(!__lang){var __lang={};}';
        }
		
		if($match[1]){
			
			$jscachefile = './data/template/' .$this->tplkey.'_'.str_replace('/', '_', $this->tplname).'_'.$jsname. '_' . $this->default_language . '.js';
			if(!file_exists($jscachefile)){
				 for($i=0;$i<count($match[1]);$i++){
		            $var1 = $match[1][$i];
		            $content1 = $this -> return_js_varvalue($var1);
		            $jslangcontent[] = '__lang.'.$var1.'='.$content1.';';
		        }	
				$jslangcontent = array_unique($jslangcontent);
				$jscontent = implode('',$jslangcontent);
		        if (!@$fp = fopen(DZZ_ROOT . $jscachefile, 'w+')) {
		            $this -> error('directory_notfound', dirname(DZZ_ROOT . CURSCRIPT));
		        }
		        fwrite($fp, $jscontent);
		        fclose($fp);
			}
		   	$return = '<script type="text/javascript" src="'.$jscachefile.'"></script>';
            $return .= preg_replace_callback("/<script([^>]+?)src=\"(.+?)\"(.*?)>[\s\S]*?/is",function($m){
                return '<script'.$m[1].'src="'.getglobal('template_paramet_replace_value').'"'.$m[3].'>';
            },$matches[0]);
            unset($_G['template_paramet_replace_value']);
            return $return;
		}
        $return = preg_replace_callback("/<script([^>]+?)src=\"(.+?)\"(.*?)>[\s\S]*?/is",function($m){
            return '<script'.$m[1].'src="'.getglobal('template_paramet_replace_value').'"'.$m[3].'>';
        },$matches[0]);
        unset($_G['template_paramet_replace_value']);
		return $return;
	}
//	模版lang替换
	function languagevar($var) {
		!isset($this -> language['inner']) && $this -> language['inner'] = array();
		$langvar = &$this -> language['inner'];
		
		if (!isset($langvar[$var])) {
			$this -> language['inner'] = lang();
		}
		if (isset($langvar[$var])) {
			return $langvar[$var];
		} else {
			return $var ;
		}
	}
//	模版lang替换
	function languagevar1($var) {
		$langvar = lang();
		if (!isset($langvar[$var])) {
	 		return '!'.$var.'!';
		}
		$jsonencode = json_encode($langvar[$var]);
		if(is_array($langvar[$var])){
		  $jsonencode = json_encode($langvar[$var]);
		}else{
		  $jsonencode = "'".$langvar[$var]."'";
		}
	 	return $jsonencode;
	}
//	img的src替换
	function language_img($var) {
		$var[3] = str_replace(' ','',$var[3]);
		$str = strrchr(basename($var[3]),'.');
		$arr = array('.png','.gif','.jpg','.jpeg','.bmp');
		if(in_array($str, $arr)){
			$name = $this -> site_operation($var[3]);
			$src = $this -> check_file_exists($name);
			if($src){
				return '<img ' . $var[1] . ' src='.$var[2].$name.$var[4] . $var[5] . '>';
			}else{
				return $var[0];
			}			
		}else{
			return $var[0];
		}

	}
//	url的地址替换
	function language_url($var) {
		$var = str_replace(' ','',$var);
		$name = $this -> site_operation($var);
		$src = $this -> check_file_exists($name);
		if($src){
			return ':url('.$name.')';
		}else{
			return ':url('.$var.')';
		}		
	}
	function language_linkurl($var) {
		$var[3] = str_replace(' ','',$var[3]);
		$link_src = str_replace('?{VERHASH}','',$var[3]);		
		$name = $this -> site_operation($link_src);
		$src = $this -> check_file_exists($name);	
		if($src){		
			return '<link ' . $var[1] . ' href='.$var[2].$name.$var[4] . $var[5] .'>';
		}else{
			return $var[0];
		}
	}
	function check_file_exists($src){
		return file_exists(DZZ_ROOT.$src);
	}
	function site_operation($var){
		$imgname = basename($var);
		$str = strrchr($imgname,'.');
		return $name = str_replace($str,'.'.$this->default_language.$str,$var);
		
	}
	

	function adtags($parameter, $varname = '') {
		$parameter = stripslashes($parameter);
		$i = count($this -> replacecode['search']);
		$this -> replacecode['search'][$i] = $search = "<!--AD_TAG_$i-->";
		$this -> replacecode['replace'][$i] = "<?php " . (!$varname ? 'echo ' : '$' . $varname . '=') . "adshow(\"$parameter\");?>";
		return $search;
	}

	function datetags($parameter) {
		$parameter = stripslashes($parameter);
		$i = count($this -> replacecode['search']);
		$this -> replacecode['search'][$i] = $search = "<!--DATE_TAG_$i-->";
		$this -> replacecode['replace'][$i] = "<?php echo dgmdate($parameter);?>";
		return $search;
	}

	function avatartags($parameter) {
		$parameter = stripslashes($parameter);
		$i = count($this -> replacecode['search']);
		$this -> replacecode['search'][$i] = $search = "<!--AVATAR_TAG_$i-->";
		$this -> replacecode['replace'][$i] = "<?php echo avatar($parameter);?>";
		return $search;
	}

	function evaltags($php) {
		$php = str_replace('\"', '"', $php);
		$i = count($this -> replacecode['search']);
		$this -> replacecode['search'][$i] = $search = "<!--EVAL_TAG_$i-->";
		$this -> replacecode['replace'][$i] = "<? $php?>";
		return $search;
	}

	function hooktags($hookid, $key = '') {
		global $_G;
		$i = count($this -> replacecode['search']);
		$this -> replacecode['search'][$i] = $search = "<!--HOOK_TAG_$i-->";
		$dev = '';
		if (isset($_G['config']['plugindeveloper']) && $_G['config']['plugindeveloper'] == 2) {
			$dev = "echo '<hook>[" . ($key ? 'array' : 'string') . " $hookid" . ($key ? '/\'.' . $key . '.\'' : '') . "]</hook>';";
		}
		$key = $key != '' ? "[$key]" : '';
		$this -> replacecode['replace'][$i] = "<?php {$dev}if(!empty(\$_G['setting']['pluginhooks']['$hookid']$key)) echo \$_G['setting']['pluginhooks']['$hookid']$key;?>";
		return $search;
	}

	function stripphpcode($type, $code) {
		$this -> phpcode[$type][] = $code;
		return '{phpcode:' . $type . '/' . (count($this -> phpcode[$type]) - 1) . '}';
	}

	function loadsubtemplate($file) {
		$tplfile = template($file, 0, '', 1);
		$filename =DZZ_ROOT.'/' . $tplfile;

		if (($content = @implode('', file($filename))) || ($content = $this -> getphptemplate(@implode('', file(substr($filename, 0, -4) . '.php'))))) {
			$this -> subtemplates[] = $tplfile;
			return $content;
		} else {
			return '<!-- ' . $file . ' -->';
		}
	}

	function getphptemplate($content) {
		$pos = strpos($content, "\n");
		return $pos !== false ? substr($content, $pos + 1) : $content;
	}

	
	function cssvtags($param, $content) {
		global $_G;
		$modules = explode(',', $param);
		foreach ($modules as $module) {
			$module .= '::';
			//fix notice
			list($b, $m) = explode('::', $module);
			if ($b && $b == $_G['basescript'] && (!$m || $m == CURMODULE)) {
				$this -> csscurmodules .= $content;
				return;
			}
		}
		return;
	}

	function transamp($str) {
		$str = str_replace('&', '&amp;', $str);
		$str = str_replace('&amp;amp;', '&amp;', $str);
		$str = str_replace('\"', '"', $str);
		return $str;
	}

	function addquote($var) {
		return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var));
	}

	function stripvtags($expr, $statement = '') {
		$expr = str_replace('\\\"', '\"', preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
		$statement = str_replace('\\\"', '\"', $statement);
		return $expr . $statement;
	}

	function stripscriptamp($matches) {
	    global $_G;
		$_G['template_extra-replace_val'] = str_replace('\\"', '"', $matches[2]);
		$_G['template_src_replace_val'] = str_replace('&amp;', '&', $matches[1]);
        $return =  preg_replace_callback("/\<script([^\>]*?)src=\"(.+?)\"(.*?)\>\s*\<\/script\>/is",function($match){
            return  "<script".$match[1]."src=\"".getglobal('template_src_replace_val')."\" ".getglobal('template_extra-replace_val')."></script>";
        },$matches[0]);
        unset($_G['template_extra-replace_val']);
        unset($_G['template_src_replace_val']);
		return $return;
	}

	function stripblock($var, $s) {
		$s = str_replace('\\"', '"', $s);
		$s = preg_replace("/<\?=\\\$(.+?)\?>/", "{\$\\1}", $s);
		preg_match_all("/<\?=(.+?)\?>/", $s, $constary);
		$constadd = '';
		$constary[1] = array_unique($constary[1]);
		foreach ($constary[1] as $const) {
			$constadd .= '$__' . $const . ' = ' . $const . ';';
		}
		$s = preg_replace("/<\?=(.+?)\?>/", "{\$__\\1}", $s);
		$s = str_replace('?>', "\n\$$var .= <<<EOF\n", $s);
		$s = str_replace('<?', "\nEOF;\n", $s);
		$s = str_replace("\nphp ", "\n", $s);
		return "<?\n$constadd\$$var = <<<EOF\n" . $s . "\nEOF;\n?>";
	}

	function error($message, $tplname) {
		dzz_error::template_error($message, $tplname);
	}

}
?>