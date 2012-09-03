<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_viewthread.php 11594 2010-06-09 00:56:45Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('SQL_ADD_THREAD', ' t.dateline, t.special, t.lastpost AS lastthreadpost, ');

require_once libfile('function/forumlist');

$page = max(1, $_G['page']);

require_once libfile('function/discuzcode');
require_once libfile('function/post');

$sdb = loadmultiserver('viewthread');

$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();
$threadtable_info = !empty($_G['cache']['threadtable_info']) ? $_G['cache']['threadtable_info'] : array();

if(!in_array(0, $threadtableids)) {
	$threadtableids = array_merge(array(0), $threadtableids);
}
$archiveid = intval($_G['gp_archiveid']);
if(empty($archiveid) || !in_array($archiveid, $threadtableids)) {
	$threadtable = !empty($_G['forum']['threadtableid']) ? "forum_thread_{$_G['forum']['threadtableid']}" : 'forum_thread';
	$_G['forum_thread'] = $sdb->fetch_first("SELECT * FROM ".DB::table($threadtable)." t WHERE tid='$_G[tid]'".($_G['forum_auditstatuson'] ? '' : " AND displayorder>='0'"));
	if($_G['forum_thread']) {
		if($_G['forum']['threadtableid']) {
			$_G['forum_thread']['is_archived'] = true;
			$_G['forum_thread']['archiveid'] = $_G['forum']['threadtableid'];
		} else {
			$_G['forum_thread']['is_archived'] = false;
		}
	}
} elseif(in_array($archiveid, $threadtableids)) {
	$threadtable = $archiveid ? "forum_thread_{$archiveid}" : 'forum_thread';
	$_G['forum_thread'] = $sdb->fetch_first("SELECT * FROM ".DB::table($threadtable)." t WHERE tid='$_G[tid]'".($_G['forum_auditstatuson'] ? '' : " AND displayorder>='0'"));

	$_G['forum_thread']['is_archived'] = true;
	$_G['forum_thread']['archiveid'] = $_G['gp_archiveid'];
} else {
	$threadtable = 'forum_thread';
	$_G['forum_thread'] = $sdb->fetch_first("SELECT * FROM ".DB::table($threadtable)." t WHERE tid='$_G[tid]'".($_G['forum_auditstatuson'] ? '' : " AND displayorder>='0'"));
}

if($_G['setting']['cachethreadlife'] && $_G['forum']['threadcaches'] && !$_G['uid'] && $page == 1 && !$_G['forum']['special'] && empty($_G['gp_do'])) {
	viewthread_loadcache();
}

$posttableid = $_G['forum_thread']['posttableid'];
if(!$_G['forum_thread']) {
	showmessage('thread_nonexistence');
}

$_G['action']['fid'] = $_G['fid'];
$_G['action']['tid'] = $_G['tid'];
$_G['gp_authorid'] = !empty($_G['gp_authorid']) ? intval($_G['gp_authorid']) : 0;
$_G['gp_ordertype'] = !empty($_G['gp_ordertype']) ? intval($_G['gp_ordertype']) : 0;

$aimgs = array();
$skipaids = array();

$oldtopics = isset($_G['cookie']['oldtopics']) ? $_G['cookie']['oldtopics'] : 'D';
if(strpos($oldtopics, 'D'.$_G['tid'].'D') === FALSE) {
	$oldtopics = 'D'.$_G['tid'].$oldtopics;
	if(strlen($oldtopics) > 3072) {
		$oldtopics = preg_replace("((D\d+)+D).*$", "\\1", substr($oldtopics, 0, 3072));
	}
	dsetcookie('oldtopics', $oldtopics, 3600);
}

if($_G['member']['lastvisit'] < $_G['forum_thread']['lastpost'] && (!isset($_G['cookie']['fid'.$_G['fid']]) || $_G['forum_thread']['lastpost'] > $_G['cookie']['fid'.$_G['fid']])) {
	dsetcookie('fid'.$_G['fid'], $_G['forum_thread']['lastpost'], 3600);
}

$thisgid = 0;

$_G['forum_thread']['subjectenc'] = rawurlencode($_G['forum_thread']['subject']);
//$_G['gp_from'] = !empty($_G['gp_from']) && in_array($_G['gp_from'], array('home', 'portal')) ? $_G['gp_from'] : '';
$_G['gp_from'] = "home";
!$_G['setting']['allowhomemode'] && $_G['gp_from'] == 'home' && $_G['gp_from'] = '';
$fromuid = $_G['setting']['creditspolicy']['promotion_visit'] && $_G['uid'] ? '&amp;fromuid='.$_G['uid'] : '';
$feeduid = $_G['forum_thread']['authorid'] ? $_G['forum_thread']['authorid'] : 0;
$feedpostnum = $_G['forum_thread']['replies'] > $_G['ppp'] ? $_G['ppp'] : ($_G['forum_thread']['replies'] ? $_G['forum_thread']['replies'] : 1);

if(!$_G['gp_from']) {
	$forumarchivename = $threadtable_info[$_G['forum']['threadtableid']]['displayname'] ? htmlspecialchars($threadtable_info[$_G['forum']['threadtableid']]['displayname']) : lang('core', 'archive').' '.$_G['forum']['threadtableid'];
	$upnavlink = 'forum.php?mod=forumdisplay&special='.$_G[gp_special].'&plugin_name='.$_G[gp_plugin_name].'&plugin_op='.$_G[gp_plugin_op].'&fid='.$_G['fid'].(!empty($_G['gp_extra']) ? '&amp;'.preg_replace("/^(&amp;)*/", '', $_G['gp_extra']) : '');
	if(empty($_G['forum']['threadtableid'])) {
		$navigation = ' &rsaquo; <a href="forum.php">'.$_G['setting']['navs'][2]['navname'].'</a> &rsaquo; <a href="'.$upnavlink.'">'.(strip_tags($_G['forum']['name']) ? strip_tags($_G['forum']['name']) : $_G['forum']['name']).'</a>';
	} else {
		$navigation = ' &rsaquo; <a href="forum.php">'.$_G['setting']['navs'][2]['navname'].'</a> &rsaquo; <a href="'.$upnavlink.'">'.(strip_tags($_G['forum']['name']) ? strip_tags($_G['forum']['name']) : $_G['forum']['name']).'</a>'.' &rsaquo; <a href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'&archiveid='.$_G['forum']['threadtableid'].'">'.$forumarchivename.'</a>';
	}
	$navtitle = $_G['forum_thread']['subject'].' - '.strip_tags($_G['forum']['name']);
	if($_G['forum']['type'] == 'sub') {
		$fup = $sdb->fetch_first("SELECT fid, name FROM ".DB::table('forum_forum')." WHERE fid='".$_G['forum']['fup']."'");
		if(empty($_G['forum']['threadtableid'])) {
			$navigation = ' &rsaquo; <a href="forum.php">'.$_G['setting']['navs'][2]['navname'].'</a> &rsaquo; <a href="forum.php?mod=forumdisplay&fid='.$fup['fid'].'">'.(strip_tags($fup['name']) ? strip_tags($fup['name']) : $fup['name']).'</a> &rsaquo; <a href="'.$upnavlink.'">'.(strip_tags($_G['forum']['name']) ? strip_tags($_G['forum']['name']) : $_G['forum']['name']).'</a>';
		} else {
			$navigation = ' &rsaquo; <a href="forum.php">'.$_G['setting']['navs'][2]['navname'].'</a> &rsaquo; <a href="forum.php?mod=forumdisplay&fid='.$fup['fid'].'">'.(strip_tags($fup['name']) ? strip_tags($fup['name']) : $fup['name']).'</a> &rsaquo; <a href="'.$upnavlink.'">'.(strip_tags($_G['forum']['name']) ? strip_tags($_G['forum']['name']) : $_G['forum']['name']).'</a>'.' &rsaquo; <a href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'&archiveid='.$_G['forum']['threadtableid'].'">'.$forumarchivename.'</a>';
		}
		$navtitle = $navtitle.' - '.strip_tags($fup['name']);
	}
} elseif($_G['gp_from'] == 'home') {
	$_G['setting']['ratelogon'] = 1;
	$navigation = ' &rsaquo; <a href="home.php">'.$_G['setting']['navs'][4]['navname'].'</a>';
	$navsubject = $_G['forum_thread']['subject'];
} elseif($_G['gp_from'] == 'portal') {
	$_G['setting']['ratelogon'] = 1;
	$navigation = ' &rsaquo; <a href="portal.php">'.lang('core', 'portal').'</a>';
	$navsubject = $_G['forum_thread']['subject'];
}
$navtitle .= ' - ';
if(in_array('forum_forumdisplay', $_G['setting']['rewritestatus'])) {
	$canonical = rewriteoutput('forum_viewthread', 1, '', $_G['tid'], $page, '', '');
} elseif(in_array('all_script', $_G['setting']['rewritestatus'])) {
	$canonical = rewriteoutput('all_script', 1, '', 'forum', 'viewthread&tid='.$_G['tid'].'&page='.$page, '');
} else {
	$canonical = 'forum.php?mod=viewthread&tid='.$_G['tid'].'&page='.$page;
}
$_G['setting']['seohead'] .= '<link href="'.$canonical.'" rel="canonical" />';
$groupnav=get_groupnav($_G['forum']);//added by fumz，2010-11-25 15:59:58


if($_G['forum']['status'] == 3) {
	$_G['action']['action'] = 3;
	require_once libfile('function/group');
	$status = groupperm($_G['forum'], $_G['uid']);
	if($status == 1) {//该专区已经关闭
		showmessage('forum_group_status_off');
	} elseif($status == 2) { //您没有权限访问该专区
		//showmessage('forum_group_noallowed', 'forum.php?mod=group&fid='.$_G['fid']);
		showmessage('您没有权限访问', 'forum.php?mod=group&fid='.$_G['fid']);
	} elseif($status == 3) { //请等待群主审核。',
		showmessage('forum_group_moderated', 'forum.php?mod=group&fid='.$_G['fid']);
	}
	$navigation = ' &rsaquo; <a href="group.php">专区</a> '.get_groupnav($_G['forum']);
	$_G['grouptypeid'] = $_G['forum']['fup'];
}
if($_G[forum][type]=="activity" && $_G[forum][fup]){
    $query = DB::query("SELECT * FROM ".DB::table("forum_forum")." WHERE fid=".$_G[forum][fup]);
    $_G[parent] = DB::fetch($query);
    $navigation .= '&rsaquo;<a href="forum.php?mod=group&fid='.$_G[forum][fup].'">'.$_G[parent][name].'</a>
            &rsaquo;<a href="forum.php?mod=activity&fid='.$_G[forum][fid].'">'.$_G[forum][name]."</a>";
}

$_G['forum_tagscript'] = '';

$threadsort = $_G['forum_thread']['sortid'] && isset($_G['forum']['threadsorts']['types'][$_G['forum_thread']['sortid']]) ? 1 : 0;
if($threadsort) {
	require_once libfile('function/threadsort');
	$threadsortshow = threadsortshow($_G['forum_thread']['sortid'], $_G['tid']);
}

$_G['forum_thread']['subject'] = ($threadsort ? ($_G['forum']['threadsorts']['listable'] ? '<a href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'&amp;filter=sort&amp;sortid='.$_G['forum_thread']['sortid'].'">['.$_G['forum']['threadsorts']['types'][$_G['forum_thread']['sortid']].']</a>' : '['.$_G['forum']['threadsorts']['types'][$_G['forum_thread']['sortid']].']').' ' : '')
	.($_G['forum_thread']['typeid'] && $_G['forum']['threadtypes']['types'][$_G['forum_thread']['typeid']] ? ($_G['forum']['threadtypes']['listable'] ? '<a href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'&amp;filter=typeid&amp;typeid='.$_G['forum_thread']['typeid'].'">['.$_G['forum']['threadtypes']['types'][$_G['forum_thread']['typeid']].']</a>' : '['.$_G['forum']['threadtypes']['types'][$_G['forum_thread']['typeid']].']').' ' : '')
	.$_G['forum_thread']['subject'];

if(empty($_G['forum']['allowview'])) {

	if(!$_G['forum']['viewperm'] && !$_G['group']['readaccess']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	} elseif($_G['forum']['viewperm'] && !forumperm($_G['forum']['viewperm'])) {
		$navtitle = '';
		showmessagenoperm('viewperm', $_G['fid']);
	}

} elseif($_G['forum']['allowview'] == -1) {
	$navtitle = '';
	showmessage('forum_access_view_disallow');
}

if($_G['forum']['formulaperm']) {
	formulaperm($_G['forum']['formulaperm']);
}

if($_G['forum']['password'] && $_G['forum']['password'] != $_G['cookie']['fidpw'.$_G['fid']]) {
	dheader("Location: $_G[siteurl]forum.php?mod=forumdisplay&fid=$_G[fid]");
}

if($_G['forum_thread']['readperm'] && $_G['forum_thread']['readperm'] > $_G['group']['readaccess'] && !$_G['forum']['ismoderator'] && $_G['forum_thread']['authorid'] != $_G['uid']) {
	showmessage('thread_nopermission', NULL, array('readperm' => $_G['forum_thread']['readperm']), array('login' => 1));
}

$usemagic = array('user' => array(), 'thread' => array());

$hiddenreplies = getstatus($_G['forum_thread']['status'], 2);

$rushreply = getstatus($_G['forum_thread']['status'], 3);

$savepostposition = getstatus($_G['forum_thread']['status'], 1);

$_G['forum_threadpay'] = FALSE;
if($_G['forum_thread']['price'] > 0 && $_G['forum_thread']['special'] == 0) {
	if($_G['setting']['maxchargespan'] && TIMESTAMP - $_G['forum_thread']['dateline'] >= $_G['setting']['maxchargespan'] * 3600) {
		DB::query("UPDATE ".DB::table($threadtable)." SET price='0' WHERE tid='$_G[tid]'");
		$_G['forum_thread']['price'] = 0;
	} else {
		$exemptvalue = $_G['forum']['ismoderator'] ? 128 : 16;
		if(!($_G['group']['exempt'] & $exemptvalue) && $_G['forum_thread']['authorid'] != $_G['uid']) {
			$query = $sdb->query("SELECT relatedid FROM ".DB::table('common_credit_log')." WHERE relatedid='$_G[tid]' AND uid='$_G[uid]' AND operation='BTC'");
			if(!$sdb->num_rows($query)) {
				require_once libfile('thread/pay', 'include');
				$_G['forum_threadpay'] = TRUE;
			}
		}
	}
}

$_G['group']['raterange'] = $_G['setting']['modratelimit'] && $adminid == 3 && !$_G['forum']['ismoderator'] ? array() : $_G['group']['raterange'];
$_G['gp_extra'] = !empty($_G['gp_extra']) ? rawurlencode($_G['gp_extra']) : '';

$_G['group']['allowgetattach'] = !empty($_G['forum']['allowgetattach']) || ($_G['group']['allowgetattach'] && !$_G['forum']['getattachperm']) || forumperm($_G['forum']['getattachperm']);
$_G['getattachcredits'] = '';
if($_G['forum_thread']['attachment']) {
	$exemptvalue = $ismoderator ? 32 : 4;
	if(!($_G['group']['exempt'] & $exemptvalue)) {
		$creditlog = updatecreditbyaction('getattach', $_G['uid'], array(), '', 1, 0, $_G['forum_thread']['fid']);
		$p = '';
		if($creditlog['updatecredit']) for($i = 1;$i <= 8;$i++) {
			if($policy = $creditlog['extcredits'.$i]) {
				$_G['getattachcredits'] .= $p.$_G['setting']['extcredits'][$i]['title'].' '.$policy.' '.$_G['setting']['extcredits'][$i]['unit'];
				$p = ', ';
			}
		}
	}
}

$seccodecheck = ($_G['setting']['seccodestatus'] & 4) && (!$_G['setting']['seccodedata']['minposts'] || getuserprofile('posts') < $_G['setting']['seccodedata']['minposts']);
$secqaacheck = $_G['setting']['secqaa']['status'] & 2 && (!$_G['setting']['secqaa']['minposts'] || getuserprofile('posts') < $_G['setting']['secqaa']['minposts']);

$postlist = $_G['forum_attachtags'] = $attachlist = $_G['forum_threadstamp'] = array();
$aimgcount = 0;
$_G['forum_attachpids'] = -1;

if(!empty($_G['gp_action']) && $_G['gp_action'] == 'printable' && $_G['tid']) {
	require_once libfile('thread/printable', 'include');
	dexit();
}

$thisgid = $_G['forum']['type'] == 'forum' ? $_G['forum']['fup'] : $_G['cache']['forums'][$_G['forum']['fup']]['fup'];
$lastmod = $_G['forum_thread']['moderated'] ? viewthread_lastmod() : array();
if(!$_G['forum_threadstamp'] && getstatus($_G['forum_thread']['status'], 5)) {
	loadcache('stamps');
	$query = DB::query("SELECT action, stamp FROM ".DB::table('forum_threadmod')." WHERE tid='$_G[tid]' ORDER BY dateline DESC");
	while($stamp = DB::fetch($query)) {
		if($stamp['action'] == 'SPA') {
			$_G['forum_threadstamp'] = $_G['cache']['stamps'][$stamp['stamp']];
			break;
		} elseif($stamp['action'] == 'SPD') {
			break;
		}
	}
}

$showsettings = str_pad(decbin($_G['setting']['showsettings']), 3, '0', STR_PAD_LEFT);

$showsignatures = $showsettings{0};
$showavatars = $showsettings{1};
$_G['setting']['showimages'] = $showsettings{2};

$highlightstatus = isset($_G['gp_highlight']) && str_replace('+', '', $_G['gp_highlight']) ? 1 : 0;

$usesigcheck = $_G['uid'] && $_G['group']['maxsigsize'] ? 1 : 0;
$_G['forum']['allowreply'] = isset($_G['forum']['allowreply']) ? $_G['forum']['allowreply'] : '';
$_G['forum']['allowpost'] = isset($_G['forum']['allowpost']) ? $_G['forum']['allowpost'] : '';
$allowpostreply = ($_G['forum']['allowreply'] != -1) && (($_G['forum_thread']['isgroup'] || (!$_G['forum_thread']['closed'] && !checkautoclose($_G['forum_thread']))) || $_G['forum']['ismoderator']) && ((!$_G['forum']['replyperm'] && $_G['group']['allowreply']) || ($_G['forum']['replyperm'] && forumperm($_G['forum']['replyperm'])) || $_G['forum']['allowreply']);
if(!$_G['uid'] && ($_G['setting']['need_avatar'] || $_G['setting']['need_email'] || $_G['setting']['need_friendnum'])) {
	$allowpostreply = false;
}
$_G['group']['allowpost'] = $_G['forum']['allowpost'] != -1 && ((!$_G['forum']['postperm'] && $_G['group']['allowpost']) || ($_G['forum']['postperm'] && forumperm($_G['forum']['postperm'])) || $_G['forum']['allowpost']);
$space = array();
space_merge($space, 'field_home');
$addfeedcheck = $space['privacy']['feed']['newreply'];
$friends = explode(',', $space['feedfriend']);

if($_G['group']['allowpost']) {
	$_G['group']['allowpostpoll'] = $_G['group']['allowpostpoll'] && ($_G['forum']['allowpostspecial'] & 1);
	$_G['group']['allowposttrade'] = $_G['group']['allowposttrade'] && ($_G['forum']['allowpostspecial'] & 2);
	$_G['group']['allowpostreward'] = $_G['group']['allowpostreward'] && ($_G['forum']['allowpostspecial'] & 4) && isset($_G['setting']['extcredits'][$_G['setting']['creditstrans']]);
	$_G['group']['allowpostactivity'] = $_G['group']['allowpostactivity'] && ($_G['forum']['allowpostspecial'] & 8);
	$_G['group']['allowpostdebate'] = $_G['group']['allowpostdebate'] && ($_G['forum']['allowpostspecial'] & 16);
} else {
	$_G['group']['allowpostpoll'] = $_G['group']['allowposttrade'] = $_G['group']['allowpostreward'] = $_G['group']['allowpostactivity'] = $_G['group']['allowpostdebate'] = FALSE;
}

$_G['forum']['threadplugin'] = $_G['group']['allowpost'] && $_G['setting']['threadplugins'] ? is_array($_G['forum']['threadplugin']) ? $_G['forum']['threadplugin'] : unserialize($_G['forum']['threadplugin']) : array();

$_G['setting']['visitedforums'] = $_G['setting']['visitedforums'] ? visitedforums() : '';
$forummenu = '';

if($_G['setting']['forumjump']) {
	$forummenu = forumselect(FALSE, 1);
}



$relatedthreadlist = array();
$relatedthreadupdate = $tagupdate = FALSE;
$relatedkeywords = $tradekeywords = $_G['forum_firstpid'] = '';
$metakeywords = !$_G['forum']['keywords'] ? $_G['forum']['name'] : $_G['forum']['keywords'];

if(!isset($_G['cookie']['collapse']) || strpos($_G['cookie']['collapse'], 'modarea_c') === FALSE) {
	$collapseimg['modarea_c'] = 'collapsed_no';
	$collapse['modarea_c'] = '';
} else {
	$collapseimg['modarea_c'] = 'collapsed_yes';
	$collapse['modarea_c'] = 'display: none';
}

$threadtag = array();
$_G['setting']['tagstatus'] = $_G['setting']['tagstatus'] && $_G['forum']['allowtag'] ? ($_G['setting']['tagstatus'] == 2 ? 2 : $_G['forum']['allowtag']) : 0;

viewthread_updateviews();

@extract($_G['cache']['custominfo']);

$_G['setting']['infosidestatus']['posts'] = $_G['setting']['infosidestatus'][1] && isset($_G['setting']['infosidestatus']['f'.$_G['fid']]['posts']) ? $_G['setting']['infosidestatus']['f'.$_G['fid']]['posts'] : $_G['setting']['infosidestatus']['posts'];


$postfieldsadd = $specialadd1 = $specialadd2 = $specialextra = '';
if($_G['forum_thread']['special'] == 2) {
	$specialadd1 = "LEFT JOIN ".DB::table('forum_trade')." tr ON p.pid=tr.pid";
	$specialadd2 = "AND tr.tid IS null";
} elseif($_G['forum_thread']['special'] == 5) {
	if(isset($_G['gp_stand']) && $_G['gp_stand'] >= 0 && $_G['gp_stand'] < 3) {
		$specialadd1 .= "LEFT JOIN ".DB::table('forum_debatepost')." dp ON p.pid=dp.pid";
		if($_G['gp_stand']) {
			$specialadd2 .= "AND (dp.stand='$_G[gp_stand]' OR p.first='1')";
		} else {
			$specialadd2 .= "AND (dp.stand='0' OR dp.stand IS NULL OR p.first='1')";
		}
		$specialextra = "&amp;stand=$_G[gp_stand]";
	} else {
		$specialadd1 = "LEFT JOIN ".DB::table('forum_debatepost')." dp ON p.pid=dp.pid";
	}
	$postfieldsadd .= ", dp.stand, dp.voters";
}

$onlyauthoradd = $threadplughtml = '';
$posttable = $posttableid ? "forum_post_$posttableid" : 'forum_post';

if(empty($_G['gp_viewpid'])) {

	$ordertype = empty($_G['gp_ordertype']) && getstatus($_G['forum_thread']['status'], 4) ? 1 : $_G['gp_ordertype'];

	$sticklist = array();
	if($_G['forum_thread']['stickreply'] && $page == 1 && !$_G['gp_authorid'] && !$ordertype) {
		$query = DB::query("SELECT p.*, pp.position FROM ".DB::table('forum_postposition')." pp
			LEFT JOIN ".DB::table($posttable)." p ON p.pid=pp.pid
			WHERE pp.tid='$_G[tid]' AND pp.stick='1' ORDER BY pp.dateline DESC");
		while($post = DB::fetch($query)) {
			$post['message'] = messagecutstr($post['message'], 400);
			$post['avatar'] = discuz_uc_avatar($post['authorid'], 'small');
			$sticklist[$post['pid']] = $post;
		}
		$stickcount = count($sticklist);
	}

	if($_G['gp_authorid']) {
		$_G['forum_thread']['replies'] = $sdb->result_first("SELECT COUNT(*) FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND invisible='0' AND authorid='$_G[gp_authorid]'") - 1;
		if($_G['forum_thread']['replies'] < 0) {
			showmessage('undefined_action');
		}
		$onlyauthoradd = "AND p.authorid='$_G[gp_authorid]'";
	} elseif($_G['forum_thread']['special'] == 5) {
		if(isset($_G['gp_stand']) && $_G['gp_stand'] >= 0 && $_G['gp_stand'] < 3) {
			if($_G['gp_stand']) {
				$_G['forum_thread']['replies'] = $sdb->result_first("SELECT COUNT(*) FROM ".DB::table('forum_debatepost')." WHERE tid='$_G[tid]' AND stand='$_G[gp_stand]'");
			} else {
				$_G['forum_thread']['replies'] = $sdb->result_first("SELECT COUNT(*) FROM ".DB::table($posttable)." p LEFT JOIN ".DB::table('forum_debatepost')." dp ON p.pid=dp.pid WHERE p.tid='$_G[tid]' AND (dp.stand='0' OR dp.stand IS NULL)");
			}
			$_G['forum_thread']['replies'] = $sdb->result_first("SELECT COUNT(*) FROM ".DB::table('forum_debatepost')." WHERE tid='$_G[tid]' AND stand='$_G[gp_stand]'");
		} else {
			$_G['forum_thread']['replies'] = $sdb->result_first("SELECT COUNT(*) FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND invisible='0'") - 1;
		}
	} elseif($_G['forum_thread']['special'] == 2) {
		$tradenum = DB::result_first("SELECT count(*) FROM ".DB::table('forum_trade')." WHERE tid='$_G[tid]'");
		$_G['forum_thread']['replies'] -= $tradenum;
	}
	
	$_G['per']=$_GET['per'];
	if(empty($_G['per'])){
		$_G['per']=10;
	}
	$_G['mod']=$_GET['mod'];
	$_G['special']=$_GET['special'];
	$_G['plugin_name']=$_GET['plugin_name'];
	$_G['plugin_op']=$_GET['plugin_op'];
	$_G['tid']=$_GET['tid'];
	$_G['ppp']=$_G['per']+1;
    $_G['ordertype']=$_GET['ordertype'];
    if (empty($_G['ordertype'])) {
    	$_G['ordertype']="desc";
    }
	$totalpage = ceil(($_G['forum_thread']['replies'] + 1) / $_G['ppp']);
	$page > $totalpage && $page = $totalpage;
	$_G['forum_pagebydesc'] = $page > 50 && $page > ($totalpage / 2) ? TRUE : FALSE;
   if($_G['ppp']){
	 $_G['ppp'] = $_G['forum']['threadcaches'] && !$_G['uid'] ? $_G['setting']['postperpage'] : $_G['ppp'];
   }
	if($_G['forum_pagebydesc']) {
		$firstpagesize = ($_G['forum_thread']['replies'] + 1) % $_G['ppp'];
		$_G['forum_ppp3'] = $_G['forum_ppp2'] = $page == $totalpage && $firstpagesize ? $firstpagesize : $_G['ppp'];
		$realpage = $totalpage - $page + 1;
		$start_limit = max(0, ($realpage - 2) * $_G['ppp'] + $firstpagesize);
		$_G['forum_numpost'] = ($page - 1) * $_G['ppp'];
		if($ordertype != 1) {
			$pageadd =  "ORDER BY dateline DESC LIMIT $start_limit, ".$_G['forum_ppp2'];
		} else {
			$_G['forum_numpost'] = $_G['forum_thread']['replies'] + 2 - $_G['forum_numpost'] + ($page > 1 ? 1 : 0);
			$pageadd = "ORDER BY first ASC,dateline ASC LIMIT $start_limit, ".$_G['forum_ppp2'];
		}
	} else {
		$start_limit = $_G['forum_numpost'] = max(0, ($page - 1) * $_G['ppp']);
		if($start_limit > $_G['forum_thread']['replies']) {
			$start_limit = $_G['forum_numpost'] = 0;
			$page = 1;
		}
		if($ordertype != 1) {
			$pageadd = "ORDER BY first DESC, dateline ". $_G['ordertype']." LIMIT $start_limit, $_G[ppp]";
		} else {
			$_G['forum_numpost'] = $_G['forum_thread']['replies'] + 2 - $_G['forum_numpost'] + ($page > 1 ? 1 : 0);
			$pageadd = "ORDER BY first DESC,dateline ". $_G['ordertype']." LIMIT $start_limit, $_G[ppp]";
		}
	}
	$multipage_archive = $_G['forum_thread']['is_archived'] ? "archive={$_G['forum_thread']['archiveid']}" : '';
	$multipage = multi($_G['forum_thread']['replies'] + 1, $_G['ppp'], $page, "forum.php?mod=viewthread&per=$_G[per]&ordertype=$_G[ordertype]&tid=$_G[tid]".(!empty($multipage_archive) ? "&{$multipage_archive}" : '')."&amp;extra=$_G[gp_extra]".($ordertype && $ordertype != getstatus($_G['forum_thread']['status'], 4) ? "&amp;ordertype=$ordertype" : '').(isset($_G['gp_highlight']) ? "&amp;highlight=".rawurlencode($_G['gp_highlight']) : '').(!empty($_G['gp_authorid']) ? "&amp;authorid=$_G[gp_authorid]" : '').(!empty($_G['gp_from']) ? "&amp;from=$_G[gp_from]" : '').$specialextra);
} else {
	$_G['gp_viewpid'] = intval($_G['gp_viewpid']);
	$pageadd = "AND p.pid='$_G[gp_viewpid]'";
}

$_G['forum_newpostanchor'] = $_G['forum_postcount'] = $_G['forum_ratelogpid'] = $_G['forum_commonpid'] = 0;

$_G['forum_onlineauthors'] = array();

$query = "SELECT p.* $postfieldsadd
		FROM ".DB::table($posttable)." p
		$specialadd1 ";

$cachepids = $positionlist = $postusers = $skipaids = array();
if($savepostposition && empty($onlyauthoradd) && empty($specialadd2) && empty($_G['gp_viewpid']) && $ordertype != 1) {
	$start = ($page - 1) * $_G['ppp'] + 1;
	$end = $start + $_G['ppp'];
	$q2 = DB::query("SELECT pid, position FROM ".DB::table('forum_postposition')." WHERE tid='$_G[tid]' AND position>='$start' AND position < $end ORDER BY position");
	while ($post = DB::fetch($q2)) {
		$cachepids[] = $post['pid'];
		$positionlist[$post['pid']] = $post['position'];
	}
	$cachepids = dimplode($cachepids);
	$pagebydesc = false;
}

$query .= $savepostposition && $cachepids ? "WHERE p.pid in($cachepids)" : ("WHERE p.tid='$_G[tid]'".($_G['forum_auditstatuson'] ? '' : " AND p.invisible='0'")." $specialadd2 $onlyauthoradd $pageadd");

$query = DB::query($query);
while($post = DB::fetch($query)) {
	if(($onlyauthoradd && $post['anonymous'] == 0) || !$onlyauthoradd) {
		if($post[anonymity]>0){
			include_once libfile('function/repeats','plugin/repeats');
			$repeatsinfo=getforuminfo($post[anonymity]);
			$post[repeatsinfo]=$repeatsinfo;
		}
		if($post['first']=='1'){
			$repeats=$repeatsinfo;
		}
		if($_G['forum_thread'][tid]==$post[tid]){
			$_G['forum_thread']['anonymity']=$post['anonymity'];
		}
		$postlist[$post['pid']] = $post;
		$postusers[$post['authorid']] = array();
	}
}

if($postusers) {
	$query = DB::query("SELECT m.uid, m.username, m.groupid, m.adminid, m.regdate, m.credits, m.email,
			ms.lastactivity, ms.lastactivity,
			mc.*, mp.gender, mp.site, mp.icq, mp.qq, mp.yahoo, mp.msn, mp.taobao, mp.alipay,
			mf.medals, mf.sightml AS signature, mf.customstatus $fieldsadd
			FROM ".DB::table('common_member')." m
			LEFT JOIN ".DB::table('common_member_field_forum')." mf ON mf.uid=m.uid
			LEFT JOIN ".DB::table('common_member_status')." ms ON ms.uid=m.uid
			LEFT JOIN ".DB::table('common_member_count')." mc ON mc.uid=m.uid
			LEFT JOIN ".DB::table('common_member_profile')." mp ON mp.uid=m.uid
			WHERE m.uid IN (".dimplode(array_keys($postusers)).")");
	
	while($postuser = DB::fetch($query)) {
		$postusers[$postuser['uid']] = $postuser;
	}
	
	foreach($postlist as $pid => $post) {
		$post = array_merge($postlist[$pid], $postusers[$post['authorid']]);
		$postlist[$pid] = viewthread_procpost($post, $_G['member']['lastvisit'], $ordertype);
	}

}

if($savepostposition && $positionlist) {
	foreach ($positionlist as $pid => $position)
	$postlist[$pid]['number'] = $position;
}
//print_r($postlist);
//print_r($_G['forum_thread']);
if($_G['forum_thread']['special'] > 0 && (empty($_G['gp_viewpid']) || $_G['gp_viewpid'] == $_G['forum_firstpid'])) {
	$_G['forum_thread']['starttime'] = gmdate($_G['forum_thread']['dateline']);
	$_G['forum_thread']['remaintime'] = '';
	switch($_G['forum_thread']['special']) {
		case 1: require_once libfile('thread/poll', 'include'); break;
		case 2: require_once libfile('thread/trade', 'include'); break;
		case 3: require_once libfile('thread/reward', 'include'); break;
		case 4: require_once libfile('thread/activity', 'include'); break;
		case 5: require_once libfile('thread/debate', 'include'); break;
		case 127:
			$sppos = strpos($postlist[$_G['forum_firstpid']]['message'], chr(0).chr(0).chr(0));
			$specialextra = substr($postlist[$_G['forum_firstpid']]['message'], $sppos + 3);
			if($specialextra) {
				if(array_key_exists($specialextra, $_G['setting']['threadplugins'])) {
					@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
					$classname = 'threadplugin_'.$specialextra;
					if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'viewthread')) {
						$threadplughtml = $threadpluginclass->viewthread($_G['tid']);
					}
				}
				$postlist[$_G['forum_firstpid']]['message'] = substr($postlist[$_G['forum_firstpid']]['message'], 0, $sppos);
			}
			break;
	}
}

if(empty($_G['gp_authorid']) && empty($postlist)) {
	$replies = intval(DB::result_first("SELECT COUNT(*) FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND invisible='0'")) - 1;
	if($_G['forum_thread']['replies'] != $replies && $replies > 0) {
		DB::query("UPDATE ".DB::table($threadtable)." SET replies='$replies' WHERE tid='$_G[tid]'");
		dheader("Location: forum.php?mod=redirect&tid=$_G[tid]&goto=lastpost");
	}
}

if($_G['forum_pagebydesc'] && (!$savepostposition || $_G['gp_ordertype'] == 1)) {
	$postlist = array_reverse($postlist, TRUE);
}

if($_G['setting']['vtonlinestatus'] == 2 && $_G['forum_onlineauthors']) {
	$query = DB::query("SELECT uid FROM ".DB::table('common_session')." WHERE uid IN(".(implode(',', $_G['forum_onlineauthors'])).") AND invisible=0");
	$_G['forum_onlineauthors'] = array();
	while($author = DB::fetch($query)) {
		$_G['forum_onlineauthors'][$author['uid']] = 1;
	}
} else {
	$_G['forum_onlineauthors'] = array();
}

$ratelogs = array();
if($_G['forum_ratelogpid']) {
	$query = DB::query("SELECT * FROM ".DB::table('forum_ratelog')." WHERE pid IN (".$_G['forum_ratelogpid'].") ORDER BY dateline DESC");
	while($ratelog = DB::fetch($query)) {
		if(count($postlist[$ratelog['pid']]['ratelog']) < $_G['setting']['ratelogrecord']) {
			$ratelogs[$ratelog['pid']][$ratelog['uid']]['username'] = $ratelog['username'];
			$ratelogs[$ratelog['pid']][$ratelog['uid']]['score'][$ratelog['extcredits']] = $ratelog['score'];
			$ratelogs[$ratelog['pid']][$ratelog['uid']]['reason'] = dhtmlspecialchars($ratelog['reason']);
			$postlist[$ratelog['pid']]['ratelog'][$ratelog['uid']] = $ratelogs[$ratelog['pid']][$ratelog['uid']];
		}

		if(!$postlist[$ratelog['pid']]['totalrate'] || !in_array($ratelog['uid'], $postlist[$ratelog['pid']]['totalrate'])) {
			$postlist[$ratelog['pid']]['totalrate'][] = $ratelog['uid'];
		}
	}
}

$comments = $commentcount = $totalcomment = array();
if($_G['forum_commonpid'] && $_G['setting']['commentnumber']) {
	$query = DB::query("SELECT * FROM ".DB::table('forum_postcomment')." WHERE pid IN (".$_G['forum_commonpid'].') ORDER BY dateline DESC');
	while($comment = DB::fetch($query)) {
		if($comment['authorid']) {
			$commentcount[$comment['pid']]++;
		}
		if(count($comments[$comment['pid']]) < $_G['setting']['commentnumber'] && $comment['authorid']) {
			$comment['avatar'] = discuz_uc_avatar($comment['authorid'], 'small');
			$comment['dateline'] = dgmdate($comment['dateline'], 'u');
			$comments[$comment['pid']][] = str_replace(array('[b]', '[/b]', '[/color]'), array('<b>', '</b>', '</font>'), preg_replace("/\[color=([#\w]+?)\]/i", "<font color=\"\\1\">", $comment));
		}
		if(!$comment['authorid']) {
			$cic = 0;
			$totalcomment[$comment['pid']] = preg_replace('/<i>([\.\d]+)<\/i>/e', "'<i class=\"cmstarv\" style=\"background-position:20px -'.(intval(\\1) * 16).'px\">'.sprintf('%1.1f', \\1).'</i>'.(\$cic++ % 2 ? '<br />' : '');", $comment['comment']);
		}
	}
}

if($_G['forum_attachpids'] != '-1') {
	require_once libfile('function/attachment');
	parseattach_topic($_G['forum_attachpids'], $_G['forum_attachtags'], $postlist, $skipaids);
}

/*if(empty($postlist)) {
	//showmessage('undefined_action', NULL);
} else {
	$_G['setting']['seodescription'] = current($postlist);
	$_G['setting']['seodescription'] = !$_G['forum_thread']['price'] ? str_replace(array("\r", "\n"), '', cutstr(strip_tags($_G['setting']['seodescription']['message']), 150)) : '';
}*/

viewthread_parsetags($postlist);

if(empty($_G['gp_viewpid'])) {
		
	$sufix = '';
	if($_G['gp_from'] == 'home') {
		$space['uid'] = $_G['forum_thread']['authorid'];
		$space['username'] = $_G['forum_thread']['author'];
		$sufix = '_home';
		$post = &$postlist[$_G['forum_firstpid']];
	} elseif($_G['gp_from'] == 'portal') {
		$sufix = '_portal';
		$post = &$postlist[$_G['forum_firstpid']];
	}
	if($_G['inajax']) {
		include template('common/header_ajax');
		$sufix = '_from_node';
	}
	/*
	 * modified by fumz,2010-8-2 16:38:00
	 */
	require_once libfile("function/category");
	$qis_enable_category = common_category_is_enable($_G["fid"], 'qbar');//提问吧分类是否开启
	$qisprefix=common_category_is_prefix($_G['fid'], 'qbar');//提问是否显示分类
	if($qis_enable_category&&$qisprefix){
		if($_G['forum_thread']['category_id']!=0){
			$sql="SELECT name from pre_common_category WHERE id=".$_G['forum_thread']['category_id'];
			$query=DB::query($sql);
			$name=DB::fetch($query,0);
			if(empty($name)){
				$_G['forum_thread']['category_name']="未分类";
			}else{
				$_G['forum_thread']['category_name']=$name['name'];	
			}				
		}else{
			$_G['forum_thread']['category_name']="未分类";
		}		
	}

	if(($_G['forum_thread']['special']==3||$_G['forum_thread']['special']==-3)){//&&$_GET['from']!='home',delete by fumz,2010-8-20 11:06:53
		//exit("您查看的是提问！");
		$post=$postlist[$_G['forum_firstpid']];
		$_G['forum']['icon'] = get_groupimg($_G['forum']['icon'], 'icon');
		include template('forum/viewquestion');
	}else{
		include template('diy:forum/viewthread'.$sufix.':'.$_G['fid']);//discuz!X 这一段的源码
	}
	
	
	if($_G['inajax']) {
		include template('common/footer_ajax');
	}
} else {
	$_G['setting']['admode'] = 0;
	$post = $postlist[$_G['gp_viewpid']];
	$post['number'] = $sdb->result_first("SELECT count(*) FROM ".DB::table($posttable)." WHERE tid='$post[tid]' AND dateline<='$post[dbdateline]'");
	include template('common/header_ajax');
	$postcount = 0;

	if($_G['gp_from']) {
		include template('forum/viewthread_from_node');
	} else {
		include template('forum/viewthread_node');
	}
	include template('common/footer_ajax');
}



function viewthread_updateviews() {
	global $_G, $do, $threadtable;

	if($_G['setting']['delayviewcount'] == 1 || $_G['setting']['delayviewcount'] == 3) {
		$_G['forum_logfile'] = './data/cache/forum_cache_threadviews.log';
		if(substr(TIMESTAMP, -2) == '00') {
			require_once libfile('function/misc');
			updateviews($threadtable, 'tid', 'views', $_G['forum_logfile']);
		}
		if(@$fp = fopen(DISCUZ_ROOT.$_G['forum_logfile'], 'a')) {
			fwrite($fp, "$_G[tid]\n");
			fclose($fp);
		} elseif($adminid == 1) {
			showmessage('view_log_invalid', '', array('logfile' => $_G['forum_logfile']));
		}
	} else {

		DB::query("UPDATE LOW_PRIORITY ".DB::table($threadtable)." SET views=views+1 WHERE tid='$_G[tid]'", 'UNBUFFERED');

	}
}

function viewthread_procpost($post, $lastvisit, $ordertype, $special = 0) {
	global $_G;

	if(!$_G['forum_newpostanchor'] && $post['dateline'] > $lastvisit) {
		$post['newpostanchor'] = '<a name="newpost"></a>';
		$_G['forum_newpostanchor'] = 1;
	} else {
		$post['newpostanchor'] = '';
	}

	$post['lastpostanchor'] = ($ordertype != 1 && $_G['forum_numpost'] == $_G['forum_thread']['replies']) || ($ordertype == 1 && $_G['forum_numpost'] == $_G['forum_thread']['replies'] + 2) ? '<a name="lastpost"></a>' : '';

	if($_G['forum_pagebydesc']) {
		if($ordertype != 1) {
			$post['number'] = $_G['forum_numpost'] + $_G['forum_ppp2']--;
		} else {
			$post['number'] = $post['first'] == 1 ? 1 : $_G['forum_numpost'] - $_G['forum_ppp2']--;
		}
	} else {
		if($ordertype != 1) {
			$post['number'] = ++$_G['forum_numpost'];
		} else {
			$post['number'] = $post['first'] == 1 ? 1 : --$_G['forum_numpost'];
		}
	}

	$_G['forum_postcount']++;

	$post['dbdateline'] = $post['dateline'];
	$post['dateline'] = dgmdate($post['dateline'], 'u');
	$post['groupid'] = $_G['cache']['usergroups'][$post['groupid']] ? $post['groupid'] : 7;

	if($post['username']) {

		$_G['forum_onlineauthors'][] = $post['authorid'];
		$post['usernameenc'] = rawurlencode($post['username']);
		!$special && $post['groupid'] = getgroupid($post['authorid'], $_G['cache']['usergroups'][$post['groupid']], $post);
		$post['readaccess'] = $_G['cache']['usergroups'][$post['groupid']]['readaccess'];
		if($_G['cache']['usergroups'][$post['groupid']]['userstatusby'] == 1) {
			$post['authortitle'] = $_G['cache']['usergroups'][$post['groupid']]['grouptitle'];
			$post['stars'] = $_G['cache']['usergroups'][$post['groupid']]['stars'];
		}

		$post['taobaoas'] = addslashes($post['taobao']);
		$post['regdate'] = dgmdate($post['regdate'], 'd');
		$post['lastdate'] = dgmdate($post['lastactivity'], 'd');

		$post['authoras'] = !$post['anonymous'] ? ' '.addslashes($post['author']) : '';

		if($post['medals']) {
			loadcache('medals');
			foreach($post['medals'] = explode("\t", $post['medals']) as $key => $medalid) {
				list($medalid, $medalexpiration) = explode("|", $medalid);
				if(isset($_G['cache']['medals'][$medalid]) && (!$medalexpiration || $medalexpiration > TIMESTAMP)) {
					$post['medals'][$key] = $_G['cache']['medals'][$medalid];
				} else {
					unset($post['medals'][$key]);
				}
			}
		}

		$post['avatar'] = discuz_uc_avatar($post['authorid']);
		$post['groupicon'] = $post['avatar'] ? g_icon($post['groupid'], 1) : '';
		$post['banned'] = $post['status'] & 1;
		$post['warned'] = ($post['status'] & 2) >> 1;

	} else {
		if(!$post['authorid']) {
			$post['useip'] = substr($post['useip'], 0, strrpos($post['useip'], '.')).'.x';
		}
	}
	$post['attachments'] = array();
	$post['imagelist'] = $post['attachlist'] = '';

	if($post['attachment']) {
		if($_G['group']['allowgetattach']) {
			$_G['forum_attachpids'] .= ",$post[pid]";
			$post['attachment'] = 0;
			if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $post['message'], $matchaids)) {
				$_G['forum_attachtags'][$post['pid']] = $matchaids[1];
			}
		} else {
			$post['message'] = preg_replace("/\[attach\](\d+)\[\/attach\]/i", '', $post['message']);
		}
	}

	$_G['forum_ratelogpid'] .= ($_G['setting']['ratelogrecord'] && $post['ratetimes']) ? ','.$post['pid'] : '';
	if($_G['setting']['commentnumber'] && ($post['first'] && $_G['setting']['commentfirstpost'] || !$post['first'])) {
		$_G['forum_commonpid'] .= $post['comment'] ? ','.$post['pid'] : '';
	}
	$post['allowcomment'] = $_G['setting']['commentnumber'] && ($_G['setting']['commentpostself'] || $post['authorid'] != $_G['uid']) &&
		($post['first'] && $_G['setting']['commentfirstpost'] && in_array($_G['group']['allowcommentpost'], array(1, 3)) ||
		(!$post['first'] && in_array($_G['group']['allowcommentpost'], array(2, 3))));
	$_G['forum']['allowbbcode'] = $_G['forum']['allowbbcode'] ? ($_G['cache']['usergroups'][$post['groupid']]['allowcusbbcode'] ? 2 : 1) : 0;
	$post['signature'] = $post['usesig'] ? ($_G['setting']['sigviewcond'] ? (strlen($post['message']) > $_G['setting']['sigviewcond'] ? $post['signature'] : '') : $post['signature']) : '';
	$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'],1, $_G['forum']['allowsmilies'], $_G['forum']['allowbbcode'], ($_G['forum']['allowimgcode'] && $_G['setting']['showimages'] ? 1 : 0), 1, ($_G['forum']['jammer'] && $post['authorid'] != $_G['uid'] ? 1 : 0), 0, $post['authorid'], 1, $post['pid']);
	$post['first'] && $_G['forum_firstpid'] = $post['pid'];
	$_G['forum_firstpid'] = intval($_G['forum_firstpid']);

	$findtag = explode('[attach]',$post['message']);
        for($i=1;$i<count($findtag);$i++){
            $st = $findtag[$i];
            $str = explode('[/attach]',$st);
            $del_aid = $str[0];
            if($str[0]!=$st){
            $values=DB::query("SELECT count(*) FROM ".DB::table('forum_attachment')." where aid = ".$str[0]);
            $rt=DB::result($values);
            if($rt == 0){
                 $k=$del_aid;
                 $post['message'] = str_replace('[attach]'.$k.'[/attach]','',$post['message']);
                 DB::query("UPDATE ".DB::table('forum_post')." SET message= '". $post['message']."'  WHERE pid=".$post['pid']);
            }
            }
        }

	return $post;
}

function viewthread_loadcache() {
	global $_G;
	$_G['forum']['livedays'] = ceil((TIMESTAMP - $_G['forum']['dateline']) / 86400);
	$_G['forum']['lastpostdays'] = ceil((TIMESTAMP - $_G['forum']['lastthreadpost']) / 86400);
	$threadcachemark = 100 - (
	$_G['forum']['displayorder'] * 15 +
	$_G['forum']['digest'] * 10 +
	min($_G['forum']['views'] / max($_G['forum']['livedays'], 10) * 2, 50) +
	max(-10, (15 - $_G['forum']['lastpostdays'])) +
	min($_G['forum']['replies'] / $_G['setting']['postperpage'] * 1.5, 15));
	if($threadcachemark < $_G['forum']['threadcaches']) {

		$threadcache = getcacheinfo($_G['tid']);

		if(TIMESTAMP - $threadcache['filemtime'] > $_G['setting']['cachethreadlife']) {
			@unlink($threadcache['filename']);
			define('CACHE_FILE', $threadcache['filename']);
		} else {
			readfile($threadcache['filename']);

			viewthread_updateviews();
			$_G['setting']['debug'] && debuginfo();
			$_G['setting']['debug'] ? die('<script type="text/javascript">document.getElementById("debuginfo").innerHTML = " '.($_G['setting']['debug'] ? 'Updated at '.gmdate("H:i:s", $threadcache['filemtime'] + 3600 * 8).', Processed in '.$debuginfo['time'].' second(s), '.$debuginfo['queries'].' Queries'.($_G['gzipcompress'] ? ', Gzip enabled' : '') : '').'";</script>') : die();
		}
	}
}

function viewthread_lastmod() {
	global $_G, $threadtable;
	if($lastmod = DB::fetch_first("SELECT uid AS moduid, username AS modusername, dateline AS moddateline, action AS modaction, magicid, stamp
		FROM ".DB::table('forum_threadmod')."
		WHERE tid='$_G[tid]' ORDER BY dateline DESC LIMIT 1")) {
		$modactioncode = lang('forum/modaction');
		$lastmod['modusername'] = $lastmod['modusername'] ? $lastmod['modusername'] : 'System';
		$lastmod['moddateline'] = dgmdate($lastmod['moddateline'], 'u');
		if($lastmod['modusername']){
			$lastmod['modusername']=user_get_user_name_by_username($lastmod['modusername']);
		}
		$_G['forum_threadstamp'] = $lastmod['modaction'] != 'SPA' ? array() : $_G['cache']['stamps'][$lastmod['stamp']];
		$lastmod['modaction'] = ($modactioncode[$lastmod['modaction']] ? $modactioncode[$lastmod['modaction']] : '').($lastmod['modaction'] != 'SPA' ? '' : ' '.$_G['cache']['stamps'][$lastmod['stamp']]['text']);
		if($lastmod['magicid']) {
			loadcache('magics');
			$lastmod['magicname'] = $_G['cache']['magics'][$lastmod['magicid']]['name'];
		}
	} else {
		DB::query("UPDATE ".DB::table($threadtable)." SET moderated='0' WHERE tid='$_G[tid]'", 'UNBUFFERED');
	}
	return $lastmod;
}

function viewthread_parsetags($postlist) {
	global $_G;
	if($_G['forum_firstpid'] && $_G['setting']['tagstatus'] && $_G['forum']['allowtag'] && !($postlist[$_G['forum_firstpid']]['htmlon'] & 2) && !empty($_G['cache']['tags'])) {
		$_G['forum_tagscript'] = '<script type="text/javascript">var tagarray = '.$_G['cache']['tags'][0].';var tagencarray = '.$_G['cache']['tags'][1].';parsetag('.$_G['forum_firstpid'].');</script>';
	}
}

function remaintime($time) {
	$days = intval($time / 86400);
	$time -= $days * 86400;
	$hours = intval($time / 3600);
	$time -= $hours * 3600;
	$minutes = intval($time / 60);
	$time -= $minutes * 60;
	$seconds = $time;
	return array((int)$days, (int)$hours, (int)$minutes, (int)$seconds);
}

?>