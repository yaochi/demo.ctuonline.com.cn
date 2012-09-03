<?php
/*
 * Created on 2012-3-6
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

 if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if($_G['gp_operation'] == 'learningupload') {
	require_once libfile('class/learnattchentupload');
	if(empty($_G['gp_simple'])) {
		$_FILES['Filedata']['name'] = addslashes(diconv(urldecode($_FILES['Filedata']['name']), 'UTF-8'));
	}

	$upload = new learnattachment_upload();
}
?>
