<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_pm.php 11152 2010-05-25 04:51:14Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

loaducenter();

$list = array();

$pmid = empty($_GET['pmid'])?0:floatval($_GET['pmid']);
$touid = empty($_GET['touid'])?0:intval($_GET['touid']);
$daterange = empty($_GET['daterange'])?1:intval($_GET['daterange']);

if($_GET['subop'] == 'view') {

	if($touid) {
		$list = uc_pm_view($_G['uid'], 0, $touid, $daterange);
		$pmid = empty($list)?0:$list[0]['pmid'];
	} elseif($pmid) {
		$list = uc_pm_view($_G['uid'], $pmid);
	}

	$actives = array($daterange=>' class="a"');

} elseif($_GET['subop'] == 'ignore') {

	$ignorelist = uc_pm_blackls_get($_G['uid']);
	$actives = array('ignore'=>' class="a"');

} else {

	$filter = in_array($_GET['filter'], array('newpm', 'privatepm', 'systempm', 'announcepm'))?$_GET['filter']:($space['newpm']?'newpm':'privatepm');

	$perpage = 10;
	$perpage = mob_perpage($perpage);

	$page = empty($_GET['page'])?0:intval($_GET['page']);
	if($page<1) $page = 1;

	$result = uc_pm_list($_G['uid'], $page, $perpage, 'inbox', $filter, 100);

	$count = $result['count'];
	$list = $result['data'];

	$multi = multi($count, $perpage, $page, "home.php?mod=space&do=pm&filter=$filter");

	if($_G['member']['newpm']) {
		DB::update('common_member', array('newpm'=>0), array('uid'=>$_G['uid']));
		uc_pm_ignore($_G['uid']);
	}

	$actives = array($filter=>' class="a"');
}

if($list) {
	$today = $_G['timestamp'] - ($_G['timestamp'] + $_G['setting']['timeoffset'] * 3600) % 86400;
	foreach ($list as $key => $value) {
		$value['daterange'] = 5;
		if($value['dateline'] >= $today) {
			$value['daterange'] = 1;
		} elseif($value['dateline'] >= $today - 86400) {
			$value['daterange'] = 2;
		} elseif($value['dateline'] >= $today - 172800) {
			$value['daterange'] = 3;
		} elseif($value['dateline'] >= $today - 604800) {
			$value['daterange'] = 4;
		}
		$list[$key] = $value;
	}

}

$listrealuids = array();
foreach($list as $user){
    $listrealuids[] = $user[msgfromid];
}

$listrealname =  user_get_user_realname($listrealuids);
include_once template("diy:home/space_pm");

?>