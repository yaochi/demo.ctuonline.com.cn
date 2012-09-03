<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_index.php 10449 2010-05-11 08:34:17Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
header("Location:group.php");
exit;

require_once libfile('function/forumlist');

do_forum_bind_domains();

$showoldetails = get_index_online_details();
$gid = !empty($_G['gp_gid']) ? intval($_G['gp_gid']) : '';

if($_G['setting']['cacheindexlife'] && !$_G['uid'] && $showoldetails != 'yes' && empty($gid)) {
	get_index_page_guest_cache();
}

$_G['uid'] && $newthreads = round((TIMESTAMP - $_G['member']['lastvisit'] + 600) / 1000) * 1000;
$rsshead = $_G['setting']['rssstatus'] ? ('<link rel="alternate" type="application/rss+xml" title="'.$_G['setting']['bbname'].'" href="'.$_G['siteurl'].'forum.php?mod=rss&auth='.$_G['rssauth']."\" />") : '';

$catlist = $forumlist = $sublist = $forumname = $collapseimg = $collapse = array();
$threads = $posts = $todayposts = $fids = $announcepm = 0;
$postdata = $_G['cache']['historyposts'] ? explode("\t", $_G['cache']['historyposts']) : array();
$postdata[0] = intval($postdata[0]);
$postdata[1] = intval($postdata[1]);

if($_G['setting']['indexhot']['status'] && $_G['cache']['heats']['expiration'] < TIMESTAMP) {
	require_once libfile('function/cache');
	updatecache('heats');
}

if(empty($gid) && empty($_G['member']['accessmasks']) && empty($showoldetails)) {
	extract(get_index_memory_by_groupid($_G['member']['groupid']));
	if(defined('FORUM_INDEX_PAGE_MEMORY') && FORUM_INDEX_PAGE_MEMORY) {
		include template('diy:forum/discuz');
		dexit();
	}
}

if(!$gid && (!defined('FORUM_INDEX_PAGE_MEMORY') || !FORUM_INDEX_PAGE_MEMORY)) {
	$announcements = get_index_announcements();

	$sql = !empty($_G['member']['accessmasks']) ?
		"SELECT f.fid, f.fup, f.type, f.name, f.threads, f.posts, f.todayposts, f.lastpost, f.inheritedmod,
			f.forumcolumns, f.simple, ff.description, ff.moderators, ff.icon, ff.viewperm, ff.redirect, ff.extra, a.allowview
			FROM ".DB::table('forum_forum')." f
			LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid
			LEFT JOIN ".DB::table('forum_access')." a ON a.uid='$_G[uid]' AND a.fid=f.fid
			WHERE f.status IN ('1','2') ORDER BY f.type, f.displayorder"
		: "SELECT f.fid, f.fup, f.type, f.name, f.threads, f.posts, f.todayposts, f.lastpost, f.inheritedmod,
			f.forumcolumns, f.simple, ff.description, ff.moderators, ff.icon, ff.viewperm, ff.redirect, ff.extra
			FROM ".DB::table('forum_forum')." f
			LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid)
			WHERE f.status IN ('1','2') ORDER BY f.type, f.displayorder";

	$query = DB::query($sql);

	while($forum = DB::fetch($query)) {
		$forumname[$forum['fid']] = strip_tags($forum['name']);
		$forum['extra'] = unserialize($forum['extra']);
		if(!is_array($forum['extra'])) {
			$forum['extra'] = array();
		}

		if($forum['type'] != 'group') {

			$threads += $forum['threads'];
			$posts += $forum['posts'];
			$todayposts += $forum['todayposts'];

			if($forum['type'] == 'forum' && isset($catlist[$forum['fup']])) {
				if(forum($forum)) {
					$catlist[$forum['fup']]['forums'][] = $forum['fid'];
					$forum['orderid'] = $catlist[$forum['fup']]['forumscount']++;
					$forum['subforums'] = '';
					$forumlist[$forum['fid']] = $forum;
				}

			} elseif(isset($forumlist[$forum['fup']])) {

				$forumlist[$forum['fup']]['threads'] += $forum['threads'];
				$forumlist[$forum['fup']]['posts'] += $forum['posts'];
				$forumlist[$forum['fup']]['todayposts'] += $forum['todayposts'];
				if($_G['setting']['subforumsindex'] && $forumlist[$forum['fup']]['permission'] == 2 && !($forumlist[$forum['fup']]['simple'] & 16) || ($forumlist[$forum['fup']]['simple'] & 8)) {
					$forumlist[$forum['fup']]['subforums'] .= (empty($forumlist[$forum['fup']]['subforums']) ? '' : ', ').'<a href="forum.php?mod=forumdisplay&fid='.$forum['fid'].'" '.(!empty($forum['extra']['namecolor']) ? ' style="color: ' . $forum['extra']['namecolor'].';"' : '') . '>'.$forum['name'].'</a>';
				}
			}

		} else {

			if(!isset($_G['cookie']['collapse']) || strpos($_G['cookie']['collapse'], '_category_'.$forum['fid'].'_') === FALSE) {
				$forum['collapseimg'] = 'collapsed_no.gif';
				$collapse['category_'.$forum['fid']] = '';
			} else {
				$forum['collapseimg'] = 'collapsed_yes.gif';
				$collapse['category_'.$forum['fid']] = 'display: none';
			}

			if($forum['moderators']) {
			 	$forum['moderators'] = moddisplay($forum['moderators'], 'flat');
			}
			$forum['forumscount'] 	= 0;
			$catlist[$forum['fid']] = $forum;
		}
	}

	foreach($catlist as  $catid => $category) {
		if($catlist[$catid]['forumscount'] && $category['forumcolumns']) {
			$catlist[$catid]['forumcolwidth'] = (floor(100 / $category['forumcolumns']) - 0.1).'%';
			$catlist[$catid]['endrows'] = '';
			if($colspan = $category['forumscount'] % $category['forumcolumns']) {
				while(($category['forumcolumns'] - $colspan) > 0) {
					$catlist[$catid]['endrows'] .= '<td>&nbsp;</td>';
					$colspan ++;
				}
				$catlist[$catid]['endrows'] .= '</tr>';
			}

		} elseif(empty($category['forumscount'])) {
			unset($catlist[$catid]);
		}
	}
	unset($catid, $category);

	if(isset($catlist[0]) && $catlist[0]['forumscount']) {
		$catlist[0]['fid'] = 0;
		$catlist[0]['type'] = 'group';
		$catlist[0]['name'] = $_G['setting']['bbname'];
		$catlist[0]['collapseimg'] = 'collapsed_no.gif';
	} else {
		unset($catlist[0]);
	}

	if($_G['setting']['whosonlinestatus'] == 1 || $_G['setting']['whosonlinestatus'] == 3) {
		$_G['setting']['whosonlinestatus'] = 1;

		$onlineinfo = explode("\t", $_G['cache']['onlinerecord']);
		if(empty($_G['cookie']['onlineusernum'])) {
			$onlinenum = DB::result_first("SELECT count(*) FROM ".DB::table('common_session'));
			if($onlinenum > $onlineinfo[0]) {
				$onlinerecord = "$onlinenum\t".TIMESTAMP;
				DB::query("UPDATE ".DB::table('common_setting')." SET svalue='$onlinerecord' WHERE skey='onlinerecord'");
				save_syscache('onlinerecord', $onlinerecord);
				$onlineinfo = array($onlinenum, TIMESTAMP);
			}
			dsetcookie('onlineusernum', intval($onlinenum), 300);
		} else {
			$onlinenum = intval($_G['cookie']['onlineusernum']);
		}
		$onlineinfo[1] = dgmdate($onlineinfo[1], 'd');

		$detailstatus = $showoldetails == 'yes' || (((!isset($_G['cookie']['onlineindex']) && !$_G['setting']['whosonline_contract']) || $_G['cookie']['onlineindex']) && $onlinenum < 500 && !$showoldetails);

		if($detailstatus) {
			$actioncode = lang('action');

			$_G['uid'] && updatesession();
			$membercount = $invisiblecount = 0;
			$whosonline = array();

			$_G['setting']['maxonlinelist'] = $_G['setting']['maxonlinelist'] ? $_G['setting']['maxonlinelist'] : 500;

			$query = DB::query("SELECT uid, username, groupid, invisible, lastactivity, fid FROM ".DB::table('common_session')." ".(isset($_G['cache']['onlinelist'][7]) ? '' : 'WHERE uid <> 0')." ORDER BY uid DESC LIMIT ".$_G['setting']['maxonlinelist']);
			while($online = DB::fetch($query)) {
				if($online['uid']) {
					$membercount ++;
					if($online['invisible']) {
						$invisiblecount++;
						continue;
					} else {
						$online['icon'] = !empty($_G['cache']['onlinelist'][$online['groupid']]) ? $_G['cache']['onlinelist'][$online['groupid']] : $_G['cache']['onlinelist'][0];
					}

				} else {
					$online['icon'] = $_G['cache']['onlinelist'][7];
					$online['username'] = $_G['cache']['onlinelist']['guest'];
				}

				$online['lastactivity'] = dgmdate($online['lastactivity'], 't');
				$whosonline[] = $online;
			}
			unset($actioncode, $online);

			if($onlinenum > $_G['setting']['maxonlinelist']) {
				$membercount = $discuz->session->onlinecount(1);
				$invisiblecount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_session')." WHERE invisible = '1'");
			}

			if($onlinenum < $membercount) {
				$onlinenum = $discuz->session->onlinecount(0);
				dsetcookie('onlineusernum', intval($onlinenum), 300);
			}

			$guestcount = $onlinenum - $membercount;

			$db = DB::object();
			$db->free_result($query);
			unset($online);
		}

	} else {
		$_G['setting']['whosonlinestatus'] = 0;
	}

	if(defined('FORUM_INDEX_PAGE_MEMORY') && !FORUM_INDEX_PAGE_MEMORY) {
		memory('set', 'forum_index_page_'.$_G['member']['groupid'], array(
			'catlist' => $catlist,
			'forumlist' => $forumlist,
			'sublist' => $sublist,
			'whosonline' => $whosonline,
			'onlinenum' => $onlinenum,
			'membercount' => $membercount,
			'guestcount' => $guestcount,
			'announcements' => $announcements,
			'threads' => $threads,
			'posts' => $posts,
			'todayposts' => $todayposts,
			'onlineinfo' => $onlineinfo,
			'announcepm' => $announcepm), getglobal('setting/memory/forumindex/ttl'));
	}

} else {
	require_once DISCUZ_ROOT.'./source/include/misc/misc_category.php';

}

$lastvisit = $lastvisit ? dgmdate($lastvisit, 'u') : 0;

include template('diy:forum/discuz');

function get_index_announcements() {
	global $_G;
	$announcements = '';
	if($_G['cache']['announcements']) {
		$readapmids = !empty($_G['cookie']['readapmid']) ? explode('D', $_G['cookie']['readapmid']) : array();
		foreach($_G['cache']['announcements'] as $announcement) {
			if(empty($announcement['groups']) || in_array($_G['member']['groupid'], $announcement['groups'])) {
				if(empty($announcement['type'])) {
					$announcements .= '<li><a href="forum.php?mod=announcement&id='.$announcement['id'].'" target="_blank">'.$announcement['subject'].
						'<em>('.dgmdate($announcement['starttime'], 'd').')</em></a></li>';
				} elseif($announcement['type'] == 1) {
					$announcements .= '<li><a href="'.$announcement['message'].'" target="_blank">'.$announcement['subject'].
						'<em>('.dgmdate($announcement['starttime'], 'd').')</em></a></li>';
				}
			}
		}
	}
	return $announcements;
}

function get_index_page_guest_cache() {
	global $_G;
	$indexcache = getcacheinfo(0);
	if(TIMESTAMP - $indexcache['filemtime'] > $_G['setting']['cacheindexlife']) {
		@unlink($indexcache['filename']);
		define('CACHE_FILE', $indexcache['filename']);
		$_G['setting']['styleid'] = $_G['cache']['settings']['styleid'];
		$styleid = $_G['setting']['styleid'];
	} elseif($indexcache['filename']) {
		@readfile($indexcache['filename']);
		$_G['setting']['debug'] && debuginfo();
		$_G['setting']['debug'] ? die('<script type="text/javascript">document.getElementById("debuginfo").innerHTML = " '.($_G['setting']['debug'] ? 'Updated at '.gmdate("H:i:s", $indexcache['filemtime'] + 3600 * 8).', Processed in '.$debuginfo['time'].' second(s), '.$debuginfo['queries'].' Queries'.($_G['gzipcompress'] ? ', Gzip enabled' : '') : '').'";</script>') : die();
	}
}

function get_index_memory_by_groupid($groupid) {
	$enable = getglobal('setting/memory/forumindex/enable');
	if($enable && memory('check')) {
		$ret = memory('get', 'forum_index_page_'.$groupid);
		define('FORUM_INDEX_PAGE_MEMORY', $ret ? 1 : 0);
		if($ret) {
			return $ret;
		}
	}
	return array('none' => null);
}

function get_index_online_details() {
	$showoldetails = getgpc('showoldetails');
	switch($showoldetails) {
		case 'no': dsetcookie('onlineindex', 0, 86400 * 365); break;
		case 'yes': dsetcookie('onlineindex', 1, 86400 * 365); break;
	}
	return $showoldetails;
}

function do_forum_bind_domains() {
	global $_G;
	if($_G['setting']['binddomains'] && $_G['setting']['forumdomains']) {
		$loadforum = isset($_G['setting']['binddomains'][$_SERVER['HTTP_HOST']]) ? max(0, intval($_G['setting']['binddomains'][$_SERVER['HTTP_HOST']])) : 0;
		if($loadforum) {
			dheader('Location: '.$_G['setting']['siteurl'].'/forum.php?mod=forumdisplay&fid='.$loadforum);
		}
	}
}

?>