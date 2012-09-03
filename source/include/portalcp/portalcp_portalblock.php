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

$catid = max(0,intval($_GET['catid']));

$permission = getallowblock($_G['uid']);



include_once libfile('function/block');
$wherearr = array();
$_GET['searchkey'] = trim($_GET['searchkey']);
if(!empty($_GET['searchkey'])) {
	if (preg_match('/^[#]?(\d+)$/', $_GET['searchkey'],$match)) {
		$bid = intval($match[1]);
		$wherearr[] = " b.bid='$bid'";
	} else {
		$_GET['searchkey'] = stripsearchkey($_GET['searchkey']);
		$wherearr[] = " b.name LIKE '%$_GET[searchkey]%'";

	}
}
if($_GET['from'] == 'push') {
	$wherearr[] = "b.blockclass='portal_article'";
	if(!checkperm('allowdiy')){
		$wherearr[] = "bp.allowdata='1'";
		$wherearr[] = "bp.uid='$_G[uid]'";
	}
} elseif(!checkperm('allowdiy')) {
	$wherearr[] = "bp.uid='$_G[uid]'";
	$wherearr[] = "(bp.allowdata='1' OR bp.allowsetting='1')";
}

$wheresql = empty($wherearr) ? '' : 'WHERE '.implode(' AND ', $wherearr);

$page = !empty($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perpage = 20;
$start = ($page-1) * $perpage;

$blocks = array();
if(checkperm('allowdiy')) {
	$count = DB::result_first('SELECT COUNT(*) FROM '.DB::table('common_block')." b $wheresql");
	$sql = 'SELECT b.*,tb.targettplname FROM '.DB::table('common_block')." b LEFT JOIN ".DB::table('common_template_block')." tb ON b.bid=tb.bid $wheresql ORDER BY b.bid DESC LIMIT $start, $perpage";
} else {
	$count = DB::result_first('SELECT COUNT(*) FROM '.DB::table('common_block_permission').' bp LEFT JOIN '.DB::table('common_block')." b ON bp.bid=b.bid $wheresql");
	$sql = 'SELECT b.*, tb.targettplname FROM '.DB::table('common_block_permission')." bp LEFT JOIN ".DB::table('common_block')." b ON bp.bid = b.bid LEFT JOIN ".DB::table('common_template_block')." tb ON bp.bid=tb.bid $wheresql ORDER BY b.bid DESC LIMIT $start, $perpage";
}

if($count) {

	require_once libfile('function/block');
	$query = DB::query($sql);
	while($value=DB::fetch($query)) {

		if($value['blocktype']) {
			$value['url'] = lang('portal/template','block_jscall');
		} else {
			$tplinfo = block_getdiyurl($value['targettplname']);
			if($tplinfo['url']) {
				$value['url'] =  lang('portal/template','block_view_link',array('url'=>$tplinfo['url']));
			} else {
				$value['url'] = lang('portal/template','block_unused');
			}
		}

		$value['name'] = empty($value['name']) ? '#'.$value['bid'] : trim($value['name']);
		$theclass = block_getclass($value['blockclass']);
		$value['datasrc'] = $theclass['script'][$value['script']];
		$value['allowdata'] = checkperm('allowdiy') || $permission[$value['bid']]['allowdata'] ? true : false;
		$value['allowsetting'] = checkperm('allowdiy') || $permission[$value['bid']]['allowsetting'] ? true : false;
		$blocks[] = $value;
	}
	$multi = multi($count, $perpage, $page, "portal.php?mod=portalcp&ac=portalblock");
}

$language =  lang('portal/template');
$navtitle = $language['block_management'].' - '.$language['portal_management'];
include_once template("portal/portalcp_portalblock");

function getallowblock($uid) {
	if (empty($uid)) return false;
	$uid = max(0,intval($uid));
	$query = DB::query('SELECT * FROM '.DB::table('common_block_permission')." WHERE uid='$uid'");

	$permission = array();
	while($value = DB::fetch($query)) {
		if ($value['allowdata'] || $value['allowsetting']) {
			$permission[$value['bid']] = $value;
		}
	}
	return $permission;
}
?>