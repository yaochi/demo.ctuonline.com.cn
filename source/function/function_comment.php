<?php
/* Function: 评论接口
 * Com.:
 * Author: wuhan
 * Date: 2010-7-30
 */
//讲师管理评价
function getcommentitem_lecturerid($id) {
	global $_G;
	$query = DB::query("SELECT * FROM ".DB::table('lecturer')." WHERE id='$id'");
	$lecturer = DB::fetch($query);
	$lecturer[uid] = $_G['uid'];
	return $lecturer;
}

//上海专区讲师管理评价
function getcommentitem_shlecturerid($id) {
	global $_G;
	$query = DB::query("SELECT * FROM ".DB::table('shlecture')." WHERE id='$id'");
	$lecturer = DB::fetch($query);
	$lecturer[uid] = $_G['uid'];
	return $lecturer;
}

/*
function getcommentfeed_lecturerid($fs, $tospace, $item) {
	$fs['title_template'] = 'feed_comment_lecturer';
	$fs['title_data'] = array('touser'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]\">".$tospace['username']."</a>", 'notice'=>"<a href=\"forum.php?mod=group&action=plugin&fid=$item[group_id]&plugin_name=lecturermanage&plugin_op=groupmenu&diy=&lecid=$id&lecturermanage_action=view\">$item[name]</a>");
	
	return $fs;
}*/

function getcommentnotify_lecturerid($id, $cid, $item) {
	global $_G;
	$n_url = "forum.php?mod=group&action=plugin&fid=$item[fid]&plugin_name=lecturermanage&plugin_op=groupmenu&diy=&lecid=$item[id]&lecturermanage_action=view&cid=$cid";

	$note_type = 'zq_teacher';
	$note = 'lecturer_comment';
	$note_values = array('url'=>$n_url, 'lecname'=>$item['name']);
	$q_note = 'lecturer_comment_reply';
	$q_values = array('url'=>$n_url, 'lecname'=>$item['name']);
	
	return array('note_type' => $note_type, 'note' => $note, 'note_values' => $note_values, 'q_note' => $q_note, 'q_values' => $q_values);
}

function getcommentreplyadd_lecturerid($id) {
	
}

function getcommentreplyremove_lecturerid($ids, $num) {

}

//通知公告评论
function getcommentitem_noticeid($id) {
	$query = DB::query("SELECT * FROM ".DB::table('notice')." WHERE id='$id'");
	echo print_r($query, true);
	return DB::fetch($query);
}

function getcommentfeed_noticeid($fs, $tospace, $item) {
	$tospace['username']=user_get_user_name_by_username($tospace['username']);
	$fs['title_template'] = 'feed_comment_notice';
	$fs['title_data'] = array('touser'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]\">".$tospace['username']."</a>", 'notice'=>"<a href=\"forum.php?mod=group&action=plugin&fid=$item[group_id]&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid=$item[id]&notice_action=view\">$item[title]</a>");
	
	return $fs;
}

function getcommentnotify_noticeid($id, $cid, $item) {
	$n_url = "forum.php?mod=group&action=plugin&fid=$item[group_id]&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid=$item[id]&notice_action=view&cid=$cid";

	$note_type = 'noticecomment';
	$note = 'notice_comment';
	$note_values = array('url'=>$n_url, 'subject'=>$item['title']);
	$q_note = 'notice_comment_reply';
	$q_values = array('url'=>$n_url);
	
	return array('note_type' => $note_type, 'note' => $note, 'note_values' => $note_values, 'q_note' => $q_note, 'q_values' => $q_values);
}

function getcommentreplyadd_noticeid($id) {
	DB::query("UPDATE ".DB::table('notice')." SET replynum=replynum+1 WHERE id='$id'");
}

function getcommentreplyremove_noticeid($ids, $num) {
	DB::query("UPDATE ".DB::table('notice')." SET replynum=replynum-$num WHERE id IN (".dimplode($ids).")");
}

//你我课堂 评论
function getcommentitem_nwktid($id){
	$query = DB::query("SELECT * FROM ".DB::table('home_nwkt')." WHERE nwktid='$id'");
	return DB::fetch($query);
}

function getcommentfeed_nwktid($fs, $tospace, $item){
	$tospace['username']=user_get_user_name_by_username($tospace['username']);
	$fs['title_template'] = 'feed_comment_nwkt';
	$fs['title_data'] = array('touser'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]\">".$tospace['username']."</a>", 'nwkt'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]&do=nwkt&id=$id\">$item[subject]</a>");
	
	return $fs;
}

function getcommentreplyadd_nwktid($id){
	DB::query("UPDATE ".DB::table('home_nwkt')." SET replynum=replynum+1 WHERE nwktid='$id'");
}

function getcommentreplyremove_nwktid($ids, $num){
	DB::query("UPDATE ".DB::table('home_nwkt')." SET replynum=replynum-$num WHERE nwktid IN (".dimplode($ids).")");
}

function getcommentreplyremove_feed($ids, $num){
	DB::query("UPDATE ".DB::table('home_feed')." SET commenttimes=commenttimes-$num WHERE feedid IN (".dimplode($ids).")");
}

function getcommentnotify_nwktid($id, $cid, $item){

	$uid=DB::result_first("SELECT uid FROM ".DB::table("home_nwkt")." WHERE nwktid='$id'");

	$n_url = "home.php?mod=space&uid=$uid&do=nwkt&id=$id&cid=$cid";
	
	$note_type = 'nwkt';
	$note = 'nwkt_comment';
	$note_values = array('actor' => "<a href=\"home.php?mod=space&uid=$item[uid]\" target=\"_blank\">$item[username]</a>", 'subject' => $item['subject'],'url'=>$n_url);
	$q_note = 'nwkt_comment_reply';
	$q_values = array('actor' => "<a href=\"home.php?mod=space&uid=$item[uid]\" target=\"_blank\">$item[username]</a>", 'subject' => $item['subject'],'url'=>$n_url);
	
	return array('note_type' => $note_type, 'note' => $note, 'note_values' => $note_values, 'q_note' => $q_note, 'q_values' => $q_values);
}
//专区图片 评论
function getcommentitem_gpicid($id){
	$query = DB::query("SELECT p.*, a.fid FROM ".DB::table('group_pic')." p, ".DB::table('group_album')." a WHERE p.picid='$id' AND p.albumid = a.albumid");
	return DB::fetch($query);
}

function getcommentfeed_gpicid($fs, $tospace, $item){
	global $_G;
	
	include_once libfile("function/home");	
	
	$message = getstr($_POST['message'], 0, 1, 1, 1, 2);
	$summay = getstr($message, 150, 1, 1, 0, 0, -1);
	$tospace['username']=user_get_user_name_by_username($tospace['username']);	
	$fs['title_template'] = 'feed_comment_image';
	$fs['title_data'] = array('touser'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]\">".$tospace['username']."</a>");
	$fs['body_template'] = '{pic_title}';
	$fs['body_data'] = array('pic_title'=>$item['title']);
	$fs['body_general'] = $summay;
	$fs['images'] = array(pic_get($item['filepath'], 'plugin_groupalbum2', $item['thumb'], $item['remote']));
	$fs['image_links'] = array("forum.php?mod=group&action=plugin&fid=$item[fid]&plugin_name=groupalbum2&plugin_op=groupmenu&picid=$item[picid]&groupalbum2_action=index");
	
	return $fs;
}

function getcommentnotify_gpicid($id, $cid, $item){
	global $_G;
	
	$forumname = DB::result_first("SELECT name FROM ".DB::table("forum_forum")." WHERE fid='$item[fid]'");
	
	$note_type = 'gpic';
	$note = 'gpic_comment';
	$note_values = array('actor' => "<a href=\"home.php?mod=space&uid=$item[uid]\" target=\"_blank\">$item[username]</a>", 'pic' => $item['title'], 'group' => "<a href=\"forum.php?mod=group&fid=$item[fid]\" target=\"_blank\">$forumname</a>");
	$q_note = 'gpic_comment_reply';
	$q_values = array('actor' => "<a href=\"home.php?mod=space&uid=$item[uid]\" target=\"_blank\">$item[username]</a>", 'pic' => $item['title'], 'group' => "<a href=\"forum.php?mod=group&fid=$item[fid]\" target=\"_blank\">$forumname</a>");
	
	return array('note_type' => $note_type, 'note' => $note, 'note_values' => $note_values, 'q_note' => $q_note, 'q_values' => $q_values);
}

//文档
function getcommentitem_docid($id){
	include_once libfile('function/doc');
	
	$doc = getFile($id);
	if($doc && !empty($doc['id'])){
		if($doc['zoneid']){
			$fid = $doc['zoneid'];
			$fname = DB::result_first("SELECT name FROM ".DB::table('forum_forum')." WHERE fid = '$fid'");
			$doc['fname'] = $fname;
			$doc['fid'] = $fid;			
		}
		$doc['uid'] = $doc['userid'];
		return $doc;
	}
	else{
		return false;
	}
}

function getcommentfeed_docid($fs, $tospace, $item){
	$tospace['username']=user_get_user_name_by_username($tospace['username']);
	if($item['type']==1){
		$fs['title_template'] = 'feed_comment_doc';
	}elseif($item['type']==2){
		$fs['title_template'] = 'feed_comment_case';
	}elseif($item['type']==4){
		$fs['title_template'] = 'feed_comment_class';
	}
	$fs['title_data'] = array('touser'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]\">".$tospace['username']."</a>", 'doc'=>"<a href=\"$item[titlelink]\" target=\"_blank\">$item[title]</a>");
	
	return $fs;
}

function getcommentnotify_docid($id, $cid, $item){
	global $_G;
	
	$note_type = 'doc';
	
	if($item['fid']){
		$note = 'gdoc_comment';
		$note_values = array('actor' => "<a href=\"home.php?mod=space&uid=$item[uid]\" target=\"_blank\">$item[username]</a>", 'title' => "<a href=\"$item[titlelink]\" target=\"_blank\">$item[title]</a>", 'group' => "<a href=\"forum.php?mod=group&fid=$item[fid]\" target=\"_blank\">$forumname</a>");
		$q_note = 'gdoc_comment_reply';
		$q_values = array('actor' => "<a href=\"home.php?mod=space&uid=$item[uid]\" target=\"_blank\">$item[username]</a>", 'title' =>"<a href=\"$item[titlelink]\" target=\"_blank\">$item[title]</a>", 'group' => "<a href=\"forum.php?mod=group&fid=$item[fid]\" target=\"_blank\">$forumname</a>");
	}else{
		$note = 'doc_comment';
		$note_values = array('actor' => "<a href=\"home.php?mod=space&uid=$item[uid]\" target=\"_blank\">$item[username]</a>", 'title' => "<a href=\"$item[titlelink]\" target=\"_blank\">$item[title]</a>");
		$q_note = 'doc_comment_reply';
		$q_values = array('actor' => "<a href=\"home.php?mod=space&uid=$item[uid]\" target=\"_blank\">$item[username]</a>", 'title' => "<a href=\"$item[titlelink]\" target=\"_blank\">$item[title]</a>");
	}
	
	
	return array('note_type' => $note_type, 'note' => $note, 'note_values' => $note_values, 'q_note' => $q_note, 'q_values' => $q_values);
}

//专区分享评论 add by yangyang 2011-3-9 9:45:00
function getcommentitem_gsid($id) {
	$query = DB::query("SELECT * FROM ".DB::table('group_share')." WHERE sid='$id'");
	return DB::fetch($query);
}

//外部课程评论 add by yangyang 2011-8-1 
function getcommentitem_extraclassid($id) {
	$query = DB::query("SELECT * FROM ".DB::table('extra_class')." WHERE id='$id'");
	return DB::fetch($query);
}

//外部讲师评论 add by yangyang 2011-8-1 
function getcommentitem_extralecid($id) {
	$query = DB::query("SELECT * FROM ".DB::table('extra_lecture')." WHERE id='$id'");
	return DB::fetch($query);
}

//外部机构评论 add by yangyang 2011-8-1 
function getcommentitem_extraorgid($id) {
	$query = DB::query("SELECT * FROM ".DB::table('extra_org')." WHERE id='$id'");
	return DB::fetch($query);
}


//动态评价 add by yangyang 2011-10-25
function getcommentitem_feed($id) {
	$query = DB::query("SELECT * FROM ".DB::table('home_feed')." WHERE feedid='$id'");
	return DB::fetch($query);
}

?>
