<?php
/* Function: 相册列表
 * Com.:
 * Author: wuhan
 * Date: 2010-7-20
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once (dirname(dirname(__FILE__))."/function/function_groupalbum.php");

class block_groupalbum {
	var $setting = array();
	function block_groupalbum() {
		$this->setting = array();
	}
	
	function getstylesetting($style) {
        $categorys_setting = array();
        //相册封面 | 相册封面+相册名称
        $categorys_setting["albumlist"] = $categorys_setting["albumstyle2"] = array(
//			'aids'	=> array(
//				'title' => '指定相册',
//				'type' => 'text',
//				'value' => ''
//			),
//			'uids'	=> array(
//				'title' => '用户UID',
//				'type' => 'text',
//				'value' => ''
//			),
			'orderby' => array(
				'title' => '相册排序方式',
				'type' => 'mradio',
				'value' => array(
					array('dateline', '按发布时间倒序'),
					array('updatetime', '按更新时间倒序'),
					array('picnum', '按照片数倒序'),
				),
				'default' => 'dateline'
			),
			'titlelength' => array(
				'title' => '相册名称字数',
				'type' => 'text',
				'default' => 40
			),
			'top_pic_width' => array(
                'title' => '相册封面宽度',
                'type' => 'text',
                'value' => 200
            ),
            'top_pic_height' => array(
                'title' => '相册封面高度',
                'type' => 'text',
                'value' => 200
            ),
			'startrow' => array(
				'title' => '起始数据行数',
				'type' => 'text',
				'default' => 0
			),
        );
        return $categorys_setting[$style];
    }

	function getsetting() {
		global $_G;
		$settings = $this->setting;
		return $settings;
	}

	function cookparameter($parameter) {
		return $parameter;
	}

	function getdata($style, $parameter) {
		if (!$_GET["fid"]) {
            return array('html' => '请在专区内使用该组件', 'data' => null);
        }
        
		global $_G;
		$parameter = $this->cookparameter($parameter);
		$uids		= !empty($parameter['uids']) ? explode(',', $parameter['uids']) : array();
		$aids		= !empty($parameter['aids']) ? explode(',', $parameter['aids']) : array();
		$startrow	= isset($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= isset($parameter['items']) ? intval($parameter['items']) : 10;
		$titlelength = isset($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;
		$orderby	= isset($parameter['orderby']) && in_array($parameter['orderby'],array('dateline', 'picnum', 'updatetime')) ? $parameter['orderby'] : 'dateline';

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();

		$list = array();
		$wheres = array();
		if($aids) {
			$wheres[] = 'albumid IN ('.dimplode($aids).')';
		}
		if($bannedids) {
			$wheres[]  = 'albumid NOT IN ('.dimplode($bannedids).')';
		}
		if($uids) {
			$wheres[] = 'uid IN ('.dimplode($uids).')';
		}
		$wheres[] = " fid= '$_G[fid]' and group_album.uid=common_member_profile.uid ";
		$wheresql = $wheres ? implode(' AND ', $wheres) : '1';
		$sql = " SELECT group_album.*,common_member_profile.realname FROM ".DB::table('group_album')."  group_album, ".DB::table('common_member_profile')."  common_member_profile  WHERE $wheresql ORDER BY $orderby DESC";
		$query = DB::query($sql." LIMIT $startrow,$items;");
		
		while($data = DB::fetch($query)) {
			//$realname = user_get_user_name($data['uid']);
			$value = array(
				'id' => $data['albumid'],
				'idtype' => 'albumid',
				'title' => cutstr($data['albumname'], $titlelength),
				'url' => "forum.php?mod=group&action=plugin&fid=$data[fid]&plugin_name=groupalbum2&plugin_op=groupmenu&id=$data[albumid]&groupalbum2_action=index",
				'pic' => 'data/attachment/plugin_groupalbum2/'.$data['pic'],
				'picflag' => $data['picflag'],
				'summary' => '',
				'uid'=>$data['uid'],
				'username'=>$data['username'],
				'realname'=>$data['realname'],
				'dateline'=>$data['dateline'],
				'updatetime'=>$data['updatetime'],
				'picnum'=>$data['picnum'],
			);
			$value['name'] = $value['title'];
			$value['contenttype'] = 'groupalbum2';
			$list[] = $value;
		}
		$_G['block_groupalbum'] = $list;
		
		return array('data' => array('listdata' => $list, 'parameter' => $parameter));
	}
}
?>
