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
	$count = DB::query("SELECT * FROM ".DB::table("forum_post")." fp,".DB::table("forum_thread")
				." ft WHERE fp.tid=ft.tid AND ft.special=0 AND ft.displayorder in (0, 1, 2, 3, 4) AND fp.subject LIKE '%".$search."%'");

    $sql = "SELECT * FROM ".DB::table("forum_post")." fp,".DB::table("forum_thread")
            ." ft WHERE fp.tid=ft.tid AND ft.special=0 AND ft.displayorder in (0, 1, 2, 3, 4) AND fp.subject LIKE '%".$search."%' LIMIT $start_limit, $_G[tpp]";
    $query = DB::query($sql);
    $datalist = array();
    while($row=DB::fetch($query)){
        $row[name] = $row[subject];
        $row[description] = cutstr($row[message], 50);
        $row[url] = "forum.php?mod=viewthread&special=0&plugin_name=topic&plugin_op=groupmenu&tid=".$row[tid];
        $datalist[] = $row;
    }

    $countdata = mysql_num_rows($count);
    $multipage = multi($countdata, $_G[tpp], $page, "search.php?mod=topic&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes");
    include template("search/other");
}else{
	include template("search/search");
}

?>