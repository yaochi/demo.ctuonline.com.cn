<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_modcp.php 10853 2010-05-17 08:37:06Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('IN_MODCP', true);

if(!empty($_G['forum']) && $_G['forum']['status'] == 3) {
	showmessage('group_admin_enter_panel', 'forum.php?mod=group&action=manage&fid='.$_G['fid']);
}

require_once DISCUZ_ROOT . './source/admincp/admincp_cpanel.php';

$_G['gp_action'] = empty($_G['gp_action']) && $_G['fid'] ? 'thread' : $_G['gp_action'];
$op = getgpc('op');
$cpscript = basename($_G['PHP_SELF']);
$modsession = new AdminSession($_G['uid'], $_G['groupid'], $_G['adminid'], $_G['clientip']);

if($modsession->cpaccess == 1) {
	if($_G['gp_action'] == 'login' && $_G['gp_cppwd'] && submitcheck('submit')) {
		loaducenter();
		$ucresult = uc_user_login($_G['uid'], $_G['gp_cppwd'], 1);
		if($ucresult[0] > 0) {
			$modsession->errorcount = '-1';
			$url_forward = $modsession->get('url_forward');
			$modsession->clear(true);
			$url_forward && dheader("Location: $cpscript?mod=modcp&$url_forward");
			$_G['gp_action'] = 'home';
		} else{
			$modsession->errorcount ++;
			$modsession->update();
		}
	} else {
		$_G['gp_action'] = 'login';
	}
}

if($_G['gp_action'] == 'logout') {
	$modsession->destroy();
	showmessage('modcp_logout_succeed', 'forum.php');
}

$modforums = $modsession->get('modforums');
if($modforums === null) {
	$modforums = array('fids' => '', 'list' => array(), 'recyclebins' => array());
	$comma = '';
	if($_G['adminid'] == 3) {
		$query = DB::query("SELECT m.fid, f.name, f.recyclebin
				FROM ".DB::table('forum_moderator')." m
				LEFT JOIN ".DB::table('forum_forum')." f ON f.fid=m.fid
				WHERE m.uid='$_G[uid]' AND f.status='1' AND f.type<>'group'");
		while($tforum = DB::fetch($query)) {
			$modforums['fids'] .= $comma.$tforum['fid']; $comma = ',';
			$modforums['recyclebins'][$tforum['fid']] = $tforum['recyclebin'];
			$modforums['list'][$tforum['fid']] = strip_tags($tforum['name']);
		}
	} else {
		$sql = $_G['member']['accessmasks'] ?
			"SELECT f.fid, f.name, f.threads, f.recyclebin, ff.viewperm, a.allowview FROM ".DB::table('forum_forum')." f
				LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid
				LEFT JOIN ".DB::table('forum_access')." a ON a.uid='$_G[uid]' AND a.fid=f.fid
				WHERE f.status='1' AND ff.redirect=''"
			: "SELECT f.fid, f.name, f.threads, f.recyclebin, ff.viewperm, ff.redirect FROM ".DB::table('forum_forum')." f
				LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid)
				WHERE f.status='1' AND f.type<>'group' AND ff.redirect=''";
		$query = DB::query($sql);
		while ($tforum = DB::fetch($query)) {
			$tforum['allowview'] = !isset($tforum['allowview']) ? '' : $tforum['allowview'];
			if($tforum['allowview'] == 1 || ($tforum['allowview'] == 0 && ((!$tforum['viewperm'] && $_G['group']['readaccess']) || ($tforum['viewperm'] && forumperm($tforum['viewperm']))))) {
				$modforums['fids'] .= $comma.$tforum['fid']; $comma = ',';
				$modforums['recyclebins'][$tforum['fid']] = $tforum['recyclebin'];
				$modforums['list'][$tforum['fid']] = strip_tags($tforum['name']);
			}
		}
	}

	$modsession->set('modforums', $modforums, true);
}

if($_G['fid'] && $_G['forum']['ismoderator']) {
	dsetcookie('modcpfid', $_G['fid']);
	$forcefid = "&amp;fid=$_G[fid]";
} elseif(!empty($modforums) && count($modforums['list']) == 1) {
	$forcefid = "&amp;fid=$modforums[fids]";
} else {
	$forcefid = '';
}

$script = $modtpl = '';
switch ($_G['gp_action']) {

	case 'announcement':
		$_G['group']['allowpostannounce'] && $script = 'announcement';
		break;

	case 'member':
		$op == 'edit' && $_G['group']['allowedituser'] && $script = 'member';
		$op == 'ban' && $_G['group']['allowbanuser'] && $script = 'member';
		$op == 'ipban' && $_G['group']['allowbanip'] && $script = 'member';
		break;

	case 'report':
		$_G['group']['allowviewreport'] && $script = 'report';
		break;

	case 'moderate':
		($op == 'threads' || $op == 'replies') && $_G['group']['allowmodpost'] && $script = 'moderate';
		$op == 'members' && $_G['group']['allowmoduser'] && $script = 'moderate';
		break;

	case 'forum':
		$op == 'editforum' && $_G['group']['alloweditforum'] && $script = 'forum';
		$op == 'recommend' && $_G['group']['allowrecommendthread'] && $script = 'forum';
		break;

	case 'forumaccess':
		$_G['group']['allowedituser'] && $script = 'forumaccess';
		break;

	case 'log':
		$_G['group']['allowviewlog'] && $script = 'log';
		break;

	case 'login':
		$script = $modsession->cpaccess == 1 ? 'login' : 'home';
		break;

	case 'thread':
		$script = 'thread';
		break;

	case 'recyclebin':
		$script = 'recyclebin';
		break;

	case 'stat':
		$script = 'stat';
		break;

	case 'plugin':
		$script = 'plugin';
		break;

	default:
		$_G['gp_action'] = $script = 'home';
		$modtpl = 'modcp_home';
}

$script = empty($script) ? 'noperm' : $script;
$modtpl = empty($modtpl) ? (!empty($script) ? 'modcp_'.$script : '') : $modtpl;
$modtpl = 'forum/' . $modtpl;
$op = isset($op) ? trim($op) : '';

if($script != 'log') {
	include libfile('function/misc');
	$extra = implodearray(array('GET' => $_GET, 'POST' => $_POST), array('cppwd', 'formhash', 'submit', 'addsubmit'));
	$modcplog = array(TIMESTAMP, $_G['username'], $_G['adminid'], $_G['clientip'], $_G['gp_action'], $op, $_G['fid'], $extra);
	writelog('modcp', implode("\t", clearlogstring($modcplog)));
}

require DISCUZ_ROOT.'./source/include/modcp/modcp_'.$script.'.php';

$reportnum = $modpostnum = $modthreadnum = $modforumnum = 0;
$modforumnum = count($modforums['list']);
if($modforumnum) {
	$modnum = ($_G['group']['allowmodpost'] ? (getcountofposts(DB::table('forum_post'), "invisible='-2' AND first='0' and fid IN($modforums[fids])") +
		DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_thread')." WHERE fid IN($modforums[fids]) AND displayorder='-2'")) : 0) +
		($_G['group']['allowmoduser'] ? DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member_validate')." WHERE status='0'") : 0);
}

switch($_G['adminid']) {
	case 1: $access = '1,2,3,4,5,6,7'; break;
	case 2: $access = '2,3,6,7'; break;
	default: $access = '1,3,5,7'; break;
}
$notenum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_adminnote')." WHERE access IN ($access)");

include template('forum/modcp');

?>