<?php
/* Function: 上传,修改后跳转页面
 * Com.:
 * Author: wuhan
 * Date: 2010-8-23
 */
if (!defined('IN_DISCUZ')) {
	exit ('Access Denied');
}

$fid = empty($_GET['zoneid'])? 0 : intval($_GET['zoneid']);
$uid = empty($_GET['uid'])? 0 : intval($_GET['userid']);

if($fid){
	$url = "forum.php?mod=group&action=plugin&fid=$fid&plugin_name=groupdoc&plugin_op=groupmenu";
}
else{
	$url = "home.php?mod=space&uid=$_G[uid]&do=doc&view=me";
}

include_once template("api/redirect");
?>
