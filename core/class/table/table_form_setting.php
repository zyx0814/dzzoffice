<?php
/* @authorcode  codestrings
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 input:单行文本；textarea:多行文本；select:单选；multiselect:多选；date:日期类型；user:用户选择
 */

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
class table_form_setting extends dzz_table
{
	private $type=array('input','textarea','time','select','multiselect','user');
	public function __construct() {

		$this->_table = 'form_setting';
		$this->_pk    = 'flag';
		parent::__construct();
	}
	
	public function fetch($flag){
		$data=parent::fetch($flag);
		
		if($data['options']) $data['options']=unserialize($data['options']);
		if($data['extra']) $data['extra']=unserialize($data['extra']);
		
		if($data['extra']['mindate']){
			$data['extra']['mindate'] = dgmdate($data['extra']['mindate'],$data['extra']['dateformat']);
		}
		if($data['extra']['maxdate']){
			$data['extra']['maxdate'] = dgmdate($data['extra']['maxdate'],$data['extra']['dateformat']);
		}
		
		return $data;
	}
	//插入表单数据;
	public function insert_by_flag($flag,$form){
		if(empty($flag)) return false;
		if(!in_array($form['type'],$this->type)) return false;
		if(empty($form['labelname'])) return false;
		
		$setarr['labelname']=getstr($form['labelname'],60);
		$setarr['required']=intval($form['required']);
		$setarr['multiple']=intval($form['multiple']);
		$setarr['type']=$form['type'];
		$setarr['disp']=intval($form['disp']);
		$setarr['system']=intval($form['system']);
		switch($form['type']){
			case 'input':case 'textarea':
				$setarr['length']=intval($form['length']);
				$setarr['regex']=trim($form['regex']);
				$extra=array(
					'hint'=>getstr($form['hint']),
				);
				$setarr['extra'] = serialize($extra);
			break;
			case 'select':
				$setarr['options']=is_array($form['options'])?serialize($form['options']):'';
				$setarr['multiple']=0;
			break;
			case 'multiselect':
				$setarr['options']=is_array($form['options'])?serialize($form['options']):'';
			break;
			case 'time':
				$setarr['multiple']=0;
				$extra=array(
						'maxdate'=>$form['maxdate']?strtotime($form['maxdate']):0,
						'mindate'=>$form['mindate']?strtotime($form['mindate']):0,
						'dateformat'=>trim($form['dateformat'])
					  );
				$setarr['extra'] = serialize($extra);
			break;
			
			case 'user':
				
			break;
			
		}
		if(parent::fetch($flag)){
			parent::update($flag,$setarr);
			$setarr['flag'] = $flag;
		}else{
			$setarr['flag'] = $flag;
			parent::insert($setarr,1);
		}
        $setarr['extra']=unserialize($setarr['extra']);
		if($setarr['extra']['mindate']){
			$setarr['extra']['mindate'] = dgmdate($setarr['extra']['mindate'],$setarr['extra']['dateformat']);
		}
		if($setarr['extra']['maxdate']){
			$setarr['extra']['maxdate'] = dgmdate($setarr['extra']['maxdate'],$setarr['extra']['dateformat']);
		}
		$setarr['options']=unserialize($setarr['options']);
		return $setarr;
	}

	/*获取所有表单项*/
	public function fetch_all($flags=array()){
		$data=array();
		$sql = 1;
		$param = array($this->_table);
		if($flags){
			$sql.=" and flag in (%n)";
			$param[] = $flags;
		}
		foreach(DB::fetch_all("select * from %t where $sql order by disp",$param) as $value){
			if($value['extra']){
				$value['extra']=unserialize($value['extra']);
				if($value['extra']['mindate']){
					$value['extra']['mindate'] = dgmdate($value['extra']['mindate'],$value['extra']['dateformat']);
				}
				if($value['extra']['maxdate']){
					$value['extra']['maxdate'] = dgmdate($value['extra']['maxdate'],$value['extra']['dateformat']);
				}
				
			}
			if($value['options']){
				$value['options']=unserialize($value['options']);
			}
			$data[]=$value;
		}
		return $data;
	}
	public function delete_by_flag($flag){
		return parent::delete($flag);
	}
	
}