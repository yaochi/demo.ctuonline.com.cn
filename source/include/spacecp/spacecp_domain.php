<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_domain.php 11397 2010-06-02 01:19:37Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$domainlength = checkperm('domainlength');

if($_G['config']['home']['allowdomain'] && $_G['config']['home']['domainroot'] && $domainlength) {
	checklowerlimit('modifydomain');
} else {
	showmessage('no_privilege');
}

if(submitcheck('domainsubmit')) {

	$setarr = array();
	$_POST['domain'] = strtolower(trim($_POST['domain']));
	if($_POST['domain'] != $space['domain']) {

		if(empty($domainlength) || empty($_POST['domain'])) {
			$setarr['domain'] = '';
		} else {
			if(strlen($_POST['domain']) < $domainlength) {
				showmessage('domain_length_error', '', array('length' => $domainlength));
			}
			if(strlen($_POST['domain']) > 30) {
				showmessage('two_domain_length_not_more_than_30_characters');
			}
			if(!preg_match("/^[a-z][a-z0-9]*$/", $_POST['domain'])) {
				showmessage('only_two_names_from_english_composition_and_figures');
			}

			if(isholddomain($_POST['domain'])) {
				showmessage('domain_be_retained');
			}

			$count = getcount('common_member_field_home', array('domain'=>$_POST['domain']));
			if($count) {
				showmessage('two_domain_have_been_occupied');
			}

			$setarr['domain'] = $_POST['domain'];
		}
	}
	if($setarr) {
		updatecreditbyaction('modifydomain');
		DB::update('common_member_field_home', $setarr, array('uid' => $_G['uid']));
	}

	showmessage('do_success', 'home.php?mod=spacecp&ac=domain');
}
$actives = array('profile' =>' class="a"');
$opactives = array('domain' =>' class="a"');

include_once template("home/spacecp_domain");

?>