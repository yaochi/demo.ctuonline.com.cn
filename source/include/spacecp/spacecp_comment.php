<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_comment.php 10793 2010-05-17 01:52:12Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


$tospace = $pic = $blog = $album = $share = $poll = array();

include_once libfile('class/bbcode');
include_once libfile('function/comment');
include_once libfile('function/extraresource');
include_once libfile('function/repeats','plugin/repeats');
$bbcode = & bbcode::instance();

if(submitcheck('commentsubmit')) {

	$idtype = $_POST['idtype'];
	$forward= $_POST['forward'];
	$icon = $_POST['icon'];
	$iconid = $_POST['iconid'];
	$iconidtype = $_POST['iconidtype'];
	$fid = $_POST['fid'];
	$anonymity=$_POST['anonymity'];
	$atjson=$_POST['atjson'];
	
	if(!$anonymity){
		$anonymity=$_G[member][repeatsstatus];
	}
	if($anonymity>0){
		include_once libfile('function/repeats','plugin/repeats');
		$repeatsinfo=getforuminfo($anonymity);
		//$fid=$repeatsinfo['fid'];
	}
	if(!checkperm('allowcomment')) {
		showmessage('no_privilege');
	}

	ckrealname('comment');

	cknewuser();

	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast', '', array('waittime' => $waittime));
	}

	$commessage=$message = getstr($_POST['message'], 0, 1, 1, 1, 2);
	if(strlen($message) < 2) {
		showmessage('content_is_too_short');
	}

	$summay = getstr($message, 150, 1, 1, 0, 0, -1);

	$id = intval($_POST['id']);

	$cid = empty($_POST['cid'])?0:intval($_POST['cid']);
	$fromajax = false;
	$comment = array();
	if($cid) {
		if($icon=='thread'||$icon=='poll'||$icon=='reward'){
			if($_G['gp_inajax']) $fromajax = true;
			$query = DB::query("SELECT * FROM ".DB::table('forum_post')." WHERE pid='$cid'");
			echo($query);
			$comment = DB::fetch($query);
			if(!$atjson&&$comment[anonymity]=='0'){
				$atjson="[{\"id\":\"".$comment[authorid]."\",\"name\":\"".user_get_user_name($comment[authorid])."\",\"type\":\"user\",\"note\":\"\"}]";
			}elseif(!$atjson&&$comment[anonymity]!='-1'){
				$finfo=getforuminfo($comment[anonymity]);
				$atjson="[{\"id\":\"".$finfo[fid]."\",\"name\":\"".$finfo[name]."\",\"type\":\"group\",\"note\":\"\"}]";
			}
			$arlcid=$cid;
			if($comment && $comment['authorid'] != $_G['uid']) {
				/*$comment['message'] = preg_replace("/\<div class=\"quote\"\>\<blockquote\>.*?\<\/blockquote\>\<\/div\>/is", '', $comment['message']);
				$comment['message'] = $bbcode->html2bbcode($comment['message']);
				$message = addslashes("<div class=\"quote\"><blockquote><b>".user_get_user_name_by_username($comment['author'])."</b>: ".getstr($comment['message'], 0, 0, 0, 0, 2, 1).'</blockquote></div>').$message;
				if($comment['idtype']=='uid') {
					$id = $comment['authorid'];
				}*/
			} else {
				$comment = array();
			}
			
		}else{
			if($_G['gp_inajax']) $fromajax = true;
			$query = DB::query("SELECT * FROM ".DB::table('home_comment')." WHERE cid='$cid' AND id='$id' AND idtype='$_POST[idtype]'");
			$comment = DB::fetch($query);
			if(!$atjson&&$comment[anonymity]=='0'){
				$atjson="[{\"id\":\"".$comment[authorid]."\",\"name\":\"".user_get_user_name($comment[authorid])."\",\"type\":\"user\",\"note\":\"\"}]";
			}elseif(!$atjson&&$comment[anonymity]!='-1'){
				$finfo=getforuminfo($comment[anonymity]);
				$atjson="[{\"id\":\"".$finfo[fid]."\",\"name\":\"".$finfo[name]."\",\"type\":\"group\",\"note\":\"\"}]";
			}
			$arlcid=$cid;
			if($comment && $comment['authorid'] != $_G['uid']) {
				/*$comment['message'] = preg_replace("/\<div class=\"quote\"\>\<blockquote\>.*?\<\/blockquote\>\<\/div\>/is", '', $comment['message']);
				$comment['message'] = $bbcode->html2bbcode($comment['message']);
				$message = addslashes("<div class=\"quote\"><blockquote><b>".user_get_user_name_by_username($comment['author'])."</b>: ".getstr($comment['message'], 0, 0, 0, 0, 2, 1).'</blockquote></div>').$message;
				if($comment['idtype']=='uid') {
					$id = $comment['authorid'];
				}*/
			} else {
				$comment = array();
			}
		}
	}

	$hotarr = array();
	$stattype = '';

	switch ($idtype) {
		case 'uid':
			$tospace = getspace($id);
			$stattype = 'wall';
			break;
		case 'picid':
			$query = DB::query("SELECT p.*, pf.hotuser
				FROM ".DB::table('home_pic')." p
				LEFT JOIN ".DB::table('home_picfield')." pf
				ON pf.picid=p.picid
				WHERE p.picid='$id'");
			$pic = DB::fetch($query);
			if(empty($pic)) {
				showmessage('view_images_do_not_exist');
			}

			$tospace = getspace($pic['uid']);

			$album = array();
			if($pic['albumid']) {
				$query = DB::query("SELECT * FROM ".DB::table('home_album')." WHERE albumid='$pic[albumid]'");
				if(!$album = DB::fetch($query)) {
					DB::update('home_pic', array('albumid'=>0), array('albumid'=>$pic['albumid']));
				}
			}
			
			if(!ckfriend($album['uid'], $album['friend'], $album['target_ids'])) {
				showmessage('no_privilege');
			} elseif(!$tospace['self'] && $album['friend'] == 4) {
				$cookiename = "view_pwd_album_$album[albumid]";
				$cookievalue = empty($_G['cookie'][$cookiename])?'':$_G['cookie'][$cookiename];
				if($cookievalue != md5(md5($album['password']))) {
					showmessage('no_privilege');
				}
			}

			$hotarr = array('picid', $pic['picid'], $pic['hotuser']);
			$stattype = 'piccomment';
			
			break;
		case 'blogid':
			$query = DB::query("SELECT b.*, bf.target_ids, bf.hotuser
				FROM ".DB::table('home_blog')." b
				LEFT JOIN ".DB::table('home_blogfield')." bf ON bf.blogid=b.blogid
				WHERE b.blogid='$id'");
			$blog = DB::fetch($query);
			if(empty($blog)) {
				showmessage('view_to_info_did_not_exist');
			}

			$tospace = getspace($blog['uid']);

			if(!ckfriend($blog['uid'], $blog['friend'], $blog['target_ids'])) {
				showmessage('no_privilege');
			} elseif(!$tospace['self'] && $blog['friend'] == 4) {
				$cookiename = "view_pwd_blog_$blog[blogid]";
				$cookievalue = empty($_G['cookie'][$cookiename])?'':$_G['cookie'][$cookiename];
				if($cookievalue != md5(md5($blog['password']))) {
					showmessage('no_privilege');
				}
			}

			if(!empty($blog['noreply'])) {
				showmessage('do_not_accept_comments');
			}
			if($blog['target_ids']) {
				$blog['target_ids'] .= ",$blog[uid]";
			}

			$hotarr = array('blogid', $blog['blogid'], $blog['hotuser']);
			$stattype = 'blogcomment';
			break;
		case 'sid':
			$query = DB::query("SELECT * FROM ".DB::table('home_share')." WHERE sid='$id'");
			$share = DB::fetch($query);
			if(empty($share)) {
				showmessage('sharing_does_not_exist');
			}

			$tospace = getspace($share['uid']);

			$hotarr = array('sid', $share['sid'], $share['hotuser']);
			$stattype = 'sharecomment';
			break;
		case 'pid':
			$query = DB::query("SELECT p.*, pf.hotuser
				FROM ".DB::table('home_poll')." p
				LEFT JOIN ".DB::table('home_pollfield')." pf ON pf.pid=p.pid
				WHERE p.pid='$id'");
			$poll = DB::fetch($query);
			if(empty($poll)) {
				showmessage('voting_does_not_exist');
			}
			$tospace = getspace($poll['uid']);
			if($poll['noreply']) {
				if(!$tospace['self'] && !in_array($_G['uid'], $tospace['friends'])) {
					showmessage('the_vote_only_allows_friends_to_comment');
				}
			}

			$hotarr = array('pid', $poll['pid'], $poll['hotuser']);
			$stattype = 'pollcomment';
			break;
		case 'lecturerid':
			if(function_exists("getcommentitem_$idtype")){
				$item = eval('return getcommentitem_'.$idtype.'($id);');
				
				if(empty($item)){
					showmessage('view_to_info_did_not_exist');
				}
				if ($item['lecid']) {
					$tospace = getspace($item['lecid']);
				}
				if (empty($tospace)) {
					$tospace = array("1");
				}
			}
			break;
		case 'shlecturerid':
			if(function_exists("getcommentitem_$idtype")){
				$item = eval('return getcommentitem_'.$idtype.'($id);');
				
				if(empty($item)){
					showmessage('view_to_info_did_not_exist');
				}
				if ($item['lecid']) {
					$tospace = getspace($item['lecid']);
				}
				if (empty($tospace)) {
					$tospace = array("1");
				}
			}
			break;
		case 'feed':
			if(function_exists("getcommentitem_$idtype")){
				$item = eval('return getcommentitem_'.$idtype.'($id);');
				
				if(empty($item)){
					showmessage('view_to_info_did_not_exist');
				}
				if ($item['uid']) {
					$tospace = getspace($item['uid']);
				}
				if (empty($tospace)) {
					$tospace = array("1");
				}
			}
			break;
		case 'docid':
			if(function_exists("getcommentitem_$idtype")){
				$item = eval('return getcommentitem_'.$idtype.'($id);');
				
				if(empty($item)){
					showmessage('view_to_info_did_not_exist');
				}
				$tospace = getspace($item['uid']);
				if(empty($tospace)){
					$tospace = array("1");
				}
			}
			break;
		case 'extraclassid':
			if(function_exists("getcommentitem_$idtype")){
				$item = eval('return getcommentitem_'.$idtype.'($id);');
				
				if(empty($item)){
					showmessage('view_to_info_did_not_exist');
				}
				$tospace = getspace($item['sugestuid']);
				if(empty($tospace)){
					$tospace = array("1");
				}
			}
			break;	
		case 'extralecid':
			if(function_exists("getcommentitem_$idtype")){
				$item = eval('return getcommentitem_'.$idtype.'($id);');
				
				if(empty($item)){
					showmessage('view_to_info_did_not_exist');
				}
				$tospace = getspace($item['sugestuid']);
				if(empty($tospace)){
					$tospace = array("1");
				}
			}
			break;	
		case 'extraorgid':
			if(function_exists("getcommentitem_$idtype")){
				$item = eval('return getcommentitem_'.$idtype.'($id);');
				
				if(empty($item)){
					showmessage('view_to_info_did_not_exist');
				}
				$tospace = getspace($item['sugestuid']);
				if(empty($tospace)){
					$tospace = array("1");
				}
			}
			break;				
		default:
			if(function_exists("getcommentitem_$idtype")){
				$item = eval('return getcommentitem_'.$idtype.'($id);');
				
				if(empty($item)){
					showmessage('view_to_info_did_not_exist');
				}
				$tospace = getspace($item['uid']);
			}
			break;
	}
	
	if(empty($tospace)) {
		showmessage('space_does_not_exist');
	}

	if($tospace['videophotostatus']) {
		if($idtype == 'uid') {
			ckvideophoto('wall', $tospace);
		} else {
			ckvideophoto('comment', $tospace);
		}
	}

	if(isblacklist($tospace['uid'])) {
		showmessage('is_blacklist');
	}

	if($hotarr && $tospace['uid'] != $_G['uid']) {
		hot_update($hotarr[0], $hotarr[1], $hotarr[2]);
	}

	$fs = array();
	$fs['icon'] = 'comment';
	$fs['target_ids'] = '';
	$fs['friend'] = '';
	$fs['body_template'] = '';
	$fs['body_data'] = array();
	$fs['body_general'] = '';
	$fs['images'] = array();
	$fs['image_links'] = array();

	switch ($_POST['idtype']) {
		case 'uid':
			$fs['icon'] = 'wall';
			$fs['title_template'] = 'feed_comment_space';
			$tospace['realname'] = user_get_user_name_by_username($tospace[username]);
			$fs['title_data'] = array('touser'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]\">$tospace[realname]</a>");
			break;
		case 'picid':
			$tospace['username']=user_get_user_name_by_username($tospace['username']);
			$fs['title_template'] = 'feed_comment_image';
			$fs['title_data'] = array('touser'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]\">".$tospace['username']."</a>");
			$fs['body_template'] = '{pic_title}';
			$fs['body_data'] = array('pic_title'=>$pic['title']);
			$fs['body_general'] = $summay;
			$fs['images'] = array(pic_get($pic['filepath'], 'album', $pic['thumb'], $pic['remote']));
			$fs['image_links'] = array("home.php?mod=space&uid=$tospace[uid]&do=album&picid=$pic[picid]");
			$fs['target_ids'] = $album['target_ids'];
			$fs['friend'] = $album['friend'];
			break;
		case 'blogid':
			$tospace['username']=user_get_user_name_by_username($tospace['username']);
			DB::query("UPDATE ".DB::table('home_blog')." SET replynum=replynum+1 WHERE blogid='$id'");
			$fs['title_template'] = 'feed_comment_blog';
			$fs['title_data'] = array('touser'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]\">".$tospace['username']."</a>", 'blog'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]&do=blog&id=$id\">$blog[subject]</a>");
			$fs['target_ids'] = $blog['target_ids'];
			$fs['friend'] = $blog['friend'];
			break;
		case 'sid':
			$tospace['username']=user_get_user_name_by_username($tospace['username']);
			$fs['title_template'] = 'feed_comment_share';
			$fs['title_data'] = array('touser'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]\">".$tospace['username']."</a>", 'share'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]&do=share&id=$id\">".str_replace(lang('spacecp', 'share_action'), '', $share['title_template'])."</a>");
			break;
		case 'extraclassid':
			$extraclassstar['totalstars']=$_POST['totalstars'];
			$extraclassstar['starsone']=$_POST['starsone'];
			$extraclassstar['starstwo']=$_POST['starstwo'];
			$extraclassstar['starsthree']=$_POST['starsthree'];
			if($extraclassstar['totalstars'] || $extraclassstar['starsone'] || $extraclassstar['starstwo'] || $extraclassstar['starsthree']){
				avgextraclassstar($id,$extraclassstar);
			}
			break;	
		case 'extralecid':
			$extralecstar['totalstars']=$_POST['totalstars'];
			$extralecstar['starsone']=$_POST['starsone'];
			$extralecstar['starstwo']=$_POST['starstwo'];
			$extralecstar['starsthree']=$_POST['starsthree'];
			if($extralecstar['totalstars'] || $extralecstar['starsone'] || $extralecstar['starstwo'] || $extralecstar['starsthree']){
				avgextralecstar($id,$extralecstar);
			}
			break;	
		case 'extraorgid':
			$extraorgstar['totalstars']=$_POST['totalstars'];
			$extraorgstar['starsone']=$_POST['starsone'];
			$extraorgstar['starstwo']=$_POST['starstwo'];
			$extraorgstar['starsthree']=$_POST['starsthree'];
			if($extraorgstar['totalstars'] || $extraorgstar['starsone'] || $extraorgstar['starstwo'] || $extraorgstar['starsthree']){
				avgextraorgstar($id,$extraorgstar);
			}
			break;	
		default:
			if(function_exists("getcommentfeed_$idtype"))
				$fs = eval('return getcommentfeed_'.$idtype.'($fs, $tospace, $item);');
			if(function_exists("getcommentreplyadd_$idtype"))
				eval('getcommentreplyadd_'.$idtype.'($id);');
	}
	if($atjson){
		$resarr=parseat1($message,$_G[uid],$atjson);
	}else{
		$resarr=parseat($message,$_G[uid],1);
	}
	$message=$resarr[message];
	if($icon=='resourcelist'){
		$setarr = array(
			'uid' => $tospace['uid'],
			'id' => $iconid,
			'idtype' => 'docid',
			'authorid' => $_G['uid'],
			'author' => $_G['username'],
			'dateline' => $_G['timestamp'],
			'message' =>preg_replace("/(<br \/>|<br>|\r|\n)/",'',  $message),
			'arlcid'=>$arlcid,
			'ip' => $_G['clientip']
		);
	}else{
		$setarr = array(
			'uid' => $tospace['uid'],
			'id' => $id,
			'idtype' => $_POST['idtype'],
			'authorid' => $_G['uid'],
			'author' => $_G['username'],
			'dateline' => $_G['timestamp'],
			'message' => preg_replace("/(<br \/>|<br>|\r|\n)/",'',  $message),
			'ip' => $_G['clientip'],
			'arlcid'=>$arlcid,
			'anonymity'=>$anonymity
		);
	}
	$messagearray=explode("回复",$message);
	if(!$messagearray[0]){
		if($resarr[atuids]){
			$setarr[uid]=$resarr[atuids][0];
		}
	}
	if($icon=='thread' || $icon=='poll' || $icon=='reward'){
		$pid = insertpost(array(
			'fid' => $fid,
			'tid' => $iconid,
			'first' => '0',
			'author' => $_G['username'],
			'authorid' => $_G['uid'],
			'dateline' => $_G['timestamp'],
			'message' => $message,
			'useip' => $_G['clientip'],
			'invisible' => 0,
			'anonymous' => 0,
			'usesig' => 0,
			'htmlon' =>1,
			'bbcodeoff' => -1,
			'smileyoff' => -1,
			'parseurloff' =>0,
			'attachment' => '0',
			'feedid'=>$id,
			'anonymity'=>$anonymity,
		));
		DB::query("UPDATE ".DB::table('forum_thread')." SET lastposter='$_G[username]', lastpost='$_G[timestamp]', replies=replies+1 WHERE tid='$iconid'", 'UNBUFFERED');
	}else{
		$cid = DB::insert('home_comment', $setarr, 1);//插入评论
		if($setarr[uid]!=$_G[uid]){
			notification_add($setarr[uid],"zq_comment",'您刚刚收到了来自<a href="home.php?mod=space&uid='.$_G[uid].'" target="_block">“'.$_G[myself][common_member_profile][$_G[uid]][realname].'”</a> 的评论，<a href="home.php?view=rcomment
">赶快去看看吧</a>', array(), 0);
		}
	}
	if($idtype=='docid'){
		DB::query("update ".DB::TABLE("home_feed")." set  commenttimes=commenttimes+1 where id=".$id." and icon='resourcelist' and idtype='resourceid'");
	}
	if($resarr[atuids]){
		foreach(array_keys($resarr[atuids]) as $uidkey){
			atrecord($resarr[atuids],$id);
			notification_add($resarr[atuids][$uidkey],"zq_at",'<a href="home.php?mod=space&uid='.$_G[uid].'" target="_block">“'.$_G[myself][common_member_profile][$_G[uid]][realname].'”</a> 在<a href="home.php?view=atme">评论</a>中提到了您，赶快去看看吧', array(), 0);
			
		}
	}
	if($resarr['atfids']){
		for($i=0;$i<count($resarr['atfids']);$i++){
			group_add_empirical_by_setting($_G['uid'],$resarr['atfids'][$i], 'at_group', $resarr['atfids'][$i]);
		}
	}
	$message=getstr($message,0,1,1,0,0,-1);
	if($atjson){
		$comresarr=parseat1($commessage,$_G[uid],$atjson);
	}else{
		$comresarr=parseat($commessage,$_G[uid],1);
	}
	if($_POST['idtype']=='feed'){
		DB::query("update ".DB::TABLE("home_feed")." set commenttimes=commenttimes+1 where feedid=".$id);
		if($forward){
			$value=DB::fetch_first("select * from ".DB::TABLE("home_feed")." where feedid=".$id);
			if($value['idtype']=='feed'){
					$prevalue=DB::fetch_first("select * from ".DB::TABLE("home_feed")." where feedid=".$value['id']);
					$id=$value['id'];
					$value['thisuid']=$value['uid'];
					$value['uid']=$prevalue['uid'];
					$value['thisusername']=user_get_user_name($value['thisuid']);
					$value['username']=$prevalue['username'];
					/*if($value[anonymity]!='-1'){
						$message=$message.'//@'.$value['thisusername'].':'.$value['body_general'];
					}*/
			}
			if($atjson){
				$resarr=parseat1($message,$_G[uid],$atjson);
			}else{
				$resarr=parseat($message,$_G[uid],1);
			}
			$message=$resarr[message];
			if($resarr['atfids']){
				$sharetofids=",".implode(',',$resarr['atfids']).",";
			}
			$feedarr = array(
				'appid' => '',
				'icon' => $value['icon'],
				'uid' => $_G['uid'],
				'username' => $space['username'],
				'dateline' => $_G['timestamp'],
				'title_template' => $value['title_template'],
				'title_data' => $value['title_data'],
				'body_template' => $value['body_template'],
				'body_data' => $value['body_data'],
				'body_general'=>$message,
				'image_1' => $value['image_1'],
				'image_1_link' => $value['image_1_link'],
				'image_2' => $value['image_2'],
				'image_2_link' => $value['image_2_link'],
				'image_3' => $value['image_3'],
				'image_3_link' => $value['image_3_link'],
				'image_4' =>$value['image_4'] ,
				'image_4_link' =>$value['image_4_link'],
				'image_5' =>$value['image_5'] ,
				'image_5_link' =>$value['image_5_link'],
				'target_ids'=>$value['target_ids'],
				'id' => $id,
				'idtype' => 'feed',
				'olduid'=>$value['uid'],
				'oldusername'=>$value['username'],
				'olddateline'=>$value['dateline'],
				'fid'=>$repeatsinfo['fid'],
				'sharetofids'=>$sharetofids,
				'anonymity'=>$anonymity
			);
		DB::insert('home_feed', $feedarr);
		if($anonymity=='0'){
			DB::query("update ".DB::TABLE("common_member_status")." set blogs=blogs+1 where uid=".$_G[uid]);
		}
		$feedid=DB::insert_id();
		if($anonymity!=-1){
			notification_add($feedarr[olduid],"zq_at",'<a href="home.php?mod=space&uid='.$_G[uid].'" target="_block">“'.$_G[myself][common_member_profile][$_G[uid]][realname].'”</a> 刚刚<a href="home.php?view=atme">转发了你的内容</a>', array(), 0);
			
			atrecord(array($feedarr[olduid]),$feedid);
		}
		
		DB::query("update ".DB::TABLE("home_feed")." set sharetimes=sharetimes+1 where feedid=".$id );
		if($value['idtype']=='feed'){
			DB::query("update ".DB::TABLE("home_feed")." set sharetimes=sharetimes+1 where feedid=".$value['id'] );
		}
				
		}
	}
	if($_POST['idtype']=='picid'){
		require_once libfile('function/feed');
		if($forward){
			$feed_hash_data = "picid{$id}";

			$picquery = DB::query("SELECT  pf.*, pic.*
				FROM ".DB::table('home_pic')." pic
				LEFT JOIN ".DB::table('home_picfield')." pf ON pf.picid=pic.picid
				WHERE pic.picid='$id'");
			if(!$pic = DB::fetch($picquery)) {
				showmessage('image_does_not_exist');
			}
			if(empty($pic['albumid'])) $pic['albumid'] = 0;

			$arr['title_template'] = lang('spacecp', 'share_image');
			$arr['body_template'] = '图片:<br>{username}<br>{title}';
			$arr['body_data'] = array(
			'username' => "<a href=\"home.php?mod=space&uid=$pic[uid]\">".user_get_user_name_by_username($pic['username'])."</a>",
			'title' => getstr($pic['title'], 100, 0, 1, 0, 0, -1)
			);
			if(strpos($pic['filepath'],'attachment/album')){
				$filepath=explode('.',$pic['filepath']);
				$arr['image'] =$filepath[0].'.thumb.'.$pic[type];
			}else{
				$arr['image']  = pic_get($pic['filepath'], 'album', $pic['thumb'], $pic['remote']);
			}
			$arr['image_link'] = "home.php?mod=space&uid=$pic[uid]&do=album&picid=$pic[picid]&diymode=1";
			
			if($atjson){
				$resarr=parseat1($commessage,$_G[uid],$atjson);
			}else{
				$resarr=parseat($commessage,$_G[uid],1);
			}
			
			$arr['body_general']=$resarr[message];
			
			feed_add('share',
						'{actor} '.$arr['title_template'],
						array('hash_data' => $feed_hash_data),
						$arr['body_template'],
						$arr['body_data'],
						$arr['body_general'],
						array($arr['image']),
						array($arr['image_link']),
						'','','',0,0,'pic',0,'',$repeatsinfo['fid'],array(),'',0,0,$anonymity
					);
		}
		
	}
	$action = 'comment';
	$becomment = 'getcomment';
	$note = $q_note = '';
	$note_values = $q_values = array();

	switch ($_POST['idtype']) {
		case 'uid':
			$n_url = "home.php?mod=space&uid=$tospace[uid]&do=wall&cid=$cid";

			$note_type = 'wall';
			$note = 'wall';
			$note_values = array('url'=>$n_url);
			$q_note = 'wall_reply';
			$q_values = array('url'=>$n_url);

			if($comment) {
				$msg = 'note_wall_reply_success';
				$magvalues = array('username' => user_get_user_name_by_username($tospace['username']));
				$becomment = '';
			} else {
				$msg = 'do_success';
				$magvalues = array();
				$becomment = 'getguestbook';
			}

			$action = 'guestbook';
			break;
		case 'picid':
			$n_url = "home.php?mod=space&uid=$tospace[uid]&do=album&picid=$id&cid=$cid";

			$note_type = 'piccomment';
			$note = 'pic_comment';
			$note_values = array('url'=>$n_url);
			$q_note = 'pic_comment_reply';
			$q_values = array('url'=>$n_url);

			$msg = 'do_success';
			$magvalues = array();
			hook_create_resource($cid,'home_pic_comment');//added by fumz，2010年9月25日10:53:59
			break;
		case 'blogid':
			$n_url = "home.php?mod=space&uid=$tospace[uid]&do=blog&id=$id&cid=$cid";

			$note_type = 'blogcomment';
			$note = 'blog_comment';
			$note_values = array('url'=>$n_url, 'subject'=>$blog['subject']);
			$q_note = 'blog_comment_reply';
			$q_values = array('url'=>$n_url);

			$msg = 'do_success';
			$magvalues = array();
			hook_create_resource($cid,'home_blog_comment');//added by fumz,2010-9-25 10:54:13
			break;
		case 'sid':
			$n_url = "home.php?mod=space&uid=$tospace[uid]&do=share&id=$id&cid=$cid";

			$note_type = 'sharecomment';
			$note = 'share_comment';
			$note_values = array('url'=>$n_url);
			$q_note = 'share_comment_reply';
			$q_values = array('url'=>$n_url);

			$msg = 'do_success';
			$magvalues = array();
			hook_create_resource($cid,'home_share_comment');//added by fumz,2010-9-25 10:54:45
			break;		
		default:
			if(function_exists("getcommentnotify_$idtype")){
				$notify = eval('return getcommentnotify_'.$idtype.'($id, $cid, $item);');
				if($notify){
					$note_type = $notify['note_type'];
					$note = $notify['note'];
					$note_values = $notify['note_values'];
					$q_note = $notify['q_note'];
					$q_values = $notify['q_values'];
				}
			}
			/**
			 * added by fumz,2010-9-25 13:08:43
			 */
			//begin
			if($_POST['idtype']=='nwktid'){
				hook_create_resource($cid,'home_nwkt_comment');//added by fumz,2010-9-25 13:08:43
			}elseif($_POST['idtype']=='docid'){
				if(!$_G['gp_fid']){//如果文档或其他资源专区id为0
					hook_create_resource($cid,'home_doc_comment');//如果评论个人文档
				}else{
					hook_create_resource($cid,'group_hcomment',$_G['gp_fid']);//如果评论在专区中的文档
				}
			}elseif($_POST['idtype']=='noticeid'){
				hook_create_resource($cid,'group_hcomment',$item['group_id']);
			}else{
				hook_create_resource($cid,'group_hcomment',$item['fid']);
			}
			//end
			$msg = 'do_success';
			$magvalues = array();
			break;
	}

	if(empty($comment)) {
		if($tospace['uid'] != $_G['uid']) {
			if(ckprivacy('comment', 'feed')) {
				require_once libfile('function/feed');
				$fs['title_data']['hash_data'] = "{$idtype}{$id}";
				//feed_add($fs['icon'], $fs['title_template'], $fs['title_data'], $fs['body_template'], $fs['body_data'], $fs['body_general'],$fs['images'], $fs['image_links'], $fs['target_ids'], $fs['friend']);
			}

			//经验值
			if($item['fid']){
				require_once libfile('function/group');
				if ($idtype=='lecturerid') {					
					group_add_empirical_by_setting($_G['uid'], $item['fid'], 'teacher_comment',$id);
				} else {
					group_add_empirical_by_setting($_G['uid'], $item['fid'], 'comment_someting', $id);
				}
			} elseif ($idtype=='noticeid') {
				group_add_empirical_by_setting($_G['uid'], $item['group_id'], 'comment_someting', $id);
			}

			$note_values['from_id'] = $_POST['id'];
			$note_values['from_idtype'] = $_POST['idtype'];
			$note_values['url'] .= "&goto=new#comment_{$cid}_li";

			notification_add($tospace['uid'], $note_type, $note, $note_values);
			
			$leavetype=$_POST['leavetype'];
			require_once libfile('function/credit');
			/*if ($leavetype=='leavemessage') {
				//留言积分
				credit_create_credit_log($_G['uid'], 'guestbook', $id);
				credit_create_credit_log($tospace['uid'], 'getguestbook', $id);//被留言积分
			}else {
				//评论积分
				credit_create_credit_log($_G['uid'], 'comment', $id);
			}*/
		}
		
		

	} elseif($comment['authorid'] != $_G['uid']) {
		//经验值
		if($item['fid']){
			require_once libfile('function/group');
			group_add_empirical_by_setting($_G['uid'], $item['fid'], 'comment_someting', $id);
		}
		
		notification_add($comment['authorid'], $note_type, $q_note, $q_values);
		
		//回复积分
		//require_once libfile('function/credit');
		//credit_create_credit_log($_G['uid'], 'reply', $id);
	}
	
	if($idtype == 'docid'){
		include_once libfile("function/doc");
		addCommentAmount($id);
	}

	if($stattype) {
		include_once libfile('function/stat');
		updatestat($stattype);
	}

	if($tospace['uid'] != $_G['uid']) {
		$needle = $id;
		if($_POST['idtype'] != 'uid') {
			$needle = $_POST['idtype'].$id;
		} else {
			$needle = $tospace['uid'];
		}
		updatecreditbyaction($action, 0, array(), $needle);
		if($becomment) {
			if($_POST['idtype'] == 'uid') {
				$needle = $_G['uid'];
			}
			updatecreditbyaction($becomment, $tospace['uid'], array(), $needle);
		}
	}
	$magvalues['cid'] = $cid;
	$magvalues['message'] = $comresarr[message];
	$magvalues['message'] = preg_replace("/(<br \/>|<br>|\r|\n)/",'', $magvalues['message']);

	showmessage($msg, dreferer(), $magvalues, $_G['gp_quickcomment'] ? array('msgtype' => 3, 'showmsg' => true) : array('showdialog' => 3, 'showmsg' => true, 'closetime' => 1));
}

$cid = empty($_GET['cid'])?0:intval($_GET['cid']);

if($_GET['op'] == 'edit') {

	$query = DB::query("SELECT * FROM ".DB::table('home_comment')." WHERE cid='$cid' AND authorid='$_G[uid]'");
	if(!$comment = DB::fetch($query)) {
		showmessage('no_privilege');
	}

	if(submitcheck('editsubmit')) {

		$message = getstr($_POST['message'], 0, 1, 1, 1, 2);
		if(strlen($message) < 2) showmessage('content_is_too_short');

		DB::update('home_comment', array('message'=>$message), array('cid' => $comment['cid']));

		showmessage('do_success', dreferer(), array('cid' => $comment['cid']), array('showdialog' => 1, 'showmsg' => true, 'closetime' => 1));
	}

	$comment['message'] = $bbcode->html2bbcode($comment['message']);

} elseif($_GET['op'] == 'delete') {
	if(submitcheck('deletesubmit')) {
		require_once libfile('function/delete');
		if(deletecomments(array($cid))) {
			showmessage('do_success', dreferer(), array('cid' => $cid), array('showdialog' => 1, 'showmsg' => true, 'closetime' => 1));
		} else {
			showmessage('no_privilege');
		}
	}

} elseif($_GET['op'] == 'reply') {

	$query = DB::query("SELECT * FROM ".DB::table('home_comment')." WHERE cid='$cid'");
	if(!$comment = DB::fetch($query)) {
		showmessage('comments_do_not_exist');
	}
	//print_r($comment);exit;
	$config = urlencode(getsiteurl().'home.php?mod=misc&ac=swfupload&op=config&doodle=1');
} else {

	showmessage('no_privilege');
}

include template('home/spacecp_comment');

?>