	<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: userapp_index.php 10070 2010-05-06 09:37:00Z liguode $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


$uid=$_G[uid];
$id=$_G[gp_id];
$version=$_G[gp_version];


if($id){
	$value=getresource_find_by_id($id,$version);
}else{
	showmessage('你查看资源不存在，请返回');
}
$value[data][releaseTime]=dgmdate($value[data][releaseTime]/1000,'Y-m-d H:i');

include template('mobileapp/Detail');
?>