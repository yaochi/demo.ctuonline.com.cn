<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
global $_G;
$fid = intval($_GET["fid"]) ? intval($_GET["fid"]) : 197;
$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
$pagesize = 5;
$start = ($page - 1) * $pagesize;

$name = $_REQUEST["name"];
$name=urldecode($name);

$wheresql="  WHERE  (released=1 or sugestuid='".$_G[uid]."')";

if($name){
    $namesql = " and name LIKE '%$name%'";
}
	$wheresql=$wheresql.$namesql;
	$query = DB::query("SELECT COUNT(1) AS c FROM ".DB::table("extra_class").$wheresql);
	$count = DB::fetch($query);
	$count = $count[c];
	if($count){
		$query = DB::query("SELECT * FROM " . DB::table("extra_class").$wheresql." ORDER BY sugestdateline LIMIT $start, $pagesize");
		while($row = DB::fetch($query)){
			$classs[] = $row;
		}
		$url = "misc.php?mod=queryextraclass&fid=".$fid;
		if($name){
			$url .= "&name=".urlencode($name);
			//echo $teachers;
		}
		$multipage = multi($count, $pagesize, $page, $url);
	}


include template('common/queryextraclass');
?>