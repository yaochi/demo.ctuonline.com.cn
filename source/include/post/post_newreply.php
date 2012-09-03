<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: post_newreply.php 11787 2010-06-13 02:36:25Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/forumlist');

$isfirstpost = 0;
$showthreadsorts = 0;

if($special == 5) {
	$debate = array_merge($thread, DB::fetch_first("SELECT * FROM ".DB::table('forum_debate')." WHERE tid='$_G[tid]'"));
	$standquery = DB::query("SELECT stand FROM ".DB::table('forum_debatepost')." WHERE tid='$_G[tid]' AND uid='$_G[uid]' AND stand<>'0' ORDER BY dateline LIMIT 1");
	$firststand = DB::result_first("SELECT stand FROM ".DB::table('forum_debatepost')." WHERE tid='$_G[tid]' AND uid='$_G[uid]' AND stand<>'0' ORDER BY dateline LIMIT 1");
	$stand = $firststand ? $firststand : intval($_G['gp_stand']);

	if($debate['endtime'] && $debate['endtime'] < TIMESTAMP) {
		showmessage('debate_end');
	}
}

if(!$_G['uid'] && !((!$_G['forum']['replyperm'] && $_G['group']['allowreply']) || ($_G['forum']['replyperm'] && forumperm($_G['forum']['replyperm'])))) {
	showmessage('replyperm_login_nopermission', NULL, array(), array('login' => 1));
} elseif(empty($_G['forum']['allowreply'])) {
	if(!$_G['forum']['replyperm'] && !$_G['group']['allowreply']) {
		showmessage('replyperm_none_nopermission', NULL, array(), array('login' => 1));
	} elseif($_G['forum']['replyperm'] && !forumperm($_G['forum']['replyperm'])) {
		showmessagenoperm('replyperm', $_G['forum']['fid']);
	}
} elseif($_G['forum']['allowreply'] == -1) {
	showmessage('post_forum_newreply_nopermission', NULL);
}

if(!$_G['uid'] && ($_G['setting']['need_avatar'] || $_G['setting']['need_email'] || $_G['setting']['need_friendnum'])) {
	showmessage('replyperm_login_nopermission', NULL, array(), array('login' => 1));
}

if(empty($thread)) {
	showmessage('thread_nonexistence');
} elseif($thread['price'] > 0 && $thread['special'] == 0 && !$_G['uid']) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

checklowerlimit('reply');

if($_G['setting']['commentnumber'] && !empty($_G['gp_comment'])) {

	$posttable = getposttablebytid($_G['tid']);
	if(!submitcheck('commentsubmit', 0, $seccodecheck, $secqaacheck)) {
		showmessage('undefined_action', NULL);
	}
	$post = DB::fetch_first('SELECT * FROM '.DB::table($posttable)." WHERE pid='$_G[gp_pid]'");
	if(!$post) {
		showmessage('undefined_action', NULL);
	}
	if($thread['closed'] && !$_G['forum']['ismoderator'] && !$thread['isgroup']) {
		showmessage('post_thread_closed');
	} elseif(!$thread['isgroup'] && $post_autoclose = checkautoclose($thread)) {
		showmessage($post_autoclose, '', array('autoclose' => $_G['forum']['autoclose']));
	} elseif(checkflood()) {
		showmessage('post_flood_ctrl', '', array('floodctrl' => $_G['setting']['floodctrl']));
	}
	$commentscore = '';
	if(!empty($_G['gp_commentitem']) && $post['authorid'] != $_G['uid']) {
		foreach($_G['gp_commentitem'] as $itemk => $itemv) {
			if($itemv !== '') {
				$commentscore .= strip_tags(trim($itemk)).': <i>'.intval($itemv).'</i> ';
			}
		}
	}
	$comment = cutstr(($commentscore ? $commentscore.'<br />' : '').censor(trim(strip_tags($_G['gp_message'])), '***'), 200, ' ');
	if(!$comment) {
		showmessage('post_sm_isnull');
	}
	DB::insert('forum_postcomment', array(
		'tid' => $post['tid'],
		'pid' => $post['pid'],
		'author' => $_G['username'],
		'authorid' => $_G['uid'],
		'dateline' => TIMESTAMP,
		'comment' => $comment,
		'score' => $commentscore ? 1 : 0,
	));
	$ppid=DB::insert_id();
	hook_create_resource($ppid,'group_ppcomment',$_G['fid']);
	DB::update($posttable, array('comment' => 1), "pid='$_G[gp_pid]'");
	updatepostcredits('+', $_G['uid'], 'reply', $_G['fid']);
	if($_G['uid'] != $post['authorid']) {
		notification_add($post['authorid'], 'pcomment', 'comment_add', array(
			'tid' => $_G['tid'],
			'pid' => $_G['gp_pid'],
			'subject' => $thread['subject'],
			'commentmsg' => cutstr(str_replace(array('[b]', '[/b]', '[/color]'), '', preg_replace("/\[color=([#\w]+?)\]/i", "", stripslashes($comment))), 200)
		));
	}
	$pcid = DB::result_first("SELECT id FROM ".DB::table('forum_postcomment')." WHERE pid='$_G[gp_pid]' AND authorid='0'");
	if($_G['gp_commentitem']) {
		$query = DB::query('SELECT comment FROM '.DB::table('forum_postcomment')." WHERE pid='$_G[gp_pid]' AND score='1'");
		$totalcomment = array();
		while($comment = DB::fetch($query)) {
			$comment['comment'] = addslashes($comment['comment']);
			if(strexists($comment['comment'], '<br />')) {
				if(preg_match_all("/([^:]+?):\s<i>(\d+)<\/i>/", $comment['comment'], $a)) {
					foreach($a[1] as $k => $itemk) {
						$totalcomment[trim($itemk)][] = $a[2][$k];
					}
				}
			}
		}
		$totalv = '';
		foreach($totalcomment as $itemk => $itemv) {
			$totalv .= strip_tags(trim($itemk)).': <i>'.(floatval(sprintf('%1.1f', array_sum($itemv) / count($itemv)))).'</i> ';
		}

		if($pcid) {
			DB::update('forum_postcomment', array('comment' => $totalv, 'dateline' => TIMESTAMP + 1), "id='$pcid'");
		} else {
			DB::insert('forum_postcomment', array(
				'tid' => $post['tid'],
				'pid' => $post['pid'],
				'author' => '',
				'authorid' => '0',
				'dateline' => TIMESTAMP + 1,
				'comment' => $totalv
			));
		}
	}
	DB::update('forum_postcomment', array('dateline' => TIMESTAMP + 1), "id='$pcid'");
	showmessage('comment_add_succeed', "forum.php?mod=viewthread&tid=$post[tid]&pid=$post[pid]&page=$_G[gp_page]&extra=$extra#pid$post[pid]", array('tid' => $post['tid'], 'pid' => $post['pid']));
}

if($special == 127) {
	$posttable = getposttablebytid($_G['tid']);
	$postinfo = DB::fetch_first("SELECT message FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND first='1'");
	$sppos = strrpos($postinfo['message'], chr(0).chr(0).chr(0));
	$specialextra = substr($postinfo['message'], $sppos + 3);
}

if(!submitcheck('replysubmit', 0, $seccodecheck, $secqaacheck)) {
	if($thread['special'] == 2 && ((!isset($_G['gp_addtrade']) || $thread['authorid'] != $_G['uid']) && !$tradenum = DB::result_first("SELECT count(*) FROM ".DB::table('forum_trade')." WHERE tid='$_G[tid]'"))) {
		showmessage('trade_newreply_nopermission', NULL);
	}
	$language = lang('forum/misc');
	$noticeauthor = $noticetrimstr = '';
	if(isset($_G['gp_repquote'])) {
		$posttable = getposttablebytid($_G['tid']);
		$thaquote = DB::fetch_first("SELECT *,tid, fid, author, authorid, first, message, useip, dateline, anonymous, status FROM ".DB::table($posttable)." WHERE pid='$_G[gp_repquote]' AND invisible='0'");
		if($thaquote['tid'] != $_G['tid']) {
			showmessage('undefined_action', NULL);
		}

		if(getstatus($thread['status'], 2) && $thaquote['authorid'] != $_G['uid'] && $_G['uid'] != $thread['authorid'] && $thaquote['first'] != 1 && !$_G['forum']['ismoderator']) {
			showmessage('undefined_action', NULL);
		}
		if(!($thread['price'] && !$thread['special'] && $thaquote['first'])) {
			$quotefid = $thaquote['fid'];
			$message = $thaquote['message'];

			if($_G['setting']['bannedmessages'] && $thaquote['authorid']) {
				$author = DB::fetch_first("SELECT groupid FROM ".DB::table('common_member')." WHERE uid='$thaquote[authorid]'");
				if(!$author['groupid'] || $author['groupid'] == 4 || $author['groupid'] == 5) {
					$message = $language['post_banned'];
				} elseif($thaquote['status'] & 1) {
					$message = $language['post_single_banned'];
				}
			}

			$time = dgmdate($thaquote['dateline']);
			$message = messagecutstr($message, 100);

			$thaquote['useip'] = substr($thaquote['useip'], 0, strrpos($thaquote['useip'], '.')).'.x';
			if($thaquote['author'] && $thaquote['anonymous']) {
				$thaquote['author'] = 'Anonymous';
			} elseif(!$thaquote['author']) {
				$thaquote['author'] = 'Guest from '.$thaquote['useip'];
			} else {
				$thaquote['author'] = $thaquote['author'];
			}

			$post_reply_quote = lang('forum/misc', 'post_reply_quote', array('author' =>user_get_user_name_by_username($thaquote['author']), 'time' => $time));
			$noticeauthormsg = htmlspecialchars($message);
			$message = "[quote]$message\n[size=2][color=#999999]{$post_reply_quote}[/color] [url=$_G[siteurl]forum.php?mod=redirect&goto=findpost&pid=$_G[gp_repquote]&ptid={$_G['tid']}][img]{$_G['siteurl']}static/image/common/back.gif[/img][/url][/size][/quote]\n\n\n";
			$message = discuzcode($message);
			$noticeauthor = htmlspecialchars('q|'.$thaquote['authorid'].'|'.$thaquote['author']);
			$noticetrimstr = htmlspecialchars($message);
		}

	} elseif(isset($_G['gp_reppost'])) {
		$posttable = getposttablebytid($_G['tid']);
		$thapost = DB::fetch_first("SELECT tid, author, authorid, useip, dateline, anonymous, status, message FROM ".DB::table($posttable)." WHERE pid='$_G[gp_reppost]' AND invisible='0'");
		if($thapost['tid'] != $_G['tid']) {
			showmessage('undefined_action', NULL);
		}

		$thapost['useip'] = substr($thapost['useip'], 0, strrpos($thapost['useip'], '.')).'.x';
		if($thapost['author'] && $thapost['anonymous']) {
			$thapost['author'] = '[i]Anonymous[/i]';
		} elseif(!$thapost['author']) {
			$thapost['author'] = '[i]Guest[/i] from '.$thapost['useip'];
		} else {
			$thapost['authorname']= user_get_user_name_by_username($thapost['author']);
			$thapost['author'] = '[i]'.user_get_user_name_by_username($thapost['author']).'[/i]';
		}
		$posttable = getposttablebytid($thapost['tid']);
		//$message = "[b]$language[post_reply] [url=$_G[siteurl]forum.php?mod=redirect&goto=findpost&pid=$_G[gp_reppost]&ptid=$thapost[tid]]$thapost[author] $language[post_thread][/url][/b]\n\n\n    ";
		$message="@$thapost[authorname]&nbsp;:&nbsp;";
		$noticeauthormsg = htmlspecialchars(messagecutstr($thapost['message'], 100));
		$noticeauthor = htmlspecialchars('r|'.$thapost['authorid'].'|'.$thapost['author']);
		$noticetrimstr = htmlspecialchars($message);
	}

	if(isset($_G['gp_addtrade']) && $thread['special'] == 2 && $_G['group']['allowposttrade'] && $thread['authorid'] == $_G['uid']) {
		$expiration_7days = date('Y-m-d', TIMESTAMP + 86400 * 7);
		$expiration_14days = date('Y-m-d', TIMESTAMP + 86400 * 14);
		$trade['expiration'] = $expiration_month = date('Y-m-d', mktime(0, 0, 0, date('m')+1, date('d'), date('Y')));
		$expiration_3months = date('Y-m-d', mktime(0, 0, 0, date('m')+3, date('d'), date('Y')));
		$expiration_halfyear = date('Y-m-d', mktime(0, 0, 0, date('m')+6, date('d'), date('Y')));
		$expiration_year = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')+1));
	}

	if($thread['replies'] <= $_G['ppp']) {
		$postlist = array();
		$posttable = getposttablebytid($_G['tid']);
		$query = DB::query("SELECT p.* ".($_G['setting']['bannedmessages'] ? ', m.groupid ' : '').
			"FROM ".DB::table($posttable)." p ".($_G['setting']['bannedmessages'] ? "LEFT JOIN ".DB::table('common_member')." m ON p.authorid=m.uid " : '').
			"WHERE p.tid='$_G[tid]' AND p.invisible='0' ".($thread['price'] > 0 && $thread['special'] == 0 ? 'AND p.first = 0' : '')." ORDER BY p.dateline DESC");
		while($post = DB::fetch($query)) {

			$post['dateline'] = dgmdate($post['dateline'], 'u');

			if($_G['setting']['bannedmessages'] && ($post['authorid'] && (!$post['groupid'] || $post['groupid'] == 4 || $post['groupid'] == 5))) {
				$post['message'] = $language['post_banned'];
			} elseif($post['status'] & 1) {
				$post['message'] = $language['post_single_banned'];
			} else {
				$post['message'] = preg_replace("/\[hide=?\d*\](.+?)\[\/hide\]/is", "[b]$language[post_hidden][/b]", $post['message']);
				$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], $post['htmlon'] & 1, $_G['forum']['allowsmilies'], $_G['forum']['allowbbcode'], $_G['forum']['allowimgcode'], $_G['forum']['allowhtml'], $_G['forum']['jammer']);
			}

			$postlist[] = $post;
		}
	}

	if($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) {
		$attachlist = getattach($pid);
		$attachs = $attachlist['attachs'];
		$imgattachs = $attachlist['imgattachs'];
		unset($attachlist);
	}

	getgpc('infloat') ? include template('forum/post_infloat') : include template('forum/post');

} else {
	if($subject == '' && $message == '' && $thread['special'] != 2) {
		showmessage('post_sm_isnull');
	} elseif($thread['closed'] && !$_G['forum']['ismoderator'] && !$thread['isgroup']) {
		showmessage('post_thread_closed');
	} elseif(!$thread['isgroup'] && $post_autoclose = checkautoclose($thread)) {
		showmessage($post_autoclose, '', array('autoclose' => $_G['forum']['autoclose']));
	} elseif($post_invalid = checkpost($subject, $message, $special == 2 && $_G['group']['allowposttrade'])) {
		showmessage($post_invalid, '', array('minpostsize' => $_G['setting']['minpostsize'], 'maxpostsize' => $_G['setting']['maxpostsize']));
	} elseif(checkflood()) {
		showmessage('post_flood_ctrl', '', array('floodctrl' => $_G['setting']['floodctrl']));
	}
	if(!empty($_G['gp_trade']) && $thread['special'] == 2 && $_G['group']['allowposttrade']) {

		$item_price = floatval($_G['gp_item_price']);
		$item_credit = intval($_G['gp_item_credit']);
		if(!trim($_G['gp_item_name'])) {
			showmessage('trade_please_name');
		} elseif($_G['group']['maxtradeprice'] && $item_price > 0 && ($_G['group']['mintradeprice'] > $item_price || $_G['group']['maxtradeprice'] < $item_price)) {
			showmessage('trade_price_between', '', array('mintradeprice' => $_G['group']['mintradeprice'], 'maxtradeprice' => $_G['group']['maxtradeprice']));
		} elseif($_G['group']['maxtradeprice'] && $item_credit > 0 && ($_G['group']['mintradeprice'] > $item_credit || $_G['group']['maxtradeprice'] < $item_credit)) {
			showmessage('trade_credit_between', '', array('mintradeprice' => $_G['group']['mintradeprice'], 'maxtradeprice' => $_G['group']['maxtradeprice']));
		} elseif(!$_G['group']['maxtradeprice'] && $item_price > 0 && $_G['group']['mintradeprice'] > $item_price) {
			showmessage('trade_price_more_than', '', array('mintradeprice' => $_G['group']['mintradeprice']));
		} elseif(!$_G['group']['maxtradeprice'] && $item_credit > 0 && $_G['group']['mintradeprice'] > $item_credit) {
			showmessage('trade_credit_more_than', '', array('mintradeprice' => $_G['group']['mintradeprice']));
		} elseif($item_price <= 0 && $item_credit <= 0) {
			showmessage('trade_pricecredit_need');
		} elseif($_G['gp_item_number'] < 1) {
			showmessage('tread_please_number');
		}

	}


	$attentionon = empty($_G['gp_attention_add']) ? 0 : 1;
	$attentionoff = empty($attention_remove) ? 0 : 1;
	
	$anonymity = $_G["gp_anonymity"];
	$atjson=$_G["gp_atjson"];
	if(!$anonymity){
		$anonymity=$_G[member][repeatsstatus];
	}

	if($thread['lastposter'] != $_G['member']['username']) {
		$posttable = getposttablebytid($_G['tid']);
		$userreplies = DB::result_first("SELECT COUNT(*) FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND first='0' AND authorid='$_G[uid]'");
		$thread['heats'] += round($_G['setting']['heatthread']['reply'] * pow(0.8, $userreplies));
		$heatbefore = $thread['heats'];
		DB::query("UPDATE ".DB::table('forum_thread')." SET heats='$thread[heats]' WHERE tid='$_G[tid]'", 'UNBUFFERED');
	}

	$bbcodeoff = checkbbcodes($message, !empty($_G['gp_bbcodeoff']));
	$smileyoff = checksmilies($message, !empty($_G['gp_smileyoff']));
	$parseurloff = !empty($_G['gp_parseurloff']);
	$htmlon = $_G['group']['allowhtml'] && !empty($_G['gp_htmlon']) ? 1 : 0;
	$usesig = !empty($_G['gp_usesig']) ? 1 : 0;

	$isanonymous = $_G['group']['allowanonymous'] && !empty($_G['gp_isanonymous'])? 1 : 0;
	$author = empty($isanonymous) ? $_G['username'] : '';
	$feedid=DB::result_first("select feedid from ".DB::TABLE("home_feed")." where idtype='tid' and id=".$_G['tid']);
	$pinvisible = $modnewreplies ? -2 : 0;
	$message = preg_replace('/\[attachimg\](\d+)\[\/attachimg\]/is', '[attach]\1[/attach]', $message);
	if($atjson){
		$atarr=parseat1($message,$_G[uid],$atjson);
	}else{
		$atarr=parseat($message,$_G[uid]);
	}
	
	$message=$atarr[message];
	$pid = insertpost(array(
		'fid' => $_G['fid'],
		'tid' => $_G['tid'],
		'first' => '0',
		'author' => $_G['username'],
		'authorid' => $_G['uid'],
		'subject' => $subject,
		'dateline' => $_G['timestamp'],
		'message' => $message,
		'useip' => $_G['clientip'],
		'invisible' => $pinvisible,
		'anonymous' => $isanonymous,
		'usesig' => $usesig,
		'htmlon' => $htmlon,
		'bbcodeoff' => $bbcodeoff,
		'smileyoff' => $smileyoff,
		'parseurloff' => $parseurloff,
		'attachment' => '0',
		'feedid'=>$feedid,
		'anonymity'=>$anonymity,
	));
	DB::query("update ".DB::TABLE("home_feed")." set commenttimes=commenttimes+1 where idtype='tid' and id=".$_G['tid']);
	if($_G[uid]!=$thread['authorid']){
		if($thread['special'] == 1){
		notification_add($thread['authorid'],"poll",'[投票]<a href="home.php?mod=space&uid='.$_G['member']['uid'].'">'.user_get_user_name_by_username($_G['member']['username']).'</a>评论了您<a href="forum.php?mod=group&fid='.$_G['fid'].'">“'.$_G[forum][name].'”</a>的<a href="forum.php?mod=viewthread&tid='.$thread[tid].'&fid='.$_G['fid'].'">“'.$thread[subject].'”</a>投票，赶快去看看吧！', array(), 1);
		}elseif($thread['special'] == 0){
		//add by qiaoyz,2011-3-24,EKSN-219 用户A在专区中评论用户B发表的话题不会在专区首页DIY的"专区动态"中显示出来
		require_once libfile('function/feed');
				$tite_data = array('actor' => '<a href="home.php?mod=space&uid='.$_G['uid'].'">'.user_get_user_name_by_username($_G['username']).'</a>', 'touser' => '<a href="home.php?mod=space&uid='.$thread['authorid'].'">'.user_get_user_name_by_username($thread['author']).'</a>', 'topic' => '<a href="forum.php?mod=viewthread&tid='.$thread[tid].'&fid='.$_G['fid'].'">“'.$thread[subject].'”</a>');
		feed_add('comment', 'feed_comment_topic', $tite_data, '', array(), '', array(), array(), '', '', '', 0, 0, '',$_G['uid'], user_get_user_name_by_username($_G['username']),$_G['fid']);
		notification_add($thread['authorid'],"thread",'[话题]<a href="home.php?mod=space&uid='.$_G['member']['uid'].'">'.user_get_user_name_by_username($_G['member']['username']).'</a>评论了您<a href="forum.php?mod=group&fid='.$_G['fid'].'">“'.$_G[forum][name].'”</a>的<a href="forum.php?mod=viewthread&tid='.$thread[tid].'&fid='.$_G['fid'].'">“'.$thread[subject].'”</a>话题，赶快去看看吧！', array(), 1);
		}elseif($thread['special'] == 3){
		notification_add($thread['authorid'],"reward",'[提问]<a href="home.php?mod=space&uid='.$_G['member']['uid'].'">'.user_get_user_name_by_username($_G['member']['username']).'</a>回答了您<a href="forum.php?mod=group&fid='.$_G['fid'].'">“'.$_G[forum][name].'”</a>的<a href="forum.php?mod=viewthread&tid='.$thread[tid].'&fid='.$_G['fid'].'&plugin_name=qbar">“'.$thread[subject].'”</a>提问，赶快去看看吧！', array(), 1);
		}else{
		}
	}

	hook_create_resource($pid,'group_pcomment',$_G['fid']);
	$cacheposition = getstatus($thread['status'], 1);
	if($pid && $cacheposition) {
		savepostposition($_G['tid'], $pid);
	}

	$nauthorid = 0;
	if(!empty($_G['gp_noticeauthor']) && !$isanonymous && !$modnewreplies) {
		list($ac, $nauthorid, $nauthor) = explode('|', $_G['gp_noticeauthor']);
		if($nauthorid != $_G['uid']) {
			$noticeauthormsg = strip_tags($_G['gp_noticeauthormsg']);
			$postmsg = messagecutstr(stripslashes(str_replace($_G['gp_noticetrimstr'], '', $message)), 100);
			if($ac == 'q') {
				notification_add($nauthorid, 'post', 'repquote_noticeauthor', array(
					'tid' => $thread['tid'],
					'subject' => $thread['subject'],
					'noticeauthormsg' => $noticeauthormsg,
					'postmsg' => $postmsg,
					'fid' => $_G['fid'],
					'pid' => $pid,
				));
			} elseif($ac == 'r') {
				notification_add($nauthorid, 'post', 'reppost_noticeauthor', array(
					'tid' => $thread['tid'],
					'subject' => $thread['subject'],
					'noticeauthormsg' => $noticeauthormsg,
					'postmsg' => $postmsg,
					'fid' => $_G['fid'],
					'pid' => $pid,
				));
			}
		}
	}

	if($special == 5) {

		if(!DB::num_rows($standquery)) {
			if($stand == 1) {
				DB::query("UPDATE ".DB::table('forum_debate')." SET affirmdebaters=affirmdebaters+1 WHERE tid='$_G[tid]'");
			} elseif($stand == 2) {
				DB::query("UPDATE ".DB::table('forum_debate')." SET negadebaters=negadebaters+1 WHERE tid='$_G[tid]'");
			}
		} else {
			$stand = $firststand;
		}
		if($stand == 1) {
			DB::query("UPDATE ".DB::table('forum_debate')." SET affirmreplies=affirmreplies+1 WHERE tid='$_G[tid]'");
		} elseif($stand == 2) {
			DB::query("UPDATE ".DB::table('forum_debate')." SET negareplies=negareplies+1 WHERE tid='$_G[tid]'");
		}
		DB::query("INSERT INTO ".DB::table('forum_debatepost')." (tid, pid, uid, dateline, stand, voters, voterids) VALUES ('$_G[tid]', '$pid', '$_G[uid]', '$_G[timestamp]', '$stand', '0', '')");
	}

	($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) && ($_G['gp_attachnew'] || $_G['gp_attachdel'] || $special == 2 && $_G['gp_tradeaid']) && updateattach($postattachcredits, $_G['tid'], $pid, $_G['gp_attachnew'], $_G['gp_attachdel']);

	$replymessage = 'post_reply_succeed';

	if($special == 2 && $_G['group']['allowposttrade'] && $thread['authorid'] == $_G['uid'] && !empty($_G['gp_trade']) && !empty($_G['gp_item_name'])) {

		require_once libfile('function/trade');
		trade_create(array(
			'tid' => $_G['tid'],
			'pid' => $pid,
			'aid' => $_G['gp_tradeaid'],
			'item_expiration' => $_G['gp_item_expiration'],
			'thread' => $thread,
			'discuz_uid' => $_G['uid'],
			'author' => $author,
			'seller' => empty($_G['gp_paymethod']) && $_G['gp_seller'] ? dhtmlspecialchars(trim($_G['gp_seller'])) : '',
			'item_name' => $_G['gp_item_name'],
			'item_price' => $_G['gp_item_price'],
			'item_number' => $_G['gp_item_number'],
			'item_quality' => $_G['gp_item_quality'],
			'item_locus' => $_G['gp_item_locus'],
			'transport' => $_G['gp_transport'],
			'postage_mail' => $_G['gp_postage_mail'],
			'postage_express' => $_G['gp_postage_express'],
			'postage_ems' => $_G['gp_postage_ems'],
			'item_type' => $_G['gp_item_type'],
			'item_costprice' => $_G['gp_item_costprice'],
			'item_credit' => $_G['gp_item_credit'],
			'item_costcredit' => $_G['gp_item_costcredit']
		));

		$replymessage = 'trade_add_succeed';

		if(!empty($_G['gp_tradeaid'])) {
			DB::query("UPDATE ".DB::table('forum_attachment')." SET tid='$_G[tid]', pid='$pid' WHERE aid='$_G[gp_tradeaid]' AND uid='$_G[uid]'");
		}

	}

	if($specialextra) {

		@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
		$classname = 'threadplugin_'.$specialextra;
		if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newreply_submit_end')) {
			$threadpluginclass->newreply_submit_end($_G['fid'], $_G['tid']);
		}

	}

    require_once libfile("function/post");
    post_forum_inc_replys($_G[fid]);

	$_G['forum']['threadcaches'] && deletethreadcaches($_G['tid']);

	$param = array('fid' => $_G['fid'], 'tid' => $_G['tid'], 'pid' => $pid, 'from' => $_G['gp_from'], 'sechash' => !empty($_G['gp_sechash']) ? $_G['gp_sechash'] : '');
	if($modnewreplies) {
		unset($param['pid']);
		DB::query("UPDATE ".DB::table('forum_forum')." SET todayposts=todayposts+1 WHERE fid='$_G[fid]'", 'UNBUFFERED');
		showmessage('post_reply_mod_succeed', "forum.php?mod=forumdisplay&fid=$_G[fid]", $param);
	} else {
		if($anonymity==-1){
			DB::query("UPDATE ".DB::table('forum_thread')." SET lastposter='-1', lastpost='$_G[timestamp]', replies=replies+1 WHERE tid='$_G[tid]'", 'UNBUFFERED');
		}else{
			DB::query("UPDATE ".DB::table('forum_thread')." SET lastposter='$author', lastpost='$_G[timestamp]', replies=replies+1 WHERE tid='$_G[tid]'", 'UNBUFFERED');
		}
		updatepostcredits('+', $_G['uid'], 'reply', $_G['fid']);

		if($_G['forum']['status'] == 3) {
			if($_G['forum']['closed'] > 1) {
				DB::query("UPDATE ".DB::table('forum_thread')." SET lastposter='$author', lastpost='$_G[timestamp]', replies=replies+1 WHERE tid='".$_G['forum']['closed']."'", 'UNBUFFERED');
			}
			DB::query("UPDATE ".DB::table('forum_groupuser')." SET replies=replies+1, lastupdate='".TIMESTAMP."' WHERE uid='$_G[uid]' AND fid='$_G[fid]'");

	if ($special != 3){
			//调用积分
			require_once libfile("function/credit");
				credit_create_credit_log($_G['uid'], 'comment',$thread[tid]);
				credit_create_credit_log($thread['authorid'],'bycomment',$thread[tid]);
			//经验值
				require_once libfile("function/group");
				if($special!=0){
					group_add_empirical_by_setting($_G['uid'],$_G['fid'], 'comment_someting', $thread[tid]);
				}else{
					group_add_empirical_by_setting($_G['uid'],$_G['fid'], 'topc_reply', $thread[tid]);
				}

			}
			updateactivity($_G['fid'], 0);
			updategroupcreditlog($_G['fid'], $_G['uid']);
		}
		$lastpost = "$thread[tid]\t".addslashes($thread['subject'])."\t$_G[timestamp]\t$author";
		DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost', posts=posts+1, todayposts=todayposts+1 WHERE fid='$_G[fid]'", 'UNBUFFERED');
		if($_G['forum']['type'] == 'sub') {
			DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost' WHERE fid='".$_G['forum']['fup']."'", 'UNBUFFERED');
		}

		$feed = array();
		if(!empty($_G['gp_addfeed']) && $_G['forum']['allowfeed'] && !$isanonymous) {
			if($special == 2 && !empty($_G['gp_trade'])) {
				$feed['icon'] = 'goods';
				$feed['title_template'] = 'feed_thread_goods_title';
				if($_G['gp_item_price'] > 0) {
					if($_G['setting']['creditstransextra'][5] != -1 && $_G['gp_item_credit']) {
						$feed['body_template'] = 'feed_thread_goods_message_1';
					} else {
						$feed['body_template'] = 'feed_thread_goods_message_2';
					}
				} else {
					$feed['body_template'] = 'feed_thread_goods_message_3';
				}
				$feed['body_data'] = array(
					'itemname'=> "<a href=\"$_G[siteurl]forum.php?mod=viewthread&do=tradeinfo&tid=$_G[tid]&pid=$pid\">$_G[gp_item_name]</a>",
					'itemprice'=> $_G['gp_item_price'],
					'itemcredit'=> $_G['gp_item_credit'],
					'creditunit'=> $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][5]]['unit'].$_G['setting']['extcredits'][$_G['setting']['creditstransextra'][5]]['title'],
				);
				if($_G['gp_tradeaid']) {
					$feed['images'] = array(getforumimg($_G['gp_tradeaid']));
					$feed['image_links'] = array("$_G[siteurl]forum.php?mod=viewthread&do=tradeinfo&tid=$_G[tid]&pid=$pid");
				}
			} elseif($special == 3 && $thread['authorid'] != $_G['uid']) {
				//$feed['icon'] = 'reward';
//				$feed['title_template'] = 'feed_reply_reward_title';
//				$feed['title_data'] = array(
//					'subject' => "<a href=\"$_G[siteurl]forum.php?mod=viewthread&tid=$_G[tid]\">$thread[subject]</a>",
//					'author' => "<a href=\"home.php?mod=space&uid=$thread[authorid]\">$thread[author]</a>"
//				);
				/**
				 *
				 * added by fumz,2010-8-11 10:03:58
				 * 回答提问调用积分和经验之接口
				 */
				//begin
				require_once libfile("function/group");
				group_add_empirical_by_setting($_G['uid'],$_G['fid'],"question_answer", $thread[tid]);

				/*notification_add($thread['authorid'], 'reward', 'reward_reply_question', array(
						'tid' => $thread['tid'],
						'replyusername'=>$_G['cookie']['realusername'],
						'subject' => $thread['subject'],
					));//回答提问给提问者发送通知*/
				require_once libfile("function/credit");
				credit_create_credit_log($_G['uid'], 'answerquestion',$thread[tid]);
				//end

			} elseif($special == 5 && $thread['authorid'] != $_G['uid']) {
				$feed['icon'] = 'debate';
				if($stand == 1) {
					$feed['title_template'] = 'feed_thread_debatevote_title_1';
				} elseif($stand == 2) {
					$feed['title_template'] = 'feed_thread_debatevote_title_2';
				} else {
					$feed['title_template'] = 'feed_thread_debatevote_title_3';
				}
				$feed['title_data'] = array(
					'subject' => "<a href=\"$_G[siteurl]forum.php?mod=viewthread&tid=$_G[tid]\">$thread[subject]</a>",
					'author' => "<a href=\"home.php?mod=space&uid=$thread[authorid]\">$thread[author]</a>"
				);
			} elseif($special == 1 && $thread['authorid'] != $_G['uid']){
				$post_url = "forum.php?mod=redirect&goto=findpost&pid=$pid&ptid=$_G[tid]";

				//$feed['icon'] = 'post';
//				$feed['title_template'] = 'feed_reply_votepoll' ;
//				if(!empty($thread['author'])){
//					$thread[author]=user_get_user_name_by_username($thread[author]);
//					}
//				$feed['title_data'] = array(
//					'subject' => "<a href=\"$post_url\">$thread[subject]</a>",
//					'author' => "<a href=\"home.php?mod=space&uid=$thread[authorid]\">$thread[author]</a>"
//				);
				if(!empty($_G['forum_attachexist'])) {
					$firstaid = DB::result_first("SELECT aid FROM ".DB::table('forum_attachment')." WHERE pid='$pid' AND dateline>'0' AND isimage='1' ORDER BY dateline LIMIT 1");
					if($firstaid) {
						$feed['images'] = array(getforumimg($firstaid));
						$feed['image_links'] = array($post_url);
					}
				}

			}elseif($thread['authorid'] != $_G['uid']) {
				$post_url = "forum.php?mod=redirect&goto=findpost&pid=$pid&ptid=$_G[tid]";

				//$feed['icon'] = 'post';
//				$feed['title_template'] = !empty($thread['author']) ? 'feed_reply_title' : 'feed_reply_title_anonymous';
//				if(!empty($thread['author'])){
//					$thread[author]=user_get_user_name_by_username($thread[author]);
//					}
//				$feed['title_data'] = array(
//					'subject' => "<a href=\"$post_url\">$thread[subject]</a>",
//					'author' => "<a href=\"home.php?mod=space&uid=$thread[authorid]\">$thread[author]</a>"
//				);
				if(!empty($_G['forum_attachexist'])) {
					$firstaid = DB::result_first("SELECT aid FROM ".DB::table('forum_attachment')." WHERE pid='$pid' AND dateline>'0' AND isimage='1' ORDER BY dateline LIMIT 1");
					if($firstaid) {
						$feed['images'] = array(getforumimg($firstaid));
						$feed['image_links'] = array($post_url);
					}
				}
			}
			$feed['title_data']['hash_data'] = "tid{$_G[tid]}";
			$feed['id'] = $tid;
			$feed['idtype'] = 'tid';
			if($feed['icon']) {
				postfeed($feed);
			}
		}
		include_once libfile('function/stat');
		updatestat($thread['isgroup'] ? 'grouppost' : 'post');

		$page = getstatus($thread['status'], 4) ? 1 : @ceil(($thread['special'] ? $thread['replies'] + 1 : $thread['replies'] + 2) / $_G['ppp']);

		$url = empty($_POST['portal_referer'])?"forum.php?mod=viewthread&tid={$thread[tid]}&pid=$pid&page=$page&extra=$extra#pid$pid":$_POST['portal_referer'];

		showmessage($replymessage, $url, $param);
	}

}

?>