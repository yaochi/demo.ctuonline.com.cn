<?php


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
	
	
   $count = DB::result_first("SELECT count(*) FROM ".DB::table(common_member_status)." cms,".DB::table('common_member_profile')." profile  WHERE cms.uid=profile.uid and profile.realname LIKE '%".$search."%'");
   if($count){
		$query = DB::query("SELECT * FROM ".DB::table(common_member_status)." cms,".DB::table('common_member_profile')." profile  WHERE cms.uid=profile.uid and profile.realname LIKE '%".$search."%'  LIMIT $start_limit, $_G[tpp]");
		$datalist = array();
		while($row=DB::fetch($query)){
			$datalist[] = $row;
		}
	}
    $multipage = multi($count, $_G[tpp], $page, "search.php?mod=member&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes");

    include template("search/member");
}else{
	include template("search/search");
}

?>