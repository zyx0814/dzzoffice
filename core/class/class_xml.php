<?php
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

function xml2array(&$xml, $isnormal = FALSE,$encodeing='ISO-8859-1') {
	$xml_parser = new XMLparse($isnormal,$encodeing); 
	$data = $xml_parser->parse($xml);
	$xml_parser->destruct();
	return $data;
}

function xmlattribute( $xml, $encodeing='ISO-8859-1') {
	$xml = str_replace($encodeing, 'UTF-8', $xml);
	libxml_disable_entity_loader(true);
    $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement')), true);
	if( $values["item"] ){
		$data=attribute2arr($values["item"]); 
	}
	return $data;
}

function attribute2arr( $values,$data=array() ){
	foreach( $values as $k=>$v ){
		if( isset($v["item"]) ){
			$return = attribute2arr($v["item"]);
			if($return) $data[$v['@attributes']["id"]]=$return ; 
		}
		if( count($v['@attributes'])>1 ){
			$data[$v['@attributes']["id"]]["_attributes"]=$v['@attributes'];
			unset($data[$v['@attributes']["id"]]["_attributes"]["id"]);
		}
	}
	return $data;
}


function array2xml($arr, $htmlon = TRUE, $isnormal = FALSE, $level = 1,$encodeing='ISO-8859-1') {
	$s = $level == 1 ? "<?xml version=\"1.0\" encoding=\"".$encodeing."\"?>\r\n<root>\r\n" : '';
	$space = str_repeat("\t", $level);
  
	foreach($arr as $k => $v) {
		if($k=="_attributes"){
			continue;
		}
		 
		$string="";
		if( isset($arr['_attributes'])){ 
			foreach($arr["_attributes"] as $k2=>$v2){
				if($k2==$k){ 
					foreach($v2 as $k3=>$v3){ 
						$string.=' '.$k3.'="'.$v3.'"';
					} 
				}
			} 
		}
		if(!is_array($v)) {
			$s .= $space."<item id=\"$k\"$string>".($htmlon ? '<![CDATA[' : '').$v.($htmlon ? ']]>' : '')."</item>\r\n";
		} else { 
			$s .= $space."<item id=\"$k\"$string>\r\n".array2xml($v, $htmlon, $isnormal, $level + 1,$encodeing).$space."</item>\r\n";
		}
	}
	$s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
	return $level == 1 ? $s."</root>" : $s;
}

class XMLparse {

	var $parser;
	var $document;
	var $stack;
	var $data;
	var $last_opened_tag;
	var $isnormal;
	var $attrs = array();
	var $failed = FALSE;

	function __construct($isnormal,$encodeing) {
		$this->XMLparse($isnormal,$encodeing);
	}

	function XMLparse($isnormal,$encodeing) {
		$this->isnormal = $isnormal;
		$this->parser = xml_parser_create($encodeing);
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'open','close');
		xml_set_character_data_handler($this->parser, 'data');
	}

	function destruct() {
		xml_parser_free($this->parser);
	}

	function parse(&$data) {
		$this->document = array();
		$this->stack	= array();
		return xml_parse($this->parser, $data, true) && !$this->failed ? $this->document : '';
	}

	function open(&$parser, $tag, $attributes) {
		$this->data = '';
		$this->failed = FALSE;
		if(!$this->isnormal) {
			if(isset($attributes['id']) && !is_string($this->document[$attributes['id']])) {
				$this->document  = &$this->document[$attributes['id']];
			} else {
				$this->failed = TRUE;
			}
		} else {
			if(!isset($this->document[$tag]) || !is_string($this->document[$tag])) {
				$this->document  = &$this->document[$tag];
			} else {
				$this->failed = TRUE;
			}
		}
		$this->stack[] = &$this->document;
		$this->last_opened_tag = $tag;
		$this->attrs = $attributes;
	}

	function data(&$parser, $data) {
		if($this->last_opened_tag != NULL) {
			$this->data .= $data;
		}
	}

	function close(&$parser, $tag) {
		if($this->last_opened_tag == $tag) {
			$this->document = $this->data;
			$this->last_opened_tag = NULL;
		}
		array_pop($this->stack);
		if($this->stack) {
			$this->document = &$this->stack[count($this->stack)-1];
		}
	}
}

?>