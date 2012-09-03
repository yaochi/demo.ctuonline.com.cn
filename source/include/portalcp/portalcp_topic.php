<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_topic.php 11116 2010-05-24 08:11:35Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$allowmanage = $allowadd = 0;
if($_G['group']['allowaddtopic'] || $_G['group']['allowmanagetopic']) {
	$allowadd = 1;
}

$op = in_array($_GET['op'], array('edit')) ? $_GET['op'] : 'add';

$topicid = $_GET['topicid'] ? intval($_GET['topicid']) : 0;
if($topicid) {
	$topic = DB::fetch_first('SELECT * FROM '.DB::table('portal_topic')." WHERE topicid = '$topicid'");
	if(empty($topic)) {
		showmessage('topic_not_exist');
	}
	if($_G['group']['allowmanagetopic'] || ($_G['group']['allowaddtopic'] && $topic['uid'] == $_G['uid'])) {
		$allowmanage = 1;
	}
	if($topic['cover']) {
		if($topic['picflag'] == '1') {
			$topic['cover'] = $_G['setting']['attachurl'].$topic['cover'];
		} elseif ($topic['picflag'] == '2') {
			$topic['cover'] = $_G['setting']['ftp']['attachurl'].$topic['cover'];
		}
	}
}

if(($topicid && !$allowmanage) || (!$topicid && !$allowadd)) {
	showmessage('topic_edit_nopermission');
}

$tpls = array();

if (($dh = opendir(DISCUZ_ROOT.'./template/default/portal'))) {
	while(($file = readdir($dh)) !== false) {
		$file = strtolower($file);
		if (fileext($file) == 'htm' && substr($file, 0, 13) == 'portal_topic_') {
			$tpls[str_replace('.htm','',$file)] = $file;
		}
	}
	closedir($dh);
}

if (empty($tpls)) showmessage('topic_has_on_template');

if(submitcheck('editsubmit')) {

	include_once libfile('function/home');
	include_once libfile('function/portalcp');
	$_POST['title'] = getstr(trim($_POST['title']), 255, 1, 1);
	$_POST['name'] = getstr(trim($_POST['name']), 255, 1, 1);
	if(empty($_POST['title'])) {
		showmessage('topictitle_cannot_be_empty');
	}
	if(empty($_POST['name'])) {
		$_POST['name'] = $_POST['title'];
	}
	if(!$topicid || $_POST['name'] != $topic['name']) {
		$value = DB::fetch_first('SELECT * FROM '.DB::table('portal_topic')." WHERE name = '$_POST[name]' LIMIT 1");
		if($value) {
			showmessage('topic_name_duplicated');
		}
	}

	$setarr = array(
			'title' => $_POST['title'],
			'name' => $_POST['name'],
			'summary' => getstr($_POST['summary'], '', 1, 1, 1),
			'useheader' => $_POST['useheader'] ? '1' : '0',
			'usefooter' => $_POST['usefooter'] ? '1' : '0',
		);

	if($_FILES['cover']['tmp_name']) {
		include_once libfile('function/home');
		$pic = pic_upload($_FILES['cover'], 'portal');
		if($pic) {
			$setarr['cover'] = 'portal/'.$pic['pic'];
			$setarr['picflag'] = $pic['remote'] ? '2' : '1';
		}
	} elseif(!empty($_POST['cover'])) {
		$setarr['cover'] = $_POST['cover'];
		$setarr['picflag'] = '0';
	}
	if($topicid) {
		DB::update('portal_topic', $setarr, array('topicid'=>$topicid));
	} else {

		$primaltplname = $_POST['primaltplname'];
		if(!$primaltplname || preg_match("/(\.)(exe|jsp|asp|aspx|cgi|fcgi|pl)(\.|$)/i", $primaltplname)) {
			showmessage('topic_filename_invalid');
		}
		$primaltplname = 'portal/'.str_replace(array('/', '\\', '.'), '', $primaltplname);
		checkprimaltpl($primaltplname);

		$setarr['uid'] = $_G['uid'];
		$setarr['username'] = $_G['username'];
		$setarr['dateline'] = $_G['timestamp'];
		$setarr['closed'] = '1';
		$setarr['primaltplname'] = $primaltplname;
		$topicid = DB::insert('portal_topic', $setarr, true);
		if(!$topicid) {
			showmessage('topic_created_failed');
		}
		$content = file_get_contents(DISCUZ_ROOT.'./template/default/'.$primaltplname.'.htm');
		$tplfile = DISCUZ_ROOT.'./data/diy/portal/portal_topic_content_'.$topicid.'.htm';
		$tplpath = dirname($tplfile);
		if (!is_dir($tplpath)) dmkdir($tplpath);
		file_put_contents($tplfile, $content);
	}
	showmessage('do_success', 'portal.php?mod=topic&diy=yes&quickforward=1&topicid='.$topicid);
}

include_once template("portal/portalcp_topic");

?>