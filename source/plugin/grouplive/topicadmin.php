<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_moderation.php 11096 2010-05-24 00:33:26Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!empty($_G['tid'])) {
	$_G['gp_moderate'] = array($_G['tid']);
}

$_G['gp_handlekey'] = 'mods';

require_once libfile('function/misc');

$allow_operation = array('delete', 'highlight', 'open', 'close', 'stick', 'digest', 'bump', 'down', 'recommend', 'type', 'move', 'recommend_group');

$operations = empty($_G['gp_operations']) ? array() : $_G['gp_operations'];
if($operations && $operations != array_intersect($operations, $allow_operation) || (!$_G['group']['allowdelpost'] && in_array('delete', $operations)) || (!$_G['group']['allowstickthread'] && in_array('stick', $operations))) {
	showmessage('admin_moderate_invalid');
}

$threadlist = $loglist = array();
$operation = getgpc('operation');
loadcache('threadtableids');
$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();
if(!in_array(0, $threadtableids)) {
	$threadtableids = array_merge(array(0), $threadtableids);
}
if($tids = dimplode($_G['gp_moderate'])) {
	$query = DB::query("SELECT * FROM ".DB::table("group_live")." WHERE liveid IN ($tids) AND fid='$_G[fid]' AND displayorder>='0' AND digest>='0' LIMIT $_G[tpp]");
	while($thread = DB::fetch($query)) {
//		if($thread['closed'] > 1 && $operation && !in_array($operation, array('delete', 'highlight', 'stick', 'digest', 'bump', 'down'))) {
//			continue;
//		}
//		$thread['lastposterenc'] = rawurlencode($thread['lastposter']);
//		$thread['dblastpost'] = $thread['lastpost'];
//		$thread['lastpost'] = dgmdate($thread['lastpost'], 'u');
		$thread['id'] = $thread['liveid'];
		$threadlist[$thread['id']] = $thread;
		$_G['tid'] = empty($_G['tid']) ? $thread['id'] : $_G['tid'];
	}
}

if(empty($threadlist)) {
	showmessage('admin_moderate_invalid');
}

$modpostsnum = count($threadlist);
$single = $modpostsnum == 1 ? TRUE : FALSE;
$frommodcp = getgpc('frommodcp');
switch($frommodcp) {
	case '1':
		$_G['referer'] = "forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=grouplive&plugin_op=groupmenu";
		break;
	case '2':
		$_G['referer'] = "forum.php?mod=modcp&action=forum&op=recommend".(getgpc('show') ? "&show=getgpc('show')" : '')."&fid=$_G[fid]";
		break;
	default:
		$_G['referer'] = "forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=grouplive&plugin_op=groupmenu";
		break;
}


$optgroup = $_G['gp_optgroup'] = isset($_G['gp_optgroup']) ? intval($_G['gp_optgroup']) : 0;
$listextra = $_G['gp_listextra'] = getgpc('listextra');
$expirationstick = getgpc('expirationstick');

$defaultcheck = array();
foreach ($allow_operation as $v) {
	$defaultcheck[$v] = '';
}
$defaultcheck[$operation] = 'checked="checked"';

if(!submitcheck('modsubmit')) {

	$stickcheck  = $closecheck = $digestcheck = array('', '', '', '', '');
	$expirationdigest = $expirationhighlight = $expirationclose = '';

	if($_G['gp_optgroup'] == 1 && $single) {
		empty($threadlist[$_G['tid']]['displayorder']) ? $stickcheck[0] ='selected="selected"' : $stickcheck[$threadlist[$_G['tid']]['displayorder']] = 'selected="selected"';
		empty($threadlist[$_G['tid']]['digest']) ? $digestcheck[0] = 'selected="selected"' : $digestcheck[$threadlist[$_G['tid']]['digest']] = 'selected="selected"';
		$string = sprintf('%02d', $threadlist[$_G['tid']]['highlight']);
		$stylestr = sprintf('%03b', $string[0]);
		for($i = 1; $i <= 3; $i++) {
			$stylecheck[$i] = $stylestr[$i - 1] ? 1 : 0;
		}
		$colorcheck = $string[1];
		$_G['forum']['modrecommend'] = is_array($_G['forum']['modrecommend']) ? $_G['forum']['modrecommend'] : array();
	} elseif($_G['gp_optgroup'] == 2) {
		require_once libfile('function/forumlist');
		$forumselect = forumselect(FALSE, 0, $single ? $threadlist[$_G['tid']]['fid'] : 0);
		$typeselect = grouptypeselect(0, 'grouplive');
	} elseif($_G['gp_optgroup'] == 4 && $single) {
		empty($threadlist[$_G['tid']]['closed']) ? $closecheck[0] = 'checked="checked"' : $closecheck[1] = 'checked="checked"';
	}

	$imgattach = array();
	if(count($threadlist) == 1 && $operation == 'recommend') {
		$query = DB::query("SELECT a.*, af.description FROM ".DB::table('forum_attachment')." a LEFT JOIN ".DB::table('forum_attachmentfield')." af ON a.aid=af.aid WHERE a.tid='$_G[tid]' AND a.isimage IN ('1', '-1')");
		while($row = DB::fetch($query)) {
			$imgattach[] = $row;
		}
		$query = DB::query("SELECT * FROM ".DB::table('forum_forumrecommend')." WHERE tid='$_G[tid]'");
		if($oldthread = DB::fetch($query)) {
			$threadlist[$_G['tid']]['subject'] = $oldthread['subject'];
			$selectposition[$oldthread['position']] = ' selected="selected"';
			$selectattach = $oldthread['aid'];
		} else {
			$selectattach = $imgattach[0]['aid'];
			$selectposition[0] = ' selected="selected"';
		}
	}
	
	include template('grouplive:topicadmin');
	exit();
} else {
	
	$moderatetids = dimplode(array_keys($threadlist));
	$reason = checkreasonpm();
	$stampstatus = 0;
	$stampaction = 'SPA';
	if(empty($operations)) {
		showmessage('admin_nonexistence');
	} else {
		$posts = $images = array();
		foreach($operations as $operation) {

//			if(in_array($operation, array('stick', 'highlight', 'digest', 'recommend'))) {
//				if(empty($posts)) {
//					$postlist = getfieldsofposts('*', "tid IN ($moderatetids) AND first='1'");
//					foreach($postlist as $post) {
//						$post['message'] = messagecutstr($post['message'], 200);
//						$posts[$post['tid']] = $post;
//					}
//				}
//			}

			$updatemodlog = TRUE;
			if($operation == 'stick') {
				$displayorder = intval($_G['gp_sticklevel']);
				if($displayorder < 0 || $displayorder > 3 || $displayorder > $_G['group']['allowdigestthread']) {
					showmessage('undefined_action');
				}
				
				$query2 = DB::query("SELECT * FROM ".DB::table('group_live')." WHERE liveid IN ($moderatetids)");
				while($value = DB::fetch($query2)){
					//相同不做
					if($displayorder == $value['displayorder'])
						continue;
						
					if($displayorder){
						foreach($threadlist as $live){
							feed_notify_stick_live($live);
						}
					}
					else{
						foreach($threadlist as $live){
							feed_notify_stick_no_live($live);
						}
					}
				}
				
				DB::query("UPDATE ".DB::table('group_live')." SET displayorder='$displayorder', moderated='1' WHERE liveid IN ($moderatetids)");
				
			} elseif($operation == 'highlight') {
				if(!$_G['group']['allowhighlightthread']) {
					showmessage('undefined_action');
				}
				$highlight_style = $_G['gp_highlight_style'];
				$highlight_color = $_G['gp_highlight_color'];
				$expiration = checkexpiration($_G['gp_expirationhighlight'], $operation);
				$stylebin = '';
				for($i = 1; $i <= 3; $i++) {
					$stylebin .= empty($highlight_style[$i]) ? '0' : '1';
				}

				$highlight_style = bindec($stylebin);
				if($highlight_style < 0 || $highlight_style > 7 || $highlight_color < 0 || $highlight_color > 8) {
					showmessage('undefined_action', NULL);
				}

				DB::query("UPDATE ".DB::table('group_live')." SET highlight='$highlight_style$highlight_color', moderated='1' WHERE liveid IN ($moderatetids)", 'UNBUFFERED');
				
				$modaction = ($highlight_style + $highlight_color) ? ($expiration ? 'EHL' : 'HLT') : 'UHL';
				$expiration = $modaction == 'UHL' ? 0 : $expiration;
				
			} elseif($operation == 'digest') {
				
				$digestlevel = intval($_G['gp_digestlevel']);
				if($digestlevel < 0 || $digestlevel > 3 || $digestlevel > $_G['group']['allowdigestthread']) {
					showmessage('undefined_action');
				}
				$expiration = checkexpiration($_G['gp_expirationdigest'], $operation);
				$expirationdigest = $digestlevel ? $expirationdigest : 0;

				$query2 = DB::query("SELECT * FROM ".DB::table('group_live')." WHERE liveid IN ($moderatetids)");
				while($value = DB::fetch($query2)){
					//相同不做
					if($digestlevel == $value['digest'])
						continue;
						
					if($digestlevel){
						foreach($threadlist as $live){
							feed_notify_digest_live($live);
						}
					}
					else{
						foreach($threadlist as $live){
							feed_notify_digest_no_live($live);
						}
					}
				}

				DB::query("UPDATE ".DB::table('group_live')." SET digest='$digestlevel', moderated='1' WHERE liveid IN ($moderatetids)");

//				foreach($threadlist as $thread) {
//					if($thread['digest'] != $digestlevel) {
//						if($digestlevel == $thread['digest']) continue;
//						$extsql = array();
//						if($digestlevel > 0 && $thread['digest'] == 0) {
//							$extsql = array('digestposts' => 1);
//						}
//						if($digestlevel == 0 && $thread['digest'] > 0) {
//							$extsql = array('digestposts' => -1);
//						}
//						if($digestlevel == 0) {
//							$stampaction = 'SPD';
//						}
//						updatecreditbyaction('digest', $thread['authorid'], $extsql, '', $digestlevel - $thread['digest']);
//					}
//				}
//
//				$modaction = $digestlevel ? ($expiration ? 'EDI' : 'DIG') : 'UDG';
//				$stampstatus = 2;
//				//fix bug
//				require_once libfile('function/group');
//				delgroupcache($_G["fid"], "digest");
			} elseif($operation == 'delete') {
				if(!$_G['group']['allowdelpost']) {
					showmessage('undefined_action');
				}
				deletelives(array_keys($threadlist));

			} elseif($operation == 'close') {
				if(!$_G['group']['allowclosethread']) {
					showmessage('undefined_action');
				}
				$expiration = checkexpiration($_G['gp_expirationclose'], $operation);
				$modaction = $expiration ? 'ECL' : 'CLS';

				DB::query("UPDATE ".DB::table('forum_thread')." SET closed='1', moderated='1' WHERE tid IN ($moderatetids)");
				DB::query("UPDATE ".DB::table('forum_threadmod')." SET status='0' WHERE tid IN ($moderatetids) AND action IN ('CLS','OPN','ECL','UCL','EOP','UEO')", 'UNBUFFERED');
			} elseif($operation == 'open') {
				if(!$_G['group']['allowclosethread']) {
					showmessage('undefined_action');
				}
				$expiration = checkexpiration($_G['gp_expirationopen'], $operation);
				$modaction = $expiration ? 'EOP' : 'OPN';

				DB::query("UPDATE ".DB::table('forum_thread')." SET closed='0', moderated='1' WHERE tid IN ($moderatetids)");
				DB::query("UPDATE ".DB::table('forum_threadmod')." SET status='0' WHERE tid IN ($moderatetids) AND action IN ('CLS','OPN','ECL','UCL','EOP','UEO')", 'UNBUFFERED');
			} elseif($operation == 'move') {
				if(!$_G['group']['allowmovethread']) {
					showmessage('undefined_action');
				}
				$moveto = $_G['gp_moveto'];
				$toforum = DB::fetch_first("SELECT f.fid, f.name, f.modnewposts, f.allowpostspecial, ff.threadplugin FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid WHERE f.fid='$moveto' AND f.status='1' AND f.type<>'group'");

				if(!$toforum) {
					showmessage('admin_move_invalid');
				} elseif($_G['fid'] == $toforum['fid']) {
					continue;
				} else {
					$moveto = $toforum['fid'];
					$modnewthreads = (!$_G['group']['allowdirectpost'] || $_G['group']['allowdirectpost'] == 1) && $toforum['modnewposts'] ? 1 : 0;
					$modnewreplies = (!$_G['group']['allowdirectpost'] || $_G['group']['allowdirectpost'] == 2) && $toforum['modnewposts'] ? 1 : 0;
					if($modnewthreads || $modnewreplies) {
						showmessage('admin_move_have_mod');
					}
				}

				if($_G['adminid'] == 3) {
					if($_G['member']['accessmasks']) {
						$accessadd1 = ', a.allowview, a.allowpost, a.allowreply, a.allowgetattach, a.allowpostattach';
						$accessadd2 = "LEFT JOIN ".DB::table('forum_access')." a ON a.uid='$_G[uid]' AND a.fid='$moveto'";
					}
					$priv = DB::fetch_first("SELECT ff.postperm, m.uid AS istargetmod $accessadd1
							FROM ".DB::table('forum_forumfield')." ff
							$accessadd2
							LEFT JOIN ".DB::table('forum_moderator')." m ON m.fid='$moveto' AND m.uid='$_G[uid]'
							WHERE ff.fid='$moveto'");
					if((($priv['postperm'] && !in_array($_G['groupid'], explode("\t", $priv['postperm']))) || ($_G['member']['accessmasks'] && ($priv['allowview'] || $priv['allowreply'] || $priv['allowgetattach'] || $priv['allowpostattach']) && !$priv['allowpost'])) && !$priv['istargetmod']) {
						showmessage('admin_move_nopermission');
					}
				}

				$moderate = array();
				$stickmodify = 0;
				$toforumallowspecial = array(
					1 => $toforum['allowpostspecial'] & 1,
					2 => $toforum['allowpostspecial'] & 2,
					3 => isset($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]]) && ($toforum['allowpostspecial'] & 4),
					4 => $toforum['allowpostspecial'] & 8,
					5 => $toforum['allowpostspecial'] & 16,
					127 => $_G['setting']['threadplugins'] ? unserialize($toforum['threadplugin']) : array(),
				);
				foreach($threadlist as $tid => $thread) {
					$allowmove = 0;
					if(!$thread['special']) {
						$allowmove = 1;
					} else {
						if($thread['special'] != 127) {
							$allowmove = $toforum['allowpostspecial'] ? $toforumallowspecial[$thread['special']] : 0;
						} else {
							if($toforumallowspecial[127]) {
								$posttable = getposttablebytid($thread['tid']);
								$message = DB::result_first("SELECT message FROM ".DB::table($posttable)." WHERE tid='$thread[tid]' AND first='1'");
								$sppos = strrpos($message, chr(0).chr(0).chr(0));
								$specialextra = substr($message, $sppos + 3);
								$allowmove = in_array($specialextra, $toforumallowspecial[127]);
							} else {
								$allowmove = 0;
							}
						}
					}

					if($allowmove) {
						$moderate[] = $tid;
						if(in_array($thread['displayorder'], array(2, 3))) {
							$stickmodify = 1;
						}
						if($_G['gp_type'] == 'redirect') {
							$thread = daddslashes($thread, 1);
							DB::query("INSERT INTO ".DB::table('forum_thread')." (fid, readperm, author, authorid, subject, dateline, lastpost, lastposter, views, replies, displayorder, digest, closed, special, attachment)
								VALUES ('$thread[fid]', '$thread[readperm]', '".addslashes($thread['author'])."', '$thread[authorid]', '".addslashes($thread['subject'])."', '$thread[dateline]', '$thread[dblastpost]', '".addslashes($thread['lastposter'])."', '0', '0', '0', '0', '$thread[tid]', '0', '0')");
						}
					}
				}

				if(!$moderatetids = implode(',', $moderate)) {
					showmessage('admin_moderate_invalid');
				}

				$displayorderadd = $_G['adminid'] == 3 ? ', displayorder=\'0\'' : '';
				DB::query("UPDATE ".DB::table('forum_thread')." SET fid='$moveto', moderated='1', isgroup='0' $displayorderadd WHERE tid IN ($moderatetids)");
				updatepost(array('fid' => $moveto), "tid IN ($moderatetids)");

				if($_G['setting']['globalstick'] && $stickmodify) {
					require_once libfile('function/cache');
					updatecache('globalstick');
				}
				$modaction = 'MOV';

				updateforumcount($moveto);
				updateforumcount($_G['fid']);
			} elseif($operation == 'type') {
				require_once libfile("function/category");
				$pluginid = $_GET["plugin_name"];
				$allowedittype = common_category_is_enable($_G['fid'], $pluginid);
				$categorys = array();
				if($allowedittype){
					$categorys = common_category_get_category($_G['fid'], $pluginid);
					
					$allowrequired = common_category_is_required($_G['fid'], $pluginid);
					if(!$allowrequired)
						$categorys[0] = array('name' => '全部');
				}

				if(!$allowedittype) {
					showmessage('undefined_action');
				}
				if(!isset($categorys[$_G['gp_typeid']])) {
					showmessage('admin_type_invalid');
				}

				DB::query("UPDATE ".DB::table('group_live')." SET typeid='$_G[gp_typeid]', moderated='1' WHERE liveid IN ($moderatetids)");
				
				foreach($threadlist as $live){
					feed_notify_type_live($live, $categorys[$live['typeid']], $categorys[$_G['gp_typeid']]);
				}
				
				$modaction = 'TYP';
			} elseif($operation == 'recommend_group') {
				if($_G['forum']['status'] != 3 || !in_array($_G['adminid'], array(1, 2))) {
					showmessage('undefined_action');
				}
				$moveto = $_G['gp_moveto'];
				$toforum = DB::fetch_first("SELECT f.fid, f.name, f.modnewposts, f.allowpostspecial, ff.threadplugin FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid WHERE f.fid='$moveto' AND f.status='1' AND f.type<>'group'");

				if(!$toforum) {
					showmessage('admin_move_invalid');
				} elseif($_G['fid'] == $toforum['fid']) {
					continue;
				}
				$moderate = array();
				$toforumallowspecial = array(
					1 => $toforum['allowpostspecial'] & 1,
					2 => $toforum['allowpostspecial'] & 2,
					3 => isset($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]]) && ($toforum['allowpostspecial'] & 4),
					4 => $toforum['allowpostspecial'] & 8,
					5 => $toforum['allowpostspecial'] & 16,
					127 => $_G['setting']['threadplugins'] ? unserialize($toforum['threadplugin']) : array(),
				);
				foreach($threadlist as $tid => $thread) {
					$allowmove = 0;
					if($thread['closed']) {
						continue;
					}
					if(!$thread['special']) {
						$allowmove = 1;
					} else {
						if($thread['special'] != 127) {
							$allowmove = $toforum['allowpostspecial'] ? $toforumallowspecial[$thread['special']] : 0;
						} else {
							if($toforumallowspecial[127]) {
								$posttable = getposttablebytid($thread['tid']);
								$message = DB::result_first("SELECT message FROM ".DB::table($posttable)." WHERE tid='$thread[tid]' AND first='1'");
								$sppos = strrpos($message, chr(0).chr(0).chr(0));
								$specialextra = substr($message, $sppos + 3);
								$allowmove = in_array($specialextra, $toforumallowspecial[127]);
							} else {
								$allowmove = 0;
							}
						}
					}

					if($allowmove) {
						$moderate[] = $tid;
						$thread = daddslashes($thread, 1);
						DB::query("INSERT INTO ".DB::table('forum_thread')." (fid, readperm, author, authorid, subject, dateline, lastpost, lastposter, views, replies, displayorder, digest, closed, special, attachment, isgroup)
							VALUES ('$moveto', '$thread[readperm]', '".addslashes($thread['author'])."', '$thread[authorid]', '".addslashes($thread['subject'])."', '$thread[dateline]', '".TIMESTAMP."', '".addslashes($thread['lastposter'])."', '$thread[views]', '$thread[replies]', '0', '$thread[digest]', '$thread[tid]', '$thread[special]', '$thread[attachment]', '$thread[isgroup]')");
						$newtid = DB::insert_id();
						DB::query("UPDATE ".DB::table('forum_thread')." SET closed='$newtid' WHERE tid='$thread[tid]'");
					}
				}
				if(!$moderatetids = implode(',', $moderate)) {
					showmessage('admin_succeed', $_G['referer']);
				}
				$modaction = 'REG';
			}

//			if(in_array($operation, array('stick', 'highlight', 'digest', 'bump', 'down', 'delete', 'move', 'close', 'open'))) {
//
//				foreach($_G['gp_moderate'] as $tid) {
//					if(!$tid = max(0, intval($tid))) continue;
//					$my_opt = $operation == 'stick' ? 'sticky' : $operation;
//					$data = array('tid' => $tid);
//					if($my_opt == 'move') $data['otherid'] = $toforum['fid'];
//
//					my_thread_log($my_opt, $data);
//				}
//			}
//
//			if($updatemodlog) {
//				updatemodlog($moderatetids, $modaction, $expiration);
//			}
//
//			updatemodworks($modaction, $modpostsnum);
//			foreach($threadlist as $thread) {
//				modlog($thread, $modaction);
//			}
//
//			if($sendreasonpm) {
//				$modactioncode = lang('forum/modaction');
//				$modaction = $modactioncode[$modaction];
//				foreach($threadlist as $thread) {
//					if($operation == 'move') {
//						sendreasonpm($thread, 'reason_move', array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => stripslashes($reason), 'tofid' => $toforum['fid'], 'toname' => $toforum['name']));
//					} else {
//						sendreasonpm($thread, 'reason_moderate', array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => stripslashes($reason)));
//					}
//				}
//			}
//
//			procreportlog($moderatetids, '', $operation == 'delete');
//
//			if($stampstatus) {
//				set_stamp($stampstatus, $stampaction, $threadlist, $expiration);
//			}

		}
		showmessage('admin_succeed', $_G['referer']);
	}

}

function checkexpiration($expiration, $operation) {
	global $_G;
	if(!empty($expiration) && in_array($operation, array('recommend', 'stick', 'digest', 'highlight', 'close'))) {
		$expiration = strtotime($expiration) - $_G['setting']['timeoffset'] * 3600 + date('Z');
		if(dgmdate($expiration, 'Ymd') <= dgmdate(TIMESTAMP, 'Ymd') || ($expiration > TIMESTAMP + 86400 * 180)) {
			showmessage('admin_expiration_invalid');
		}
	} else {
		$expiration = 0;
	}
	return $expiration;
}

function set_stamp($typeid, $stampaction, &$threadlist, $expiration) {
	global $_G;
	$moderatetids = dimplode(array_keys($threadlist));
	if(empty($threadlist)) {
		return false;
	}
	if(array_key_exists($typeid, $_G['cache']['stamptypeid'])) {
		DB::query("UPDATE ".DB::table('forum_thread')." SET ".buildbitsql('status', 5, TRUE)." WHERE tid IN ($moderatetids)");
		if($stampaction == 'SPD') {
			$stamptids = array();
			foreach($threadlist as $stamptid => $thread) {
				$currentstamp = DB::fetch_first("SELECT * FROM ".DB::table('forum_threadmod')." WHERE tid='$stamptid' AND status='1' ORDER BY dateline DESC LIMIT 1");
				if(!empty($currentstamp) && $currentstamp['stamp'] == $_G['cache']['stamptypeid'][$typeid]) {
					$stamptids[] = $stamptid;
				}
			}
			if(!empty($stamptids)) {
				$moderatetids = dimplode($stamptids);
			} else {
				$moderatetids = '';
			}
		}
		!empty($moderatetids) && updatemodlog($moderatetids, $stampaction, $expiration, 0, $_G['cache']['stamptypeid'][$typeid]);
	}
}

function grouptypeselect($curtypeid = 0, $pluginid) {
	global $_G;
	require_once libfile("function/category");
	$threadtypes = common_category_get_category($_G['fid'], $pluginid);
	if($threadtypes) {
		$html = '<select name="typeid" id="typeid">';
		$allowrequired = common_category_is_required($_G['fid'], 'grouplive');
		if(!$allowrequired)
			$html .='<option value="0">&nbsp;</option>';

		foreach($threadtypes as $typeid => $value) {
			$html .= '<option value="'.$typeid.'" '.($curtypeid == $typeid ? 'selected' : '').'>'.strip_tags($value['name']).'</option>';
		}
		$html .= '</select>';
		return $html;
	} else {
		return '';
	}
}

//精华动态 & 通知
function feed_notify_digest_live($live){
	global $_G;
	require_once libfile("function/feed");
	if($live){
		$feed['icon'] = 'live';
		$feed['id'] = $live['live'];
		$feed['idtype'] = 'gliveid';
		$feed['uid'] = $live['uid'];
		$feed['username'] = $live['username'];
		$feed['dateline'] = $live['dateline'];
		
		$url = join_plugin_action2("livecp", array('id' => $live['liveid'], 'op' => 'join'));
		
		$feed['title_template'] = 'feed_digest_grouplive_title';
		$feed['body_template'] = 'feed_digest_grouplive_body';
		$feed['body_data'] = array(
			'title' => "<a href=\"$url\">$live[subject]</a>",
		);
		$feed = mkfeed($feed);
		
		feed_add($feed['icon'], $feed['title_template'], $feed['title_data'], $feed['body_template'], $feed['body_data'], $feed['body_general'], array($feed['image_1']), array($feed['image_1_link']), $feed['target_ids'], $feed['friend'], $feed['appid'], $feed['returnid'], $feed['id'], $feed['idtype'], $feed['uid'], $feed['username'],$_G['fid']);
		
		if($live['uid'] != $_G['uid']){
			$note_type = 'glive';
			$note = 'live_digest';
			$forumname = $_G['forum']['name'];
			$note_values = array('group' => "<a href=\"forum.php?mod=group&fid=$_G[fid]\" target=\"_blank\">$forumname</a>", 'subject'=>"<a href=\"$url\" target=\"_blank\">$live[subject]</a>", 'actor' => "<a href=\"home.php?mod=space&uid=$live[uid]\" target=\"_blank\">$live[username]</a>");
			
			notification_add($live['uid'], $note_type, $note, $note_values);
		}
		
		//经验值
		require_once libfile('function/group');
		group_add_empirical_by_setting($live['uid'], $live['fid'], 'topic_competitive', $live[liveid]);
	
		//积分
		require_once libfile('function/credit');
		credit_create_credit_log($live['uid'], 'digest', $live['liveid']);
	}
}
//取消精华动态 & 通知
function feed_notify_digest_no_live($live){
	global $_G;
	require_once libfile("function/feed");
	if($live){
		$feed['icon'] = 'live';
		$feed['id'] = $live['live'];
		$feed['idtype'] = 'gliveid';
		$feed['uid'] = $live['uid'];
		$feed['username'] = $live['username'];
		$feed['dateline'] = $live['dateline'];
		
		$url = join_plugin_action2("livecp", array('id' => $live['liveid'], 'op' => 'join'));
		
		$feed['title_template'] = 'feed_digest_no_grouplive_title';
		$feed['body_template'] = 'feed_digest_no_grouplive_body';
		$feed['body_data'] = array(
			'title' => "<a href=\"$url\">$live[subject]</a>",
		);
		$feed = mkfeed($feed);
		
		feed_add($feed['icon'], $feed['title_template'], $feed['title_data'], $feed['body_template'], $feed['body_data'], $feed['body_general'], array($feed['image_1']), array($feed['image_1_link']), $feed['target_ids'], $feed['friend'], $feed['appid'], $feed['returnid'], $feed['id'], $feed['idtype'], $feed['uid'], $feed['username'],$_G['fid']);
		
		if($live['uid'] != $_G['uid']){
			$note_type = 'glive';
			$note = 'live_digest_no';
			$forumname = $_G['forum']['name'];
			$note_values = array('group' => "<a href=\"forum.php?mod=group&fid=$_G[fid]\" target=\"_blank\">$forumname</a>", 'subject'=>"<a href=\"$url\" target=\"_blank\">$live[subject]</a>", 'actor' => "<a href=\"home.php?mod=space&uid=$live[uid]\" target=\"_blank\">$live[username]</a>");
			
			notification_add($live['uid'], $note_type, $note, $note_values);
		}
	}
}
//置顶动态 & 通知
function feed_notify_stick_live($live){
	global $_G;
	require_once libfile("function/feed");
	if($live){
		$feed['icon'] = 'live';
		$feed['id'] = $live['id'];
		$feed['idtype'] = 'gliveid';
		$feed['uid'] = $live['uid'];
		$feed['username'] = $live['username'];
		$feed['dateline'] = $live['uploadtime'];
		
		$url = join_plugin_action2("livecp", array('id' => $live['liveid'], 'op' => 'join'));
		
		$feed['title_template'] = 'feed_stick_grouplive_title';
		$feed['body_template'] = 'feed_stick_grouplive_body';
		$feed['body_data'] = array(
			'title' => "<a href=\"$url\">$live[subject]</a>",
		);
		$feed = mkfeed($feed);
		
		feed_add($feed['icon'], $feed['title_template'], $feed['title_data'], $feed['body_template'], $feed['body_data'], $feed['body_general'], array($feed['image_1']), array($feed['image_1_link']), $feed['target_ids'], $feed['friend'], $feed['appid'], $feed['returnid'], $feed['id'], $feed['idtype'], $feed['uid'], $feed['username'],$_G['fid']);
		
		if($live['uid'] != $_G['uid']){
			$note_type = 'glive';
			$note = 'live_stick';
			$forumname = $_G['forum']['name'];
			$note_values = array('group' => "<a href=\"forum.php?mod=group&fid=$_G[fid]\" target=\"_blank\">$forumname</a>", 'subject'=>"<a href=\"$url\" target=\"_blank\">$live[subject]</a>", 'actor' => "<a href=\"home.php?mod=space&uid=$live[uid]\" target=\"_blank\">$live[username]</a>");
			
			notification_add($live['uid'], $note_type, $note, $note_values);
		}
		
		//经验值
		require_once libfile('function/group');
		group_add_empirical_by_setting($live['uid'], $live['fid'], 'topic_top', $live[liveid]);
	
		//积分
		require_once libfile('function/credit');
		credit_create_credit_log($live['uid'], 'top', $live['liveid']);
	}
}

//取消置顶动态 & 通知
function feed_notify_stick_no_live($live){
	global $_G;
	require_once libfile("function/feed");
	if($live){
		$feed['icon'] = 'live';
		$feed['id'] = $live['id'];
		$feed['idtype'] = 'gliveid';
		$feed['uid'] = $live['uid'];
		$feed['username'] = $live['username'];
		$feed['dateline'] = $live['uploadtime'];
		
		$url = join_plugin_action2("livecp", array('id' => $live['liveid'], 'op' => 'join'));
		
		$feed['title_template'] = 'feed_stick_no_grouplive_title';
		$feed['body_template'] = 'feed_stick_no_grouplive_body';
		$feed['body_data'] = array(
			'title' => "<a href=\"$url\">$live[subject]</a>",
		);
		$feed = mkfeed($feed);
		
		feed_add($feed['icon'], $feed['title_template'], $feed['title_data'], $feed['body_template'], $feed['body_data'], $feed['body_general'], array($feed['image_1']), array($feed['image_1_link']), $feed['target_ids'], $feed['friend'], $feed['appid'], $feed['returnid'], $feed['id'], $feed['idtype'], $feed['uid'], $feed['username'],$_G['fid']);
		
		if($live['uid'] != $_G['uid']){
			$note_type = 'glive';
			$note = 'live_stick_no';
			$forumname = $_G['forum']['name'];
			$note_values = array('group' => "<a href=\"forum.php?mod=group&fid=$_G[fid]\" target=\"_blank\">$forumname</a>", 'subject'=>"<a href=\"$url\" target=\"_blank\">$live[subject]</a>", 'actor' => "<a href=\"home.php?mod=space&uid=$live[uid]\" target=\"_blank\">$live[username]</a>");
			
			notification_add($live['uid'], $note_type, $note, $note_values);
		}
	}
}

//分类移动 动态& 通知
function feed_notify_type_live($live, $type1, $type2){
	global $_G;
	require_once libfile("function/feed");
	if($live){
		if($live['uid'] != $_G['uid']){
			if($type1[id]){
				$note_type = 'glive';
				$note = 'live_type';
				$forumname = $_G['forum']['name'];
				$note_values = array('group' => "<a href=\"forum.php?mod=group&fid=$_G[fid]\" target=\"_blank\">$forumname</a>", 'subject'=>"<a href=\"$url\" target=\"_blank\">$live[subject]</a>", 'actor' => "<a href=\"home.php?mod=space&uid=$live[uid]\" target=\"_blank\">$live[username]</a>", 'type1' => $type1['name'], 'type2' => $type2['name']);
			}else{
				$note_type = 'glive';
				$note = 'live_type_no';
				$forumname = $_G['forum']['name'];
				$note_values = array('group' => "<a href=\"forum.php?mod=group&fid=$_G[fid]\" target=\"_blank\">$forumname</a>", 'subject'=>"<a href=\"$url\" target=\"_blank\">$live[subject]</a>", 'actor' => "<a href=\"home.php?mod=space&uid=$live[uid]\" target=\"_blank\">$live[username]</a>", 'type2' => $type2['name']);
			}
			
			notification_add($live['uid'], $note_type, $note, $note_values);
		}
	}
}
?>