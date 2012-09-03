<?php 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$fid=$_GET['fid'];
require_once libfile('function/forumlist');
dheader("Location: forum.php?mod=forumdisplay&action=list&fid=$_G[fid]#groupnav");
?>
