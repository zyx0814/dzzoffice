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
class table_organization_upjob extends dzz_table
{
	public function __construct() {

		$this->_table = 'organization_upjob';
		$this->_pk    = 'id';
		parent::__construct();
	}
	public function fetch_by_uid($uid){
		if(!$data=DB::fetch_first("select * from %t where uid=%d ",array($this->_table,$uid))) return array();
		if(!$job=C::t('organization_job')->fetch_by_jobid($data['jobid'])) return array();
		$job['depart']=array();
		foreach($job['orgtree'] as $value){
			$job['depart'][]=$value['orgname'];
		}
		$job['depart']=implode(' - ',$job['depart']);
		return array_merge($data,$job);
	}
	public function insert_by_uid($uid,$jobid){
		if(!$jobid ) return self::delete_by_uid($uid);
		$setarr=array('uid'=>$uid,
					  'jobid'=>$jobid,
					  'dateline'=>TIMESTAMP,
					  'opuid'=>getglobal('uid')
					  );
		return parent::insert($setarr,1,1);
	}
	public function delete_by_uid($uid){
		return DB::query("delete from %t where uid=%d",array($this->_table,$uid));
	}
}

?>
