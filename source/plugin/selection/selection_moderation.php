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

if(!defined('IN_DISCUZ') || CURMODULE != 'topicadmin') {
	exit('Access Denied');
}

$allow_operation = array('delete','category', 'highlight', 'open', 'close', 'stick', 'digest', 'bump', 'down', 'recommend', 'type', 'move', 'recommend_group');

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
	$query = DB::query("SELECT * FROM ".DB::table("selection")." WHERE selectionid IN ($tids) AND fid='$_G[fid]' AND displayorder>='0' AND digest>='0' LIMIT $_G[tpp]");
	while($thread = DB::fetch($query)) {
//		if($thread['closed'] > 1 && $operation && !in_array($operation, array('delete', 'highlight', 'stick', 'digest', 'bump', 'down'))) {
//			continue;
//		}
//		$thread['lastposterenc'] = rawurlencode($thread['lastposter']);
//		$thread['dblastpost'] = $thread['lastpost'];
//		$thread['lastpost'] = dgmdate($thread['lastpost'], 'u');
		$threadlist[$thread['selectionid']] = $thread;
		$_G['tid'] = empty($_G['tid']) ? $thread['selectionid'] : $_G['tid'];
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
		$_G['referer'] = "forum.php?mod=modcp&action=thread&fid=$_G[fid]&op=thread&do=".($frommodcp == 1 ? '' : 'list');
		break;
	case '2':
		$_G['referer'] = "forum.php?mod=modcp&action=forum&op=recommend".(getgpc('show') ? "&show=getgpc('show')" : '')."&fid=$_G[fid]";
		break;
	default:
		$_G['referer'] = "forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=selection&plugin_op=groupmenu";
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
		$typeselect = typeselect($single ? $threadlist[$_G['tid']]['typeid'] : 0);
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
		
//	分类
	require_once libfile("function/category");
    $is_enable_category = false;
    $pluginid = "selection";
    if(common_category_is_enable($_G['fid'],$pluginid)){
        $is_enable_category = true;
        $categorys = common_category_get_category($_G['fid'],$pluginid);
    }
	
	include template('forum/selectiontopicadmin');

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
			if(in_array($operation, array('stick', 'highlight', 'digest', 'recommend'))) {
				if(empty($posts)) {
					$postlist = getfieldsofposts('*', "tid IN ($moderatetids) AND first='1'");
					foreach($postlist as $post) {
						$post['message'] = messagecutstr($post['message'], 200);
						$posts[$post['tid']] = $post;
					}
				}
			}

			$updatemodlog = TRUE;
			if($operation == 'stick') {
				$displayorder = intval($_G['gp_sticklevel']);
				if($displayorder < 0 || $displayorder > 3 || $displayorder > $_G['group']['allowdigestthread']) {
					showmessage('undefined_action');
				}
				//通知
				//require_once libfile('function/core');
//				$opquerys=DB::query("SELECT * FROM ".DB::table("selection")." WHERE selectionid IN ($moderatetids)");
//				while($opquery = DB::fetch($opquerys)) {
//				if($displayorder==$opquery[displayorder]){
//				}
//				else if($displayorder=='0'){
//					notification_add($opquery[uid],"zq_selectionion",'[问卷]您“'.$_G[forum][name].'”的专区的“'.$opquery[selectionname].'”的资源被['.user_get_user_name_by_username($_G['member']['username']).']撤销置顶了，赶快去看看吧', array(), 1);
//					}else{
//					notification_add($opquery[uid],"zq_selectionion",'[问卷]您“'.$_G[forum][name].'”的专区的“'.$opquery[selectionname].'”的资源被['.user_get_user_name_by_username($_G['member']['username']).']置顶了，赶快去看看吧', array(), 1);
//					}
//				}
				DB::query("UPDATE ".DB::table('selection')." SET displayorder='$displayorder', moderated='1' WHERE selectionid IN ($moderatetids)");

				
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

				DB::query("UPDATE ".DB::table('selection')." SET highlight='$highlight_style$highlight_color', moderated='1' WHERE selectionid IN ($moderatetids)", 'UNBUFFERED');
				
				$modaction = ($highlight_style + $highlight_color) ? ($expiration ? 'EHL' : 'HLT') : 'UHL';
				$expiration = $modaction == 'UHL' ? 0 : $expiration;
				
			} elseif($operation == 'digest') {
				$digestlevel = intval($_G['gp_digestlevel']);
				if($digestlevel < 0 || $digestlevel > 3 || $digestlevel > $_G['group']['allowdigestthread']) {
					showmessage('undefined_action');
				}
				$expiration = checkexpiration($_G['gp_expirationdigest'], $operation);
				$expirationdigest = $digestlevel ? $expirationdigest : 0;
				
				//通知
				//require_once libfile('function/core');
//				$opquerys=DB::query("SELECT * FROM ".DB::table("selection")." WHERE selectionid IN ($moderatetids)");
//				while($opquery = DB::fetch($opquerys)) {
//					if($digestlevel==$opquery[digest]){
//					}else if($digestlevel=='0'){
//						notification_add($opquery[uid],"zq_selectionion",'[问卷]您“'.$_G[forum][name].'”的专区的“'.$opquery[selectionname].'”的资源被['.user_get_user_name_by_username($_G['member']['username']).']撤销加精了，赶快去看看吧', array(), 1);
//						}else{
//						notification_add($opquery[uid],"zq_selectionion",'[问卷]您“'.$_G[forum][name].'”的专区的“'.$opquery[selectionname].'”的资源被['.user_get_user_name_by_username($_G['member']['username']).']加精了，赶快去看看吧', array(), 1);
//						}
//				}
				DB::query("UPDATE ".DB::table('selection')." SET digest='$digestlevel', moderated='1' WHERE selectionid IN ($moderatetids)");

				foreach($threadlist as $thread) {
					if($thread['digest'] != $digestlevel) {
						if($digestlevel == $thread['digest']) continue;
						$extsql = array();
						if($digestlevel > 0 && $thread['digest'] == 0) {
							$extsql = array('digestposts' => 1);
						}
						if($digestlevel == 0 && $thread['digest'] > 0) {
							$extsql = array('digestposts' => -1);
						}
						if($digestlevel == 0) {
							$stampaction = 'SPD';
						}
						updatecreditbyaction('digest', $thread['authorid'], $extsql, '', $digestlevel - $thread['digest']);
					}
				}

				$modaction = $digestlevel ? ($expiration ? 'EDI' : 'DIG') : 'UDG';
				$stampstatus = 2;
				//fix bug
				require_once libfile('function/group');
				delgroupcache($_G["fid"], "digest");
			} elseif($operation == 'delete') {
				
				if(!$_G['group']['allowdelpost']) {
					showmessage('undefined_action');
				}
				//通知
				//foreach($threadlist as $thread){
//					hook_delete_resource($thread[selectionid],'selectionion');
//				}
				require_once libfile('function/core');
				$opquerys=DB::query("SELECT * FROM ".DB::table("selection")." WHERE selectionid IN ($moderatetids)");
				//while($opquery = DB::fetch($opquerys)) {
//					notification_add($opquery[uid],"zq_selectionion",'[问卷]您“'.$_G[forum][name].'”的专区的“'.$opquery[selectionname].'”的资源被['.user_get_user_name_by_username($_G['member']['username']).']删除了', array(), 1);
//				}
				
				DB::query("UPDATE ".DB::table('selection')." SET moderated='-1' WHERE selectionid IN ($moderatetids)");
				
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
			} elseif($operation == 'category') {
				if(!$_G['group']['allowdelpost']) {
					showmessage('undefined_action');
				}
				//通知
				//require_once libfile('function/core');
//				$opquerys=DB::query("SELECT * FROM ".DB::table("selection")." WHERE selectionid IN ($moderatetids)");
//				while($opquery = DB::fetch($opquerys)) {
//					$oldid=$opquery[classid];
//					$oldcategory=DB::fetch_first("SELECT name FROM ".DB::table("common_category")." WHERE id=".$oldid);
//					$newcategory=DB::fetch_first("SELECT name FROM ".DB::table("common_category")." WHERE id='".$_POST["category"]."'");
//				notification_add($opquery[uid],"zq_selectionion",'[问卷]您“'.$_G[forum][name].'”的专区的“'.$opquery[selectionname].'”的问卷被['.user_get_user_name_by_username($_G['member']['username']).']从“'.$oldcategory[name].'”分类下移动到“'.$newcategory[name].'"分类下，赶快去看看吧', array(), 1);
//				}
				DB::query("UPDATE ".DB::table('selection')." SET classid=".$_POST["category"]." WHERE selectionid IN ($moderatetids)");

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
				if(!$_G['group']['allowedittypethread']) {
					showmessage('undefined_action');
				}
				if(!isset($_G['forum']['threadtypes']['types'][$_G['gp_typeid']]) && ($_G['gp_typeid'] != 0 || $_G['forum']['threadtypes']['required'])) {
					showmessage('admin_type_invalid');
				}

				DB::query("UPDATE ".DB::table('forum_thread')." SET typeid='$_G[gp_typeid]', moderated='1' WHERE tid IN ($moderatetids)");
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

?>