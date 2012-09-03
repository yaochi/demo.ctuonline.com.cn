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
	$count = DB::query("SELECT q.questid, q.fid, q.questname, q.questdescr FROM ".DB::table("questionary")." q WHERE q.questname LIKE '%".$search."%'");


    $sql = "SELECT q.questid, q.fid, q.questname, q.questdescr FROM ".DB::table("questionary")." q WHERE q.questname LIKE '%".$search."%' LIMIT $start_limit, $_G[tpp]";
    $query = DB::query($sql);
    $datalist = array();
    while($row=DB::fetch($query)){
        $row[name] = $row["questname"];
        $row[description] = cutstr($row["questdescr"], 50);
        $row[url] = "forum.php?mod=group&action=plugin&fid=".$row[fid]."&plugin_name=questionary&plugin_op=groupmenu&questid=".$row[questid]."&questionary_action=answer";
        $datalist[] = $row;
    }
    $countdata = mysql_num_rows($count);
    $multipage = multi($countdata, $_G[tpp], $page, "search.php?mod=question&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes");

    include template("search/other");
}else{
	include template("search/search");
}

?>