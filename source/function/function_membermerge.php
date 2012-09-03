<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_membermerge.php 6741 2010-03-25 07:36:01Z cnteacher $
 */

function getuidfields() {
	return array(
		'members',
		'memberfields',
		'access',
		'activities',
		'activityapplies',
		'attachments',
		'attachpaymentlog',
		'creditslog',
		'debateposts',
		'debates',
		'forumrecommend|authorid,moderatorid',
		'invites',
		'magiclog',
		'magicmarket',
		'membermagics',
		'memberspaces',
		'moderators',
		'modworks',
		'onlinetime',
		'orders',
		'paymentlog|uid,authorid',
		'posts|authorid|pid',
		'promotions',
		'ratelog',
		'rewardlog|authorid,answererid',
		'searchindex|uid',
		'spacecaches',
		'threads|authorid|tid',
		'threadsmod',
		'tradecomments|raterid,rateeid',
		'tradelog|sellerid,buyerid',
		'trades|sellerid',
		'validating',
	);
}

function membermerge($olduid, $newuid) {
	$uidfields = getuidfields();
	foreach($uidfields as $value) {
		list($table, $field, $stepfield) = explode('|', $value);
		$fields = !$field ? array('uid') : explode(',', $field);
		foreach($fields as $field) {
			DB::query("UPDATE `".DB::table($table)."` SET `$field`='$newuid' WHERE `$field`='$olduid'");
		}
	}
}

?>