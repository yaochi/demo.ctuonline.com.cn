<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search.php 10326 2010-05-10 09:27:36Z zhaoxiongfei $
 */

define('APPTYPEID', 0);
define('CURSCRIPT', 'search');

require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$modarray = array('portal', 'forum', 'blog', 'album', 'group', 'my', 'activity', 'topic', 'qbar', 'question', 'group_live', 'group_notice', 'group_poll','member');

$mod = !in_array($discuz->var['mod'], $modarray) ? 'forum' : $discuz->var['mod'];

define('CURMODULE', $mod);


$modcachelist = array('register' => array('modreasons', 'stamptypeid', 'fields_required', 'fields_optional'));

$cachelist = $slist = array();
if(isset($modcachelist[CURMODULE])) {
	$cachelist = $modcachelist[CURMODULE];
}

$discuz->cachelist = $cachelist;
$discuz->init();


runhooks();

require_once libfile('function/discuzcode');
$other = $discuz->var['mod'];

require DISCUZ_ROOT.'./source/module/search/search_'.$mod.'.php';	




?>