<?php
/**
 * $tablename 表态内容的表名(该表必须有click1, click2, click3, click4, click5字段分别代表漂亮 	酷毙 	雷人 	鲜花 	鸡蛋)
 * $id 表态内容的编号
 * $idtype 表态内容的编号名称(表字段名)
 * $clickid 表态类型(即 1,2,3,4,5分别代表click1, click2, click3, click4, click5)
 * $uid 表态用户
 */
function click_add($tablename, $id, $idtype, $clickid, $uid){
    global $_G;
	
	$anonymity=$_G[member][repeatsstatus];
    $query = DB::query("SELECT * FROM ".DB::table('home_clickuser')." WHERE uid='$uid' AND id='$id' AND idtype='$idtype'");
	if($value = DB::fetch($query)) {
		return false;
	}
    
    $setarr = array(
		'uid' => $uid,
		'username' => $_G['username'],
		'id' => $id,
		'idtype' => $idtype,
		'clickid' => $clickid,
		'dateline' => $_G['timestamp'],
		'anonymity'=>$anonymity
	);
	DB::insert('home_clickuser', $setarr);

	if(function_exists("clickupdate_$idtype")){
		eval('clickupdate_'.$idtype.'($clickid, $id);');
	}
	else{
		DB::query("UPDATE $tablename SET click{$clickid}=click{$clickid}+1 WHERE $idtype='$id'");
	}
    return true;
}
//你我课堂
function clicksql_nwktid($id){
	return "SELECT * FROM ".DB::table('home_nwkt')." WHERE nwktid='$id'";
}

function clicktable_nwktid(){
	return DB::table('home_nwkt');
}

function clickfeed_nwktid($fs, $item, $click){
	$item[username]=user_get_user_name_by_username($item[username]);
	$fs['title_template'] = 'feed_click_nwkt';
	$fs['title_data'] = array(
		'touser' => "<a href=\"home.php?mod=space&uid=$item[uid]\">{$item[username]}</a>",
		'subject' => "<a href=\"home.php?mod=space&uid=$item[uid]&do=nwkt&id=$item[nwktid]\">$item[subject]</a>",
		'click' => $click['name']
	);
	return $fs;
}

function clicknotify_nwktid($item, $click){
	$uid=DB::result_first("SELECT uid FROM ".DB::table("home_nwkt")." WHERE nwktid='".$item['nwktid']."'");

	$n_url = "home.php?mod=space&uid=$uid&do=nwkt&id=".$item['nwktid'];
	$note_type = 'nwkt';
	$q_note = 'click_nwktid';
	$q_note_values = array('actor' => "<a href=\"home.php?mod=space&uid=$item[uid]\" target=\"_blank\">$item[username]</a>", 'subject' => $item['subject'], 'click' => $click['name'],'url'=>$n_url);
	return array('note_type' => $note_type, 'q_note' => $q_note, 'q_note_values' => $q_note_values);
}
//-------------------------------------
function clicksql_noticeid($id){
	return "SELECT * FROM ".DB::table('notice')." WHERE id='$id'";
}

function clicktable_noticeid(){
	return DB::table('notice');
}

function clickfeed_noticeid($fs, $item, $click){
	$fs['title_template'] = 'feed_click_notice';
	$item[realname] = user_get_user_name_by_username($item[username]);
	$fs['title_data'] = array(
		'touser' => "<a href=\"home.php?mod=space&uid=$item[uid]\">{$item[realname]}</a>",
		'click' => $click['name'],
		'subject' => "<a href=\"forum.php?mod=group&action=plugin&fid=$item[group_id]&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid=$item[id]&notice_action=view\">$item[title]</a>"
	);
	
	return $fs;
}

function clicknotify_noticeid($item){
	$note_type = 'clicknotice';
	$q_note = 'click_notice';
	$q_note_values = array(
		'url' => "forum.php?mod=group&action=plugin&fid=$item[group_id]&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid=$item[id]&notice_action=view",
		'subject' => $item['title'],
		'from_id' => $item['noticeid'],
		'from_idtype' => 'noticeid'
	);
	
	return array('note_type' => $note_type, 'q_note' => $q_note, 'q_note_values' => $q_note_values);
}

function clickupdate_noticeid($clickid, $id){
	DB::query("UPDATE ".DB::table('notice')." SET click{$clickid}=click{$clickid}+1 WHERE id='$id'");
}
//专区相册表态
function clicksql_gpicid($id){
	return "SELECT p.*, a.fid FROM ".DB::table('group_pic')." p, ".DB::table('group_album')." a WHERE p.picid='$id' AND p.albumid=a.albumid";
}

function clicktable_gpicid(){
	return DB::table('group_pic');
}

function clickfeed_gpicid($fs, $item, $click){
	$fs['title_template'] = 'feed_click_pic';
	$item[realname] = user_get_user_name_by_username($item[username]);
	$fs['title_data'] = array(
		'touser' => "<a href=\"home.php?mod=space&uid=$item[uid]\">{$item[realname]}</a>",
		'click' => $click['name']
	);
	include_once libfile("function/home");
	
	$fs['images'] = array(pic_get($item['filepath'], 'plugin_groupalbum2', $item['thumb'], $item['remote']));
	$fs['image_links'] = array("forum.php?mod=group&action=plugin&fid=$item[fid]&plugin_name=groupalbum2&plugin_op=groupmenu&picid=$item[picid]&groupalbum2_action=index");
	$fs['body_general'] = $item['title'];
	
	return $fs;
}

function clicknotify_gpicid($item, $click){
	global $_G;
	
	$forumname = DB::result_first("SELECT name FROM ".DB::table("forum_forum")." WHERE fid='$item[fid]'");
	
	$note_type = 'gpic';
	$q_note = 'gpic_click';
	$q_note_values = array('actor' => "<a href=\"home.php?mod=space&uid=$item[uid]\" target=\"_blank\">$item[username]</a>", 'pic' => $item['title'], 'group' => "<a href=\"forum.php?mod=group&fid=$item[fid]\" target=\"_blank\">".$forumname."</a>", 'click' => $click['name']);
	
	return array('note_type' => $note_type, 'q_note' => $q_note, 'q_note_values' => $q_note_values);
}

function clickupdate_gpicid($clickid, $id){
	DB::query("UPDATE ".DB::table('group_pic')." SET click{$clickid}=click{$clickid}+1 WHERE picid='$id'");
}

//文档
function clicksql_docid($id){
	$sql = "SELECT * FROM ".DB::table('doc_click')." WHERE docid='$id'";
	$doc = DB::result_first($sql);
	if(!$doc || empty($doc['docid'])){
		DB::insert('doc_click', array('docid' => $id));
	}
	return $sql;
}

function clicktable_docid(){
	return DB::table('doc_click');
}

function clickitem_docid($id){
	include_once libfile('function/doc');
	
	$doc = getFile($id);
	if($doc && !empty($doc['id'])){
		if($doc['zoneid']){
			$fid = $doc['zoneid'];
			$fname = DB::result_first("SELECT name FROM ".DB::table('forum_forum')." WHERE fid = '$fid'");
			$doc['fname'] = $fname;
			$doc['fid'] = $fid;			
		}
		$doc['dateline'] = "";
		$doc['uid'] = "";
		return $doc;
	}
	else{
		return false;
	}
}

function clickfeed_docid($fs, $item, $click){
	$fs['title_template'] = 'feed_click_doc';
	$item[realname] = user_get_user_name_by_username($item[username]);
	$fs['title_data'] = array(
		'touser' => "<a href=\"home.php?mod=space&uid=$item[userid]\">{$item[realname]}</a>",
		'subject' => "<a href=\"$item[titlelink]\" target=\"_blank\">$item[title]</a>",
		'click' => $click['name']
	);
	return $fs;
}

function clicknotify_docid($item, $click){
	$note_type = 'doc';
	if($item['zoneid']){
		$q_note = 'gdoc_click';
		$q_note_values = array('actor' => "<a href=\"home.php?mod=space&uid=$item[userid]\" target=\"_blank\">$item[username]</a>", 'title' => $item['title'], 'group' => "<a href=\"forum.php?mod=group&fid=$item[fid]\" target=\"_blank\">$item[fname]</a>", 'click' => $click['name']);
	}
	else{
		$q_note = 'doc_click';
		$q_note_values = array('actor' => "<a href=\"home.php?mod=space&uid=$item[userid]\" target=\"_blank\">$item[username]</a>", 'title' => $item['title'], 'click' => $click['name']);
	}
	
	return array('note_type' => $note_type, 'q_note' => $q_note, 'q_note_values' => $q_note_values);
}

?>
