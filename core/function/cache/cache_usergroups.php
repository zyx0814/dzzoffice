<?php
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

function build_cache_usergroups() {
	global $_G;
	$data_uf = C::t('usergroup_field')->fetch_all();
	foreach(C::t('usergroup')->range_orderby_creditshigher() as $key=>$value) {
		$group = array_merge(array('groupid' => $value['groupid'], 'type' => $value['type'], 'grouptitle' => $value['grouptitle'], 'creditshigher' => $value['creditshigher'], 'creditslower' => $value['creditslower'], 'stars' => $value['stars'], 'color' => $value['color'], 'icon' => $value['icon'], 'system' => $value['system']), $data_uf[$key]);
	
		$groupid = $group['groupid'];
		$group['grouptitle'] = $group['color'] ? '<font color="'.$group['color'].'">'.$group['grouptitle'].'</font>' : $group['grouptitle'];
		unset($group['creditshigher'], $group['creditslower']);
		unset($group['groupid']);
		$data[$groupid] = $group;
	}
	savecache('usergroups', $data);

	build_cache_usergroups_single();

	
}

function build_cache_usergroups_single() {
	$data_uf = C::t('usergroup_field')->fetch_all();
	foreach(C::t('usergroup')->range() as $gid => $data) {
		$data = array_merge($data, (array)$data_uf[$gid]);
		$ratearray = array();
		if($data['raterange']) {
			foreach(explode("\n", $data['raterange']) as $rating) {
				$rating = explode("\t", $rating);
				$ratearray[$rating[0]] = array('isself' => $rating[1], 'min' => $rating[2], 'max' => $rating[3], 'mrpd' => $rating[4]);
			}
		}
		$data['raterange'] = $ratearray;
		$data['grouptitle'] = $data['color'] ? '<font color="'.$data['color'].'">'.$data['grouptitle'].'</font>' : $data['grouptitle'];
		$data['grouptype'] = $data['type'];
		$data['grouppublic'] = $data['system'] != 'private';
		$data['maxspacesize'] = intval($data['maxspacesize']);
		unset($data['type'], $data['system'], $data['creditshigher'], $data['creditslower'], $data['groupavatar'], $data['admingid']);
		savecache('usergroup_'.$data['groupid'], $data);
	}
}
