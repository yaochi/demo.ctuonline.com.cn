<?php
/* Function: 直播数据源 高级自定义
 * Com.:
 * Author: wuhan
 * Date: 2010-7-19
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_grouplive {
	var $setting = array();
	function block_grouplive() {
		$this->setting = array(
			'typeid'	=> array(
				'title' => '选择分类',
				'type' => 'select',
				'value' => ''
			),
		);
	}

	function getsetting() {
		global $_G;
		$settings = $this->setting;

		if($settings['typeid']){
			require_once libfile("function/category");
		    $pluginid = "grouplive";
		    if(common_category_is_enable($_G['fid'], $pluginid)){
		        $categorys = common_category_get_category($_G['fid'], $pluginid);
		    }
		    $settings['typeid']['value'][] = array(0, '全部');
		    foreach($categorys as $key => $value){
		    	$settings['typeid']['value'][] = array($value['id'], $value['name']);
		    }
		}

		return $settings;
	}

	function getstylesetting($style) {
        $categorys_setting = array();
        
        $categorys_setting["livelist"] = array(
			'titlelength' => array(
				'title' => '直播标题字数',
				'type' => 'text',
				'default' => 50
			),
			
        );

        return $categorys_setting[$style];
    }

	function cookparameter($parameter) {
		return $parameter;
	}
	
	function getdata($style, $parameter) {
		global $_G;

		$parameter = $this->cookparameter($parameter);
		$uids		= !empty($parameter['uids']) ? explode(',', $parameter['uids']) : array();
		$liveids		= !empty($parameter['liveids']) ? explode(',', $parameter['liveids']) : array();
		$typeid		= !empty($parameter['typeid']) ? $parameter['typeid'] : array();
		$startrow	= isset($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= isset($parameter['items']) ? intval($parameter['items']) : 10;
		$titlelength = isset($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;
		$namelength = isset($parameter['namelength']) ? intval($parameter['namelength']) : 7;
		$orderby	= isset($parameter['orderby']) && in_array($parameter['orderby'],array('starttime', 'dateline', 'playnum')) ? $parameter['orderby'] : 'starttime';

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();

		$list = array();
		$wheres = array();
		if($liveids) {
			$wheres[] = 'liveid IN ('.dimplode($liveids).')';
		}
		if($bannedids) {
			$wheres[]  = 'liveid NOT IN ('.dimplode($bannedids).')';
		}
		if($uids) {
			$wheres[] = 'uid IN ('.dimplode($uids).')';
		}
		if($typeid && !in_array('0', $typeid)) {
			$wheres[] = 'typeid IN ('.dimplode($typeid).')';
		}
		$wheres[] = "friend = '0' AND fid= '$_G[fid]' ";
		$wheresql = $wheres ? implode(' AND ', $wheres) : '1';
		$sql = "SELECT * FROM ".DB::table('group_live')." WHERE $wheresql ORDER BY $orderby DESC";
		$query = DB::query($sql." LIMIT $startrow,$items;");
		
		$realnames = array();
		
		while($data = DB::fetch($query)) {
			if($data['starttime']>time()){
						$whatstatus='0';
					}elseif($data["endtime"]<time()){
						$whatstatus='2';
					}else{
						$whatstatus='1';
					}
			$value = array(
				'id' => $data['liveid'],
				'subject' => $data['subject'],
				'title' => cutstr($data['subject'], $titlelength),
				'url' => "forum.php?mod=group&action=plugin&fid=$data[fid]&plugin_name=grouplive&plugin_op=groupmenu&id=$data[liveid]&grouplive_action=index",
				'play' => "forum.php?mod=group&action=plugin&fid=$data[fid]&plugin_name=grouplive&plugin_op=groupmenu&grouplive_action=livecp&op=join&liveid=$data[liveid]",
				'whatstatus'=>$whatstatus,
				'uid'=>$data['uid'],
				'username'=>$data['username'],
				'dateline'=>$data['dateline'],
				'playnum'=>$data['playnum'],
				'viewnum'=>$data['viewnum'],
				'viewplay'=>$data['viewnum']+$data['playnum'],
				'starttime' => $data['starttime'],
				'endtime' => $data['endtime'],
				'startdate' => dgmdate($data['starttime'], 'Y.m.d'),
				'starttimestamp' => dgmdate($data['starttime'], 'H:i'),
				'endtimestamp' => dgmdate($data['endtime'], 'H:i'),
			);
			
			$value['firstman_names'] = array();
			if(!empty($data['firstman_ids'])){
				/* 尽量cache中取  */
				require_once libfile("function/group");
				
				$data['firstman_ids'] = explode(',',$data['firstman_ids']);
				foreach($data['firstman_ids'] as $id){
					if(!array_key_exists($id, $realnames)){
						$realname = user_get_user_name($id);
						//$realname = DB::result_first("SELECT realname FROM ".DB::table('common_member_profile')." WHERE uid = '$id'");
						$realnames[$id] = $realname;
					}
					$value['firstman_names'][] = $realnames[$id];						
				}
			}
			$value['firstman_names'] = implode(', ', $value['firstman_names']);
			
			
			//added by fumz,查询直播 主讲人真实姓名
			//begin
			$value['secondman_names'] = array();
			if(!empty($data['secondman_ids'])){
				/* 尽量cache中取  */
				require_once libfile("function/group");
				
				$data['secondman_ids'] = explode(',',$data['secondman_ids']);
				foreach($data['secondman_ids'] as $id){
					if(!array_key_exists($id, $realnames)){
						//$realname = DB::result_first("SELECT realname FROM ".DB::table('common_member_profile')." WHERE uid = '$id'");
						$realname = user_get_user_name($id);
						$realnames[$id] = $realname;
					}
					$value['secondman_names'][] = $realnames[$id];						
				}
			}
			$value['secondman_names'] = implode(', ', $value['secondman_names']);
			
			
//			$value['firstman_names'] = cutstr($value['firstman_names'], $namelength);
			$value['contenttype']='grouplive';
			$list[] = $value;
		}
		$_G['block_grouplive'] = $list;
        //begin update by qiaoyongzhi,2011-2-25 EKSN143 模块高度
		$result["parameter"] = $parameter;
		$result["listdata"] = $list;
        return array('data' => $result);
	}
}
?>
