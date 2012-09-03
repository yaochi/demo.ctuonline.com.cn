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

class block_info {

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
		$fid=$_G["fid"];
		$info = DB::query("select description,threads from pre_forum_forumfield where fid =".$fid);
		$foruminfo = DB::fetch($info);

		$sql = "select g.uid,g.username,p.realname from pre_forum_groupuser g,pre_common_member_profile p where g.uid=p.uid and g.fid=".$fid." and g.level = 1 order by g.joindateline";
		$query=DB::query("$sql");
		while($moderatorlist=DB::fetch($query)){
			$moderatorlists[]=$moderatorlist;
		}
		$result=array();
		$result["parameter"] = $parameter;
		$result["description"] = $foruminfo["description"];
		$result["threads"] = $foruminfo["threads"];
		$result["moderatorlist"]=$moderatorlists;
		//$result["listdata"] = $_G["group_plugins"]["group_available"]["groupmenu"];
        return array('data' => $result);
	}
}

?>