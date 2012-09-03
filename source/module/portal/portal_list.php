<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_list.php 9843 2010-05-05 05:38:57Z wangjinbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$catid = empty($_GET['catid'])?0:intval($_GET['catid']);
if(empty($catid)) {
	showmessage("list_choose_category");
}
$cat = $_G['cache']['portalcategory'][$catid];
if(empty($cat)) {
	showmessage("list_category_noexist");
}

$cat = category_remake($catid);

if($cat['subs']) {
	$subcatids = array_keys($cat['subs']);
	$subcatids[] = $catid;

	$wheresql = "catid IN (".dimplode($subcatids).")";
} else {
	$wheresql = "catid='$catid'";
}

$perpage = 15;
$page = intval($_GET['page']);
$start = ($page-1)*$perpage;
if($start<0) $start = 0;

$list = array();
$multi = '';

$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('portal_article_title')." WHERE $wheresql"), 0);
if($count) {
	$query = DB::query("SELECT * FROM ".DB::table('portal_article_title')." WHERE $wheresql ORDER BY dateline DESC LIMIT $start,$perpage");
	while ($value = DB::fetch($query)) {
		$value['catname'] = $value['catid']==$cat['catid']?$cat['catname']:$cat['subs'][$value['catid']]['catname'];
		if($value['pic']) $value['pic'] = pic_get($value['pic'], 'portal', $value['thumb'], $value['remote']);
		$value['dateline'] = dgmdate($value['dateline']);
		$list[] = $value;
	}

	$multi = multi($count, $perpage, $page, "portal.php?mod=list&catid=$catid");
}

$navtitle = $cat['catname'].($page>1?" ( $page )":'').' -';

include_once template("diy:portal/list:$catid");

?>