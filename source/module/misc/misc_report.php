<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_report.php 6757 2010-04-07 09:01:29Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if(empty($_G['uid'])) {
	showmessage('not_loggedin', '', array(), array('login' => 1));
}
$_G['gp_handlekey'] = 'miscreport';
$url = base64_decode($_G['gp_url']);
$url = preg_match("/^http[s]?:\/\/[^\[\"']+$/i", trim($url)) ? trim($url) : '';
if(empty($url) || empty($_G['inajax'])) {
	showmessage('report_parameters_invalid');
}
$urlkey = md5($url);
if(submitcheck('reportsubmit')) {
	$message = censor(cutstr(dhtmlspecialchars(trim($_G['gp_message'])), 200, ''));
	$message = rtrim($message, "\\");
	if($reportid = DB::result_first("SELECT id FROM ".DB::table('common_report')." WHERE urlkey='$urlkey' AND opuid='0'")) {
		DB::query("UPDATE ".DB::table('common_report')." SET num=num+1 WHERE id='$reportid'");
	} else {
		DB::query("INSERT INTO ".DB::table('common_report')."(url, urlkey, uid, username, message, dateline) VALUES ('$url', '$urlkey', '$_G[uid]', '$_G[username]', '$message', '".TIMESTAMP."')");
	}
	showmessage('report_succeed', '', array(), array('closetime' => 3, 'showdialog' => 1));
}
require_once libfile('function/misc');
include template('common/report');
?>