<?php

function dmicrotime() {
	return array_sum(explode(' ', microtime()));
}
function str_split_utf8($str) { 
	// place each character of the string into and array 
	$split = 1; 
	$array = array(); $len = strlen($str);
	for ( $i = 0; $i < $len; ){ 
		$value = ord($str[$i]); 
		if($value > 127){ 
			if($value >= 192 && $value <= 223) 
				$split = 2; 
			elseif($value >= 224 && $value <= 239) 
				$split = 3; 
			elseif($value >= 240 && $value <= 247) 
				$split = 4; 
		} else { 
			$split = 1; 
		} 
		$key = NULL; 
		for ( $j = 0; $j < $split; ++$j, ++$i ) { 
			$key .= $str[$i]; 
		} 
		$array[] = $key;
		//array_push( $array, $key ); 
	} 
	return $array; 
}

function isHanz($str) {
	return preg_match("/[\\x{7F}-\\x{FF}]/",$str);
}
/**
 * 从某索引取到另外一个索引
 * 不包含end_offset
 *
 */
function array_slice_byoffset(&$arr,$start_offset,$end_offset,$preserve_keys = false) {
	return array_slice($arr,$start_offset,$end_offset - $start_offset,preserve_keys);
	/*
	$result = array();
	foreach($arr as $k => $v) {
		if ($k >= $start_offset && $k < $end_offset) {
			if ($preserve_keys) {
				$result[$k] = $v;
			} else {
				$result[] = $v;
			}
		}
	}
	return $result;
	*/
}

function preg_match_array($pattern_array,$subject) {
	$result = 0;
	foreach($pattern_array as $v) {
		$result |= preg_match('/'.$v.'/',$subject);
	}
	return $result;
}

define('MODE_CHARACTER',1);
define('MODE_TAG',2);
define('MODE_WHITESPACE',3);
define('ACTION_EQUAL',1);
define('ACTION_DELETE',2);
define('ACTION_INSERT',3);
define('ACTION_NONE',4);
define('ACTION_REPLACE',5);


class html_diff {
	private $specialCaseOpeningTags,$specialCaseClosingTags;
	private $content,$wordIndices;
	private $oldWords,$newWords;
	public function html_diff() {
		$this->specialCaseOpeningTags = array( "<strong[\\>\\s]+", "<b[\\>\\s]+", "<i[\\>\\s]+", "<big[\\>\\s]+", "<small[\\>\\s]+", "<u[\\>\\s]+", "<sub[\\>\\s]+", "<sup[\\>\\s]+", "<strike[\\>\\s]+", "<s[\\>\\s]+" );
		$this->specialCaseClosingTags = array( "</strong>", "</b>", "</i>", "</big>", "</small>", "</u>", "</sub>", "</sup>", "</strike>", "</s>" );
	}
	public function &instance() {
		static $_instance;
		if (!$_instance) {
			$_instance = new self();
		}
		return $_instance;
	}
	
	public function compare($oldText,$newText) {
		$this->content = array();
		$this->wordIndices = array();
		
		$this->oldWords = $this->ConvertHtmlToListOfWords(str_split_utf8($oldText));
		$this->newWords = $this->ConvertHtmlToListOfWords(str_split_utf8($newText));
		
		$this->wordIndices = $this->IndexNewWords($this->newWords);
		$operations = $this->Operations();
		//print_r($this->wordIndices);
		//print_r($this->newWords);
		foreach ($operations as $item) {
			$this->PerformOperation($item);
		}
		
		return implode('',$this->content);
	}
	private function IndexNewWords(&$newWords) {
		$wordIndices = array();
		for ($i = 0; $i < count($newWords); $i++) {
			$word = $newWords[$i];
			if (array_key_exists($word,$wordIndices)) {
				$wordIndices[$word][] = $i;
			} else {
				$wordIndices[$word] = array($i);
			}
		}
		return $wordIndices;
	}
	private function ConvertHtmlToListOfWords($characterString) {
		$mode = MODE_CHARACTER;
		$current_word = '';
		$words = array();
		foreach($characterString as $character) {
			switch($mode) {
				case MODE_CHARACTER:
					if ($this->IsStartOfTag($character)) {
						if (!empty($current_word)) $words[] = $current_word;
						$current_word = '<';
						$mode = MODE_TAG;
					} else if ($this->IsWhiteSpace($character)) {
						if (!empty($current_word)) $words[] = $current_word;
						$current_word = $character;
						$mode = MODE_WHITESPACE;
					} else {
						//$current_word .= $character;  //src  english
						if (isHanz($current_word.$character)) { //hanz
							if (!empty($current_word)) $words[] = $current_word;
							$current_word = $character;
						} else {
							$current_word .= $character;
						}
						
					}
					break;
				case MODE_TAG:
					if ($this->isEndOfTag($character)) {
						$current_word .= '>';
						$words[] = $current_word;
						$current_word = '';

						if ($this->IsWhiteSpace($character)) {
							$mode = MODE_WHITESPACE;
						} else {
							$mode = MODE_CHARACTER;
						}
					} else {
						$current_word .= $character;
					}
					break;
				case MODE_WHITESPACE:
					if ($this->IsStartOfTag($character)) {
							if (!empty($current_word)) $words[] = $current_word;
							$current_word = '<';
							$mode = MODE_TAG;
					} else if ($this->IsWhiteSpace($character)) {
							$current_word .= $character;
					} else {
							if (!empty($current_word)) $words[] = $current_word;
							$current_word = $character;
							$mode = MODE_CHARACTER;
					}
					break;
				default:
					break;
			}
		}
		if (!empty($current_word)) $words[] = $current_word;
		return $words;
	}
	private function IsStartOfTag($val) {
		return $val == '<';
	}
	private function IsEndOfTag($val) {
		return $val == '>';
	}
	private function IsWhiteSpace($value) {
		$result = preg_match('/\s/',$value);
		return $result;
	}
	private function PerformOperation(&$operation){
		switch ($operation->Action) {
				case ACTION_EQUAL:
					$this->ProcessEqualOperation($operation);
					break;
				case ACTION_DELETE:
					$this->ProcessDeleteOperation($operation, 'diffdel');
					break;
				case ACTION_INSERT:
					$this->ProcessInsertOperation($operation, 'diffins');
					break;
				case ACTION_NONE:
					break;
				case ACTION_REPLACE:
					$this->ProcessReplaceOperation($operation);
					break;
				default:
					break;
		}
	}
	private function ProcessReplaceOperation(&$operation) {
		$this->ProcessDeleteOperation($operation, 'diffmod');
		$this->ProcessInsertOperation($operation, 'diffmod');
	}
	private function ProcessInsertOperation(&$operation, $cssClass) {
		$text = array_slice_byoffset($this->newWords,$operation->StartInNew,$operation->EndInNew);
		$this->InsertTag("ins", $cssClass, $text);
	}
	private function ProcessDeleteOperation(&$operation, $cssClass) {
			$text = array_slice_byoffset($this->oldWords,$operation->StartInOld,$operation->EndInOld);
			$this->InsertTag("del", $cssClass, $text);
	}
	private function ProcessEqualOperation(&$operation) {
		$result = array_slice_byoffset($this->newWords,$operation->StartInNew,$operation->EndInNew);
		$this->content[] = implode('', $result);
	}
	/**
	 * This method encloses words within a specified tag (ins or del), and adds this into "content", 
	 * with a twist: if there are words contain tags, it actually creates multiple ins or del, 
	 * so that they don't include any ins or del. This handles cases like
	 * old: '<p>a</p>'
	 * new: '<p>ab</p><p>c</b>'
	 * diff result: '<p>a<ins>b</ins></p><p><ins>c</ins></p>'
	 * this still doesn't guarantee valid HTML (hint: think about diffing a text containing ins or
	 * del tags), but handles correctly more cases than the earlier version.
	 * 
	 * P.S.: Spare a thought for people who write HTML browsers. They live in this ... every day.
	 * @param $tag string
	 * @param $cssClass string
	 * @param $words mixed
	 */
	private function InsertTag($tag, $cssClass, $words) {
		while (true) {
				if (count($words) == 0) {
						break;
				}

				//$nonTags = $this->ExtractConsecutiveWords($words, x => !this.IsTag(x));
				$nonTags = $this->ExtractConsecutiveWords($words, false);

				$specialCaseTagInjection = '';
				$specialCaseTagInjectionIsBefore = false;

				if (count($nonTags) != 0) {
					$text = $this->WrapText(implode('', $nonTags), $tag, $cssClass);
					$this->content[] = $text;
				} else {
					// Check if strong tag
					//if ($this->specialCaseOpeningTags.FirstOrDefault(x => Regex.IsMatch($words[0], x)) != null) {
					if (!!preg_match_array($this->specialCaseOpeningTags,$words[0]) ) {
						$specialCaseTagInjection = '<ins class=\'mod\'>';
						// words.RemoveAt(0);
						if ($tag == 'del') array_shift($words); //delete 0
					} else if (in_array($words[0],$this->specialCaseClosingTags)) {
						$specialCaseTagInjection = '</ins>';
						$specialCaseTagInjectionIsBefore = true;
						if ($tag == "del") array_shift($words); //delete 0
					}
				}

				if (count($words) == 0 && strlen($specialCaseTagInjection) == 0) {
					break;
				}

				if ($specialCaseTagInjectionIsBefore) {
					$this->content[] = $specialCaseTagInjection . implode('', $this->ExtractConsecutiveWords($words, true));
				} else {
					$this->content[] = implode('', $this->ExtractConsecutiveWords($words, true)) . $specialCaseTagInjection;
				}
		}
	}
	private function WrapText($text, $tagName, $cssClass) {
		return sprintf("<%s class='%s'>%s</%s>", $tagName, $cssClass, $text ,$tagName);
	}
	private function ExtractConsecutiveWords(&$words, $condition) {
			$indexOfFirstTag = false;

			for ($i = 0; $i < count($words); $i++) {
				$word = $words[$i];

				if ($condition ? !$this->IsTag($word) : !!$this->IsTag($word)) {
					$indexOfFirstTag = $i;
					break;
				}
			}
//print_r($indexOfFirstTag);
			if ($indexOfFirstTag !== false) {
				//var items = words.Where((s, pos) => pos >= 0 && pos < indexOfFirstTag).ToArray();
				$items = array_slice_byoffset($words,0,$indexOfFirstTag);
				if ($indexOfFirstTag > 0) {
					//$words.RemoveRange(0, $indexOfFirstTag);
					array_splice($words,0,$indexOfFirstTag);
				}
				//print_r($words);
				return $items;
			} else {
				//$items = $words.Where((s, pos) => pos >= 0 && pos <= words.Count).ToArray();
				$items = $words;
				//words.RemoveRange(0, words.Count);
				$words = array();
				return $items;
			}
	}
	private function IsTag($item) {
		$isTag = $this->IsOpeningTag($item) || $this->IsClosingTag($item);
		return $isTag;
	}

	private function IsOpeningTag($item) {
		return preg_match("/^\\s*<[^>]+>\\s*$/",$item) > 0;
	}

	private function IsClosingTag($item) {
		return preg_match("/^\\s*<\\/[^>]+>\\s*$/",$item) > 0;
	}
	private function Operations() {
			$positionInOld = 0; $positionInNew = 0;
			//List<Operation> operations = new List<Operation>();
			$operations = array();

			$matches = $this->MatchingBlocks();

			$matches[] = new Match(count($this->oldWords), count($this->newWords), 0);

			for ($i = 0; $i < count($matches); $i++)
			{
					$match = $matches[$i];

					$matchStartsAtCurrentPositionInOld = ($positionInOld == $match->StartInOld);
					$matchStartsAtCurrentPositionInNew = ($positionInNew == $match->StartInNew);

					$action = ACTION_NONE;

					if ($matchStartsAtCurrentPositionInOld == false && $matchStartsAtCurrentPositionInNew == false) {
							$action = ACTION_REPLACE;
					} else if ($matchStartsAtCurrentPositionInOld == true && $matchStartsAtCurrentPositionInNew == false) {
							$action = ACTION_INSERT;
					} else if ($matchStartsAtCurrentPositionInOld == false && $matchStartsAtCurrentPositionInNew == true) {
							$action = ACTION_DELETE;
					} else { // This occurs if the first few words are the same in both versions
							$action = ACTION_NONE;
					}

					if ($action != ACTION_NONE) {
							$operations[] = 
									new Operation(
											$action,
											$positionInOld,
											$match->StartInOld,
											$positionInNew,
											$match->StartInNew);
					}

					if (count($match) != 0)
					{
							$operations[] = new Operation(
									ACTION_EQUAL,
									$match->StartInOld,
									$match->EndInOld(),
									$match->StartInNew,
									$match->EndInNew());
					}

					$positionInOld = $match->EndInOld();
					$positionInNew = $match->EndInNew();
			}

			return $operations;

	}
	private function MatchingBlocks() {
		//List<Match> matchingBlocks = new List<Match>();
		$matchingBlocks = array();
		$this->FindMatchingBlocks(0, count($this->oldWords), 0, count($this->newWords), $matchingBlocks);
		return $matchingBlocks;
	}
	private function FindMatchingBlocks($startInOld, $endInOld, $startInNew, $endInNew, &$matchingBlocks) {
		$match = $this->FindMatch($startInOld, $endInOld, $startInNew, $endInNew);

		if ($match != null) {
				if ($startInOld < $match->StartInOld && $startInNew < $match->StartInNew) {
					$this->FindMatchingBlocks($startInOld, $match->StartInOld, $startInNew, $match->StartInNew, $matchingBlocks);
				}

				$matchingBlocks[] = $match;

				if ($match->EndInOld() < $endInOld && $match->EndInNew() < $endInNew) {
					$this->FindMatchingBlocks($match->EndInOld(), $endInOld, $match->EndInNew(), $endInNew, $matchingBlocks);
				}

		}
	}
	private function FindMatch($startInOld, $endInOld, $startInNew, $endInNew) {
			$bestMatchInOld = $startInOld;
			$bestMatchInNew = $startInNew;
			$bestMatchSize = 0;

			//Dictionary<int, int> matchLengthAt = new Dictionary<int, int>();
			$matchLengthAt = array();
			
			for ($indexInOld = $startInOld; $indexInOld < $endInOld; $indexInOld++)
			{
					//var newMatchLengthAt = new Dictionary<int, int>();
					$newMatchLengthAt = array();
					$index = $this->oldWords[$indexInOld];

					//if (!this.wordIndices.ContainsKey($index)) {
					if (!array_key_exists($index,$this->wordIndices)) {
							$matchLengthAt = $newMatchLengthAt;
							continue;
					}

					//foreach (var indexInNew in this.wordIndices[$index]) {
					foreach($this->wordIndices[$index] as $indexInNew) {
						if ($indexInNew < $startInNew) {
								continue;
						}

						if ($indexInNew >= $endInNew) {
								break;
						}


						//int newMatchLength = (matchLengthAt.ContainsKey(indexInNew - 1) ? matchLengthAt[indexInNew - 1] : 0) + 1;
						$newMatchLength = (array_key_exists($indexInNew - 1,$matchLengthAt) ? $matchLengthAt[$indexInNew - 1] : 0) + 1;
						$newMatchLengthAt[$indexInNew] = $newMatchLength;

						if ($newMatchLength > $bestMatchSize) {
							$bestMatchInOld = $indexInOld - $newMatchLength + 1;
							$bestMatchInNew = $indexInNew - $newMatchLength + 1;
							$bestMatchSize = $newMatchLength;
						}
				}

				$matchLengthAt = $newMatchLengthAt;
			}

			return $bestMatchSize != 0 ? new Match($bestMatchInOld, $bestMatchInNew, $bestMatchSize) : null;
	}
}
class Match {
	public $StartInOld,$StartInNew,$Size;
	//public $EndInOld,$EndInNew;
	
	public function Match($startInOld, $startInNew, $size) {
			$this->StartInOld = $startInOld;
			$this->StartInNew = $startInNew;
			$this->Size = $size;

	}
	public function EndInOld() {
		return $this->StartInOld + $this->Size;
	}
	public function EndInNew() {
		return $this->StartInNew + $this->Size;
	}
}
class Operation{
		public $Action;
		public $StartInOld;
		public $EndInOld;
		public $StartInNew;
		public $EndInNew;

		public function Operation($action, $startInOld, $endInOld, $startInNew, $endInNew) {
				$this->Action = $action;
				$this->StartInOld = $startInOld;
				$this->EndInOld = $endInOld;
				$this->StartInNew = $startInNew;
				$this->EndInNew = $endInNew;
		}
}


?>