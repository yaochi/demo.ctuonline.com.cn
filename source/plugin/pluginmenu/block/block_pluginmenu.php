<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_blank.php 7141 2010-03-29 12:25:15Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class block_pluginmenu {

	function getsetting() {

		$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        return array(
                array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>'), );
	}

	function getstylesetting($style) {
		return array();
    }

	function getdata($style, $parameter) {

		global $_G;
		$status = $_G['myself']['group']['status'];//从G中获取
		if(group_is_group_founder($_G[fid], $_G[uid])){
			$type = 'creater';
		}
		if(!group_is_group_founder($_G[fid], $_G[uid])&&$_G['forum']['ismoderator']){
			if($status == 'isgroupuser'){
				$type = 'admin1';
			}else if($status != 'isgroupuser'){
				$type = 'admin2';
			}
		}
		if(!group_is_group_founder($_G[fid], $_G[uid])&&!$_G['forum']['ismoderator']){
			if($status != 'isgroupuser'){
					$type = 'notmember';
			}
		}
		if(!group_is_group_founder($_G[fid], $_G[uid])&&!$_G['forum']['ismoderator']){
			if ($status == 'isgroupuser'){
					$type = 'member';
			}
		}
		$result=array();
		$result["parameter"] = $parameter;
		$result['type']=$type;
		//$result["listdata"] = $_G["group_plugins"]["group_available"]["groupmenu"];
        return array('data' => $result);
	}
}

?>