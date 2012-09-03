<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_inputpwd.php 9821 2010-05-05 04:03:14Z wangjinbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(submitcheck('pwdsubmit')) {

	$blogid = empty($_POST['blogid'])?0:intval($_POST['blogid']);
	$albumid = empty($_POST['albumid'])?0:intval($_POST['albumid']);
	$galbumid = empty($_POST['galbumid'])?0:intval($_POST['galbumid']);

	$itemarr = array();
	if($blogid) {
		$query = DB::query("SELECT * FROM ".DB::table('home_blog')." WHERE blogid='$blogid'");
		$itemarr = DB::fetch($query);
		$itemurl = "home.php?mod=space&uid=$itemarr[uid]&do=blog&id=$itemarr[blogid]";
		$cookiename = 'view_pwd_blog_'.$blogid;
	} elseif($albumid) {
		$query = DB::query("SELECT * FROM ".DB::table('home_album')." WHERE albumid='$albumid'");
		$itemarr = DB::fetch($query);
		$itemurl = "home.php?mod=space&uid=$itemarr[uid]&do=album&id=$itemarr[albumid]";
		$cookiename = 'view_pwd_album_'.$albumid;
	} elseif($galbumid) {
		$query = DB::query("SELECT * FROM ".DB::table('group_album')." WHERE albumid='$galbumid'");
		$itemarr = DB::fetch($query);
		$itemurl = "forum.php?mod=group&action=plugin&fid=$itemarr[fid]&plugin_name=groupalbum2&plugin_op=groupmenu&id=$itemarr[albumid]&groupalbum2_action=index&";
		$cookiename = 'view_pwd_galbum_'.$galbumid;
	}
	

	if(empty($itemarr)) {
		showmessage('news_does_not_exist');
	}

	if($itemarr['password'] && $_POST['viewpwd'] == $itemarr['password']) {
		dsetcookie($cookiename, md5(md5($itemarr['password'])));
		showmessage('proved_to_be_successful', $itemurl, array('succeed'=>1), array('showmsg'=>1, 'timeout'=>1));
	} else {
		showmessage('password_is_not_valid', $itemurl, array('succeed'=>0), array('showmsg'=>1));
	}
}

?>