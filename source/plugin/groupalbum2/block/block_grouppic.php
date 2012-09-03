<?php
/* Function: 图片列表
 * Com.:
 * Author: wuhan
 * Date: 2010-8-12
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once (dirname(dirname(__FILE__))."/function/function_groupalbum.php");

class block_grouppic {
	var $setting = array();
	function block_grouppic() {
		$this->setting = array(
			'pids'	=> array(
				'title' => '指定图片',
				'type' => 'text',
				'value' => ''
			),
			'uids'	=> array(
				'title' => '用户UID',
				'type' => 'text',
				'value' => ''
			),
			'aids'	=> array(
				'title' => '指定相册',
				'type' => 'mselect',
				'value' => ''
			),
			'titlelength' => array(
				'title' => '指定图片名称最大长度',
				'type' => 'text',
				'default' => 40
			),
			'top_pic_width' => array(
                'title' => '图片宽度',
                'type' => 'text',
                'value' => 200
            ),
            'top_pic_height' => array(
                'title' => '图片高度',
                'type' => 'text',
                'value' => 200
            ),
			'startrow' => array(
				'title' => '起始数据行数',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function getsetting() {
		global $_G;
		$settings = $this->setting;

		if($settings['aids']) {
			$settings['aids']['value'][] = array(0, '全部分类');
			
			$query = DB :: query("SELECT * FROM " . DB :: table('group_album') . " WHERE friend='0' AND fid='$_G[fid]' ORDER BY albumid DESC");
			while ($value = DB :: fetch($query)) {
				$settings['aids']['value'][] = array($value['albumid'], $value['albumname']);
			}
		}
		
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
		$pids		= !empty($parameter['pids']) ? explode(',', $parameter['pids']) : array();
		$uids		= !empty($parameter['uids']) ? explode(',', $parameter['uids']) : array();
		$aids		= !empty($parameter['aids']) ? $parameter['aids'] : array();
		$startrow	= isset($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= isset($parameter['items']) ? intval($parameter['items']) : 10;
		$titlelength = isset($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();

		$list = array();
		$wheres = array();
		if($pids){
			$wheres[]  = 'p.picid IN ('.dimplode($pids).')';
		}
		if($bannedids) {
			$wheres[]  = 'p.picid NOT IN ('.dimplode($bannedids).')';
		}
		if($uids) {
			$wheres[] = 'p.uid IN ('.dimplode($uids).')';
		}
		if($aids && !in_array('0', $aids)) {
			$wheres[] = 'a.albumid IN ('.dimplode($aids).')';
		}
		$wheres[] = "a.friend = '0' AND a.fid= '$_G[fid]' AND a.albumid = p.albumid";
		$wheresql = $wheres ? implode(' AND ', $wheres) : '1';
		$sql = "SELECT p.*, a.fid FROM ".DB::table('group_album')." a, ".DB::table('group_pic')." p WHERE $wheresql ORDER BY p.dateline DESC";
		
		$query = DB::query($sql." LIMIT $startrow,$items;");
		
		while($data = DB::fetch($query)) {
			$value = array(
				'id' => $data['picid'],
				'idtype' => 'picid',
				'title' => cutstr($data['name'], $titlelength),
				'url' => "forum.php?mod=group&action=plugin&fid=$data[fid]&plugin_name=groupalbum2&plugin_op=groupmenu&picid=$data[picid]&groupalbum2_action=index",
				'pic' => 'data/attachment/plugin_groupalbum2/'.$data['filepath'].($data['thumb']? ".thumb.jpg" : ""),
				'uid'=>$data['uid'],
				'username'=>$data['username'],
				'dateline'=>$data['dateline'],
			);
			$value['name'] = $value['title'];
			$value['contenttype'] = 'groupalbum2';
			$list[] = $value;
		}
		$_G['block_grouppic'] = $list;
		
		return array('data' => array('listdata' => $list, 'parameter' => $parameter));
	}
}
?>
