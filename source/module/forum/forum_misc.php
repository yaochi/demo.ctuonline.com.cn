<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_misc.php 11691 2010-06-11 05:09:36Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);
require_once libfile('function/post');

$feed = array();
if($_G['gp_action'] == 'paysucceed') {

	$url = !empty($_G['gp_orderid']) ? 'forum.php?mod=trade&orderid='.intval($_G['gp_orderid']) : 'home.php?mod=spacecp&ac=credit';
	showmessage('payonline_succeed', $url);

} elseif($_G['gp_action'] == 'nav') {

	require_once libfile('misc/forumselect', 'include');
	exit;

} elseif($_G['gp_action'] == 'attachcredit') {
	if($_G['gp_formhash'] != FORMHASH) {
		showmessage('undefined_action', NULL);
	}

	$aid = intval($_G['gp_aid']);
	$attach = DB::fetch_first("SELECT tid, filename FROM ".DB::table('forum_attachment')." WHERE aid='$aid'");
	$thread = DB::fetch_first("SELECT fid FROM ".DB::table('forum_thread')." WHERE tid='$attach[tid]' AND displayorder>='0'");

	checklowerlimit('getattach');
	$getattachcredits = updatecreditbyaction('getattach', $_G['uid'], array(), '', 1, 1, $thread['fid']);
	$_G['policymsg'] = $p = '';
	if($getattachcredits['updatecredit']) {
		if($getattachcredits['updatecredit']) for($i = 1;$i <= 8;$i++) {
			if($policy = $getattachcredits['extcredits'.$i]) {
				$_G['policymsg'] .= $p.($_G['setting']['extcredits'][$i]['img'] ? $_G['setting']['extcredits'][$i]['img'].' ' : '').$_G['setting']['extcredits'][$i]['title'].' '.$policy.' '.$_G['setting']['extcredits'][$i]['unit'];
				$p = ', ';
			}
		}
	}

	$ck = substr(md5($aid.TIMESTAMP.md5($_G['config']['security']['authkey'])), 0, 8);
	$aidencode = aidencode($aid);
	showmessage('attachment_credit', "forum.php?mod=attachment&aid=$aidencode&ck=$ck", array('policymsg' => $_G['policymsg'], 'filename' => $attach['filename']), array('redirectmsg' => 1, 'login' => 1));

} elseif($_G['gp_action'] == 'attachpay') {
	$aid = intval($_G['gp_aid']);
	$aidencode = aidencode($aid);
	if(!$aid) {
		showmessage('undefined_action', NULL);
	} elseif(!isset($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]])) {
		showmessage('credits_transaction_disabled');
	} elseif(!$_G['uid']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	} else {
		$attach = DB::fetch_first("SELECT a.aid, a.tid, a.pid, a.uid, a.price, a.filename, af.description, a.readperm, m.username AS author FROM ".DB::table('forum_attachment')." a LEFT JOIN ".DB::table('forum_attachmentfield')." af ON a.aid=af.aid LEFT JOIN ".DB::table('common_member')." m ON a.uid=m.uid WHERE a.aid='$aid'");
		if($attach['price'] <= 0) {
			showmessage('undefined_action', NULL);
		}
	}

	if($attach['readperm'] && $attach['readperm'] > $_G['group']['readaccess']) {
		showmessage('attachment_forum_nopermission', NULL, array(), array('login' => 1));
	}

	if(($balance = getuserprofile('extcredits'.$_G['setting']['creditstransextra'][1]) - $attach['price']) < ($minbalance = 0)) {
		showmessage('credits_balance_insufficient', '', array('title' => $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['title'], 'minbalance' => $attach['price']));
	}

	$sidauth = rawurlencode(authcode($_G['sid'], 'ENCODE', $_G['authkey']));

	if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_credit_log')." WHERE relatedid='$aid' AND uid='$_G[uid]' AND operation='BAC'")) {
		showmessage('attachment_yetpay', "forum.php?mod=attachment&aid=$aidencode", array(), array('redirectmsg' => 1));
	}

	$attach['netprice'] = round($attach['price'] * (1 - $_G['setting']['creditstax']));

	if(!submitcheck('paysubmit')) {
		include template('forum/attachpay');
	} else {
		$updateauthor = 1;
		if($_G['setting']['maxincperthread'] > 0) {
			$extcredit = 'extcredits'.$_G['setting']['creditstransextra'][1];
			if((DB::result_first("SELECT SUM($extcredit) FROM ".DB::table('common_credit_log')." WHERE relatedid='$aid' AND uid='$attach[uid]' AND operation='SAC'")) > $_G['setting']['maxincperthread']) {
				$updateauthor = 0;
			}
		}
		if($updateauthor) {
			updatemembercount($attach['uid'], array($_G['setting']['creditstransextra'][1] => $attach['netprice']), 1, 'SAC', $attach['aid']);
		}
		updatemembercount($_G['uid'], array($_G['setting']['creditstransextra'][1] => -$attach['price']), 1, 'BAC', $attach['aid']);

		$aidencode = aidencode($aid);
		$_G['forum_attach_filename'] = $attach['filename'];
		showmessage('attachment_buy', "forum.php?mod=attachment&aid=$aidencode", array('filename' => $_G['forum_attach_filename']), array('redirectmsg' => 1));
	}

} elseif($_G['gp_action'] == 'viewattachpayments') {

	$aid = intval($_G['gp_aid']);
	$extcreditname = 'extcredits'.$_G['setting']['creditstransextra'][1];

	$loglist = array();
	$query = DB::query("SELECT l.*, m.username FROM ".DB::table('common_credit_log')." l
		LEFT JOIN ".DB::table('common_member')." m USING (uid)
		WHERE l.relatedid='$aid' AND l.operation='BAC' ORDER BY l.dateline");
	while($log = DB::fetch($query)) {
		$log['dateline'] = dgmdate($log['dateline'], 'u');
		$log[$extcreditname] = abs($log[$extcreditname]);
		$loglist[] = $log;
	}
	include template('forum/attachpay_view');

} elseif($_G['gp_action'] == 'getonlines') {

	$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_session')."", 0);
	showmessage($num);

} elseif($_G['gp_action'] == 'upload') {

	$_G['group']['allowpostattach'] = $_G['forum']['allowpostattach'] != -1 && ($_G['forum']['allowpostattach'] == 1 || (!$_G['forum']['postattachperm'] && $_G['group']['allowpostattach']) || ($_G['forum']['postattachperm'] && forumperm($_G['forum']['postattachperm'])));
	$_G['group']['allowpostimage'] = $_G['forum']['allowpostimage'] != -1 && ($_G['forum']['allowpostimage'] == 1 || (!$_G['forum']['postimageperm'] && $_G['group']['allowpostattach']) || ($_G['forum']['postimageperm'] && forumperm($_G['forum']['postimageperm'])));
	$_G['group']['attachextensions'] = $_G['forum']['attachextensions'] ? $_G['forum']['attachextensions'] : $_G['group']['attachextensions'];
	if($_G['group']['attachextensions']) {
		$imgexts = explode(',', str_replace(' ', '', $_G['group']['attachextensions']));
		$imgexts = array_intersect(array('jpg','jpeg','gif','png','bmp'), $imgexts);
		$imgexts = implode(', ', $imgexts);
	} else {
		$imgexts = 'jpg, gif';
	}
	$allowpostimg = $_G['group']['allowpostimage'] && $imgexts;
	if(!$allowpostimg) {
		showmessage('undefined_action', NULL);
	}

	include template('forum/upload');

} elseif($_G['gp_action'] == 'comment') {

	if(!$_G['setting']['commentnumber'] || !$_G['inajax']) {
		showmessage('undefined_action', NULL);
	}
	$posttable = getposttablebytid($_G['tid']);
	$post = DB::fetch_first('SELECT * FROM '.DB::table($posttable)." WHERE pid='$_G[gp_pid]'");
	if($_G['group']['allowcommentitem'] && $post['authorid'] != $_G['uid']) {
		$itemi = DB::result_first('SELECT special FROM '.DB::table('forum_thread')." WHERE tid='$post[tid]'");
		if($itemi > 0) {
			if($itemi == 2){
				$itemi = $post['first'] || DB::result_first('SELECT count(*) FROM '.DB::table('forum_trade')." WHERE pid='$post[pid]'") ? 2 : 0;
			} else {
				$itemi = $post['first'] ? $itemi : 0;
			}
		}
		$_G['setting']['commentitem'] = $_G['setting']['commentitem'][$itemi];
		if($itemi == 0) {
			loadcache('forums');
			if($_G['cache']['forums'][$post['fid']]['commentitem']) {
				$_G['setting']['commentitem'] = $_G['cache']['forums'][$post['fid']]['commentitem'];
			}
		}
		if($_G['setting']['commentitem'] && !DB::result_first('SELECT count(*) FROM '.DB::table('forum_postcomment')." WHERE pid='$_G[gp_pid]' AND authorid='$_G[uid]' AND score='1'")) {
			$commentitem = explode("\n", $_G['setting']['commentitem']);
		}
	}
	if(!$post || !($_G['setting']['commentpostself'] || $post['authorid'] != $_G['uid']) || !(($post['first'] && $_G['setting']['commentfirstpost'] && in_array($_G['group']['allowcommentpost'], array(1, 3)) || (!$post['first'] && in_array($_G['group']['allowcommentpost'], array(2, 3)))))) {
		showmessage('undefined_action', NULL);
	}
	$extra = !empty($_G['gp_extra']) ? rawurlencode($_G['gp_extra']) : '';
	$seccodecheck = ($_G['setting']['seccodestatus'] & 4) && (!$_G['setting']['seccodedata']['minposts'] || getuserprofile('posts') < $_G['setting']['seccodedata']['minposts']);
	$secqaacheck = $_G['setting']['secqaa']['status'] & 2 && (!$_G['setting']['secqaa']['minposts'] || getuserprofile('posts') < $_G['setting']['secqaa']['minposts']);

	include template('forum/comment');

} elseif($_G['gp_action'] == 'commentmore') {

	if(!$_G['setting']['commentnumber'] || !$_G['inajax']) {
		showmessage('undefined_action', NULL);
	}
	require_once libfile('function/discuzcode');
	$commentlimit = intval($_G['setting']['commentnumber']);
	$page = max(1, $_G['page']);
	$start_limit = ($page - 1) * $commentlimit;
	$comments = array();
	$query = DB::query('SELECT * FROM '.DB::table('forum_postcomment')." WHERE pid='$_G[gp_pid]' AND authorid>'0' ORDER BY dateline DESC LIMIT $start_limit, $commentlimit");
	while($comment = DB::fetch($query)) {
		$comment['avatar'] = discuz_uc_avatar($comment['authorid'], 'small');
		$comment['dateline'] = dgmdate($comment['dateline'], 'u');
		$comments[] = str_replace(array('[b]', '[/b]', '[/color]'), array('<b>', '</b>', '</font>'), preg_replace("/\[color=([#\w]+?)\]/i", "<font color=\"\\1\">", $comment));
	}
	$totalcomment = DB::result_first('SELECT comment FROM '.DB::table('forum_postcomment')." WHERE pid='$_G[gp_pid]' AND authorid='0'");
	$totalcomment = preg_replace('/<i>([\.\d]+)<\/i>/e', "'<i class=\"cmstarv\" style=\"background-position:20px -'.(intval(\\1) * 16).'px\">'.sprintf('%1.1f', \\1).'</i>'.(\$cic++ % 2 ? '<br />' : '');", $totalcomment);
	$count = DB::result_first('SELECT count(*) FROM '.DB::table('forum_postcomment')." WHERE pid='$_G[gp_pid]' AND authorid>'0'");
	$multi = multi($count, $commentlimit, $page, "forum.php?mod=misc&action=commentmore&tid=$_G[tid]&pid=$_G[gp_pid]");
	include template('forum/comment_more');

} else {

	if(empty($_G['forum']['allowview'])) {
		if(!$_G['forum']['viewperm'] && !$_G['group']['readaccess']) {
			showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
		} elseif($_G['forum']['viewperm'] && !forumperm($_G['forum']['viewperm'])) {
			showmessage('forum_nopermission', NULL, array($_G['group']['grouptitle']), array('login' => 1));
		}
	}

	$thread = DB::fetch_first("SELECT * FROM ".DB::table('forum_thread')." WHERE tid='$_G[tid]' AND displayorder>='0'");
	if($thread['readperm'] && $thread['readperm'] > $_G['group']['readaccess'] && !$_G['forum']['ismoderator'] && $thread['authorid'] != $_G['uid']) {
		showmessage('thread_nopermission', NULL, array('readperm' => $thread['readperm']), array('login' => 1));
	}

	if($_G['forum']['password'] && $_G['forum']['password'] != $_G['cookie']['fidpw'.$_G['fid']]) {
		showmessage('forum_passwd', "forum.php?mod=forumdisplay&fid=$_G[fid]");
	}


	if(!$thread) {
		showmessage('thread_nonexistence');
	}

	if($_G['forum']['type'] == 'forum') {
		$navigation = "&rsaquo; <a href=\"forum.php?mod=forumdisplay&fid=$_G[fid]\">".$_G['forum']['name']."</a> &raquo; <a href=\"forum.php?mod=viewthread&tid=$_G[tid]\">$thread[subject]</a> ";
		$navtitle = strip_tags($_G['forum']['name']).' - '.$thread['subject'];
	} elseif($_G['forum']['type'] == 'sub') {
		$fup = DB::fetch_first("SELECT name, fid FROM ".DB::table('forum_forum')." WHERE fid='".$_G['forum']['fup']."'");
		$navigation = "&rsaquo; <a href=\"forum.php?mod=forumdisplay&fid=$fup[fid]\">$fup[name]</a> &raquo; <a href=\"forum.php?mod=forumdisplay&fid=$_G[fid]\">".$_G['forum']['name']."</a> &raquo; <a href=\"forum.php?mod=viewthread&tid=$_G[tid]\">$thread[subject]</a> ";
		$navtitle = strip_tags($fup['name']).' - '.strip_tags($_G['forum']['name']).' - '.$thread['subject'];
	}

}

if($_G['gp_action'] == 'votepoll' && submitcheck('pollsubmit', 1)) {

	if(!$_G['group']['allowvote']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	} elseif(!empty($thread['closed'])) {
		showmessage('thread_poll_closed', NULL, array(), array('login' => 1));
	} elseif(empty($_G['gp_pollanswers'])) {
		showmessage('thread_poll_invalid', NULL, array(), array('login' => 1));
	}
	$thread	= DB::fetch_first("SELECT * FROM ".DB::table('forum_thread')." WHERE tid='$_G[tid]'");
	$fid=$thread['fid'];
	$pollarray = DB::fetch_first("SELECT overt, maxchoices, expiration FROM ".DB::table('forum_poll')." WHERE tid='$_G[tid]'");
	$overt = $pollarray['overt'];
	if(!$pollarray) {
		showmessage('undefined_action', NULL);
	} elseif($pollarray['expiration'] && $pollarray['expiration'] < TIMESTAMP) {
		showmessage('poll_overdue', NULL, array(), array('login' => 1));
	} elseif($pollarray['maxchoices'] && $pollarray['maxchoices'] < count($_G['gp_pollanswers'])) {
		showmessage('poll_choose_most', NULL, array('maxchoices' => $pollarray['maxchoices']), array('login' => 1));
	}

	$voterids = $_G['uid'] ? $_G['uid'] : $_G['clientip'];

	$polloptionid = array();
	$query = DB::query("SELECT polloptionid, voterids FROM ".DB::table('forum_polloption')." WHERE tid='$_G[tid]'");
	while($pollarray = DB::fetch($query)) {
		if(strexists("\t".$pollarray['voterids']."\t", "\t".$voterids."\t")) {
			showmessage('thread_poll_voted', NULL, array(), array('login' => 1));
		}
		$polloptionid[] = $pollarray['polloptionid'];
	}

	$polloptionids = '';
	foreach($_G['gp_pollanswers'] as $key => $id) {
		if(!in_array($id, $polloptionid)) {
			showmessage('undefined_action', NULL);
		}
		unset($polloptionid[$key]);
		$polloptionids[] = $id;
	}

	$pollanswers = implode('\',\'', $polloptionids);

	DB::query("UPDATE ".DB::table('forum_polloption')." SET votes=votes+1, voterids=CONCAT(voterids,'$voterids\t') WHERE polloptionid IN ('$pollanswers')", 'UNBUFFERED');
	DB::query("UPDATE ".DB::table('forum_thread')." SET lastpost='$_G[timestamp]' WHERE tid='$_G[tid]'", 'UNBUFFERED');
	DB::query("UPDATE ".DB::table('forum_poll')." SET voters=voters+1 WHERE tid='$_G[tid]'", 'UNBUFFERED');

	DB::insert('forum_pollvoter', array(
		'tid' => $_G['tid'],
		'uid' => $_G['uid'],
		'username' => $_G['username'],
		'options' => implode("\t", $_G['gp_pollanswers']),
		'dateline' => $_G['timestamp'],
		));

	updatecreditbyaction('joinpoll');

	$space = array();
	space_merge($space, 'field_home');

	if($overt && !empty($space['privacy']['feed']['newreply'])) {
		$feed['icon'] = 'poll';
		$feed['title_template'] = 'feed_thread_votepoll_title';
		$feed['title_data'] = array(
			'subject' => "<a href=\"$_G[siteurl]forum.php?mod=viewthread&tid=$_G[tid]\">$thread[subject]</a>",
			'author' => "<a href=\"home.php?mod=space&uid=$thread[authorid]\">$thread[author]</a>",
			'hash_data' => "tid{$_G[tid]}"
		);
		$feed['id'] = $_G['tid'];
		$feed['idtype'] = 'tid';
		postfeed($feed);
	}
	$posttable = getposttablebytid($_G['tid']);
	$pid = DB::result_first("SELECT pid FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND first='1'");
	
	
	//积分
	require_once libfile("function/credit");
	credit_create_credit_log($_G['uid'],"joinpoll",$_G[tid]);
	
	//经验值
	require_once libfile("function/group");
	group_add_empirical_by_setting($_G['uid'],$fid, 'vote_join', $_G[tid]);
	
	

	if(!empty($_G['inajax'])) {
		dheader("location: forum.php?mod=viewthread&tid=$_G[tid]&viewpid=$pid&inajax=1");
	} else {
		showmessage('thread_poll_succeed', "forum.php?mod=viewthread&tid=$_G[tid]".($_G['gp_from'] ? '&from='.$_G['gp_from'] : ''));
	}

} elseif($_G['gp_action'] == 'viewvote') {

	require_once libfile('function/post');
	$polloptionid = is_numeric($_G['gp_polloptionid']) ? $_G['gp_polloptionid'] : '';

	$overt = DB::result_first("SELECT overt FROM ".DB::table('forum_poll')." WHERE tid='$_G[tid]'");

	$polloptions = array();
	$query = DB::query("SELECT polloptionid, polloption FROM ".DB::table('forum_polloption')." WHERE tid='$_G[tid]'");
	while($options = DB::fetch($query)) {
		if(empty($polloptionid)) {
			$polloptionid = $options['polloptionid'];
		}
		$options['polloption'] = preg_replace("/\[url=(https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/i",
			"<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>", $options['polloption']);
		$polloptions[] = $options;
	}

	$arrvoterids = array();
	if($overt || $_G['adminid'] == 1) {
		$voterids = '';
		$voterids = DB::result_first("SELECT voterids FROM ".DB::table('forum_polloption')." WHERE polloptionid='$polloptionid'");
		$arrvoterids = explode("\t", trim($voterids));
	}

	if(!empty($arrvoterids)) {
		$arrvoterids = array_slice($arrvoterids, -100);
	}
	$voterlist = $voter = array();
	if($voterids = dimplode($arrvoterids)) {
		$query = DB::query("SELECT uid, username FROM ".DB::table('common_member')." WHERE uid IN ($voterids)");
		while($voter = DB::fetch($query)) {
			$voterlist[] = $voter;
		}
	}
	include template('forum/viewthread_poll_voter');

} elseif($_G['gp_action'] == 'rate' && $_G['gp_pid']) {

	if(!$_G['inajax']) {
		showmessage('undefined_action', NULL);
	}
	if(!$_G['group']['raterange']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	} elseif($_G['setting']['modratelimit'] && $_G['adminid'] == 3 && !$_G['forum']['ismoderator']) {
		showmessage('thread_rate_moderator_invalid', NULL);
	}
	$posttable = getposttablebytid($_G['tid']);
	$reasonpmcheck = $_G['group']['reasonpm'] == 2 || $_G['group']['reasonpm'] == 3 ? 'checked="checked" disabled' : '';
	if(($_G['group']['reasonpm'] == 2 || $_G['group']['reasonpm'] == 3) || !empty($_G['gp_sendreasonpm'])) {
		$forumname = strip_tags($_G['forum']['name']);
		$sendreasonpm = 1;
	} else {
		$sendreasonpm = 0;
	}

	$post = DB::fetch_first("SELECT * FROM ".DB::table($posttable)." WHERE pid='$_G[gp_pid]' AND invisible='0' AND authorid<>'0'");
	if(!$post || $post['tid'] != $thread['tid'] || !$post['authorid']) {
		showmessage('undefined_action', NULL);
	} elseif(!$_G['forum']['ismoderator'] && $_G['setting']['karmaratelimit'] && TIMESTAMP - $post['dateline'] > $_G['setting']['karmaratelimit'] * 3600) {
		showmessage('thread_rate_timelimit', NULL, array('karmaratelimit' => $_G['setting']['karmaratelimit']));
	} elseif($post['authorid'] == $_G['uid'] || $post['tid'] != $_G['tid']) {
		showmessage('thread_rate_member_invalid', NULL);
	} elseif($post['anonymous']) {
		showmessage('thread_rate_anonymous', NULL);
	} elseif($post['status'] & 1) {
		showmessage('thread_rate_banned', NULL);
	}

	$allowrate = TRUE;
	if(!$_G['setting']['dupkarmarate']) {
		$query = DB::query("SELECT pid FROM ".DB::table('forum_ratelog')." WHERE uid='$_G[uid]' AND pid='$_G[gp_pid]' LIMIT 1");
		if(DB::num_rows($query)) {
			showmessage('thread_rate_duplicate', NULL);
		}
	}

	$page = intval($_G['gp_page']);

	require_once libfile('function/misc');

	if(!submitcheck('ratesubmit')) {
		$_G['referer'] = $_G['siteurl'].'forum.php?mod=viewthread&tid='.$_G['tid'].'&page='.$page.($_G['gp_from'] ? '&from='.$_G['gp_from'] : '').'#pid'.$_G['gp_pid'];
		$ratelist = getratelist($_G['group']['raterange']);
		include template('forum/rate');

	} else {

		$reason = checkreasonpm();
		$maxratetoday = getratingleft($_G['group']['raterange']);
		$rate = $ratetimes = 0;
		$creditsarray = array();
		foreach($_G['group']['raterange'] as $id => $rating) {
			$score = intval($_G['gp_score'.$id]);
			if(isset($_G['setting']['extcredits'][$id]) && !empty($score)) {
				if(abs($score) <= $maxratetoday[$id]) {
					if($score > $rating['max'] || $score < $rating['min']) {
						showmessage('thread_rate_range_invalid');
					} else {
						$creditsarray[$id] = $score;
						$rate += $score;
						$ratetimes += ceil(max(abs($rating['min']), abs($rating['max'])) / 5);
					}
				} else {
					showmessage('thread_rate_ctrl');
				}
			}
		}

		if(!$creditsarray) {
			showmessage('thread_rate_range_invalid', NULL);
		}

		updatemembercount($post['authorid'], $creditsarray, 1, 'PRC', $_G['gp_pid']);
		DB::query("UPDATE ".DB::table($posttable)." SET rate=rate+($rate), ratetimes=ratetimes+$ratetimes WHERE pid='$_G[gp_pid]'");
		if($post['first']) {
			$threadrate = intval(@($post['rate'] + $rate) / abs($post['rate'] + $rate));
			DB::query("UPDATE ".DB::table('forum_thread')." SET rate='$threadrate' WHERE tid='$_G[tid]'");

		}

		require_once libfile('function/discuzcode');
		$sqlvalues = $comma = '';
		$sqlreason = censor(trim($_G['gp_reason']));
		$sqlreason = cutstr(dhtmlspecialchars($sqlreason), 40);
		foreach($creditsarray as $id => $addcredits) {
			$sqlvalues .= "$comma('$_G[gp_pid]', '$_G[uid]', '$_G[username]', '$id', '$_G[timestamp]', '$addcredits', '$sqlreason')";
			$comma = ', ';
		}
		DB::query("INSERT INTO ".DB::table('forum_ratelog')." (pid, uid, username, extcredits, dateline, score, reason)
			VALUES $sqlvalues", 'UNBUFFERED');

		include_once libfile('function/post');
		$_G['forum']['threadcaches'] && @deletethreadcaches($_G['tid']);

		$reason = dhtmlspecialchars(censor(trim($reason)));
		if($sendreasonpm) {
			$ratescore = $slash = '';
			foreach($creditsarray as $id => $addcredits) {
				$ratescore .= $slash.$_G['setting']['extcredits'][$id]['title'].' '.($addcredits > 0 ? '+'.$addcredits : $addcredits).' '.$_G['setting']['extcredits'][$id]['unit'];
				$slash = ' / ';
			}
			sendreasonpm($post, 'rate_reason', array(
				'tid' => $thread['tid'],
				'pid' => $_G['gp_pid'],
				'subject' => $thread['subject'],
				'ratescore' => $ratescore,
				'reason' => stripslashes($reason),
			));
		}

		$logs = array();
		foreach($creditsarray as $id => $addcredits) {
			$logs[] = dhtmlspecialchars("$_G[timestamp]\t{$_G[member][username]}\t$_G[adminid]\t$post[author]\t$id\t$addcredits\t$_G[tid]\t$thread[subject]\t$reason");
		}
		writelog('ratelog', $logs);

		showmessage('thread_rate_succeed', dreferer());
	}
} elseif($_G['gp_action'] == 'removerate' && $_G['gp_pid']) {

	if(!$_G['forum']['ismoderator'] || !$_G['group']['raterange']) {
		showmessage('undefined_action');
	}

	$reasonpmcheck = $_G['group']['reasonpm'] == 2 || $_G['group']['reasonpm'] == 3 ? 'checked="checked" disabled' : '';
	if(($_G['group']['reasonpm'] == 2 || $_G['group']['reasonpm'] == 3) || !empty($_G['gp_sendreasonpm'])) {
		$forumname = strip_tags($_G['forum']['name']);
		$sendreasonpm = 1;
	} else {
		$sendreasonpm = 0;
	}

	foreach($_G['group']['raterange'] as $id => $rating) {
		$maxratetoday[$id] = $rating['mrpd'];
	}
	$posttable = getposttablebytid($_G['tid']);
	$post = DB::fetch_first("SELECT * FROM ".DB::table($posttable)." WHERE pid='$_G[gp_pid]' AND invisible='0' AND authorid<>'0'");
	if(!$post || $post['tid'] != $thread['tid'] || !$post['authorid']) {
		showmessage('undefined_action');
	}

	require_once libfile('function/misc');

	if(!submitcheck('ratesubmit')) {

		$_G['referer'] = $_G['siteurl'].'forum.php?mod=viewthread&tid='.$_G['tid'].'&page='.$page.($_G['gp_from'] ? '&from='.$_G['gp_from'] : '').'#pid'.$_G['gp_pid'];
		$ratelogs = array();
		$query = DB::query("SELECT * FROM ".DB::table('forum_ratelog')." WHERE pid='$_G[gp_pid]' ORDER BY dateline");
		while($ratelog = DB::fetch($query)) {
			$ratelog['dbdateline'] = $ratelog['dateline'];
			$ratelog['dateline'] = dgmdate($ratelog['dateline'], 'u');
			$ratelog['scoreview'] = $ratelog['score'] > 0 ? '+'.$ratelog['score'] : $ratelog['score'];
			$ratelogs[] = $ratelog;
		}

		include template('forum/rate');

	} else {

		$reason = checkreasonpm();

		if(!empty($_G['gp_logidarray'])) {
			if($sendreasonpm) {
				$ratescore = $slash = '';
			}

			$query = DB::query("SELECT * FROM ".DB::table('forum_ratelog')." WHERE pid='$_G[gp_pid]'");
			$rate = $ratetimes = 0;
			$logs = array();
			while($ratelog = DB::fetch($query)) {
				if(in_array($ratelog['uid'].' '.$ratelog['extcredits'].' '.$ratelog['dateline'], $_G['gp_logidarray'])) {
					$rate += $ratelog['score'] = -$ratelog['score'];
					$ratetimes += ceil(max(abs($rating['min']), abs($rating['max'])) / 5);
					updatemembercount($post['authorid'], array($ratelog['extcredits'] => $ratelog['score']));
					DB::delete('common_credit_log', array('uid' => $post['authorid'], 'operation' => 'PRC', 'relatedid' => $_G['gp_pid']));
					DB::query("DELETE FROM ".DB::table('forum_ratelog')." WHERE pid='$_G[gp_pid]' AND uid='$ratelog[uid]' AND extcredits='$ratelog[extcredits]' AND dateline='$ratelog[dateline]'", 'UNBUFFERED');
					$logs[] = dhtmlspecialchars("$_G[timestamp]\t{$_G[member][username]}\t$_G[adminid]\t$ratelog[username]\t$ratelog[extcredits]\t$ratelog[score]\t$_G[tid]\t$thread[subject]\t$reason\tD");
					if($sendreasonpm) {
						$ratescore .= $slash.$_G['setting']['extcredits'][$ratelog['extcredits']]['title'].' '.($ratelog['score'] > 0 ? '+'.$ratelog['score'] : $ratelog['score']).' '.$_G['setting']['extcredits'][$ratelog['extcredits']]['unit'];
						$slash = ' / ';
					}
				}
			}
			writelog('ratelog', $logs);

			if($sendreasonpm) {
				sendreasonpm($post, 'rate_removereason', array(
					'tid' => $thread['tid'],
					'pid' => $_G['gp_pid'],
					'subject' => $thread['subject'],
					'ratescore' => $ratescore,
					'reason' => stripslashes($reason),
				));
			}
			DB::query("UPDATE ".DB::table($posttable)." SET rate=rate+($rate), ratetimes=ratetimes-$ratetimes WHERE pid='$_G[gp_pid]'");
			if($post['first']) {
				$threadrate = @intval(@($post['rate'] + $rate) / abs($post['rate'] + $rate));
				DB::query("UPDATE ".DB::table('forum_thread')." SET rate='$threadrate' WHERE tid='$_G[tid]'");
			}

		}

		showmessage('thread_rate_removesucceed', dreferer());

	}

} elseif($_G['gp_action'] == 'viewratings' && $_G['gp_pid']) {
	$posttable = getposttablebytid($_G['tid']);
	$queryr = DB::query("SELECT * FROM ".DB::table('forum_ratelog')." WHERE pid='$_G[gp_pid]' ORDER BY dateline DESC");
	$queryp = DB::query("SELECT p.* ".($_G['setting']['bannedmessages'] ? ", m.groupid " : '').
		" FROM ".DB::table($posttable)." p ".
		($_G['setting']['bannedmessages'] ? "LEFT JOIN ".DB::table('common_member')." m ON m.uid=p.authorid" : '').
		" WHERE p.pid='$_G[gp_pid]' AND p.invisible='0'");

	if(!(DB::num_rows($queryr)) || !(DB::num_rows($queryp))) {
		showmessage('thread_rate_log_nonexistence');
	}

	$post = DB::fetch($queryp);
	if($post['tid'] != $thread['tid']) {
		showmessage('undefined_action', NULL);
	}

	$loglist = $logcount = array();
	while($log = DB::fetch($queryr)) {
		$logcount[$log['extcredits']] += $log['score'];
		$log['dateline'] = dgmdate($log['dateline'], 'u');
		$log['score'] = $log['score'] > 0 ? '+'.$log['score'] : $log['score'];
		$log['reason'] = dhtmlspecialchars($log['reason']);
		$loglist[] = $log;
	}

	include template('forum/rate_view');

} elseif($_G['gp_action'] == 'viewwarning' && $_G['gp_uid']) {

	if(!($warnuser = DB::result_first("SELECT username FROM ".DB::table('common_member')." WHERE uid='$_G[gp_uid]'"))) {
		showmessage('undefined_action', NULL);
	}

	$query = DB::query("SELECT * FROM ".DB::table('forum_warning')." WHERE authorid='$_G[gp_uid]'");

	if(!($warnnum = DB::num_rows($query))) {
		showmessage('thread_warning_nonexistence');
	}

	$warning = array();
	while($warning = DB::fetch($query)) {
		$warning['dateline'] = dgmdate($warning['dateline'], 'u');
		$warning['reason'] = dhtmlspecialchars($warning['reason']);
		$warnings[] = $warning;
	}

	include template('forum/warn_view');

} elseif($_G['gp_action'] == 'pay') {

	if(!isset($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]])) {
		showmessage('credits_transaction_disabled');
	} elseif($thread['price'] <= 0 || $thread['special'] <> 0) {
		showmessage('undefined_action', NULL);
	} elseif(!$_G['uid']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	}

	if(($balance = getuserprofile('extcredits'.$_G['setting']['creditstransextra'][1]) - $thread['price']) < ($minbalance = 0)) {
		showmessage('credits_balance_insufficient', '', array('title' => $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['title'], 'minbalance' => $thread['price']));
	}

	if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_credit_log')." WHERE relatedid='$_G[tid]' AND operation='BTC' AND uid='$_G[uid]'")) {
		showmessage('credits_buy_thread', 'forum.php?mod=viewthread&tid='.$_G['tid'].($_G['gp_from'] ? '&from='.$_G['gp_from'] : ''));
	}

	$thread['netprice'] = floor($thread['price'] * (1 - $_G['setting']['creditstax']));

	if(!submitcheck('paysubmit')) {

		include template('forum/pay');

	} else {

		$updateauthor = true;
		if($_G['setting']['maxincperthread'] > 0) {
			$extcredit = 'extcredits'.$_G['setting']['creditstransextra'][1];
			if((DB::result_first("SELECT SUM($extcredit) FROM ".DB::table('common_credit_log')." WHERE uid='$thread[authorid]' AND operation='STC' AND relatedid='$_G[tid]'")) > $_G['setting']['maxincperthread']) {
				$updateauthor = false;
			}
		}
		if($updateauthor) {
			updatemembercount($thread['authorid'], array($_G['setting']['creditstransextra'][1] => $thread['netprice']), 1, 'STC', $_G['tid']);
		}
		updatemembercount($_G['uid'], array($_G['setting']['creditstransextra'][1] => -$thread['price']), 1, 'BTC', $_G['tid']);

		showmessage('thread_pay_succeed', "forum.php?mod=viewthread&tid=$_G[tid]".($_G['gp_from'] ? '&from='.$_G['gp_from'] : ''));

	}

} elseif($_G['gp_action'] == 'viewpayments') {
	$extcreditname = 'extcredits'.$_G['setting']['creditstransextra'][1];
	$loglist = array();
	$query = DB::query("SELECT l.*, m.username FROM ".DB::table('common_credit_log')." l
		LEFT JOIN ".DB::table('common_member')." m USING (uid)
		WHERE relatedid='$_G[tid]' AND operation='BTC' ORDER BY l.dateline");
	while($log = DB::fetch($query)) {
		$log['dateline'] = dgmdate($log['dateline'], 'u');
		$log[$extcreditname] = abs($log[$extcreditname]);
		$loglist[] = $log;
	}
	include template('forum/pay_view');

} elseif($_G['gp_action'] == 'viewthreadmod' && $_G['tid']) {

	$modactioncode = lang('forum/modaction');
	$loglist = array();
	$query = DB::query("SELECT * FROM ".DB::table('forum_threadmod')." WHERE tid='$_G[tid]' ORDER BY dateline DESC");

	while($log = DB::fetch($query)) {
		$log['dateline'] = dgmdate($log['dateline'], 'u');
		$log['expiration'] = !empty($log['expiration']) ? dgmdate($log['expiration'], 'd') : '';
		$log['status'] = empty($log['status']) ? 'style="text-decoration: line-through" disabled' : '';
		if(!$modactioncode[$log['action']] && preg_match('/S(\d\d)/', $log['action'], $a) || $log['action'] == 'SPA') {
			loadcache('stamps');
			if($log['action'] == 'SPA') {
				$log['action'] = 'SPA'.$log['stamp'];
				$stampid = $log['stamp'];
			} else {
				$stampid = intval($a[1]);
			}
			$modactioncode[$log['action']] = $modactioncode['SPA'].' '.$_G['cache']['stamps'][$stampid]['text'];
		}
		if($log['magicid']) {
			loadcache('magics');
			$log['magicname'] = $_G['cache']['magics'][$log['magicid']]['name'];
		}
		$loglist[] = $log;
	}

	if(empty($loglist)) {
		showmessage('threadmod_nonexistence');
	}

	include template('forum/viewthread_mod');

} elseif($_G['gp_action'] == 'bestanswer' && $_G['tid'] && $_G['gp_pid'] && submitcheck('bestanswersubmit')) {
	
	
	$forward = 'forum.php?mod=viewthread&tid='.$_G['tid'].($_G['gp_from'] ? '&from='.$_G['gp_from'] : '');
	$posttable = getposttablebytid($_G['tid']);
	$post = DB::fetch_first("SELECT authorid, first FROM ".DB::table($posttable)." WHERE pid='$_G[gp_pid]' and tid='$_G[tid]'");

	if(!($thread['special'] == 3 && $post && ($_G['forum']['ismoderator'] || $thread['authorid'] == $_G['uid']) && $post['authorid'] != $thread['authorid'] && $post['first'] == 0 && $_G['uid'] != $post['authorid'])) {
		showmessage('reward_cant_operate');
	} elseif($post['authorid'] == $thread['authorid']) {
		showmessage('reward_cant_self');
	} elseif($thread['price'] < 0) {
		showmessage('reward_repeat_selection');
	}
	/*
	 * added by fumz，2010-7-30 17:04:25
	 * 更新专区经验值
	 */
	//begin
	require_once libfile("function/group");
	if($thread['special'] == 3&&$thread['price']>0){
		group_add_empirical($post['authorid'],$thread['fid'],$thread['price']);//更新用户在专区的经验值
	}
	require_once libfile("function/credit");
	//exit("credit_create_credit_log(".$post['authorid'].", 'bestquestion',".$post['pid'].")");	
	credit_create_credit_log($post['authorid'], 'bestquestion',$_G['gp_pid']);//调用积分接口
	//end
	
	updatemembercount($post['authorid'], array($_G['setting']['creditstransextra'][2] => $thread['price']), 1, 'RAC', $_G['tid']);
	$thread['price'] = '-'.$thread['price'];
	//DB::query("UPDATE ".DB::table('forum_thread')." SET price='$thread[price]' WHERE tid='$_G[tid]'");
	DB::query("UPDATE ".DB::table('forum_thread')." SET price='$thread[price]',question_status='Y' WHERE tid='$_G[tid]'");//modified by fumz ,2010-8-2 13:44:28,修改提问状态的
	DB::query("UPDATE ".DB::table($posttable)." SET dateline=$thread[dateline]+1 WHERE pid='$_G[gp_pid]'");

	$thread['dateline'] = dgmdate($thread['dateline']);
	if($_G['uid'] != $thread['authorid']) {
		notification_add($thread['authorid'], 'reward', 'reward_question', array(
			'tid' => $thread['tid'],
			'subject' => $thread['subject'],
		));
	}
	notification_add($post['authorid'], 'reward', 'reward_bestanswer', array(
		'tid' => $thread['tid'],
		'subject' => $thread['subject'],
	));

	
	
	showmessage('reward_completion', $forward);

} elseif($_G['gp_action'] == 'activityapplies') {

	if(!$_G['uid']) {
		showmessage('undefined_action', NULL);
	}

	if(submitcheck('activitysubmit')) {
		$expiration = DB::result_first("SELECT expiration FROM ".DB::table('forum_activity')." WHERE tid='$_G[tid]'");
		if($expiration && $expiration < TIMESTAMP) {
			showmessage('activity_stop', NULL, array(), array('login' => 1));
		}

		$query = DB::query("SELECT applyid FROM ".DB::table('forum_activityapply')." WHERE tid='$_G[tid]' and username='$_G[username]'");
		if(DB::num_rows($query)) {
			showmessage('activity_repeat_apply', NULL, array(), array('login' => 1));
		}
		$payvalue = intval($_G['gp_payvalue']);
		$payment = $_G['gp_payment'] ? $payvalue : -1;
		$message = cutstr(dhtmlspecialchars($_G['gp_message']), 200);
		$contact = cutstr(dhtmlspecialchars($_G['gp_contact']), 200);
		$verified = $thread['authorid'] == $_G['uid'] ? 1 : 0;

		DB::query("INSERT INTO ".DB::table('forum_activityapply')." (tid, username, uid, message, verified, dateline, payment, contact)
			VALUES ('$_G[tid]', '$_G[username]', '$_G[uid]', '$message', '$verified', '$_G[timestamp]', '$payment', '$contact')");

		$applynumber = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_activityapply')." WHERE tid='$_G[tid]' AND verified=1");
		DB::update('forum_activity', array('applynumber' => $applynumber), "tid='$_G[tid]'");

		if($thread['authorid'] != $_G['uid']) {
			notification_add($thread['authorid'], 'activity', 'activity_notice', array(
				'tid' => $_G['tid'],
				'subject' => $thread['subject'],
			));
			$space = array();
			space_merge($space, 'field_home');

			if(!empty($space['privacy']['feed']['newreply'])) {
				$feed['icon'] = 'activity';
				$feed['title_template'] = 'feed_reply_activity_title';
				$feed['title_data'] = array(
					'subject' => "<a href=\"$_G[siteurl]forum.php?mod=viewthread&tid=$_G[tid]\">$thread[subject]</a>",
					'hash_data' => "tid{$_G[tid]}"
				);
				$feed['id'] = $_G['tid'];
				$feed['idtype'] = 'tid';
				postfeed($feed);
			}
		}

		if($verified) {
			dheader("location: forum.php?mod=viewthread&tid=$_G[tid]".($_G['gp_from'] ? '&from='.$_G['gp_from'] : ''));
		} else {
			showmessage('activity_completion', "forum.php?mod=viewthread&tid=$_G[tid]".($_G['gp_from'] ? '&from='.$_G['gp_from'] : ''));
		}

	}

} elseif($_G['gp_action'] == 'activityapplylist') {

	$isactivitymaster = $thread['authorid'] == $_G['uid'] || $_G['group']['alloweditactivity'];
	$activity = DB::fetch_first("SELECT * FROM ".DB::table('forum_activity')." WHERE tid='$_G[tid]'");
	if(!$activity || $thread['special'] != 4 || !$isactivitymaster) {
		showmessage('undefined_action');
	}

	if(!submitcheck('applylistsubmit')) {
		$sqlverified = $isactivitymaster ? '' : 'AND verified=1';

		if(!empty($_G['gp_uid']) && $isactivitymaster) {
			$sqlverified .= " AND uid='$_G[gp_uid]'";
		}

		$applylist = array();
		$query = DB::query("SELECT applyid, username, uid, message, verified, dateline, payment, contact FROM ".DB::table('forum_activityapply')." WHERE tid='$_G[tid]' $sqlverified ORDER BY dateline DESC");
		while($activityapplies = DB::fetch($query)) {
			$activityapplies['dateline'] = dgmdate($activityapplies['dateline'], 'u');
			$applylist[] = $activityapplies;
		}

		$activity['starttimefrom'] = dgmdate($activity['starttimefrom'], 'u');
		$activity['starttimeto'] = $activity['starttimeto'] ? dgmdate($activity['starttimeto'], 'u') : 0;
		$activity['expiration'] = $activity['expiration'] ? dgmdate($activity['expiration'], 'u') : 0;

		include template('forum/activity_applylist');
	} else {
		if(empty($_G['gp_applyidarray'])) {
			showmessage('activity_choice_applicant');
		} else {
			$uidarray = $unverified = array();
			$ids = dimplode($_G['gp_applyidarray']);
			$query = DB::query("SELECT a.uid,a.verified FROM ".DB::table('forum_activityapply')." a RIGHT JOIN ".DB::table('common_member')." m USING(uid) WHERE a.applyid IN (".$ids.")");
			while($uid = DB::fetch($query)) {
				$uidarray[] = $uid['uid'];
				if(!$uid['verified']) {
					$unverified[] = $uid['uid'];
				}
			}
			$activity_subject = $thread['subject'];
			$reason = strip_tags($_G['gp_reason']);
			if($_G['gp_operation'] == 'delete') {
				if($uidarray) {
					DB::query("DELETE FROM ".DB::table('forum_activityapply')." WHERE applyid IN (".$ids.")", 'UNBUFFERED');

					foreach($uidarray as $uid) {
						notification_add($uid, 'activity', 'activity_delete', array(
							'tid' => $_G['tid'],
							'subject' => $activity_subject,
							'reason' => stripslashes($reason),
						));
					}
				}
				$applynumber = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_activityapply')." WHERE tid='$_G[tid]' AND verified=1");
				DB::update('forum_activity', array('applynumber' => $applynumber), "tid='$_G[tid]'");

				showmessage('activity_delete_completion', "forum.php?mod=viewthread&tid=$_G[tid]&do=viewapplylist".($_G['gp_from'] ? '&from='.$_G['gp_from'] : ''));
			} else {
				if($unverified) {
					DB::query("UPDATE ".DB::table('forum_activityapply')." SET verified=1 WHERE applyid IN (".$ids.")", 'UNBUFFERED');

					foreach($unverified as $uid) {
						notification_add($uid, 'activity', 'activity_apply', array(
							'tid' => $_G['tid'],
							'subject' => $activity_subject,
							'reason' => stripslashes($reason),
						));
					}
				}
				$applynumber = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_activityapply')." WHERE tid='$_G[tid]' AND verified=1");
				DB::update('forum_activity', array('applynumber' => $applynumber), "tid='$_G[tid]'");

				showmessage('activity_auditing_completion', "forum.php?mod=viewthread&tid=$_G[tid]&do=viewapplylist".($_G['gp_from'] ? '&from='.$_G['gp_from'] : ''));
			}
		}
	}

} elseif($_G['gp_action'] == 'activityexport') {
	$posttable = getposttablebytid($_G['tid']);
	$activity = DB::fetch_first("SELECT a.*, p.message FROM ".DB::table('forum_activity')." a LEFT JOIN ".DB::table($posttable)." p ON p.tid=a.tid AND p.first='1' WHERE a.tid='$_G[tid]'");
	if(!$activity || $thread['special'] != 4 || $thread['authorid'] != $_G['uid'] && !$_G['group']['alloweditactivity']) {
		showmessage('undefined_action');
	}

	$activity['starttimefrom'] = dgmdate($activity['starttimefrom'], 'dt');
	$activity['starttimeto'] = $activity['starttimeto'] ? dgmdate($activity['starttimeto'], 'dt') : 0;
	$activity['expiration'] = $activity['expiration'] ? dgmdate($activity['expiration'], 'dt') : 0;

	$applynumbers = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_activityapply')." WHERE tid='$_G[tid]' AND verified=1");

	$applylist = array();
	$query = DB::query("SELECT applyid, username, uid, message, verified, dateline, payment, contact FROM ".DB::table('forum_activityapply')." WHERE tid='$_G[tid]' ORDER BY dateline DESC");
	while($apply = DB::fetch($query)) {
		$apply['dateline'] = dgmdate($apply['dateline'], 'dt');
		$applylist[] = $apply;
	}

	$filename = "activity_{$_G[tid]}.csv";

	ob_end_clean();
	header('Content-Encoding: none');
	header('Content-Type: '.('application/octet-stream'));
	header('Content-Disposition: '.('attachment; ').'filename='.$filename);
	header('Pragma: no-cache');
	header('Expires: 0');
	include template('forum/activity_export');

} elseif($_G['gp_action'] == 'tradeorder') {

	$trades = array();
	$query=DB::query("SELECT * FROM ".DB::table('forum_trade')." WHERE tid='$_G[tid]' ORDER BY displayorder");

	if($thread['authorid'] != $_G['uid'] && !$_G['group']['allowedittrade']) {
		showmessage('undefined_action', NULL);
	}

	if(!submitcheck('tradesubmit')) {

		$stickcount = 0;$trades = $tradesstick = array();
		while($trade = DB::fetch($query)) {
			$stickcount = $trade['displayorder'] > 0 ? $stickcount + 1 : $stickcount;
			$trade['displayorderview'] = $trade['displayorder'] < 0 ? 128 + $trade['displayorder'] : $trade['displayorder'];
			if($trade['expiration']) {
				$trade['expiration'] = ($trade['expiration'] - TIMESTAMP) / 86400;
				if($trade['expiration'] > 0) {
					$trade['expirationhour'] = floor(($trade['expiration'] - floor($trade['expiration'])) * 24);
					$trade['expiration'] = floor($trade['expiration']);
				} else {
					$trade['expiration'] = -1;
				}
			}
			if($trade['displayorder'] < 0) {
				$trades[] = $trade;
			} else {
				$tradesstick[] = $trade;
			}
		}
		$trades = array_merge($tradesstick, $trades);
		include template('forum/trade_displayorder');

	} else {

		$count = 0;
		while($trade = DB::fetch($query)) {
			$displayordernew = abs(intval($_G['gp_displayorder'][$trade['pid']]));
			$displayordernew = $displayordernew > 128 ? 0 : $displayordernew;
			if($_G['gp_stick'][$trade['pid']]) {
				$count++;
				$displayordernew = $displayordernew == 0 ? 1 : $displayordernew;
			}
			if(!$_G['gp_stick'][$trade['pid']] || $displayordernew > 0 && $_G['group']['tradestick'] < $count) {
				$displayordernew = -1 * (128 - $displayordernew);
			}
			DB::query("UPDATE ".DB::table('forum_trade')." SET displayorder='".$displayordernew."' WHERE tid='$_G[tid]' AND pid='$trade[pid]'");
		}

		showmessage('trade_displayorder_updated', "forum.php?mod=viewthread&tid=$_G[tid]".($_G['gp_from'] ? '&from='.$_G['gp_from'] : ''));

	}

} elseif($_G['gp_action'] == 'debatevote') {

	if(!empty($thread['closed'])) {
		showmessage('thread_poll_closed');
	}

	if(!$_G['uid']) {
		showmessage('debate_poll_nopermission');
	}

	$isfirst = empty($_G['gp_pid']) ? TRUE : FALSE;

	$debate = DB::fetch_first("SELECT uid, endtime, affirmvoterids, negavoterids FROM ".DB::table('forum_debate')." WHERE tid='$_G[tid]'");

	if(empty($debate)) {
		showmessage('debate_nofound');
	}

	if($isfirst) {
		$stand = intval($_G['gp_stand']);

		if($stand == 1 || $stand == 2) {
			if(strpos("\t".$debate['affirmvoterids'], "\t{$_G['uid']}\t") !== FALSE || strpos("\t".$debate['negavoterids'], "\t{$_G['uid']}\t") !== FALSE) {
				showmessage('debate_poll_voted');
			} elseif($debate['uid'] == $_G['uid']) {
				showmessage('debate_poll_myself');
			} elseif($debate['endtime'] && $debate['endtime'] < TIMESTAMP) {
				showmessage('debate_poll_end');
			}
		}
		if($stand == 1) {
			DB::query("UPDATE ".DB::table('forum_debate')." SET affirmvotes=affirmvotes+1 WHERE tid='$_G[tid]'");
			DB::query("UPDATE ".DB::table('forum_debate')." SET affirmvoterids=CONCAT(affirmvoterids, '$_G[uid]\t') WHERE tid='$_G[tid]'");
		} elseif($stand == 2) {
			DB::query("UPDATE ".DB::table('forum_debate')." SET negavotes=negavotes+1 WHERE tid='$_G[tid]'");
			DB::query("UPDATE ".DB::table('forum_debate')." SET negavoterids=CONCAT(negavoterids, '$_G[uid]\t') WHERE tid='$_G[tid]'");
		}

		showmessage('debate_poll_succeed');
	}

	$debatepost = DB::fetch_first("SELECT stand, voterids, uid FROM ".DB::table('forum_debatepost')." WHERE pid='$_G[gp_pid]' AND tid='$_G[tid]'");
	if(empty($debatepost)) {
		showmessage('debate_nofound');
	}
	$debate = array_merge($debate, $debatepost);
	unset($debatepost);

	if($debate['uid'] == $_G['uid']) {
		showmessage('debate_poll_myself', "forum.php?mod=viewthread&tid=$_G[tid]".($_G['gp_from'] ? '&from='.$_G['gp_from'] : ''), array(), array('showmsg' => 1));
	} elseif(strpos("\t".$debate['voterids'], "\t$_G[uid]\t") !== FALSE) {
		showmessage('debate_poll_voted', "forum.php?mod=viewthread&tid=$_G[tid]".($_G['gp_from'] ? '&from='.$_G['gp_from'] : ''), array(), array('showmsg' => 1));
	} elseif($debate['endtime'] && $debate['endtime'] < TIMESTAMP) {
		showmessage('debate_poll_end', "forum.php?mod=viewthread&tid=$_G[tid]".($_G['gp_from'] ? '&from='.$_G['gp_from'] : ''), array(), array('showmsg' => 1));
	}

	DB::query("UPDATE ".DB::table('forum_debatepost')." SET voters=voters+1, voterids=CONCAT(voterids, '$_G[uid]\t') WHERE pid='$_G[gp_pid]'");

	showmessage('debate_poll_succeed', "forum.php?mod=viewthread&tid=$_G[tid]".($_G['gp_from'] ? '&from='.$_G['gp_from'] : ''), array(), array('showmsg' => 1));

} elseif($_G['gp_action'] == 'debateumpire') {

	$debate = DB::fetch_first("SELECT * FROM ".DB::table('forum_debate')." WHERE tid='$_G[tid]'");

	if(empty($debate)) {
		showmessage('debate_nofound');
	}elseif(!empty($thread['closed']) && TIMESTAMP - $debate['endtime'] > 3600) {
		showmessage('debate_umpire_edit_invalid');
	} elseif($_G['member']['username'] != $debate['umpire']) {
		showmessage('debate_umpire_nopermission');
	}

	$debate = array_merge($debate, $thread);

	if(!submitcheck('umpiresubmit')) {
		$query = DB::query("SELECT SUM(dp.voters) as voters, dp.stand, m.uid, m.username FROM ".DB::table('forum_debatepost')." dp
			LEFT JOIN ".DB::table('common_member')." m ON m.uid=dp.uid
			WHERE dp.tid='$_G[tid]' AND dp.stand<>0
			GROUP BY m.uid
			ORDER BY voters DESC
			LIMIT 30");
		$candidate = $candidates = array();
		while($candidate = DB::fetch($query)) {
			$candidate['username'] = dhtmlspecialchars($candidate['username']);
			$candidates[$candidate['username']] = $candidate;
		}
		$winnerchecked = array($debate['winner'] => ' checked="checked"');

		list($debate['bestdebater']) = preg_split("/\s/", $debate['bestdebater']);

		include template('forum/debate_umpire');
	} else {
		if(empty($_G['gp_bestdebater'])) {
			showmessage('debate_umpire_nofound_bestdebater');
		} elseif(empty($_G['gp_winner'])) {
			showmessage('debate_umpire_nofound_winner');
		} elseif(empty($_G['gp_umpirepoint'])) {
			showmessage('debate_umpire_nofound_point');
		}
		$bestdebateruid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$_G[gp_bestdebater]' LIMIT 1");
		if(!$bestdebateruid) {
			showmessage('debate_umpire_bestdebater_invalid');
		}
		if(!$bestdebaterstand = DB::result_first("SELECT stand FROM ".DB::table('forum_debatepost')." WHERE tid='$_G[tid]' AND uid='$bestdebateruid' AND stand>'0' AND uid<>'$debate[uid]' AND uid<>'$_G[uid]' LIMIT 1")) {
			showmessage('debate_umpire_bestdebater_invalid');
		}
		$arr = DB::fetch_first("SELECT SUM(voters) as voters, COUNT(*) as replies FROM ".DB::table('forum_debatepost')." WHERE tid='$_G[tid]' AND uid='$bestdebateruid'");
		$bestdebatervoters = $arr['voters'];
		$bestdebaterreplies = $arr['replies'];

		$umpirepoint = dhtmlspecialchars($_G['gp_umpirepoint']);
		$bestdebater = dhtmlspecialchars($_G['gp_bestdebater']);
		$winner = intval($_G['gp_winner']);
		DB::query("UPDATE ".DB::table('forum_thread')." SET closed='1' WHERE tid='$_G[tid]'");
		DB::query("UPDATE ".DB::table('forum_debate')." SET umpirepoint='$umpirepoint', winner='$winner', bestdebater='$bestdebater\t$bestdebateruid\t$bestdebaterstand\t$bestdebatervoters\t$bestdebaterreplies', endtime='$_G[timestamp]' WHERE tid='$_G[tid]'");
		showmessage('debate_umpire_comment_succeed', 'forum.php?mod=viewthread&tid='.$_G['tid'].($_G['gp_from'] ? '&from='.$_G['gp_from'] : ''));
	}

} elseif($_G['gp_action'] == 'recommend') {
	
	dsetcookie('discuz_recommend', '', -1, 0);
	if(!$_G['setting']['recommendthread']['status'] || !$_G['group']['allowrecommend']) {
		showmessage('undefined_action', NULL);
	}

	if(DB::fetch_first("SELECT * FROM ".DB::table('forum_memberrecommend')." WHERE recommenduid='$_G[uid]' AND tid='$_G[tid]'")) {
		showmessage('recommend_duplicate', '', array(), array('msgtype' => 3));
	}

	$recommendcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_memberrecommend')." WHERE recommenduid='$_G[uid]' AND dateline>$_G[timestamp]-86400");
	if($_G['setting']['recommendthread']['daycount'] && $recommendcount >= $_G['setting']['recommendthread']['daycount']) {
		showmessage('recommend_outoftimes', '', array(), array('msgtype' => 3));
	}

	if($thread['authorid'] == $_G['uid'] && !$_G['setting']['recommendthread']['ownthread']) {
		showmessage('recommend_self_disallow', '', array(), array('msgtype' => 3));
	}
	$_G['group']['allowrecommend'] = intval($_G['gp_do'] == 'add' ? $_G['group']['allowrecommend'] : -$_G['group']['allowrecommend']);
	if($_G['gp_do'] == 'add') {
		$heatadd = 'recommend_add=recommend_add+1';
		if($thread['special']==1){
			notification_add($thread[authorid],"poll",'[投票]'.$_G[cookie][realusername].'对您“'.$_G[forum][name].'”专区的“'.$thread[subject].'”的投票进行了支持表态', array(), 1);
		}elseif($thread['special']==3){
			notification_add($thread[authorid],"reward",'[提问]'.$_G[cookie][realusername].'对您“'.$_G[forum][name].'”专区的“'.$thread[subject].'”的提问进行了支持表态', array(), 1);
		}elseif($thread['special']==0){
			notification_add($thread[authorid],"thread",'[话题]'.$_G[cookie][realusername].'对您“'.$_G[forum][name].'”专区的“'.$thread[subject].'”的话题进行了支持表态', array(), 1);
		}
	} else {
		$heatadd = 'recommend_sub=recommend_sub+1';
			if($thread['special']==1){
			notification_add($thread[authorid],"poll",'[投票]'.$_G[cookie][realusername].'对您“'.$_G[forum][name].'”专区的“'.$thread[subject].'”的投票进行了反对表态', array(), 1);
		}elseif($thread['special']==3){
			notification_add($thread[authorid],"reward",'[提问]'.$_G[cookie][realusername].'对您“'.$_G[forum][name].'”专区的“'.$thread[subject].'”的提问进行了反对表态', array(), 1);
		}elseif($thread['special']==0){
			notification_add($thread[authorid],"thread",'[话题]'.$_G[cookie][realusername].'对您“'.$_G[forum][name].'”专区的“'.$thread[subject].'”的话题进行了反对表态', array(), 1);
		}
	}
	
	DB::query("UPDATE ".DB::table('forum_thread')." SET heats=heats+'".(abs($_G['group']['allowrecommend']) * $_G['heatthread']['recommend'])."', recommends=recommends+'{$_G[group][allowrecommend]}', $heatadd WHERE tid='$_G[tid]'");
	DB::query("INSERT INTO ".DB::table('forum_memberrecommend')." (tid, recommenduid, dateline) VALUES ('$_G[tid]', '$_G[uid]', '$_G[timestamp]')"); 
    //DB::query("INSERT INTO pre_forum_memberrecommend(tid,recommenduid,dateline) VALUES ($_G['tid'],60000039,$_G['timestamp'])");
	//积分 经验值
	require_once libfile('function/group');
	group_add_empirical_by_setting($_G['uid'], $thread['fid'], 'express_someting', $thread['tid']);
	
	require_once libfile('function/credit');
	credit_create_credit_log($_G['uid'], 'click', $thread['tid']);


	dsetcookie('recommend', 1, 43200);
	$_G['group']['allowrecommend'] = $_G['group']['allowrecommend'] > 0 ? '+'.$_G['group']['allowrecommend'] : $_G['group']['allowrecommend'];
	if($_G['setting']['recommendthread']['daycount']) {
		$daycount = $_G['setting']['recommendthread']['daycount'] - $recommendcount;
		showmessage('recommend_daycount_succed', '', array('allowrecommend' => $_G['group']['allowrecommend'], 'daycount' => $daycount), array('msgtype' => 3));
	} else {
		showmessage('recommend_succed', '', array('allowrecommend' => $_G['group']['allowrecommend']), array('msgtype' => 3));
	}

} elseif($_G['gp_action'] == 'removeindexheats') {

	if($_G['adminid'] != 1) {
		showmessage('undefined_action', NULL);
	}
	DB::query("UPDATE ".DB::table('forum_thread')." SET heats=0 WHERE tid='$_G[tid]'");
	require_once libfile('function/cache');
	updatecache('heats');
	dheader('Location: '.dreferer());

} elseif($_G['gp_action'] == 'protectsort') {

	if($_G['gp_sortvalue']) {
		makevaluepic($_G['gp_sortvalue']);
	} else {
		$tid = $_G['gp_tid'];
		$optionid = $_G['gp_optionid'];
		include template('common/header_ajax');
		echo DB::result_first('SELECT value FROM '.DB::table('forum_typeoptionvar')." WHERE tid='$tid' AND optionid='$optionid'");
		include template('common/footer_ajax');
	}

}

function makevaluepic($value) {
	Header("Content-type:image/png");
	$im = @imagecreate(130, 25);
	$background_color = imagecolorallocate($im, 255, 255, 255);
	$text_color = imagecolorallocate($im, 23, 14, 91);
	imagestring($im, 4, 0, 4, $value, $text_color);
	imagepng($im);
	imagedestroy($im);
}

function getratelist($raterange) {
	global $_G;
	$maxratetoday = getratingleft($raterange);

	$ratelist = array();
	foreach($raterange as $id => $rating) {
		if(isset($_G['setting']['extcredits'][$id])) {
			$ratelist[$id] = '';
			$rating['max'] = $rating['max'] < $maxratetoday[$id] ? $rating['max'] : $maxratetoday[$id];
			$rating['min'] = -$rating['min'] < $maxratetoday[$id] ? $rating['min'] : -$maxratetoday[$id];
			$offset = abs(ceil(($rating['max'] - $rating['min']) / 10));
			if($rating['max'] > $rating['min']) {
				for($vote = $rating['max']; $vote >= $rating['min']; $vote -= $offset) {
					$ratelist[$id] .= $vote ? '<li>'.($vote > 0 ? '+'.$vote : $vote).'</li>' : '';
				}
			}
		}
	}
	return $ratelist;
}

function getratingleft($raterange) {
	global $_G;
	$maxratetoday = array();

	foreach($raterange as $id => $rating) {
		$maxratetoday[$id] = $rating['mrpd'];
	}

	$query = DB::query("SELECT extcredits, SUM(ABS(score)) AS todayrate FROM ".DB::table('forum_ratelog')."
		WHERE uid='$_G[uid]' AND dateline>=$_G[timestamp]-86400
		GROUP BY extcredits");
	while($rate = DB::fetch($query)) {
		$maxratetoday[$rate['extcredits']] = $raterange[$rate['extcredits']]['mrpd'] - $rate['todayrate'];
	}
	return $maxratetoday;
}

?>