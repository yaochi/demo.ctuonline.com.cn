<?php

/**
 *
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

global $_G;

if($_G['forum']['type'] !='activity'){ //
	$g_plugins = array('pluginmenu','foruminfo','forumremark','grouptop');
	foreach ($g_plugins as $plugin) {
	 $blockfile = DISCUZ_ROOT . ($modfile = './source/plugin/' . $plugin . '/block/setting.inc.php');
		if (file_exists($blockfile)) {
		 @include($blockfile);
		}
	}

}

?>