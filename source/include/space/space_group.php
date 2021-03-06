<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_group.php 10930 2010-05-18 07:34:59Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid']) {
	showmessage('to_login');
}
if(!$_G['setting']['groupstatus']) {
	showmessage('group_status_off');
}
require_once libfile('function/group');

$view = $_G['gp_view'] && in_array($_G['gp_view'], array('manager', 'join', 'groupthread', 'mythread')) ? $_G['gp_view'] : 'groupthread';
$actives = array('manager' => '', 'join' => '', 'groupthread' => '', 'mythread' => '');
$actives[$view] = ' class="a"';

$perpage = 20;
$page = intval($_G['gp_page']) ? intval($_G['gp_page']) : 1;
$start = ($page - 1) * $perpage;

if($view == 'groupthread' || $view == 'mythread') {
	$typeid = intval($_G['gp_typeid']);
	$attentiongroups = $usergroups = array();
	$usergroups = getuserprofile('groups');
	if(!empty($usergroups)) {
		$usergroups = unserialize($usergroups);
	} else {
		$usergroups = update_usergroups($_G['uid']);
	}

	if($view == 'groupthread' && empty($typeid) && !empty($usergroups['grouptype'])) {
//		$attentiongroup = $_G['member']['attentiongroup'];
		if(empty($attentiongroup)) {
//			$attentiongroups = array_slice($usergroups['grouptype'], 0, 1);
		} else {
			foreach(explode(',', $attentiongroup) as $fup) {
				if(!empty($usergroups['grouptype'][$fup])) {
					$attentiongroups[] = $usergroups['grouptype'][$fup];
				}
			}
		}
		$attentionthread = array();
		$attentiongroupfid = '';
		foreach($attentiongroups as $agroup) {
			$attentiongroupfid .= $attentiongroupfid ? ','.$agroup['groups'] : $agroup['groups'];
			if($page == 1) {
				$query = DB::query("SELECT fid, tid, subject, lastpost, lastposter, views, replies FROM ".DB::table('forum_thread')." WHERE special = 0 AND fid IN (".dimplode(explode(',', $agroup['groups'])).") AND displayorder>='0' ORDER BY dateline DESC LIMIT 5");
				while($thread = DB::fetch($query)) {
					$attentionthread[$agroup['fid']][$thread['tid']]['fid'] = $thread['fid'];
					$attentionthread[$agroup['fid']][$thread['tid']]['subject'] = $thread['subject'];
					$attentionthread[$agroup['fid']][$thread['tid']]['groupname'] = $usergroups['groups'][$thread['fid']];
					$attentionthread[$agroup['fid']][$thread['tid']]['views'] =  $thread['views'];
					$attentionthread[$agroup['fid']][$thread['tid']]['replies'] =  $thread['replies'];
					$attentionthread[$agroup['fid']][$thread['tid']]['lastposter'] =  user_get_user_name_by_username($thread['lastposter']);
					$groupthreadlist[$thread['tid']]['unreallastposter'] = $thread['lastposter'];
					$attentionthread[$agroup['fid']][$thread['tid']]['lastpost'] = dgmdate($thread['lastpost'], 'u');
					$attentionthread[$agroup['fid']][$thread['tid']]['folder'] = 'common';
					if(empty($_G['cookie']['oldtopics']) || strpos($_G['cookie']['oldtopics'], 'D'.$thread['tid'].'D') === FALSE) {
						$attentionthread[$agroup['fid']][$thread['tid']]['folder'] = 'new';
					}
				}
			}
		}
	}

	$mygrouplist = mygrouplist($_G['uid'], 'lastupdate', array('f.name', 'ff.icon'), 50);
	if($mygrouplist) {
		$managegroup = $commongroup = $groupthreadlist = array();
		foreach($mygrouplist as $fid => $group) {
			if($group['level'] == 1 || $group['level'] == 2) {
				if(count($managegroup) == 8) {
					continue;
				}
				$managegroup[$fid]['name'] = $group['name'];
				$managegroup[$fid]['icon'] = $group['icon'];
				if ($group["group_type"] >= 1 && $group["group_type"] < 20) {
					$managegroup[$fid]['type_icn_id'] = 'brzone_s';
					//$group['type_icn_id'] = 'brzone_s';
				} elseif ($group["group_type"] >= 20 && $group["group_type"] < 60) {
					$managegroup[$fid]['type_icn_id'] = 'bizone_s';
					//$group['type_icn_id'] = 'bizone_s';
				}
				
			} else {
				if(count($commongroup) == 8) {
					continue;
				}
				$commongroup[$fid]['name'] = $group['name'];
				$commongroup[$fid]['icon'] = $group['icon'];
				if ($group["group_type"] >= 1 && $group["group_type"] < 20) {
					$commongroup[$fid]['type_icn_id'] = 'brzone_s';
					//$group['type_icn_id'] = 'brzone_s';
				} elseif ($group["group_type"] >= 20 && $group["group_type"] < 60) {
					$commongroup[$fid]['type_icn_id'] = 'bizone_s';
					//$group['type_icn_id'] = 'bizone_s';
				}
			}
		}
		
		$managefids=array_keys($managegroup);
		$commonfids=array_keys($commongroup);
		if(count($managefids)){
			$managequery=DB::query("select fid,jointype from ".DB::TABLE("forum_forumfield")." where fid in (".implode(',',$managefids).")");
			while($managevalue=DB::fetch($managequery)){
				$managegroup[$managevalue[fid]][jointype]=$managevalue[jointype];
			}
		}
		if(count($managefids)){
			$commonquery=DB::query("select fid,jointype from ".DB::TABLE("forum_forumfield")." where fid in (".implode(',',$commonfids).")");
			while($commonvalue=DB::fetch($commonquery)){
				$commongroup[$commonvalue[fid]][jointype]=$commonvalue[jointype];
			}
		}
		$mygroupfid = array_keys($mygrouplist);
		if($typeid && !empty($usergroups['grouptype'][$typeid]['groups'])) {
			$mygroupfid = explode(',', $usergroups['grouptype'][$typeid]['groups']);
			$typeurl = '&typeid='.$typeid;
		} else {
			$typeid = 0;
		}
		$mythreadsql = $view == 'mythread' ? " AND authorid='$_G[uid]'": '';
		if(!empty($attentiongroupfid) && !empty($mygroupfid)) {
			$mygroupfid = array_diff($mygroupfid, explode(',', $attentiongroupfid));
		}
		if($mygroupfid) {
			$query = DB::query("SELECT count(*) FROM ".DB::table('forum_thread')." WHERE special = 0 AND fid IN (".dimplode($mygroupfid).") AND displayorder>='0' $mythreadsql ");
			$threadcount =  DB::result($query, 0);
			$query = DB::query("SELECT fid, tid, subject, lastpost, lastposter, views, replies FROM ".DB::table('forum_thread')." WHERE special = 0 AND fid IN (".dimplode($mygroupfid).") AND displayorder>='0' $mythreadsql ORDER BY lastpost DESC LIMIT $start, $perpage");
			while($thread = DB::fetch($query)) {
				$groupthreadlist[$thread['tid']]['fid'] = $thread['fid'];
				$groupthreadlist[$thread['tid']]['subject'] = $thread['subject'];
				$groupthreadlist[$thread['tid']]['groupname'] = $mygrouplist[$thread['fid']]['name'];
				$groupthreadlist[$thread['tid']]['views'] =  $thread['views'];
				$groupthreadlist[$thread['tid']]['replies'] =  $thread['replies'];
				$groupthreadlist[$thread['tid']]['lastposter'] = user_get_user_name_by_username($thread['lastposter']);
				$groupthreadlist[$thread['tid']]['unreallastposter'] = $thread['lastposter'];
				$groupthreadlist[$thread['tid']]['lastpost'] = dgmdate($thread['lastpost'], 'u');
				$groupthreadlist[$thread['tid']]['folder'] = 'common';
				if(empty($_G['cookie']['oldtopics']) || strpos($_G['cookie']['oldtopics'], 'D'.$thread['tid'].'D') === FALSE) {
					$groupthreadlist[$thread['tid']]['folder'] = 'new';
				}
			}
			$multipage = simplepage(count($groupthreadlist), $perpage, $page, 'home.php?mod=space&do=group&view='.$view.$typeurl);
			$is_hasMore = $threadcount > $perpage ? true : false;
		}
	}
} elseif($view == 'manager' || $view == 'join') {
	$perpage = 35;
	$ismanager = $view == 'manager' ? 1 : 2;
	$num = mygrouplist($_G['uid'], 'lastupdate', array('f.name', 'ff.icon'), 0, 0, $ismanager, 1);
	$multipage = multi($num, $perpage, $page, 'home.php?mod=space&do=group&view='.$view);
	$grouplist = mygrouplist($_G['uid'], 'lastupdate', array('f.name', 'ff.icon'), $perpage, $start, $ismanager);
	$fids = array_keys($grouplist);
	if(count($fids)){
		$query=DB::query("select fid,jointype from ".DB::TABLE("forum_forumfield")." where fid in (".implode(',',$fids).")");
		while($value=DB::fetch($query)){
			$grouplist[$value[fid]][jointype]=$value[jointype];
		}
	}
}

include_once template("diy:home/space_group");

?>