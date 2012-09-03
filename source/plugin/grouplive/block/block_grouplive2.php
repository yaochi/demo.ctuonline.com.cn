<?php
/* Function: 直播数据源 高级自定义
 * Com.:
 * Author: wuhan
 * Date: 2010-7-19
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_grouplive2 {
	var $setting = array();
	function block_grouplive2() {
		$this->setting = array(
			'typeid'	=> array(
				'title' => '选择分类',
				'html'> '',
			),
			'selectlive'=> array(
				'title' => '选择直播',
				'html' => ''
			),
		);
	}

	function getsetting() {
		global $_G;
		$settings = $this->setting;
		$myscript = "'".$_GET['script']."'";
		$myname = "'grouplive'";
		$html_one = '<select class="ps" name="parameter[typeid]" onchange="block_get_setting_array('.$myname.', '.$myscript.', '.$_GET['fid'].', this.value)">';
    	
 		//		分类
 		$html_one .= '<option value="0">'.'全部'.'</option>';
		require_once libfile("function/category");
	    $is_enable_category = false;
	    $pluginid = 'grouplive';
	    if(common_category_is_enable($_G['fid'], $pluginid)){
	        $is_enable_category = true;
	        $categorys = common_category_get_category($_G['fid'], $pluginid);
	    }
		$defaulttype = '';
		foreach ($categorys as $value) {
			if(!$defaulttype) {
				$defaulttype = $value['id'];
			}
			$check = '';
			if($_GET['select_type'] == $value['id']){
                $check = 'selected="selected"';
            }
			$html_one .= '<option value="'.$value['id'].'" '.$check.'>'.$value['name'].'</option>';
		}
        $html_one .='</select>';
		$settings['typeid']['html'] = $html_one;
		
		//$mytype = " and typeid=225";
		$mytype ='';
      
        if(isset($_GET['select_type'])) {
        	$mytype .= " AND typeid=".$_GET['select_type'];
        } else {
        	$mytype .= "";
        }
		$html = '<select class="ps" multiple="multiple" name="parameter[select_live][]">';
		//echo "SELECT * FROM ".DB::table('group_live')." WHERE status=1 AND fid=".$_G['fid'].$mytype;
		$query = DB::query("SELECT * FROM ".DB::table('group_live')." WHERE fid=".$_G['fid'].$mytype);
		while($value=DB::fetch($query)) {
			$html .= '<option value="'.$value['liveid'].'" >'.$value['subject'].'</option>';
		}
        $html .='</select>';

		
		
		$html.='<input type="hidden" name="parameter[plugin_id]" value="'.$pluginid.'"/>';
		$settings['selectlive']['html'] = $html;

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
        //print_r($parameter);
		$parameter = $this->cookparameter($parameter);
		$uids		= !empty($parameter['uids']) ? explode(',', $parameter['uids']) : array();
		$liveids		= $parameter['select_live'];
		
		//print_r($liveids);
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
			$lids="(-1";
			foreach($liveids as $value){
				$lids.=",".$value;
			}
			$lids.=")";
			//echo $lids;
			$wheres[] = 'liveid IN '.$lids;
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
