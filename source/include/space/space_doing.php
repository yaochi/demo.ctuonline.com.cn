<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_doing.php 10930 2010-05-18 07:34:59Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$perpage = 20;
$perpage = mob_perpage($perpage);

$page = empty($_GET['page'])?0:intval($_GET['page']);
if($page<1) $page=1;
$start = ($page-1)*$perpage;

ckstart($start, $perpage);

$dolist = array();
$count = 0;

if(empty($_GET['view'])) {
	$_GET['view'] = 'we';
}

$gets = array(
	'mod' => 'space',
	'uid' => $space['uid'],
	'do' => 'doing',
	'view' => $_GET['view'],
	'searchkey' => $_GET['searchkey'],
	'from' => $_GET['from']
);
$theurl = 'home.php?'.url_implode($gets);

$f_index = '';
$diymode = 0;
if($_GET['view'] == 'all') {

	$wheresql = "1";
	$f_index = 'USE INDEX(dateline)';

} elseif($_GET['view'] == 'we') {

	space_merge($space, 'field_home');
	if($space['feedfriend']) {
		$wheresql = "uid IN ($space[feedfriend],$space[uid])";
		$f_index = 'USE INDEX(dateline)';
	} else {
		$wheresql = "uid='$space[uid]'";
	}

} else {

	if($_GET['from'] == 'space') $diymode = 1;

	$wheresql = "uid='$space[uid]'";
}
$actives = array($_GET['view'] =>' class="a"');

$doid = empty($_GET['doid'])?0:intval($_GET['doid']);
$doids = $clist = $newdoids = array();

if($doid) {
	$count = 1;
	$f_index = '';
	$wheresql = "doid='$doid'";
	$theurl .= "&doid=$doid";
}

if($searchkey = stripsearchkey($_GET['searchkey'])) {
	$wheresql .= " AND message LIKE '%$searchkey%'";
}

if(empty($count)) {
	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_doing')." WHERE $wheresql"), 0);
}
if($count) {
	$query = DB::query("SELECT * FROM ".DB::table('home_doing')." $f_index
		WHERE $wheresql
		ORDER BY dateline DESC
		LIMIT $start,$perpage");
	while ($value = DB::fetch($query)) {
		$doids[] = $value['doid'];
		$dolist[] = $value;
	}
}

if($doid) {
	$dovalue = empty($dolist)?array():$dolist[0];
	if($dovalue) {
		if($dovalue['uid'] == $_G['uid']) {
			$actives = array('me'=>' class="a"');
		} else {
			$actives = array('all'=>' class="a"');
		}
	}
}


if($doids) {

	require_once libfile('class/tree');
	$tree = new tree();

	$values = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_docomment')." USE INDEX(dateline) WHERE doid IN (".dimplode($doids).") ORDER BY dateline");
	while ($value = DB::fetch($query)) {
		$newdoids[$value['doid']] = $value['doid'];
		if(empty($value['upid'])) {
			$value['upid'] = "do$value[doid]";
		}
		$tree->setNode($value['id'], $value['upid'], $value);
	}
}

$showdoinglist = array();
foreach ($newdoids as $cdoid) {
	$values = $tree->getChilds("do$cdoid");
	$show = false;
	foreach ($values as $key => $id) {
		$one = $tree->getValue($id);
		$one['layer'] = $tree->getLayer($id) * 2 - 2;
		$one['style'] = "padding-left:{$one['layer']}em;";
		if($_GET['highlight'] && $one['id'] == $_GET['highlight']) {
			$one['style'] .= 'color:#F60;';
		}
		if(!$show && $one['uid']) {
			$show = true;
		}
		$clist[$cdoid][] = $one;
	}
	$showdoinglist[$cdoid] = $show;
}

$multi = multi($count, $perpage, $page, $theurl);

dsetcookie('home_diymode', $diymode);

include_once template('diy:home/space_doing');

?>