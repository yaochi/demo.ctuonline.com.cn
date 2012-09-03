<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: thread_debate.php 6752 2010-03-25 08:47:54Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$debate = $_G['forum_thread'];
$debate = $sdb->fetch_first("SELECT * FROM ".DB::table('forum_debate')." WHERE tid='$_G[tid]'");
$debate['dbendtime'] = $debate['endtime'];
if($debate['dbendtime']) {
	$debate['endtime'] = dgmdate($debate['dbendtime']);
}
if($debate['dbendtime'] > TIMESTAMP) {
	$debate['remaintime'] = remaintime($debate['dbendtime'] - TIMESTAMP);
}
$debate['starttime'] = dgmdate($debate['starttime'], 'u');
$debate['affirmpoint'] = discuzcode($debate['affirmpoint'], 0, 0, 0, 1, 1, 0, 0, 0, 0, 0);
$debate['negapoint'] = discuzcode($debate['negapoint'], 0, 0, 0, 1, 1, 0, 0, 0, 0, 0);
if($debate['affirmvotes'] || $debate['negavotes']) {
	if($debate['affirmvotes'] && $debate['affirmvotes'] > $debate['negavotes']) {
		$debate['affirmvoteswidth'] = 100;
		$debate['negavoteswidth'] = intval($debate['negavotes'] / $debate['affirmvotes'] * 100);
		$debate['negavoteswidth'] = $debate['negavoteswidth'] > 0 ? $debate['negavoteswidth'] : 5;
	} elseif($debate['negavotes'] && $debate['negavotes'] > $debate['affirmvotes']) {
		$debate['negavoteswidth'] = 100;
		$debate['affirmvoteswidth'] = intval($debate['affirmvotes'] / $debate['negavotes'] * 100);
		$debate['affirmvoteswidth'] = $debate['affirmvoteswidth'] > 0 ? $debate['affirmvoteswidth'] : 5;
	} else {
		$debate['affirmvoteswidth'] = $debate['negavoteswidth'] = 100;
	}
} else {
	$debate['negavoteswidth'] = $debate['affirmvoteswidth'] = 5;
}
if($debate['umpirepoint']) {
	$debate['umpirepoint'] = discuzcode($debate['umpirepoint'], 0, 0, 0, 1, 1, 1, 0, 0, 0, 0);
}
$debate['umpireurl'] = rawurlencode($debate['umpire']);
list($debate['bestdebater'], $debate['bestdebateruid'], $debate['bestdebaterstand'], $debate['bestdebatervoters'], $debate['bestdebaterreplies']) = explode("\t", $debate['bestdebater']);
$debate['bestdebaterurl'] = rawurlencode($debate['bestdebater']);
$posttable = getposttablebytid($_G['tid']);
$query = $sdb->query("SELECT author, authorid FROM ".DB::table($posttable)." p LEFT JOIN ".DB::table('forum_debatepost')." dp ON p.pid=dp.pid WHERE p.tid='$_G[tid]' AND p.invisible='0' AND dp.stand='1' GROUP BY dp.uid ORDER BY p.dateline DESC LIMIT 8");
while($affirmavatar = $sdb->fetch_array($query)) {
	$affirmavatar['avatar'] = discuz_uc_avatar($affirmavatar['authorid'], 'small');
	$debate['affirmavatars'][] = $affirmavatar;
}

$query = $sdb->query("SELECT author, authorid FROM ".DB::table($posttable)." p LEFT JOIN ".DB::table('forum_debatepost')." dp ON p.pid=dp.pid WHERE p.tid='$_G[tid]' AND p.invisible='0' AND dp.stand='2' GROUP BY dp.uid ORDER BY p.dateline DESC LIMIT 8");
while($negaavatar = $sdb->fetch_array($query)) {
	$negaavatar['avatar'] = discuz_uc_avatar($negaavatar['authorid'], 'small');
	$debate['negaavatars'][] = $negaavatar;
}

if($_G['setting']['fastpost'] && $allowpostreply && $_G['forum_thread']['closed'] == 0) {
	$firststand = $sdb->result_first("SELECT stand FROM ".DB::table('forum_debatepost')." WHERE tid='$_G[tid]' AND uid='$_G[uid]' AND stand<>'0' ORDER BY dateline LIMIT 1");
}

?>