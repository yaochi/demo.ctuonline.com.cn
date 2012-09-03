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
	$count = DB::query("SELECT ff.*, fff.description,ffa.viewnum FROM ".DB::table("forum_forum")." ff,".DB::table("forum_forumfield")." fff,".DB::table("forum_forum_activity")." ffa
        WHERE ff.fid=fff.fid AND ff.fid=ffa.fid AND ff.name like '%".$search."%'");

    $query = DB::query("SELECT ff.*, fff.description,ffa.viewnum FROM ".DB::table("forum_forum")." ff,".DB::table("forum_forumfield")." fff,".DB::table("forum_forum_activity")." ffa
        WHERE ff.fid=fff.fid AND ff.fid=ffa.fid AND ff.name like '%".$search."%'  LIMIT $start_limit, $_G[tpp]");
    $datalist = array();
    while($row=DB::fetch($query)){
    	$row[url] = "forum.php?mod=activity&fup=".$row['fid']."&fid=".$row['fup'];
        $datalist[] = $row;
    }

    /*echo "start_limit:".$start_limit."<br>";
    echo "page:".$page."<br>";
    echo "gp_page:".intval($_G['gp_page'])."<br>";
    echo "search:".$search."<br>";
    echo "datalist:".count($datalist)."<br>";*/
	$countdata = mysql_num_rows($count);
    $multipage = multi($countdata, $_G[tpp], $page, "search.php?mod=activity&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes");
    include template("search/other");
}else{
	include template("search/search");
}

?>