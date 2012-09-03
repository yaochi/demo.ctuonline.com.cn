<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: thread_activity.php 6752 2010-03-25 08:47:54Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$sdb = loadmultiserver();
$applylist = array();
$activity = $sdb->fetch_first("SELECT * FROM ".DB::table('forum_activity')." WHERE tid='$_G[tid]'");
$activityclose = $activity['expiration'] ? ($activity['expiration'] > TIMESTAMP - date('Z') ? 0 : 1) : 0;
$activity['starttimefrom'] = dgmdate($activity['starttimefrom'], 'u');
$activity['starttimeto'] = $activity['starttimeto'] ? dgmdate($activity['starttimeto']) : 0;
$activity['expiration'] = $activity['expiration'] ? dgmdate($activity['expiration']) : 0;
$activity['attachurl'] = $activity['thumb'] = '';

if($activity['aid']) {
	$attach = DB::fetch_first("SELECT a.*,af.description FROM ".DB::table('forum_attachment')." a LEFT JOIN ".DB::table('forum_attachmentfield')." af ON a.aid=af.aid WHERE a.aid='$activity[aid]'");
	if($attach['isimage']) {
		$activity['attachurl'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/'.$attach['attachment'];
		$activity['thumb'] = $activity['attachurl'].($attach['thumb'] ? '.thumb.jpg' : '');
		$activity['width'] = $attach['thumb'] && $_G['setting']['thumbwidth'] < $attach['width'] ? $_G['setting']['thumbwidth'] : $attach['width'];
	}
	$skipaids[] = $activity['aid'];
}

$isverified = $applied = 0;
if($_G['uid']) {
	$query = DB::query("SELECT verified FROM ".DB::table('forum_activityapply')." WHERE tid='$_G[tid]' AND uid='$_G[uid]'");
	if(DB::num_rows($query)) {
		$isverified = DB::result($query, 0);
		$applied = 1;
	}
}

$query = DB::query("SELECT aa.username, aa.uid, aa.dateline, aa.message, aa.payment, aa.contact, m.groupid FROM ".DB::table('forum_activityapply')." aa
	LEFT JOIN ".DB::table('common_member')." m USING(uid)
	LEFT JOIN ".DB::table('common_member_field_forum')." mf USING(uid)
	WHERE aa.tid='$_G[tid]' AND aa.verified=1 ORDER BY aa.dateline DESC LIMIT 8");
while($activityapplies = DB::fetch($query)) {
	$activityapplies['dateline'] = dgmdate($activityapplies['dateline'], 'u');
	$applylist[] = $activityapplies;
}

if($_G['forum_thread']['authorid'] == $_G['uid']) {
	$applylistverified = array();
	$query = DB::query("SELECT aa.username, aa.uid, aa.dateline, aa.message, aa.payment, aa.contact, m.groupid FROM ".DB::table('forum_activityapply')." aa
		LEFT JOIN ".DB::table('common_member')." m USING(uid)
		LEFT JOIN ".DB::table('common_member_field_forum')." mf USING(uid)
		WHERE aa.tid='$_G[tid]' AND aa.verified=0 ORDER BY aa.dateline DESC LIMIT 9");
	while($activityapplies = DB::fetch($query)) {
		$activityapplies['dateline'] = dgmdate($activityapplies['dateline'], 'u');
		$applylistverified[] = $activityapplies;
	}
}

$applynumbers = $activity['applynumber'];
$aboutmembers = $activity['number'] >= $applynumbers ? $activity['number'] - $applynumbers : 0;

if($_G['forum']['status'] == 3) {
	$isgroupuser = groupperm($_G['forum'], $_G['uid']);
}
?>