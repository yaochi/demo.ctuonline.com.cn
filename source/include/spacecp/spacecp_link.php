<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_link.php 6752 2010-07-19 08:47:54Z wuft $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$linkid = empty($_GET['official_linkid'])?0:intval($_GET['official_linkid']);
$op = empty($_GET['op'])?'':$_GET['op'];

$link = array();
if($linkid) {
	$query = DB::query("SELECT * FROM ".DB::table('home_official_link')." WHERE linkid='$linkid' ");
	$link = DB::fetch($query);
	if(empty($link)) showmessage('enter_the_correct_link_name');
}

if ($op == 'edit') {

	if(submitcheck('editsubmit')) {
		$linkname = $_POST['linkname'];
		$linkurl = $_POST['linkurl'];
		
		if(empty($link))
		{
			if(!empty($linkname) and !empty($linkurl))
			{
				DB::insert('home_official_link', array('linkname'=>$linkname,'linkurl'=>$linkurl,'dateline'=>$_G['timestamp']));
				showmessage('do_success', dreferer());
			}else{
				showmessage('error_link_insert', dreferer());
			}
		}else{
			if(!empty($linkname) and !empty($linkurl))
			{
				
				DB::update('home_official_link',array('linkname'=>$linkname,'linkurl'=>$linkurl,'dateline'=>$_G['timestamp']),array('linkid'=>$linkid));
				showmessage('do_success', dreferer());
			}else{
				showmessage('error_link_insert', dreferer());
			}
		}
	}

} elseif ($op == 'delete') {
	if(submitcheck('deletesubmit')) {
		
		DB::query("DELETE FROM ".DB::table('home_official_link')." WHERE linkid='$linkid'");

		showmessage('do_success', dreferer());
	}
}elseif ($op == 'deleteall') {
	if(submitcheck('deletesubmit')) {
		
		DB::query("DELETE FROM ".DB::table('home_official_link'));
		showmessage('do_success', dreferer());
	}
}

include_once template("home/spacecp_link");

?>