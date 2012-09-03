<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_doing.php 11795 2010-06-13 05:15:34Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$doid = empty($_GET['doid'])?0:intval($_GET['doid']);
$id = empty($_GET['id'])?0:intval($_GET['id']);


if(submitcheck('addsubmit')) {
	$type=$_POST['type'];
	$atid=$_POST['atid'];
	$add_doing = 1;
	if(!checkperm('allowdoing')) {
		showmessage('no_privilege');
	}

	ckrealname('doing');

	ckvideophoto('doing');

	cknewuser();

	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast', '', array('waittime' => $waittime));
	}


	//$message = getstr($_POST['message'], 280, 1, 1, 1);轻博客修改
	$message = getstr($_POST['msginput'], 280, 1, 1, 1);
	$message = preg_replace("/\<br.*?\>/i", ' ', $message);
	if(strlen($message) < 1) {
		showmessage('should_write_that');
	}
	
	if($add_doing) {
		$setarr = array(
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'dateline' => $_G['timestamp'],
			'message' => $message,
			'ip' => $_G['clientip']
		);
		$newdoid = DB::insert('home_doing', $setarr, 1);
	}

	$setarr = array('recentnote'=>$message, 'spacenote'=>$message);
	$credit = $experience = 0;
	$extrasql = array('doings' => 1);

	updatecreditbyaction('doing', 0, $extrasql);

	DB::update('common_member_field_home', $setarr, "uid='$_G[uid]'");

	if($_POST['to_signhtml']) {
		db::update('common_member_field_forum', array('sightml'=>$message), "uid='$_G[uid]'");
	}

	if($add_doing && ckprivacy('doing', 'feed')) {
		$feedarr = array(
			'appid' => '',
			'icon' => 'doing',
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'dateline' => $_G['timestamp'],
			'title_template' => lang('feed', 'feed_doing_title'),
			'title_data' => daddslashes(serialize(dstripslashes(array('message'=>$message)))),
			'body_template' => '',
			'body_data' => '',
			'id' => $newdoid,
			'idtype' => 'doid'
		);
		DB::insert('home_feed', $feedarr);
	}

	require_once libfile('function/stat');
	updatestat('doing');
	
	//积分
	require_once libfile('function/credit'); 
	credit_create_credit_log($_G['uid'], 'doing', $newdoid); 
    hook_create_resource($newdoid, "doing");//fumz,2010-9-25 15:54:06
        
	showmessage('do_success', dreferer(), array('doid' => $newdoid), $_G['gp_spacenote'] ? array('showmsg' => false):array('header' => true));

} elseif (submitcheck('commentsubmit')) {

	if(!checkperm('allowdoing')) {
		showmessage('no_privilege');
	}

	ckrealname('doing');

	cknewuser();

	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast', '', array('waittime' => $waittime));
	}

	$message = getstr($_POST['message'], 280, 1, 1, 1);
	$message = preg_replace("/\<br.*?\>/i", ' ', $message);
	if(strlen($message) < 1) {
		showmessage('should_write_that');
	}

	$updo = array();
	if($id) {
		$query = DB::query("SELECT * FROM ".DB::table('home_docomment')." WHERE id='$id'");
		$updo = DB::fetch($query);
	}
	if(empty($updo) && $doid) {
		$query = DB::query("SELECT * FROM ".DB::table('home_doing')." WHERE doid='$doid'");
		$updo = DB::fetch($query);
	}
	if(empty($updo)) {
		showmessage('docomment_error');
	} else {
		if(isblacklist($updo['uid'])) {
			showmessage('is_blacklist');
		}
	}

	$updo['id'] = intval($updo['id']);
	$updo['grade'] = intval($updo['grade']);

	$setarr = array(
		'doid' => $updo['doid'],
		'upid' => $updo['id'],
		'uid' => $_G['uid'],
		'username' => $_G['username'],
		'dateline' => $_G['timestamp'],
		'message' => $message,
		'ip' => $_G['clientip'],
		'grade' => $updo['grade']+1
	);

	if($updo['grade'] >= 3) {
		$setarr['upid'] = $updo['upid'];
	}

	$newid = DB::insert('home_docomment', $setarr, 1);
	hook_create_resource($newid, "home_doing_comment");//fumz,2010-9-25 15:56:08

	DB::query("UPDATE ".DB::table('home_doing')." SET replynum=replynum+1 WHERE doid='$updo[doid]'");

	if($updo['uid'] != $_G['uid']) {
		notification_add($updo['uid'], 'doing', 'doing_reply', array(
			'url'=>"home.php?mod=space&do=doing&doid=$updo[doid]&highlight=$newid",
			'from_id'=>$updo['doid'],
			'from_idtype'=>'doid'));
		updatecreditbyaction('comment', 0, array(), 'doing'.$updo['doid']);
	}

	include_once libfile('function/stat');
	updatestat('docomment');

	showmessage('do_success', dreferer(), array('doid' => $updo['doid']));
}

if($_GET['op'] == 'delete') {
	
	if(submitcheck('deletesubmit')) {
		if($id) {
			$allowmanage = checkperm('managedoing');
			$query = DB::query("SELECT dc.*, d.uid as duid FROM ".DB::table('home_docomment')." dc, ".DB::table('home_doing')." d WHERE dc.id='$id' AND dc.doid=d.doid");
			if($value = DB::fetch($query)) {
				if($allowmanage || $value['uid'] == $_G['uid'] || $value['duid'] == $_G['uid'] ) {
					DB::update('home_docomment', array('uid'=>0, 'username'=>'', 'message'=>''), "id='$id'");
					if($value['uid'] != $_G['uid'] && $value['duid'] != $_G['uid']) {
						batchupdatecredit('comment', $value['uid'], array(), -1);
					}
					DB::query("UPDATE ".DB::table('home_doing')." SET replynum=replynum-'1' WHERE doid='$doid'");
					hook_delete_resources(array($id), "home_doing_comment");//added by fumz,2010-9-27 14:01:31
				}
			}
		} else {
			require_once libfile('function/delete');
			deletedoings(array($doid));
			hook_delete_resources(array($doid), "doing");//added by fumz，2010-9-27 14:01:17
		}
        
		header('location: '.dreferer());
		exit();
	}

} elseif ($_GET['op'] == 'getcomment') {

	include_once(DISCUZ_ROOT.'./source/class/class_tree.php');
	$tree = new tree();

	$list = array();
	$highlight = 0;
	$count = 0;

	if(empty($_GET['close'])) {
		$query = DB::query("SELECT * FROM ".DB::table('home_docomment')." WHERE doid='$doid' ORDER BY dateline");
		while ($value = DB::fetch($query)) {
			$tree->setNode($value['id'], $value['upid'], $value);
			$count++;
			if($value['authorid'] = $space['uid']) $highlight = $value['id'];
		}
	}

	if($count) {
		$values = $tree->getChilds();
		foreach ($values as $key => $vid) {
			$one = $tree->getValue($vid);
			$one['layer'] = $tree->getLayer($vid) * 2;
			$one['style'] = "padding-left:{$one['layer']}em;";
			if($one['id'] == $highlight && $one['uid'] == $space['uid']) {
				$one['style'] .= 'color:#F60;';
			}
			$list[] = $one;
		}
	}
} elseif ($_GET['op'] == 'spacenote') {
	space_merge($space, 'field_home');
}

include template('home/spacecp_doing');

?>