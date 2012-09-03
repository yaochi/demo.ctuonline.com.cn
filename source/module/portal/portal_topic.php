<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_topic.php 11032 2010-05-20 04:57:03Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_GET['diy']=='yes' && !$_G['group']['allowaddtopic'] && !$_G['group']['allowmanagetopic']) {
	$_GET['diy'] = '';
	showmessage('topic_edit_nopermission');
}

$topicid = $_GET['topicid'] ? intval($_GET['topicid']) : 0;
if($topicid) {
	$topic = DB::fetch_first('SELECT * FROM '.DB::table('portal_topic')." WHERE topicid = '$topicid'");
} elseif($_GET['topic']) {
	$_GET['topic'] = addslashes($_GET['topic']);
	$topic = DB::fetch_first('SELECT * FROM '.DB::table('portal_topic')." WHERE name = '$_GET[topic]'");
}
if(empty($topic)) {
	showmessage('topic_not_exist');
}

if($topic['closed'] && !$_G['group']['allowmanagetopic'] && !($topic['uid'] == $_G['uid'] && $_G['group']['allowaddtopic'])) {
	showmessage('topic_is_closed');
}

if($_GET['diy'] == 'yes' && $topic['uid'] != $_G['uid'] && !$_G['group']['allowmanagetopic']) {
	$_GET['diy'] = '';
	showmessage('topic_edit_nopermission');
}

$topicid = intval($topic['topicid']);
$diyurl = "portal.php?mod=topic&topicid=$topicid&diy=yes";

DB::query("UPDATE ".DB::table('portal_topic')." SET viewnum=viewnum+1 WHERE topicid='$topicid'");

$file = 'portal/portal_topic_content:'.$topicid;
include template('diy:'.$file);

?>