<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_videophoto.php 9234 2010-04-27 11:00:14Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($_G['setting']['videophoto'])) {
	showmessage('no_open_videophoto');
}

if($space['videophotostatus']) {
	space_merge($space, 'field_home');
	$videophoto = getvideophoto($space['videophoto']);
} else {
	$videophoto = '';
}
$actives = array('profile' =>' class="a"');
$opactives = array('videophoto' =>' class="a"');
include template("home/spacecp_videophoto");
?>