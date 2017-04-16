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

class table_market_field extends dzz_table
{
	public function __construct() {

		$this->_table = 'market_field';
		$this->_pk    = 'mid';
		parent::__construct();
	}
	public function insert_by_mid($mid,$message,$attachs){
		if($field=parent::fetch($mid)){
			$return=parent::update($mid,array('desc'=>$message,'attachs'=>implode(',',$attachs)));
			$oattachs=$field['attachs']?explode(',',$field['attachs']):array();
			$dels=array_diff($oattachs,$attachs);
			$adds=array_diff($attachs,$oattachs);
			C::t('attachment')->addcopy_by_aid($dels,-1);
			C::t('attachment')->addcopy_by_aid($adds,1);
			return $return;
		}else{
			return parent::insert(array('mid'=>$mid,'desc'=>$message,'attachs'=>implode(',',$attachs)),1);
		}
	}
	public function update_pic_by_mid($mid,$aids){
		if($field=parent::fetch($mid)){
			$return=parent::update($mid,array('pics'=>implode(',',$aids)));
			$opics=$field['pics']?explode(',',$field['pics']):array();
			$dels=array_diff($opics,$aids);
			$adds=array_diff($aids,$opics);
			C::t('attachment')->addcopy_by_aid($dels,-1);
			C::t('attachment')->addcopy_by_aid($adds,1);
			return $return;
		}else{
			return parent::insert(array('mid'=>$mid,'desc'=>'','pics'=>implode(',',$aids)),1);
		}
	}
	public function fetch_pic_by_mid($mid,$width=800,$height=500,$original=1){
		$data=array();
		if($field=parent::fetch($mid)){
			$aids=$field['pics']?explode(',',$field['pics']):array();
			foreach($aids as $aid){
				$data[$aid]=C::t('attachment')->getThumbByAid($aid,$width,$height,$original);
			}
		}
		return $data;
	}
	public function delete_by_mid($mid){
		$data=parent::fetch($mid);
		$opics=$data['pics']?explode(',',$data['pics']):array();
		$attachs=$data['attachs']?explode(',',$data['attachs']):array();
		$aids=array_merge($opics,$attachs);
		foreach($aids as $aid){
			C::t('attachment')->delete_by_aid($aid);
		}
		return true;
	}
}

?>
