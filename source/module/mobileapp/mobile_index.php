<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: userapp_index.php 10070 2010-05-06 09:37:00Z liguode $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


$filejson=getresource_find_lastest('m001',1,9);
$list1=$filejson['data'];
$filejson=getresource_find_lastest('m002',1,9);
$list2=$filejson['data'];
$filejson=getresource_find_lastest('m003',1,9);
$list3=$filejson['data'];
$filejson=getresource_find_hot('m003',1,10);
$hotlist=$filejson['data'];





include template('mobileapp/index');
?>