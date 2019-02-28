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

class table_attachment extends dzz_table
{
	public function __construct() {

		$this->_table = 'attachment';
		$this->_pk    = 'aid';
		$this->_pre_cache_key = 'attachment_';
		$this->_cache_ttl = 0;
		parent::__construct();
	}
	public function setUnrun_by_aid($aid,$r){//设置允许运行，如果文件在本地同时修改实际文件名，增加无法运行的后缀；
		$data=parent::fetch($aid);
			if($data['remote']==0 || $data['remote']==1){//文件在本地，修改文件名
				if($r>0){
					$earr=explode('.',$data['attachment']);
					foreach($earr as $key=> $ext){
						if(in_array(strtolower($ext),array($data['filetype'],'dzz'))) unset($earr[$key]);
					}
					$tattachment=implode('.',$earr).'.dzz';
					if(is_file(getglobal('setting/attachdir').'./'.$data['attachment']) && @rename(getglobal('setting/attachdir').'./'.$data['attachment'],getglobal('setting/attachdir').'./'.$tattachment)){
						return parent::update($aid,array('unrun'=>$r,'attachment'=>$tattachment));
					}
					
				}else{
					$earr=explode('.',$data['attachment']);
					foreach($earr as $key=> $ext){
						if(in_array(strtolower($ext),array($data['filetype'],'dzz'))) unset($earr[$key]);
					}
					$tattachment=implode('.',$earr).'.'.$data['filetype'];
					if(is_file(getglobal('setting/attachdir').'./'.$data['attachment']) && @rename(getglobal('setting/attachdir').'./'.$data['attachment'],getglobal('setting/attachdir').'./'.$tattachment)){
						return parent::update($aid,array('unrun'=>$r,'attachment'=>$tattachment));
					}
				}
		}
		return false;
	}
	public function getThumbByAid($aid,$width=256,$height=256,$original=0){ //通过附件获取缩略图
	  //可以让$aid 带入$attach数组.
		if(!is_array($aid)){
			$attach=self::fetch($aid);
		}else{
			$attach=$aid;
		}
		if(!$width ||!$height)  $original=1;
		/*$bz=io_remote::getBzByRemoteid($attach['remote']);
		if($bz=='dzz'){*/
			$path='attach::'.$attach['aid'];
		/*}else{
			$path=$bz.'/'.$attach['attachment'];
		}*/
		return (defined('DZZSCRIPT')?DZZSCRIPT:'index.php').'?mod=io&op=thumbnail&width='.$width.'&height='.$height.'&original='.$original.'&path='.dzzencode($path);
	}
	public function get_total_filesize() {
		$attachsize = 0;
		$attachsize = DB::result_first("SELECT SUM(filesize) FROM ".DB::table($this->table));
		return $attachsize;
	}
	public function addcopy_by_aid($aids,$ceof=1){
		if(!is_array($aids)) $aids=array($aids);
		
		if($ceof>0){
			DB::query("update %t set copys=copys+%d where aid IN(%n)",array($this->_table,$ceof,$aids));
		}else{
			DB::query("update %t set copys=copys-%d where aid IN(%n)",array($this->_table,abs($ceof),$aids));
		}
		$this->clear_cache($aids);
	}
	public function delete_by_aid($aid){ //删除附件
	  global $_G;
		if(!$data=$this->fetch($aid)){
			return false;	
		}
		if($data['copys']>1){
			return $this->update($aid,array('copys'=>$data['copys']-1));
		}else{
			if(io_remote::DeleteFromSpace($data)){
				return  $this->delete($aid);
			}else{
				return false;
			}
		}
		return true;
	}
	public function fetch_by_md5($md5){ //通过md5值返回一条数据
		return DB::fetch_first("SELECT * FROM %t WHERE md5 = %s ",array($this->table,$md5));
	}
	public function getSizeByRemote($remoteid){ //统计占用空间
		if($remoteid<2){
			return DB::result_first("SELECT  sum(filesize) FROM %t WHERE remote <2 and copys>0 ",array($this->table));
		}else{
			return DB::result_first("SELECT  sum(filesize) FROM %t WHERE remote = %d and copys>0 ",array($this->table,$remoteid));
		}
		
	}
	public function getAttachByFilter($filter,$sizecount=false){ //统计占用空间
	    $where='copys>0 and remote!='.$filter['remoteid'];
		if($filter['oremoteid']){
			if($filter['oremoteid']<2){
				$where.="  and remote<2";
			}else{
				$where.="  and remote= '{$filter[oremoteid]}'";
			}
		}
		
		if($filter['aid']){
			$where.=" and aid='{$filter['aid']}'";
		}
		$filter['sizelt']=intval($filter['sizelt']*1024*1024);
		if($filter['sizelt']>0){
			$where.=" and filesize>'$filter[sizelt]'";
		}
		$filter['sizegt']=intval($filter['sizegt']*1024*1024);
		if($filter['sizegt']>0){
			$where.=" and filesize<'$filter[sizegt]'";
		}
		if($filter['exts']){
			$extarr=explode(',',$filter['exts']);
			if($extarr){
				$where.=" and filetype IN (".dimplode($extarr).")";
			}
		}
		if($filter['dateline']){
			$where.=" and dateline>='{$filter[dateline]}'";
		}
		if($filter['aid1']){
			$where.=" and aid>'{$filter[aid1]}'";
		}
		
		if($filter['ignore']){
			$ignores=explode(',',$filter['ignore']);
			if($ignores){
				$where.=" and aid NOT IN (".dimplode($ignores).")";
			}
		}
		if($sizecount)	return DB::result_first("SELECT  sum(filesize) FROM ".DB::table($this->_table)."  WHERE $where ");
		else  return DB::fetch_first("SELECT  * FROM ".DB::table($this->_table)." WHERE $where order by aid");
		
	}
	public function insert($setarr,$return_insert_id=1){
		if($aid=parent::insert($setarr,$return_insert_id)){
			Hook::listen('table_attachment_insert', $aid);//插入附件表时的挂载点
			return $aid;
		}
		return false;
	}
}

?>
