<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_top.php 10793 2010-05-17 01:52:12Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_G['setting']['creditstransextra'][6]) {
	$key = 'extcredits'.intval($_G['setting']['creditstransextra'][6]);
} elseif ($_G['setting']['creditstrans']) {
	$key = 'extcredits'.intval($_G['setting']['creditstrans']);
} else {
	showmessage('trade_credit_invalid', '', array(), array('return' => 1));
}
space_merge($space, 'count');

if(submitcheck('friendsubmit')) {

	$showcredit = intval($_POST['stakecredit']);
	if($showcredit > $space[$key]) $showcredit = $space[$key];
	if($showcredit < 1) {
		showmessage('showcredit_error');
	}

	$_POST['fusername'] = trim($_POST['fusername']);
	$friend = DB::fetch(DB::query("SELECT * FROM ".DB::table('home_friend')." WHERE uid='$space[uid]' AND fusername='$_POST[fusername]'"));
	$fuid = $friend['fuid'];
	if(empty($_POST['fusername']) || empty($fuid) || $fuid == $space['uid']) {
		showmessage('showcredit_fuid_error', '', array(), array('return' => 1));
	}

	$count = getcount('home_show', array('uid'=>$fuid));
	if($count) {
		DB::query("UPDATE ".DB::table('home_show')." SET credit=credit+$showcredit WHERE uid='$fuid'");
	} else {
		DB::insert('home_show', array('uid'=>$fuid, 'username'=>$_POST['fusername'], 'credit'=>$showcredit), 0, true);
	}

	member_count_update($space['uid'], array('credit'=>(0-$showcredit)));

	notification_add($fuid, 'credit', 'showcredit', array('credit'=>$showcredit));


	if(ckprivacy('show', 'feed')) {
		require_once libfile('function/feed');
		feed_add('show', 'feed_showcredit', array(
		'fusername' => "<a href=\"home.php?mod=space&uid=$fuid\">{$friend[fusername]}</a>",
		'credit' => $showcredit));
	}

	showmessage('showcredit_friend_do_success', "home.php?mod=space&do=top");

} elseif(submitcheck('showsubmit')) {

	$showcredit = intval($_POST['showcredit']);
	if($showcredit > $space[$key]) $showcredit = $space[$key];
	if($showcredit < 1) {
		showmessage('showcredit_error', '', array(), array('return' => 1));
	}
	$_POST['note'] = getstr($_POST['note'], 100, 1, 1, 1);

	$count = getcount('home_show', array('uid'=>$_G['uid']));
	if($count) {
		$notesql = $_POST['note']?", note='$_POST[note]'":'';
		DB::query("UPDATE ".DB::table('home_show')." SET credit=credit+$showcredit $notesql WHERE uid='$_G[uid]'");
	} else {
		DB::insert('home_show', array('uid'=>$_G['uid'], 'username'=>$_G['username'], 'credit'=>$showcredit, 'note'=>$_POST['note']), 0, true);
	}

	member_count_update($space['uid'], array('credit'=>(0-$showcredit)));

	if(ckprivacy('show', 'feed')) {
		require_once libfile('function/feed');
		feed_add('show', 'feed_showcredit_self', array('credit'=>$showcredit), '', array(), $_POST['note']);
	}

	showmessage('showcredit_do_success', "home.php?mod=space&do=top");
}

?>