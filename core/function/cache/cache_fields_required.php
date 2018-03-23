<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}


function build_cache_fields_required() {
	$data = array();

	foreach(C::t('user_profile_setting')->fetch_all_by_available_required(1, 1) as $field) {
		$choices = array();
		if($field['selective']) {
			foreach(explode("\n", $field['choices']) as $item) {
				list($index, $choice) = explode('=', $item);
				$choices[trim($index)] = trim($choice);
			}
			$field['choices'] = $choices;
		} else {
			unset($field['choices']);
		}
		$data['field_'.$field['fieldid']] = $field;
	}

	savecache('fields_required', $data);
}

?>