<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_comment.php 9821 2010-05-05 04:03:14Z wangjinbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$cid = intval($_GET['cid']);
$comment = array();
if($cid) {
	$query = DB::query("SELECT * FROM ".DB::table('portal_comment')." WHERE cid='$cid'");
	$comment = db::fetch($query);
}
if($_GET['op'] == 'requote') {

	if(!empty($comment['message'])) {

		include_once libfile('class/bbcode');
		$bbcode = & bbcode::instance();
		$comment['message'] = $bbcode->html2bbcode($comment['message']);
		$comment['message'] = preg_replace("/\[quote\].*?\[\/quote\]/is", '', $comment['message']);
		$comment['message'] = getstr($comment['message'], 150, 0, 0, 0, 2, -1);
	}

} elseif($_GET['op'] == 'edit') {

	if(empty($comment)) {
		showmessage('comment_edit_noexist');
	}

	if(!$_G['group']['allowmanagearticle'] && $_G['uid'] != $comment['uid']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	}

	if(submitcheck('editsubmit')) {
		$message = getstr($_POST['message'], 0, 1, 1, 1, 2);
		if(strlen($message) < 2) showmessage('content_is_too_short');

		DB::update('portal_comment', array('message'=>$message), array('cid' => $comment['cid']));

		showmessage('do_success', dreferer());
	}

	include_once libfile('class/bbcode');
	$bbcode = & bbcode::instance();
	$comment['message'] = $bbcode->html2bbcode($comment['message']);

} elseif($_GET['op'] == 'delete') {

	if(empty($comment)) {
		showmessage('comment_delete_noexist');
	}

	if(!$_G['group']['allowmanagearticle'] && $_G['uid'] != $comment['uid']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	}

	if(submitcheck('deletesubmit')) {
		db::query("DELETE FROM ".DB::table('portal_comment')." WHERE cid='$cid'");
		db::query("UPDATE ".DB::table('portal_article_count')." SET commentnum=commentnum-1 WHERE aid='$comment[aid]'");
		showmessage('do_success', dreferer());
	}

}

if(submitcheck('commentsubmit')) {

	if(!$_G['group']['allowcomment']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	}

	$aid = intval($_POST['aid']);

	$article = DB::fetch_first("SELECT * FROM ".DB::table('portal_article_title')." WHERE aid='$aid'");
	if(empty($article)) {
		showmessage("comment_comment_noexist");
	}
	if($article['allowcomment'] != 1) {
		showmessage("comment_comment_notallowed");
	}

	$message = getstr($_POST['message'], 0, 1, 1, 1, 1, 0);
	if(strlen($message) < 2) showmessage('content_is_too_short');

	$setarr = array(
		'uid' => $_G['uid'],
		'username' => $_G['username'],
		'aid' => $aid,
		'postip' => $_G['onlineip'],
		'dateline' => $_G['timestamp'],
		'message' => $message
	);

	DB::insert('portal_comment', $setarr);

	DB::query("UPDATE ".DB::table('portal_article_count')." SET commentnum=commentnum+1 WHERE aid='$aid'");

	showmessage('do_success', $_POST['referer']?$_POST['referer']:"portal.php?mod=comment&quickforward=1&aid=$aid");
}

include_once template("portal/portalcp_comment");

?>