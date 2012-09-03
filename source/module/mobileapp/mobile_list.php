<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: userapp_index.php 10070 2010-05-06 09:37:00Z liguode $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
 $_G[gp_mod]?$_G[gp_mod]:"index";
$listtype=$_G['gp_listtype']?$_G['gp_listtype']:"new";
$category_id=$_G['gp_category_id']?$_G['gp_category_id']:"m001";

	$perpage = 10;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;

if($listtype=='new'){
	$filejson=getresource_find_lastest($category_id,$page,$perpage );
	$list=$filejson['data'];
}elseif($listtype=='stars'){
	$filejson=getresource_find_stars($category_id,$page,$perpage );
	$list=$filejson['data'];
}elseif($listtype=='download'){
	$filejson=getresource_find_hot($category_id,$page,$perpage );
	$list=$filejson['data'];
}

$url='mobileapp.php?mod=list&category_id='.$category_id.'&listtype='.$listtype;
$multipage = multi($list[totalSize], $perpage, $page, $url);

include template('mobileapp/listPage');
?>