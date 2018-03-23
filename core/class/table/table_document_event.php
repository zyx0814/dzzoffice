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

class table_document_event extends dzz_table
{
	public function __construct() {

		$this->_table = 'document_event';
		$this->_pk    = 'eid';

		parent::__construct();
	}
  
	 public function delete_by_did($dids){
		if(!is_array($dids)) $dids=array($dids);
	    return DB::delete($this->_table,"did IN (".dimplode($dids).")");
	}
	
	public function fetch_all_by_did($did){
		$data=array();
		foreach(DB::fetch_all("select * from %t where did = %d order by dateline",array($this->_table,$did)) as $value){
			switch($value['action']){
				case 'create':
					$value['faction']=lang('create_document');
					break;
				case 'reversion':
					$value['faction']=lang('edit_document');
					break;
				case 'edit':
					$value['faction']=lang('edit_document');
					break;
				case 'delete':
					$value['faction']=lang('delete_document');
					break;
				case 'rename':
					$value['faction']=lang('rename_document');
					break;
			}
			$data[]=$value;
		}
	}
}

?>
