<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: thread_reward.php 9199 2010-04-27 09:10:37Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$bapid = 0;
$rewardprice = abs($_G['forum_thread']['price']);
$dateline = $_G['forum_thread']['dateline'] + 1;
$bestpost = array();
if($_G['forum_thread']['price'] < 0 && $page == 1) {
	foreach($postlist as $key => $post) {
		if($post['dbdateline'] == $dateline) {
			$bapid = $key;
			break;
		}
	}
}elseif($_G['forum_thread']['price'] ==0&&$_G['forum_thread']['question_status']=='Y'){//elseif added by fumz，2010-8-5 14:02:42
	foreach($postlist as $key => $post) {
		if($post['dbdateline'] == $dateline) {
			$bapid = $key;
			break;
		}
	}
}

if($bapid) {
	$bestpost = DB::fetch_first("SELECT p.* FROM ".DB::table($posttable)." p WHERE p.pid='$bapid'");
	$bestpost['message'] = messagecutstr($bestpost['message'], 400);
	$bestpost['avatar'] = discuz_uc_avatar($bestpost['authorid'], 'small');
}

?>