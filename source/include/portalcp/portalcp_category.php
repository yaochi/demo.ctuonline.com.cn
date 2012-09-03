<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_article.php 7701 2010-04-12 06:01:33Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
require_once libfile('function/portalcp');

$catid = max(0,intval($_GET['catid']));
$_GET['type'] = isset($_GET['type']) && in_array($_GET['type'], array('unpushed', 'pushed')) ? $_GET['type'] : 'all';
$typearr[$_GET['type']] = 'class="a"';

$permission = getallowcategory($_G['uid']);

if (!checkperm('allowmanagearticle') && !$permission[$catid]['allowmanage'] && !$permission[$catid]['allowpush']) {
	showmessage('portal_nopermission');
}

$category = getcategory();
$cate = $category[$catid];

if(empty($cate)) {
	showmessage('article_category_empty');
}

$allowmanage = checkperm('allowmanagearticle') || $permission[$catid]['allowmanage'] ? true : false;
$allowpush = checkperm('allowmanagearticle') || $permission[$catid]['allowpush'] ? true : false;

$wherearr = array();
$wherearr[] = "catid='$catid'";
if($_GET['searchkey']) {
	$_GET['searchkey'] = stripsearchkey($_GET['searchkey']);
	$wherearr[] = "title LIKE '%$_GET[searchkey]%'";
}
if($_GET['type'] == 'pushed') {
	$wherearr[] = "bid != ''";
} elseif($_GET['type'] == 'unpushed') {
	$wherearr[] = "bid = ''";
}
$wheresql = implode(' AND ', $wherearr);

$perpage = 15;
$page = max(1,intval($_GET['page']));
$start = ($page-1)*$perpage;
if($start<0) $start = 0;

$list = array();
$multi = '';

$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('portal_article_title')." WHERE $wheresql"), 0);
if($count) {
	$query = DB::query("SELECT * FROM ".DB::table('portal_article_title')." WHERE $wheresql ORDER BY dateline DESC LIMIT $start,$perpage");
	while ($value = DB::fetch($query)) {
		if($value['pic']) $value['pic'] = pic_get($value['pic'], 'portal', $value['thumb'], $value['remote']);
		$value['dateline'] = dgmdate($value['dateline']);
		$value['allowmanage'] = $allowmanage;
		$value['allowpush'] = $allowpush;
		$list[] = $value;
	}

	$multi = multi($count, $perpage, $page, "portal.php?mod=portalcp&ac=category&catid=$catid");
}

$language =  lang('portal/template');
$navtitle = $cate['catname'].' - '.$language['category_management'].' - '.$language['portal_management'];
include_once template("portal/portalcp_category");


?>