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
if($_G['gp_operation'] == 'upload') {
	require_once libfile('class/attachmentupload');
	if(empty($_G['gp_simple'])) {
		$_FILES['Filedata']['name'] = addslashes(diconv(urldecode($_FILES['Filedata']['name']), 'UTF-8'));
	}
	
	$upload = new attachment_upload();
}
?>