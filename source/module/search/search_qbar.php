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
$bypage=$_G['bypage'];
if(submitcheck('searchsubmit', 1)) {
	/*$pagesize = 10;
    $page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
    $start = ($page - 1) * $pagesize;*/
    $page = max(1, intval($_G['gp_page']));
	$start_limit = ($page - 1) * $_G['tpp'];
	$count = DB::query("SELECT ft.subject as subject, fp.message as message,fp.tid as tid FROM ".DB::table("forum_thread")." ft,".DB::table("forum_post")
        ." fp WHERE ft.tid=fp.tid AND fp.first=1 AND ft.displayorder in (0, 1, 2, 3, 4) AND ft.special=3 AND ft.subject LIKE '%".$search."%'");



    $sql = "SELECT ft.subject as subject, fp.message as message,fp.tid as tid FROM ".DB::table("forum_thread")." ft,".DB::table("forum_post")
        ." fp WHERE ft.tid=fp.tid AND fp.first=1 AND ft.displayorder in (0, 1, 2, 3, 4) AND ft.special=3 AND ft.subject LIKE '%".$search."%'  LIMIT $start_limit, $_G[tpp]";
    //$sql="SELECT ft.subject,ft.tid,ft.fid FROM pre_forum_thread ft WHERE ft.special=3 AND ft.subject like '%$search%' LIMIT $start,$pagesize";
    //$sql="SELECT ft.subject,ft.tid,ft.fid FROM pre_forum_thread ft WHERE ft.special=3 AND ft.subject like '%$search%'";
    //$countsql="SELECT count(*) FROM pre_forum_thread ft,pre_forum_post fp WHERE ft.special=3 AND ft.tid=fp.tid AND ft.subject like '%$search%'";
	$query = DB::query($sql);
    $datalist = array();
    while($row=DB::fetch($query)){
        $row[name] = $row["subject"];
        $row[url] = "forum.php?mod=viewthread&tid=".$row[tid];
        $row[description] = cutstr($row["message"], 50);
        $datalist[] = $row;
    }
/*	$query=DB::query($countsql);
	$count=DB::fetch($query,0);
	foreach($count as $value){
		$count=$value;
	}
    $url = "search.php?mod=qbar&bypage=1&keyword=$search&searchsubmit=yes";
    $multipage = multi($count, $pagesize, $page, $url);*/

    $countdata = mysql_num_rows($count);
    $multipage = multi($countdata, $_G[tpp], $page, "search.php?mod=qbar&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes");
    include template("search/other");
}else{

	include template("search/search");
}

?>