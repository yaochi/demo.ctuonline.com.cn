<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_swfupload.php 11170 2010-05-25 08:18:52Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_G['gp_operation'] == 'config' && $_G['uid']) {

	$swfhash = md5(substr(md5($_G['config']['security']['authkey']), 8).$_G['uid']);
	$xmllang = lang('forum/swfupload');
	$imageexts = array('jpg','jpeg','gif','png','bmp');
	if($_G['group']['attachextensions'] !== '') {
		$_G['group']['attachextensions'] = str_replace(' ', '', $_G['group']['attachextensions']);
		$exts = explode(',', $_G['group']['attachextensions']);
		if($_G['gp_type'] == 'image') {
			$exts = array_intersect($imageexts, $exts);
		}
		$_G['group']['attachextensions'] = '*.'.implode(',*.', $exts);
	} else {
		$_G['group']['attachextensions'] = $_G['gp_type'] == 'image' ? '*.'.implode(',*.', $imageexts) : '*.*';
	}
	$depict = $_G['gp_type'] == 'image' ? 'Image File ' : 'All Support Formats ';
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><parameter><allowsExtend><extend depict=\"$depict\">{$_G[group][attachextensions]}</extend></allowsExtend><language>$xmllang</language><config><userid>$_G[uid]</userid><hash>$swfhash</hash><maxupload>{$_G[group][maxattachsize]}</maxupload></config></parameter>";

} elseif($_G['gp_operation'] == 'upload') {

	require_once libfile('class/forumupload');
	if(empty($_G['gp_simple'])) {
		$_FILES['Filedata']['name'] = addslashes(diconv(urldecode($_FILES['Filedata']['name']), 'UTF-8'));
	}
	$upload = new forum_upload();

}

?>