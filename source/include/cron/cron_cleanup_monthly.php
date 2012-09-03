<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_cleanup_monthly.php 6752 2010-03-25 08:47:54Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$myrecordtimes = TIMESTAMP - $_G['setting']['myrecorddays'] * 86400;

DB::query("DELETE FROM ".DB::table('common_mytask')." WHERE status='-1' AND dateline<'$_G[timestamp]'-2592000", 'UNBUFFERED');

?>