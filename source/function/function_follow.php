<?php

/**
 *    
 *
 *      $Id: function_follow.php 2011-9-5 15:34:35 yangy$
 */

function follow_list($uid, $order,$limit, $start=0) {
	$list = array();
	if($order){
		$orderby=" '$order' DESC";
	}else{
		$orderby=" num DESC,dateline DESC";
	}
	$query = DB::query("SELECT * FROM ".DB::table('home_friend')."
		WHERE uid='$uid' and (type='1' or type='3') ORDER BY $orderby
		LIMIT $start, $limit");
	while ($value = DB::fetch($query)) {
		$list[$value['fuid']] = $value;
	}
	return $list;
}

function fans_list($uid, $order,$limit, $start=0) {
	$list = array();
	if($order){
		$orderby=" $order desc";
	}else{
		$orderby=" num DESC,dateline DESC";
	}
	$query = DB::query("SELECT * FROM ".DB::table('home_friend')."
		WHERE uid='$uid' and (type='2' or type='3') ORDER BY $orderby
		LIMIT $start, $limit");
	while ($value = DB::fetch($query)) {
		$list[$value['fuid']] = $value;
	}
	return $list;
}


function follow_all_list($uid,$order) {
	$list = array();
	if($order){
	}else{
		$orderby=" f.num DESC,f.dateline DESC";
	}
	$query = DB::query("SELECT f.*,m.realname FROM ".DB::table('home_friend')." f left join ".DB::table('common_member_profile')." m on f.fuid=m.uid
		WHERE f.uid='$uid' and (type='1' or type='3') ORDER BY $orderby ");
	while ($value = DB::fetch($query)) {
		$list[$value['fuid']] = $value;
	}
	return $list;
}

function follow_group_list() {
	global $_G;

	$space = array('uid' => $_G['uid']);
	space_merge($space, 'field_home');

	$groups = array();
	$spacegroup = empty($space['privacy']['groupname'])?array():$space['privacy']['groupname'];
	for($i = 0; $i < $_G['myself']['common_member_field_home'][$_G['uid']][followgroups]; $i++) {
		if($i == 0) {
			$groups[0] = lang('follow', 'follow_group_default');
		} else {
			if(!empty($spacegroup[$i])) {
				$groups[$i] = $spacegroup[$i];
			} else {
				if($i<4) {
					$groups[$i] = lang('follow', 'follow_group_'.$i);
				} else {
					$groups[$i] = lang('follow', 'follow_group_more', array('num'=>$i));
				}
			}
		}
	}
	return $groups;
}

function follow_group_list_new($uid=0) {
	global $_G;
	
	if($uid){
		$space = array('uid' =>$uid);
	}else{
		$space = array('uid' => $_G['uid']);
	}
	space_merge($space, 'field_home');

	$groups = array();
	$space['groupnames']=unserialize($space['groupnames']);
	$spacegroup = empty($space['groupnames'])?array():$space['groupnames'];
	
	return $spacegroup;
}

function follow_add($touid, $gids, $note='') {
	global $_G;

	if($touid == $_G['uid']) return -2;
	if(follow_check($touid)=='1') return -2;
	if(count($gids)){
		$gids=",".implode(',',$gids);
	}else{
		$gids=",1";
	}
	
	$freind_request = DB::fetch_first("SELECT * FROM ".DB::table('home_friend_request')." WHERE uid='$_G[uid]' AND fuid='$touid'");
	if($freind_request) {
		$setarr = array(
			'uid' => $_G['uid'],
			'fuid' => $freind_request['fuid'],
			'fusername' => addslashes($freind_request['fusername']),
			'gid' => $gid,
			'dateline' => $_G['timestamp']
		);
		DB::insert('home_friend', $setarr);

		friend_request_delete($touid);

		friend_cache($_G['uid']);

		$setarr = array(
			'uid' => $touid,
			'fuid' => $_G['uid'],
			'fusername' => $_G['username'],
			'gid' => $freind_request['gid'],
			'dateline' => $_G['timestamp']
		);
		DB::insert('home_friend', $setarr);

		addfriendlog($_G['uid'], $touid);
		friend_cache($touid);

	} else {

		$to_follow_request = DB::fetch_first("SELECT * FROM ".DB::table('home_friend_request')." WHERE uid='$touid' AND fuid='$_G[uid]'");
		if($to_follow_request) {
			return -1;
		}

		$setarr = array(
			'uid' => $touid,
			'fuid' => $_G['uid'],
			'fusername' => $_G['username'],
			'note' => $note,
			'dateline' => $_G['timestamp']
		);
		DB::insert('home_friend_request', $setarr);
		$setarr = array(
			'uid' =>  $_G['uid'],
			'fuid' => $touid,
			'fusername' => $_G['username'],
			'type' => '1',
			'gids'=>$gids,
			'dateline' => $_G['timestamp']
		);
		DB::insert('home_friend', $setarr);
		$setarr = array(
			'uid' => $touid,
			'fuid' => $_G['uid'],
			'fusername' => $_G['username'],
			'type' => '2',
			'dateline' => $_G['timestamp']
		);
		DB::insert('home_friend', $setarr);
		DB::query("UPDATE ".DB::table('common_member_status')." SET fans=fans+1 WHERE uid='$touid'");
		DB::query("UPDATE ".DB::table('common_member_status')." SET follow=follow+1 WHERE uid='$_G[uid]'");
		DB::query("UPDATE ".DB::table('common_member')." SET newprompt=newprompt+1 WHERE uid='$touid'");
	}

	return 1;
}

function follow_check($touids) {
	global $_G;
	
	if(empty($_G['uid'])) return false;
	if(is_array($touids)) {
		$query = DB::query("SELECT fuid FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]' AND type='1' AND fuid IN (".dimplode($touids).")");
		while($value = DB::fetch($query)) {
			$touid = $value['fuid'];
			$var = "home_follow_{$_G['uid']}_{$touid}";
			$_G[$var]  = 1;
		}
		$query = DB::query("SELECT fuid FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]' AND type='3' AND fuid IN (".dimplode($touids).")");
		while($value = DB::fetch($query)) {
			$touid = $value['fuid'];
			$var = "home_friend_{$_G['uid']}_{$touid}";
			$fvar = "home_friend_{$touid}_{$_G['uid']}";
			$_G[$var] = $_G[$fvar] = 2;
		}
	} else {
		$touid = $touids;
		$follow = DB::fetch_first("SELECT fuid FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]' AND type='1' AND fuid='$touid'");
		$friend = DB::fetch_first("SELECT fuid FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]' AND type='3' AND fuid='$touid'");
		$_G[$var] =false;
		if($follow){
			$var = "home_follow_{$_G['uid']}_{$touid}";
			$_G[$var] =1;
		}
		if($friend){
			$var = "home_friend_{$_G['uid']}_{$touid}";
			$fvar = "home_friend_{$touid}_{$_G['uid']}";
			$_G[$var] = $_G[$fvar] = 2;
		}
		
		return $_G[$var];
	}

}
?>