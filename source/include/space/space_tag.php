<?php

/**
 *      $Id: space_tag.php 10930 2012-2-10 11:08:17 yangyang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$tagname=$_G['gp_tagname'];
if($tagname){
	$tag=urldecode($tagname);
}


include_once template('diy:home/space_tag');

?>