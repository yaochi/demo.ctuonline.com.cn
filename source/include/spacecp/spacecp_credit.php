<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_credit.php 11582 2010-06-08 08:35:20Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$perpage = 20;
$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
if($page < 1) $page = 1;
$start = ($page-1) * $perpage;
ckstart($start, $perpage);

checkusergroup();

$operation = in_array($_GET['op'], array('usergroup', 'base', 'log', 'rule')) ? trim($_GET['op']) : 'base';
$opactives = array($operation =>' class="a"');
if($operation == 'rule') {
	$operation = 'base';
}
include_once libfile('spacecp/credit_'.$operation, 'include');




?>