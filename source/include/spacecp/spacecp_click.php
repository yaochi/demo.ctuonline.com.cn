<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_click.php 10793 2010-05-17 01:52:12Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once libfile("function/click");

$clickid = empty($_GET['clickid'])?0:intval($_GET['clickid']);
$idtype = empty($_GET['idtype'])?'':trim($_GET['idtype']);
$id = empty($_GET['id'])?0:intval($_GET['id']);

loadcache('click');
$clicks = empty($_G['cache']['click'][$idtype])?array():$_G['cache']['click'][$idtype];
//针对专区图片和你我课堂，使用日志的表态属性，增加通知公告
if(in_array($idtype, array('gpicid', 'nwktid', 'noticeid', 'docid'))){
	$clicks = empty($_G['cache']['click']['blogid'])?array():$_G['cache']['click']['blogid'];
}


$click = $clicks[$clickid];

if(empty($click)) {
	showmessage('click_error');
}

switch ($idtype) {
	case 'picid':
		$sql = "SELECT p.*, s.username, a.friend, pf.hotuser FROM ".DB::table('home_pic')." p
			LEFT JOIN ".DB::table('home_picfield')." pf ON pf.picid=p.picid
			LEFT JOIN ".DB::table('home_album')." a ON a.albumid=p.albumid
			LEFT JOIN ".DB::table('common_member')." s ON s.uid=p.uid
			WHERE p.picid='$id'";
		$tablename = DB::table('home_pic');
		break;
	case 'aid':
		$sql = "SELECT a.* FROM ".DB::table('portal_article_title')." a
			LEFT JOIN ".DB::table('portal_article_content')." af ON af.aid=a.aid
			WHERE a.aid='$id'";
		$tablename = DB::table('portal_article_title');
		break;
	case 'gpicid'://专区图片
		$sql = clicksql_gpicid($id);
		$tablename = clicktable_gpicid();
		break;
	case 'noticeid'://通知公告
		$sql = clicksql_noticeid($id);
		$tablename = clicktable_noticeid();
		break;
	case 'nwktid'://你我课堂
		$sql = clicksql_nwktid($id);
		$tablename = clicktable_nwktid();
		break;
	case 'docid'://文档
		$sql = clicksql_docid($id);
		$tablename = clicktable_docid();
		break;
	default:
		$idtype = 'blogid';
		$sql = "SELECT b.*, bf.hotuser FROM ".DB::table('home_blog')." b
			LEFT JOIN ".DB::table('home_blogfield')." bf ON bf.blogid=b.blogid
			WHERE b.blogid='$id'";
		$tablename = DB::table('home_blog');
		break;
}

if($idtype == 'docid'){
	$item = clickitem_docid($id);
	if(!$item){
		showmessage('click_item_error');
	}
}
else{
	$query = DB::query($sql);
	if(!$item = DB::fetch($query)) {
		showmessage('click_item_error');
	}
}
$hash = md5($item['uid']."\t".$item['dateline']);
if($_GET['op'] == 'add') {
	if($item[userid]){
		$item['uid']=$item[userid];
	}
	if(!checkperm('allowclick') || $_GET['hash'] != $hash) {
		showmessage('no_privilege');
	}
	
	if($item['uid'] == $_G['uid']) {
		showmessage('click_no_self');
	}

	if(isblacklist($item['uid'])) {
		showmessage('is_blacklist');
	}

    require_once libfile("function/click");
    if(!click_add($tablename, $id, $idtype, $clickid, $space['uid'])){
        showmessage('click_have');
    }

	hot_update($idtype, $id, $item['hotuser']);

	$note_type = '';
	$q_note = '';
	$q_note_values = array();

	$fs = array();
	if($item['uid']){
		switch ($idtype) {
			case 'blogid':
				$fs['title_template'] = 'feed_click_blog';
				$item[realname] = user_get_user_name_by_username($item[username]);
				$fs['title_data'] = array(
					'touser' => "<a href=\"home.php?mod=space&uid=$item[uid]\">{$item[realname]}</a>",
					'subject' => "<a href=\"home.php?mod=space&uid=$item[uid]&do=blog&id=$item[blogid]\">$item[subject]</a>",
					'click' => $click['name']
				);
	
				$note_type = 'clickblog';
				$q_note = 'click_blog';
				$q_note_values = array(
					'url'=>"home.php?mod=space&uid=$item[uid]&do=blog&id=$item[blogid]",
					'subject'=>$item['subject'],
					'from_id' => $item['blogid'],
					'from_idtype' => 'blogid'
				);
				break;
			case 'aid':
				$fs['title_template'] = 'feed_click_article';
				$item[realname] = user_get_user_name_by_username($item[username]);
				$fs['title_data'] = array(
					'touser' => "<a href=\"home.php?mod=space&uid=$item[uid]\">{$item[realname]}</a>",
					'subject' => "<a href=\"portal.php?mod=view&aid=$item[aid]\">$item[title]</a>",
					'click' => $click['name']
				);
	
				$note_type = 'clickarticle';
				$q_note = 'click_article';
				$q_note_values = array(
					'url'=>"portal.php?mod=view&aid=$item[aid]",
					'subject'=>$item['title'],
					'from_id' => $item['aid'],
					'from_idtype' => 'aid'
				);
				break;
			case 'picid':
				$fs['title_template'] = 'feed_click_pic';
				$fs['title_data'] = array(
					'touser' => "<a href=\"home.php?mod=space&uid=$item[uid]\">{$item[username]}</a>",
					'click' => $click['name']
				);
				$fs['images'] = array(pic_get($item['filepath'], 'album', $item['thumb'], $item['remote']));
				$fs['image_links'] = array("home.php?mod=space&uid=$item[uid]&do=album&picid=$item[picid]");
				$fs['body_general'] = $item['title'];
	
				$note_type = 'clickpic';
				$q_note = 'click_pic';
				$q_note_values = array(
					'url'=>"home.php?mod=space&uid=$item[uid]&do=album&picid=$item[picid]",
					'from_id' => $item['picid'],
					'from_idtype' => 'picid'
				);
				break;
			case 'gpicid'://专区图片
				$fs = clickfeed_gpicid($fs, $item, $click);
				$note = clicknotify_gpicid($item, $click);
				$note_type = $note['note_type'];
				$q_note = $note['q_note'];
				$q_note_values = $note['q_note_values'];
				break;
			case 'noticeid'://通知公告
				$fs = clickfeed_noticeid($fs, $item, $click);
				$note = clicknotify_noticeid($item);
				$note_type = $note['note_type'];
				$q_note = $note['q_note'];
				$q_note_values = $note['q_note_values'];
				break;
			case 'nwktid'://你我课堂
				$fs = clickfeed_nwktid($fs, $item, $click);
				$note = clicknotify_nwktid($item, $click);
				$note_type = $note['note_type'];
				$q_note = $note['q_note'];
				$q_note_values = $note['q_note_values'];
				break;
			case 'docid'://文档
				$fs = clickfeed_docid($fs, $item, $click);
				$note = clicknotify_docid($item, $click);
				$note_type = $note['note_type'];
				$q_note = $note['q_note'];
				$q_note_values = $note['q_note_values'];
				$item['uid'] = $item['userid'];
				break;
		}
	}
	if(empty($item['friend']) && ckprivacy('click', 'feed')) {
		require_once libfile('function/feed');
		$fs['title_data']['hash_data'] = "{$idtype}{$id}";
		//feed_add('click', $fs['title_template'], $fs['title_data'], '', array(), $fs['body_general'],$fs['images'], $fs['image_links']);
	}

	updatecreditbyaction('click', 0, array(), $idtype.$id);

	require_once libfile('function/stat');
	updatestat('click');

	//经验值
	if($item['fid']){
		require_once libfile('function/group');
		group_add_empirical_by_setting($_G['uid'], $item['fid'], 'express_someting', $id);
	}
	if($item['uid']){
		//notification_add($item['uid'], $note_type, $q_note, $q_note_values);
	}
	//表态积分
	require_once libfile('function/credit');
	credit_create_credit_log($_G['uid'], 'click', $id);

	showmessage('click_success', '', array('idtype' => $idtype, 'id' => $id, 'clickid' => $clickid), array('msgtype' => 3, 'showmsg' => true, 'closetime' => 1));

} elseif ($_GET['op'] == 'show') {

	$maxclicknum = 0;
	foreach ($clicks as $key => $value) {
		$value['clicknum'] = $item["click{$key}"];
		$value['classid'] = mt_rand(1, 4);
		if($value['clicknum'] > $maxclicknum) $maxclicknum = $value['clicknum'];
		$clicks[$key] = $value;
	}

	$perpage = 22;
	$page = intval($_GET['page']);
	$start = ($page-1)*$perpage;
	if($start < 0) $start = 0;

	$count = getcount('home_clickuser', array('id'=>$id, 'idtype'=>$idtype));
	$clickuserlist = array();
	$click_multi = '';

	if($count) {
		$query = DB::query("SELECT * FROM ".DB::table('home_clickuser')."
			WHERE id='$id' AND idtype='$idtype'
			ORDER BY dateline DESC
			LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			$value['clickname'] = $clicks[$value['clickid']]['name'];
			if($value['anonymity']>0){
				include_once libfile('function/repeats','plugin/repeats');
				$repeatsinfo=getforuminfo($value['anonymity']);
				$value[repeats]=$repeatsinfo;
			}
			$clickuserlist[] = $value;
			$count++;
		}

		//$click_multi = multi($count, $perpage, $page, "home.php?mod=spacecp&ac=click&op=show&clickid=$clickid&idtype=$idtype&id=$id");
	}
}

include_once(template('home/spacecp_click'));

?>