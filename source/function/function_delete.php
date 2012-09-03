<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_delete.php 11083 2010-05-21 07:07:52Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/home');
function deletemember($uids, $other = 1) {
	$query = DB::query("DELETE FROM ".DB::table('common_member')." WHERE uid IN ($uids)");
	$numdeleted = DB::affected_rows();
	DB::query("DELETE FROM ".DB::table('common_member_field_forum')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_field_home')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_count')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_log')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_profile')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_security')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_status')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_validate')." WHERE uid IN ($uids)", 'UNBUFFERED');

	DB::query("DELETE FROM ".DB::table('forum_access')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('forum_moderator')." WHERE uid IN ($uids)", 'UNBUFFERED');

	if($other) {
		DB::query("DELETE FROM ".DB::table('common_member_validate')." WHERE uid IN ($uids)", 'UNBUFFERED');
		DB::query("DELETE FROM ".DB::table('common_member_magic')." WHERE uid IN ($uids)", 'UNBUFFERED');

		$query = DB::query("SELECT uid, attachment, thumb, remote, aid FROM ".DB::table('forum_attachment')." WHERE uid IN ($uids) AND pid>0");
		while($attach = DB::fetch($query)) {
			dunlink($attach);
		}
		DB::query("DELETE FROM ".DB::table('forum_attachment')." WHERE uid IN ($uids) AND pid>0", 'UNBUFFERED');
		DB::query("DELETE FROM ".DB::table('forum_attachmentfield')." WHERE uid IN ($uids) AND pid>0", 'UNBUFFERED');
		deletepost("authorid IN ($uids)", true, false);
	}

	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE uid IN ($uids) OR (id IN ($uids) AND idtype='uid')");

	$doids = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_doing')." WHERE uid IN ($uids)");
	while ($value = DB::fetch($query)) {
		$doids[$value['doid']] = $value['doid'];
	}

	DB::query("DELETE FROM ".DB::table('home_doing')." WHERE uid IN ($uids)");

	$delsql = !empty($doids) ? "doid IN (".dimplode($doids).") OR " : "";
	DB::query("DELETE FROM ".DB::table('home_docomment')." WHERE $delsql uid IN ($uids)");

	DB::query("DELETE FROM ".DB::table('home_share')." WHERE uid IN ($uids)");

	DB::query("DELETE FROM ".DB::table('home_album')." WHERE uid IN ($uids)");

	DB::query("DELETE FROM ".DB::table('common_credit_rule_log')." WHERE uid IN ($uids)");
	DB::query("DELETE FROM ".DB::table('common_credit_rule_log_field')." WHERE uid IN ($uids)");

	DB::query("DELETE FROM ".DB::table('home_notification')." WHERE (uid IN ($uids) OR authorid IN ($uids))");

	DB::query("DELETE FROM ".DB::table('home_poke')." WHERE (uid IN ($uids) OR fromuid IN ($uids))");


	$query = DB::query("SELECT filepath, thumb, remote FROM ".DB::table('home_pic')." WHERE uid IN ($uids)");
	while ($value = DB::fetch($query)) {
		deletepicfiles($value);
	}

	DB::query("DELETE FROM ".DB::table('home_pic')." WHERE uid IN ($uids)");

	DB::query("DELETE FROM ".DB::table('home_blog')." WHERE uid IN ($uids)");
	DB::query("DELETE FROM ".DB::table('home_blogfield')." WHERE uid IN ($uids)");

	DB::query("DELETE FROM ".DB::table('home_comment')." WHERE (uid IN ($uids) OR authorid IN ($uids) OR (id IN ($uids) AND idtype='uid'))");

	DB::query("DELETE FROM ".DB::table('home_visitor')." WHERE (uid IN ($uids) OR vuid IN ($uids))");

	DB::query("DELETE FROM ".DB::table('home_class')." WHERE uid IN ($uids)");

	DB::query("DELETE FROM ".DB::table('home_friend')." WHERE (uid IN ($uids) OR fuid IN ($uids))");

	DB::query("DELETE FROM ".DB::table('home_clickuser')." WHERE uid IN ($uids)");

	DB::query("DELETE FROM ".DB::table('common_invite')." WHERE (uid IN ($uids) OR fuid IN ($uids))");

	DB::query("DELETE FROM ".DB::table('common_mailcron').", ".DB::table('common_mailqueue')." USING ".DB::table('common_mailcron').", ".DB::table('common_mailqueue')." WHERE ".DB::table('common_mailcron').".touid IN ($uids) AND ".DB::table('common_mailcron').".cid=".DB::table('common_mailqueue').".cid");

	DB::query("DELETE FROM ".DB::table('common_myinvite')." WHERE (touid IN ($uids) OR fromuid IN ($uids))");
	DB::query("DELETE FROM ".DB::table('home_userapp')." WHERE uid IN ($uids)");
	DB::query("DELETE FROM ".DB::table('home_userappfield')." WHERE uid IN ($uids)");

	DB::query("DELETE FROM ".DB::table('home_show')." WHERE uid IN ($uids)");

	manyoulog('user', $uids, 'delete');

	require_once libfile('function/forum');
	foreach(explode(',', $uids) as $uid) {
		my_thread_log('deluser', array('uid' => $uid));
	}
	return $numdeleted;
}

function deletepost($condition, $unbuffered = true, $deleteattach = true) {
	global $_G;
	loadcache('posttableids');
	$num = 0;
	if(!empty($_G['cache']['posttableids'])) {
		$posttableids = $_G['cache']['posttableids'];
	} else {
		$posttableids = array('0');
	}
	foreach($posttableids as $id) {
		if($id == 0) {
			DB::delete('forum_post', $condition, 0, $unbuffered);
		} else {
			DB::delete("forum_post_$id", $condition, 0, $unbuffered);
		}
		$num += DB::affected_rows();
	}
	if($deleteattach) {
		$query = DB::query("SELECT attachment, thumb, remote, aid FROM ".DB::table('forum_attachment')." WHERE $condition AND pid>0");
		while($attach = DB::fetch($query)) {
			dunlink($attach);
		}
		DB::delete('forum_attachment', $condition.' AND pid>0', 0, $unbuffered);
		DB::delete('forum_attachmentfield', $condition.' AND pid>0', 0, $unbuffered);
	}
	return $num;
}

function deletethread($condition, $unbuffered = true) {
	$deletedthreads = 0;
	$query = DB::query("SELECT attachment, thumb, remote, aid FROM ".DB::table('forum_attachment')." WHERE $condition AND pid>0");
	while($attach = DB::fetch($query)) {
		dunlink($attach);
	}
	DB::delete('forum_attachment', $condition.' AND pid>0', 0, $unbuffered);
	DB::delete('forum_attachmentfield', $condition.' AND pid>0', 0, $unbuffered);
	foreach(array(
		'forum_thread', 'forum_polloption', 'forum_poll', 'forum_trade', 'forum_activity', 'forum_activityapply',
		'forum_debate', 'forum_debatepost', 'forum_threadmod', 'forum_relatedthread',
		'forum_typeoptionvar', 'forum_postposition', 'forum_pollvoter') as $table) {
		DB::delete($table, $condition, 0, $unbuffered);
		if($table == 'forum_thread') {
			$deletedthreads = DB::affected_rows();
		}
	}
	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE ".str_replace('tid', 'id', $condition)." AND idtype='tid'");
	return $deletedthreads;
}

function deletecomments($cids) {
	global $_G;
	
	include_once libfile('function/comment');

	$blognums = $newcids = $dels = $counts = $others = array();
	$allowmanage = checkperm('managecomment');

	$query = DB::query("SELECT * FROM ".DB::table('home_comment')." WHERE cid IN (".dimplode($cids).")");
	while ($value = DB::fetch($query)) {
		if($allowmanage || $value['authorid'] == $_G['uid'] || $value['uid'] == $_G['uid']) {
			$dels[] = $value;
			$newcids[] = $value['cid'];
			if($value['authorid'] != $_G['uid'] && $value['uid'] != $_G['uid']) {
				$counts[$value['authorid']]['coef'] -= 1;
			}
			if($value['idtype'] == 'blogid') {
				$blognums[$value['id']]++;
			}
			else{
				if(!empty($others[$value['idtype']])){
					$others[$value['idtype']] = array();
				}
				$others[$value['idtype']][$value['id']]++;
			}
		}
	}

	if(empty($dels)) return array();
	hook_delete_resources($cids,'home%comment');
	DB::query("DELETE FROM ".DB::table('home_comment')." WHERE cid IN (".dimplode($newcids).")");

	if($counts) {
		foreach ($counts as $uid => $setarr) {
			batchupdatecredit('comment', $uid, array(), $setarr['coef']);
		}
	}
	
	if($blognums) {
		$nums = renum($blognums);
		foreach ($nums[0] as $num) {
			DB::query("UPDATE ".DB::table('home_blog')." SET replynum=replynum-$num WHERE blogid IN (".dimplode($nums[1][$num]).")");
		}
	}
	else if($others){
		foreach($others as $key => $value){
			$nums = renum($value);
			foreach ($nums[0] as $num) {
				if(function_exists("getcommentreplyremove_$key"))
					eval('getcommentreplyremove_'.$key.'($nums[1][$num], $num);');
			}
		}
	}
	return $dels;
}



function deleteblogs($blogids) {
	global $_G;
    if(empty($blogids)){
    	return ;
    }
    
    
    //修改 by songsp  2011-3-14 17:03:06   
    $is_flushcache = 0;  //是否更新社区官方博客cache信息
    $gbuid = getOfficialBlogUid();//官方博客uid
    
	$blogs = $newblogids = $counts = array();
	$allowmanage = checkperm('manageblog');
	$query = DB::query("SELECT * FROM ".DB::table('home_blog')." WHERE blogid IN (".implode(',',$blogids).")");
	while ($value = DB::fetch($query)) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$blogs[] = $value;
			$newblogids[] = $value['blogid'];

			if($value['uid'] != $_G['uid']) {
				$counts[$value['uid']]['coef'] -= 1;
			}
			$counts[$value['uid']]['blogs'] -= 1;
			
			
			//add  by songsp  2011-3-14 17:05:55 
			//start
			if($is_flushcache == 0  && $gbuid == $value['uid']){
				$is_flushcache = 1; //更新社区首页官方博客cache信息
			}
			//end 

		}
	}
	if(empty($blogs)) return array();
	
	$bcomquery=DB::query("SELECT cid FROM pre_home_comment WHERE id IN (".dimplode($newblogids).")");
	$bcidarray=array();
	while($row=DB::fetch($bcomquery)){
		$bcidarray[$row['cid']]=$row['cid'];
	}

	DB::query("DELETE FROM ".DB::table('home_blog')." WHERE blogid IN (".dimplode($newblogids).")");
	DB::query("DELETE FROM ".DB::table('home_blogfield')." WHERE blogid IN (".dimplode($newblogids).")");
	DB::query("DELETE FROM ".DB::table('home_comment')." WHERE id IN (".dimplode($newblogids).") AND idtype='blogid'");
	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE id IN (".dimplode($newblogids).") AND idtype='blogid'");
	DB::query("DELETE FROM ".DB::table('home_clickuser')." WHERE id IN (".dimplode($newblogids).") AND idtype='blogid'");

	if($counts) {
		foreach ($counts as $uid => $setarr) {
			batchupdatecredit('publishblog', $uid, array('blogs' => $setarr['blogs']), $setarr['coef']);
		}
	}
	//added by fumz，2010-9-30 11:01:45
	//begin
	hook_delete_resources($blogids,'blog');//added by fumz,2010-9-26 17:20:21
	hook_delete_resources($bcidarray,'home_blog_comment');
	//end
	
	
	//add by songsp 2011-3-14 17:06:49  更新社区首页官方博客cache信息
	if($is_flushcache == 1){
		require_once libfile('function/blog');
		flushBlog4PortalCache();
	}
	
	
	
	return $blogs;
}

function deletefeeds($feedids) {
	global $_G;

	$allowmanage = checkperm('managefeed');

	$feeds = $newfeedids = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_feed')." WHERE feedid IN (".dimplode($feedids).")");
	while ($value = DB::fetch($query)) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$newfeedids[] = $value['feedid'];
			$feeds[] = $value;
		}
	}

	if(empty($newfeedids)) return array();

	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE feedid IN (".dimplode($newfeedids).")");

	return $feeds;
}


function deleteshares($sids) {
	global $_G;

	$allowmanage = checkperm('manageshare');

	$shares = $newsids = $counts = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_share')." WHERE sid IN (".dimplode($sids).")");
	while ($value = DB::fetch($query)) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$shares[] = $value;
			$newsids[] = $value['sid'];

			if($value['uid'] != $_G['uid']) {
				$counts[$value['uid']]['coef'] -= 1;
			}
			$counts[$value['uid']]['sharings'] -= 1;
		}
	}
	if(empty($shares)) return array();

	DB::query("DELETE FROM ".DB::table('home_share')." WHERE sid IN (".dimplode($newsids).")");
	DB::query("DELETE FROM ".DB::table('home_comment')." WHERE id IN (".dimplode($newsids).") AND idtype='sid'");
	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE id IN (".dimplode($newsids).") AND idtype='sid'");
	DB::query("DELETE FROM ".DB::table('home_sharelog')." WHERE sid IN (".dimplode($newsids).")");
	
	
	if($counts) {
		foreach ($counts as $uid => $setarr) {
			batchupdatecredit('createshare', $uid, array('sharings' => $setarr['sharings']), $setarr['coef']);
		}
	}

	return $shares;
}


function deletedoings($ids) {
	global $_G;

	$allowmanage = checkperm('managedoing');

	$doings = $newdoids = $counts = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_doing')." WHERE doid IN (".dimplode($ids).")");
	while ($value = DB::fetch($query)) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$doings[] = $value;
			$newdoids[] = $value['doid'];

			if($value['uid'] != $_G['uid']) {
				$counts[$value['uid']]['coef'] -= 1;
			}
			$counts[$value['uid']]['doings'] -= 1;
		}
	}

	if(empty($doings)) return array();
	
	/**
	 * 同步删除pre_common_resource表中记录记录回复的数据
	 * fumz,2010-9-27 13:39:17
	 */
	//begin
	if(!empty($ids)){
		$dcids=array();
		$dcquery=DB::query("select id from pre_home_docomment where doid in (".dimplode($ids).")");
		while($row=DB::fetch($dcquery)){
			$dcids[$row['id']]=$row['id'];
		}
		if(!empty($dcids)){
			hook_delete_resources($dcids,'home_doing_comment');
		}		
	}
	//end
		
	DB::query("DELETE FROM ".DB::table('home_doing')." WHERE doid IN (".dimplode($newdoids).")");
	DB::query("DELETE FROM ".DB::table('home_docomment')." WHERE doid IN (".dimplode($newdoids).")");
	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE id IN (".dimplode($newdoids).") AND idtype='doid'");

	if($counts) {
		foreach ($counts as $uid => $setarr) {
			batchupdatecredit('doing', $uid, array('doings' => $setarr['doings']), $setarr['coef']);
		}
	}

	return $doings;
}
function deletedocomments($ids) {
	global $_G;
	if(empty($ids)||!is_array($ids)){
		return;
	}
	$count=count($ids);
	/*
	 * 同步更改记录的回复数
	 * fumz,2010-9-27 14:06:01
	 */
	//begin
	if($count){
		foreach($ids as $id){
			$doidquery=DB::query("select doid  from pre_home_docomment where id=$id");
			while($row=DB::fetch($doidquery)){
				if($row['doid']){
					$doid=$row['doid'];
					DB::query("UPDATE pre_home_doing SET replynum=replynum-'1' WHERE doid='$doid'");
				}			
			}
		}			
	}
	//end
	$query=DB::query("DELETE FROM pre_home_docomment WHERE id IN (".dimplode($ids).")");
	
	
}
/**
 *删除记录的下级回复
 *$id 
 */
function deletesubdocomment($id){
	global $_G;
	if(!$id){
		return;
	}
	$query=DB::query("select * from pre_home_docomment where upid=$id");
	while($row=DB::fetch($query)){
		if($row['grade']<3){
			deletesubdocomment($row['id']);
		}else{
			$subcountquery=DB::query("select count(*) from pre_home_docomment where upid=$id");
			$subcount=DB::fetch($subcountquery,0);
			foreach($subcount as $value){
				$subcount=$value;
			}
			if($subcount){
				//DB::query("update pre_home_doing set replynum=replynum-'$subcount' where doid='$row['doid']'");
				DB::query("delete from pre_home_docomment where upid=$id");
			}	
		}
	}
}

function deletespace($uid) {
	global $_G;

	$allowmanage = checkperm('managedelspace');

	if($allowmanage) {
		DB::query("UPDATE ".DB::table('common_member')." SET status='1' WHERE uid='$uid'");
		if($_G['setting']['my_app_status']) manyoulog('user', $uid, 'delete');
		return true;
	} else {
		return false;
	}
}

function deletepics($picids) {
	global $_G;

	$sizes = $pics = $newids = $counts = array();
	
	$allowmanage = checkperm('managealbum');
		

	$albumids = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_pic')." WHERE picid IN (".dimplode($picids).")");
	require_once libfile('function/credit');
	while ($value = DB::fetch($query)) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$pics[] = $value;
			$newids[] = $value['picid'];

			if($value['uid'] != $_G['uid']) {
				$counts[$value['uid']]['coef'] -= 1;
			}
			$sizes[$value['uid']] = $sizes[$value['uid']] + $value['size'];
			$albumids[$value['albumid']] = $value['albumid'];
			credit_create_credit_log($value['uid'], 'deletepicture', $value['picid']);
		}
	}
	if(empty($pics)) return array();
	$cidquery=DB::query("SELECT cid FROM pre_home_comment WHERE id IN (".dimplode($newids).") AND idtype='picid'");
	$cidarr=array();
	while($value=DB::fetch($cidquery)){
		$cidarr[$value['cid']]=$value['cid'];
	}


	DB::query("DELETE FROM ".DB::table('home_pic')." WHERE picid IN (".dimplode($newids).")");
	DB::query("DELETE FROM ".DB::table('home_comment')." WHERE id IN (".dimplode($newids).") AND idtype='picid'");
	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE id IN (".dimplode($newids).") AND idtype='picid'");
	DB::query("DELETE FROM ".DB::table('home_clickuser')." WHERE id IN (".dimplode($newids).") AND idtype='picid'");
    //added by fumz,2010-9-30 10:53:55
    //begin
	hook_delete_resources($cidarr,'home_pic_comment');
	hook_delete_resources($newids,'home_pic');
	//end
	
	if($counts) {
		foreach ($counts as $uid => $setarr) {
			$attachsize = intval($sizes[$uid]);
			batchupdatecredit('uploadimage', $uid, array('attachsize' => -$attachsize), $setarr['coef']);
			unset($sizes[$uid]);
		}
	}
	if($sizes) {
		foreach ($sizes as $uid => $setarr) {
			$attachsize = intval($sizes[$uid]);
			updatemembercount($uid, array('attachsize' => -$attachsize), false);
		}
	}

	require_once libfile('function/spacecp');
	foreach ($albumids as $albumid) {
		if($albumid) {
			album_update_pic($albumid);
		}
	}

	deletepicfiles($pics);

	return $pics;
}

/**
 * special for manager
 * added by fumz 2010-10-9 17:08:40
 * 三级管理专用
 * @param unknown_type $picids
 * @return multitype:|Ambigous <multitype:, unknown>
 */
function managedeletepics($picids) {
	global $_G;

	$sizes = $pics = $newids = $counts = array();

	$albumids = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_pic')." WHERE picid IN (".dimplode($picids).")");
	while ($value = DB::fetch($query)) {		
			$pics[] = $value;
			$newids[] = $value['picid'];

			if($value['uid'] != $_G['uid']) {
				$counts[$value['uid']]['coef'] -= 1;
			}
			$sizes[$value['uid']] = $sizes[$value['uid']] + $value['size'];
			$albumids[$value['albumid']] = $value['albumid'];		
	}
	if(empty($pics)) return array();
	$cidquery=DB::query("SELECT cid FROM pre_home_comment WHERE id IN (".dimplode($newids).") AND idtype='picid'");
	$cidarr=array();
	while($value=DB::fetch($cidquery)){
		$cidarr[$value['cid']]=$value['cid'];
	}


	DB::query("DELETE FROM ".DB::table('home_pic')." WHERE picid IN (".dimplode($newids).")");
	DB::query("DELETE FROM ".DB::table('home_comment')." WHERE id IN (".dimplode($newids).") AND idtype='picid'");
	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE id IN (".dimplode($newids).") AND idtype='picid'");
	DB::query("DELETE FROM ".DB::table('home_clickuser')." WHERE id IN (".dimplode($newids).") AND idtype='picid'");
    //added by fumz,2010-9-30 10:53:55
    //begin
	hook_delete_resources($cidarr,'home_pic_comment');
	hook_delete_resources($newids,'home_pic');
	//end
	
	if($counts) {
		foreach ($counts as $uid => $setarr) {
			$attachsize = intval($sizes[$uid]);
			batchupdatecredit('uploadimage', $uid, array('attachsize' => -$attachsize), $setarr['coef']);
			unset($sizes[$uid]);
		}
	}
	if($sizes) {
		foreach ($sizes as $uid => $setarr) {
			$attachsize = intval($sizes[$uid]);
			updatemembercount($uid, array('attachsize' => -$attachsize), false);
		}
	}

	require_once libfile('function/spacecp');
	foreach ($albumids as $albumid) {
		if($albumid) {
			album_update_pic($albumid);
		}
	}

	deletepicfiles($pics);

	return $pics;
}

function deletepicfiles($pics) {
	global $_G;
	$remotes = array();
	include_once libfile('function/home');
	foreach ($pics as $pic) {
		pic_delete($pic['filepath'], 'album', $pic['thumb'], $pic['remote']);
	}
}

function deletealbums($albumids) {
	global $_G;

	$sizes = $dels = $newids = $counts = array();
	$allowmanage = checkperm('managealbum');

	$query = DB::query("SELECT * FROM ".DB::table('home_album')." WHERE albumid IN (".dimplode($albumids).")");
	while ($value = DB::fetch($query)) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$dels[] = $value;
			$newids[] = $value['albumid'];
		}
		$counts[$value['uid']]['albums'] -= 1;
	}
	if(empty($dels)) return array();

	$pics = $picids = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_pic')." WHERE albumid IN (".dimplode($newids).")");
	while ($value = DB::fetch($query)) {
		$pics[] = $value;
		$picids[] = $value['picid'];
		$sizes[$value['uid']] = $sizes[$value['uid']] + $value['size'];
		if($value['uid'] != $_G['uid']) {
			$counts[$value['uid']]['coef'] -= 1;
		}
	}
	
    
	DB::query("DELETE FROM ".DB::table('home_pic')." WHERE albumid IN (".dimplode($newids).")");	
	DB::query("DELETE FROM ".DB::table('home_album')." WHERE albumid IN (".dimplode($newids).")");
	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE id IN (".dimplode($newids).") AND idtype='albumid'");
	if($picids) DB::query("DELETE FROM ".DB::table('home_clickuser')." WHERE id IN (".dimplode($picids).") AND idtype='picid'");
    
	//added by fumz，2010-9-26 18:13:00
	//begin
	hook_delete_resources($newids,'home_album');
	/*if($picids){
		hook_delete_resources($picids,"home_pic");
	}*/
	//end
	
	if($counts) {
		foreach ($counts as $uid => $setarr) {
			$attachsize = intval($sizes[$uid]);
			batchupdatecredit('uploadimage', $uid, array('albums' => $setarr['albums'], 'attachsize' => -$attachsize), $setarr['coef']);
		}
	}
	if($sizes) {
		foreach ($sizes as $uid => $value) {
			$attachsize = intval($sizes[$uid]);
			updatemembercount($uid, array('attachsize' => -$attachsize), false);
		}
	}

	if($pics) {
		deletepicfiles($pics);
	}

	return $dels;
}

function deletepolls($pids) {
	global $_G;


	$counts = $polls = $newpids = array();
	$allowmanage = checkperm('managepoll');

	$query = DB::query("SELECT * FROM ".DB::table('home_poll')." WHERE pid IN (".dimplode($pids).")");
	while ($value = DB::fetch($query)) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$polls[] = $value;
			$newpids[] = $value['pid'];

			if($value['uid'] != $_G['uid']) {
				$counts[$value['uid']]['coef'] -= 1;
			}
			$counts[$value['uid']]['polls'] -= 1;
		}
	}
	if(empty($polls)) return array();

	DB::query("DELETE FROM ".DB::table('home_poll')." WHERE pid IN (".dimplode($newpids).")");
	DB::query("DELETE FROM ".DB::table('home_pollfield')." WHERE pid IN (".dimplode($newpids).")");
	DB::query("DELETE FROM ".DB::table('home_polloption')." WHERE pid IN (".dimplode($newpids).")");
	DB::query("DELETE FROM ".DB::table('home_polluser')." WHERE pid IN (".dimplode($newpids).")");
	DB::query("DELETE FROM ".DB::table('home_comment')." WHERE id IN (".dimplode($newpids).") AND idtype='pid'");
	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE id IN (".dimplode($newpids).") AND idtype='pid'");

	if($counts) {
		foreach ($counts as $uid => $setarr) {
			batchupdatecredit('createpoll', $uid, array('polls' => $setarr['polls']), $setarr['coef']);
		}
	}

	return $polls;

}


function deletetrasharticle($aids) {
	global $_G;

	$articles = $trashid = array();
	$query = DB::query("SELECT * FROM ".DB::table('portal_article_trash')." WHERE aid IN (".dimplode($aids).")");
	while ($value = DB::fetch($query)) {
		$dels[$value['aid']] = $value['aid'];
		$article = unserialize($value['content']);
		$articles[$article['aid']] = $article;
		if($article['pic']) {
			@unlink($_G['config']['attachdir'].'./'.$article['pic']);
		}
	}

	if($dels) {
		DB::query('DELETE FROM '.DB::table('portal_article_trash')." WHERE aid IN(".dimplode($dels).")", 'UNBUFFERED');
		deletearticlerelated($dels);
	}

	return $articles;
}


function deletearticle($aids, $istrash = 1) {
	global $_G;

	if(empty($aids)) return false;
	$trasharr = $article = $bids = $dels = $attachment = $attachaid = $catids = array();
	$query = DB::query("SELECT * FROM ".DB::table('portal_article_title')." WHERE aid IN (".dimplode($aids).")");
	while ($value = DB::fetch($query)) {
		$catids[] = intval($value['catid']);
		$dels[$value['aid']] = $value['aid'];
		$article[] = $value;
	}
	if($dels) {
		foreach($article as $key => $value) {
			if($istrash) {
				$valstr = daddslashes(serialize($value));
				$trasharr[] = "('$value[aid]', '$valstr')";
			} elseif($value['pic']) {
				pic_delete($value['pic'], 'portal', $value['thumb'], $value['remote']);
				$attachaid[] = $value['aid'];
			}
		}
		if($istrash) {
			if($trasharr) {
				DB::query("INSERT INTO ".DB::table('portal_article_trash')." (`aid`, `content`) VALUES ".implode(',', $trasharr));
			}
		} else {
			deletearticlerelated($dels);
		}

		DB::query('DELETE FROM '.DB::table('portal_article_title')." WHERE aid IN(".dimplode($dels).")", 'UNBUFFERED');

		$catids = array_unique($catids);
		if($catids) {
			foreach($catids as $catid) {
				$cnt = DB::result_first('SELECT COUNT(*) FROM '.DB::table('portal_article_title')." WHERE catid = '$catid'");
				DB::update('portal_category', array('articles'=>$cnt), array('catid'=>$catid));
			}
		}
	}
	return $article;
}

function deletearticlerelated ($dels) {

	DB::query('DELETE FROM '.DB::table('portal_article_count')." WHERE aid IN(".dimplode($dels).")", 'UNBUFFERED');
	DB::query('DELETE FROM '.DB::table('portal_article_content')." WHERE aid IN(".dimplode($dels).")", 'UNBUFFERED');

	$query = DB::query("SELECT * FROM ".DB::table('portal_attachment')." WHERE aid IN (".dimplode($dels).")");
	while ($value = DB::fetch($query)) {
		$attachment[] = $value;
		$attachdel[] = $value['attachid'];
	}
	foreach ($attachment as $value) {
		pic_delete($value['attachment'], 'portal', $value['thumb'], $value['remote']);
	}
	DB::query("DELETE FROM ".DB::table('portal_attachment')." WHERE aid IN (".dimplode($dels).")", 'UNBUFFERED');

	DB::query('DELETE FROM '.DB::table('portal_comment')." WHERE aid IN(".dimplode($dels).")", 'UNBUFFERED');

	DB::query('DELETE FROM '.DB::table('portal_article_related')." WHERE aid IN(".dimplode($dels).")", 'UNBUFFERED');

}
/*
 * 删除提问吧内容
 */
function deleteqbar($ids,$fid){
	$result=0;
	
	if($ids&&!empty($ids)){
		DB::query("DELETE FROM pre_forum_thread WHERE tid IN(".dimplode($ids).")", 'UNBUFFERED');
		/*
		 * undo 数据同步
		 */
		hook_delete_resources($ids,'group_pcomment');
		$result=1;
	}
	return $result;
	
}



?>