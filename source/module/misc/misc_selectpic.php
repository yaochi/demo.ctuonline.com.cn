<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
$pagesize = 20;
$start = ($page - 1) * $pagesize;

$albumids = $_POST["albumids"];

if($albumids){
    $wheresql = " WHERE albumid in ('$albumids')";
}

$query = DB::query("SELECT COUNT(1) AS c FROM ".DB::table("group_pic").$wheresql);
$count = DB::fetch($query);
$count = $count[c];
if($count){
    $query = DB::query("SELECT * FROM " . DB::table("group_pic").$wheresql." ORDER BY dateline LIMIT $start, $pagesize");
    while($row = DB::fetch($query)){
        $pics[] = $row;
    }
    $url = "misc.php?mod=selectpic";
    if($albumids){
        $url .= "&albumids=" . $albumids;
    }
    $multipage = multi($count, $pagesize, $page, $url);
}
if($albumids){
    header('Content-Type: text/xml');
    $sql = '<?xml version="1.0" encoding="utf-8"?><root><![CDATA[<table width="100%"><tr><td>选择</td><td>名称</td></tr>';
    foreach ($pics as $pic){
        $sql .= '<tr><td><input type="checkbox" id="ids" name="ids" value="'.$pic[picid].'" title="'.$pic[filename].'"/></td><td>'.$pic[filename].'</td></tr>';
    }
    $sql .='<tr><td colspan="2">'.$multipage.'</td></tr></table>]]></root>';
    echo $sql;
    exit;
}

include template('common/selectpic');
?>