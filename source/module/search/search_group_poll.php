<?php

/**
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);
$srchtxt = $_G['gp_srchtxt'];
if($srchtxt){
    $searchid = $srchtxt;
}else{
	$searchid = $_GET['searchid'];
	$srchtxt = $searchid;
}
$keyword = isset($srchtxt) ? htmlspecialchars(trim($srchtxt)) : '';
$search = explode(" ", $keyword);
$search = $search[0];
if(submitcheck('searchsubmit', 1)) {
	$page = max(1, intval($_G['gp_page']));
	$start_limit = ($page - 1) * $_G['tpp'];
    $sql2 = "SELECT ft.tid, ft.subject, fp.message FROM ".DB::table("forum_thread")
    			." ft,".DB::table("forum_post")." fp WHERE ft.tid=fp.tid AND ft.special=1 AND ft.subject LIKE '%".$search."%'";
    $sql = "SELECT ft.tid, ft.subject, fp.message FROM ".DB::table("forum_thread")
    		." ft,".DB::table("forum_post")." fp WHERE ft.tid=fp.tid AND ft.special=1 AND ft.subject LIKE '%".$search."%'  LIMIT $start_limit, $_G[tpp]";
    $query = DB::query($sql);
    $count = DB::query($sql2);
    $datalist = array();
    while($row=DB::fetch($query)){
        $row[name] = $row["subject"];
        $row[url] = "forum.php?mod=viewthread&special=1&plugin_name=poll&plugin_op=createmenu&tid=".$row[tid];
        $row[description] = cutstr($row["message"], 50);
        $datalist[] = $row;
    }

    $countdata = mysql_num_rows($count);
    $multipage = multi($countdata, $_G[tpp], $page, "search.php?mod=activity&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes");

    include template("search/other");
}else{
	include template("search/search");
}

?>