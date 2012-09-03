<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_comment.php 10928 2010-05-18 07:32:05Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$aid = empty($_GET['aid'])?0:intval($_GET['aid']);
if(empty($aid)) {
	showmessage("comment_no_article_id");
}
$article = DB::fetch_first("SELECT a.*, ac.*
	FROM ".DB::table('portal_article_title')." a
	LEFT JOIN ".DB::table('portal_article_count')." ac
	ON ac.aid=a.aid
	WHERE a.aid='$aid'");
if(empty($article)) {
	showmessage("comment_article_no_exist");
}

$perpage = 5;
$page = intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;

$commentlist = array();
$multi = '';

if($article['commentnum']) {
	$query = DB::query("SELECT * FROM ".DB::table('portal_comment')." WHERE aid='$aid' ORDER BY cid DESC LIMIT $start,$perpage");
	while ($value = DB::fetch($query)) {
		$value['allowop'] = 1;
		$commentlist[] = $value;
	}
}

$multi = multi($article['commentnum'], $perpage, $page, "portal.php?mod=comment&aid=$aid");

include_once template("diy:portal/comment");

?>