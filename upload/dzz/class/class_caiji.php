<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

class caiji{
	
	private $_url;
	private $_content='';
	private $_charset='';
	private $_meta=array();
	private $_title='';
	private $_redirect=0;
	private $_proxy='';
	
	public function __construct($url){
		$this->_url = $url; 
	}
	public function getProxy(){
		return $this->_proxy;
	}
	public function getCharset(){
		if(!$this->_content) $this->_content='&nbsp;'.dzz_file_get_contents($this->_url,$this->_redirect,$this->_proxy);
		if(	preg_match("/<meta\s+http-equiv=\"Content-Type\".+?content=\".*?charset=\"{0,1}(.+?)\"/ims",$this->_content,$matches2)){
			$this->_charset=trim($matches2[1]);
		}
		//如果头部没有明确声明charset ,利用内容判断
		if(!$this->_charset){
			$content=($this->_content);
			require_once DZZ_ROOT.'./dzz/class/class_encode.php';
			$p=new Encode_Core();
			$this->_charset=$p->get_encoding(strip_tags($this->_content));
		}
		if(!$this->_charset) $this->_charset='UTF-8';
		return $this->_charset;
	}
	
	public function getTitle(){
		
		if(!$this->_content){
				$this->_content='&nbsp;'.dzz_file_get_contents($this->_url,$this->_redirect,$this->_proxy); 
		}
		if($this->_content && preg_match("/<title>([^>]*)<\/title>/si",$this->_content, $matches)){
			if(!$this->_charset) $this->_charset=$this->getCharset();
			if (isset($matches) && is_array($matches) && count($matches) > 0)
			{
				$this->_title=diconv(strip_tags($matches[1]),$this->_charset);
			}
		}
		/*if($this->_title) {
			$titlearr=preg_split("/[-|—]{1}/",$this->_title);
			$this->_title=$titlearr[0];
		}*/
		$titlearr=explode("-",$this->_title);
		$this->_title=$titlearr[0];
		$titlearr=explode("—",$this->_title);
		$this->_title=$titlearr[0];
		return $this->_title;
	}
	
	public function getKeywords(){
		if(!$this->_meta) $this->_meta=$this->getMeta();
		if($this->_meta['keywords']){
			if(!$this->_charset) $this->_charset=$this->getCharset();
			return diconv(strip_tags($this->_meta['keywords']),$this->_charset);
		}else return '';
	}
	
	public function getDescription(){
		if(!$this->_meta) $this->_meta=$this->getMeta();
		if($this->_meta['description']){
			if(!$this->_charset) $this->_charset=$this->getCharset();
			return diconv(strip_tags($this->_meta['description']),$this->_charset);
		}else return '';
	}
	
	public function getMeta(){
		if(!$this->_meta){
				 $this->_meta=@get_meta_tags($this->_url,true);
		}
		return $this->_meta;
	}
	
	public function getFavicon(){
		$parseurl=parse_url($this->_url);
		$host=$parseurl['host'];
		if($parseurl['scheme']=='https') $parseurl['scheme']='http';
		$host=preg_replace("/^www./",'',$host);//strstr('.',$host);
		
		if(!$this->_content) $this->_content= dzz_file_get_contents($this->_url,$this->_redirect,$this->_proxy);
		if(	preg_match("/<link(.+?)rel=\"[shortcut\s+icon|shortcut|icon|apple-touch-icon]+\"(.+?)>/i",$this->_content,$matches2)){
			if(preg_match("/href=\"(.+?)\"/i",$matches2[0],$matches3)){
				$ico=trim($matches3[1]);
				$purl=parse_url($ico);
				if(empty($purl['host'])){
					//exit('dfdsf');
					$ico0=$parseurl['scheme'].'://'.preg_replace("/\/\//i",'/',$host.'/'.$ico);
					$ico1=$parseurl['scheme'].'://'.preg_replace("/\/\//i",'/','www.'.$host.'/'.$ico);
				}else{
					$ico0=$ico1=preg_replace("/^https/i",'http',$ico);
				}
				//exit($ico0.'===='.$ico1);
				if(check_remote_file_exists($ico1,$this->_proxy)) return $ico1;
				if(check_remote_file_exists($ico0,$this->_proxy)) return $ico0;
			}
		}
		$ico=$parseurl['scheme'].'://'.$host.'/favicon.ico';
		$ico_not_www=$parseurl['scheme'].'://www.'.$host.'/favicon.ico';
		if(check_remote_file_exists($ico,$this->_proxy)) return $ico;
		elseif(check_remote_file_exists($ico_not_www,$this->_proxy)) return $ico_not_www;
		return '';
	}
	
}

?>
