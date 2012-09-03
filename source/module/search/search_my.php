<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_my.php 10508 2010-05-12 01:59:36Z zhouguoqiang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

if (!$_G['setting']['my_siteid']) {
	header('Location: index.php');
	exit;
}

require_once DISCUZ_ROOT . './api/manyou/Manyou.php';

$my_forums = SearchHelper::getForums();

$my_extgroupids = array();
$_extgroupids = explode("\t", $_G['member']['extgroupids']);
foreach($_extgroupids as $v) {
	if ($v) {
		$my_extgroupids[] = $v;
	}
}
$my_extgroupids_str = implode(',', $my_extgroupids);

$params = array('sId' => $_G['setting']['my_siteid'],
				'ts' => time(),
				'cuId' => $_G['uid'],
				'cuName' => $_G['username'],
				'gId' => $_G['groupid'],
				'agId' => $_G['adminid'],
				'egIds' => $my_extgroupids_str,
				'fmSign' => substr($my_forums['sign'], -4),
			   );

$groupIds = explode(',', $_G['groupid']);
if ($_G['adminid']) {
	$groupIds[] = $_G['adminid'];
}
if ($my_extgroupids) {
	$groupIds = array_merge($groupIds, $my_extgroupids);
}

$groupIds = array_unique($groupIds);
$userGroups = SearchHelper::getUserGroupPermissions($groupIds);
foreach($groupIds as $k => $v) {
	$value =  substr($userGroups[$v]['sign'], -4);
	if ($value) {
		$params['ugSign' . $v] = $value;
	}
}

$params['sign'] = md5(implode('|', $params) . '|' . $_G['setting']['my_sitekey']);

$extra = array('q', 'fId', 'author', 'scope', 'source', 'module', 'isAdv');
foreach($extra as $v) {
	if ($_GET[$v]) {
		$params[$v] = $_GET[$v];
	}
}
$params['charset'] = $_G['charset'];
if ($_G['setting']['my_search_domain']) {
	$domain = $_G['setting']['my_search_domain'];
} else {
	$domain = 'search.manyou.com';
}
$url = 'http://' . $domain . '/f/discuz?' . http_build_query($params);

header('Location: ' . $url);
?>