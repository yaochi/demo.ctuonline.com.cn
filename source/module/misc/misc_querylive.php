<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
$pagesize=10;
$page=$_REQUEST['page']?$_REQUEST['page']:1;
$start=($page-1)*$pagesize;


$query = DB::query("SELECT * FROM " . DB::table("group_live")." WHERE fid=".$_GET[fid]." LIMIT $start,$pagesize");
$count=DB::result_first("SELECT count(*) FROM " . DB::table("group_live")." WHERE fid=".$_GET[fid]);
$url="misc.php?mod=querylive&fid=".$_GET[fid];
$multipage=multi($count,$pagesize,$page,$url);
while($row = DB::fetch($query)){
	$lives[] = $row;
}
include template('common/querylive');
?>