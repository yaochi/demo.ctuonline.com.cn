<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}


$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
$pagesize = 20;
$start = ($page - 1) * $pagesize;

$name = $_REQUEST["name"];
$name=urldecode($name);
//exit($name);
if($name){
    $wheresql = " WHERE name LIKE '%$name%'";
}

$query = DB::query("SELECT COUNT(1) AS c FROM ".DB::table("lecturer").$wheresql);
$count = DB::fetch($query);
$count = $count[c];
if($count){
    $query = DB::query("SELECT * FROM " . DB::table("lecturer").$wheresql." ORDER BY dateline LIMIT $start, $pagesize");
    while($row = DB::fetch($query)){
        $teachers[] = $row;
    }
    $url = "misc.php?mod=queryteacher";
    if($name){
        $url .= "&name=".urlencode($name);
        //echo $teachers;
    }
    $multipage = multi($count, $pagesize, $page, $url);
}
/*if($name){

	$url = "misc.php?mod=queryteacher&name=".urlencode(($name));
	$multipage = multi($count, $pagesize, $page, $url);
    $sql = '<table width="100%"><tr><td>选择</td><td>名称</td></tr>';
    foreach ($teachers as $teacher){
        $sql .= '<tr><td><input type="checkbox" id="ids" name="ids" value="'.$teacher[id].'" title="'.$teacher[name].'"/></td><td>'.$teacher[name].'</td></tr>';
    }
    $sql .='<tr><td colspan="2">'.$multipage.'</td></tr>';
    $sql.='</table>';
    echo $sql;
    exit;
}*/

include template('common/queryteacher');
?>