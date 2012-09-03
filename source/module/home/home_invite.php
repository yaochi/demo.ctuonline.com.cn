<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: home_invite.php 11548 2010-06-08 02:30:44Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$id = intval($_GET['id']);
$uid = intval($_GET['u']);
$appid = intval($_GET['app']);

$acceptconfirm = false;

if($_G['uid']) {

	if($_GET['accept'] == 'yes') {
		$cookies = empty($_G['cookie']['invite_auth'])?array():explode(',', $_G['cookie']['invite_auth']);

		if(empty($cookies)) {
			showmessage('invite_code_error');
		}
		if(count($cookies) == 3) {
			$uid = intval($cookies[0]);
			$_GET['c'] = $cookies[1];
			$appid = intval($cookies[2]);
		} else {
			$id = intval($cookies[0]);
			$_GET['c'] = $cookies[1];
		}
		$acceptconfirm = true;

	} elseif($_GET['accept'] == 'no') {
		dsetcookie('invite_auth', '', -86400 * 365);
		showmessage('invite_accept_no', 'home.php');
	}
}

if($id) {

	$query = DB::query("SELECT * FROM ".DB::table('common_invite')." WHERE id='$id'");
	$invite = DB::fetch($query);

	if(empty($invite) || $invite['code'] != $_GET['c']) {
		showmessage('invite_code_error');
	}
	if($invite['fuid'] && $invite['fuid'] != $_G['uid']) {
		showmessage('invite_code_fuid');
	}
	if($invite['endtime'] && $_G['timestamp'] > $invite['endtime']) {
		DB::query("DELETE FROM ".DB::table('common_invite')." WHERE id='$id'");
		showmessage('invite_code_endtime_error');
	}

	$appid = $invite['appid'];
	$uid = $invite['uid'];

	$cookievar = "$id,$invite[code]";

} elseif ($uid) {

	$id = 0;
	$invite_code = space_key($uid, $appid);
	if($_GET['c'] != $invite_code) {
		showmessage('invite_code_error');
	}

	$cookievar = "$uid,$invite_code,$appid";

} else {
	showmessage('invite_code_error');
}

$userapp = array();
if($appid) {
	$query = DB::query("SELECT * FROM ".DB::table('common_myapp')." WHERE appid='$appid'");
	$userapp = DB::fetch($query);
}

$space = getspace($uid);
if(empty($space)) {
	showmessage('space_does_not_exist');
}
$jumpurl = $appid ? "userapp.php?mod=app&id=$appid&my_extra=invitedby_bi_$_GET[u]_$_GET[c]&my_suffix=Lw%3D%3D" : 'home.php?mod=space&uid='.$uid;
if($acceptconfirm) {

	dsetcookie('invite_auth', '', -3600*24*7);

	if($_G['uid'] == $uid) {
		showmessage('should_not_invite_your_own');
	}

	require_once libfile('function/friend');
	if(friend_check($uid)) {
		showmessage('you_have_friends', 'home.php?mod=space&uid='.$uid);
	}

	friend_make($space['uid'], addslashes($space['username']));

	if($id) {
		DB::update("common_invite", array('fuid'=>$_G['uid'], 'fusername'=>$_G['username']), array('id'=>$id));
		notification_add($uid, 'friend', 'invite_friend', array('actor' => '<a href="home.php?mod=space&uid='.$_G['uid'].'" target="_blank">'.$_G['username'].'</a>'), 1);
	}
	space_merge($space, 'field_home');
	if(!empty($space['privacy']['feed']['invite'])) {
		require_once libfile('function/feed');
		$tite_data = array('username' => '<a href="home.php?mod=space&uid='.$_G['uid'].'">'.user_get_user_name_by_username($_G['username']).'</a>');
		$space['username']=user_get_user_name($space['uid']);
		feed_add('friend', 'feed_invite', $tite_data, '', array(), '', array(), array(), '', '', '', 0, 0, '', $space['uid'], $space['username']);
		notification_add($space['uid'], 'friend', 'friend_add');
	}

	include_once libfile('function/stat');
	updatestat($appid ? 'appinvite' : 'invite');

	showmessage('invite_friend_ok', $jumpurl);

} else {
	dsetcookie('invite_auth', $cookievar, 3600*24*7);
}

space_merge($space, 'count');
space_merge($space, 'field_home');

$flist = array();
$query = DB::query("SELECT fuid AS uid, fusername AS username FROM ".DB::table('home_friend')." WHERE uid='$uid' ORDER BY num DESC, dateline DESC LIMIT 0,12");
while ($value = DB::fetch($query)) {
	$flist[] = $value;
}
$jumpurl = urlencode($jumpurl);
$space['username']=user_get_user_name($space['uid']);
include_once template('home/invite');

?>