<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_userapp.php 6741 2010-03-25 07:36:01Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function _my_env_get($var) {
	global $_G, $space;

	if($var == 'owner') {
		return $space['uid'];
	} elseif($var == 'viewer') {
		return $_G['uid'];
	} elseif($var == 'prefix_url') {
		if(!isset($_G['prefix_url'])) {
			$_G['prefix_url'] = $_G['siteurl'];
		}
		return $_G['prefix_url'];
	} else {
		return '';
	}
}

function _my_get_friends($uid) {
	global $_G;

	$var = "my_get_friends_$uid";
	if(!isset($_G[$var])) {
		$_G[$var] = array();
		$query = DB::query("SELECT fuid FROM ".DB::table('home_friend')." WHERE uid='$uid'");
		while ($value = DB::fetch($query)) {
			$_G[$var][] = $value['fuid'];
		}
	}
	return $_G[$var];
}

function _my_get_name($uid) {
	global $_G;

	$var = "my_get_name_$uid";
	if(!isset($_G[$var])) {
		$query = DB::query("SELECT username FROM ".DB::table('common_member')." WHERE uid='$uid'");
		if($value = DB::fetch($query)) {
			$_G[$var] = $value['username'];
		}
	}
	return $_G[$var];
}

function _my_get_profilepic($uid, $size='small') {
	return UC_API.'/avatar.php?uid='.$uid.'&size='.$size;
}

function _my_are_friends($uid1, $uid2) {
	global $_G;

	$var = "my_are_friends_{$uid1}_{$uid2}";
	if(!isset($_G[$var])) {
		$_G[$var] = false;
		$query = DB::query("SELECT uid FROM ".DB::table('home_friend')." WHERE uid='$uid1' AND fuid='$uid2' LIMIT 1");
		if($value = DB::fetch($query)) {
			$_G[$var] = true;
		}
	}
	return $_G[$var];
}

function _my_user_is_added_app($uid, $appid) {
	global $_G;

	$var = "my_user_is_added_app_{$uid}_{$appid}";
	if(!isset($_G[$var])) {
		$_G[$var] = false;
		$query = DB::query("SELECT uid FROM ".DB::table('home_userapp')." WHERE uid='$uid' AND appid='$appid' LIMIT 1");
		if($value = DB::fetch($query)) {
			$_G[$var] = true;
		}
	}
	return $_G[$var];
}

function _my_get_app_url($appid, $suffix) {
	global $_G;

	if(!isset($_G['prefix_url'])) {
		$_G['prefix_url'] = getsiteurl();
	}
	return $_G['prefix_url']."home.php?mod=userapp&appid=$appid";
}

function _my_get_app_position($appid) {
	global $_G;

	$var = "my_get_app_position_{$appid}";
	if(!isset($_G[$var])) {
		$_G[$var] = 'wide';
		$query = DB::query("SELECT narrow FROM ".DB::table('common_myapp')." WHERE appid='$appid' LIMIT 1");
		if($value = DB::fetch($query)) {
			if($value['narrow']) $_G[$var] = 'narrow';
		}
	}
	return $_G[$var];
}

function my_user_is_allow_friend($uid){
    $query = DB::query("SELECT privacy FROM ".DB::table("common_member_field_home")." WHERE uid=".$uid);
    $row=DB::fetch($query);
    $privacy = unserialize($row["privacy"]);
    if(empty($privacy["view"]["joinfriend"]) || $privacy["view"]["joinfriend"]!=0){
        return true;
    }
    return false;
}
?>