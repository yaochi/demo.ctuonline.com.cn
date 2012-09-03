<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_class.php 6752 2010-03-25 08:47:54Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$classid = empty($_GET['classid'])?0:intval($_GET['classid']);
$op = empty($_GET['op'])?'':$_GET['op'];

$class = array();
if($classid) {
	$query = DB::query("SELECT * FROM ".DB::table('home_class')." WHERE classid='$classid' AND uid='$_G[uid]'");
	$class = DB::fetch($query);
}
if(empty($class)) showmessage('did_not_specify_the_type_of_operation');

if ($op == 'edit') {

	if(submitcheck('editsubmit')) {

		$_POST['classname'] = getstr($_POST['classname'], 40, 1, 1, 1);
		if(strlen($_POST['classname']) < 1) {
			showmessage('enter_the_correct_class_name');
		}
		DB::update('home_class', array('classname'=>$_POST['classname']), array('classid'=>$classid));
		showmessage('do_success', dreferer(),array('classid'=>$classid), array('showdialog' => 1, 'showmsg' => true, 'closetime' => 1));
	}

} elseif ($op == 'delete') {
	if(submitcheck('deletesubmit')) {
		DB::update('home_blog', array('classid'=>0), array('classid'=>$classid));
		DB::query("DELETE FROM ".DB::table('home_class')." WHERE classid='$classid'");

		showmessage('do_success', dreferer());
	}
}

include_once template("home/spacecp_class");

?>