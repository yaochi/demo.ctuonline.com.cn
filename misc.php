<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc.php 10387 2010-05-11 03:05:22Z zhengqingpeng $
 */

define('APPTYPEID', 100);
define('CURSCRIPT', 'misc');


require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$modarray = array('seccode', 'secqaa', 'initsys', 'invite','invitegroupothers', 'faq', 'report', 'swfupload', 'manyou',
    'upload','queryuser', 'selectorg','queryorg', 'queryorgs', 'queryteacher','queryfriend', 'querylive', 'querystation', 'mydoc', "kcategory", "korg","querystation_tree","querycourse", "isteacher","querynewcc","queryextraorg","queryextraclass","queryextralec","learningupload","click");

$modcachelist = array(
);

$mod = getgpc('mod');
$mod = (empty($mod) || !in_array($mod, $modarray)) ? 'error' : $mod;

$cachelist = array();
if(isset($modcachelist[$mod])) {
	$cachelist = $modcachelist[$mod];
}

$discuz->cachelist = $cachelist;

switch ($mod) {
	case 'secqaa':
	case 'manyou':
	case 'seccode':
		$discuz->init_cron = false;
		$discuz->init_session = false;
		break;
	case 'updatecache':
		$discuz->init_cron = false;
		$discuz->init_session = false;
	default:
		break;
}

$discuz->init();

switch ($mod){
	case 'queryuser':
		define('CURMODULE', 'invite');
		break;
	default:
		define('CURMODULE', $mod);
}
require DISCUZ_ROOT.'./source/module/misc/misc_'.$mod.'.php';

?>