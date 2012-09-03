<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_ajax.php 10313 2010-05-10 08:13:57Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);

if($_G['gp_action'] == 'checkusername') {


	$username = trim($_G['gp_username']);
	loaducenter();
	$ucresult = uc_user_checkname($username);

	if($ucresult == -1) {
		showmessage('profile_username_illegal');
	} elseif($ucresult == -2) {
		showmessage('profile_username_protect');
	} elseif($ucresult == -3) {
		if(DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$username'")) {
			showmessage('register_check_found');
		} else {
			showmessage('register_activation');
		}
	}

} elseif($_G['gp_action'] == 'checkemail') {

	$email = trim($_G['gp_email']);
	loaducenter();
	$ucresult = uc_user_checkemail($email);

	if($ucresult == -4) {
		showmessage('profile_email_illegal');
	} elseif($ucresult == -5) {
		showmessage('profile_email_domain_illegal');
	} elseif($ucresult == -6) {
		showmessage('profile_email_duplicate');
	}

} elseif($_G['gp_action'] == 'checkuserexists') {

	$check = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='".trim($_G['gp_username'])."'");
	$check ? showmessage('<img src="'.IMGDIR.'/check_right.gif" width="13" height="13">', '', array(), array('msgtype' => 3))
		: showmessage('username_nonexistence', '', array(), array('msgtype' => 3));

} elseif($_G['gp_action'] == 'checkinvitecode') {

	$invitecode = trim($_G['gp_invitecode']);
	$check = DB::result_first("SELECT invitecode FROM ".DB::table('common_invite')." WHERE invitecode='".trim($invitecode)."' AND status IN ('1', '3')");
	if(!$check) {
		showmessage('invite_invalid');
	} else {
		$query = DB::query("SELECT m.username FROM ".DB::table('common_invite')." i, ".DB::table('common_member')." m WHERE invitecode='".trim($invitecode)."' AND i.uid=m.uid");
		$inviteuser = DB::fetch($query);
		$inviteuser = $inviteuser['username'];
		showmessage('invite_send', '', array('inviteuser' => $inviteuser, 'bbname' => $_G['setting']['bbname']));
	}

} elseif($_G['gp_action'] == 'attachlist') {

	require_once libfile('function/post');
	$attachlist = getattach($_G['gp_pid'], intval($_G['gp_posttime']));
	$attachlist = $attachlist['attachs']['unused'];
	$_G['group']['maxprice'] = isset($_G['setting']['extcredits'][$_G['setting']['creditstrans']]) ? $_G['group']['maxprice'] : 0;

	include template('common/header_ajax');
	include template('forum/ajax_attachlist');
	include template('common/footer_ajax');
	dexit();

} elseif($_G['gp_action'] == 'imagelist') {

	require_once libfile('function/post');
	$attachlist = getattach($_G['gp_pid']);
	$imagelist = $attachlist['imgattachs']['unused'];

	include template('common/header_ajax');
	include template('forum/ajax_imagelist');
	include template('common/footer_ajax');
	dexit();

} elseif($_G['gp_action'] == 'secondgroup') {

	require_once libfile('function/group');
	$groupselect = get_groupselect($_G['gp_fupid'], $_G['gp_groupid']);
	include template('common/header_ajax');
	include template('forum/ajax_secondgroup');
	include template('common/footer_ajax');
	dexit();

} elseif($_G['gp_action'] == 'displaysearch_adv') {
	$display = $_G['gp_display'] == 1 ? 1 : '';
	dsetcookie('displaysearch_adv', $display);
} elseif($_G['gp_action'] == 'checkgroupname') {
	$groupname = stripslashes(trim($_G['gp_groupname']));
	if(empty($groupname)) {
		showmessage('group_name_empty', '', array(), array('msgtype' => 3));
	}
	$tmpname = cutstr($groupname, 200, '');
	if($tmpname != $groupname) {
		showmessage('group_name_oversize', '', array(), array('msgtype' => 3));
	}
	if(DB::result_first("SELECT fid FROM ".DB::table('forum_forum')." WHERE name='".addslashes($groupname)."' AND type='sub' ")) {
		showmessage('group_name_exist', '', array(), array('msgtype' => 3));
	}
	include template('common/header_ajax');
	include template('common/footer_ajax');
	dexit();
} elseif($_G['gp_action'] == 'checkgrouplevel') {//ajax验证经验值等级是否已存在
	$result="";	 
	$levelname = urldecode(trim($_G['gp_levelname']));
	$fid=$_G['gp_fid'];
	//$levelname = iconv("UTF-8","GB2312",$string);
	//$levelname = urldecode($string);
	loaducenter();	
	if($levelname&&$fid){
		$check = DB::result_first("SELECT id FROM pre_forum_userlevel WHERE level_name='".$levelname."' AND fid=".$fid.";");
		if($check){
			showmessage("false");
		}else{
			showmessage("succeed");
		}
	}else{
		showmessage("false");
	}	

}elseif($_G['gp_action'] == 'ajaxupdate') {
	//评选投票
	$selectionid = empty($_GET['selectionid'])?0:intval($_GET['selectionid']);
	global  $_G;
	$mod=$_GET['mod'];
	$query=DB::query("SELECT * FROM ".DB::TABLE("selection")." WHERE selectionid='$selectionid' AND fid='$_G[fid]' AND moderated!='-1'");
	$selection = DB::fetch($query);
	if($selection){
			$query = DB :: query("SELECT * FROM " . DB :: table('selection_user_vote_num') . " WHERE selectionid='$selectionid' AND uid='$_G[uid]' LIMIT 1");
			$uservotenum = DB :: fetch($query);	 
			$uservotenum['usednum'] = 0;
			$uservotenum['dateline']= $_G['timestamp'];
			$uservotenum = DB :: update("selection_user_vote_num",$uservotenum,"id=".$uservotenum['id']); 
	} 	
	showmessage("succeed");
}

showmessage($_G['setting']['reglinkname']);

?>