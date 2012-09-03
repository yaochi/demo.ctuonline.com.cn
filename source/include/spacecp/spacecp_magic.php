<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_magic.php 8313 2010-04-19 11:53:09Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$space['credit'] = $space['credits'];

$op = empty($_GET['op']) ? "view" : $_GET['op'];
$mid = empty($_GET['mid']) ? '' : trim($_GET['mid']);

if(!checkperm('allowmagics')) {
	showmessage('magic_groupid_not_allowed');
}

if($op == 'cancelflicker') {

	$mid = 'flicker';
	$_GET['idtype'] = 'cid';
	$_GET['id'] = intval($_GET['id']);
	$query = DB::query('SELECT * FROM '.DB::table('home_comment')." WHERE cid = '$_GET[id]' AND authorid = '$_G[uid]'");
	$value = DB::fetch($query);
	if(!$value || !$value['magicflicker']) {
		showmessage('no_flicker_yet');
	}

	if(submitcheck('cancelsubmit')) {
		DB::update('home_comment', array('magicflicker'=>0), array('cid'=>$_GET['id'], 'authorid'=>$_G['uid']));
		showmessage('do_success', dreferer(), array(), array('showdialog' => 1, 'closetime'=>1));
	}

} elseif($op == 'cancelcolor') {

	$mid = 'color';
	$_GET['id'] = intval($_GET['id']);
	$mapping = array('blogid'=>'blogfield', 'tid'=>'thread');
	$tablename = $mapping[$_GET['idtype']];
	if(empty($tablename)) {
		showmessage('no_color_yet');
	}
	$query = DB::query('SELECT * FROM '.DB::table($tablename)." WHERE $_GET[idtype] = '$_GET[id]' AND uid = '$_G[uid]'");
	$value = DB::fetch($query);
	if(!$value || !$value['magiccolor']) {
		showmessage('no_color_yet');
	}

	if(submitcheck('cancelsubmit')) {
		DB::update($tablename, array('magiccolor'=>0), array($_GET['idtype']=>$_GET[id]));
		$query = DB::query('SELECT * FROM '.DB::table('home_feed')." WHERE id = '$_GET[id]' AND idtype = '$_GET[idtype]'");
		$feed = DB::fetch($query);
		if($feed) {
			$feed['body_data'] = unserialize($feed['body_data']);
			if($feed['body_data']['magic_color']) {
				unset($feed['body_data']['magic_color']);
			}
			$feed['body_data'] = serialize($feed['body_data']);
			DB::update('home_feed', array('body_data'=>$feed['body_data']), array('feedid'=>$feed['feedid']));
		}
		showmessage('do_success', dreferer(), 0);
	}


}

include_once template('home/spacecp_magic');

?>