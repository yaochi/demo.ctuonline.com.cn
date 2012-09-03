<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_plugin.php 6752 2010-03-25 08:47:54Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include pluginmodule($_G['gp_id'], 'spacecp'.($op ? '_'.$op : ''));
if(!$op || $op == 'credit') {
	include template('home/spacecp_plugin');
} elseif($op == 'profile') {
	$operation = 'plugin';
	include template('home/spacecp_profile');
}

?>