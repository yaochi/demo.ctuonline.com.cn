<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_money.php 7830 2010-04-14 02:22:32Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_doodle {

	var $version = '1.0';
	var $name = 'doodle_name';
	var $description = 'doodle_desc';
	var $price = '20';
	var $weight = '20';
	var $useevent = 0;
	var $targetgroupperm = false;
	var $copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {}

	function setsetting(&$magicnew, &$parameters) {}

	function usesubmit() {
		global $_G;

		$config = urlencode(getsiteurl().'home.php?mod=misc&ac=swfupload&op=config&doodle=1');
		$src = IMGDIR."/doodle.swf?fid={$_G[gp_handlekey]}&oid={$_G[gp_mtarget]}&from={$_G[gp_from]}&config=$config";
		include template('home/magic_doodle');
	}

	function show() {
		global $_G;
		magicshowtips(lang('magic/doodle', 'doodle_info'));
		echo <<<SCRIPT
<p>
	<input type="hidden" name="showid" value="$_GET[showid]" />
	<input type="hidden" name="mtarget" value="$_GET[target]" />
	<input type="hidden" name="from" value="$_GET[from]" />
</p>
SCRIPT;
	}

}

?>