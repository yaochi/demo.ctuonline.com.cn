<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_invite.php 9843 2010-05-05 05:38:57Z wangjinbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if(submitcheck('querysubmit')){
	$jsstr = '';
	$uids = $_G['gp_uids'];
	if($uids) {
		if(count($uids) > 255) {
			showmessage('group_choose_friends_max');
		}
		$query = DB::query("SELECT cm.uid, cm.username, cmp.realname FROM ".DB::table('common_member')." cm, ".DB::table("common_member_profile")
                            ." cmp WHERE cm.uid=cmp.uid AND cm.uid IN (".dimplode($uids).")");
        $uids = array();
		while($user = DB::fetch($query)) {
            $user["username"] = $user["realname"]?$user["realname"]:$user["username"];
			$jsstr .= "$user[username]";
            $uids[] = $user["uid"];
		}
	}
	if($_G['gp_id']){
        $uids = implode(",", $uids);
        $changeuids = "var ids = window.parent.document.getElementById('$_G[gp_id]_uids');if(ids){if(ids.value){ids.value+=',$uids'}else{ids.value='$uids'};}";
		echo "<script> $changeuids; window.parent.document.getElementById('$_G[gp_id]').value += \"$jsstr\"; window.parent.hideWindow('query');</script>";
		exit();
	}
}
if($_G['gp_action'] == 'query'){
	$perpage = 20;

	$page = empty($_G['gp_page'])?0:intval($_G['gp_page']);
	if($page<1) $page = 1;
	$start = ($page-1) * $perpage;
	$json = array();
	$singlenum = 0;
	
	$wheresql = "1 AND cm.uid=cmp.uid";
	if ($searchkey = stripsearchkey($_GET['searchkey'])) {
		$wheresql .= " AND cmp.realname ='$searchkey'";
	}
	
	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_member')." cm, ".DB::table('common_member_profile')." cmp WHERE $wheresql"), 0);
	if($count) {
		$query = DB::query("SELECT cm.*,cmp.realname FROM ".DB::table('common_member')." cm, ".DB::table("common_member_profile")." cmp WHERE cm.uid=cmp.uid AND $wheresql ORDER BY username LIMIT $start,$perpage");
        while($value = DB::fetch($query)) {
			$value['username'] = daddslashes($value['username']);
			$value['avatar'] = avatar($value['uid'], 'small', true);
			$singlenum++;
			require_once libfile("function/org");
			$orgname = getOrgNameByUser($value['username']);
            $station = org_get_stattion($_G[uid]);
            $station = $station[s_name];
			$json[$value['uid']] = "$value[uid]:{'uid':$value[uid], 'username':'$value[realname]', 'avatar':'$value[avatar]',
                                    'org':'$orgname', 'station':'$station'}";
		}
	}
	$jsstr = "{'userdata':{".implode(',', $json)."}, 'maxfriendnum':'$count', 'singlenum':'$singlenum'}";
}

function uc_avatar($uid, $size = '', $returnsrc = FALSE) {
	global $_G;
	return avatar($uid, $size, $returnsrc, FALSE, $_G['setting']['avatarmethod'], $_G['setting']['ucenterurl']);
}

include template('common/queryuser');
?>