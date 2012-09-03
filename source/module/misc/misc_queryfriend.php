<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
$pagesize = 20;
$start = ($page - 1) * $pagesize;

$name = $_POST["name"];

if($name){
    $wheresql = " AND profile.realname LIKE '%$name%'";
}

$query = DB::query("SELECT COUNT(1) AS c FROM ".DB::table('home_friend')." main INNER JOIN ".DB::table('common_member_profile')." profile ON main.fuid=profile.uid WHERE main.uid='$_G[uid]'".$wheresql);
$count = DB::fetch($query);
$count = $count[c];
if($count){
    $query = DB::query("SELECT main.fuid,main.fusername,main.uid,profile.realname FROM ".DB::table('home_friend')." main  INNER JOIN ".DB::table('common_member_profile')." profile ON main.fuid=profile.uid WHERE main.uid='$_G[uid]' ".$wheresql." ORDER BY main.dateline LIMIT $start, $pagesize");
    while($row = DB::fetch($query)){
        $friends[] = $row;
    }
    $url = "misc.php?mod=queryqueryfriend";
    if($name){
        $url .= "&name=".  urlencode(base64_encode($name));
    }
    $multipage = multi($count, $pagesize, $page, $url);
}
if($name){
    header('Content-Type: text/xml');
    $sql = '<?xml version="1.0" encoding="utf-8"?><root><![CDATA[<table width="100%"><tr><td>选择</td><td>名称</td></tr>';
    foreach ($friends as $friend){
        $sql .= '<tr><td><input type="radio" id="ids" name="ids" value="'.$friend[username].'" title="'.$friend[realname].'"/></td><td>'.$friend[realname].'</td></tr>';
    }
    $sql .='<tr><td colspan="2">'.$multipage.'</td></tr></table>]]></root>';
    echo $sql;
    exit;
}

include template('common/queryfriend');
?>