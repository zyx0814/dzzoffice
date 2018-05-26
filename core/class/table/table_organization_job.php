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
class table_organization_job extends dzz_table
{
	public function __construct() {

		$this->_table = 'organization_job';
		$this->_pk    = 'jobid';
		parent::__construct();
	}
	public function fetch_all_by_orgid($orgid,$up=0){
		$data=array();
		foreach(DB::fetch_all("select * from %t where orgid = %d order by orgid",array($this->_table,$orgid)) as $value){
			$data[$value['jobid']]=$value;
		}
		return $data;
	}
	function fetch_by_jobid($jobid){
		include_once libfile('function/organization');
		$data=parent::fetch($jobid);
		$data['orgtree']=getTreeByOrgid($data['orgid']);
		return $data;
	}
	
	function insert_job_by_name($orgid=0, $name="" ){
		if (!$orgid || !$name ) return 0;
		if( !$jobid = DB::result_first("select jobid from %t where orgid = %d and  name=%s", array('organization_job',$orgid,$name)) ) {
				$data=array(
						   "orgid" => $orgid,
						   'name' => $name,
						   'jobid' => 0,
						   'dateline' => TIMESTAMP
						);
				if($jobid = DB::insert('organization_job',$data,1)){//插入版本数据
					return $jobid;
				}
		}else{
			return $jobid;
		}
		return 0;
	}
}

?>
